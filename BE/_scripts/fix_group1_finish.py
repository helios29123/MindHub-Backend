#!/usr/bin/env python3
# -*- coding: utf-8 -*-

"""
MindHub Backend - Finish Group 1 blockers
- Remove Category restore route
- Remove Course view endpoint + controller method
- Delete CourseViewService only if no call sites remain
- Run php -l, artisan about, route:list, targeted scans
- Write a Markdown report
- No commit / no push
"""

from __future__ import annotations
import datetime as dt
import re
import shutil
import subprocess
import sys
from pathlib import Path

def run(cmd, cwd):
    return subprocess.run(
        cmd, cwd=str(cwd), capture_output=True, text=True,
        encoding="utf-8", errors="replace", shell=False
    )

def app_root():
    here = Path.cwd().resolve()
    for p in [here, here / "BE", here.parent / "BE"]:
        if (p / "artisan").exists() and (p / "app").is_dir():
            return p
    raise SystemExit("Không tìm thấy Laravel app root. Hãy chạy trong thư mục BE.")

def read(p): return p.read_text(encoding="utf-8")
def write(p, s): p.write_text(s, encoding="utf-8", newline="")

def backup(p, root, backup_root):
    rel = p.resolve().relative_to(root.resolve())
    dst = backup_root / rel
    dst.parent.mkdir(parents=True, exist_ok=True)
    shutil.copy2(p, dst)

def remove_php_method(text: str, method: str):
    pat = re.compile(
        rf"(?m)^[ \t]*(?:public|protected|private)?[ \t]*(?:static[ \t]+)?function[ \t]+{re.escape(method)}[ \t]*\("
    )
    m = pat.search(text)
    if not m:
        return text, False

    start = m.start()
    brace = text.find("{", m.end())
    if brace < 0:
        return text, False

    depth = 0
    i = brace
    in_s = in_d = esc = False
    while i < len(text):
        ch = text[i]
        if esc:
            esc = False
        elif ch == "\\" and (in_s or in_d):
            esc = True
        elif ch == "'" and not in_d:
            in_s = not in_s
        elif ch == '"' and not in_s:
            in_d = not in_d
        elif not in_s and not in_d:
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

def search_refs(root: Path, needles, excludes=()):
    excludes = {p.resolve() for p in excludes}
    hits = []
    for base in [root / "app", root / "routes"]:
        if not base.exists():
            continue
        for p in base.rglob("*.php"):
            if p.resolve() in excludes:
                continue
            try:
                lines = read(p).splitlines()
            except Exception:
                continue
            for i, line in enumerate(lines, 1):
                if any(n in line for n in needles):
                    hits.append(f"{p.relative_to(root)}:{i}: {line.strip()}")
    return hits

def main():
    root = app_root()
    stamp = dt.datetime.now().strftime("%Y%m%d_%H%M%S")
    audit = root / "_audit"
    audit.mkdir(exist_ok=True)
    backup_root = audit / f"backup_group1_finish_{stamp}"
    backup_root.mkdir(parents=True, exist_ok=True)
    report = audit / f"GROUP1_FINISH_REPORT_{stamp}.md"

    changed = []
    blocked = []
    notes = []

    def save_change(path, new_text, note):
        old = read(path)
        if old == new_text:
            return
        backup(path, root, backup_root)
        write(path, new_text)
        changed.append(path)
        notes.append(note)

    # 1) Remove Category restore route only
    for route_file in (root / "routes").glob("*.php"):
        text = read(route_file)
        lines = text.splitlines(True)
        kept = []
        removed = []
        for line in lines:
            normalized = line.lower().replace(" ", "")
            is_category_restore = (
                "categories" in normalized
                and "restore" in normalized
                and (
                    "admincategorycontroller" in normalized
                    or "restore" in normalized
                )
            )
            if is_category_restore:
                removed.append(line.rstrip())
                continue
            kept.append(line)

        if removed:
            save_change(
                route_file,
                "".join(kept),
                f"Removed Category restore route from {route_file.relative_to(root)}"
            )

    # 2) Remove CoursePublicController::recordView
    controllers = list((root / "app/Http/Controllers").rglob("CoursePublicController.php"))
    if not controllers:
        blocked.append("Không tìm thấy CoursePublicController.php")
    else:
        for controller in controllers:
            text = read(controller)
            new_text, removed = remove_php_method(text, "recordView")
            if removed:
                save_change(
                    controller,
                    new_text,
                    f"Removed CoursePublicController::recordView from {controller.relative_to(root)}"
                )

    # 3) Remove course view route
    for route_file in (root / "routes").glob("*.php"):
        text = read(route_file)
        lines = text.splitlines(True)
        kept = []
        removed = []
        for line in lines:
            normalized = line.lower().replace(" ", "")
            is_course_view_route = (
                "coursepubliccontroller" in normalized
                and "recordview" in normalized
            ) or (
                "courses/" in normalized
                and "/view" in normalized
                and "recordview" in normalized
            )
            if is_course_view_route:
                removed.append(line.rstrip())
                continue
            kept.append(line)

        if removed:
            save_change(
                route_file,
                "".join(kept),
                f"Removed course view route from {route_file.relative_to(root)}"
            )

    # 4) Delete CourseViewService only when it is truly unused
    cvs = root / "app/Services/Course/CourseViewService.php"
    if cvs.exists():
        refs = search_refs(root, ["CourseViewService", "recordView("], excludes=[cvs])
        if refs:
            blocked.append("CourseViewService vẫn còn call site: " + " | ".join(refs[:10]))
        else:
            backup(cvs, root, backup_root)
            cvs.unlink()
            changed.append(cvs)
            notes.append("Deleted unused legacy app/Services/Course/CourseViewService.php")

    # Verification
    lint = []
    for p in changed:
        if p.suffix.lower() == ".php" and p.exists():
            cp = run(["php", "-l", str(p)], root)
            lint.append((str(p.relative_to(root)), cp.returncode, (cp.stdout + cp.stderr).strip()))

    about = run(["php", "artisan", "about"], root)
    routes = run(["php", "artisan", "route:list"], root)

    route_output = routes.stdout + routes.stderr

    targeted = {
        "Category restore route": bool(re.search(r"categories.*restore", route_output, re.I)),
        "Course view route": bool(re.search(r"courses.*view.*CoursePublicController@recordView", route_output, re.I)),
        "CoursePublicController recordView": bool(search_refs(root, ["function recordView("])),
        "CourseViewService reference": bool(search_refs(root, ["CourseViewService"])),
    }

    # Existing Group 1 targeted leftovers from previous audit
    legacy_patterns = {
        "InstructorCourseService legacy": (
            root / "app/Services/Instructor/InstructorCourseService.php",
            ["deleted_at", "approved_at", "rejected_reason", "total_duration_seconds"]
        ),
        "InstructorCourseRepository Quiz placeholders": (
            root / "app/Repositories/Instructor/InstructorCourseRepository.php",
            ["getChecklistQuizzes", "getChecklistQuizQuestionStats"]
        ),
        "Category fake SoftDelete API": (
            None,
            ["findWithTrashed", "findOnlyTrashed", "function restore("]
        ),
        "Moderation deleted_at": (
            root / "app/Services/Moderation/ModerationService.php",
            ["deleted_at"]
        ),
    }

    legacy_results = {}
    for name, (path, pats) in legacy_patterns.items():
        hits = []
        if path:
            if path.exists():
                for i, line in enumerate(read(path).splitlines(), 1):
                    if any(p in line for p in pats):
                        hits.append(f"{path.relative_to(root)}:{i}: {line.strip()}")
        else:
            for p in [
                root / "app/Repositories/Admin/AdminCategoryRepository.php",
                root / "app/Services/Admin/AdminCategoryService.php",
            ]:
                if p.exists():
                    for i, line in enumerate(read(p).splitlines(), 1):
                        if any(x in line for x in pats):
                            hits.append(f"{p.relative_to(root)}:{i}: {line.strip()}")
        legacy_results[name] = hits

    lint_ok = all(rc == 0 for _, rc, _ in lint)
    artisan_ok = about.returncode == 0 and routes.returncode == 0
    targeted_ok = not any(targeted.values())
    old_group1_ok = all(not v for v in legacy_results.values())
    overall = lint_ok and artisan_ok and targeted_ok and old_group1_ok and not blocked

    git_status = run(["git", "status", "--short"], root)
    git_diff_stat = run(["git", "diff", "--stat"], root)
    git_diff = run(["git", "diff", "--no-ext-diff", "--unified=3"], root)

    out = []
    out += [
        "# GROUP 1 FINISH REPORT",
        "",
        f"**Generated:** {dt.datetime.now().isoformat(timespec='seconds')}",
        f"**Result:** {'PASS' if overall else 'CHƯA PASS'}",
        f"**Backup:** `{backup_root}`",
        "",
        "## 1. What this script changed",
    ]
    out += [f"- {x}" for x in notes] or ["- Không có thay đổi."]
    out += ["", "## 2. Blocked"]
    out += [f"- {x}" for x in blocked] or ["- Không có."]
    out += ["", "## 3. Targeted verification"]
    for k, bad in targeted.items():
        out.append(f"- {k}: {'FAIL - còn tồn tại' if bad else 'PASS'}")

    out += ["", "## 4. Previous Group 1 leftovers"]
    for k, hits in legacy_results.items():
        out.append(f"### {k}: {'PASS' if not hits else 'FAIL'}")
        out += [f"- `{h}`" for h in hits] or ["- CLEAN"]

    out += ["", "## 5. PHP lint"]
    for rel, rc, msg in lint:
        out += [f"### `{rel}` — {'PASS' if rc == 0 else 'FAIL'}", "```text", msg, "```"]
    if not lint:
        out.append("- Không có file PHP hiện hữu nào cần lint.")

    out += ["", "## 6. php artisan about", f"Exit code: {about.returncode}", "```text"]
    out += (about.stdout + about.stderr).splitlines()
    out += ["```", "", "## 7. php artisan route:list", f"Exit code: {routes.returncode}", "```text"]
    out += route_output.splitlines()
    out += ["```", "", "## 8. git status", "```text"]
    out += (git_status.stdout + git_status.stderr).splitlines()
    out += ["```", "", "## 9. git diff --stat", "```text"]
    out += (git_diff_stat.stdout + git_diff_stat.stderr).splitlines()
    out += ["```", "", "## 10. full git diff", "```diff"]
    out += (git_diff.stdout + git_diff.stderr).splitlines()
    out += ["```"]

    report.write_text("\n".join(out), encoding="utf-8")

    print("=" * 70)
    print("GROUP 1 FINISH")
    print("=" * 70)
    print("Result :", "PASS" if overall else "CHUA PASS")
    print("Report :", report)
    print("Backup :", backup_root)
    print("=" * 70)

    return 0 if overall else 2

if __name__ == "__main__":
    sys.exit(main())
