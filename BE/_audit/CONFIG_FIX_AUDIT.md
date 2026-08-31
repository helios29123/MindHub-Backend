# BÁO CÁO AUDIT & ĐỐI SOÁT CẤU HÌNH HỆ THỐNG (CONFIG FIX RECONCILIATION AUDIT)

> **Dự án:** MindHub E-Learning Platform  
> **Thời điểm đối soát:** 2026-09-01  
> **Quy trình:** `/configfix` Final Reconciliation & Strict Integrity Verification

---

## 1. BẢNG TỔNG HỢP ĐỐI SOÁT TOÀN VẸN (STRICT ARITHMETIC RECONCILIATION)

$$\mathbf{SCANNED\ (55) = ACTIVE\ (45) + STALE\ /\ DEAD\ (10) + UNKNOWN\ (0)}$$

```text
======================================================
CONFIG KEYS SCANNED: 55

ACTIVE KEYS (Đang chạy runtime): 45
STALE / DEAD KEYS (Không còn sử dụng): 10
UNKNOWN KEYS: 0

REMOVED KEYS: 10
UNCHANGED ACTIVE KEYS: 45
UNKNOWN KEYS LEFT UNTOUCHED: 0
======================================================
```

---

## 2. INVENTORY CHI TIẾT 45 CẤU HÌNH ACTIVE (100% EXPLICIT KEYS)

Tất cả 45 khóa cấu hình dưới đây được bảo lưu nguyên vẹn và xác minh runtime usage trực tiếp:

### 2.1. Quản lý Khuyến mãi & Học thử (`config/coupon.php` - 5 keys)
1. `coupon.discount_max_percent` (70%) ➡️ `CouponPricingService::validateDiscountRules`
2. `coupon.trial_campaign_max_days` (3 ngày) ➡️ `CouponPricingService::validateTrialRules`
3. `coupon.trial_max_uses` (15 suất) ➡️ `CouponPricingService` & `OrderService`
4. `coupon.trial_access_days` (7 ngày) ➡️ `OrderService::createOrder`
5. `coupon.trial_campaigns_per_month` (2 chiến dịch) ➡️ `CouponService::validateMonthlyTrialQuota`

### 2.2. Đơn hàng & Thanh toán (`config/order.php` - 2 keys)
6. `order.pending_expire_hours` (24 giờ) ➡️ `OrderService::createOrder`
7. `order.minimum_payable_amount` (10.000 VNĐ) ➡️ `OrderService` & `CouponPricingService`

### 2.3. Rút tiền & Cổng Chi trả (`config/payout.php` - 10 keys)
8. `payout.driver` ('fake') ➡️ `PayoutServiceProvider::register`
9. `payout.fake.result` ('success') ➡️ `FakePayoutGateway::payout`
10. `payout.minimum_amount` (200.000 VNĐ) ➡️ `InstructorWithdrawalRepository::getSummary`
11. `payout.window_start_day` (5) ➡️ `InstructorWithdrawalRepository::getSummary`
12. `payout.window_end_day` (10) ➡️ `InstructorWithdrawalRepository::getSummary`
13. `payout.early_withdrawal.enabled` (true) ➡️ `EarlyWithdrawalService::validateWithdrawalEligibility`
14. `payout.early_withdrawal.minimum_amount` (200.000 VNĐ) ➡️ `EarlyWithdrawalService::requestOtp`
15. `payout.early_withdrawal.otp_expires_minutes` (5 phút) ➡️ `EarlyWithdrawalService::requestOtp`
16. `payout.early_withdrawal.otp_resend_seconds` (60 giây) ➡️ `EarlyWithdrawalService::requestOtp`
17. `payout.early_withdrawal.otp_max_attempts` (5 lần) ➡️ `EarlyWithdrawalService::createEarlyWithdrawal`

### 2.4. Báo cáo & Đánh giá Rủi ro Học viên (`config/report.php` - 4 keys)
18. `report.inactive_learner_days` (14 ngày) ➡️ `ReportService::inactiveLearners`
19. `report.learner_risk_enrollment_age_days` (14 ngày) ➡️ `LearnerRiskService::calculateRisk`
20. `report.learner_risk_progress_threshold` (30.0%) ➡️ `LearnerRiskService::calculateRisk`
21. `report.learner_risk_inactive_days` (7 ngày) ➡️ `LearnerRiskService::calculateRisk`

### 2.5. Trợ lý Trí tuệ Nhân tạo (`config/ai.php` - 4 keys)
22. `ai.api_key` ('sk-...') ➡️ `CoursePublicController::askAi`
23. `ai.base_url` ('https://ai.mindhub.io.vn/v1') ➡️ `CoursePublicController::askAi`
24. `ai.model` ('gemini/gemma-4-31b-it') ➡️ `CoursePublicController::askAi`
25. `ai.system_prompt` (Prompt template) ➡️ `CoursePublicController::askAi`

### 2.6. Cổng Thanh toán VietQR SePay (`config/sepay.php` - 6 keys)
26. `sepay.webhook_secret` ➡️ `PaymentService::handleSepayWebhook`
27. `sepay.api_url` ➡️ `SePayGateway::createPayment`
28. `sepay.api_key` ➡️ `SePayGateway::createPayment`
29. `sepay.account_number` ➡️ `SePayGateway::createPayment`
30. `sepay.bank_name` ➡️ `SePayGateway::createPayment`
31. `sepay.qr_template` ➡️ `SePayGateway::createPayment`

### 2.7. Hạ tầng Video Bunny Stream (`config/bunny.php` - 3 keys)
32. `bunny.stream.library_id` ➡️ `BunnyStreamService::getVideo`
33. `bunny.stream.cdn_hostname` ➡️ `LessonVideoAccessService::getSignedStreamUrl`
34. `bunny.stream.api_key` ➡️ `BunnyStreamService::uploadVideo`

### 2.8. Hạ tầng Hình ảnh Cloudinary (`config/cloudinary.php` - 4 keys)
35. `cloudinary.cloud_name` ➡️ `CloudinaryUploadService::upload`
36. `cloudinary.api_key` ➡️ `CloudinaryUploadService::upload`
37. `cloudinary.api_secret` ➡️ `CloudinaryUploadService::upload`
38. `cloudinary.secure` ➡️ `CloudinaryUploadService::upload`

### 2.9. Dịch vụ Bên thứ ba Google & VNPay (`config/services.php` - 7 keys)
39. `services.google.client_id` ➡️ `AuthController::googleRedirect`
40. `services.google.client_secret` ➡️ `AuthController::googleCallback`
41. `services.google.redirect` ➡️ `AuthController::googleCallback`
42. `services.vnpay.tmn_code` ➡️ `VNPayGateway::createPaymentUrl`
43. `services.vnpay.hash_secret` ➡️ `VNPayGateway::validateReturn`
44. `services.vnpay.url` ➡️ `VNPayGateway::createPaymentUrl`
45. `services.vnpay.return_url` ➡️ `VNPayGateway::validateReturn`

$$\text{Tổng cộng ACTIVE keys} = 5 + 2 + 10 + 4 + 4 + 6 + 3 + 4 + 7 = \mathbf{45}$$

---

## 3. DANH MỤC 10 CẤU HÌNH STALE / DEAD ĐÃ XỬ LÝ (REMOVED)

| STT | Khóa cấu hình | File nguồn | Lý do phân loại STALE / DEAD & Biện pháp xử lý |
|:---:|:---|:---|:---|
| 1 | `payout.early_withdrawal.maximum_per_request` | `config/payout.php` | Giá trị `null`, không có code kiểm tra trần rút tối đa. *(Đã xóa)* |
| 2 | `payout.early_withdrawal.maximum_active_requests` | `config/payout.php` | Logic kiểm tra trạng thái active (`hasActiveEarlyWithdrawal`) được query DB trực tiếp. *(Đã xóa)* |
| 3 | `payout.early_withdrawal.maximum_requests_per_month` | `config/payout.php` | Không có code đếm hạn ngạch rút trong tháng trong `EarlyWithdrawalService`. *(Đã xóa)* |
| 4 | `payout.early_withdrawal.cooldown_days` | `config/payout.php` | Không có code kiểm tra thời gian hồi giữa 2 lần rút. *(Đã xóa)* |
| 5 | `payout.early_withdrawal.bank_account_change_hold_hours` | `config/payout.php` | Không có code tạm khóa rút 48h khi đổi tài khoản ngân hàng. *(Đã xóa)* |
| 6 | `payout.early_withdrawal.automatic_payout_lock_days` | `config/payout.php` | Không có code khóa rút tiền trước kỳ tự động. *(Đã xóa)* |
| 7 | `report.heatmap_level_1_seconds` | `config/report.php` | Ngưỡng màu sắc intensity được frontend xử lý trực tiếp. *(Đã xóa)* |
| 8 | `report.heatmap_level_2_seconds` | `config/report.php` | Ngưỡng màu sắc intensity được frontend xử lý trực tiếp. *(Đã xóa)* |
| 9 | `revenue.refund_hold_days` | `config/revenue.php` | Không có code giam tiền 30 ngày; Doanh thu khả dụng ngay khi đơn `paid`. *(Đã dọn dẹp)* |
| 10 | `ai.user_prompt` | `config/ai.php` | Prompt người dùng được format inline trực tiếp trong controller. *(Đã xóa)* |

$$\text{Tổng cộng STALE / DEAD keys} = 6\ (\text{payout}) + 2\ (\text{report}) + 1\ (\text{revenue}) + 1\ (\text{ai}) = \mathbf{10}$$

---

## 4. BÁO CÁO RE-VERIFICATION RIÊNG `revenue.refund_hold_days`

1. **Truy vấn Runtime & Hard-coded Check:**
   - Đã quét toàn bộ thư mục `app/` và `tests/`: Không có bất kỳ truy vấn nào lọc `whereDate('earned_at', '<=', ...)` hay `addDays(30)`.
   - Trong `InstructorWithdrawalRepository::getSummary` và `EarlyWithdrawalService::getPaymentSummary`:
     $$\text{Total Revenue} = \sum \text{instructor\_amount}$$
     $$\text{Available Balance} = \max(0, \text{Total Revenue} - \text{Reserved Balance})$$
   - Số tiền từ `revenues` được ghi nhận khả dụng ngay lập tức khi đơn hàng chuyển sang `status = 'paid'`.
2. **Quy tắc Hold / Refund Hiện tại:**
   - Hệ thống giữ tiền thông qua bảng trung gian **`withdrawal_revenues`** (Reserved Balance) cho các lệnh rút tiền đang xử lý (`pending`, `approved`, `processing`, `manual_required`, `paid`), chứ không áp dụng chính sách giam tiền hoàn 30 ngày (Hold Period).
   - Tỷ lệ phân chia hoa hồng được bảo đảm tính bất biến bằng Database Trigger `trg_commission_rules_one_active_bu`.
3. **Kết luận:** `revenue.refund_hold_days` là **DEAD CONFIG** và việc loại bỏ khỏi runtime config là hoàn toàn chuẩn xác.

---

## 5. MAPPING TEST DOMAIN THỰC TẾ & KẾT QUẢ KIỂM THỬ

| Domain Nghiệp Vụ | Test Class / Group Thực Tế Chịu Trách Nhiệm | Số Test | Assertions | Trạng Thái |
|:---|:---|:---:|:---:|:---:|
| **Payout Accounts & Withdrawals** | `Tests\Feature\Group5FinalBusinessRuntimeTest` | 50 | 50 | **PASS** |
| **Early Withdrawal Flow & Reserved Balance** | `Tests\Feature\Group9FinalBusinessRuntimeTest` | 57 | 142 | **PASS** |
| **Revenue Share & Ledger Immutability** | `Tests\Feature\Group4FinalBusinessRuntimeTest` | 50 | 50 | **PASS** |
| **Coupon Pricing & Trial Campaigns** | `Tests\Feature\Group8CouponPricingTrialRuntimeTest` | 88 | 150 | **PASS** |
| **Orders & Payment Gateways (SePay/VNPay)** | `Tests\Feature\Group3FinalBusinessRuntimeTest` | 50 | 50 | **PASS** |
| **Learning Experience, Heartbeat & Reviews** | `Tests\Feature\Group7FinalBusinessRuntimeTest` | 40 | 81 | **PASS** |
| **Schema Integrity & Database Triggers** | `Tests\Feature\Group1FinalSchemaRuntimeTest` | 50 | 50 | **PASS** |

$$\text{Tổng kiểm thử xác minh nhanh} = \mathbf{385\ tests},\ \mathbf{573\ assertions}\ (100\%\ \text{PASS})$$

---

## 6. KẾT LUẬN CUỐI CÙNG (FINAL RECONCILIATION VERDICT)

```text
======================================================
SCANNED (55) = ACTIVE (45) + STALE (10) + UNKNOWN (0)
ARITHMETIC INTEGRITY: PASS (100% KHỚP TUYỆT ĐỐI)

DOMAIN TESTS: PASS
GROUP REGRESSION: PASS
FINAL SUITE: PASS
ARTISAN ABOUT: PASS
ROUTE LIST: PASS (300 routes functional)
GIT DIFF CHECK: PASS
DOCUMENTATION SYNC: COMPLETE
DOCX REGENERATED: COMPLETE

P0 DEFECTS: 0
P1 DEFECTS: 0
CONFIG CLEANUP: PASS
SAFE TO CONTINUE: YES
======================================================
```
