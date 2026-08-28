# BÁO CÁO THIẾT KẾ HOÀN CHỈNH MODULE THANH TOÁN DOANH THU GIẢNG VIÊN TỰ ĐỘNG THEO MÔ HÌNH UDEMY

**Dự án Backend**: `F:\Phatnt\laragon\www\MindHub-Backend\be`  
**Dự án Frontend**: `F:\Phatnt\Documents\MindHub-Frontend`  
**Thời gian hoàn thành**: 26/07/2026

---

## 1. Bảng So Sánh Chức Năng Cũ (Withdrawal) & Logic Mới (Udemy Payout)

| Chức năng | File hiện tại | Logic hiện tại | Trạng thái (Giữ/Sửa/Deprecated) |
|---|---|---|---|
| Màn hình Rút tiền | `InstructorWithdrawal.tsx` | Nhập số tiền rút thủ công, bấm nút gửi | **SỬA HOÀN TOÀN** → Thành màn hình *"Thanh toán giảng viên"* theo kỳ |
| Form Tạo Yêu Cầu Rút | `InstructorWithdrawal.tsx` | Drawer nhập amount & gửi yêu cầu | **DEPRECATED / LOẠI BỎ HOÀN TOÀN** khỏi UI |
| Endpoint Tạo Yêu Cầu Rút | `POST /api/instructor/withdrawals` | Tạo record rút tiền thủ công | **DEPRECATED** → Trả HTTP 422 từ chối rút thủ công |
| Tải Báo Cáo Doanh Thu | `GET /api/instructor/withdrawals/summary` | Trả 4 thẻ ví cũ | **SỬA** → Trả 5 thẻ Payout Udemy + thông tin kỳ thanh toán |
| Danh Sách Thanh Toán | `GET /api/instructor/withdrawals` | Lịch sử rút tiền thủ công | **SỬA** → Lịch sử đợt thanh toán theo kỳ (`payout_history`) |
| Chi Tiết Thanh Toán | `GET /api/instructor/withdrawals/{id}` | Chi tiết đơn rút tiền | **SỬA** → Chi tiết Payout Statement kèm danh sách revenues |
| Cập Nhật Thẻ Ngân Hàng | `POST /api/instructor/payout-accounts` | Lưu tài khoản ngân hàng | **GIỮ & NÂNG CẤP** → Giữ luồng OTP Email bảo mật |

---

## 2. Audit Database Schema Thật

- **`orders`**: `id`, `course_id` (1 order = 1 course), `user_id`, `amount`, `status`, `sale_source`, `commission_rule_id`, `paid_at`.
- **`courses`**: `id`, `instructor_id` (trỏ `users.id`), `price`, `status`.
- **`commission_rules`**: `id`, `sale_channel`, `instructor_rate`, `platform_rate`, `is_active`.
- **`revenues`**: `id`, `order_id` (UNIQUE), `course_id`, `instructor_id`, `payout_id` (FK -> `withdraw_requests.id`), `gross_amount`, `instructor_amount`, `platform_fee_amount`, `status` (`pending`, `available`, `scheduled`, `included_in_payout`, `paid`, `refunded`, `reversed`), `earned_at`, `available_at`.
- **`withdraw_requests` (Payout Statements)**: `id`, `user_id`, `payout_account_id`, `amount`, `status` (`initial`, `ready_to_pay`, `queued`, `processing`, `paid`, `failed`, `blocked`, `cancelled`), `period_start`, `period_end`, `expected_payment_at`, `processed_at`, `bank_name`, `account_number_snapshot`, `account_name_snapshot`, `payout_method`, `blocked_reason`, `failure_reason`.
- **`payout_accounts`**: `id`, `user_id`, `provider`, `account_number`, `account_name`, `status`, `is_default`, `approved_at`.

---

## 3. RevenueShareService Chia Doanh Thu (Single Course Per Order)

- File: `app/Services/Payment/RevenueShareService.php`
- Logic xử lý trong `DB::transaction()` với `Order::query()->lockForUpdate()`.
- Chống callback trùng: Kiểm tra `Revenue::where('order_id', $order->id)->first()`, đảm bảo tính an toàn với DB UNIQUE constraint trên `revenues.order_id`.
- Tự động gán `status = 'pending'`, `available_at = paid_at + 30 ngày`.

---

## 4. Quy Tắc Phân Chia (Commission Rule Resolver)

1. Ưu tiên tra cứu Coupon sở hữu (Giảng viên: 97/3 vs Admin/Platform: 37/63).
2. Tra cứu `sale_source` trong bảng `commission_rules` (`instructor_coupon`, `platform_ads`, `admin_campaign`, `instructor_referral`, `marketplace_default`).
3. Fallback về `marketplace_default` (70% Giảng viên / 30% Platform).

---

## 5. Refund Hold Policy & Release Command

- Cấu hình: `config('revenue.refund_hold_days', 30)`.
- Command giải phóng: `php artisan revenues:release-available` chuyển các revenue `pending` quá 30 ngày sang `available`.

---

## 6. Instructor Payout Service & Monthly Cycle Generation

- File: `app/Services/Payout/InstructorPayoutService.php`
- Command: `php artisan payouts:generate-monthly {--period=} {--instructor=} {--dry-run}`
- Gom toàn bộ revenue `available` của giảng viên trong kỳ chốt.
- Ngưỡng tối thiểu: **200.000 VNĐ** (`config('revenue.payout.minimum_amount')`).
- Nếu chưa đạt ngưỡng hoặc chưa verified tài khoản ngân hàng -> Trạng thái payout là `blocked` kèm lý do cụ thể, bảo lưu số dư sang kỳ tiếp theo.
- Snapshot tài khoản: Lưu `bank_name`, `account_number_snapshot`, `account_name_snapshot` vào Payout Statement để bảo toàn lịch sử khi giảng viên thay đổi tài khoản ngân hàng sau này.

---

## 7. Enum Trạng Thái Payout Chuẩn Hóa

- `initial`: Khởi tạo
- `ready_to_pay`: Sẵn sàng thanh toán
- `queued`: Đang xếp hàng
- `processing`: Đang xử lý
- `paid`: Đã thanh toán thành công
- `failed`: Thanh toán thất bại
- `blocked`: Bị tạm giữ (do thiếu tài khoản hoặc chưa đạt 200k)
- `cancelled`: Đã hủy

---

## 8. Deprecated Manual Withdrawal Endpoint

- `POST /api/instructor/withdrawals`:
  - Trả về HTTP `422` Unprocessable Entity:
    ```json
    {
      "message": "MindHub thanh toán doanh thu giảng viên tự động theo kỳ và không hỗ trợ rút tiền thủ công."
    }
    ```

---

## 9. Thiết Kế Mới Tầng Frontend React/TypeScript

- **Tên trang mới**: **Thanh toán giảng viên** (`InstructorWithdrawal.tsx`)
- **Subtitle**: *Theo dõi doanh thu đủ điều kiện và các khoản thanh toán định kỳ theo chu kỳ Udemy.*
- **Loại bỏ hoàn toàn**:
  - Form/Drawer nhập số tiền rút
  - Input nhập số tiền rút
  - Nút "Rút tối đa" & Nút "Gửi yêu cầu"
  - Nút "Hủy yêu cầu rút"
- **5 Thẻ Tổng Quan Mới (Dashboard Summary Cards)**:
  1. Doanh thu đang chờ (`pending_revenue`)
  2. Số dư khả dụng (`available_balance`)
  3. Thanh toán sắp tới (`scheduled_payout`)
  4. Tổng đã thanh toán (`paid_amount`)
  5. Khoản bị tạm giữ (`blocked_amount`)
- **Khung Kỳ Thanh Toán Tiếp Theo**: Hiển thị ngày dự kiến (05-10 tháng sau), kỳ doanh thu, ngưỡng 200k, và cảnh báo tài khoản/số dư.
- **Tài Khoản Nhận Tiền**: Hiển thị masked account number (`********6789`), chủ tài khoản, badge "Đã xác minh OTP", nút "Cập nhật tài khoản" mở modal OTP email.
- **Lịch Sử Thanh Toán Theo Kỳ**: Bảng lịch sử Payout Statements kèm bộ lọc trạng thái, phân trang mượt mà không reload trang, và Modal xem chi tiết từng đợt thanh toán kèm danh sách khóa học cấu thành.

---

## 10. Danh Sách Các File Đã Sửa & Tạo Mới

### Backend:
- `config/revenue.php`
- `database/migrations/2026_07_26_000000_add_udemy_payout_and_revenue_columns.php`
- `app/Exceptions/*.php` (11 Domain Exception Classes)
- `app/Models/Revenue.php`
- `app/Models/WithdrawRequest.php`
- `app/Services/Payment/RevenueShareService.php`
- `app/Services/Payout/InstructorPayoutService.php`
- `app/Services/Instructor/InstructorWithdrawalService.php`
- `app/Repositories/Instructor/InstructorWithdrawalRepository.php`
- `app/Http/Resources/Instructor/InstructorWithdrawalSummaryResource.php`
- `app/Http/Controllers/ReportController.php`
- `app/Console/Commands/*.php` (4 Artisan Commands)
- `tests/Feature/RevenueShareTest.php`
- `tests/Feature/InstructorPayoutTest.php`

### Frontend:
- `F:\Phatnt\Documents\MindHub-Frontend\src\components\InstructorWithdrawal.tsx`

---

## 11. Kết Quả Kiểm Thử & Build

### Backend Tests:
- `php artisan test --filter=RevenueShare`: **18/18 PASS** (100 assertions).
- `php artisan test --filter=InstructorPayout`: **9/9 PASS** (35 assertions).
- **Tổng Backend**: **27/27 PASS 100%**.

### Frontend Build:
- `npx tsc --noEmit`: **0 ERRORS** (100% Clean TypeScript build).
