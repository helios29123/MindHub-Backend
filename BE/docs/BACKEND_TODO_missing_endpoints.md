# Backend TODO — API frontend gọi nhưng backend chưa có route

> Cập nhật: 2026-07-03. Sinh ra khi rà soát khớp `MindHub-Frontend/src/services/api.ts` với `routes/api/*.php`.
> Các mục đã xử lý trong đợt này KHÔNG liệt kê lại (xem cuối file).

Ký hiệu: **[MIGRATION]** = cần bảng/cột DB mới · **[ROUTE]** = chỉ cần thêm route/controller · **[EXTERNAL]** = dịch vụ ngoài.

---

## 1. Quản lý phiên đăng nhập (Auth sessions) — [ROUTE]
Frontend gọi (đang 404): `logoutAll`, `refreshToken`, `getSessions`, `revokeSession`.
Bảng `sessions` đã có (SessionRepository). Đề xuất thêm trong `routes/api/auth.php` (group `auth.session`):
- `POST /auth/logout-all` → xóa mọi session của user hiện tại.
- `POST /auth/refresh` → cấp lại access token từ session hợp lệ.
- `GET /auth/sessions` → liệt kê session đang hoạt động.
- `DELETE /auth/sessions/{id}` → thu hồi 1 session.

## 2. Hỏi đáp khóa học (Q&A) — [MIGRATION]
Frontend: `getCourseQuestions`, `addCourseQuestion`, `answerCourseQuestion` (component `CourseQA.tsx`).
Cần bảng `course_questions` (course_id, lesson_id?, author_id, content, is_internal, status) và `course_question_answers` (question_id, author_id, content).
Route đề xuất:
- `GET/POST /courses/{id}/questions`
- `POST /courses/{id}/questions/{questionId}/answers`

## 3. Yêu cầu đóng/khóa tài khoản (account requests) — [MIGRATION]
Frontend: `createAccountRequest`, `getAccountRequests`, `resolveAccountRequest`.
Cần bảng `account_requests` (user_id, type[delete|suspend], reason, status, timestamps).
Route:
- `POST /users/me/account-requests`
- `GET /admin/account-requests`
- `PATCH /admin/account-requests/{id}/resolve`

## 4. Duyệt rút tiền (payout requests) admin — [MIGRATION hoặc dùng bảng payout hiện có]
`POST /instructor/withdrawals` đã có. Còn thiếu phía admin:
- `GET /admin/payout-requests` (danh sách)
- `PATCH /admin/payout-requests/{id}/resolve` (completed|rejected)
Kiểm tra xem có bảng `withdrawals`/`payout_requests` để tái dùng; nếu chưa, tạo migration.

## 5. Cập nhật trạng thái đơn hàng (admin) — [ROUTE]
Frontend `updateOrderStatus` → `PATCH /admin/orders/{id}/status`.
Hiện chỉ có `GET /admin/orders`. Thêm 1 route + method cập nhật status (success|pending|failed).

## 6. Khóa/mở tài khoản user (admin) — [ROUTE, hoặc align FE]
Frontend `toggleUserLockAdmin` → `POST /admin/users/{id}/lock`.
Có thể ALIGN FE sang `PATCH /admin/users/{id}` với `{ status: 'locked'|'active' }` nếu `UpdateAdminUserRequest` cho phép — kiểm tra rules trước.

## 7. Liên hệ (contact) — [MIGRATION nhẹ hoặc chỉ gửi mail]
Frontend `sendContactMessage` → `POST /contact` (component `ContactPage.tsx`).
Đề xuất: route public `POST /contact` lưu bảng `contact_messages` và/hoặc gửi mail admin.

## 8. Đồng bộ chương trình học (bulk chapters) — [ROUTE, nên align]
Frontend `updateCourseChapters` → `POST /courses/{id}/chapters`.
Backend đã có Sections API (`/instructor/sections`). Nên ALIGN FE sang tạo/sửa section thay vì route bulk mới.

## 9. Tiến độ học tổng quát — [align FE]
Frontend `updateStudentProgress` → `PATCH /progress/{courseId}`.
Backend đã có `PATCH /learn/lessons/{id}/progress` + `/complete`. Nên bỏ/ghép `updateStudentProgress` vào các route này.

## 10. Rời vai trò giảng viên / xin quyền admin — [MIGRATION/POLICY]
Frontend `requestLeaveInstructorRole`, `requestAdminRole` — chưa có backend & chưa rõ nghiệp vụ.
Cần chốt quy trình (ai duyệt, lưu ở đâu) trước khi làm.

## 11. Đơn mua gói lượt & xác nhận thanh toán — [ROUTE]
Đã nối tạo đơn (`POST /instructor/credit-orders`) + số dư + giao dịch. Còn thiếu:
- `GET` lịch sử đơn mua gói của instructor (frontend `getInstructorPackageOrders`).
- `GET` danh sách đơn mua gói cho admin (frontend `getAdminPackageOrders`).
- Xác nhận thanh toán gói lượt (frontend `confirmPackagePayment` → `POST /payments/confirm`): nên thống nhất dùng VNPay (`POST /payments/vnpay/create` + `vnpay-return`) cho credit-order thay vì endpoint riêng.

## 12. OTP email & điện thoại — [EXTERNAL]
Frontend `resendVerificationEmail`, `verifyEmailOtp`, `sendPhoneOtp`, `verifyPhoneOtp` gọi thẳng `http://localhost:3000` (Node server trong `MindHub-Frontend/server`).
→ Hoặc deploy/chạy service Node đó, hoặc chuyển logic OTP sang Laravel (`/auth/email/*`, `/auth/phone/*`).

---

## Đã xử lý trong đợt rà soát này (tham khảo)
- ✅ `PATCH /admin/courses/{id}/approve|reject` (sửa method POST→PATCH ở FE).
- ✅ `updateCourseStatusAdmin` → `PATCH /admin/courses/{id}`.
- ✅ Đăng ký/duyệt giảng viên → `/me/instructor-upgrade`, `/admin/instructor-upgrade-requests/*`.
- ✅ Credits/packages → `/instructor|admin/credit-packages`, `/instructor/course-credits`, `/instructor/credit-transactions`, `/instructor/credit-orders`.
- ✅ Khóa học theo giảng viên → thêm filter `instructor_id` cho `GET /courses` (backend) + FE dùng `/courses?instructor_id=`.
- ✅ `GET /admin/test` health-check.
- ✅ Video streaming → route `learn.lessons.stream` chạy bằng URL đã ký (bỏ Bearer), FE ClassroomScreen gọi `/learn/lessons/{id}/video-url`.
