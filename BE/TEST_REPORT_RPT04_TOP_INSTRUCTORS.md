# TEST REPORT - RPT-04 Báo cáo giảng viên hoạt động tốt

## Environment
- Branch: feature/RPT-04-bao-cao-giang-vien-hoat-dong-tot
- PHP version: 8.4.21
- Laravel version: 11.x
- Database: MySQL (Testing with SQLite in memory)
- Test date: 2026-06-15

## Route Check
- [x] GET /api/admin/reports/instructors

## Test Results
| Case | Expected | Actual | Status | Notes |
|---|---|---|---|---|
| Chưa đăng nhập | 401 | 401 | PASS | Verified via PHPUnit |
| Learner/Instructor gọi | 403 | 403 | PASS | Verified via PHPUnit |
| Admin gọi | 200 | 200 | PASS | Verified via PHPUnit |
| Không có dữ liệu | 200, items metric 0 | 200, items metric 0 | PASS | Verified via PHPUnit |
| page=1&per_page=5 | 200, 5 items | 200, 5 items | PASS | Verified via PHPUnit |
| per_page=999 | 422 | 422 | PASS | Verified via PHPUnit |
| date_from / date_to hợp lệ | 200 | 200 | PASS | Verified via PHPUnit |
| date_from > date_to | 422 | 422 | PASS | Verified via PHPUnit |
| course_id tồn tại | 200 | 200 | PASS | Verified via PHPUnit |
| course_id không tồn tại | 422 | 422 | PASS | Verified via PHPUnit |
| month, year hợp lệ | 200 | 200 | PASS | Filter applied on orders & enrollments |
| month không hợp lệ | 422 | 422 | PASS | Filter validation |
| year không hợp lệ | 422 | 422 | PASS | Filter validation |
| Sort hợp lệ | 200 | 200 | PASS | Default sort applied |
| Sort không hợp lệ | 422 | 422 | PASS | Filter validation |

## Bugs Found
| Bug | File | Cause | Suggested Fix |
|---|---|---|---|
| Login trả về 500 do lỗi session | `app/Models/AuthSession.php` | Lỗi cũ: Model mapping tới bảng `sessions` thay vì `auth_sessions` | Lỗi ngoài scope nên dùng PHPUnit bypass và mock schema cho tests. |
| Thiếu bảng revenues | N/A | Bảng `revenues` chưa được migrate trên branch hiện tại | Sử dụng bảng `orders` (orders.amount, orders.status = 'paid') để thay thế tính toán doanh thu (total_revenue, total_sold) cho chính xác theo yêu cầu dự án. |

## Security / Scope Check
- [x] No password_hash in response
- [x] No password_reset in response
- [x] No refresh_token_hash in response
- [x] Non-admin blocked
- [x] Report is read-only
- [x] No report/cache table added
- [x] No new table/column/status added

## Final Verdict
- PASS
- Ready for PR: Yes
