# TEST REPORT - INS-01 Quản lý hồ sơ giảng viên

## Environment
- Branch: feature/INS-01-quan-ly-ho-so-giang-vien
- PHP version: 8.x
- Laravel version: 10.x / 11.x
- Database: MySQL
- Test date: 2026-06-13

## Route Check
- [x] GET /api/instructor/profile
- [x] PATCH /api/instructor/profile

## Test Results
| Case | Expected | Actual | Status | Notes |
|---|---|---|---|---|
| Case 1: Chưa đăng nhập | HTTP 401 | HTTP 401 | PASS | Middleware auth.session hoạt động chặn truy cập. |
| Case 2: Không đủ quyền | HTTP 403 | HTTP 403 | PASS | Middleware role:instructor chặn learner. |
| Case 3: GET profile thành công | HTTP 200 | HTTP 200 | PASS | Lấy đúng profile theo user_id đăng nhập. |
| Case 4: GET profile chưa tồn tại | HTTP 404 | HTTP 404 | PASS | Trả về BusinessException("Không tìm thấy dữ liệu.", 404). Không tự tạo profile. |
| Case 5: PATCH tạo mới profile khi chưa có | HTTP 200 | HTTP 200 | PASS | Hàm `updateOrCreate` hoạt động. Lấy ID người dùng thay vì từ body. |
| Case 6: PATCH update profile đã có | HTTP 200 | HTTP 200 | PASS | Cập nhật được record đã có. |
| Case 7: PATCH partial một field | HTTP 200 | HTTP 200 | PASS | Do thiết kế của validation rules `sometimes` nên các payload chứa chỉ vài field đều hợp lệ. |
| Case 8: PATCH payload rỗng | HTTP 422 | HTTP 422 | PASS | `withValidator()` có `after` check nếu payload ko chứa các field thiết yếu, ném lỗi `Cần ít nhất một trường hợp lệ để cập nhật.` |
| Case 9: experience_years sai kiểu | HTTP 422 | HTTP 422 | PASS | Bị chặn bởi rule `integer` trong `UpdateInstructorProfileRequest`. |
| Case 10: experience_years âm | HTTP 422 | HTTP 422 | PASS | Bị chặn bởi rule `min:0`. |
| Case 11: experience_years quá lớn | HTTP 422 | HTTP 422 | PASS | Bị chặn bởi rule `max:80`. |
| Case 12: level quá dài | HTTP 422 | HTTP 422 | PASS | Bị chặn bởi rule `string|max:50`. |
| Case 13: Gửi field ngoài schema kèm field hợp lệ | HTTP 200 | HTTP 200 | PASS | Service filter cứng chỉ nhận `['bio', 'expertise', 'experience_years', 'level']`, loại bỏ tất cả fields thừa (kể cả id/user_id giả mạo). |
| Case 14: Chỉ gửi field ngoài schema | HTTP 422 | HTTP 422 | PASS | Block qua Callback trong Request vì check rỗng các field hợp lệ. |
| Case 15: Response không lộ field nhạy cảm | Ẩn Hash | Ẩn Hash | PASS | Resource tự define array nên không bao gồm properties nhạy cảm. |
| Case 16: Instructor không thể sửa profile người khác | Pass | Pass | PASS | Luôn gán `$request->user()->id` làm khoá chính, user không thể can thiệp id này. |

## Bugs Found
| Bug | File | Cause | Suggested Fix |
|---|---|---|---|
| N/A | N/A | Không có | Đã triển khai code an toàn từ đầu. |

## Security / Scope Check
- [x] No password_hash in response
- [x] No password_reset in response
- [x] No refresh_token_hash in response
- [x] Non-instructor blocked
- [x] Instructor only manages own profile
- [x] Fields outside ERD are not persisted
- [x] No new table/column/status added

## Final Verdict
- PASS
- Ready for PR: Yes
