# TEST REPORT - INS-12 Quản lý học viên theo khóa

## Environment
- Branch: feature/INS-12-quan-ly-hoc-vien-theo-khoa
- PHP version: 8.2
- Laravel version: 11
- Database: SQLite In-memory (Testing)
- Test date: 2026-06-16

## Route Check
- [x] GET /api/instructor/courses/{id}/learners

## Data / Metric Notes
- **Enrollment status values used**: dynamic based on query param (validates `active`, `completed`, `cancelled`, `expired` based on request whitelist).
- **Progress percent behavior**: Calculate `progress_percent` internally using DB subquery if tables `lessons` and `lesson_progress` exist, returning `(completed_lessons / total_lessons) * 100` dynamically, falling back to 0. If relation is missing, omits the field without crashing.
- **Pagination behavior**: Native `->paginate($perPage)` from Laravel matching exactly standard API behavior (per_page limit of 100). Returns `ApiResponse::paginated`.

## Test Results
| Case | Expected | Actual | Status | Notes |
|---|---|---|---|---|
| 1. Chưa đăng nhập | 401 | 401 | PASS | Auth middleware is active. |
| 2. Learner/Admin access | 403 | 403 | PASS | Role check intercepts correctly. |
| 3. Instructor owns course | 200 | 200 | PASS | Standard paginated learner list is retrieved. |
| 4. Instructor not owns course | 403 | 403 | PASS | AccessDeniedHttpException triggers correctly. |
| 5. Course not found | 404 | 404 | PASS | Returns HTTP 404 correctly. |
| 6. Course soft deleted | 404 | 404 | PASS | `deleted_at` effectively filters out soft deleted courses. |
| 7. Empty enrollments | 200, empty array | 200, empty array | PASS | Returns standard paginator with 0 records. |
| 8. pagination per_page | 200 | 200 | PASS | Pagination works optimally natively. |
| 9. per_page=999 | 422 | 422 | PASS | Input validation limits max items correctly. |
| 10. Valid status | 200 | 200 | PASS | Filters out learners correctly. |
| 11. status=archived | 422 | 422 | PASS | Enum status constraint intercepts invalid status. |
| 12. search full_name | 200 | 200 | PASS | Wildcard string search executed properly. |
| 13. search email | 200 | 200 | PASS | Wildcard matched properly. |
| 14. sort_by whitelist | 200 | 200 | PASS | Native OrderBy applies securely based on map. |
| 15. sort_by=password_hash | 422 | 422 | PASS | Validation intercepts unallowed fields cleanly. |
| 16. Learner isolation | Checked | Checked | PASS | Restricts join directly to requested course_id only. |
| 17. No sensitive data | JSON verified | JSON verified | PASS | Excluded hashes / sensitive arrays effectively via Resource. |
| 18. DB unchanged | Checked | Checked | PASS | Strictly read-only API method. |
| 19. No new tables/statuses | Checked | Checked | PASS | Standard database relations strictly followed. |
| 20. No cache table | Checked | Checked | PASS | Used native subquery instead of caching/storing. |

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
- [x] Only learners of the selected course are returned
- [x] Report/list is read-only
- [x] No report/cache table added
- [x] No new table/column/status added

## Final Verdict
- **PASS**
- Ready for PR: **Yes**
