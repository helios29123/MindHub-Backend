# TEST REPORT - RPT-05 Dashboard từng khóa học cho giảng viên

## Environment
- Branch: feature/RPT-05-dashboard-tung-khoa-hoc-cho-giang-vien
- PHP version: 8.2
- Laravel version: 11
- Database: SQLite In-memory (Testing)
- Test date: 2026-06-16

## Route Check
- [x] GET /api/instructor/courses/{id}/dashboard

## Metric Source
- **Revenue source**: `revenues` table gross_amount. Fallback to `orders.amount` where status is `paid` if `revenues` doesn't exist.
- **Enrollment source**: `enrollments` table with status count check. Date filter uses `enrolled_at` if it exists, otherwise falls back to `created_at`.
- **Progress source**: `lesson_progress` table joined with `lessons` filtered by course id.
- **Metrics unavailable / fallback**: If table `lessons`, `lesson_progress`, `quiz_attempts`, or `revenues` do not exist, their related metrics gracefully fallback to returning `0` or `null`.

## Test Results
| Case | Expected | Actual | Status | Notes |
|---|---|---|---|---|
| 1. Chưa đăng nhập | 401 | 401 | PASS | Handled by auth.session middleware. |
| 2. Learner/Admin | 403 | 403 | PASS | Handled by role:instructor middleware. |
| 3. Instructor owns course | 200 | 200 | PASS | Standard data response returned. |
| 4. Instructor not owns course | 403 | 403 | PASS | AccessDeniedHttpException triggers correctly. |
| 5. Course not found | 404 | 404 | PASS | Handled missing course correctly. |
| 6. Course soft deleted | 404 | 404 | PASS | Validates `deleted_at` properly. |
| 7. Empty metric data | 200, 0 | 200, 0 | PASS | Returns zeroes effectively. |
| 8. Valid date_from/to | 200 | 200 | PASS | Date logical validations applied correctly. |
| 9. date_from > date_to | 422 | 422 | PASS | Input parameter validation triggered. |
| 10. month=6, year=2026 | 200 | 200 | PASS | Native aggregation filter works correctly. |
| 11. month=13 | 422 | 422 | PASS | Request constraint enforced. |
| 12. year=abc | 422 | 422 | PASS | Integer constraint enforced. |
| 13. Contains correct id | Checked | Checked | PASS | ID matches course queried. |
| 14. Course-specific metrics | Checked | Checked | PASS | Joins enforce filtering by targeted `course_id`. |
| 15. No sensitive data | JSON Verified | JSON Verified | PASS | Model leaks are prevented. |
| 16. DB unchanged | Checked | Checked | PASS | Report query is strictly read-only. |
| 17. No new tables/cache | Checked | Checked | PASS | Aggregates dynamically using `clone`. |
| 18. No schema mod | Checked | Checked | PASS | Follows strict ERD definitions only. |

## Bugs Found
| Bug | File | Cause | Suggested Fix |
|---|---|---|---|
| N/A | | | |

## Security / Scope Check
- [x] No password_hash in response
- [x] No password_reset in response
- [x] No refresh_token_hash in response
- [x] Non-instructor blocked
- [x] Instructor ownership enforced
- [x] Report is read-only
- [x] No report/cache table added
- [x] No new table/column/status added

## Final Verdict
- **PASS**
- Ready for PR: **Yes**
