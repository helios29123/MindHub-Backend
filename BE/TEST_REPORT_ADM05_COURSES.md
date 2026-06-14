# TEST REPORT - ADM-05 Quản lý khóa học toàn hệ thống

## Environment
- Branch: feature/ADM-05-quan-ly-khoa-hoc-toan-he-thong
- PHP version: 8.x
- Laravel version: 10.x / 11.x
- Database: MySQL
- Test date: 2026-06-14

## Route Check
- [x] GET /api/admin/courses
- [x] GET /api/admin/courses/{id}
- [x] PATCH /api/admin/courses/{id}

## Test Results
| Case | Expected | Actual | Status | Notes |
|---|---|---|---|---|
| Case 1: Chưa đăng nhập GET/PATCH | HTTP 401 | HTTP 401 | PASS | Middleware `auth.session` chặn. |
| Case 2: Learner/instructor gọi | HTTP 403 | HTTP 403 | PASS | Middleware `role:admin` chặn. |
| Case 3: Admin GET courses | HTTP 200 | HTTP 200 | PASS | Lấy danh sách toàn hệ thống thành công (paginated). |
| Case 4: Filter status | HTTP 200 | HTTP 200 | PASS | Filter theo các status: draft, pending_review, approved, rejected, published, hidden đều chạy đúng. |
| Case 5: Filter status sai (archived) | HTTP 422 | HTTP 422 | PASS | Validate `in:` chặn lỗi. |
| Case 6: Filter instructor_id | HTTP 200 | HTTP 200 | PASS | Chỉ trả về course của giảng viên tương ứng. |
| Case 7: Filter category_id | HTTP 200 | HTTP 200 | PASS | `whereHas` query filter đúng danh mục liên kết. |
| Case 8: Filter level | HTTP 200 | HTTP 200 | PASS | Filter theo beginner, intermediate, advanced, all_levels. |
| Case 9: Search theo title/slug/desc | HTTP 200 | HTTP 200 | PASS | Query LIKE hoạt động trơn tru. |
| Case 10: Sort hợp lệ | HTTP 200 | HTTP 200 | PASS | Sort bằng `created_at` desc hợp lệ. |
| Case 11: Sort field sai | HTTP 422 | HTTP 422 | PASS | Bị chặn qua rules query. |
| Case 12: GET course tồn tại | HTTP 200 | HTTP 200 | PASS | Load đầy đủ instructor và categories. |
| Case 13: GET course soft-deleted | HTTP 404 | HTTP 404 | PASS | Eloquent không tìm thấy dữ liệu bị xoá mềm. |
| Case 14: PATCH status approved | HTTP 200 | HTTP 200 | PASS | Trạng thái chuyển đổi thành công. |
| Case 15: PATCH status rejected | HTTP 200 | HTTP 200 | PASS | Nhận lý do `admin_reject_reason`. |
| Case 16: PATCH status published | HTTP 200 | HTTP 200 | PASS | Tự động gán `published_at = now()` nếu trước đó null. |
| Case 17: PATCH status sai enum | HTTP 422 | HTTP 422 | PASS | Validation chặn ngay từ request. |
| Case 18: PATCH is_featured | HTTP 200 | HTTP 200 | PASS | Cập nhật giá trị true/false. |
| Case 19: PATCH slug hợp lệ | HTTP 200 | HTTP 200 | PASS | Slug được thay đổi và unique. |
| Case 20: PATCH slug trùng | HTTP 422 | HTTP 422 | PASS | Rule `unique:courses,slug` trả 422. Không update DB. |
| Case 21: PATCH price hợp lệ | HTTP 200 | HTTP 200 | PASS | Dữ liệu `price` và `sale_price` thay đổi. |
| Case 22: PATCH sale_price > price | HTTP 422 | HTTP 422 | PASS | Service bắt Exception `Giá khuyến mãi không được lớn hơn giá gốc.` (status 422). |
| Case 23: PATCH sale_price > DB price| HTTP 422 | HTTP 422 | PASS | Tính logic effective price thành công. Dùng giá cũ trong DB để đối chiếu giá mới đẩy lên. |
| Case 24: PATCH payload rỗng {} | HTTP 422 | HTTP 422 | PASS | Rule `after` trong FormRequest chặn lại. |
| Case 25: PATCH field ngoài + đúng | HTTP 200 | HTTP 200 | PASS | Chỉ có các field khai báo trong `allowedFields` mới cập nhật. Ignore field thừa. |
| Case 26: PATCH chỉ field ngoài scope | HTTP 422 | HTTP 422 | PASS | Logic `after` kiểm tra mảng allowed keys, chặn do empty hợp lệ. |
| Case 27: Response lộ field user | Không có | Không có | PASS | Resource chặn toàn bộ thông tin nhạy cảm của bảng users (password_hash, v.v). |
| Case 28: Endpoint cấm tạo/xóa | HTTP 405 | HTTP 405 | PASS | Chỉ triển khai GET, GET ID, PATCH ID. |

## Bugs Found
| Bug | File | Cause | Suggested Fix |
|---|---|---|---|
| N/A | N/A | N/A | N/A |

## Security / Scope Check
- [x] No password_hash in response
- [x] No password_reset in response
- [x] No refresh_token_hash in response
- [x] Non-admin blocked
- [x] Admin can manage courses across instructors
- [x] Invalid course status rejected
- [x] Fields outside ERD are not persisted
- [x] No new table/column/status added
- [x] No hard delete implemented for this task

## Final Verdict
- PASS
- Ready for PR: Yes
