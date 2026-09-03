# BÁO CÁO THIẾT KẾ & HOÀN THIỆN HỆ THỐNG CHIA DOANH THU VÀ THANH TOÁN GIẢNG VIÊN THEO MÔ HÌNH UDEMY

**Dự án**: MindHub Backend (`F:\Phatnt\laragon\www\MindHub-Backend\be`)  
**Tác giả**: Backend Developer Laravel  
**Ngày cập nhật**: 26/07/2026

---

## 1. Tên Trang Giao Diện & Định Hướng Mới

- **Tên trang hiển thị**: **Thanh toán giảng viên** (`page_title: "Thanh toán giảng viên"`)
- **Định hướng nghiệp vụ**: Chuyển hoàn toàn từ cơ chế *"Rút tiền thủ công"* sang mô hình *"Thanh toán định kỳ theo chu kỳ Udemy (Udemy Monthly Payout Statement)"*.

---

## 2. Chuẩn Hóa 5 Thẻ Báo Cáo (Dashboard Summary Cards)

Hệ thống API `/api/instructor/withdrawals/summary` trả về đúng 5 thẻ báo cáo theo yêu cầu:

1. **Doanh thu đang chờ** (`pending_revenue`): Tổng doanh thu khóa học đã thanh toán nhưng chưa đủ 30 ngày (đang trong thời hạn chờ sinh viên hoàn tiền).
2. **Số dư khả dụng** (`available_balance`): Doanh thu đã vượt qua thời hạn 30 ngày, chưa gom vào payout batch và sẵn sàng để thanh toán.
3. **Thanh toán sắp tới** (`scheduled_payout`): Số tiền đợt thanh toán đã được hệ thống gom cho kỳ hiện tại (`ready_to_pay` / `queued` / `processing`).
4. **Tổng đã thanh toán** (`paid_amount`): Tổng số tiền hệ thống đã giải ngân thành công cho giảng viên qua các kỳ (`paid`).
5. **Khoản bị tạm giữ** (`blocked_amount`): Số tiền bị giữ lại chưa chi trả do chưa đủ ngưỡng hoặc tài khoản nhận chưa được xác minh (`blocked`).

---

## 3. Các Thành Phần Đã Loại Bỏ Hoàn Toàn (Removed Features)

Hệ thống Backend đã vô hiệu hóa hoàn toàn các thao tác rút tiền tự do:

- ❌ **Nhập số tiền rút**: Không cho phép gửi param `amount` từ client.
- ❌ **Nút Gửi yêu cầu**: Chặn endpoint `POST /api/instructor/withdrawals`. Khi gọi tới sẽ trả về HTTP Status `422` kèm thông báo: *"Hệ thống thanh toán giảng viên theo kỳ và không hỗ trợ rút tiền thủ công."*
- ❌ **Chọn số tiền muốn rút**: Số tiền thanh toán do Backend tự động gom dựa trên số dư khả dụng (`available_balance`).
- ❌ **Hủy yêu cầu rút tiền**: Không cho phép hủy thủ công các đợt thanh toán tự động đã gom.

---

## 4. Các Thành Phần Mới Thay Thế (New Payout Components)

1. **Ngày thanh toán dự kiến** (`expected_payment_date`): Chu kỳ thanh toán cố định (từ ngày 05 đến 10 của tháng tiếp theo sau khi chốt doanh thu).
2. **Kỳ doanh thu** (`revenue_period`): Định danh tháng chốt doanh thu (ví dụ: *"Tháng 07/2026"*).
3. **Mức thanh toán tối thiểu** (`minimum_payout_label`): Mặc định **200.000 VNĐ** (`config('revenue.payout.minimum_amount')`). Nếu chưa đủ ngưỡng, số dư sẽ được bảo lưu sang kỳ kế tiếp.
4. **Trạng thái phương thức nhận tiền** (`payout_account_status`): `verified` (đã xác minh), `unverified` (chưa xác minh OTP/Admin), hoặc `missing` (chưa cài đặt).
5. **Lịch sử Payout** (`payout_history`): Endpoint `GET /api/instructor/withdrawals` trả về danh sách lịch sử các đợt thanh toán tự động theo kỳ.
6. **Nút cập nhật tài khoản nhận tiền** (`account_update_url`): Trỏ đường dẫn `/instructor/payout-accounts` để giảng viên cài đặt/xác minh ngân hàng.
7. **Cảnh báo nếu tài khoản chưa xác minh** (`verification_warning`): Trả về thông báo hướng dẫn rõ ràng nếu thiếu tài khoản, chưa xác minh hoặc chưa đạt ngưỡng tối thiểu.

---

## 5. Audit Schema Cơ Sở Dữ Liệu Thật

### A. Bảng `orders`:
- `id`, `course_id`, `user_id`, `amount`, `status`, `sale_source`, `commission_rule_id`, `paid_at`.

### B. Bảng `commission_rules`:
- `id`, `sale_channel`, `instructor_rate`, `platform_rate`, `is_active`.

### C. Bảng `revenues`:
- `id`, `order_id` (UNIQUE), `course_id`, `instructor_id`, `payout_id` (FK -> `withdraw_requests.id`), `gross_amount`, `instructor_amount`, `platform_fee_amount`, `status` (`pending`, `available`, `scheduled`, `included_in_payout`, `paid`, `refunded`, `reversed`), `earned_at`, `available_at`.

### D. Bảng `withdraw_requests` (Payout Statements):
- `id`, `user_id`, `payout_account_id`, `amount`, `status` (`initial`, `ready_to_pay`, `queued`, `processing`, `paid`, `failed`, `blocked`, `cancelled`), `period_start`, `period_end`, `expected_payment_at`, `processed_at`, `bank_name`, `account_number_snapshot`, `account_name_snapshot`, `payout_method`, `blocked_reason`, `failure_reason`.

### E. Bảng `payout_accounts`:
- `id`, `user_id`, `provider`, `account_number`, `account_name`, `status`, `is_default`.

---

## 6. Vòng Đời Trạng Thái Doanh Thu VÀ Payout (Status Flows)

### Vòng đời Revenue:
- `pending` (chờ 30 ngày) ──> `available` (đủ hạn) ──> `included_in_payout` / `scheduled` (gom vào kỳ) ──> `paid` (đã giải ngân).
- Hoàn tiền trong 30 ngày ──> `refunded`. Hoàn tiền sau 30 ngày ──> `reversed` (sinh adjustment âm).

### Vòng đời Payout:
- `initial` ──> `ready_to_pay` (nếu đạt tối thiểu + tài khoản verified) / `blocked` (nếu thiếu tài khoản/chưa đạt ngưỡng) ──> `queued` ──> `processing` ──> `paid` / `failed`.

---

## 7. Các Artisan Commands & Schedulers

1. `php artisan revenues:release-available` (giải phóng doanh thu `pending` -> `available` sau 30d).
2. `php artisan payouts:generate-monthly {--period=} {--instructor=} {--dry-run}` (gom số dư khả dụng tạo Payout theo kỳ).
3. `php artisan payouts:process-ready` (xử lý giải ngân các đợt sẵn sàng sang `paid`).
4. `php artisan payouts:reconcile {--instructor=}` (đối soát số liệu).

---

## 8. Kết Quả Kiểm Thử (Automated Tests)

- **`RevenueShareTest`**: **18/18 PASS** (100 assertions).
- **`InstructorPayoutTest`**: **9/9 PASS** (35 assertions).
- **Tổng cộng**: **27/27 feature tests PASS 100%**.
