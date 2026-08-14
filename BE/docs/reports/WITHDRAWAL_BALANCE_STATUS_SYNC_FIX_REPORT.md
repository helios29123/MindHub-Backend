# Báo cáo sửa lỗi đồng bộ dữ liệu Withdrawal Balance/Status

## 1. Mục tiêu
Sửa lỗi bất đồng bộ số liệu hiển thị liên quan đến số dư trước và sau khi rút, trạng thái và payout provider giữa Admin và Instructor FE.

## 2. Chi tiết thực hiện

### Database
- Đã chạy migration `2026_08_14_210153_add_snapshot_balances_to_withdraw_requests_table.php` thêm `available_balance_before` và `available_balance_after` vào bảng `withdraw_requests`.

### Backend Models & Services
- Đã thêm `available_balance_before` và `available_balance_after` vào thuộc tính `$fillable` và `$casts` của model `WithdrawRequest` với kiểu `decimal:2`.
- Trong `EarlyWithdrawalService::createEarlyWithdrawal()`, đã triển khai cơ chế **Snapshot Creation**:
  - Gắn vào transaction để snapshot số dư `availableBefore` lấy từ `getPaymentSummary()` lúc yêu cầu rút tiền thành công.
  - Lấy `availableAfter = availableBefore - amount`.
- Đã cập nhật `AdminWithdrawalController::show()`:
  - Loại bỏ các phép tính toán (tính query Revenue) cho số dư.
  - Render trực tiếp `available_balance_before` và `available_balance_after` từ Model, cho phép trả về `null` cho dữ liệu lịch sử để hiển thị `"—"`.

### Payout Implementation
- Cập nhật `FakePayoutGateway` để thêm field `payout_provider` vào response.
- Cập nhật `PayoutService` để tự động lưu thông tin `payout_provider = 'fake'` và `provider_payout_id` khi request đến gateway thành công.

### Frontend
- **Admin UI** (`withdrawals.js`):
  - Xử lý các giá trị `balance_snapshot` là `null` thành `"—"` thay vì số `"0đ"`.
  - Giữ cảnh báo warning validation logic phù hợp.
- **Instructor UI** (`InstructorWithdrawal.tsx`):
  - Bổ sung `window.addEventListener('focus', handleFocus)` bên trong `useEffect` để kích hoạt việc tự động refresh dữ liệu `/summary` và lịch sử rút tiền mỗi khi User quay lại trang này (chống dữ liệu bị stale do duyệt tab).

### Kiểm thử (Testing)
- Đã bổ sung bộ Test suite: `WithdrawalSnapshotTest.php`:
  - `test_snapshots_are_saved_correctly_on_creation`: Kiểm tra Snapshot balances lưu trữ chính xác giá trị tại thời điểm rút.
  - `test_snapshots_are_immutable_when_status_changes`: Kiểm tra tính Immutability của Snapshot balances, đảm bảo không bị cập nhật sai lệch khi trạng thái WithdrawRequest thay đổi (ví dụ: chuyển từ Pending sang Rejected).
- Toàn bộ Test của `FakePayoutFlowTest` và `EarlyWithdrawalRulesTest` đã Pass.

## 3. Kết luận
Lỗi hiển thị "0đ" bất đồng bộ giữa Admin UI (đọc từ DB) và Instructor UI (chạy động) đã được xử lý triệt để qua việc dùng Snapshots bất biến. Các thao tác Payout cũng đã lưu trữ được Provider hợp lệ. Báo cáo này xác nhận hoàn thiện yêu cầu.
