# BÁO CÁO KẾT QUẢ AUDIT VÀ NỐI API TAB "CÀI ĐẶT" HỒ SƠ GIẢNG VIÊN

**Ngày thực hiện:** 24/07/2026  
**Dự án Frontend:** `F:\Phatnt\Documents\MindHub-Frontend`  
**Dự án Backend:** `F:\Phatnt\laragon\www\MindHub-Backend\be`  
**Trang:** `http://127.0.0.1:3000/instructor/profile?tab=settings`

---

## 1. TỔNG QUAN AUDIT VÀ THIẾT KẾ BẢO MẬT

- **Giữ nguyên 100% giao diện UI:** Đã giữ nguyên style, màu sắc, breadcrumb, tab navigation, layout form và 4 card sidebar theo đúng yêu cầu.
- **Session Auth & cookie `credentials: "include"`:** Tất cả các request Frontend gọi tới Backend đều lấy người dùng hiện tại từ Laravel Auth Session context.
- **Luồng Đổi Mật Khẩu 2 Bước với Email OTP:**
  1. Người dùng nhập `current_password`, `password`, `password_confirmation`.
  2. Bấm nút **"Gửi mã OTP qua Email"** -> Backend kiểm tra `current_password`, chính sách mật khẩu, sinh OTP 6 số, hash và lưu DB bảng `user_otps` (hiệu lực 5 phút), đồng thời gửi email thông báo tiếng Việt tới email của người dùng.
  3. Mở Modal OTP hiển thị email dạng che (`in****@mindhub.test`), đếm ngược 60 giây cho phép gửi lại mã OTP, ô nhập 6 chữ số và nút bấm xác nhận.
  4. Bấm **"Xác nhận đổi mật khẩu"** -> Backend xác minh OTP (chưa dùng, chưa hết hạn, sai <= 5 lần, đúng purpose = `change_password`), hash mật khẩu mới, hủy mã OTP, regenerate session ID.
  5. Khi thành công: hiển thị Toast "Đổi mật khẩu thành công!", đóng modal, xóa trắng các trường mật khẩu, **không reload toàn trang**.

---

## 2. MA TRẬN API BỔ SUNG VA TÍCH HỢP

| Chức năng FE | Endpoint Backend | Method | Trạng thái | Ghi chú xử lý |
|---|---|---|---|---|
| 1. Gửi OTP đổi mật khẩu | `POST /api/instructor/profile/password/send-otp` | POST | **CREATED (200)** | Kiểm tra mật khẩu cũ, sinh mã OTP 6 số, lưu DB `user_otps`, gửi email Mailable `PasswordChangeOtpMail`. |
| 2. Xác minh OTP & Đổi mật khẩu | `PATCH /api/instructor/profile/password` | PATCH | **UPDATED (200)** | Bắt buộc có mã `otp` 6 chữ số. Cập nhật hash mật khẩu mới, hủy OTP, regenerate session. |
| 3. Danh sách phiên đăng nhập | `GET /api/instructor/profile/sessions` | GET | **CREATED (200)** | Đọc phiên đăng nhập thực tế của user từ bảng `sessions` (thiết bị, IP, thời gian hoạt động). |
| 4. Đăng xuất các phiên khác | `DELETE /api/instructor/profile/sessions/others` | DELETE | **CREATED (200)** | Thu hồi các session khác ngoại trừ session ID hiện tại. |
| 5. Cài đặt thông báo | `GET & PATCH /api/instructor/profile/notification-preferences` | GET/PATCH | **UPDATED (200)** | Bổ sung kiểm tra số điện thoại: từ chối bật SMS Alerts nếu chưa có SĐT. |
| 6. Quyền riêng tư (Privacy) | `GET & PATCH /api/instructor/profile/privacy` | GET/PATCH | **CREATED (200)** | Lưu/đọc tùy chọn quyền riêng tư trong metadata người dùng (`profile_visibility`, `show_email`, `show_phone`, `show_social_links`). |
| 7. Trạng thái tài khoản | `GET /api/instructor/profile` | GET | **AVAILABLE (200)** | Lấy thông tin xác minh email, phone, uy tín và chính sách. |
| 8. Thông báo hệ thống | `GET /api/instructor/notifications` | GET | **AVAILABLE (200)** | Danh sách thông báo hệ thống gần đây. |

---

## 3. CÁC TẬP TIN ĐÃ THAY ĐỔI

### Backend (`MindHub-Backend/be/`)
1. [database/migrations/2026_07_24_000000_create_user_otps_table.php](file:///F:/Phatnt/laragon/www/MindHub-Backend/be/database/migrations/2026_07_24_000000_create_user_otps_table.php): **[NEW]** Tạo bảng `user_otps` lưu trữ mã OTP mã hóa hash, mục đích, số lần thử và thời gian hết hạn.
2. [app/Models/UserOtp.php](file:///F:/Phatnt/laragon/www/MindHub-Backend/be/app/Models/UserOtp.php): **[NEW]** Eloquent Model cho bảng `user_otps` với helper `generateOtp` và `verifyOtp`.
3. [app/Mail/PasswordChangeOtpMail.php](file:///F:/Phatnt/laragon/www/MindHub-Backend/be/app/Mail/PasswordChangeOtpMail.php): **[NEW]** Mailable gửi mã OTP 6 số qua email.
4. [app/Services/Instructor/InstructorProfileService.php](file:///F:/Phatnt/laragon/www/MindHub-Backend/be/app/Services/Instructor/InstructorProfileService.php): **[MODIFY]** Thêm logic `sendPasswordOtp`, nâng cấp `changePassword`, thêm quản lý `sessions`, `privacy` và kiểm tra SĐT cho `sms_alerts`.
5. [app/Http/Controllers/InstructorProfileController.php](file:///F:/Phatnt/laragon/www/MindHub-Backend/be/app/Http/Controllers/InstructorProfileController.php): **[MODIFY]** Thêm controller handlers: `sendPasswordOtp`, `getSessions`, `revokeOtherSessions`, `revokeSession`, `getPrivacy`, `updatePrivacy`.
6. [routes/api/instructor.php](file:///F:/Phatnt/laragon/www/MindHub-Backend/be/routes/api/instructor.php): **[MODIFY]** Đăng ký các route `/api/instructor/profile/password/send-otp`, `/sessions`, `/privacy`.
7. [tests/Feature/Instructor/InstructorProfileSettingsTest.php](file:///F:/Phatnt/laragon/www/MindHub-Backend/be/tests/Feature/Instructor/InstructorProfileSettingsTest.php): **[NEW]** Unit & Feature test suite đầy đủ cho tab Cài đặt & luồng OTP.
8. [tests/Feature/Instructor/InstructorProfileTest.php](file:///F:/Phatnt/laragon/www/MindHub-Backend/be/tests/Feature/Instructor/InstructorProfileTest.php): **[MODIFY]** Cập nhật test cases đổi mật khẩu tương thích luồng OTP.

### Frontend (`MindHub-Frontend/`)
1. [src/types.ts](file:///F:/Phatnt/Documents/MindHub-Frontend/src/types.ts): **[MODIFY]** Thêm TypeScript interfaces `SendPasswordOtpPayload`, `ChangePasswordPayload`, `UserSession`, `PrivacySettings`.
2. [src/services/api.ts](file:///F:/Phatnt/Documents/MindHub-Frontend/src/services/api.ts): **[MODIFY]** Thêm API methods `sendChangePasswordOtp`, `changeInstructorPassword`, `getInstructorSessions`, `revokeOtherInstructorSessions`, `revokeInstructorSession`, `getInstructorPrivacySettings`, `updateInstructorPrivacySettings`.
3. [src/components/instructor-ui/InstructorProfilePage.tsx](file:///F:/Phatnt/Documents/MindHub-Frontend/src/components/instructor-ui/InstructorProfilePage.tsx): **[MODIFY]**
   - Bổ sung nút Ẩn/Hiện mật khẩu (`Eye` / `EyeOff`) cho cả 3 ô nhập mật khẩu.
   - Nối Form Đổi mật khẩu với luồng 2 bước Gửi OTP Email -> Hiển thị Modal OTP 6 số + countdown 60s + resend code + xác nhận.
   - Nối danh sách phiên đăng nhập thật và nút "Đăng xuất các thiết bị khác".
   - Bổ sung Modal Tùy chọn quyền riêng tư và validation check SĐT trước khi bật SMS Alerts.
   - Không reload trang khi đổi mật khẩu hoặc cập nhật cài đặt.

---

## 4. KẾT QUẢ KIỂM THỬ (VERIFICATION RESULTS)

### Backend Pest & PHPUnit Tests
```powershell
php artisan test --filter=InstructorProfile
```
- **Kết quả:** `Passed: 47 / 47 tests` (206 assertions), 0 errors.

### Frontend TypeScript Check & Production Build
```powershell
npx tsc --noEmit
npm run build
```
- **Kết quả:** Build thành công 100% trong 10.38s (`dist/assets/index-BLdyKx8w.js`), 0 lỗi TypeScript.

---

## 5. ĐIỀU KIỆN HOÀN THÀNH

- [x] Mật khẩu hiện tại được Backend xác minh.
- [x] Mật khẩu mới nhập hai lần và khớp.
- [x] OTP 6 số được gửi tới email user trong session.
- [x] OTP hết hạn sau 5 phút và chỉ dùng 1 lần.
- [x] Không thể bỏ qua OTP khi gọi API đổi mật khẩu.
- [x] Đổi mật khẩu lưu DB thật và regenerate session.
- [x] Không reload trang.
- [x] Phiên đăng nhập thật từ DB và hỗ trợ thu hồi session khác.
- [x] SMS Preference kiểm tra SĐT trước khi bật.
- [x] Privacy settings nối API thật.
- [x] Build và test pass 100%. Không commit hoặc push Git.
