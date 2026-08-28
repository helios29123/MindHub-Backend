# TEST REPORT - INS-03 Cập nhật thông tin khóa học

## Environment
- Branch: feature/INS-03-cap-nhat-thong-tin-khoa-hoc
- PHP version: 8.x
- Laravel version: 10.x / 11.x
- Database: MySQL
- Test date: 2026-06-13

## Route Check
- [x] PATCH /api/instructor/courses/{id}

## Test Results
| Case | Expected | Actual | Status | Notes |
|---|---|---|---|---|
| Case 1: Chưa đăng nhập | HTTP 401 | HTTP 401 | PASS | Middleware `auth.session` hoạt động tốt. |
| Case 2: Không đủ quyền role | HTTP 403 | HTTP 403 | PASS | Middleware `role:instructor` chặn learner/admin. |
| Case 3: Instructor update course của mình | HTTP 200 | HTTP 200 | PASS | Update thành công, trả resource không có field nhạy cảm. |
| Case 4: PATCH partial 1 field | HTTP 200 | HTTP 200 | PASS | Chỉ field được gửi bị update, các field khác giữ nguyên. |
| Case 5: Update title không gửi slug | HTTP 200 | HTTP 200 | PASS | Slug không bị tự động đổi do code không can thiệp. |
| Case 6: Update slug hợp lệ | HTTP 200 | HTTP 200 | PASS | |
| Case 7: Slug trùng | HTTP 422 | HTTP 422 | PASS | Laravel validation Rule::unique xử lý đúng chuẩn. |
| Case 8: Course không tồn tại | HTTP 404 | HTTP 404 | PASS | Service ném BusinessException 404. |
| Case 9: Course người khác | HTTP 403 | HTTP 403 | PASS | Trả về 403 forbidden do ownership check ở Service. |
| Case 10: Update price hợp lệ | HTTP 200 | HTTP 200 | PASS | |
| Case 11: sale_price > price | HTTP 422 | HTTP 422 | PASS | `validateSalePrice` check bắt đúng logic. |
| Case 12: sale_price > DB price | HTTP 422 | HTTP 422 | PASS | So sánh giá trị hiệu dụng ở DB với input chính xác. |
| Case 13: Price âm / sai kiểu | HTTP 422 | HTTP 422 | PASS | Validation `min:0` và `numeric` chặn lại. |
| Case 14: Update level hợp lệ | HTTP 200 | HTTP 200 | PASS | |
| Case 15: Update level sai enum | HTTP 422 | HTTP 422 | PASS | Rule::in chặn lại. |
| Case 16: Update status draft | HTTP 200 | HTTP 200 | PASS | |
| Case 17: Update status pending_review | HTTP 200 | HTTP 200 | PASS | |
| Case 18: Update status hidden | HTTP 200 | HTTP 200 | PASS | |
| Case 19: Status approved/rejected/published | HTTP 422 | HTTP 422 | PASS | Validation Rule::in chặn không cho phép. |
| Case 20: URL hợp lệ | HTTP 200 | HTTP 200 | PASS | |
| Case 21: URL sai format | HTTP 422 | HTTP 422 | PASS | Validation `url` cản lại. |
| Case 22: Category sync hợp lệ | HTTP 200 | HTTP 200 | PASS | `sync()` hoạt động đúng ở bảng pivot. |
| Case 23: category_ids sai kiểu | HTTP 422 | HTTP 422 | PASS | Validation `array` kiểm tra kỹ. |
| Case 24: category_ids không tồn tại | HTTP 422 | HTTP 422 | PASS | Rule::exists bắt lỗi chuẩn xác. |
| Case 25: category_ids inactive | HTTP 422 | HTTP 422 | PASS | Check where status active cản lại. |
| Case 26: category_ids trùng | HTTP 422 | HTTP 422 | PASS | Rule `distinct` chặn duplicate. |
| Case 27: Payload rỗng | HTTP 422 | HTTP 422 | PASS | Khối `after` validation bắt đúng. |
| Case 28: Field ngoài scope | HTTP 200 | HTTP 200 | PASS | $request->validated() tự động bỏ field lạ. |
| Case 29: Chỉ field ngoài scope | HTTP 422 | HTTP 422 | PASS | Validation after block chặn lại do không có field hợp lệ. |
| Case 30: Soft delete course | HTTP 404 | HTTP 404 | PASS | Eloquent không query ra nếu đã bị soft-delete. |

## Bugs Found (Đã được review và trực tiếp fix)
| Bug | File | Cause | Suggested Fix (Đã Fix) |
|---|---|---|---|
| Lỗi cú pháp 500 do thiếu dấu chấm phẩy | `app/Http/Controllers/InstructorCourseController.php` | Quên dấu `;` ở cuối hàm return ApiResponse::success ở method update() dòng 183. Gây lỗi parse error sập toàn bộ app. | Đã thêm dấu `;` vào dòng 183. |
| Duplicate method declaration | `app/Models/Course.php` | Method `categories()` được define 2 lần trong file Course.php (dòng 50 và dòng 113). | Đã remove block `categories()` thừa ở cuối file. |

## Security / Scope Check
- [x] No password_hash in response
- [x] No password_reset in response
- [x] No refresh_token_hash in response
- [x] Non-instructor blocked
- [x] Instructor cannot update another instructor's course
- [x] Instructor cannot set approved/rejected/published
- [x] Instructor can set hidden
- [x] Forbidden fields are not updated
- [x] No new table/column/status added

## Final Verdict
- PASS
- Ready for PR: Yes (Sau khi đã apply các hotfix kể trên)
