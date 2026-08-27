#!/usr/bin/env python3
# -*- coding: utf-8 -*-

"""
MindHub Backend - GROUP 1 FINAL AUDIT (READ ONLY)

Mục tiêu:
- Không sửa bất kỳ file nào.
- Kiểm tra Group 1: SoftDelete/legacy cleanup + Course/Category/Moderation cleanup.
- Kiểm tra các file bị xóa có còn dependency/call site hay không.
- Kiểm tra route chết đã được gỡ.
- PHP lint toàn bộ PHP file đang modified/deleted context (chỉ lint file còn tồn tại).
- php artisan about + route:list.
- Phân biệt:
    BLOCKER GROUP 1  -> phải sửa trước khi sang nhóm khác.
    DEFERRED         -> legacy thuộc nhóm khác, ghi nhận nhưng không chặn Group 1.
- Xuất report Markdown để gửi lại ChatGPT.

Không commit / không push / không sửa Migration / Model / source code.
"""

from __future__ import annotations

import datetime as dt
import re
import subprocess
import sys
from pathlib import Path


# ============================================================
# Helpers
# ============================================================

def run(cmd: list[str], cwd: Path) -> subprocess.CompletedProcess:
    return subprocess.run(
        cmd,
        cwd=str(cwd),
        capture_output=True,
        text=True,
        encoding="utf-8",
        errors="replace",
        shell=False,
    )


def find_app_root() -> Path:
    here = Path.cwd().resolve()
    candidates = [here, here / "BE", here.parent / "BE"]
    for p in candidates:
        if (p / "artisan").exists() and (p / "app").is_dir():
            return p
    raise SystemExit(
        "Không tìm thấy Laravel app root. "
        "Hãy chạy script trong D:\\laragon\\www\\datn\\MindHub-Backend\\BE"
    )


def git_root(app_root: Path) -> Path:
    cp = run(["git", "rev-parse", "--show-toplevel"], app_root)
    if cp.returncode != 0:
        raise SystemExit("Không xác định được git root.")
    return Path(cp.stdout.strip()).resolve()


def text(path: Path) -> str:
    return path.read_text(encoding="utf-8", errors="replace")


def rel(path: Path, root: Path) -> str:
    try:
        return path.resolve().relative_to(root.resolve()).as_posix()
    except Exception:
        return str(path)


def scan_files(root: Path, patterns: dict[str, str], dirs: list[Path]) -> dict[str, list[str]]:
    compiled = {k: re.compile(v, re.I) for k, v in patterns.items()}
    result = {k: [] for k in patterns}
    for base in dirs:
        if not base.exists():
            continue
        for p in base.rglob("*.php"):
            try:
                lines = text(p).splitlines()
            except Exception:
                continue
            for i, line in enumerate(lines, 1):
                for name, rx in compiled.items():
                    if rx.search(line):
                        result[name].append(f"{rel(p, root)}:{i}: {line.strip()}")
    return result


def scan_exact_refs(root: Path, needles: list[str], excludes: set[Path] | None = None) -> list[str]:
    excludes = {p.resolve() for p in (excludes or set())}
    hits: list[str] = []
    for base in [root / "app", root / "routes", root / "bootstrap", root / "config"]:
        if not base.exists():
            continue
        for p in base.rglob("*.php"):
            if p.resolve() in excludes:
                continue
            try:
                for i, line in enumerate(text(p).splitlines(), 1):
                    if any(n in line for n in needles):
                        hits.append(f"{rel(p, root)}:{i}: {line.strip()}")
            except Exception:
                continue
    return hits


def resolve_git_path(repo_root: Path, app_root: Path, raw: str) -> Path | None:
    raw = raw.strip().replace("\\", "/")
    if not raw:
        return None

    p1 = repo_root / raw
    if p1.exists():
        return p1

    p2 = app_root / raw
    if p2.exists():
        return p2

    # Repo root có thể là ...\MindHub-Backend, còn app nằm trong BE/
    if raw.startswith("BE/"):
        p3 = app_root / raw[3:]
        if p3.exists():
            return p3

    return None


# ============================================================
# Main
# ============================================================

def main() -> int:
    root = find_app_root()
    repo = git_root(root)

    stamp = dt.datetime.now().strftime("%Y%m%d_%H%M%S")
    audit_dir = root / "_audit"
    audit_dir.mkdir(parents=True, exist_ok=True)
    report = audit_dir / f"GROUP1_FINAL_AUDIT_{stamp}.md"

    blockers: list[str] = []
    warnings: list[str] = []

    # --------------------------------------------------------
    # 1. Git state
    # --------------------------------------------------------
    branch = run(["git", "branch", "--show-current"], repo)
    commit = run(["git", "rev-parse", "HEAD"], repo)
    status = run(["git", "status", "--short"], repo)
    diff_stat = run(["git", "diff", "--stat"], repo)
    diff_full = run(["git", "diff", "--no-ext-diff", "--unified=3"], repo)

    changed_names_cp = run(["git", "diff", "--name-only", "--diff-filter=ACMR"], repo)
    deleted_names_cp = run(["git", "diff", "--name-only", "--diff-filter=D"], repo)

    changed_raw = [x.strip() for x in changed_names_cp.stdout.splitlines() if x.strip()]
    deleted_raw = [x.strip() for x in deleted_names_cp.stdout.splitlines() if x.strip()]

    # --------------------------------------------------------
    # 2. Group 1 exact target scan
    # --------------------------------------------------------
    target_patterns = {
        "SoftDelete field deleted_at": r"\bdeleted_at\b",
        "withTrashed": r"\bwithTrashed\s*\(",
        "onlyTrashed": r"\bonlyTrashed\s*\(",
        "SoftDeletes trait": r"\bSoftDeletes\b",
        "Category restore method": r"function\s+restore\s*\(",
        "Category fake findWithTrashed": r"function\s+findWithTrashed\s*\(",
        "Category fake findOnlyTrashed": r"function\s+findOnlyTrashed\s*\(",
        "CourseViewService": r"\bCourseViewService\b",
        "recordView method/call": r"\brecordView\s*\(",
        "course_views table": r"\bcourse_views\b",
        "Quiz checklist placeholder": r"\bgetChecklistQuiz(?:zes|QuestionStats)\s*\(",
        "Course legacy approved_at": r"\bapproved_at\b",
        "Course legacy rejected_reason": r"\brejected_reason\b",
        "Course legacy total_duration_seconds": r"\btotal_duration_seconds\b",
        "courses.level": r"\bcourses\.level\b",
    }

    # Chỉ các file thuộc phạm vi Group 1 đã chốt.
    group1_files = [
        root / "app/Http/Controllers/AdminCategoryController.php",
        root / "app/Http/Controllers/CoursePublicController.php",
        root / "app/Repositories/Admin/AdminCategoryRepository.php",
        root / "app/Repositories/Admin/AdminCourseRepository.php",
        root / "app/Repositories/Catalog/BannerRepository.php",
        root / "app/Repositories/Catalog/CatalogCourseRepository.php",
        root / "app/Repositories/Catalog/CategoryRepository.php",
        root / "app/Repositories/Catalog/FeaturedInstructorRepository.php",
        root / "app/Repositories/Instructor/InstructorCourseRepository.php",
        root / "app/Repositories/Moderation/CourseModerationRepository.php",
        root / "app/Services/Admin/AdminCategoryService.php",
        root / "app/Services/Admin/AdminCourseService.php",
        root / "app/Services/Catalog/CatalogService.php",
        root / "app/Services/Course/CourseAvailabilityService.php",
        root / "app/Services/Course/CoursePublicService.php",
        root / "app/Services/Course/RelatedCourseService.php",
        root / "app/Services/Instructor/CourseChecklistService.php",
        root / "app/Services/Instructor/InstructorCourseService.php",
        root / "app/Services/Moderation/ModerationService.php",
        root / "routes/api/admin.php",
        root / "routes/api/course.php",
    ]

    group1_hits = {k: [] for k in target_patterns}
    compiled = {k: re.compile(v, re.I) for k, v in target_patterns.items()}

    for p in group1_files:
        if not p.exists():
            # CourseViewService deliberately deleted and is NOT in list.
            continue
        try:
            for i, line in enumerate(text(p).splitlines(), 1):
                for name, rx in compiled.items():
                    if rx.search(line):
                        group1_hits[name].append(f"{rel(p, root)}:{i}: {line.strip()}")
        except Exception as e:
            blockers.append(f"Không đọc được {rel(p, root)}: {e}")

    # Exceptions / interpretation:
    # - "total_duration_seconds" may exist in unrelated learner/report code, but here group1 target only.
    # - approved_at may exist in Withdrawal later, not scanned here.
    # Any hit in target Group1 files is blocker.
    for name, hits in group1_hits.items():
        if hits:
            blockers.append(f"{name}: còn {len(hits)} hit(s) trong phạm vi Group 1")

    # --------------------------------------------------------
    # 3. Stale routes must be gone
    # --------------------------------------------------------
    routes_cp = run(["php", "artisan", "route:list"], root)
    route_output = routes_cp.stdout + routes_cp.stderr

    stale_routes = {
        "Category restore": r"categories/\{id\}/restore",
        "Course view": r"courses/\{courseId\}/view",
    }
    stale_route_hits: dict[str, list[str]] = {}
    for name, pat in stale_routes.items():
        rx = re.compile(pat, re.I)
        hits = [line.strip() for line in route_output.splitlines() if rx.search(line)]
        stale_route_hits[name] = hits
        if hits:
            blockers.append(f"Stale route còn tồn tại: {name}")

    # --------------------------------------------------------
    # 4. Deleted files safety audit
    # --------------------------------------------------------
    # Các file delete từng xuất hiện trong working tree.
    deleted_service_contracts = {
        "app/Services/AdminService.php": [
            r"use\s+App\\Services\\AdminService\s*;",
            r"\\App\\Services\\AdminService\b",
        ],
        "app/Services/CoursePublicService.php": [
            r"use\s+App\\Services\\CoursePublicService\s*;",
            r"\\App\\Services\\CoursePublicService\b",
        ],
        "app/Services/InteractionService.php": [
            r"use\s+App\\Services\\InteractionService\s*;",
            r"\\App\\Services\\InteractionService\b",
        ],
        "app/Services/MarketingService.php": [
            r"use\s+App\\Services\\MarketingService\s*;",
            r"\\App\\Services\\MarketingService\b",
        ],
        "app/Services/ModerationService.php": [
            r"use\s+App\\Services\\ModerationService\s*;",
            r"\\App\\Services\\ModerationService\b",
        ],
        "app/Services/QuizService.php": [
            r"use\s+App\\Services\\QuizService\s*;",
            r"\\App\\Services\\QuizService\b",
        ],
        "app/Services/Course/CourseViewService.php": [
            r"use\s+App\\Services\\Course\\CourseViewService\s*;",
            r"\\App\\Services\\Course\\CourseViewService\b",
            r"\bCourseViewService::class\b",
        ],
    }

    deleted_dependency_results: dict[str, list[str]] = {}

    for deleted_file, regexes in deleted_service_contracts.items():
        # Only audit as a deletion concern if git says deleted OR file no longer exists.
        candidate = root / deleted_file
        relevant = (not candidate.exists()) or any(
            x.replace("\\", "/").endswith(deleted_file) for x in deleted_raw
        )
        if not relevant:
            continue

        hits: list[str] = []
        compiled_refs = [re.compile(x) for x in regexes]
        for base in [root / "app", root / "routes", root / "bootstrap", root / "config"]:
            if not base.exists():
                continue
            for p in base.rglob("*.php"):
                if p.resolve() == candidate.resolve():
                    continue
                try:
                    for i, line in enumerate(text(p).splitlines(), 1):
                        if any(rx.search(line) for rx in compiled_refs):
                            hits.append(f"{rel(p, root)}:{i}: {line.strip()}")
                except Exception:
                    continue

        deleted_dependency_results[deleted_file] = hits
        if hits:
            blockers.append(
                f"File bị xóa `{deleted_file}` vẫn còn {len(hits)} dependency/call-site exact."
            )

    # Backup route file deletion is not runtime blocker; just warning.
    backup_deleted = [
        x for x in deleted_raw
        if ".backup-" in x.lower() or x.lower().endswith(".bak")
    ]
    if backup_deleted:
        warnings.append(
            "Có file backup cũ đang bị git đánh dấu deleted. Không phải runtime blocker, "
            "nhưng cần quyết định có muốn commit việc xóa file backup này hay không."
        )

    # --------------------------------------------------------
    # 5. PHP lint all changed PHP files that still exist
    # --------------------------------------------------------
    lint_results: list[tuple[str, int, str]] = []

    for raw in changed_raw:
        if not raw.lower().endswith(".php"):
            continue
        p = resolve_git_path(repo, root, raw)
        if p is None or not p.exists():
            warnings.append(f"Không resolve được changed PHP path để lint: {raw}")
            continue
        cp = run(["php", "-l", str(p)], root)
        lint_results.append((rel(p, root), cp.returncode, (cp.stdout + cp.stderr).strip()))
        if cp.returncode != 0:
            blockers.append(f"PHP lint FAIL: {rel(p, root)}")

    # --------------------------------------------------------
    # 6. Artisan boot checks
    # --------------------------------------------------------
    about_cp = run(["php", "artisan", "about"], root)

    if about_cp.returncode != 0:
        blockers.append("php artisan about FAIL")
    if routes_cp.returncode != 0:
        blockers.append("php artisan route:list FAIL")

    # --------------------------------------------------------
    # 7. Deferred legacy scan outside Group 1
    # --------------------------------------------------------
    deferred_patterns = {
        "deleted_at": r"\bdeleted_at\b",
        "withTrashed": r"\bwithTrashed\s*\(",
        "onlyTrashed": r"\bonlyTrashed\s*\(",
        "Quiz model refs": r"App\\Models\\Quiz(?:Attempt|Question|Answer|Option)?\b",
        "course_views": r"\bcourse_views\b",
        "approved_at": r"\bapproved_at\b",
        "rejected_reason": r"\brejected_reason\b",
        "courses.level": r"\bcourses\.level\b",
        "object ->level": r"->level\b",
        "revenues.status": r"\brevenues\.status\b",
        "sale_source/sale_channel": r"\b(?:sale_source|sale_channel)\b",
    }

    deferred_scan = scan_files(
        root,
        deferred_patterns,
        [root / "app/Repositories", root / "app/Services", root / "app/Http/Controllers"],
    )

    # Remove hits already in target Group1 files from deferred listing to make it truly "later".
    group1_abs = {p.resolve() for p in group1_files if p.exists()}
    deferred_filtered: dict[str, list[str]] = {}
    for name, hits in deferred_scan.items():
        kept = []
        for h in hits:
            # h begins app/...:line:
            file_part = h.split(":", 1)[0]
            hp = root / file_part
            if hp.exists() and hp.resolve() in group1_abs:
                continue
            kept.append(h)
        deferred_filtered[name] = kept

    # --------------------------------------------------------
    # 8. PASS criteria
    # --------------------------------------------------------
    overall_pass = len(blockers) == 0

    # --------------------------------------------------------
    # 9. Report
    # --------------------------------------------------------
    out: list[str] = []
    out += [
        "# GROUP 1 FINAL AUDIT",
        "",
        f"**Generated:** {dt.datetime.now().isoformat(timespec='seconds')}",
        f"**App root:** `{root}`",
        f"**Git root:** `{repo}`",
        f"**Branch:** `{branch.stdout.strip()}`",
        f"**Commit:** `{commit.stdout.strip()}`",
        "",
        f"# KẾT LUẬN: {'PASS' if overall_pass else 'CHƯA PASS'}",
        "",
    ]

    out += ["## 1. BLOCKERS"]
    if blockers:
        out += [f"- {b}" for b in blockers]
    else:
        out.append("- Không có blocker Group 1.")
    out.append("")

    out += ["## 2. WARNINGS"]
    if warnings:
        out += [f"- {w}" for w in warnings]
    else:
        out.append("- Không có.")
    out.append("")

    out += ["## 3. GROUP 1 TARGETED LEGACY SCAN"]
    for name, hits in group1_hits.items():
        out.append(f"### {name}: {'PASS' if not hits else 'FAIL'}")
        if hits:
            out += [f"- `{h}`" for h in hits]
        else:
            out.append("- CLEAN")
        out.append("")

    out += ["## 4. STALE ROUTE CHECK"]
    for name, hits in stale_route_hits.items():
        out.append(f"### {name}: {'PASS' if not hits else 'FAIL'}")
        out += [f"- `{h}`" for h in hits] if hits else ["- Không còn route."]
        out.append("")

    out += ["## 5. DELETED FILE DEPENDENCY AUDIT"]
    if deleted_dependency_results:
        for file_name, hits in deleted_dependency_results.items():
            out.append(f"### `{file_name}`: {'PASS' if not hits else 'FAIL'}")
            if hits:
                out += [f"- `{h}`" for h in hits]
            else:
                out.append("- Không còn exact dependency/call-site.")
            out.append("")
    else:
        out.append("- Không có deleted service candidate để audit.")
        out.append("")

    out += ["## 6. PHP LINT — CHANGED PHP FILES"]
    if lint_results:
        for file_name, rc, msg in lint_results:
            out += [
                f"### `{file_name}` — {'PASS' if rc == 0 else 'FAIL'}",
                "```text",
                msg,
                "```",
                "",
            ]
    else:
        out.append("- Không có changed PHP file nào được lint.")
        out.append("")

    out += [
        "## 7. PHP ARTISAN ABOUT",
        f"**Exit code:** {about_cp.returncode}",
        "```text",
        *(about_cp.stdout + about_cp.stderr).splitlines(),
        "```",
        "",
        "## 8. PHP ARTISAN ROUTE:LIST",
        f"**Exit code:** {routes_cp.returncode}",
        "```text",
        *(route_output.splitlines()),
        "```",
        "",
    ]

    out += [
        "## 9. DEFERRED LEGACY — KHÔNG CHẶN GROUP 1",
        "",
        "> Các hit dưới đây nằm ngoài phạm vi Group 1 đã chốt. "
        "Chúng phải được xử lý ở nhóm nghiệp vụ tương ứng, nhưng không tự động làm Group 1 FAIL.",
        "",
    ]
    for name, hits in deferred_filtered.items():
        out.append(f"### `{name}` — {len(hits)} hit(s)")
        if hits:
            out += [f"- `{h}`" for h in hits[:120]]
            if len(hits) > 120:
                out.append(f"- ... còn {len(hits) - 120} hit(s)")
        else:
            out.append("- No matches")
        out.append("")

    out += [
        "## 10. GIT STATUS",
        "```text",
        *(status.stdout + status.stderr).splitlines(),
        "```",
        "",
        "## 11. GIT DIFF STAT",
        "```text",
        *(diff_stat.stdout + diff_stat.stderr).splitlines(),
        "```",
        "",
        "## 12. FULL GIT DIFF",
        "```diff",
        *(diff_full.stdout + diff_full.stderr).splitlines(),
        "```",
        "",
    ]

    report.write_text("\n".join(out), encoding="utf-8")

    print("=" * 76)
    print("MINDHUB - GROUP 1 FINAL AUDIT (READ ONLY)")
    print("=" * 76)
    print("Result :", "PASS" if overall_pass else "CHUA PASS")
    print("Blockers:", len(blockers))
    print("Report :", report)
    print("=" * 76)

    return 0 if overall_pass else 2


if __name__ == "__main__":
    sys.exit(main())
