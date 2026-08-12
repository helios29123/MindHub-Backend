# MindHub Credit Flow API Test Result

- Generated at: 2026-06-29 19:03:21
- BaseUrl: http://127.0.0.1:8000/api

## Summary

| Total | PASS | FAIL | OPTIONAL |
|---:|---:|---:|---:|
| 39 | 37 | 0 | 2 |

## AUTH

| Result | Case | Method | Path | Expected | Got |
|---|---|---|---|---:|---:|
| PASS | Login admin | POST | /auth/login | 200 | 200 |
| PASS | Login instructor | POST | /auth/login | 200 | 200 |
| PASS | Login learner | POST | /auth/login | 200 | 200 |

## SECURITY

| Result | Case | Method | Path | Expected | Got |
|---|---|---|---|---:|---:|
| PASS | No token calls admin credit packages should be 401 | GET | /admin/credit-packages | 401 | 401 |
| PASS | Instructor calls admin credit packages should be 403 | GET | /admin/credit-packages | 403 | 403 |
| PASS | Learner calls instructor balance should be 403 | GET | /instructor/course-credits | 403 | 403 |

## ADMIN_PACKAGE

| Result | Case | Method | Path | Expected | Got |
|---|---|---|---|---:|---:|
| PASS | Admin creates valid credit package | POST | /admin/credit-packages | 200,201 | 201 |
| PASS | Admin creates package with credits zero should be 422 | POST | /admin/credit-packages | 422 | 422 |
| PASS | Admin creates package without name should be 422 | POST | /admin/credit-packages | 422 | 422 |
| PASS | Admin creates package with invalid status should be 422 | POST | /admin/credit-packages | 422 | 422 |
| PASS | Admin updates valid credit package | PATCH | /admin/credit-packages/9 | 200 | 200 |
| PASS | Admin updates package with negative credits should be 422 | PATCH | /admin/credit-packages/9 | 422 | 422 |

## INSTRUCTOR_CREDIT

| Result | Case | Method | Path | Expected | Got |
|---|---|---|---|---:|---:|
| PASS | Instructor lists active credit packages | GET | /instructor/credit-packages | 200 | 200 |
| PASS | Instructor gets current credit balance | GET | /instructor/course-credits | 200 | 200 |
| PASS | Instructor creates credit order without package id should be 422 | POST | /instructor/credit-orders | 422 | 422 |
| PASS | Instructor creates credit order with missing package should be 422 or 404 | POST | /instructor/credit-orders | 404,422 | 422 |
| PASS | Instructor creates valid credit package order | POST | /instructor/credit-orders | 200,201 | 201 |

## PAYMENT_OPTIONAL

| Result | Case | Method | Path | Expected | Got |
|---|---|---|---|---:|---:|
| SKIP_OR_OPTIONAL | Create VNPAY URL for instructor credit order if route exists | POST | /payments/vnpay/create | 200,201 | 403 |

## ADMIN_INSTRUCTOR_CREDIT

| Result | Case | Method | Path | Expected | Got |
|---|---|---|---|---:|---:|
| PASS | Admin manually adds 5 credits to instructor for approve test | POST | /admin/instructors/3/credits/adjust | 200,201 | 200 |
| PASS | Admin adjusts credits zero should be 422 | POST | /admin/instructors/3/credits/adjust | 422 | 422 |
| PASS | Admin views instructor credit balance | GET | /admin/instructors/3/credits | 200 | 200 |
| PASS | Admin views instructor credit transactions | GET | /admin/instructors/3/credit-transactions | 200 | 200 |
| PASS | Admin views missing instructor credit should be 404 or 200 | GET | /admin/instructors/999999999/credits | 200,404 | 404 |

## ADMIN_COURSE_APPROVAL

| Result | Case | Method | Path | Expected | Got |
|---|---|---|---|---:|---:|
| PASS | Approve missing course should be 404 | PATCH | /admin/courses/999999999/approve | 404,422 | 404 |
| PASS | Instructor calls approve course should be 403 | PATCH | /admin/courses/999999999/approve | 403 | 403 |
| PASS | Learner calls approve course should be 403 | PATCH | /admin/courses/999999999/approve | 403 | 403 |
| PASS | Admin approves pending review course and deducts 1 credit | PATCH | /admin/courses/31/approve | 200 | 200 |
| PASS | Approve same course again should not deduct second time | PATCH | /admin/courses/31/approve | 200,400,409 | 400 |
| PASS | Check credit balance after approve | GET | /admin/instructors/3/credits | 200 | 200 |
| PASS | Admin rejects course and does not deduct credit | PATCH | /admin/courses/32/reject | 200 | 200 |
| PASS | Check credit balance after reject | GET | /admin/instructors/3/credits | 200 | 200 |
| PASS | Admin approves course without enough credits should fail | PATCH | /admin/courses/34/approve | 400,409 | 409 |

## COURSE_PURCHASE

| Result | Case | Method | Path | Expected | Got |
|---|---|---|---|---:|---:|
| PASS | Create course order without token should be 401 | POST | /orders | 401 | 401 |
| PASS | Learner buys missing course should be 404 | POST | /orders | 404,422 | 422 |
| PASS | Create course order without course_id should be 422 | POST | /orders | 422 | 422 |
| PASS | Learner creates valid order for published course | POST | /orders | 200,201,409 | 201 |
| PASS | Instructor cannot buy own course | POST | /orders | 400,403,409 | 403 |

## CATALOG

| Result | Case | Method | Path | Expected | Got |
|---|---|---|---|---:|---:|
| PASS | Public search should only return published courses from active unlocked instructors | GET | /courses?per_page=5 | 200 | 200 |
| SKIP_OR_OPTIONAL | Suggestions should hide empty categories and locked instructor courses | GET | /catalog/suggestions?keyword=a&limit=10 | 200 | 404 |

## Error Details

### SKIP_OR_OPTIONAL - Create VNPAY URL for instructor credit order if route exists

- Method: POST
- Path: /payments/vnpay/create
- Expected: 200,201
- Got: 403

Response:

{"success":false,"message":"B\u1ea1n kh\u00f4ng c\u00f3 quy\u1ec1n th\u1ef1c hi\u1ec7n thao t\u00e1c n\u00e0y.","errors":[]}

### SKIP_OR_OPTIONAL - Suggestions should hide empty categories and locked instructor courses

- Method: GET
- Path: /catalog/suggestions?keyword=a&limit=10
- Expected: 200
- Got: 404

Response:

{"success":false,"message":"Kh\u00f4ng t\u00ecm th\u1ea5y d\u1eef li\u1ec7u.","errors":[]}

