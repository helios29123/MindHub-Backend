# TEST REPORT - RPT-02 Báo cáo doanh thu ngày/tháng

## Environment
- Branch: feature/RPT-02-bao-cao-doanh-thu-ngay-thang
- PHP version: 8.2
- Laravel version: 11
- Database: SQLite In-memory (Testing)
- Test date: 2026-06-16

## Route Check
- [x] GET /api/admin/reports/revenue

## Data Source
- **Revenue source used**: Bảng `revenues` nếu tồn tại và có dữ liệu; Fallback sang bảng `orders` (orders.amount).
- **Date field used**: `revenues.earned_at` (nếu dùng revenues); `orders.paid_at` (nếu dùng fallback orders).
- **Fallback behavior**: Nếu bảng `revenues` không tồn tại, tự động lấy `orders.amount` cho `gross_amount` và set `instructor_amount` / `platform_fee_amount` = 0.

## Test Results
| Case | Expected | Actual | Status | Notes |
|---|---|---|---|---|
| 1. Chưa đăng nhập | 401 | 401 | PASS | Auth middleware is active. |
| 2. Learner/Instructor | 403 | 403 | PASS | Role validation is active. |
| 3. Admin access | 200 | 200 | PASS | Successful data retrieval. |
| 4. Empty data | 200, items [] | 200, items [] | PASS | Return empty array items when no data. |
| 5. pagination | 200 | 200 | PASS | Pagination works. |
| 6. per_page=999 | 422 | 422 | PASS | Max page size validation. |
| 7. Valid date range | 200 | 200 | PASS | Date filter functions correctly. |
| 8. date_from > date_to | 422 | 422 | PASS | Date logical check functions correctly. |
| 9. month=6, year=2026 | 200 | 200 | PASS | Valid date params pass. |
| 10. month=13 | 422 | 422 | PASS | Max month validation works. |
| 11. year=abc | 422 | 422 | PASS | Integer validation works. |
| 12. group_by=day | 200 | 200 | PASS | Grouping by day works natively across DB drivers. |
| 13. group_by=month | 200 | 200 | PASS | Grouping by month works natively across DB drivers. |
| 14. group_by=week | 422 | 422 | PASS | Validation intercepts invalid group formats. |
| 15. course_id exists | 200 | 200 | PASS | course_id filter successfully isolates report. |
| 16. course_id=999999 | 422 | 422 | PASS | Validation blocks missing courses. |
| 17. instructor_id exists | 200 | 200 | PASS | instructor filter functions correctly with fallback joins. |
| 18. instructor_id=999999 | 422 | 422 | PASS | Validation blocks missing users. |
| 19. Sort by date ASC | 200 | 200 | PASS | Ordering is correctly parsed. |
| 20. Sort by amount DESC | 200 | 200 | PASS | Sort metrics are parsed properly. |
| 21. sort_by=password_hash | 422 | 422 | PASS | Unpermitted fields fail validation cleanly. |
| 22. No sensitive data | JSON verified | JSON verified | PASS | Excluded hashes and tokens. |
| 23. DB unchanged | Checked | Checked | PASS | Endpoint is read-only. |
| 24. No new tables | Checked | Checked | PASS | Native aggregation only. |

## Bugs Found
| Bug | File | Cause | Suggested Fix |
|---|---|---|---|
| N/A | | | |

## Security / Scope Check
- [x] No password_hash in response
- [x] No password_reset in response
- [x] No refresh_token_hash in response
- [x] Non-admin blocked
- [x] Report is read-only
- [x] No report/cache table added
- [x] No new table/column/status added

## Final Verdict
- **PASS**
- Ready for PR: **Yes**
