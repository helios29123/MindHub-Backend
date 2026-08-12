# SWAGGER-LOCAL: Cấu hình tải Swagger UI cục bộ (Local Assets, Offline spec & Sync endpoints)

## 1. Tóm tắt nhiệm vụ
- Liên kết Swagger UI (`api-docs.html`) và tài liệu OpenAPI (`openapi.yaml`) cục bộ, hỗ trợ offline hoàn toàn.
- Cập nhật danh sách máy chủ API (bao gồm máy chủ staging/production `62.171.157.22`).
- Rà soát, đối chiếu các API thực tế của dự án với đặc tả `openapi.yaml`, loại bỏ API thừa không sử dụng và bổ sung các API hồ sơ giảng viên bị thiếu.

## 2. Quyết định kỹ thuật & Thiết kế
- **Cập nhật danh sách máy chủ**:
  - Thêm máy chủ production vào danh sách máy chủ thử nghiệm: `http://62.171.157.22/api`.
- **Rà soát & Đồng bộ các API (Reconciliation)**:
  - Sử dụng CodeGraph và đối chiếu danh sách route thực tế (`routes.json` sinh từ `route:list`) với `openapi.yaml`.
  - **Loại bỏ API thừa**:
    - Xóa `PATCH /instructor/profile` (tuyến đường cập nhật chung không tồn tại trong backend thực tế).
  - **Bổ sung các API thiếu (Instructor Profile)**:
    - `PATCH /instructor/profile/account` (Cập nhật full_name).
    - `PATCH /instructor/profile/expertise` (Cập nhật chuyên môn, kinh nghiệm, cấp độ).
    - `PATCH /instructor/profile/introduction` (Cập nhật bio).
    - `GET /instructor/profile/completion` (Lấy trạng thái hoàn thiện hồ sơ).
- **Hỗ trợ chạy trực tiếp trên file://**:
  - Chuyển đổi `openapi.yaml` đã sửa đổi sang `openapi-spec.js` chứa biến toàn cục `window.swaggerSpec` để tránh lỗi CORS khi chạy trực tiếp qua `file://` trên trình duyệt.

## 3. Các file đã chỉnh sửa & tạo mới
- **Tài liệu API (Chỉnh sửa)**:
  - [openapi.yaml](file:///d:/laragon/www/MindHub/MindHub-Backend/BE/public/openapi.yaml) (Bổ sung server `62.171.157.22`, xóa `PATCH /instructor/profile`, thêm các API `/profile/...` mới).
  - [api-docs.html](file:///d:/laragon/www/MindHub/MindHub-Backend/BE/public/api-docs.html) (Liên kết assets cục bộ).
- **Tệp Spec JS (Tự động sinh lại)**:
  - [openapi-spec.js](file:///d:/laragon/www/MindHub/MindHub-Backend/BE/public/swagger-ui/openapi-spec.js)

## 4. Trạng thái & Việc còn dở
- **Trạng thái**: Hoàn thành 100%. Các API hồ sơ giảng viên và thông tin server ảo đã được đồng bộ chính xác.
- **Việc dở dang**: Vẫn còn một số endpoint nâng cao khác chưa được bổ sung hết vào spec (đang tiếp tục đối chiếu ở các phase sau nếu cần).
