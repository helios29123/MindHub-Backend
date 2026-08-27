#!/usr/bin/env python3
# -*- coding: utf-8 -*-

"""
MindHub Backend - Group 1 remaining cleanup
Safe, guarded fixer + verifier.

Mục tiêu:
1) Dọn legacy field còn sót trong InstructorCourseService
2) Xóa 2 Quiz placeholder methods trong InstructorCourseRepository
3) Gỡ API SoftDelete giả của Category (Repository/Service + controller/route nếu phát hiện đúng pattern)
4) Gỡ CourseView khỏi public flow và deleted_at khỏi ModerationService
5) Chạy php -l, artisan about, route:list, legacy scan và xuất report Markdown

Không commit/push.
Không sửa Migration/Model.
Tạo backup trước khi ghi file.
"""

from __future__ import annotations

import datetime as dt
import os
import re
import shutil
import subprocess
import sys
from pathlib import Path
from typing import Iterable

LEGACY_PATTERNS = [
    r"deleted_at",
    r"withTrashed",
    r"onlyTrashed",
    r"restore\s*\(",
    r"SoftDeletes",
    r"App\\Models\\Quiz",
    r"App\\Models\\QuizQuestion",
    r"App\\Models\\QuizOption",
    r"App\\Models\\QuizAttempt",
    r"getChecklistQuizzes",
    r"getChecklistQuizQuestionStats",
    r"CourseViewService",
    r"recordView\s*\(",
    r"course_views",
    r"approved_at",
    r"approved_by",
    r"rejected_reason",
    r"hidden_reason",
    r"courses\.level",
    r"->level\b",
    r"total_duration_seconds",
]

def now_stamp() -> str:
    return dt.datetime.now().strftime("%Y%m%d_%H%M%S")

def run(cmd: list[str], cwd: Path, check: bool = False) -> subprocess.CompletedProcess:
    return subprocess.run(
        cmd,
        cwd=str(cwd),
        capture_output=True,
        text=True,
        encoding="utf-8",
        errors="replace",
        check=check,
        shell=False,
    )

def detect_app_root() -> Path:
    here = Path.cwd().resolve()
    candidates = [here, here / "BE", here.parent / "BE"]
    for p in candidates:
        if (p / "artisan").exists() and (p / "app").is_dir():
            return p
    raise SystemExit("Không tìm thấy Laravel app root (cần có artisan + app/). Hãy chạy script trong thư mục BE.")

def git_root(app_root: Path) -> Path:
    cp = run(["git", "rev-parse", "--show-toplevel"], app_root)
    if cp.returncode != 0:
        raise SystemExit("Không xác định được git root.")
    return Path(cp.stdout.strip()).resolve()

def read_text(path: Path) -> str:
    return path.read_text(encoding="utf-8")

def write_text(path: Path, text: str) -> None:
    path.write_text(text, encoding="utf-8", newline="")

def backup_file(path: Path, app_root: Path, backup_root: Path) -> None:
    rel = path.resolve().relative_to(app_root.resolve())
    dest = backup_root / rel
    dest.parent.mkdir(parents=True, exist_ok=True)
    shutil.copy2(path, dest)

def replace_exact(text: str, old: str, new: str) -> tuple[str, int]:
    count = text.count(old)
    return text.replace(old, new), count

def remove_method(text: str, method_name: str) -> tuple[str, bool]:
    """
    Remove a PHP method by locating `function method_name(` and balancing braces.
    Handles public/protected/private/static modifiers.
    """
    pattern = re.compile(
        rf"(?m)^[ \t]*(?:public|protected|private)?[ \t]*(?:static[ \t]+)?function[ \t]+{re.escape(method_name)}[ \t]*\("
    )
    m = pattern.search(text)
    if not m:
        return text, False

    start = m.start()
    brace = text.find("{", m.end())
    if brace == -1:
        return text, False

    depth = 0
    i = brace
    in_single = in_double = False
    escaped = False

    while i < len(text):
        ch = text[i]

        if escaped:
            escaped = False
            i += 1
            continue

        if ch == "\\" and (in_single or in_double):
            escaped = True
            i += 1
            continue

        if ch == "'" and not in_double:
            in_single = not in_single
        elif ch == '"' and not in_single:
            in_double = not in_double
        elif not in_single and not in_double:
            if ch == "{":
                depth += 1
            elif ch == "}":
                depth -= 1
                if depth == 0:
                    end = i + 1
                    while end < len(text) and text[end] in " \t":
                        end += 1
                    if end < len(text) and text[end] == "\r":
                        end += 1
                    if end < len(text) and text[end] == "\n":
                        end += 1
                    return text[:start] + text[end:], True
        i += 1

    return text, False

def find_php_call_sites(app_root: Path, needles: Iterable[str], exclude: Iterable[Path] = ()) -> list[str]:
    excludes = {p.resolve() for p in exclude}
    hits = []
    for path in (app_root / "app").rglob("*.php"):
        if path.resolve() in excludes:
            continue
        try:
            content = read_text(path)
        except Exception:
            continue
        for needle in needles:
            if needle in content:
                rel = path.relative_to(app_root)
                for idx, line in enumerate(content.splitlines(), 1):
                    if needle in line:
                        hits.append(f"{rel}:{idx}: {line.strip()}")
    return hits

def apply_file_change(path: Path, new_text: str, app_root: Path, backup_root: Path, changed: list[Path], notes: list[str]) -> None:
    old = read_text(path)
    if old == new_text:
        return
    backup_file(path, app_root, backup_root)
    write_text(path, new_text)
    changed.append(path)
    notes.append(f"CHANGED: {path.relative_to(app_root)}")

def scan_patterns(app_root: Path) -> dict[str, list[str]]:
    roots = [app_root / "app" / "Repositories", app_root / "app" / "Services"]
    result = {}
    for pattern in LEGACY_PATTERNS:
        regex = re.compile(pattern)
        hits = []
        for root in roots:
            if not root.exists():
                continue
            for path in root.rglob("*.php"):
                try:
                    for i, line in enumerate(read_text(path).splitlines(), 1):
                        if regex.search(line):
                            hits.append(f"{path.relative_to(app_root)}:{i}: {line.strip()}")
                except Exception:
                    pass
        result[pattern] = hits
    return result

def main() -> int:
    app_root = detect_app_root()
    repo_root = git_root(app_root)

    stamp = now_stamp()
    audit_dir = app_root / "_audit"
    audit_dir.mkdir(parents=True, exist_ok=True)

    backup_root = audit_dir / f"backup_group1_fix_{stamp}"
    backup_root.mkdir(parents=True, exist_ok=True)

    report_path = audit_dir / f"GROUP1_FIX_REPORT_{stamp}.md"

    changed: list[Path] = []
    notes: list[str] = []
    blocked: list[str] = []
    warnings: list[str] = []

    # -------------------------------------------------------
    # PATCH 1 - InstructorCourseService
    # -------------------------------------------------------
    p = app_root / "app/Services/Instructor/InstructorCourseService.php"
    if p.exists():
        text = read_text(p)
        original = text

        # Xóa các phần tử legacy trong các unset(...) list nếu còn.
        for token in [
            '$data["total_duration_seconds"],',
            "$data['total_duration_seconds'],",
            '$data["deleted_at"],',
            "$data['deleted_at'],",
        ]:
            text = text.replace(token, "")

        # Field moderation cũ -> field FINAL.
        text = text.replace("'approved_at' => null,", "'reviewed_by' => null,")
        text = text.replace('"approved_at" => null,', '"reviewed_by" => null,')
        text = text.replace("'rejected_reason' => null,", "'admin_reject_reason' => null,")
        text = text.replace('"rejected_reason" => null,', '"admin_reject_reason" => null,')

        # Nếu do replacement tạo duplicate reviewed_by/admin_reject_reason liên tiếp thì loại duplicate đơn giản.
        lines = text.splitlines(True)
        cleaned = []
        prev_key = None
        for line in lines:
            key = None
            if re.search(r"['\"]reviewed_by['\"]\s*=>", line):
                key = "reviewed_by"
            elif re.search(r"['\"]admin_reject_reason['\"]\s*=>", line):
                key = "admin_reject_reason"
            if key and key == prev_key:
                continue
            cleaned.append(line)
            prev_key = key
        text = "".join(cleaned)

        if text != original:
            apply_file_change(p, text, app_root, backup_root, changed, notes)
    else:
        blocked.append("Không tìm thấy InstructorCourseService.php")

    # -------------------------------------------------------
    # PATCH 2 - InstructorCourseRepository: remove Quiz placeholders
    # -------------------------------------------------------
    p = app_root / "app/Repositories/Instructor/InstructorCourseRepository.php"
    if p.exists():
        text = read_text(p)
        original = text
        for method in ["getChecklistQuizzes", "getChecklistQuizQuestionStats"]:
            text, removed = remove_method(text, method)
            if not removed and method in original:
                blocked.append(f"Không thể tự xóa method {method} trong InstructorCourseRepository.php")
        if text != original:
            apply_file_change(p, text, app_root, backup_root, changed, notes)
    else:
        blocked.append("Không tìm thấy InstructorCourseRepository.php")

    # -------------------------------------------------------
    # PATCH 3 - Category fake SoftDelete API
    # Repository methods
    # -------------------------------------------------------
    repo = app_root / "app/Repositories/Admin/AdminCategoryRepository.php"
    svc = app_root / "app/Services/Admin/AdminCategoryService.php"
    ctrl = app_root / "app/Http/Controllers/AdminCategoryController.php"

    if repo.exists():
        text = read_text(repo)
        original = text
        for method in ["findWithTrashed", "findOnlyTrashed"]:
            text, removed = remove_method(text, method)
            if not removed and method in original:
                blocked.append(f"Không thể tự xóa {method} trong AdminCategoryRepository.php")
        if text != original:
            apply_file_change(repo, text, app_root, backup_root, changed, notes)

    # Service restore + controller/route dependency
    if svc.exists():
        service_text = read_text(svc)
        has_restore = re.search(r"function\s+restore\s*\(", service_text) is not None

        if has_restore:
            # Kiểm tra controller call site.
            call_hits = find_php_call_sites(
                app_root,
                ["->restore(", "restore("],
                exclude=[svc]
            )

            # Chỉ tự xử lý controller AdminCategory + route categories restore;
            # các call site lạ sẽ block để tránh phá code.
            unexpected = [
                h for h in call_hits
                if "AdminCategoryController.php" not in h
                and "routes" not in h.lower()
                and "AdminCategoryService.php" not in h
            ]
            if unexpected:
                blocked.append(
                    "Category restore còn call site ngoài phạm vi an toàn: " + " | ".join(unexpected[:10])
                )
            else:
                new_svc, removed = remove_method(service_text, "restore")
                if removed:
                    apply_file_change(svc, new_svc, app_root, backup_root, changed, notes)

                # Remove controller restore method if exact controller exists.
                if ctrl.exists():
                    ctext = read_text(ctrl)
                    new_ctext, removed_ctrl = remove_method(ctext, "restore")
                    if removed_ctrl:
                        apply_file_change(ctrl, new_ctext, app_root, backup_root, changed, notes)
                else:
                    # Search possible controller path and handle only exact class filename.
                    matches = list((app_root / "app/Http/Controllers").rglob("AdminCategoryController.php"))
                    for cp in matches:
                        ctext = read_text(cp)
                        new_ctext, removed_ctrl = remove_method(ctext, "restore")
                        if removed_ctrl:
                            apply_file_change(cp, new_ctext, app_root, backup_root, changed, notes)

                # Remove route lines that clearly point to category restore.
                routes_dir = app_root / "routes"
                if routes_dir.exists():
                    for rp in routes_dir.glob("*.php"):
                        rtext = read_text(rp)
                        rlines = rtext.splitlines(True)
                        kept = []
                        removed_any = False
                        for line in rlines:
                            low = line.lower().replace(" ", "")
                            if "categories" in low and "restore" in low:
                                removed_any = True
                                continue
                            kept.append(line)
                        if removed_any:
                            apply_file_change(rp, "".join(kept), app_root, backup_root, changed, notes)

    # -------------------------------------------------------
    # PATCH 4A - CoursePublicService: remove CourseView call/comment
    # -------------------------------------------------------
    cps = app_root / "app/Services/Course/CoursePublicService.php"
    if cps.exists():
        text = read_text(cps)
        original = text
        lines = []
        for line in text.splitlines(True):
            if "CourseViewService" in line or "recordView(" in line:
                continue
            if "Record course view" in line:
                continue
            lines.append(line)
        text = "".join(lines)
        if text != original:
            apply_file_change(cps, text, app_root, backup_root, changed, notes)

    # Candidate delete CourseViewService only if no refs outside itself
    cvs = app_root / "app/Services/Course/CourseViewService.php"
    if cvs.exists():
        refs = find_php_call_sites(app_root, ["CourseViewService", "recordView("], exclude=[cvs])
        if not refs:
            backup_file(cvs, app_root, backup_root)
            cvs.unlink()
            changed.append(cvs)
            notes.append("DELETED candidate legacy: app/Services/Course/CourseViewService.php (không còn call site)")
        else:
            warnings.append("CourseViewService còn call site nên chưa xóa: " + " | ".join(refs[:10]))

    # -------------------------------------------------------
    # PATCH 4B - ModerationService: remove review deleted_at semantics
    # -------------------------------------------------------
    ms = app_root / "app/Services/Moderation/ModerationService.php"
    if ms.exists():
        text = read_text(ms)
        original = text

        # Xóa key deleted_at trong response arrays.
        text = re.sub(
            r"(?m)^[ \t]*['\"]deleted_at['\"]\s*=>\s*null,\s*\r?\n",
            "",
            text,
        )
        text = re.sub(
            r"(?m)^[ \t]*['\"]deleted_at['\"]\s*=>\s*\$r->deleted_at\s*\?\s*\$r->deleted_at->toISOString\(\)\s*:\s*null,\s*\r?\n",
            "",
            text,
        )

        # DB FINAL không có review deleted_at/status hidden; current record is visible if it exists.
        text = re.sub(
            r"\$statusVal\s*=\s*\$r->deleted_at\s*\?\s*['\"]deleted['\"]\s*:\s*['\"]visible['\"]\s*;",
            "$statusVal = 'visible';",
            text,
        )

        if text != original:
            apply_file_change(ms, text, app_root, backup_root, changed, notes)

    # -------------------------------------------------------
    # VERIFY
    # -------------------------------------------------------
    # Syntax lint on changed PHP files that still exist.
    lint_results = []
    for path in changed:
        if path.suffix.lower() == ".php" and path.exists():
            cp = run(["php", "-l", str(path)], app_root)
            lint_results.append((path.relative_to(app_root).as_posix(), cp.returncode, (cp.stdout + cp.stderr).strip()))

    about = run(["php", "artisan", "about"], app_root)
    routes = run(["php", "artisan", "route:list"], app_root)

    scan = scan_patterns(app_root)

    # Git status/diff
    status = run(["git", "status", "--short"], repo_root)
    diff_stat = run(["git", "diff", "--stat"], repo_root)
    diff = run(["git", "diff", "--no-ext-diff", "--unified=3"], repo_root)

    # Specific required checks
    required_checks = {
        "InstructorCourseService legacy fields": [],
        "InstructorCourseRepository quiz placeholders": [],
        "Category fake SoftDelete API": [],
        "Course public CourseView dependency": [],
        "Moderation deleted_at": [],
    }

    targets = {
        "InstructorCourseService legacy fields": app_root / "app/Services/Instructor/InstructorCourseService.php",
        "InstructorCourseRepository quiz placeholders": app_root / "app/Repositories/Instructor/InstructorCourseRepository.php",
        "Category fake SoftDelete API": None,
        "Course public CourseView dependency": app_root / "app/Services/Course/CoursePublicService.php",
        "Moderation deleted_at": app_root / "app/Services/Moderation/ModerationService.php",
    }

    if targets["InstructorCourseService legacy fields"].exists():
        t = read_text(targets["InstructorCourseService legacy fields"])
        for pat in ["total_duration_seconds", "deleted_at", "approved_at", "rejected_reason"]:
            if pat in t:
                required_checks["InstructorCourseService legacy fields"].append(pat)

    if targets["InstructorCourseRepository quiz placeholders"].exists():
        t = read_text(targets["InstructorCourseRepository quiz placeholders"])
        for pat in ["getChecklistQuizzes", "getChecklistQuizQuestionStats"]:
            if pat in t:
                required_checks["InstructorCourseRepository quiz placeholders"].append(pat)

    cat_hits = []
    for pp in [repo, svc]:
        if pp.exists():
            t = read_text(pp)
            for pat in ["findWithTrashed", "findOnlyTrashed", "function restore("]:
                if pat in t:
                    cat_hits.append(f"{pp.name}: {pat}")
    # route check
    route_out = routes.stdout + routes.stderr
    if re.search(r"categories.*restore", route_out, re.I):
        cat_hits.append("route:list vẫn còn category restore route")
    required_checks["Category fake SoftDelete API"] = cat_hits

    if targets["Course public CourseView dependency"].exists():
        t = read_text(targets["Course public CourseView dependency"])
        for pat in ["CourseViewService", "recordView("]:
            if pat in t:
                required_checks["Course public CourseView dependency"].append(pat)

    if targets["Moderation deleted_at"].exists():
        t = read_text(targets["Moderation deleted_at"])
        if "deleted_at" in t:
            required_checks["Moderation deleted_at"].append("deleted_at")

    # PASS criteria
    lint_ok = all(rc == 0 for _, rc, _ in lint_results)
    artisan_ok = about.returncode == 0 and routes.returncode == 0
    required_ok = all(not v for v in required_checks.values())
    overall_pass = lint_ok and artisan_ok and required_ok and not blocked

    # -------------------------------------------------------
    # REPORT
    # -------------------------------------------------------
    out = []
    out.append("# GROUP 1 FIX + VERIFY REPORT")
    out.append("")
    out.append(f"**Generated:** {dt.datetime.now().isoformat(timespec='seconds')}")
    out.append(f"**App root:** `{app_root}`")
    out.append(f"**Git root:** `{repo_root}`")
    out.append(f"**Backup:** `{backup_root}`")
    out.append("")
    out.append(f"## KẾT LUẬN: {'PASS' if overall_pass else 'CHƯA PASS'}")
    out.append("")

    out.append("## 1. Files changed by this script")
    if changed:
        for path in changed:
            try:
                rel = path.relative_to(app_root)
            except Exception:
                rel = path
            out.append(f"- `{rel}`")
    else:
        out.append("- Không có file nào được sửa.")
    out.append("")

    out.append("## 2. Patch notes")
    for n in notes:
        out.append(f"- {n}")
    if not notes:
        out.append("- Không có.")
    out.append("")

    out.append("## 3. Blocked / cần người duyệt")
    if blocked:
        for n in blocked:
            out.append(f"- {n}")
    else:
        out.append("- Không có.")
    out.append("")

    out.append("## 4. Warnings")
    if warnings:
        for n in warnings:
            out.append(f"- {n}")
    else:
        out.append("- Không có.")
    out.append("")

    out.append("## 5. Required checks")
    for name, hits in required_checks.items():
        out.append(f"### {name}: {'PASS' if not hits else 'FAIL'}")
        if hits:
            for h in hits:
                out.append(f"- {h}")
        else:
            out.append("- CLEAN")
        out.append("")

    out.append("## 6. PHP lint")
    if lint_results:
        for rel, rc, msg in lint_results:
            out.append(f"### `{rel}` — {'PASS' if rc == 0 else 'FAIL'}")
            out.append("```text")
            out.append(msg)
            out.append("```")
    else:
        out.append("- Không có PHP file thay đổi để lint.")
    out.append("")

    out.append("## 7. Artisan about")
    out.append(f"**Exit code:** {about.returncode}")
    out.append("```text")
    out.extend((about.stdout + about.stderr).splitlines())
    out.append("```")
    out.append("")

    out.append("## 8. Artisan route:list")
    out.append(f"**Exit code:** {routes.returncode}")
    out.append("```text")
    out.extend((routes.stdout + routes.stderr).splitlines())
    out.append("```")
    out.append("")

    out.append("## 9. Legacy scan after fix")
    for pattern, hits in scan.items():
        out.append(f"### `{pattern}` — {len(hits)} hit(s)")
        if hits:
            for h in hits[:100]:
                out.append(f"- `{h}`")
            if len(hits) > 100:
                out.append(f"- ... còn {len(hits)-100} hit(s)")
        else:
            out.append("- No matches")
        out.append("")

    out.append("## 10. Git status")
    out.append("```text")
    out.extend((status.stdout + status.stderr).splitlines())
    out.append("```")
    out.append("")

    out.append("## 11. Git diff stat")
    out.append("```text")
    out.extend((diff_stat.stdout + diff_stat.stderr).splitlines())
    out.append("```")
    out.append("")

    out.append("## 12. Full git diff")
    out.append("```diff")
    out.extend((diff.stdout + diff.stderr).splitlines())
    out.append("```")
    out.append("")

    report_path.write_text("\n".join(out), encoding="utf-8")

    print("=" * 70)
    print("MINDHUB GROUP 1 FIX COMPLETED")
    print("=" * 70)
    print("Result :", "PASS" if overall_pass else "CHUA PASS")
    print("Report :", report_path)
    print("Backup :", backup_root)
    print("=" * 70)

    return 0 if overall_pass else 2

if __name__ == "__main__":
    raise SystemExit(main())
