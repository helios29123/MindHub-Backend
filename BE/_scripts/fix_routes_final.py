#!/usr/bin/env python3
# -*- coding: utf-8 -*-

"""
MindHub Backend - Remove 2 stale routes only

1) routes/api/admin.php
   Remove:
   Route::post('/categories/{id}/restore', [AdminCategoryController::class, 'restore'])
       ->whereNumber('id');

2) routes/api/course.php
   Remove:
   Route::post('/courses/{courseId}/view', [CoursePublicController::class, 'recordView'])->where('courseId', '[0-9]+');

Then:
- backup both files before editing
- php -l both files
- php artisan route:list
- verify both stale routes are gone
- write markdown report
- no commit / no push
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
        cmd,
        cwd=str(cwd),
        capture_output=True,
        text=True,
        encoding="utf-8",
        errors="replace",
        shell=False,
    )

def find_root():
    here = Path.cwd().resolve()
    for p in [here, here / "BE", here.parent / "BE"]:
        if (p / "artisan").exists() and (p / "routes").is_dir():
            return p
    raise SystemExit("Không tìm thấy Laravel app root. Hãy chạy trong thư mục BE.")

def read_text(p: Path) -> str:
    return p.read_text(encoding="utf-8")

def write_text(p: Path, s: str):
    p.write_text(s, encoding="utf-8", newline="")

def backup_file(src: Path, root: Path, backup_root: Path):
    rel = src.resolve().relative_to(root.resolve())
    dst = backup_root / rel
    dst.parent.mkdir(parents=True, exist_ok=True)
    shutil.copy2(src, dst)

def main():
    root = find_root()
    stamp = dt.datetime.now().strftime("%Y%m%d_%H%M%S")
    audit_dir = root / "_audit"
    audit_dir.mkdir(exist_ok=True)
    backup_root = audit_dir / f"backup_routes_fix_{stamp}"
    backup_root.mkdir(parents=True, exist_ok=True)
    report = audit_dir / f"ROUTES_FIX_REPORT_{stamp}.md"

    admin_route = root / "routes/api/admin.php"
    course_route = root / "routes/api/course.php"

    changed = []
    errors = []
    notes = []

    if not admin_route.exists():
        errors.append("Không tìm thấy routes/api/admin.php")
    if not course_route.exists():
        errors.append("Không tìm thấy routes/api/course.php")

    # -----------------------------
    # 1. Remove Category restore
    # -----------------------------
    if admin_route.exists():
        text = read_text(admin_route)
        original = text

        pattern = re.compile(
            r"""(?mx)
^[ \t]*Route::post\(
    ['"]/categories/\{id\}/restore['"],
    [ \t]*\[AdminCategoryController::class,[ \t]*['"]restore['"]\]
\)
[ \t]*\r?\n
[ \t]*->whereNumber\(['"]id['"]\);
[ \t]*\r?\n?
"""
        )

        text, count = pattern.subn("", text)

        if count == 0:
            errors.append("Không match được route Category restore trong routes/api/admin.php")
        else:
            backup_file(admin_route, root, backup_root)
            write_text(admin_route, text)
            changed.append(admin_route)
            notes.append(f"Đã xóa Category restore route ({count} match).")

    # -----------------------------
    # 2. Remove Course view route
    # -----------------------------
    if course_route.exists():
        text = read_text(course_route)
        original = text

        exact_line = "Route::post('/courses/{courseId}/view', [CoursePublicController::class, 'recordView'])->where('courseId', '[0-9]+');"
        count = text.count(exact_line)

        if count == 0:
            # fallback regex only for this exact route signature
            pattern = re.compile(
                r"""(?m)^[ \t]*Route::post\(
['"]/courses/\{courseId\}/view['"],
[ \t]*\[CoursePublicController::class,[ \t]*['"]recordView['"]\]
\)
->where\(['"]courseId['"],[ \t]*['"]\[0-9\]\+['"]\);
[ \t]*\r?\n?""".replace("\n", r"\s*")
            )
            text, count = pattern.subn("", text)
        else:
            text = text.replace(exact_line + "\r\n", "", count)
            text = text.replace(exact_line + "\n", "", count)
            text = text.replace(exact_line, "", count)

        if count == 0:
            errors.append("Không match được Course view route trong routes/api/course.php")
        else:
            backup_file(course_route, root, backup_root)
            write_text(course_route, text)
            changed.append(course_route)
            notes.append(f"Đã xóa Course view route ({count} match).")

    # -----------------------------
    # Verification
    # -----------------------------
    lint_results = []
    for p in [admin_route, course_route]:
        if p.exists():
            cp = run(["php", "-l", str(p)], root)
            lint_results.append((p.relative_to(root).as_posix(), cp.returncode, (cp.stdout + cp.stderr).strip()))

    route_list = run(["php", "artisan", "route:list"], root)
    route_output = route_list.stdout + route_list.stderr

    category_restore_present = bool(re.search(r"categories/\{id\}/restore", route_output, re.I))
    course_view_present = bool(re.search(r"courses/\{courseId\}/view", route_output, re.I))

    admin_text = read_text(admin_route) if admin_route.exists() else ""
    course_text = read_text(course_route) if course_route.exists() else ""

    source_checks = {
        "admin.php còn restore route": "categories/{id}/restore" in admin_text or "AdminCategoryController::class, 'restore'" in admin_text,
        "course.php còn view route": "courses/{courseId}/view" in course_text or "CoursePublicController::class, 'recordView'" in course_text,
    }

    lint_ok = all(rc == 0 for _, rc, _ in lint_results)
    routes_ok = route_list.returncode == 0
    source_ok = not any(source_checks.values())
    route_verify_ok = not category_restore_present and not course_view_present
    overall = not errors and lint_ok and routes_ok and source_ok and route_verify_ok

    git_status = run(["git", "status", "--short"], root)
    git_diff = run(["git", "diff", "--", "routes/api/admin.php", "routes/api/course.php"], root)

    # -----------------------------
    # Report
    # -----------------------------
    out = []
    out += [
        "# ROUTES FIX REPORT",
        "",
        f"**Generated:** {dt.datetime.now().isoformat(timespec='seconds')}",
        f"**Result:** {'PASS' if overall else 'CHƯA PASS'}",
        f"**Backup:** `{backup_root}`",
        "",
        "## 1. Changes",
    ]
    out += [f"- {n}" for n in notes] or ["- Không có thay đổi."]
    out += ["", "## 2. Errors"]
    out += [f"- {e}" for e in errors] or ["- Không có."]

    out += ["", "## 3. Source verification"]
    for k, bad in source_checks.items():
        out.append(f"- {k}: {'FAIL' if bad else 'PASS'}")

    out += ["", "## 4. Route list verification"]
    out.append(f"- Category restore route còn tồn tại: {'YES' if category_restore_present else 'NO'}")
    out.append(f"- Course view route còn tồn tại: {'YES' if course_view_present else 'NO'}")
    out.append(f"- php artisan route:list exit code: {route_list.returncode}")

    out += ["", "## 5. PHP lint"]
    for rel, rc, msg in lint_results:
        out += [f"### `{rel}` — {'PASS' if rc == 0 else 'FAIL'}", "```text", msg, "```"]

    out += ["", "## 6. Matching routes after fix", "```text"]
    matched = [
        line for line in route_output.splitlines()
        if re.search(r"categories/\{id\}/restore|courses/\{courseId\}/view", line, re.I)
    ]
    out += matched if matched else ["No stale routes found."]
    out += ["```"]

    out += ["", "## 7. git status", "```text"]
    out += (git_status.stdout + git_status.stderr).splitlines()
    out += ["```"]

    out += ["", "## 8. git diff (2 route files only)", "```diff"]
    out += (git_diff.stdout + git_diff.stderr).splitlines()
    out += ["```"]

    report.write_text("\n".join(out), encoding="utf-8")

    print("=" * 70)
    print("ROUTES FIX")
    print("=" * 70)
    print("Result :", "PASS" if overall else "CHUA PASS")
    print("Report :", report)
    print("Backup :", backup_root)
    print("=" * 70)

    return 0 if overall else 2

if __name__ == "__main__":
    sys.exit(main())
