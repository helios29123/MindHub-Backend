# Báo cáo Triển khai: Fake Payout Gateway & Kiến trúc Payout

## Tổng quan
Theo yêu cầu, một `FakePayoutGateway` đã được triển khai cho môi trường Local/Demo nhằm tự động hóa quá trình chạy giả lập việc thanh toán sau khi Admin approve yêu cầu rút tiền sớm. 

**Những quyết định kiến trúc đã được áp dụng:**
1. **Synchronous Execution**: Chạy Payout ngay trong luồng Approve mà không dùng Queue Job, giúp việc demo ở local mượt mà. Logic đã được trừu tượng hóa qua `PayoutService` nên sau này có thể dễ dàng wrap bằng Queue Job.
2. **Không sao chép logic `markPaid`**: Logic hoàn tất thanh toán đã được tập trung lại vào method `PayoutService::finalizeSuccess()`. Việc `AdminWithdrawalController::markPaid()` cũng gọi chung vào service này, tránh lặp lại logic tính toán Partial Allocations.
3. **Database Migration**: Thêm `payout_provider` và `provider_payout_id` cho `withdraw_requests` (đã cập nhật vào model `$fillable` để map DB nếu đã có sẵn từ trước).
4. **An toàn Môi trường**: Chặn trường hợp sử dụng `FakePayoutGateway` tại môi trường `production`.

---

## 1. Thành phần Cốt lõi
- **`PayoutGatewayInterface`**: Giao diện tiêu chuẩn cho tất cả Gateway, chứa method `processPayout(WithdrawRequest $withdrawal)`.
- **`FakePayoutGateway`**: Gateway giả lập. Đọc cấu hình `payout.fake.result` (thường là 'success') để trả về trạng thái giả.
- **`PayoutService`**: Service xử lý chung cho Payout. Chứa các hàm:
  - `process()`: Gọi gateway, tùy theo trạng thái gateway trả về (SUCCESS, FAILED, PROCESSING) để rẽ nhánh gọi các hàm finalize.
  - `resolveWebhook()`: Hàm hỗ trợ xử lý khi Gateway gửi Webhook trả kết quả.
  - `finalizeSuccess()`: Logic tập trung xử lý Revenue allocations và update WithdrawRequest -> PAID.
  - `finalizeFailed()`: Logic tập trung nhả (release) allocations và update WithdrawRequest -> FAILED.
- **`PayoutServiceProvider`**: Bind Gateway class phù hợp theo file cấu hình `config/payout.php`.
- **`payout:fake-resolve` Command**: Cho phép test luồng "PROCESSING -> PAID/FAILED" thủ công qua CLI (VD: `php artisan payout:fake-resolve 10 success`). Không chọc trực tiếp Database mà đi qua `PayoutService::resolveWebhook()`.

---

## 2. Xác minh (Test)
Toàn bộ logic thanh toán và Finalization đã được test qua:
- **`FakePayoutFlowTest`**: 
  - Đảm bảo SUCCESS flow (chuyển sang PAID).
  - Đảm bảo FAILED flow (trả lại Available Balance & Monthly Quota cho Instructor).
  - Đảm bảo PROCESSING flow (vẫn giữ Reserve balance như cũ, thay đổi status).
  - Cập nhật thành công Provider Payout ID.
  - Idempotency test (không Approve 2 lần, không Failed khi đã Paid).
  - Test Partial Allocation: Chỉ cập nhật Revenue = PAID khi đã allocate đủ, nếu không thì giữ nguyên AVAILABLE.
- **`EarlyWithdrawalRulesTest`**: Vẫn PASS toàn bộ test hiện có.

---

## 3. Final Pre-Commit Review

### Database Schema Source
- `provider_payout_id`: Không có trong bất kỳ file migration nào trước đó. Cột này vốn nằm trôi nổi trong file dump cũ (`ntp.sql`). Đã khởi tạo mới thông qua migration an toàn `2026_08_14_203821_ensure_provider_payout_id_on_withdraw_requests_table.php` (sử dụng `Schema::hasColumn`).
- `payout_provider`: Được tạo bởi migration `2026_08_14_203421_add_payout_provider_to_withdraw_requests_table.php` (cũng có `Schema::hasColumn` guard). 
Hiện tại khi chạy một DB mới hoàn toàn (`migrate:fresh`), cả 2 cột đều sẽ được tạo đầy đủ và chính xác từ các file migrations này, không còn phụ thuộc vào việc import DB bằng tay.

### ServiceProvider Registration
- `PayoutServiceProvider` đã được register trực tiếp tại `bootstrap/providers.php`. Container có khả năng resolve `PayoutGatewayInterface` thành `FakePayoutGateway` khi driver là `fake`.

### Production Guard Verification
- `FakePayoutGateway` được code cứng một `BusinessException` ném ra khi `config('app.env') === 'production'`, không thể bypass.

### Files Changed (Git Diff)
**IN SCOPE:**
- `app/Http/Controllers/AdminWithdrawalController.php` (Refactor gọi PayoutService)
- `app/Models/WithdrawRequest.php` (Thêm fillable properties)
- `bootstrap/providers.php` (Đăng ký provider)
- `tests/Feature/EarlyWithdrawalRulesTest.php` (Update mock cho test hiện có)
- `app/Console/Commands/PayoutFakeResolveCommand.php` (Command mới)
- `app/Providers/PayoutServiceProvider.php` (Provider mới)
- `app/Services/Payout/Contracts/PayoutGatewayInterface.php` (Interface mới)
- `app/Services/Payout/Gateways/FakePayoutGateway.php` (Fake gateway)
- `app/Services/Payout/PayoutService.php` (Service mới)
- `config/payout.php` (Config mới)
- `tests/Feature/FakePayoutFlowTest.php` (Test mới)
- `database/migrations/2026_08_14_203421_add_payout_provider_to_withdraw_requests_table.php` (Migration mới)
- `database/migrations/2026_08_14_203821_ensure_provider_payout_id_on_withdraw_requests_table.php` (Migration mới)
- Các file báo cáo (Reports).

**OUT OF SCOPE:** (Sẽ không commit)
- `.codegraph/`
- `commit_84eb82e_diff.txt`
- `ntp.sql`
- `table_audit.php`
- `table_usage_results.json`

### Exact Test Counts
- **`FakePayoutFlowTest`**: 9 Tests, 20 Assertions (PASS).
- **`EarlyWithdrawalRulesTest`**: 13 Tests, 31 Assertions (PASS).

### Known Limitations
- FakePayoutGateway hiện tại được chạy Synchronous để phục vụ Local/Demo. Khi lên hệ thống Gateway thật (ví dụ: SePay API / Chuyển khoản thật) cần bọc `PayoutService::process` vào trong một Queue Job để tránh request timeout. Kiến trúc hiện tại đã tách biệt logic này nên việc bọc vào Job sau này sẽ không gặp cản trở về mặt thiết kế.
