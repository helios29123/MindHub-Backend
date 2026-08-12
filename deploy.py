# -*- coding: utf-8 -*-
"""
MindHub Backend deployer (FTP) — đồng bộ mã nguồn Laravel (BE/) lên host.

Chỉ cần điền FTP_* vào .env.deploy (cùng thư mục) rồi chạy:
    py deploy.py                 # upload toàn bộ BE/ (trừ vendor, storage, .env...)
    py deploy.py --dry-run       # chỉ liệt kê file sẽ upload, không gửi
    py deploy.py app routes      # chỉ upload các thư mục con này của BE/

.env.deploy cần:
    FTP_HOST=62.171.157.22
    FTP_USER=xam
    FTP_PASS=xxxxxxxx
    FTP_ROOT=default             # web root; backend nằm ở <FTP_ROOT>/BE
    # FTP_PORT=21

KHÔNG đụng tới trên host: vendor/, storage/ (log, cache, private media),
.env (server giữ .env riêng), bootstrap/cache/, .git. Vì thế deploy an toàn,
không phá dữ liệu/biến môi trường production.
"""
import os
import sys
import ftplib

try:
    sys.stdout.reconfigure(encoding="utf-8")
except Exception:
    pass

HERE = os.path.dirname(os.path.abspath(__file__))
SRC = os.path.join(HERE, "BE")  # mã nguồn Laravel

# Thư mục/đuôi KHÔNG upload (đường dẫn tương đối trong BE/, dùng '/')
EXCLUDE_DIRS = {
    "vendor", "node_modules", ".git", ".idea", ".vscode",
    "storage", "bootstrap/cache", "tests", "public/storage",
}
EXCLUDE_EXT = {".log", ".md"}
EXCLUDE_NAMES = {".env", ".env.example", ".env.deploy", "cookies.txt"}


def load_env():
    cfg = {}
    for fname in (".env.deploy", ".env"):
        path = os.path.join(HERE, fname)
        if not os.path.exists(path):
            continue
        for line in open(path, encoding="utf-8"):
            line = line.strip()
            if not line or line.startswith("#") or "=" not in line:
                continue
            k, v = line.split("=", 1)
            cfg.setdefault(k.strip(), v.strip().strip('"').strip("'"))
    missing = [k for k in ("FTP_HOST", "FTP_USER", "FTP_PASS") if not cfg.get(k)]
    if missing:
        sys.exit(f"[!] Thiếu {', '.join(missing)} trong .env.deploy")
    return cfg


def is_excluded(rel):
    rel = rel.replace("\\", "/")
    for d in EXCLUDE_DIRS:
        if rel == d or rel.startswith(d + "/"):
            return True
    name = os.path.basename(rel)
    if name in EXCLUDE_NAMES:
        return True
    if name.startswith(".env."):
        return True
    return os.path.splitext(name)[1].lower() in EXCLUDE_EXT


def gather(subdirs):
    """Trả về danh sách (local_abs, rel_path) cần upload."""
    files = []
    roots = [os.path.join(SRC, s) for s in subdirs] if subdirs else [SRC]
    for root in roots:
        for dirpath, dirnames, filenames in os.walk(root):
            rel_dir = os.path.relpath(dirpath, SRC).replace("\\", "/")
            rel_dir = "" if rel_dir == "." else rel_dir
            # cắt nhánh bị loại để khỏi đi sâu
            dirnames[:] = [d for d in dirnames if not is_excluded((rel_dir + "/" + d).lstrip("/"))]
            for fn in filenames:
                rel = (rel_dir + "/" + fn).lstrip("/")
                if not is_excluded(rel):
                    files.append((os.path.join(dirpath, fn), rel))
    return files


def connect(cfg):
    ftp = ftplib.FTP()
    ftp.connect(cfg["FTP_HOST"], int(cfg.get("FTP_PORT", 21)), timeout=30)
    ftp.login(cfg["FTP_USER"], cfg["FTP_PASS"])
    ftp.set_pasv(True)
    return ftp


def ensure_cwd(ftp, path):
    ftp.cwd("/")
    for part in [p for p in path.split("/") if p]:
        try:
            ftp.cwd(part)
        except ftplib.error_perm:
            ftp.mkd(part)
            ftp.cwd(part)


def main():
    args = [a for a in sys.argv[1:] if not a.startswith("--")]
    dry = "--dry-run" in sys.argv
    if not os.path.isdir(SRC):
        sys.exit(f"[!] Không thấy thư mục mã nguồn BE/ tại {SRC}")

    files = gather(args)
    print(f"[*] {len(files)} file sẽ upload từ BE/{('/'.join(args)) if args else ''}")
    if dry:
        for _, rel in files[:200]:
            print("   ", rel)
        if len(files) > 200:
            print(f"    ... và {len(files) - 200} file nữa")
        print("[i] --dry-run: không gửi gì.")
        return

    cfg = load_env()
    remote_base = (cfg.get("FTP_ROOT", "").strip("/") + "/BE").strip("/")
    ftp = connect(cfg)
    ensure_cwd(ftp, remote_base)   # đảm bảo <root>/BE tồn tại
    print(f"[+] Deploy vào: /{remote_base}")

    made = set()
    def ensure_remote_dir(rel_dir):
        if not rel_dir or rel_dir in made:
            return
        cur = ""
        for part in rel_dir.split("/"):
            cur = f"{cur}/{part}" if cur else part
            if cur in made:
                continue
            try:
                ftp.mkd(f"/{remote_base}/{cur}")
            except ftplib.error_perm:
                pass
            made.add(cur)

    ok = 0
    for local, rel in files:
        ensure_remote_dir(os.path.dirname(rel))
        try:
            with open(local, "rb") as f:
                ftp.storbinary(f"STOR /{remote_base}/{rel}", f)
            ok += 1
            if ok % 25 == 0:
                print(f"    ... {ok}/{len(files)}")
        except Exception as e:
            print(f"    [x] {rel}: {e}")
    ftp.quit()
    print(f"[OK] Backend deploy xong: {ok}/{len(files)} file len /{remote_base}")
    print("    (Config/route không cache nên có hiệu lực ngay.)")


if __name__ == "__main__":
    main()
