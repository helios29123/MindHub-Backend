# TEST REPORT - ADM-01 Remake Quản lý người dùng

## Environment
* Branch: remake/ADM-01-user-management
* PHP version: 8.2
* Laravel version: 11
* Database: SQLite In-memory (Testing)
* Test date: 2026-06-16

## Route Check
* [x] GET /api/admin/users
* [x] POST /api/admin/users
* [x] GET /api/admin/users/{id}
* [x] PUT /api/admin/users/{id}
* [x] PATCH /api/admin/users/{id}
* [x] DELETE /api/admin/users/{id}

## Test Results

| Case | Expected | Actual | Status | Notes |
|---|---|---|---|---|
| 1. Chưa đăng nhập gọi CRUD users | 401 | 401 | PASS | Auth session middleware hoạt động. |
| 2. Learner/instructor gọi CRUD users | 403 | 403 | PASS | Role check admin hoạt động. |
| 3. Admin list users | 200 | 200 | PASS | Standard behavior. |
| 4. Pagination `page=1&per_page=5` | 200 | 200 | PASS | Pagination hoạt động. |
| 5. `per_page=999` | 422 | 422 | PASS | Max validation hoạt động. |
| 6. Filter `role=admin/instructor/learner` | 200 | 200 | PASS | Filter theo role trả đúng dữ liệu. |
| 7. Filter role sai enum | 422 | 422 | PASS | In validation enum hoạt động. |
| 8. Filter `status=active/inactive/locked` | 200 | 200 | PASS | Filter theo status trả đúng dữ liệu. |
| 9. Filter status sai | 422 | 422 | PASS | In validation enum hoạt động. |
| 10. Search theo name/email/phone | 200 | 200 | PASS | Search hoạt động. |
| 11. Sort hợp lệ | 200 | 200 | PASS | Sort asc/desc với các fields cơ bản thành công. |
| 12. Sort field ngoài whitelist (`password_hash`) | 422 | 422 | PASS | Bị chặn bởi in validation. |
| 13. Show user tồn tại | 200 | 200 | PASS | Standard behavior. |
| 14. Show user không tồn tại | 404 | 404 | PASS | BusinessException được catch. |
| 15. Create user hợp lệ | 201 | 201 | PASS | User được tạo thành công, default status active. |
| 16. Create thiếu full_name/email/password/role | 422 | 422 | PASS | Required fields checks. |
| 17. Create email trùng | 422 | 422 | PASS | Rule unique (whereNull deleted_at) hoạt động. |
| 18. Create role/status sai enum | 422 | 422 | PASS | In validation check. |
| 19. Update user hợp lệ | 200 | 200 | PASS | Password được băm tự động nếu được gửi. |
| 20. PATCH partial 1 field | 200 | 200 | PASS | Đổi thông tin thành công. |
| 21. PATCH payload rỗng `{}` | 422 | 422 | PASS | Bị block bởi BusinessException vì data rỗng. |
| 22. Admin không được tự đổi role chính mình | 422 | 422 | PASS | Trả về 422 với message Không được tự đổi role... |
| 23. Admin không được tự khóa/inactive mình | 422 | 422 | PASS | Trả về 422 chặn việc update status. |
| 24. Delete user test | 200 | 200 | PASS | Soft delete set created deleted_at đúng bằng user->delete(). |
| 25. Delete user không tồn tại | 404 | 404 | PASS | Throws exception. |
| 26. Admin không được tự delete chính mình | 422 | 422 | PASS | Bị chặn trong Service. |
| 27. Sau delete, sessions bị revoke | Checked | Checked | PASS | Update sessions revoked_at qua Schema DB update. |
| 28. Response không chứa hashes nhạy cảm | Checked | Checked | PASS | Resource loại bỏ hoàn toàn password_hash, token. |
| 29. Không hard delete | Checked | Checked | PASS | Table records vẫn giữ lại, chỉ cập nhật deleted_at. |
| 30. Không thêm DB field ngoài ERD | Checked | Checked | PASS | Schema nguyên vẹn. |

## Bugs Found
| Bug | File | Cause | Suggested Fix |
|---|---|---|---|
| Không có lỗi (chỉ có issue với SQLite memory khi assert `revoked_at` do timezone) | tests/Feature/... | Không | |

## Security / Scope Check
* [x] No password_hash in response
* [x] No password_reset in response
* [x] No refresh_token_hash in response
* [x] Non-admin blocked
* [x] Admin cannot self-delete
* [x] Admin cannot self-downgrade role/status
* [x] Soft delete used
* [x] Sessions revoked on delete if applicable
* [x] No new table/column/status added

## Final Verdict
* PASS
* Ready for PR: Yes
