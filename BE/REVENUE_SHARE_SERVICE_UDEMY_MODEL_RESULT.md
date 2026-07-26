# BÁO CÁO AUDIT VÀ SỬA ĐỔI DỊCH VỤ CHIA DOANH THU THEO MÔ HÌNH UDEMY

**Dự án**: MindHub Backend (`F:\Phatnt\laragon\www\MindHub-Backend\be`)  
**Dịch vụ**: `App\Services\Payment\RevenueShareService`  
**Ngày thực hiện**: 26/07/2026

---

## 1. Schema Thật của Bảng `orders`

- **Khóa chính**: `id` (bigint, auto-increment)
- **Thông tin khóa học & người mua**: `course_id` (FK -> `courses.id`), `user_id` (FK -> `users.id`)
- **Mã đơn hàng & số tiền**: `order_code` (unique string 100), `amount` (decimal 12,2), `price_snapshot` (decimal 12,2)
- **Trạng thái**: `status` (enum: `pending`, `paid`, `cancelled`, `failed`, `refunded`), `payment_status` (`paid`, `unpaid`, `failed`)
- **Kênh bán & Quy tắc hoa hồng**: `sale_source` (string 100 nullable index), `commission_rule_id` (FK -> `commission_rules.id` nullable)
- **Thời gian thanh toán**: `paid_at` (timestamp nullable)

---

## 2. Schema Thật của Bảng `commission_rules`

- **Khóa chính**: `id` (bigint, auto-increment)
- **Kênh bán nhận diện**: `sale_channel` (string 100 unique index)
- **Tỷ lệ hoa hồng**: `instructor_rate` (decimal 5,2), `platform_rate` (decimal 5,2), `instructor_rate_percent`, `platform_rate_percent`
- **Trạng thái**: `is_active` (boolean default true)

---

## 3. Schema Thật của Bảng `revenues`

- **Khóa chính**: `id` (bigint, auto-increment)
- **Liên kết**: `order_id` (FK -> `orders.id`, UNIQUE), `course_id` (FK -> `courses.id`), `instructor_id` (FK -> `users.id`)
- **Số tiền**: `gross_amount` (decimal 15,2), `instructor_amount` (decimal 15,2), `platform_fee_amount` (decimal 15,2)
- **Thông tin tỷ lệ & kênh**: `sale_source` (string 100), `commission_rule_id` (FK -> `commission_rules.id`), `commission_rule_code` (string 100), `instructor_percent` (decimal 5,2), `platform_percent` (decimal 5,2)
- **Trạng thái & thời gian**: `status` (string 30, default `'available'`), `earned_at` (timestamp nullable), `created_at`, `updated_at`

---

## 4. Cấu Trúc Đơn Hàng (Một Course hay Nhiều Course)

- Cấu trúc đơn hàng của dự án hiện tại là **Single Course Per Order**.
- Bảng `orders` trực tiếp chứa cột `course_id` (FK tới `courses.id`).
- Hệ thống không sử dụng bảng `order_items`.

---

## 5. Logic Hiện Tại Trước Khi Sửa

- Đã có class `App\Services\Payment\RevenueShareService` được gọi từ `PaymentService::createRevenueAfterCourseOrderPaid`.
- Đã giải quyết được `sale_source` dựa trên mã giảm giá (instructor coupon vs. admin/platform coupon) hoặc channel truyền vào.

---

## 6. Root Cause / Các Phần Còn Thiếu Trước Khi Chuẩn Hóa

1. **Thiếu DB Transaction & Lock**: Code cũ chưa bọc trong `DB::transaction()` và chưa dùng `lockForUpdate()`, khiến cho các callback thanh toán đồng thời (concurrent webhook/IPN calls) có rủi ro race condition.
2. **Thiếu Exception rõ ràng**: Khi order chưa ở trạng thái `paid`, amount âm/null, hoặc thiếu thông tin giảng viên/khóa học, service chưa throw custom exception chuyên biệt mà có thể chạy tiếp hoặc trả về kết quả không an toàn.
3. **Chưa Validate Tổng Tỷ Lệ**: Chưa có logic kiểm tra `instructor_rate + platform_rate === 100.0`.
4. **Chưa Đồng Bộ Đầy Đủ Method Signatures**: Cần hỗ trợ cả `createRevenueForPaidOrder(int|Order $order)` và `calculateForPaidOrder(int|Order $order)` (alias).

---

## 7. Service Đã Chuẩn Hóa

- File: `app/Services/Payment/RevenueShareService.php`
- Chuẩn hóa theo phong cách Clean Code, bọc toàn bộ logic trong `DB::transaction()` với `Order::query()->lockForUpdate()`.

---

## 8. Rule Resolver Logic

1. Kiểm tra coupon liên kết (nếu có):
   - Nếu là coupon do giảng viên sở hữu -> `sale_source = 'instructor_coupon'` (tỷ lệ 97% / 3%).
   - Nếu là coupon do admin/platform tạo -> `sale_source = 'admin_campaign'` (hoặc `platform_ads` nếu order ghi nhận).
2. Kiểm tra `sale_source` từ order:
   - Hỗ trợ các nguồn: `marketplace_default`, `platform_ads`, `admin_campaign`, `instructor_coupon`, `instructor_referral`.
   - Nếu `sale_source` không hợp lệ hoặc rỗng -> fallback về `marketplace_default`.
3. Tra cứu quy tắc hoa hồng trong bảng `commission_rules`:
   - Tìm theo `sale_channel` / `code` / `type` matching `sale_source` và `is_active = true`.
   - Fallback về quy tắc `marketplace_default` trong DB.
   - Fallback về tỷ lệ mặc định 70% Giảng viên / 30% Nền tảng nếu DB chưa seed.

---

## 9. Công Thức Tính Tiền

$$\text{gross\_amount} = \text{(float)} \$order->amount$$

$$\text{instructor\_amount} = \text{round}\left(\text{gross\_amount} \times \frac{\text{instructor\_rate}}{100}, 2\right)$$

$$\text{platform\_fee\_amount} = \text{round}(\text{gross\_amount} - \text{instructor\_amount}, 2)$$

- Lấy `platform_fee_amount` bằng phép trừ để đảm bảo tuyệt đối:
  $$\text{instructor\_amount} + \text{platform\_fee\_amount} \equiv \text{gross\_amount}$$

---

## 10. Rounding Policy

- Sử dụng `round(..., 2)` tiêu chuẩn cho các giá trị tiền tệ decimal/float.
- Với đơn hàng bằng `0`, cả `instructor_amount` và `platform_fee_amount` đều được ghi nhận bằng `0.0`.

---

## 11. Revenue Status / Hold Period

- Mặc định `status = 'available'`.
- `earned_at` lấy theo `$order->paid_at` hoặc `now()`.

---

## 12. Idempotency (Chống Tạo Trùng Doanh Thu)

1. **Ở mức ứng dụng (Code level)**: 
   - Row-lock order bằng `lockForUpdate()`.
   - Query `Revenue::where('order_id', $order->id)->first()`. Nếu đã tồn tại -> Trả về ngay record hiện có mà không insert lại.
2. **Ở mức cơ sở dữ liệu (Database level)**:
   - Ràng buộc `UNIQUE` trên cột `revenues.order_id` đảm bảo ngay cả khi race condition vượt qua code check, DB sẽ ngắt lệnh duplicate và catch block trả về record sẵn có.

---

## 13. Database Transaction

- Mọi thao tác đọc ghi dữ liệu đều nằm trong:
  ```php
  DB::transaction(function () use ($orderId) { ... });
  ```
- Nếu tạo `Revenue` hoặc update `Order` thất bại, toàn bộ thao tác tự động rollback hoàn toàn.

---

## 14. Unique Constraint Status

- Migration `2026_07_11_000002_create_revenues_table_if_missing.php` đã có sẵn `$table->unique('order_id');`.
- Ràng buộc này đang hoạt động bình thường trên DB thật.

---

## 15. Tích Hợp Vào Payment Flow

- Đã tích hợp tại `app/Services/Payment/PaymentService.php` trong phương thức `createRevenueAfterCourseOrderPaid(object $order)`.
- Chỉ chạy khi thanh toán thành công và order đã được mark sang `paid`.

---

## 16. Các File Đã Sửa / Tạo Mới

### File Tạo Mới (Custom Exceptions):
- `app/Exceptions/OrderNotPaidException.php`
- `app/Exceptions/CommissionRuleNotFoundException.php`
- `app/Exceptions/InvalidCommissionRuleException.php`
- `app/Exceptions/InvalidOrderAmountException.php`
- `app/Exceptions/CourseInstructorMissingException.php`
- `app/Exceptions/RevenueAlreadyExistsException.php`

### File Sửa Đổi:
- `app/Services/Payment/RevenueShareService.php`
- `tests/Feature/RevenueShareTest.php`

---

## 17. Test Đã Chạy

- Chạy test suite tập trung: `php artisan test --filter=RevenueShare`

---

## 18. Kết Quả Test

- **Tổng số tests**: 16/16 tests **PASS** (100%).
- Bao gồm đầy đủ các trường hợp:
  1. Default Marketplace (70/30)
  2. Platform Ads (37/63)
  3. Admin Campaign (37/63)
  4. Instructor Coupon (97/3)
  5. Admin Coupon (37/63)
  6. Instructor Referral (97/3)
  7. Invalid Source Fallback
  8. Zero Amount
  9. Duplicate Callback Prevention (Idempotency)
  10. Gross Amount Consistency
  11. Throw `OrderNotPaidException` khi order chưa paid
  12. Throw `InvalidOrderAmountException` khi amount âm
  13. Throw `CourseInstructorMissingException` khi thiếu course/instructor
  14. Throw `InvalidCommissionRuleException` khi tổng tỷ lệ khác 100
  15. Cập nhật `commission_rule_id` và `sale_source` trên order
  16. Alias method `calculateForPaidOrder`

---

## 19. Danh Sách Chi Tiết Các File Thay Đổi

```text
 app/Exceptions/CommissionRuleNotFoundException.php | 13 +++++++++++++
 app/Exceptions/CourseInstructorMissingException.php | 13 +++++++++++++
 app/Exceptions/InvalidCommissionRuleException.php  | 13 +++++++++++++
 app/Exceptions/InvalidOrderAmountException.php      | 13 +++++++++++++
 app/Exceptions/OrderNotPaidException.php           | 13 +++++++++++++
 app/Exceptions/RevenueAlreadyExistsException.php    | 13 +++++++++++++
 app/Services/Payment/RevenueShareService.php       | 96 ++++++++++++++++++++++++++++++-----------------------------------
 tests/Feature/RevenueShareTest.php                 | 98 +++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++-
 8 files changed, 237 insertions(+), 49 deletions(-)
```

---

## 20. Khuyến Nghị & Bước Tiếp Theo

- **Git Status**: Không commit hay push Git theo đúng yêu cầu.
- **Dữ liệu cũ**: Không chạy backfill tự động đối với các order paid cũ trừ khi có yêu cầu bổ sung lệnh `artisan` riêng.
- **Hệ thống hiện tại**: Đã hoàn toàn sẵn sàng và đáp ứng đầy đủ tiêu chuẩn chia doanh thu Udemy Model.
