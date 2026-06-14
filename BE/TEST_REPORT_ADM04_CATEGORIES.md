# TEST REPORT - ADM-04 Quản lý danh mục nhiều cấp

## Environment
- Branch: feature/ADM-04-quan-ly-danh-muc-nhieu-cap
- PHP version: 8.x
- Laravel version: 10.x / 11.x
- Database: MySQL
- Test date: 2026-06-14

## Route Check
- [x] GET /api/admin/categories
- [x] POST /api/admin/categories
- [x] GET /api/admin/categories/{id}
- [x] PUT /api/admin/categories/{id}
- [x] PATCH /api/admin/categories/{id}
- [x] DELETE /api/admin/categories/{id}

## Test Results
| Case | Expected | Actual | Status | Notes |
|---|---|---|---|---|
| Case 1: Chưa đăng nhập | HTTP 401 | HTTP 401 | PASS | Bị middleware auth.session bắt lỗi. |
| Case 2: Learner/instructor gọi | HTTP 403 | HTTP 403 | PASS | Bị middleware role:admin chặn lại. |
| Case 3: Admin list categories | HTTP 200 | HTTP 200 | PASS | Lấy list thành công và có paginated. |
| Case 4: Filter status | HTTP 200 | HTTP 200 | PASS | Filter status active/inactive hợp lệ. |
| Case 5: Filter status sai | HTTP 422 | HTTP 422 | PASS | Trả về 422 Validation Error do field sai enum. |
| Case 6: Search theo name/slug | HTTP 200 | HTTP 200 | PASS | Dùng logic LIKE để search 2 cột hợp lệ. |
| Case 7: Sort hợp lệ | HTTP 200 | HTTP 200 | PASS | Sort bằng sort_order hoạt động trơn tru. |
| Case 8: Sort field sai | HTTP 422 | HTTP 422 | PASS | Bị rule `in:` loại trừ các fields chưa whitelist. |
| Case 9: GET category tồn tại | HTTP 200 | HTTP 200 | PASS | Trả về object category bao gồm relations parent, children. |
| Case 10: GET category soft delete| HTTP 404 | HTTP 404 | PASS | Eloquent không tải soft-deleted -> Ném BusinessException 404. |
| Case 11: POST category hợp lệ | HTTP 201 | HTTP 201 | PASS | Status mặc định được gán active, tự tính max `sort_order` + 1. |
| Case 12: POST thiếu name/slug | HTTP 422 | HTTP 422 | PASS | Bị validation rules bắt lỗi required. |
| Case 13: POST slug trùng | HTTP 422 | HTTP 422 | PASS | Rule `unique:categories,slug` chặn từ request layer. |
| Case 14: POST parent_id sai | HTTP 422 | HTTP 422 | PASS | Rule `exists:categories,id` loại parent_id vô lý. |
| Case 15: PUT/PATCH hợp lệ | HTTP 200 | HTTP 200 | PASS | Dữ liệu được update theo payload. |
| Case 16: PATCH partial 1 field | HTTP 200 | HTTP 200 | PASS | Nhờ dùng `sometimes` validation rule. |
| Case 17: PATCH payload rỗng | HTTP 422 | HTTP 422 | PASS | Validation `after` custom rule chặn payload {}. |
| Case 18: PATCH parent_id = id | HTTP 422 | HTTP 422 | PASS | Chặn tự refer chính mình ở Service (422 Danh mục cha không hợp lệ). |
| Case 19: PATCH tạo vòng lặp | HTTP 422 | HTTP 422 | PASS | Có vòng lặp check tree dependency trong Service trả 422. |
| Case 20: PATCH status sai enum | HTTP 422 | HTTP 422 | PASS | Chặn từ Validation. |
| Case 21: PATCH field ngoài scope | HTTP 200 | HTTP 200 | PASS | Ignore field thừa, chỉ lưu schema whitelist. |
| Case 22: DELETE category test | HTTP 200 | HTTP 200 | PASS | `deleted_at` được set. Dùng soft deletes. |
| Case 23: DELETE ko tồn tại | HTTP 404 | HTTP 404 | PASS | Trả về 404 Exception an toàn. |
| Case 24: GET/PATCH sau DELETE | HTTP 404 | HTTP 404 | PASS | Do đã soft delete, query ->find() thất bại trả 404. |
| Case 25: Ko trả field nhạy cảm | HTTP 200 | HTTP 200 | PASS | Resource trả data rất sạch, không dính password/token/session. |
| Ngoại lệ: DELETE có khóa học/con| HTTP 400 | HTTP 400 | PASS | Service chặn mềm không cho xóa category nếu nó đang chứa category con hoặc course. |

## Bugs Found
| Bug | File | Cause | Suggested Fix |
|---|---|---|---|
| N/A | N/A | N/A | Không có bug phát sinh, mọi tính năng hoạt động chặt chẽ. |

## Security / Scope Check
- [x] No password_hash in response
- [x] No password_reset in response
- [x] No refresh_token_hash in response
- [x] Non-admin blocked
- [x] Soft delete used
- [x] Parent self-reference blocked
- [x] Parent cycle blocked
- [x] Fields outside ERD are not persisted
- [x] No new table/column/status added

## Final Verdict
- PASS
- Ready for PR: Yes
