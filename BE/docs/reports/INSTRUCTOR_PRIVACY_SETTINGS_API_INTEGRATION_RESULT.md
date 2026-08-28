# BÁO CÁO KẾT QUẢ NỐI API THẬT CHO MODAL "TÙY CHỌN QUYỀN RIÊNG TƯ"

**Ngày thực hiện:** 24/07/2026  
**Dự án Frontend:** `F:\Phatnt\Documents\MindHub-Frontend`  
**Dự án Backend:** `F:\Phatnt\laragon\www\MindHub-Backend\be`  
**Trang:** `http://127.0.0.1:3000/instructor/profile`

---

## 1. AUDIT FRONTEND & BACKEND

### Frontend (`MindHub-Frontend/`)
- **Modal Component:** Modal "Tùy chọn quyền riêng tư" nằm trong [InstructorProfilePage.tsx](file:///F:/Phatnt/Documents/MindHub-Frontend/src/components/instructor-ui/InstructorProfilePage.tsx).
- **Trước khi xử lý:** Modal lưu bằng local state `privacySettings` và chỉ đồng bộ sơ bộ khi tab settings mount.
- **Sau khi nối API thật:**
  - Bấm mở modal -> Gọi `GET /api/instructor/profile/privacy` bằng helper `ApiService.getInstructorPrivacySettings()`.
  - Bấm nút **"Lưu quyền riêng tư"** -> Gọi `PATCH /api/instructor/profile/privacy` với payload validated (`profile_visibility`, `show_email`, `show_phone`, `show_social_links`).
  - Disable nút bấm, hiển thị icon spinner `Loader2` chống double click, thông báo Toast thành công và tự động đóng modal. Không reload trang.

### Backend (`MindHub-Backend/be/`)
- **API Endpoints:**
  - `GET /api/instructor/profile/privacy` -> Tải cài đặt quyền riêng tư hiện tại của người dùng từ metadata.
  - `PATCH /api/instructor/profile/privacy` -> Xác minh và cập nhật quyền riêng tư vào `locked_reason` metadata của `users` table.
- **Validation Rules:**
  - `profile_visibility`: `sometimes|required|string|in:public,students_only,private`
  - `show_email`: `sometimes|required|boolean`
  - `show_phone`: `sometimes|required|boolean`
  - `show_social_links`: `sometimes|required|boolean`
- **Áp dụng Logic Nghiệp vụ vào Public Profile Endpoint (`GET /api/instructors/{id}`):**
  - **`profile_visibility = 'private'`:** Từ chối truy cập cho người dùng khác / khách (`403 Forbidden: "Hồ sơ giảng viên này đang ở chế độ riêng tư."`).
  - **`profile_visibility = 'students_only'`:** Chỉ cho phép người dùng đã đăng ký ít nhất 1 khóa học của giảng viên xem hồ sơ (`403 Forbidden: "Hồ sơ giảng viên này chỉ dành cho học viên đã đăng ký khóa học."`).
  - **`show_email = false`:** Ẩn email trên API công khai (`email = null`).
  - **`show_phone = false`:** Ẩn số điện thoại trên API công khai (`phone = null`).
  - **`show_social_links = false`:** Ẩn mạng xã hội trên API công khai (`social_links = null`).

---

## 2. KẾT QUẢ KIỂM THỬ (VERIFICATION RESULTS)

### Backend Pest / PHPUnit Test Suite
```powershell
php artisan test --filter=InstructorProfile
```
- **Kết quả:** `Passed: 49 / 49 tests` (212 assertions), 0 errors. Sau đây là các test cases mới:
  - `test_can_get_and_update_privacy_settings`: PASS
  - `test_public_profile_respects_private_visibility`: PASS
  - `test_public_profile_hides_email_and_phone_when_disabled`: PASS

### Frontend TypeScript & Production Build
```powershell
npx tsc --noEmit
npm run build
```
- **Kết quả:** Build thành công 100% trong 9.80s (`dist/assets/index-HP2Xet2u.js`), 0 lỗi TypeScript.

---

## 3. CÁC TẬP TIN ĐÃ SỬA DỔI

1. [be/app/Services/Course/CoursePublicService.php](file:///F:/Phatnt/laragon/www/MindHub-Backend/be/app/Services/Course/CoursePublicService.php): **[MODIFY]** Thêm kiểm tra `profile_visibility` (chế độ `private` và `students_only`) trước khi trả dữ liệu giảng viên công khai.
2. [be/app/Http/Resources/Course/InstructorResource.php](file:///F:/Phatnt/laragon/www/MindHub-Backend/be/app/Http/Resources/Course/InstructorResource.php): **[MODIFY]** Lọc ẩn/hiện `email`, `phone`, `social_links` theo thiết lập quyền riêng tư của giảng viên.
3. [be/app/Http/Controllers/InstructorProfileController.php](file:///F:/Phatnt/laragon/www/MindHub-Backend/be/app/Http/Controllers/InstructorProfileController.php): **[MODIFY]** Bổ sung validation chuẩn hóa cho `updatePrivacy`.
4. [be/tests/Feature/Instructor/InstructorProfileSettingsTest.php](file:///F:/Phatnt/laragon/www/MindHub-Backend/be/tests/Feature/Instructor/InstructorProfileSettingsTest.php): **[MODIFY]** Thêm test cases tự động kiểm tra tính tuân thủ quyền riêng tư trên API công khai.
5. [MindHub-Frontend/src/components/instructor-ui/InstructorProfilePage.tsx](file:///F:/Phatnt/Documents/MindHub-Frontend/src/components/instructor-ui/InstructorProfilePage.tsx): **[MODIFY]** Nối `handleOpenPrivacyModal` và `handleSavePrivacy` với API thật, chống double click, không reload toàn trang, giữ nguyên cài đặt khi mở lại modal hoặc reload trang.

---

## 4. ĐIỀU KIỆN HOÀN THÀNH

- [x] Modal load cài đặt từ Backend (`GET /api/instructor/profile/privacy`).
- [x] Lưu bằng request `PATCH /api/instructor/profile/privacy`.
- [x] Cài đặt được lưu thật vào DB và giữ nguyên sau khi reload trang.
- [x] Endpoint Public Profile (`/api/instructors/{id}`) thực sự tôn trọng cài đặt riêng tư (ẩn email, ẩn phone, ẩn social links, hoặc chặn private / students_only).
- [x] Không commit hoặc push Git.
