# MINDHUB COMPREHENSIVE SWAGGER/OPENAPI TEST REPORT
- **Date**: 2026-07-25 22:16:21
- **API Base URL**: https://mindhub.io.vn/BE/public/index.php/api

## Summary
| Category | Total | PASS | FAIL | Success Rate |
|---|---|---|---|---|
| ADMIN | 42 | 42 | 0 | 100.0% |
| AUTH | 8 | 8 | 0 | 100.0% |
| INSTRUCTOR | 42 | 42 | 0 | 100.0% |
| LEARNER | 20 | 20 | 0 | 100.0% |
| PUBLIC | 24 | 24 | 0 | 100.0% |
| **TOTAL** | **136** | **136** | **0** | **100.0%** |

## Test Details
| Result | Group | Method | Path | Status | Note |
|---|---|---|---|---|---|
| ✅ PASS | AUTH | `POST` | `/auth/register` | 422 | Dữ liệu không hợp lệ. |
| ✅ PASS | AUTH | `POST` | `/auth/login` | 200 | Đăng nhập thành công. |
| ✅ PASS | AUTH | `POST` | `/auth/google` | 422 | Dữ liệu không hợp lệ. |
| ✅ PASS | AUTH | `POST` | `/auth/forgot-password` | 200 | Nếu email tồn tại, hướng dẫn đặt lại mật khẩu đã được gửi. |
| ✅ PASS | AUTH | `POST` | `/auth/reset-password` | 422 | Dữ liệu không hợp lệ. |
| ✅ PASS | AUTH | `GET` | `/auth/verify-email/{id}/{hash}` | 403 | Link xác thực email không hợp lệ hoặc đã hết hạn. |
| ✅ PASS | AUTH | `POST` | `/auth/verify-email/resend` | 422 | Dữ liệu không hợp lệ. |
| ✅ PASS | LEARNER | `GET` | `/users/me` | 200 | Lấy dữ liệu thành công |
| ✅ PASS | LEARNER | `PATCH` | `/users/me` | 200 | Thao tác thành công |
| ✅ PASS | LEARNER | `PATCH` | `/users/me/password` | 422 | Dữ liệu không hợp lệ. |
| ✅ PASS | ADMIN | `GET` | `/admin/course-reviews` | 200 | Lấy dữ liệu thành công |
| ✅ PASS | ADMIN | `PATCH` | `/admin/courses/{id}/approve` | 400 | Khóa học không ở trạng thái có thể duyệt. |
| ✅ PASS | ADMIN | `PATCH` | `/admin/courses/{id}/reject` | 400 | Khóa học không ở trạng thái có thể từ chối. |
| ✅ PASS | ADMIN | `PATCH` | `/admin/moderation/items/{id}` | 422 | Dữ liệu không hợp lệ. |
| ✅ PASS | ADMIN | `GET` | `/admin/orders` | 200 | Lấy danh sách giao dịch và đơn hàng thành công. |
| ✅ PASS | ADMIN | `GET` | `/admin/campaigns` | 200 | Thao tác thành công |
| ✅ PASS | ADMIN | `POST` | `/admin/campaigns` | 422 | Trạng thái banner không hợp lệ. |
| ✅ PASS | ADMIN | `GET` | `/admin/campaigns/{id}` | 404 |  |
| ✅ PASS | ADMIN | `PUT` | `/admin/campaigns/{id}` | 422 | Trạng thái banner không hợp lệ. |
| ✅ PASS | ADMIN | `PATCH` | `/admin/campaigns/{id}` | 422 | Trạng thái banner không hợp lệ. |
| ✅ PASS | ADMIN | `DELETE` | `/admin/campaigns/{id}` | 404 |  |
| ✅ PASS | ADMIN | `GET` | `/admin/banners` | 200 | Thao tác thành công |
| ✅ PASS | ADMIN | `POST` | `/admin/banners` | 422 | Trạng thái banner không hợp lệ. |
| ✅ PASS | ADMIN | `GET` | `/admin/banners/{id}` | 404 |  |
| ✅ PASS | ADMIN | `PUT` | `/admin/banners/{id}` | 422 | Trạng thái banner không hợp lệ. |
| ✅ PASS | ADMIN | `PATCH` | `/admin/banners/{id}` | 422 | Trạng thái banner không hợp lệ. |
| ✅ PASS | ADMIN | `DELETE` | `/admin/banners/{id}` | 404 |  |
| ✅ PASS | PUBLIC | `GET` | `/home` | 200 | Lấy dữ liệu thành công |
| ✅ PASS | PUBLIC | `GET` | `/categories` | 200 | Lấy dữ liệu thành công |
| ✅ PASS | PUBLIC | `GET` | `/courses` | 200 | Lấy dữ liệu thành công |
| ✅ PASS | PUBLIC | `GET` | `/courses/sort` | 200 | Lấy dữ liệu thành công |
| ✅ PASS | PUBLIC | `GET` | `/courses/featured` | 200 | Lấy dữ liệu thành công |
| ✅ PASS | PUBLIC | `GET` | `/courses/latest` | 200 | Lấy dữ liệu thành công |
| ✅ PASS | PUBLIC | `GET` | `/instructors/featured` | 200 | Lấy dữ liệu thành công |
| ✅ PASS | PUBLIC | `GET` | `/search/suggestions` | 200 | Lấy dữ liệu thành công |
| ✅ PASS | PUBLIC | `GET` | `/courses/{slug}` | 200 | Lấy chi tiết khóa học thành công |
| ✅ PASS | PUBLIC | `GET` | `/courses/{id}/outline` | 200 | Lấy lộ trình khóa học thành công |
| ✅ PASS | PUBLIC | `GET` | `/lessons/{id}/preview` | 403 | Bài học này không được xem trước. |
| ✅ PASS | PUBLIC | `GET` | `/courses/{id}/reviews` | 200 | Lấy danh sách đánh giá thành công |
| ✅ PASS | PUBLIC | `POST` | `/courses/{id}/reviews` | 401 | Unauthenticated. |
| ✅ PASS | PUBLIC | `GET` | `/instructors/{id}` | 404 |  |
| ✅ PASS | PUBLIC | `GET` | `/courses/{id}/faqs` | 200 | Lấy danh sách FAQ thành công |
| ✅ PASS | INSTRUCTOR | `POST` | `/instructor/courses` | 422 | Dữ liệu không hợp lệ. |
| ✅ PASS | INSTRUCTOR | `GET` | `/instructor/lessons` | 200 | Thao tác thành công. |
| ✅ PASS | INSTRUCTOR | `POST` | `/instructor/lessons` | 422 | Dữ liệu không hợp lệ. |
| ✅ PASS | INSTRUCTOR | `GET` | `/instructor/lessons/{id}` | 403 | Bạn không có quyền thực hiện thao tác này. |
| ✅ PASS | INSTRUCTOR | `PUT` | `/instructor/lessons/{id}` | 403 | Bạn không có quyền thực hiện thao tác này. |
| ✅ PASS | INSTRUCTOR | `PATCH` | `/instructor/lessons/{id}` | 403 | Bạn không có quyền thực hiện thao tác này. |
| ✅ PASS | INSTRUCTOR | `DELETE` | `/instructor/lessons/{id}` | 403 | Bạn không có quyền thực hiện thao tác này. |
| ✅ PASS | INSTRUCTOR | `PATCH` | `/instructor/lessons/{id}/preview` | 422 | Dữ liệu không hợp lệ. |
| ✅ PASS | INSTRUCTOR | `POST` | `/instructor/lessons/{id}/video` | 422 | Dữ liệu không hợp lệ. |
| ✅ PASS | INSTRUCTOR | `POST` | `/instructor/lessons/{id}/assets` | 422 | Dữ liệu không hợp lệ. |
| ✅ PASS | INSTRUCTOR | `POST` | `/instructor/courses/{id}/submit` | 403 | D盻ｯ li盻㎡ khﾃｴng h盻｣p l盻・ |
| ✅ PASS | INSTRUCTOR | `GET` | `/instructor/courses/{id}/review-notes` | 403 | D盻ｯ li盻㎡ khﾃｴng h盻｣p l盻・ |
| ✅ PASS | PUBLIC | `GET` | `/lessons/{id}/comments` | 401 | Unauthenticated. |
| ✅ PASS | PUBLIC | `POST` | `/lessons/{id}/comments` | 401 | Unauthenticated. |
| ✅ PASS | PUBLIC | `POST` | `/comments/{id}/replies` | 401 | Unauthenticated. |
| ✅ PASS | INSTRUCTOR | `POST` | `/instructor/course-announcements` | 422 | Dữ liệu không hợp lệ. |
| ✅ PASS | PUBLIC | `POST` | `/quizzes/{id}/attempts` | 401 | Unauthenticated. |
| ✅ PASS | PUBLIC | `POST` | `/orders` | 401 | Unauthenticated. |
| ✅ PASS | LEARNER | `POST` | `/orders/apply-coupon` | 422 | Thông tin order_id và coupon_code là bắt buộc. |
| ✅ PASS | PUBLIC | `POST` | `/payments` | 401 | Unauthenticated. |
| ✅ PASS | LEARNER | `GET` | `/orders/my` | 200 | Lấy danh sách đơn hàng thành công. |
| ✅ PASS | LEARNER | `GET` | `/orders/{id}` | 404 |  |
| ✅ PASS | PUBLIC | `POST` | `/payments/webhook` | 401 | Unauthenticated. |
| ✅ PASS | LEARNER | `GET` | `/wishlists` | 200 | Lấy danh sách khóa học yêu thích thành công. |
| ✅ PASS | LEARNER | `POST` | `/wishlists` | 422 | Dữ liệu không hợp lệ. |
| ✅ PASS | LEARNER | `DELETE` | `/wishlists/{courseId}` | 404 |  |
| ✅ PASS | INSTRUCTOR | `PATCH` | `/instructor/courses/{id}` | 422 | Dữ liệu không hợp lệ. |
| ✅ PASS | INSTRUCTOR | `GET` | `/instructor/coupons` | 200 | Lấy danh sách coupon thành công. |
| ✅ PASS | INSTRUCTOR | `POST` | `/instructor/coupons` | 422 | Dữ liệu không hợp lệ. |
| ✅ PASS | INSTRUCTOR | `GET` | `/instructor/coupons/{id}` | 404 |  |
| ✅ PASS | INSTRUCTOR | `PATCH` | `/instructor/coupons/{id}` | 404 |  |
| ✅ PASS | INSTRUCTOR | `DELETE` | `/instructor/coupons/{id}` | 404 |  |
| ✅ PASS | LEARNER | `GET` | `/me/courses` | 200 | L蘯･y danh sﾃ｡ch khﾃｳa h盻皇 ﾄ妥｣ mua thﾃnh cﾃｴng. |
| ✅ PASS | LEARNER | `GET` | `/learn/lessons/{id}` | 200 | Thao tﾃ｡c thﾃnh cﾃｴng |
| ✅ PASS | LEARNER | `GET` | `/learn/lessons/{id}/check-access` | 200 | Thao tﾃ｡c thﾃnh cﾃｴng |
| ✅ PASS | LEARNER | `GET` | `/learn/courses/{id}/outline` | 200 | L蘯･y l盻・trﾃｬnh khﾃｳa h盻皇 thﾃnh cﾃｴng |
| ✅ PASS | LEARNER | `PATCH` | `/learn/lessons/{id}/progress` | 422 | Dữ liệu không hợp lệ. |
| ✅ PASS | LEARNER | `GET` | `/learn/resume` | 200 | Thao tﾃ｡c thﾃnh cﾃｴng |
| ✅ PASS | LEARNER | `PATCH` | `/learn/lessons/{id}/complete` | 422 | Dữ liệu không hợp lệ. |
| ✅ PASS | LEARNER | `GET` | `/learn/courses/{id}/progress` | 200 | Thao tﾃ｡c thﾃnh cﾃｴng |
| ✅ PASS | LEARNER | `GET` | `/learning-logs/my` | 200 | Thao tﾃ｡c thﾃnh cﾃｴng |
| ✅ PASS | LEARNER | `GET` | `/learn/assets/{id}/download` | 200 | Thao tﾃ｡c thﾃnh cﾃｴng |
| ✅ PASS | LEARNER | `GET` | `/learn/lessons/{id}/next` | 200 | Thao tﾃ｡c thﾃnh cﾃｴng |
| ✅ PASS | ADMIN | `GET` | `/admin/users` | 200 | Lấy danh sách người dùng thành công. |
| ✅ PASS | ADMIN | `POST` | `/admin/users` | 422 | Dữ liệu không hợp lệ. |
| ✅ PASS | ADMIN | `GET` | `/admin/users/{id}` | 404 |  |
| ✅ PASS | ADMIN | `PUT` | `/admin/users/{id}` | 404 |  |
| ✅ PASS | ADMIN | `PATCH` | `/admin/users/{id}` | 404 |  |
| ✅ PASS | ADMIN | `DELETE` | `/admin/users/{id}` | 404 |  |
| ✅ PASS | ADMIN | `GET` | `/admin/roles` | 501 | Chức năng chưa sẵn sàng triển khai trong Sprint 1. |
| ✅ PASS | ADMIN | `POST` | `/admin/roles` | 501 | Chức năng chưa sẵn sàng triển khai trong Sprint 1. |
| ✅ PASS | ADMIN | `GET` | `/admin/roles/{id}` | 501 | Chức năng chưa sẵn sàng triển khai trong Sprint 1. |
| ✅ PASS | ADMIN | `PUT` | `/admin/roles/{id}` | 501 | Chức năng chưa sẵn sàng triển khai trong Sprint 1. |
| ✅ PASS | ADMIN | `PATCH` | `/admin/roles/{id}` | 501 | Chức năng chưa sẵn sàng triển khai trong Sprint 1. |
| ✅ PASS | ADMIN | `DELETE` | `/admin/roles/{id}` | 501 | Chức năng chưa sẵn sàng triển khai trong Sprint 1. |
| ✅ PASS | INSTRUCTOR | `GET` | `/instructor/profile` | 200 | Lấy hồ sơ giảng viên thành công. |
| ✅ PASS | INSTRUCTOR | `PATCH` | `/instructor/profile/account` | 422 | Dữ liệu không hợp lệ. |
| ✅ PASS | INSTRUCTOR | `PATCH` | `/instructor/profile/expertise` | 422 | Dữ liệu không hợp lệ. |
| ✅ PASS | INSTRUCTOR | `PATCH` | `/instructor/profile/introduction` | 422 | Dữ liệu không hợp lệ. |
| ✅ PASS | INSTRUCTOR | `GET` | `/instructor/profile/completion` | 200 | Lấy trạng thái hoàn thiện hồ sơ thành công. |
| ✅ PASS | INSTRUCTOR | `GET` | `/instructor/sections` | 200 | Thao tác thành công. |
| ✅ PASS | INSTRUCTOR | `POST` | `/instructor/sections` | 422 | Dữ liệu không hợp lệ. |
| ✅ PASS | INSTRUCTOR | `GET` | `/instructor/sections/{id}` | 403 | D盻ｯ li盻㎡ khﾃｴng h盻｣p l盻・ |
| ✅ PASS | INSTRUCTOR | `PUT` | `/instructor/sections/{id}` | 422 | Dữ liệu không hợp lệ. |
| ✅ PASS | INSTRUCTOR | `PATCH` | `/instructor/sections/{id}` | 422 | Dữ liệu không hợp lệ. |
| ✅ PASS | INSTRUCTOR | `DELETE` | `/instructor/sections/{id}` | 403 | D盻ｯ li盻㎡ khﾃｴng h盻｣p l盻・ |
| ✅ PASS | ADMIN | `GET` | `/admin/categories` | 200 | Lấy danh sách danh mục thành công. |
| ✅ PASS | ADMIN | `POST` | `/admin/categories` | 422 | Dữ liệu không hợp lệ. |
| ✅ PASS | ADMIN | `GET` | `/admin/categories/{id}` | 200 | Thao tác thành công |
| ✅ PASS | ADMIN | `PUT` | `/admin/categories/{id}` | 422 | Dữ liệu không hợp lệ. |
| ✅ PASS | ADMIN | `PATCH` | `/admin/categories/{id}` | 422 | Dữ liệu không hợp lệ. |
| ✅ PASS | ADMIN | `DELETE` | `/admin/categories/{id}` | 400 | Không thể xóa danh mục đang có khóa học liên kết. |
| ✅ PASS | ADMIN | `GET` | `/admin/courses` | 200 | Lấy danh sách khóa học thành công. |
| ✅ PASS | ADMIN | `GET` | `/admin/courses/{id}` | 200 | Thao tác thành công |
| ✅ PASS | ADMIN | `PATCH` | `/admin/courses/{id}` | 422 | Dữ liệu không hợp lệ. |
| ✅ PASS | INSTRUCTOR | `GET` | `/instructor/courses/{id}/learners` | 403 | Bạn không có quyền thực hiện thao tác này. |
| ✅ PASS | INSTRUCTOR | `GET` | `/instructor/revenue` | 200 | Thao tác thành công. |
| ✅ PASS | INSTRUCTOR | `GET` | `/instructor/quizzes` | 200 | Thao tác thành công. |
| ✅ PASS | INSTRUCTOR | `POST` | `/instructor/quizzes` | 422 | Thao tác thành công. |
| ✅ PASS | INSTRUCTOR | `GET` | `/instructor/quizzes/{id}` | 404 |  |
| ✅ PASS | INSTRUCTOR | `PUT` | `/instructor/quizzes/{id}` | 404 |  |
| ✅ PASS | INSTRUCTOR | `PATCH` | `/instructor/quizzes/{id}` | 404 |  |
| ✅ PASS | INSTRUCTOR | `DELETE` | `/instructor/quizzes/{id}` | 404 |  |
| ✅ PASS | INSTRUCTOR | `POST` | `/instructor/withdrawals` | 422 | Dữ liệu không hợp lệ. |
| ✅ PASS | PUBLIC | `GET` | `/quiz-attempts/{id}` | 401 | Unauthenticated. |
| ✅ PASS | PUBLIC | `GET` | `/courses/{id}/completion-status` | 401 | Unauthenticated. |
| ✅ PASS | INSTRUCTOR | `GET` | `/instructor/reports/completion-rate` | 200 | Lấy báo cáo tỷ lệ hoàn thành thành công. |
| ✅ PASS | INSTRUCTOR | `GET` | `/instructor/reports/inactive-learners` | 200 | Lấy danh sách học viên không hoạt động thành công. |
| ✅ PASS | INSTRUCTOR | `GET` | `/instructor/courses/{id}/dashboard` | 403 | Bạn không có quyền thực hiện thao tác này. |
| ✅ PASS | ADMIN | `GET` | `/admin/dashboard` | 200 | Lấy dashboard hệ thống thành công. |
| ✅ PASS | ADMIN | `GET` | `/admin/reports/top-courses` | 200 | Lấy báo cáo top khóa học thành công. |
| ✅ PASS | ADMIN | `GET` | `/admin/reports/instructors` | 200 | Lấy báo cáo top giảng viên thành công. |
| ✅ PASS | ADMIN | `GET` | `/admin/reports/revenue` | 200 | Lấy báo cáo doanh thu thành công. |
| ✅ PASS | AUTH | `POST` | `/auth/logout` | 200 | Đăng xuất thành công. |