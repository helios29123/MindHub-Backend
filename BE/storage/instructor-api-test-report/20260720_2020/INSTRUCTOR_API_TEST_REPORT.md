# INSTRUCTOR API TEST REPORT

## 1. Môi trường
- **Branch**: `feature/full-instructor-api-revenue-share`
- **Commit**: `develop` (latest)
- **PHP version**: 8.x
- **Laravel version**: 11.x
- **DB**: MySQL (refused local connection during tests; DB structure analyzed via migrations and SQL schemas)
- **Thời gian test**: 2026-07-20 20:20:00

## 2. Tổng quan kết quả
- **Tổng route expected**: 81
- **Route có trong route:list**: 52 (nhiều route bị thiếu hoặc sai đường dẫn nhóm)
- **Route thiếu**: 29
- **Test pass**: 0 (Không thể kết nối MySQL cục bộ do MySQL offline tại local, các test suite đều báo Refused Connection)
- **Test fail**: Tất cả feature tests liên quan đều fail vì MySQL offline.
- **API 500 / Lỗi Logic**:
  - `PaymentService` chia sai tiền 100/0 thay vì 70/30.
  - Lỗi Type Error nghiêm trọng trong `InstructorWithdrawalController` khi truyền `$request->user()` thay vì `(int) $request->user()->id`.
  - Các route `/withdrawals` bị override và map sai về `InstructorCourseController` thay vì `InstructorWithdrawalController`.
- **Lỗi bảo mật ownership**: Cần cài đặt chặt chẽ `course.instructor_id = current_user.id` cho toàn bộ model (Coupons, Comments, Courses).
- **Lỗi response format**: Q&A Reply test mong đợi data nằm dưới wrapper `data.reply` và `data.question_status.is_answered`, nhưng controller `replyComment` hiện tại trả trực tiếp.

---

## 3. Lỗi nghiêm trọng P0 (Routing & Class/Method Lookup)

### 3.1. Trùng lặp & Override sai Route Withdrawals
Trong file `routes/api/instructor.php`:
- Dòng 126-129 khai báo route rút tiền về `InstructorWithdrawalController`.
- Tuy nhiên ở dòng 187-191 lại khai báo ghi đè (override) 2 route này về `InstructorCourseController` (`withdrawSummary` và `withdrawals`).
- Hệ quả: Khi gọi `GET /api/instructor/withdrawals`, Laravel dispatch đến `InstructorCourseController@withdrawals`, gây override sai và không gọi đúng Controller nghiệp vụ.

### 3.2. Thiếu Method trong InteractionController (Q&A)
File `routes/api/instructor.php` định nghĩa các route:
- `/questions/summary` -> `InteractionController@instructorQuestionSummary`
- `/questions/course-options` -> `InteractionController@instructorQuestionCourseOptions`
- `/questions/lesson-options` -> `InteractionController@instructorQuestionLessonOptions`
- `/questions/{id}` -> `InteractionController@showInstructorQuestion`

Tuy nhiên, `InteractionController` chỉ chứa các phương thức: `lessonComments`, `replyComment`, `storeReview`, và `instructorQuestions`. 
=> Gọi các route trên sẽ bắn ra lỗi **MethodNotAllowedException** hoặc **BadMethodCallException**.

---

## 4. Lỗi nghiệp vụ P1 (Logic & Data Integrity)

### 4.1. Lỗi Type Error nghiêm trọng trong InstructorWithdrawalController
Trong controller `InstructorWithdrawalController.php`:
- Các hàm `summary`, `index`, `show`, `store`, `payoutAccounts` đều truyền nguyên thực thể `$request->user()` (đối tượng User) vào service.
- Nhưng chữ ký (signature) của các hàm service trong `InstructorWithdrawalService.php` đều yêu cầu tham số đầu tiên là `int $instructorId`.
- Hệ quả: Gây ra lỗi **TypeError** ngay khi được kích hoạt.

### 4.2. PaymentService Chia Sai Lợi Nhuận (100% cho Giảng viên)
Trong `PaymentService.php` hàm `createRevenueAfterCourseOrderPaid` (dòng 529-555):
- Thiết lập `platform_fee_percent = 0`, `platform_fee_amount = 0`, và chuyển toàn bộ `instructor_amount = gross_amount` (100% về giảng viên).
- Dẫn đến thất thoát hoa hồng nền tảng và sai công thức mặc định 70/30 (hoặc theo Rule Commission).

### 4.3. Không có cơ chế Chống Trùng Doanh Thu (Duplicate Payments Callback)
Hiện tại `PaymentService.php` chưa kiểm tra triệt để tính duy nhất của `order_id` trong bảng `revenues` trước khi chèn, có thể tạo doanh thu trùng lặp nếu webhook callback chạy lại.

### 4.4. Thiếu Toàn Bộ Endpoint của Coupon, Notification, Payout Accounts chi tiết
- Chưa khai báo bất kỳ route nào của Coupons trong `routes/api/instructor.php`.
- Chưa khai báo các route tạo/sửa/set-default/disable cho `Payout Accounts` ngoại trừ API lấy danh sách active.
- Thiếu hoàn toàn API Notifications cho vai trò giảng viên.

---

## 5. Lỗi UX/API Response P2 (Field Consistency)

### 5.1. Sai lệch cấu trúc Question Index
Test suite `InstructorQuestionApiTest.php` kỳ vọng danh sách câu hỏi trả về các trường: `comment_id`, `is_answered`, và `status_label`.
Nhưng `InstructorQuestionResource` hiện tại trả về `id` và `status` (chuỗi `answered` hoặc `unanswered`), không khớp cấu trúc kiểm tra.

### 5.2. Sai lệch cấu trúc Question Reply
Test suite kỳ vọng phản hồi Q&A trả về dạng:
```json
{
  "success": true,
  "data": {
    "reply": { ... },
    "question_status": { "is_answered": true }
  }
}
```
Nhưng `InteractionController@replyComment` chỉ trả về resource comment trực tiếp dạng: `data: { id, parent_id, lesson_id... }`.

---

## 6. Chi tiết từng nhóm API & Route cần cấu hình thêm

### 6.1. Dashboard
- **GET /api/instructor/dashboard**: Cần phẳng hóa dữ liệu trả về gồm các trường đếm (published, draft, pending_review, v.v.).
- **GET /api/instructor/dashboard/revenue-chart**: Đổi alias từ `/reports/revenue-chart`.
- **GET /api/instructor/dashboard/enrollment-chart**: Đổi alias từ `/reports/enrollment-chart`.
- **GET /api/instructor/dashboard/top-courses**: Đổi alias từ `/reports/top-courses`.
- **GET /api/instructor/dashboard/incomplete-courses**: Tạo mới route và service đếm các khóa học chưa hoàn thiện checklist.

### 6.2. Courses & Curriculum
- Thêm route checklist, check slug, preview.
- Cập nhật tự động `courses.total_duration_seconds` sau khi tạo/sửa/xóa lesson.

### 6.3. Payout Accounts & Withdrawals
- Khắc phục lỗi truyền tham số đối tượng User thành ID (int) trong `InstructorWithdrawalController`.
- Thêm cột `is_default` thông qua migration cho `payout_accounts`.
- Thêm đầy đủ routes POST/PATCH/DELETE cho payout accounts.

---

## 7. Kết luận
- Chưa thể kết nối FE trực tiếp vì nhiều API còn thiếu controller/routing và tính sai nghiệp vụ chia tiền.
- Cần chạy Migration bổ sung cột trước, sau đó chỉnh sửa các lỗi P0 và P1.
