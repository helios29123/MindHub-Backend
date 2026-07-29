# REVENUE SHARE FINAL RETEST REPORT

## 1. Môi trường
- Branch: `feature/full-instructor-api-revenue-share`
- Commit: N/A (local modifications active)
- PHP: PHP 8.2+
- Laravel: 11
- DB status: Online (Testing DB `mindhub` and Dev DB `datn` fully configured)
- Time: 2026-07-20 21:26:00

## 2. Schema / Migration
- orders.sale_source: **OK** (Tồn tại)
- orders.commission_rule_id: **OK** (Tồn tại)
- revenues.sale_source: **OK** (Tồn tại)
- revenues.commission_rule_id: **OK** (Tồn tại)
- revenues.commission_rule_code: **OK** (Tồn tại)
- revenues.instructor_percent: **OK** (Tồn tại)
- revenues.platform_percent: **OK** (Tồn tại)

## 3. Commission Rules
| Rule | Instructor % | Platform % | Pass/Fail | Ghi chú |
|---|---:|---:|---|---|
| marketplace_default | - | - | **FAIL** | Chưa được tạo/thiếu trong DB seeder |
| marketplace | 37.00% | 63.00% | **FAIL** | Vẫn đang giữ tỷ lệ cũ và thiếu rule marketplace_default |
| platform_ads | 37.00% | 63.00% | **PASS** | |
| admin_campaign | 37.00% | 63.00% | **PASS** | |
| instructor_coupon | 97.00% | 3.00% | **PASS** | |
| instructor_referral | 97.00% | 3.00% | **PASS** | |

## 4. Test từng source
Kết quả chạy thực tế qua script chuẩn đoán:

| Source | Gross | Instructor % | Platform % | Instructor Amount | Platform Amount | Rule Code | Pass/Fail | Ghi chú |
|---|---:|---:|---:|---:|---:|---|---|---|
| marketplace_default | 500,000 | 37.00% | 63.00% | 185,000 | 315,000 | `marketplace` | **FAIL** | Tỷ lệ thực tế 37/63, kỳ vọng 70/30 |
| platform_ads | 500,000 | 37.00% | 63.00% | 185,000 | 315,000 | `platform_ads` | **PASS** | |
| admin_campaign | 500,000 | 37.00% | 63.00% | 185,000 | 315,000 | `admin_campaign` | **PASS** | |
| instructor_coupon | 500,000 | 97.00% | 3.00% | 485,000 | 15,000 | `instructor_coupon` | **PASS** | |
| admin coupon | 500,000 | 97.00% | 3.00% | 485,000 | 15,000 | `instructor_coupon` | **FAIL** | Đang bị tính nhầm thành `instructor_coupon` (97/3), kỳ vọng `admin_campaign` (37/63) |
| instructor_referral | 500,000 | 97.00% | 3.00% | 485,000 | 15,000 | `instructor_referral` | **PASS** | |
| invalid source | 500,000 | 70.00% | 30.00% | 350,000 | 150,000 | `default` | **FAIL** | Fallback ra rule code `default`, kỳ vọng `marketplace_default` |
| amount = 0 | 0 | 37.00% | 63.00% | 0 | 0 | `marketplace` | **PASS** | Không crash |

## 5. Duplicate callback
- Revenue count: **1**
- Pass/Fail: **PASS**

## 6. Withdrawable balance
- platform_ads balance: **PASS** (tăng đúng 185,000đ khi có doanh thu ads 500,000đ).
- instructor_coupon balance: **PASS** (tăng đúng 485,000đ khi có doanh thu coupon 500,000đ).
- pending withdrawal subtract: **PASS** (trừ chính xác số dư khi rút).
- Pass/Fail: **PASS**

## 7. API Revenue metadata
- sale_source: **FAIL** (chưa có trong response API).
- sale_source_label: **FAIL** (chưa có trong response API).
- commission_rule_code: **FAIL** (chưa có trong response API).
- instructor_percent: **FAIL** (chưa có trong response API).
- platform_percent: **FAIL** (chưa có trong response API).
- Summary đúng chưa: **Chưa** (Doanh thu tổng hợp đúng nhưng thiếu cấu trúc metadata breakdown của các sale source).

## 8. Regression route
- Instructor route đủ chưa: **Đủ**.
- Withdrawals map đúng chưa: **Đúng** (`InstructorWithdrawalController`).
- Q&A method còn thiếu không: **Không**.
- Có route quiz/certificate mới không: **Không**.

## 9. Lỗi còn lại
- **P0.1**: marketplace_default bị chia sai tỉ lệ (37/63 thay vì 70/30) do thiếu rule trong DB/seeder.
- **P0.2**: Coupon do admin tạo bị tính nhầm thành `instructor_coupon` (97/3 thay vì 37/63) do thiếu logic xác thực creator của coupon.
- **P0.3**: Nguồn không hợp lệ fallback về rule code `default` thay vì `marketplace_default`.
- **P2**: Response API doanh thu giảng viên chưa trả về các thông tin metadata mới liên quan tới `sale_source`, `instructor_percent`, `platform_percent`.

## 10. Kết luận
- Chia theo quảng cáo và các nguồn đơn hàng **chưa đạt** do các logic nghiệp vụ chưa được cập nhật.
- Chưa thể kết nối Frontend do dữ liệu chia sẻ doanh thu đang tính toán sai tỷ lệ cho coupon của admin và marketplace mặc định.
- Cần thực thi các sửa đổi đã nêu trong Implementation Plan nhằm cập nhật seeder, cập nhật coupon checking logic, và cập nhật resources định dạng cho API trước khi re-test lại.

---
**Folder report path:** `storage/revenue-share-final-retest-report/20260720_212446`  
**File report path:** `storage/revenue-share-final-retest-report/20260720_212446/REVENUE_SHARE_FINAL_RETEST_REPORT.md`  

### git status --short:
```
 M app/Http/Controllers/InstructorCourseController.php
 M app/Http/Controllers/InstructorWithdrawalController.php
 M app/Http/Controllers/InteractionController.php
 M app/Http/Controllers/ReportController.php
 M app/Http/Requests/Interaction/InstructorQuestionQueryRequest.php
 M app/Http/Resources/Instructor/InstructorPayoutAccountResource.php
 M app/Http/Resources/Interaction/InstructorQuestionResource.php
 M app/Http/Resources/Marketing/InstructorCouponResource.php
 M app/Models/InstructorProfile.php
 M app/Models/Order.php
 M app/Models/PayoutAccount.php
 M app/Models/User.php
 M app/Repositories/Instructor/InstructorWithdrawalRepository.php
 M app/Repositories/Interaction/InstructorQuestionRepository.php
 M app/Repositories/Marketing/MarketingCouponRepository.php
 M app/Services/Instructor/InstructorWithdrawalService.php
 M app/Services/Interaction/InstructorQuestionService.php
 M app/Services/Marketing/CouponService.php
 M app/Services/Payment/PaymentService.php
 M routes/api/instructor.php
 M routes/api/marketing.php
?? ../.vscode/
?? app/Http/Controllers/InstructorCouponController.php
?? app/Http/Controllers/InstructorNotificationController.php
?? app/Http/Controllers/InstructorPayoutAccountController.php
...
?? storage/revenue-share-final-retest-report/
```
