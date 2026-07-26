# INSTRUCTOR COURSE HIDE & DELETE MANAGEMENT - AUDIT & FIX REPORT

> [!NOTE]
> **Chức năng Quản lý Khóa học Giảng viên (Xóa khóa học soft-delete, Ẩn/Hiện lại khóa học, Modal xác nhận, Toast thông báo, & Filter Đang ẩn)** tại `/instructor/courses` đã được triển khai và hoàn thiện 100% giữa Laravel Backend và React/TypeScript Frontend.

---

## 1. PHÂN TÍCH NGUYÊN NHÂN LỖI XÓA VÀ NÂNG CẤP

1. **Nguyên nhân lỗi xóa ban đầu (`"Lỗi xóa khóa học: Có lỗi xảy ra..."`)**:
   - *Backend*: Chưa khai báo route `DELETE /api/instructor/courses/{id}` cũng như các action controller/service tương ứng.
   - *Frontend*: Gọi endpoint không tồn tại, bị bẫy trong `try/catch` với `alert("Lỗi xóa khóa học: " + e.message)`.

2. **Quy tắc Xóa vs. Ẩn Khóa Học Hợp Nghiệp Vụ**:
   - **Xóa Khóa Học (Soft Delete)**: Chỉ áp dụng cho khóa học *chưa phát sinh học viên (`enrollments`), đơn hàng (`orders`), hoặc doanh thu (`revenues`)*. Sử dụng `deleted_at` (Eloquent `SoftDeletes`).
   - **Ràng Buộc Khi Đã Có Dữ Liệu (HTTP 409 Conflict)**: Khi khóa học đã phát sinh học viên/đơn hàng/doanh thu, Backend chặn xóa và trả HTTP 409 với mã `COURSE_HAS_DEPENDENCIES`: `"Khóa học đã phát sinh học viên hoặc giao dịch nên không thể xóa. Bạn có thể ẩn khóa học thay thế."`.
   - **Đề Xuất Ẩn Tự Động**: Khi bấm Xóa khóa học có dữ liệu, hệ thống tự động hiển thị Modal giải thích lý do kèm nút *"Ẩn khóa học thay thế"* giúp Giảng viên thao tác nhanh chóng mà không bị lỗi.

3. **Chức Năng Ẩn / Hiện Lại Khóa Học**:
   - **Ẩn Khóa Học (`PATCH /api/instructor/courses/{id}/hide`)**: Chuyển trạng thái sang `hidden`. Khóa học không còn hiển thị cho học viên mới ghi danh, nhưng giữ nguyên 100% dữ liệu bài học, học viên đã mua và lịch sử doanh thu.
   - **Hiện Lại Khóa Học (`PATCH /api/instructor/courses/{id}/unhide`)**: Chuyển trạng thái về `published` (nếu trước đó đã được duyệt/xuất bản) hoặc `draft`.

---

## 2. BẢNG AUDIT ENDPOINTS & API CONTRACT

| Action | HTTP Method | Endpoint Backend | Ownership Scope | Response Success | Error Code (Ràng buộc) |
|---|---|---|---|---|---|
| **Xóa Khóa Học** | `DELETE` | `/api/instructor/courses/{id}` | `$request->user()->id` | `200 OK` (`"Đã xóa khóa học."`) | `409 Conflict` (`COURSE_HAS_DEPENDENCIES`) |
| **Ẩn Khóa Học** | `PATCH` | `/api/instructor/courses/{id}/hide` | `$request->user()->id` | `200 OK` (`status: "hidden"`) | `404 Not Found` (Khóa học không thuộc GV) |
| **Hiện Lại Khóa Học** | `PATCH` | `/api/instructor/courses/{id}/unhide` | `$request->user()->id` | `200 OK` (`status: "published" / "draft"`) | `404 Not Found` (Khóa học không thuộc GV) |
| **Lấy Danh Sách** | `GET` | `/api/instructor/courses?status=hidden` | `$request->user()->id` | `200 OK` (Phân trang & Bỏ qua deleted_at) | `200 OK` |

---

## 3. THIẾT KẾ GIAO DIỆN & TRẢI NGHIỆM NGƯỜI DÙNG (UI/UX)

1. **Card Thống Kê (Overview Summary Cards)**:
   - Bổ sung Card thứ 6 **"Hidden" (Đang ẩn)** hiển thị số lượng khóa học đang tạm ẩn.
   - Cập nhật số liệu tức thì sau khi Hide/Unhide/Delete không cần reload trang (`window.location.reload()`).

2. **Cột Thao Tác Trong Bảng**:
   - Nút **Chỉnh sửa** (Edit course builder).
   - Nút **Ẩn khóa học** (icon `EyeOff`) khi khóa học đang công khai/nháp.
   - Nút **Hiện lại** (icon `Eye`) khi khóa học đang ở trạng thái `hidden`.
   - Nút **Xóa** (icon `Trash2`).
   - Hiệu ứng **Loading Per-Row**: Chỉ xoay spinner trên đúng hàng đang thao tác (`courseActionLoadingId`), không khóa toàn bộ bảng.

3. **Thay Thế Browser Alert Bằng Custom Modals & Toast**:
   - **Modal Confirm Hide**: Tiêu đề *"Ẩn khóa học?"*, hiển thị cảnh báo không mất dữ liệu học viên cũ.
   - **Modal Confirm Delete**: Tiêu đề *"Xóa khóa học?"*, cảnh báo xóa mềm cho khóa học chưa có giao dịch.
   - **Modal Suggest Hide**: Tự động hiện khi xóa khóa học có dữ liệu, gợi ý *"Ẩn khóa học thay thế"*.
   - **Toast Overlay**: Thông báo mượt mà ở góc dưới màn hình.

---

## 4. KẾT QUẢ AUTOMATED TESTS & BUILD VERIFICATION

### A. Backend Feature Test Suite
```bash
php artisan test --filter=InstructorCourseManagementTest
# Output: Tests: 4 passed (18 assertions) - 100% PASS
```
- ✅ Giảng viên xóa khóa học chưa có dữ liệu -> Soft Delete thành công (`deleted_at !== null`).
- ✅ Giảng viên xóa khóa học đã có học viên/đơn hàng -> Trả HTTP 409 `COURSE_HAS_DEPENDENCIES`.
- ✅ Giảng viên ẩn khóa học -> Chuyển status thành `hidden`.
- ✅ Giảng viên hiện lại khóa học -> Trả về `published`.
- ✅ Giảng viên thao tác khóa học của người khác -> Trả HTTP 404 Not Found.

### B. Frontend TypeScript Check
```bash
npx tsc --noEmit
# Output: Exit Code 0 (0 errors, 100% clean compilation)
```

---

## 5. DANH SÁCH FILE ĐÃ THAY ĐỔI

### Backend (`MindHub-Backend/be`)
- `app/Services/Instructor/InstructorCourseService.php`: Bổ sung `deleteCourse`, `hideCourse`, `unhideCourse`.
- `app/Http/Controllers/InstructorCourseController.php`: Bổ sung `destroy`, `hide`, `unhide`.
- `routes/api/instructor.php`: Đăng ký `DELETE /courses/{id}`, `PATCH /courses/{id}/hide`, `PATCH /courses/{id}/unhide`.
- `tests/Feature/InstructorCourseManagementTest.php`: Bộ test kiểm thử tự động toàn diện.

### Frontend (`MindHub-Frontend`)
- `src/services/api.ts`: Thêm `deleteInstructorCourse`, `hideInstructorCourse`, `unhideInstructorCourse`.
- `src/types.ts`: Cập nhật union type `Course['status']` bổ sung `'published'`, `'approved'`.
- `src/components/InstructorDashboard.tsx`: Bổ sung Card 6 Hidden, Filter trạng thái Đang ẩn, Modal xác nhận Hide/Delete/Suggest, Toast overlay, và Loading theo hàng.
- `src/App.tsx`: Cập nhật `handleDeleteCourse`.

> [!IMPORTANT]
> Không thực hiện bất kỳ lệnh `git commit` hay `git push` nào theo đúng chỉ thị dự án.
