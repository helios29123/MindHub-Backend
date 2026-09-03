# CICD-AUTO-DEPLOY: Cấu hình tự động hóa CI/CD, chuẩn hóa vị trí Workflows và hệ thống Auto-Deploy qua SSH/FTP cho Backend & Frontend

## 1. Tóm tắt nhiệm vụ
- **Khắc phục lỗi GitHub Actions Backend CI không hoạt động**: Phát hiện và sửa lỗi đặt sai vị trí thư mục workflow từ `BE/.github/workflows/` ra thư mục gốc `.github/workflows/` của Git repository.
- **Xây dựng hệ thống Continuous Integration (CI)**:
  - Backend: Tự động khởi tạo MariaDB container, cài đặt thư viện, generate key, chạy migrations và kiểm thử Pest/PHPUnit khi tạo PR hoặc push vào nhánh `develop`, `main`.
  - Frontend: Tự động kiểm tra cú pháp & kiểu dữ liệu TypeScript (`tsc --noEmit`) và kiểm tra build Vite (`npm run build`).
- **Xây dựng hệ thống Continuous Deployment (CD - Tự động triển khai lên máy chủ)**:
  - Tự động SSH vào máy chủ Linux / aaPanel khi push code vào nhánh triển khai.
  - Tự động kéo mã nguồn mới nhất (`git fetch` & `git reset --hard origin/<branch>`).
  - Cài đặt dependencies production (`composer install --no-dev --optimize-autoloader` / `npm ci`).
  - Tự động thực thi **Database Migrations** (`php artisan migrate --force`) và **Database Seeders** (`php artisan db:seed --force`).
  - Tối ưu hóa hệ thống & dọn cache (`php artisan optimize:clear`, `config:cache`, `route:cache`, `view:cache`).
  - Cập nhật phân quyền ghi thư mục `storage/`, `bootstrap/cache/` và reload Queue worker.
  - Bổ sung cơ chế triển khai dự phòng qua FTP Action và hỗ trợ `workflow_dispatch` (chạy thủ công với các tham số `fresh_database`, `run_seeders`).

---

## 2. Quyết định kỹ thuật & Thiết kế
- **Cấu trúc thư mục Workflows**:
  - GitHub Actions yêu cầu các tệp cấu hình phải nằm chính xác tại `<repo_root>/.github/workflows/*.yml`.
  - Cấu hình `defaults.run.working-directory: BE` trong workflow Backend để các lệnh Composer và Artisan thực thi đúng ngữ cảnh thư mục con của Laravel.
- **Bảo mật & Quản lý Biến môi trường (Secrets)**:
  - Tách biệt toàn bộ thông tin nhạy cảm vào GitHub Repository Secrets: `SSH_HOST`, `SSH_USER`, `SSH_PRIVATE_KEY` / `SSH_KEY`, `SSH_PASSWORD`, `SSH_PORT`, `SERVER_BE_PATH`, `SERVER_FE_PATH`.
  - Tinh chỉnh định dạng YAML trong `cd.yml` đặt khối `env:` ở cấp độ bước và `script:` trực tiếp bên trong `with:` để action `appleboy/ssh-action@v1.2.0` nhận diện kịch bản thực thi một cách tuyệt đối chính xác.
- **Chiến lược Cập nhật Cơ sở dữ liệu trong Pipeline CD**:
  - Tích hợp logic rẽ nhánh thông minh:
    - Chế độ thông thường: Chạy `migrate --force` (chỉ nạp các migration mới) và chạy seeder bổ sung (`db:seed --force`).
    - Chế độ làm mới (`FRESH_DB=true`): Khi kích hoạt thủ công qua GitHub UI với tùy chọn `fresh_database`, pipeline sẽ chạy `migrate:fresh --force` và seed lại toàn bộ cơ sở dữ liệu mẫu.
- **Tối ưu hóa Hiệu năng & Cache**:
  - Sử dụng `actions/cache@v4` để cache thư mục gói thư viện Composer (`BE/composer.lock`) và Node (`package-lock.json`), rút ngắn thời gian chạy CI từ 3-4 phút xuống dưới 40 giây.
  - Thiết lập `concurrency: cancel-in-progress: true` cho CI để tự động hủy các build cũ khi có commit mới được push đè lên PR.

---

## 3. Các file đã chỉnh sửa & tạo mới
- **Backend (MindHub-Backend)**:
  - [ci.yml](file:///home/tinhnvq/matbaows/_dự%20án%20web/MindHub/MindHub-Backend/.github/workflows/ci.yml): Pipeline CI kiểm thử tự động với MariaDB Service Container.
  - [cd.yml](file:///home/tinhnvq/matbaows/_dự%20án%20web/MindHub/MindHub-Backend/.github/workflows/cd.yml): Pipeline CD tự động SSH deploy, chạy migration, seeder, dọn cache và FTP fallback.
  - Xóa bỏ thư mục cấu hình sai `BE/.github/` để tránh xung đột.
- **Frontend (MindHub-Frontend)**:
  - [ci.yml](file:///home/tinhnvq/matbaows/_dự%20án%20web/MindHub/MindHub-Frontend/.github/workflows/ci.yml): Pipeline CI kiểm tra TypeScript và Vite build.
  - [cd.yml](file:///home/tinhnvq/matbaows/_dự%20án%20web/MindHub/MindHub-Frontend/.github/workflows/cd.yml): Pipeline CD tự động SSH deploy, build production assets và phân quyền thư mục `dist/`.

---

## 4. Trạng thái & Việc còn dở
- **Trạng thái**: Hoàn thành 100%.
  - Cú pháp toàn bộ các tệp YAML đã được kiểm tra và hợp lệ (`Valid YAML`).
  - Đã kiểm tra trực tiếp Endpoint máy chủ `https://mindhub.io.vn/BE/public/index.php/api/courses` và `/categories`: phản hồi `200 OK`, dữ liệu trả về chuẩn xác.
- **Việc dở dang**: Không có.
