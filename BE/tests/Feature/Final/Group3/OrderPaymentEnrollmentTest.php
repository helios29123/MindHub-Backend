<?php

namespace Tests\Feature\Final\Group3;

use PHPUnit\Framework\Attributes\DataProvider;
use Tests\Feature\Final\Support\FinalFeatureTestCase;
use Tests\Feature\Final\Support\SourceAwareExecutor;

final class OrderPaymentEnrollmentTest extends FinalFeatureTestCase
{
    #[DataProvider('cases')]
    public function test_nghiep_vu_final(string $id, string $nhan): void
    {
        SourceAwareExecutor::run($this, $id, $nhan);
    }

    public static function cases(): iterable
    {
        yield 'G3-001 🟢 Learner tạo pending order cho course chưa mua.' => ['G3-001', 'G3-001 🟢 Learner tạo pending order cho course chưa mua.'];
        yield 'G3-002 🔴 User đã sở hữu course không tạo pending order mới.' => ['G3-002', 'G3-002 🔴 User đã sở hữu course không tạo pending order mới.'];
        yield 'G3-003 🟢 Double click mua chỉ tạo một pending order hợp lệ.' => ['G3-003', 'G3-003 🟢 Double click mua chỉ tạo một pending order hợp lệ.'];
        yield 'G3-004 🟢 Order sinh `order_code` unique.' => ['G3-004', 'G3-004 🟢 Order sinh `order_code` unique.'];
        yield 'G3-005 🟢 Order chụp `price_snapshot` tại thời điểm tạo.' => ['G3-005', 'G3-005 🟢 Order chụp `price_snapshot` tại thời điểm tạo.'];
        yield 'G3-006 🟢 Order chụp `discount_amount` tại thời điểm tạo.' => ['G3-006', 'G3-006 🟢 Order chụp `discount_amount` tại thời điểm tạo.'];
        yield 'G3-007 🟢 Order chụp `amount` cuối cùng tại thời điểm tạo.' => ['G3-007', 'G3-007 🟢 Order chụp `amount` cuối cùng tại thời điểm tạo.'];
        yield 'G3-008 🟢 Order chụp `commission_rule_id` tại thời điểm tạo.' => ['G3-008', 'G3-008 🟢 Order chụp `commission_rule_id` tại thời điểm tạo.'];
        yield 'G3-009 🟢 Course đổi giá sau đó không làm thay đổi order pending cũ.' => ['G3-009', 'G3-009 🟢 Course đổi giá sau đó không làm thay đổi order pending cũ.'];
        yield 'G3-010 🟢 Coupon inactive sau đó không làm thay đổi order pending cũ.' => ['G3-010', 'G3-010 🟢 Coupon inactive sau đó không làm thay đổi order pending cũ.'];
        yield 'G3-011 🟢 Coupon expired sau đó không làm thay đổi order pending cũ.' => ['G3-011', 'G3-011 🟢 Coupon expired sau đó không làm thay đổi order pending cũ.'];
        yield 'G3-012 🟢 Order mới sau khi coupon inactive không được giảm.' => ['G3-012', 'G3-012 🟢 Order mới sau khi coupon inactive không được giảm.'];
        yield 'G3-013 🟢 Order không coupon có discount_amount = 0.' => ['G3-013', 'G3-013 🟢 Order không coupon có discount_amount = 0.'];
        yield 'G3-014 🔴 Client không được gửi price_snapshot để ép giá.' => ['G3-014', 'G3-014 🔴 Client không được gửi price_snapshot để ép giá.'];
        yield 'G3-015 🔴 Client không được gửi discount_amount để ép giảm.' => ['G3-015', 'G3-015 🔴 Client không được gửi discount_amount để ép giảm.'];
        yield 'G3-016 🔴 Client không được gửi amount để ép giá.' => ['G3-016', 'G3-016 🔴 Client không được gửi amount để ép giá.'];
        yield 'G3-017 🔴 Client không được ép `commission_rule_id`; backend tự snapshot rule áp dụng.' => ['G3-017', 'G3-017 🔴 Client không được ép `commission_rule_id`; backend tự snapshot rule áp dụng.'];
        yield 'G3-018 🟢 Pending order có payment_status=pending.' => ['G3-018', 'G3-018 🟢 Pending order có payment_status=pending.'];
        yield 'G3-019 🟢 Thanh toán thành công chuyển order → paid.' => ['G3-019', 'G3-019 🟢 Thanh toán thành công chuyển order → paid.'];
        yield 'G3-020 🟢 Thanh toán thành công chuyển payment_status → paid.' => ['G3-020', 'G3-020 🟢 Thanh toán thành công chuyển payment_status → paid.'];
        yield 'G3-021 🟢 paid_at được ghi đúng khi payment thành công.' => ['G3-021', 'G3-021 🟢 paid_at được ghi đúng khi payment thành công.'];
        yield 'G3-022 🟢 Payment method được ghi theo gateway thực tế.' => ['G3-022', 'G3-022 🟢 Payment method được ghi theo gateway thực tế.'];
        yield 'G3-023 🟢 provider_transaction_id được lưu khi gateway trả về.' => ['G3-023', 'G3-023 🟢 provider_transaction_id được lưu khi gateway trả về.'];
        yield 'G3-024 🔴 Thanh toán thiếu tiền không được mark paid.' => ['G3-024', 'G3-024 🔴 Thanh toán thiếu tiền không được mark paid.'];
        yield 'G3-025 🔴 Payment callback cho order không tồn tại bị từ chối.' => ['G3-025', 'G3-025 🔴 Payment callback cho order không tồn tại bị từ chối.'];
        yield 'G3-026 🟢 Webhook thành công tạo side effects đúng một lần.' => ['G3-026', 'G3-026 🟢 Webhook thành công tạo side effects đúng một lần.'];
        yield 'G3-027 🟢 Webhook lặp cùng giao dịch không tạo duplicate enrollment.' => ['G3-027', 'G3-027 🟢 Webhook lặp cùng giao dịch không tạo duplicate enrollment.'];
        yield 'G3-028 🟢 Webhook lặp không tạo duplicate revenue.' => ['G3-028', 'G3-028 🟢 Webhook lặp không tạo duplicate revenue.'];
        yield 'G3-029 🟢 Webhook lặp không tăng coupon usage hai lần.' => ['G3-029', 'G3-029 🟢 Webhook lặp không tăng coupon usage hai lần.'];
        yield 'G3-030 🔴 Webhook token/signature sai bị từ chối.' => ['G3-030', 'G3-030 🔴 Webhook token/signature sai bị từ chối.'];
        yield 'G3-031 🔴 Webhook amount không khớp bị từ chối.' => ['G3-031', 'G3-031 🔴 Webhook amount không khớp bị từ chối.'];
        yield 'G3-032 🟢 Payment fail chuyển trạng thái failed đúng rule.' => ['G3-032', 'G3-032 🟢 Payment fail chuyển trạng thái failed đúng rule.'];
        yield 'G3-033 🟢 Payment fail lưu failed_reason.' => ['G3-033', 'G3-033 🟢 Payment fail lưu failed_reason.'];
        yield 'G3-034 🟢 Retry sau fail rồi success chuyển paid đúng một lần.' => ['G3-034', 'G3-034 🟢 Retry sau fail rồi success chuyển paid đúng một lần.'];
        yield 'G3-035 🟢 Cancel pending order chuyển cancelled.' => ['G3-035', 'G3-035 🟢 Cancel pending order chuyển cancelled.'];
        yield 'G3-036 🔴 Cancel order đã paid bị từ chối.' => ['G3-036', 'G3-036 🔴 Cancel order đã paid bị từ chối.'];
        yield 'G3-037 🟢 Expire pending order quá hạn theo command.' => ['G3-037', 'G3-037 🟢 Expire pending order quá hạn theo command.'];
        yield 'G3-038 🔴 Pending chưa quá hạn không bị expire.' => ['G3-038', 'G3-038 🔴 Pending chưa quá hạn không bị expire.'];
        yield 'G3-039 🟢 Paid order không bị expire.' => ['G3-039', 'G3-039 🟢 Paid order không bị expire.'];
        yield 'G3-040 🟢 Payment VNPay flow cũ vẫn hoạt động sau tích hợp coupon.' => ['G3-040', 'G3-040 🟢 Payment VNPay flow cũ vẫn hoạt động sau tích hợp coupon.'];
        yield 'G3-041 🟢 SePay confirm thành công tìm đúng order.' => ['G3-041', 'G3-041 🟢 SePay confirm thành công tìm đúng order.'];
        yield 'G3-042 🔴 SePay confirm nội dung không khớp order bị từ chối.' => ['G3-042', 'G3-042 🔴 SePay confirm nội dung không khớp order bị từ chối.'];
        yield 'G3-043 🟢 SePay xử lý đúng order_code trong nội dung chuyển khoản.' => ['G3-043', 'G3-043 🟢 SePay xử lý đúng order_code trong nội dung chuyển khoản.'];
        yield 'G3-044 🟢 Trial tạo zero-paid order đúng đặc tả.' => ['G3-044', 'G3-044 🟢 Trial tạo zero-paid order đúng đặc tả.'];
        yield 'G3-045 🟢 Trial dùng payment_method=coupon_trial.' => ['G3-045', 'G3-045 🟢 Trial dùng payment_method=coupon_trial.'];
        yield 'G3-046 🟢 Trial không gọi payment gateway.' => ['G3-046', 'G3-046 🟢 Trial không gọi payment gateway.'];
        yield 'G3-047 🟢 Trial order ngay lập tức paid theo flow trial.' => ['G3-047', 'G3-047 🟢 Trial order ngay lập tức paid theo flow trial.'];
        yield 'G3-048 🟢 Trial không tạo revenue.' => ['G3-048', 'G3-048 🟢 Trial không tạo revenue.'];
        yield 'G3-049 🟢 Trial tạo enrollment 7 ngày.' => ['G3-049', 'G3-049 🟢 Trial tạo enrollment 7 ngày.'];
        yield 'G3-050 🟢 Trial→paid thật reuse enrollment cũ.' => ['G3-050', 'G3-050 🟢 Trial→paid thật reuse enrollment cũ.'];
        yield 'G3-051 🟢 Trial→paid thật giữ nguyên progress.' => ['G3-051', 'G3-051 🟢 Trial→paid thật giữ nguyên progress.'];
        yield 'G3-052 🟢 Trial→paid thật xóa expires_at của enrollment.' => ['G3-052', 'G3-052 🟢 Trial→paid thật xóa expires_at của enrollment.'];
        yield 'G3-053 🟢 Paid order thường tạo enrollment active.' => ['G3-053', 'G3-053 🟢 Paid order thường tạo enrollment active.'];
        yield 'G3-054 🟢 Enrollment liên kết đúng user-course-order.' => ['G3-054', 'G3-054 🟢 Enrollment liên kết đúng user-course-order.'];
        yield 'G3-055 🔴 Enrollment trỏ order khác user bị từ chối ở business layer.' => ['G3-055', 'G3-055 🔴 Enrollment trỏ order khác user bị từ chối ở business layer.'];
        yield 'G3-056 🔴 Enrollment trỏ order khác course bị từ chối.' => ['G3-056', 'G3-056 🔴 Enrollment trỏ order khác course bị từ chối.'];
        yield 'G3-057 🟢 Một order paid không tạo hai enrollment.' => ['G3-057', 'G3-057 🟢 Một order paid không tạo hai enrollment.'];
        yield 'G3-058 🟢 Wishlist item không đóng băng giá.' => ['G3-058', 'G3-058 🟢 Wishlist item không đóng băng giá.'];
        yield 'G3-059 🟢 Khi bấm mua từ wishlist, order lấy giá hiện tại.' => ['G3-059', 'G3-059 🟢 Khi bấm mua từ wishlist, order lấy giá hiện tại.'];
        yield 'G3-060 🟢 Giá phải trả tối thiểu 10.000 VND cho paid discount.' => ['G3-060', 'G3-060 🟢 Giá phải trả tối thiểu 10.000 VND cho paid discount.'];
        yield 'G3-061 🔴 Discount làm amount 1–9.999 VND bị từ chối.' => ['G3-061', 'G3-061 🔴 Discount làm amount 1–9.999 VND bị từ chối.'];
        yield 'G3-062 🟢 Amount đúng 10.000 VND được chấp nhận.' => ['G3-062', 'G3-062 🟢 Amount đúng 10.000 VND được chấp nhận.'];
        yield 'G3-063 🔴 Amount âm bị từ chối.' => ['G3-063', 'G3-063 🔴 Amount âm bị từ chối.'];
        yield 'G3-064 🟢 Transaction rollback toàn bộ khi payment side effect lỗi giữa chừng.' => ['G3-064', 'G3-064 🟢 Transaction rollback toàn bộ khi payment side effect lỗi giữa chừng.'];
        yield 'G3-065 🟢 Concurrent payment cùng order chỉ một request thắng.' => ['G3-065', 'G3-065 🟢 Concurrent payment cùng order chỉ một request thắng.'];
        yield 'G3-066 🟢 Concurrent webhook cùng transaction không duplicate side effect.' => ['G3-066', 'G3-066 🟢 Concurrent webhook cùng transaction không duplicate side effect.'];
        yield 'G3-067 🟢 Lịch sử order vẫn đọc đúng snapshot sau khi course/coupon/rule thay đổi.' => ['G3-067', 'G3-067 🟢 Lịch sử order vẫn đọc đúng snapshot sau khi course/coupon/rule thay đổi.'];
        yield 'G3-068 🟢 Expire pending order chạy lặp nhiều lần vẫn idempotent và không đổi order đã ở trạng thái terminal.' => ['G3-068', 'G3-068 🟢 Expire pending order chạy lặp nhiều lần vẫn idempotent và không đổi order đã ở trạng thái terminal.'];
    }
}