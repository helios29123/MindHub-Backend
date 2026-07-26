# BÁO CÁO KẾT QUẢ KẾT NỐI API HỒ SƠ / TRUNG TÂM TÀI KHOẢN GIẢNG VIÊN

**Ngày thực hiện:** 24/07/2026  
**Dự án Frontend:** `F:\Phatnt\Documents\MindHub-Frontend`  
**Dự án Backend:** `F:\Phatnt\laragon\www\MindHub-Backend\be`  
**Trang:** `http://127.0.0.1:3000/instructor/profile` (Tabs: `?tab=profile`, `?tab=notifications`, `?tab=settings`)

---

## 1. TỔNG QUAN HẠ TẦNG VÀ KẾT QUẢ

- **Giữ nguyên 100% giao diện hiện tại:** Tất cả các thành phần UI (Breadcrumbs, Tiêu đề "Trung tâm tài khoản", 3 Tab chính, Avatar cá nhân, Form Thông tin cá nhân, Mạng xã hội, Card Thông báo hệ thống gần đây, Cài đặt nhanh Email/SMS, Trạng thái tài khoản, Lối tắt tài khoản nhận tiền) đều được giữ nguyên style, màu sắc và layout.
- **Session Authentication:** Tất cả các request Frontend gọi tới Backend đều dùng `credentials: "include"`. Thông tin giảng viên lấy trực tiếp từ Session authentication của Laravel (Session user context).
- **Không thay đổi Schema Database:** Không chạy migration hoặc sửa cột bảng dữ liệu. Metadata (avatar, social_links, notification_preferences) được mã hóa JSON và lưu trữ an toàn trong mô hình dữ liệu hiện tại.

---

## 2. DANH SÁCH API BACKEND VÀ TRẠNG THÁI INTEGRATION

| FE Function / Block | Endpoint Backend | Method | Status | Trạng thái dữ liệu |
|---|---|---|---|---|
| 1. Lấy thông tin hồ sơ | `GET /api/instructor/profile` | GET | **OK (200)** | Trả về đầy đủ thông tin giảng viên: `full_name`, `email`, `phone`, `expertise`, `bio`, `avatar_url`, `social_links`, `quick_settings`, `verification`, `account_status`, `policy_compliance`, `reputation_score`, `payout_shortcut`. |
| 2. Cập nhật hồ sơ | `PATCH /api/instructor/profile` | PATCH | **OK (200)** | Cập nhật đồng bộ `full_name`, `phone`, `expertise`, `bio`, `social_links`. |
| 3. Tải ảnh đại diện (Avatar) | `POST /api/instructor/profile/avatar` | POST | **OK (200)** | Nhận file ảnh (max 5MB, JPG/PNG/WEBP), lưu vào public storage `storage/avatars/` và trả về `avatar_url`. Cập nhật đồng bộ lên Topbar. |
| 4. Cài đặt thông báo | `GET & PATCH /api/instructor/profile/notification-preferences` | GET/PATCH | **OK (200)** | Bật/tắt trạng thái `email_notifications` và `sms_alerts`. |
| 5. Đổi mật khẩu | `PATCH /api/instructor/profile/password` | PATCH | **OK (200)** | Kiểm tra mật khẩu hiện tại, mã hóa Hash và cập nhật mật khẩu mới. |
| 6. Thông báo hệ thống | `GET /api/instructor/notifications` | GET | **OK (200)** | Trả về danh sách thông báo hệ thống gần đây của giảng viên. |

---

## 3. CÁC TẬP TIN ĐÃ CẬP NHẬT

### Backend (`be/`)
1. [app/Http/Controllers/InstructorProfileController.php](file:///F:/Phatnt/laragon/www/MindHub-Backend/be/app/Http/Controllers/InstructorProfileController.php): Thêm các method `update()`, `uploadAvatar()`, `getPreferences()`, `updatePreferences()`, `changePassword()`.
2. [app/Services/Instructor/InstructorProfileService.php](file:///F:/Phatnt/laragon/www/MindHub-Backend/be/app/Services/Instructor/InstructorProfileService.php): Bổ sung logic xử lý thông tin hồ sơ, lưu trữ JSON metadata và mã hóa mật khẩu.
3. [app/Http/Resources/Instructor/InstructorProfileResource.php](file:///F:/Phatnt/laragon/www/MindHub-Backend/be/app/Http/Resources/Instructor/InstructorProfileResource.php): Định dạng dữ liệu trả về theo phẳng và cấu trúc tương thích.
4. [routes/api/instructor.php](file:///F:/Phatnt/laragon/www/MindHub-Backend/be/routes/api/instructor.php): Đăng ký các route `/api/instructor/profile`.
5. [tests/Feature/Instructor/InstructorProfileTest.php](file:///F:/Phatnt/laragon/www/MindHub-Backend/be/tests/Feature/Instructor/InstructorProfileTest.php): Bổ sung 4 unit/integration test cases cho các endpoint mới.

### Frontend (`MindHub-Frontend/`)
1. [src/components/instructor-ui/InstructorProfilePage.tsx](file:///F:/Phatnt/Documents/MindHub-Frontend/src/components/instructor-ui/InstructorProfilePage.tsx):
   - Thay thế dữ liệu mock mặc định bằng API thật `ApiService.getInstructorProfile()`.
   - Đồng bộ query tab URL `?tab=profile|notifications|settings` với `pushState` / `popstate` khi chuyển tab hoặc nhấn nút Back/Forward.
   - Thêm nút tải ảnh ẩn `<input type="file">`, kết nối nút "Đổi ảnh" và Icon Camera với API `uploadInstructorAvatar()`, cập nhật avatar tức thì trên hồ sơ và Topbar.
   - Kết nối Form thông tin cá nhân với `updateInstructorProfile()`.
   - Kết nối Toggle Email Notifications & SMS Alerts với `updateInstructorNotificationPreferences()`.
   - Kết nối Form Đổi mật khẩu với `changeInstructorPassword()`.
   - Nối dữ liệu Thông báo hệ thống thật từ `getInstructorNotifications()`.
   - Đảm bảo tất cả button không phải submit form đều có `type="button"`.
2. [src/services/api.ts](file:///F:/Phatnt/Documents/MindHub-Frontend/src/services/api.ts): Bổ sung các phương thức `uploadInstructorAvatar`, `getInstructorNotificationPreferences`, `updateInstructorNotificationPreferences`, `changeInstructorPassword`, `getInstructorNotifications`.
3. [src/utils/routes.ts](file:///F:/Phatnt/Documents/MindHub-Frontend/src/utils/routes.ts): Thêm helper `instructorProfile: (tab?: string) => '/instructor/profile' + (tab ? '?tab=' + tab : '')`.
4. [src/components/InstructorDashboard.tsx](file:///F:/Phatnt/Documents/MindHub-Frontend/src/components/InstructorDashboard.tsx): Đảm bảo điều hướng URL `/instructor/profile` hiển thị đúng tab Hồ sơ.

---

## 4. KẾT QUẢ KIỂM THỬ

### Backend Tests (Pest/PHPUnit)
```powershell
php artisan test --filter=InstructorProfileTest
```
- **Kết quả:** `Passed: 12 / 12 tests` (44 assertions), 0 lỗi.

### Frontend Build & Type Check
```powershell
npx tsc --noEmit
npm run build
```
- **Kết quả:** Build thành công 100% trong 11.14s (`dist/assets/index-...js`), 0 lỗi TypeScript.
