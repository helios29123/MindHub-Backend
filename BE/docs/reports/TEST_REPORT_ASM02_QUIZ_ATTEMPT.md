# TEST REPORT - ASM-02 Xem kết quả quiz/test

## Environment

* Branch: feature/ASM-02-xem-ket-qua-quiz-test
* PHP version: 8.2
* Laravel version: 11
* Database: SQLite In-memory (Testing)
* Test date: 2026-06-16

## Route Check

* [x] GET /api/quiz-attempts/{id}

## Data / Rule Notes

* Enrollment status values used: `active`, `completed`.
* Quiz/course/lesson availability rule: Checked for `published` status correctly.
* In-progress attempt behavior: Return user's answers and selections, but `is_correct`, `score_earned`, and `correct_option_id` are explicitly omitted if `status` != `submitted`.
* Correct answer visibility rule: Enforced via `QuizAttemptResource`, strictly limits answers visibility when the quiz is not yet submitted.

## Test Results

| Case | Expected | Actual | Status | Notes |
| ---- | -------- | ------ | ------ | ----- |
| 1. Chưa đăng nhập gọi endpoint | 401 | 401 | PASS | Auth middleware intercepts correctly. |
| 2. Admin/instructor gọi route learner | 403 | 403 | PASS | Role check middleware prevents access. |
| 3. Learner xem attempt của chính mình | 200 | 200 | PASS | Standard behavior works. |
| 4. Learner xem attempt người khác | 403 | 403 | PASS | Service throws AccessDeniedHttpException. |
| 5. Attempt không tồn tại | 404 | 404 | PASS | Service throws NotFoundHttpException. |
| 6. Learner chưa enrollment course chứa quiz | 403 | 403 | PASS | Enforced access control via Enrollments. |
| 7. Quiz hidden/draft | 403 | 403 | PASS | AccessDeniedHttpException returned, no 500. |
| 8. Course hidden/draft | 403 | 403 | PASS | AccessDeniedHttpException returned, no 500. |
| 9. Lesson hidden/draft | 403 | 403 | PASS | AccessDeniedHttpException returned, no 500. |
| 10. Attempt submitted trả đầy đủ metrics | 200 | 200 | PASS | Score, total_score, passed are mapped securely. |
| 11. Attempt submitted trả is_correct/score | 200 | 200 | PASS | Answer payload includes full scoring data. |
| 12. Attempt in_progress leak check | 200 | 200 | PASS | Returns data but securely omits `is_correct` and correct options. |
| 13. Path id sai format (/abc) | 404 | 404 | PASS | Handled naturally via Laravel router `->where('id', '[0-9]+')`. |
| 14. Response validation | Checked | Checked | PASS | Excludes sensitive password/token hashes. |
| 15. Không DB Mutation | Checked | Checked | PASS | Method is purely read-only via GET. |
| 16. Schema Integrity | Checked | Checked | PASS | No new tables/columns added outside ERD. |

## Bugs Found

| Bug | File | Cause | Suggested Fix |
| --- | ---- | ----- | ------------- |
| N/A | | | |

## Security / Scope Check

* [x] No password_hash in response
* [x] No password_reset in response
* [x] No refresh_token_hash in response
* [x] Non-learner blocked
* [x] Learner can only view own attempt
* [x] Enrollment access checked
* [x] Hidden/draft content blocked
* [x] In-progress attempt does not leak correct answers
* [x] Read-only, no DB mutation
* [x] No new table/column/status added

## Final Verdict

* PASS
* Ready for PR: Yes
