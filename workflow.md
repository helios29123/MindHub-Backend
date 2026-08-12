# Quy trình làm việc và Hướng dẫn dự án (workflow.md)

Tài liệu này ghi nhận quy trình làm việc (Git Workflow, Code Conventions), cách khởi động dự án từ đầu, và cấu trúc thư mục chuẩn đã đồng nhất cho đồ án tốt nghiệp E-learning Backend.

---

## 1. Quy trình làm việc (Workflow & Conventions)

### 1.1. Quy trình Git (Git Workflow)
- **Nhánh chính**: Nhánh `develop` được dùng làm nhánh phát triển chính.
- **Quy trình cập nhật**:
  1. Kiểm tra trạng thái hiện tại: `git status`
  2. Lấy code mới nhất: `git pull origin develop`
  3. Sau khi code và chạy test thành công, stage các thay đổi: `git add .`
  4. Commit với thông điệp rõ ràng theo định dạng Conventional Commits:
     ```bash
     git commit -m "feat(learning): LEARN-09 - Xem lịch sử học tập"
     git commit -m "fix(auth): xử lý lỗi xác thực token"
     git commit -m "chore(project): refactor cấu trúc thư mục"
     ```

### 1.2. Nhật ký phát triển (Agent Memory - `ai-memory`)
- Mỗi khi hoàn thành một chức năng hoặc một đợt cập nhật quan trọng, bắt buộc tạo một file ghi nhớ lưu tại thư mục `.agent/ai-memory/`.
- Định dạng đặt tên file: `YYYY-MM-DD-FEATURE-ID.md` (Ví dụ: `2026-06-13-LEARN-09.md`).
- Nội dung file bao gồm:
  1. Tóm tắt nhiệm vụ.
  2. Quyết định kỹ thuật & Thiết kế.
  3. Các file đã chỉnh sửa & tạo mới (kèm link).
  4. Trạng thái & Việc còn dở.

### 1.3. Nguyên tắc Cơ sở dữ liệu
- Chỉ sử dụng các bảng, cột và trạng thái (status) đã quy định sẵn trong ERD Giai đoạn 1.
- Tuyệt đối không tự tạo bảng trung gian hay thêm trường ngoài schema khi chưa được phê duyệt.
- Ưu tiên tính toán động dữ liệu từ các bảng hiện có thay vì sinh thêm bảng lưu cache/report mới.

### 1.4. Quy trình sử dụng CodeGraph để đọc Codebase
Để tránh việc tự giả định cấu trúc code, tất cả AI Agent và nhà phát triển bắt buộc sử dụng CodeGraph khi tiếp cận mã nguồn:
- **Đọc hiểu trước khi code**: Sử dụng các công cụ tìm kiếm của `codegraph` (`codegraph_search`, `codegraph_context`, `codegraph_callers`, v.v.) để tìm hiểu cấu trúc lớp (classes), phương thức (methods) và luồng gọi trước khi sửa đổi codebase.
- **Cập nhật index CodeGraph**: Sau khi pull code hoặc thay đổi cấu trúc tệp lớn, cần đồng bộ lại index CodeGraph bằng cách chạy lệnh:
  ```powershell
  codegraph index [path_to_project]
  ```

---

## 2. Hướng dẫn khởi động dự án (Project Startup Guide)

### 2.1. Yêu cầu môi trường (Prerequisites)
- **Hệ điều hành**: Windows (Laragon được khuyến khích).
- **PHP**: Phiên bản `8.3.x` (đường dẫn thực tế: `d:\laragon\bin\php\php-8.3.16-Win32-vs16-x64\php.exe`).
- **MySQL**: Phiên bản `8.x` hoặc MariaDB tương thích.
- **Composer**: Quản lý gói thư viện PHP.

### 2.2. Các bước cài đặt & Khởi động
1. **Cài đặt thư viện**:
   Di chuyển vào thư mục `BE/` và cài đặt các package PHP:
   ```bash
   composer install
   ```
2. **Thiết lập file cấu hình môi trường**:
   Sao chép file `.env.example` thành `.env` và cấu hình các thông số kết nối Database (DB_DATABASE, DB_USERNAME, DB_PASSWORD).
3. **Tạo Key ứng dụng**:
   ```bash
   php artisan key:generate
   ```
4. **Import Database & Seed dữ liệu**:
   - Import cấu trúc database gốc từ file SQL: `d:\laragon\www\MindHub\elearning_erd_gd1 (1).sql`.
   - Nạp dữ liệu mẫu chạy test API từ file SQL: `d:\laragon\www\MindHub\elearning_gd1_seed_api_test (1).sql`.
   - Cập nhật mật khẩu test (mặc định dùng chung `12345678` mã hóa bcrypt) cho toàn bộ tài khoản test trong bảng `users` để hỗ trợ kiểm thử API.
5. **Cập nhật Autoload & Xóa Cache**:
   Mỗi khi thay đổi cấu trúc tệp tin hoặc namespace:
   ```bash
   # Cập nhật danh sách classmap
   composer dump-autoload
   
   # Xóa cache config và route của Laravel
   php artisan route:clear
   php artisan config:clear
   ```
6. **Khởi chạy bộ kiểm thử (Pest Testing)**:
   Để đảm bảo toàn bộ hệ thống hoạt động ổn định và không phát sinh lỗi hồi quy:
   ```bash
   # Chạy toàn bộ test suite
   vendor/bin/pest
   
   # Chạy riêng một file test cụ thể
   vendor/bin/pest tests/Feature/Learning/LearningLogsTest.php
   ```

---

## 3. Quy ước cấu trúc thư mục (Directory Structure)

Dự án được cấu trúc theo mô hình phân tách Module rõ ràng, đảm bảo tính nhất quán và dễ mở rộng.

```txt
app/
├── Http/
│   ├── Controllers/
│   │   ├── Controller.php
│   │   ├── AuthController.php            <-- Các controller đặt TRỰC TIẾP tại đây, không chia thư mục con
│   │   ├── UserProfileController.php
│   │   ├── LearningController.php
│   │   └── ...
│   │
│   ├── Requests/                         <-- Request Validation chia theo Module con
│   │   ├── Auth/
│   │   ├── User/
│   │   ├── Learning/
│   │   └── ...
│   │
│   └── Resources/                        <-- API Response Resources chia theo Module con
│       ├── Auth/
│       ├── User/
│       ├── Learning/
│       └── ...
│
├── Services/                             <-- Business Logic layers chia theo Module con
│   ├── Auth/
│   ├── User/
│   ├── Learning/
│   └── ...
│
├── Repositories/                         <-- Data Access layers chia theo Module con
│   ├── Auth/
│   ├── User/
│   ├── Learning/
│   └── ...
│
├── Models/                               <-- Chỉ chứa các Eloquent Models
│   └── ...
│
└── Policies/                             <-- Phân quyền truy cập tài nguyên (CoursePolicy, LessonPolicy...)
    └── ...

routes/
├── api.php                               <-- Chỉ dùng để require các file route con
└── api/                                  <-- Danh sách các file route tương ứng với từng module
    ├── auth.php
    ├── user.php
    ├── learning.php
    └── ...
```

### Quy tắc cốt lõi:
1. **Không sử dụng** mô hình `app/Modules/...` tự chế để tránh làm dài namespace phức tạp.
2. **Không chia thư mục con** trong `app/Http/Controllers/`. Toàn bộ Controller phải đặt trực tiếp ở thư mục Controller gốc.
3. Các layer **Request, Resource, Service, Repository** bắt buộc phải chia thư mục con theo module để dễ dàng quản lý mã nguồn.
4. Route API phải được viết trong file module con riêng biệt tại `routes/api/*.php` và được require tập trung tại `routes/api.php`.
5. Models chỉ được tạo khi thực tế có bảng tương ứng trong database.
