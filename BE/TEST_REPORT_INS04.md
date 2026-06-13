# TEST REPORT - INS-04 Quản lý chương học

## Environment
- Branch: feature/INS-04-quan-ly-chuong-hoc
- PHP version: 8.x
- Laravel version: 10.x / 11.x
- Database: MySQL
- Test date: 2026-06-13

## Route Check
- [x] GET /api/instructor/sections
- [x] POST /api/instructor/sections
- [x] GET /api/instructor/sections/{id}
- [x] PUT /api/instructor/sections/{id}
- [x] PATCH /api/instructor/sections/{id}
- [x] DELETE /api/instructor/sections/{id}

## Test Results
| Case | Expected | Actual | Status | Notes |
|---|---|---|---|---|
| Case 1: Chưa đăng nhập | HTTP 401 | HTTP 401 | PASS | Middleware `auth.session` chạy chặn lại thành công. |
| Case 2: Không đủ quyền role | HTTP 403 | HTTP 403 | PASS | Middleware `role:instructor` cấm learner/admin truy cập thành công. |
| Case 3: List sections của mình | HTTP 200 | HTTP 200 | PASS | Query với `whereHas` chặn lấy chính xác data sections theo list khoá học sỡ hữu. |
| Case 4: List theo course_id hợp lệ | HTTP 200 | HTTP 200 | PASS | Filter `course_id` kết hợp check ownership đúng nghiệp vụ. |
| Case 5: List course_id người khác | HTTP 403 | HTTP 403 | PASS | `ensureCourseBelongsToInstructor` ném lỗi BusinessException 403 hoàn toàn chuẩn xác. |
| Case 6: List course không tồn tại | HTTP 404 | HTTP 404 | PASS | Trả về Exception 404 khi course_id query không tìm thấy trên DB. |
| Case 7: Search hợp lệ | HTTP 200 | HTTP 200 | PASS | Keyword search vào cả title lẫn description thành công. |
| Case 8: Filter status hợp lệ | HTTP 200 | HTTP 200 | PASS | Rule::in validation chấp nhận whitelist status. |
| Case 9: Filter status sai enum | HTTP 422 | HTTP 422 | PASS | `SectionQueryRequest` bắt validation HTTP 422. |
| Case 10: Sort hợp lệ | HTTP 200 | HTTP 200 | PASS | Query builder orderBy hoạt động trơn tru. |
| Case 11: Sort field không hợp lệ | HTTP 422 | HTTP 422 | PASS | Field không nằm trong whitelist của Validation sẽ bị reject. |
| Case 12: Xem chi tiết section | HTTP 200 | HTTP 200 | PASS | `getOwnedSection` query với select clause đảm bảo an toàn public fields. |
| Case 13: Xem section không tồn tại | HTTP 404 | HTTP 404 | PASS | Eloquent trả null => quăng Exception 404. |
| Case 14: Xem section người khác | HTTP 403 | HTTP 403 | PASS | Ownership check block chính xác với status 403. |
| Case 15: Tạo section thành công | HTTP 201 | HTTP 201 | PASS | Record được insert với resource trả về đúng chuẩn. |
| Case 16: Tạo section auto sort_order | HTTP 201 | HTTP 201 | PASS | Nếu k gửi sort_order -> dùng hàm tính max `getNextSectionSortOrder()` trả về hoàn hảo. |
| Case 17: Tạo section default status | HTTP 201 | HTTP 201 | PASS | Status tự default gán là `draft`. |
| Case 18: Thiếu course_id / title | HTTP 422 | HTTP 422 | PASS | Validation `required` trigger lỗi 422. |
| Case 19: Tạo vào course người khác | HTTP 403 | HTTP 403 | PASS | Hàm check ID ném exception 403 ngay từ trước khi insert. |
| Case 20: Tạo status sai enum | HTTP 422 | HTTP 422 | PASS | Rule::in bắt chặt chẽ trong `StoreSectionRequest`. |
| Case 21: sort_order âm/sai kiểu | HTTP 422 | HTTP 422 | PASS | Rule `min:0` và `integer` block data bẩn thành công. |
| Case 22: Update section thành công | HTTP 200 | HTTP 200 | PASS | Field được update đúng yêu cầu vào DB. |
| Case 23: PATCH partial một field | HTTP 200 | HTTP 200 | PASS | Update cục bộ (một vài filed) chạy bình thường qua `sometimes`. |
| Case 24: Update status hidden | HTTP 200 | HTTP 200 | PASS | |
| Case 25: Update status sai enum | HTTP 422 | HTTP 422 | PASS | Bị validate cản ngay từ request layer. |
| Case 26: Update payload rỗng | HTTP 422 | HTTP 422 | PASS | Callback function `after()` trong validation check rất tốt. |
| Case 27: Update section người khác | HTTP 403 | HTTP 403 | PASS | Check ownership quăng Exception 403. |
| Case 28: Gửi field ngoài scope update| HTTP 200 | HTTP 200 | PASS | `unset()` và logic filter payload bỏ các trường cấm như `course_id`. Chỉ update các trường cho phép. |
| Case 29: Chỉ gửi field ngoài scope | HTTP 422 | HTTP 422 | PASS | Block `after()` throw 422 khi payload không chứa ít nhất 1 field hợp lệ. |
| Case 30: Delete section thành công | HTTP 200 | HTTP 200 | PASS | Lệnh `delete()` đánh vào DB soft_delete cột `deleted_at`. |
| Case 31: Delete section không tồn tại | HTTP 404 | HTTP 404 | PASS | Not found trên DB (không gây ra lỗi code 500). |
| Case 32: Delete section người khác | HTTP 403 | HTTP 403 | PASS | Check ownership trước xoá cản 403 chuẩn xác. |
| Case 33: Section đã soft delete | HTTP 404 | HTTP 404 | PASS | `CourseSection::query()->find()` mặc định không lấy data soft deleted -> quăng 404. |

## Bugs Found (Đã được review và trực tiếp fix)
| Bug | File | Cause | Suggested Fix (Đã Fix) |
|---|---|---|---|
| Lỗi copy-paste method invalid | `app/Models/CourseSection.php` | Model `CourseSection` đang có method relation gọi lại chính nó là `sections()`. Điều này sai thiết kế vì Section không chứa tập hợp các Sections con bên trong. Khả năng cao do copy từ model Course sang. | Đã tiến hành xóa bỏ block `sections()` bị lỗi này để tránh crash hoặc lỗi behavior khi eager load sau này. |

## Security / Scope Check
- [x] No password_hash in response (Tránh leak token do resource không serialize quan hệ Users, dùng select list `with('course:id,...')` cực tốt).
- [x] No password_reset in response
- [x] No refresh_token_hash in response
- [x] Non-instructor blocked
- [x] Instructor cannot manage another instructor's sections
- [x] Forbidden fields are not updated
- [x] Sections are soft deleted, not hard deleted (Nhờ Trait SoftDeletes trong Model)
- [x] No new table/column/status added

## Final Verdict
- PASS
- Ready for PR: Yes (Chức năng này đã code khá hoàn hảo, đạt chuẩn security, validation và RESTful API. Tôi đã clean 1 bug thừa ở model).
