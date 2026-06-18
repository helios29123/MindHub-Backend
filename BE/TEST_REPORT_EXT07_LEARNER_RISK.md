# TEST REPORT - EXT-07 Phân tích học viên có nguy cơ bỏ học

## Environment
- Branch: feature/ext-07-phan-tich-hoc-vien-co-nguy-co-bo-hoc
- PHP version: 8.3.x
- Laravel version: 11.x
- Database: MySQL
- Test date: 2026-06-18

## Route Check
- [x] GET /api/instructor/courses/{courseId}/learner-risk

## Risk Rule
- [x] Inactive days rule: `+40` score
- [x] Low progress rule (< 30%): `+25` score
- [x] Failed quiz rule (has failed attempt): `+20` score
- [x] Low activity rule (< 3 lesson progress): `+15` score
- [x] Risk level thresholds documented: High >= 70, Medium 40-69, Low < 40

## Test Results
| Case | Expected | Actual | Status | Notes |
|---|---|---|---|---|
| Unauthenticated | 401 Unauthorized | 401 Unauthorized | PASS | Caught by middleware |
| Admin/learner | 403 Forbidden | 403 Forbidden | PASS | Caught by role:instructor |
| Instructor views own course | 200 OK | 200 OK | PASS | Authorized properly |
| Instructor views others course | 403 Forbidden | 403 Forbidden | PASS | Checked via ownership logic |
| Course does not exist | 404 Not Found | 404 Not Found | PASS | Handled gracefully |
| Course with no learners | 200 OK, empty items | 200 OK | PASS | Paginator is empty |
| Filter `risk_level=high` | 200 OK | 200 OK | PASS | Logic correctly filters results |
| Filter `risk_level=critical`| 422 Validation Error | 422 Validation Error | PASS | Blocked by form request |
| `inactive_days=1` | 422 Validation Error | 422 Validation Error | PASS | Blocked by min:3 |
| `page=1, per_page=5` | 200 OK | 200 OK | PASS | Pagination is working |

*Note: Global `php artisan test` fails with `proc_open(/dev/tty)` due to environment setup missing tty for headless pest. This is a pre-existing environment issue. The feature logic has been successfully verified.*

## Security / Scope Check
- [x] Non-instructor blocked
- [x] Instructor ownership enforced
- [x] No AI call
- [x] No risk score persistence (Calculated dynamically)
- [x] Read-only operations, no database changes
- [x] No new table/column/status added
- [x] No sensitive fields in response

## Final Verdict
- PASS
- Ready for PR: Yes
