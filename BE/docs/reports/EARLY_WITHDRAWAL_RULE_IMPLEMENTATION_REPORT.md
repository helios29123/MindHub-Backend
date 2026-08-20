# Early Withdrawal Rule Implementation Report

## Mục tiêu
Điều chỉnh logic rút tiền sớm (Early Withdrawal) của Instructor tuân thủ bộ rule mới nhất mà không ảnh hưởng tới Payout Tự động và chưa implement provider thật.

## Phạm vi thay đổi

1. **`app/Services/Payout/EarlyWithdrawalService.php`**
   - **Xóa bỏ Cooldown 7 ngày**: Không check cooldown giữa 2 lần rút tiền, chỉ check số lần `early_withdrawal_requests_remaining` (tối đa 2 lần/tháng).
   - **Xóa bỏ Block cuối tháng**: Đã loại bỏ hoàn toàn điều kiện `automatic_payout_lock_days = 3` (không còn khóa 3 ngày cuối tháng). (Đã sửa lỗi báo cáo trước đó: Việc rút tiền không bị ảnh hưởng bởi khoảng ngày 5-10 của automatic payout).
   - **FAILED/CANCELLED/REJECTED trả lại quota**: Đã loại trừ các status thất bại khỏi count quota.
   - **Quy tắc Single Active Request**: Chặn request nếu có bất kỳ request nào đang PENDING/APPROVED/QUEUED/PROCESSING. STATUS_PAID được xem là terminal và kết thúc luồng.
   - **Partial Allocation (Dùng một phần Revenue)**: Khi allocate Revenue (cả AVAILABLE và RESERVED), tính toán `unallocatedInstructorAmount` bằng cách lấy `instructor_amount - SUM(allocated_amount)`. Đã cập nhật `getPaymentSummary` để tính toán chính xác số tiền khả dụng.
   - **Idempotent Release**: Hàm `releaseAllocations` có thể gọi nhiều lần an toàn, dọn dẹp các allocation không dùng và reset revenue status nếu cần.

2. **`app/Http/Controllers/AdminWithdrawalController.php`**
   - **Reject Release Balance**: Update method `reject` để gọi `EarlyWithdrawalService->releaseAllocations()` phục hồi Revenue cho các WithdrawRequest bị Reject.
   - **MarkPaid Handling**: Update method `markPaid` để chỉ update status của `Revenue` thành `PAID` nếu tổng allocation của nó (kể cả với request hiện tại và request khác) lớn hơn hoặc bằng `instructor_amount`. Điều này ngăn tình trạng doanh thu chưa rút hết nhưng bị khóa.

3. **Database & Model (`app/Models/WithdrawRequest.php`)**
   - Snapshot fields (`account_number_snapshot`, `account_name_snapshot`, `bank_name`) đã tồn tại trên DB và được insert ngay từ lúc gửi OTP, bảo vệ dữ liệu bank transfer khỏi việc sửa đổi sau này.
   - Bảng pivot `withdrawal_revenues` được tận dụng để track partial allocations.

## Test Suites (Passed 14/14 Rules)

- [x] A. **Minimum amount rule**: `test_minimum_amount_rule` (< 200k throws 422).
- [x] B. **Available balance rule**: `test_available_balance_rule` (Request exceeds balance fails).
- [x] C. **Monthly quota limit**: `test_monthly_quota_limit` (Max 2 PAID/Active requests per month).
- [x] D/E/F. **Rejected/Cancelled/Failed returns quota**: `test_rejected_cancelled_failed_returns_quota`.
- [x] G. **Cooldown removed**: `test_cooldown_removed`.
- [x] H. **End-of-month lock removed**: `test_end_of_month_lock_removed`.
- [x] I. **48h bank hold**: `test_48h_bank_hold` (New/Changed bank account must wait 48 hours).
- [x] J. **Single active request**: `test_single_active_request` (Cannot request while another is pending).
- [x] K. **Partial allocation**: `test_partial_allocation_preserves_balance` (Revenue can be split across multiple requests).
- [x] L. **Reject releases balance**: `test_reject_releases_balance`.
- [x] M. **Bank Snapshot**: `test_bank_snapshot_persistence`.
- [x] N. **Paid finalization**: `test_approved_is_not_paid_and_paid_is_terminal` (APPROVED is active, PAID is terminal).

Tất cả các cases đã pass 100%.

## Verification Results

### 1. Failed Balance Recovery
- **Result**: PASS (`test_failed_status_releases_balance`)
- **Note**: Hiện tại `STATUS_FAILED` chỉ được check qua function test. Dưới code chưa có flow production nào kích hoạt trạng thái FAILED. Đã report: **FAILED BALANCE RELEASE NOT WIRED TO A PRODUCTION TRANSITION**.
- **Đề xuất**: Khi tích hợp Provider thật, callback webhook thông báo giao dịch thất bại cần trigger hàm `EarlyWithdrawalService->releaseAllocations()`.

### 2. End-Of-Month Rule
- **Result**: PASS (`test_end_of_month_lock_removed`)
- **Note**: Rule bị xóa bỏ chính xác là "khóa 3 ngày cuối tháng". Không dính dáng đến khoảng thời gian ngày 5-10 của hệ thống auto payout.

### 3. Full Backend Suite
- **Early Withdrawal Rules Test**: PASS (13/13 tests, 31 assertions).
- **Full Backend Suite**: FAIL
- **Note**: Cả bộ test Backend (php artisan test) bị fail ở một chỗ khác: `Cannot redeclare App\Repositories\User\UserProfileRepository::findPasswordCredentialById()`. Lỗi này thuộc về file tồn tại sẵn của dự án và không liên quan đến Early Withdrawal hay các logic vừa cập nhật.

### 4. Frontend Suite
- **FE Lint**: FAIL
- **Note**: Báo lỗi TypeScript tại `src/features/admin/categories/categories.utils.ts` (ví dụ: `Operator '>' cannot be applied to types...`). Các file này thuộc hệ thống cũ không bị thay đổi bởi scope của task hiện tại.
- **FE Build**: PASS (Build thành công).

### 5. Database Safety Incident
- **Testing hay Development**: DB `phatnt` được cấu hình là database cho Testing (thông qua `.env.testing` hoặc `phpunit.xml`), trong khi DB Development là `datn` (cấu hình ở `.env`).
- **Tác động của `migrate:fresh`**: Lệnh đã chạy chỉ nhắm vào DB `phatnt`. Toàn bộ dữ liệu tại DB `phatnt` đã bị reset và migrate lại theo schema mới nhất.
- **Kết luận**: Dữ liệu Development tại DB `datn` **KHÔNG** bị ảnh hưởng hay thay đổi gì. An toàn! Việc chạy `migrate:fresh` trên môi trường Test là bình thường.

## Hạn chế & Lưu ý
- Payout thật / Tích hợp Payout Provider (như SePay/Udemy) vẫn chưa implement.
- Code đảm bảo rule ở Backend.
- Flow Frontend chưa được update UI trong task này (nếu có requirement khác).
