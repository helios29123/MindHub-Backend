# REVENUE SHARE SOURCE RETEST REPORT

## 1. Môi trường
- Branch: `feature/full-instructor-api-revenue-share`
- Commit: N/A (local modifications active)
- PHP: PHP 8.2+
- Laravel: 11
- DB status: Online (Testing DB `mindhub` and Dev DB `datn` fully configured)
- Time: 2026-07-20 21:14:00

## 2. Migration / Schema
Tất cả các cột yêu cầu đều tồn tại đầy đủ và chạy đúng:
- orders.sale_source: **OK**
- orders.commission_rule_id: **OK**
- revenues.sale_source: **OK**
- revenues.commission_rule_id: **OK**
- revenues.commission_rule_code: **OK**
- revenues.instructor_percent: **OK**
- revenues.platform_percent: **OK**

## 3. Commission Rules
Bảng `commission_rules` được seed bởi `CommissionRuleSeeder`:
- marketplace_default: **THIẾU** (hiện tại trong DB chỉ có rule `marketplace` với tỷ lệ sai 37/63)
- platform_ads: **OK** (37/63)
- admin_campaign: **OK** (37/63)
- instructor_coupon: **OK** (97/3)
- instructor_referral: **OK** (97/3)
- Seeder chạy lại có duplicate không: **Không** (Seeder sử dụng `updateOrCreate` nên idempotent thành công).

## 4. PaymentService / RevenueShareService
- PaymentService còn hardcode 100/0 không: **Không**. Logic chia tiền được ủy thác hoàn toàn cho `RevenueShareService::createRevenueForPaidOrder`.
- RevenueShareService có resolveSaleSource không: **Có**, nhưng chưa đầy đủ. Chưa phân biệt được nguồn coupon của admin hay giảng viên tạo.
- RevenueShareService có resolveCommissionRule không: **Có**, thực hiện query theo `sale_channel` trong DB.
- RevenueShareService có chống duplicate order_id không: **Có**, kiểm tra sự tồn tại của `order_id` trước khi tạo record.

## 5. Test kết quả từng source
Kết quả chạy thử nghiệm qua script chuẩn đoán:

| Source | Gross | Instructor % | Platform % | Instructor Amount | Platform Amount | Pass/Fail | Ghi chú |
|---|---:|---:|---:|---:|---:|---|---|
| CASE 1: marketplace_default | 500,000 | 37.00% | 63.00% | 185,000 | 315,000 | **FAIL** | Kỳ vọng tỷ lệ 70/30 |
| CASE 2: platform_ads | 500,000 | 37.00% | 63.00% | 185,000 | 315,000 | **PASS** | |
| CASE 3: admin_campaign | 500,000 | 37.00% | 63.00% | 185,000 | 315,000 | **PASS** | |
| CASE 4: instructor_coupon | 500,000 | 97.00% | 3.00% | 485,000 | 15,000 | **PASS** | |
| CASE 4b: admin_campaign_coupon | 500,000 | 97.00% | 3.00% | 485,000 | 15,000 | **FAIL** | Coupon admin tạo kỳ vọng 37/63 nhưng bị tính nhầm thành 97/3 |
| CASE 5: instructor_referral | 500,000 | 97.00% | 3.00% | 485,000 | 15,000 | **PASS** | |
| CASE 6: invalid source | 500,000 | 70.00% | 30.00% | 350,000 | 150,000 | **FAIL** | Fallback trả về rule code `default` thay vì `marketplace_default` |
| CASE 7: amount = 0 | 0 | 37.00% | 63.00% | 0 | 0 | **PASS** | Không lỗi crash |

## 6. Duplicate callback
- Revenue count theo order_id: **1** (Khi gọi lại lần 2, hàm `createRevenueForPaidOrder` trả về record có sẵn).
- Pass/Fail: **PASS**

## 7. Withdrawable Balance
- Balance dùng instructor_amount chưa: **Rồi** (sử dụng `SUM(revenues.instructor_amount)`).
- Test platform_ads balance: **PASS** (tăng đúng 185,000đ khi có doanh thu ads 500,000đ).
- Test instructor_coupon balance: **PASS** (tăng đúng 485,000đ khi có doanh thu coupon 500,000đ).
- Pass/Fail: **PASS**

## 8. API Revenue
- Summary đúng chưa: **Chưa** (Doanh thu trả về đúng nhưng không hiển thị/chứa các trường mới).
- List có source/rule field chưa: **Chưa** (Thiếu các trường `sale_source`, `sale_source_label`, `commission_rule_code`, `instructor_percent`, `platform_percent` trong JSON response).
- Chart đúng chưa: **Rồi**.
- Top courses đúng chưa: **Rồi**.
- Course breakdown đúng chưa: **Rồi**.

## 9. Regression routes
- Route instructor còn đủ không: **Đủ**.
- Withdrawals map đúng không: **Đúng** (`InstructorWithdrawalController`).
- Q&A missing method còn không: **Không còn lỗi**.
- Có route quiz/certificate mới không: **Không** (Các route `/quizzes` đã có từ trước, không thêm route mới).

## 10. Lỗi còn lại
- **P0**: 
  - `marketplace_default` bị chia sai tỷ lệ (37/63 thay vì 70/30) do seeder định nghĩa sai rule và thiếu rule `marketplace_default`.
  - Coupon do admin tạo bị tính sai sang rule `instructor_coupon` (97/3 thay vì 37/63).
  - Nguồn không hợp lệ fallback về rule code `default` thay vì `marketplace_default`.
- **P2**:
  - Response API doanh thu giảng viên chưa trả về các thông tin metadata mới liên quan tới `sale_source`, `instructor_percent`, `platform_percent`.

## 11. Kết luận
- Cơ chế chia theo quảng cáo/nguồn đơn hàng đã sẵn sàng ở lớp schema cơ sở dữ liệu.
- Cần cập nhật logic nghiệp vụ trong `RevenueShareService` để kiểm tra phân biệt coupon admin/giảng viên, sửa lại seeder đúng tỷ lệ 70/30 cho marketplace mặc định, và thêm các trường thông tin này vào API doanh thu giảng viên trước khi kết nối với Frontend.

---
**Folder report path:** `storage/revenue-share-source-retest-report/20260720_210752`  
**File report path:** `storage/revenue-share-source-retest-report/20260720_210752/REVENUE_SHARE_SOURCE_RETEST_REPORT.md`  

### Git Status:
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
...
```
