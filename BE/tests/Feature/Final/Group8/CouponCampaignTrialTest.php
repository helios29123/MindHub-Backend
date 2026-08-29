<?php

namespace Tests\Feature\Final\Group8;

use PHPUnit\Framework\Attributes\DataProvider;
use Tests\Feature\Final\Support\FinalFeatureTestCase;
use Tests\Feature\Final\Support\SourceAwareExecutor;

final class CouponCampaignTrialTest extends FinalFeatureTestCase
{
    #[DataProvider('cases')]
    public function test_nghiep_vu_final(string $id, string $nhan): void
    {
        SourceAwareExecutor::run($this, $id, $nhan);
    }

    public static function cases(): iterable
    {
        yield 'G8-001 🟢 Instructor tạo discount campaign cho course của mình.' => ['G8-001', 'G8-001 🟢 Instructor tạo discount campaign cho course của mình.'];
        yield 'G8-002 🔴 Instructor không tạo campaign cho course người khác.' => ['G8-002', 'G8-002 🔴 Instructor không tạo campaign cho course người khác.'];
        yield 'G8-003 🔴 Learner không gọi API instructor coupon.' => ['G8-003', 'G8-003 🔴 Learner không gọi API instructor coupon.'];
        yield 'G8-004 🔴 Chưa đăng nhập không gọi API instructor coupon.' => ['G8-004', 'G8-004 🔴 Chưa đăng nhập không gọi API instructor coupon.'];
        yield 'G8-005 🔴 Client không tự truyền instructor_id để chiếm ownership.' => ['G8-005', 'G8-005 🔴 Client không tự truyền instructor_id để chiếm ownership.'];
        yield 'G8-006 🔴 Client không tự truyền user_id để chiếm ownership.' => ['G8-006', 'G8-006 🔴 Client không tự truyền user_id để chiếm ownership.'];
        yield 'G8-007 🔴 Client không tự chọn coupon code.' => ['G8-007', 'G8-007 🔴 Client không tự chọn coupon code.'];
        yield 'G8-008 🟢 Backend sinh coupon code unique.' => ['G8-008', 'G8-008 🟢 Backend sinh coupon code unique.'];
        yield 'G8-009 🟢 Percent discount hợp lệ trong giới hạn.' => ['G8-009', 'G8-009 🟢 Percent discount hợp lệ trong giới hạn.'];
        yield 'G8-010 🔴 Percent discount <= 0 bị từ chối.' => ['G8-010', 'G8-010 🔴 Percent discount <= 0 bị từ chối.'];
        yield 'G8-011 🟢 Fixed discount hợp lệ.' => ['G8-011', 'G8-011 🟢 Fixed discount hợp lệ.'];
        yield 'G8-012 🟢 Percent coupon dùng `max_discount_amount` hợp lệ.' => ['G8-012', 'G8-012 🟢 Percent coupon dùng `max_discount_amount` hợp lệ.'];
        yield 'G8-013 🟢 Fixed campaign để `max_discount_amount=NULL`.' => ['G8-013', 'G8-013 🟢 Fixed campaign để `max_discount_amount=NULL`.'];
        yield 'G8-014 🔴 Fixed campaign gửi max_discount_amount trái rule bị từ chối.' => ['G8-014', 'G8-014 🔴 Fixed campaign gửi max_discount_amount trái rule bị từ chối.'];
        yield 'G8-015 🟢 Discount không làm giá phải trả dưới 10.000 VND.' => ['G8-015', 'G8-015 🟢 Discount không làm giá phải trả dưới 10.000 VND.'];
        yield 'G8-016 🔴 Giá sau giảm 9.999 VND bị từ chối.' => ['G8-016', 'G8-016 🔴 Giá sau giảm 9.999 VND bị từ chối.'];
        yield 'G8-017 🟢 Giá sau giảm đúng 10.000 VND hợp lệ.' => ['G8-017', 'G8-017 🟢 Giá sau giảm đúng 10.000 VND hợp lệ.'];
        yield 'G8-018 🟢 Đúng start_at tự có hiệu lực không cần scheduler.' => ['G8-018', 'G8-018 🟢 Đúng start_at tự có hiệu lực không cần scheduler.'];
        yield 'G8-019 🟢 Active campaign áp giá đúng.' => ['G8-019', 'G8-019 🟢 Active campaign áp giá đúng.'];
        yield 'G8-020 🟢 Qua end_at tự hết hiệu lực.' => ['G8-020', 'G8-020 🟢 Qua end_at tự hết hiệu lực.'];
        yield 'G8-021 🟢 Disable active → inactive.' => ['G8-021', 'G8-021 🟢 Disable active → inactive.'];
        yield 'G8-022 🟢 Delete coupon chỉ inactive, không hard delete.' => ['G8-022', 'G8-022 🟢 Delete coupon chỉ inactive, không hard delete.'];
        yield 'G8-023 🟢 History campaign vẫn tồn tại sau inactive.' => ['G8-023', 'G8-023 🟢 History campaign vẫn tồn tại sau inactive.'];
        yield 'G8-024 🔴 Campaign terminal `expired/used_up` không được kích hoạt lại.' => ['G8-024', 'G8-024 🔴 Campaign terminal `expired/used_up` không được kích hoạt lại.'];
        yield 'G8-025 🟢 Course không campaign tạo campaign mới được.' => ['G8-025', 'G8-025 🟢 Course không campaign tạo campaign mới được.'];
        yield 'G8-026 🔴 Active campaign overlap bị reject.' => ['G8-026', 'G8-026 🔴 Active campaign overlap bị reject.'];
        yield 'G8-027 🔴 Scheduled campaign overlap bị reject.' => ['G8-027', 'G8-027 🔴 Scheduled campaign overlap bị reject.'];
        yield 'G8-028 🔴 Active + future overlap bị reject.' => ['G8-028', 'G8-028 🔴 Active + future overlap bị reject.'];
        yield 'G8-029 🟢 Expired campaign cho phép tạo campaign mới.' => ['G8-029', 'G8-029 🟢 Expired campaign cho phép tạo campaign mới.'];
        yield 'G8-030 🟢 Inactive campaign cho phép tạo campaign mới.' => ['G8-030', 'G8-030 🟢 Inactive campaign cho phép tạo campaign mới.'];
        yield 'G8-031 🟢 Một course có nhiều campaign lịch sử.' => ['G8-031', 'G8-031 🟢 Một course có nhiều campaign lịch sử.'];
        yield 'G8-032 🟢 Hai request concurrent overlap không tạo hai campaign hợp lệ.' => ['G8-032', 'G8-032 🟢 Hai request concurrent overlap không tạo hai campaign hợp lệ.'];
        yield 'G8-033 🟢 Discount `usage_limit=NULL` nghĩa unlimited.' => ['G8-033', 'G8-033 🟢 Discount `usage_limit=NULL` nghĩa unlimited.'];
        yield 'G8-034 🟢 Discount usage_limit lớn như 1000 hợp lệ.' => ['G8-034', 'G8-034 🟢 Discount usage_limit lớn như 1000 hợp lệ.'];
        yield 'G8-035 🔴 usage_limit < used_count bị từ chối.' => ['G8-035', 'G8-035 🔴 usage_limit < used_count bị từ chối.'];
        yield 'G8-036 🟢 Tạo pending order chưa tăng used_count.' => ['G8-036', 'G8-036 🟢 Tạo pending order chưa tăng used_count.'];
        yield 'G8-037 🟢 Payment success tăng used_count đúng một lần.' => ['G8-037', 'G8-037 🟢 Payment success tăng used_count đúng một lần.'];
        yield 'G8-038 🔴 Payment fail không tăng used_count.' => ['G8-038', 'G8-038 🔴 Payment fail không tăng used_count.'];
        yield 'G8-039 🔴 Cancel order không tăng used_count.' => ['G8-039', 'G8-039 🔴 Cancel order không tăng used_count.'];
        yield 'G8-040 🟢 Webhook lặp không tăng used_count hai lần.' => ['G8-040', 'G8-040 🟢 Webhook lặp không tăng used_count hai lần.'];
        yield 'G8-041 🟢 Lượt dùng cuối chuyển campaign → used_up.' => ['G8-041', 'G8-041 🟢 Lượt dùng cuối chuyển campaign → used_up.'];
        yield 'G8-042 🟢 used_up reset/sync sale_price đúng.' => ['G8-042', 'G8-042 🟢 used_up reset/sync sale_price đúng.'];
        yield 'G8-043 🟢 Hai payment tranh lượt cuối không vượt usage_limit.' => ['G8-043', 'G8-043 🟢 Hai payment tranh lượt cuối không vượt usage_limit.'];
        yield 'G8-044 🟢 Double disable không corrupt state.' => ['G8-044', 'G8-044 🟢 Double disable không corrupt state.'];
        yield 'G8-045 🟢 Retry fail→success chỉ side effect một lần.' => ['G8-045', 'G8-045 🟢 Retry fail→success chỉ side effect một lần.'];
        yield 'G8-046 🟢 Order snapshot đúng giá gốc/discount/amount/coupon.' => ['G8-046', 'G8-046 🟢 Order snapshot đúng giá gốc/discount/amount/coupon.'];
        yield 'G8-047 🟢 OrderService không tin `sale_price` cache stale.' => ['G8-047', 'G8-047 🟢 OrderService không tin `sale_price` cache stale.'];
        yield 'G8-048 🟢 Pending order giữ snapshot sau coupon inactive.' => ['G8-048', 'G8-048 🟢 Pending order giữ snapshot sau coupon inactive.'];
        yield 'G8-049 🟢 Pending order giữ snapshot sau campaign expired.' => ['G8-049', 'G8-049 🟢 Pending order giữ snapshot sau campaign expired.'];
        yield 'G8-050 🟢 Double click mua không tạo hai pending order.' => ['G8-050', 'G8-050 🟢 Double click mua không tạo hai pending order.'];
        yield 'G8-051 🟢 User A giữ snapshot; user B không được giảm sau used_up.' => ['G8-051', 'G8-051 🟢 User A giữ snapshot; user B không được giảm sau used_up.'];
        yield 'G8-052 🟢 Đổi course price khi campaign active tính lại sale_price.' => ['G8-052', 'G8-052 🟢 Đổi course price khi campaign active tính lại sale_price.'];
        yield 'G8-053 🟢 Pending order cũ không đổi sau course đổi giá.' => ['G8-053', 'G8-053 🟢 Pending order cũ không đổi sau course đổi giá.'];
        yield 'G8-054 🔴 Client không ép sale_price/discount_amount/amount.' => ['G8-054', 'G8-054 🔴 Client không ép sale_price/discount_amount/amount.'];
        yield 'G8-055 🟢 Trial campaign có các discount field nullable đúng schema.' => ['G8-055', 'G8-055 🟢 Trial campaign có các discount field nullable đúng schema.'];
        yield 'G8-056 🟢 Trial duration chỉ 1–3 ngày.' => ['G8-056', 'G8-056 🟢 Trial duration chỉ 1–3 ngày.'];
        yield 'G8-057 🔴 Trial duration ngoài 1–3 bị từ chối.' => ['G8-057', 'G8-057 🔴 Trial duration ngoài 1–3 bị từ chối.'];
        yield 'G8-058 🟢 Trial tạo paid zero order + enrollment 7 ngày + tăng used_count.' => ['G8-058', 'G8-058 🟢 Trial tạo paid zero order + enrollment 7 ngày + tăng used_count.'];
        yield 'G8-059 🟢 Trial không cần gateway/webhook.' => ['G8-059', 'G8-059 🟢 Trial không cần gateway/webhook.'];
        yield 'G8-060 🟢 Trial không tạo revenue.' => ['G8-060', 'G8-060 🟢 Trial không tạo revenue.'];
        yield 'G8-061 🟢 Learner chỉ trial một lần mỗi course.' => ['G8-061', 'G8-061 🟢 Learner chỉ trial một lần mỗi course.'];
        yield 'G8-062 🔴 Instructor không tạo trial thứ ba trong cùng tháng.' => ['G8-062', 'G8-062 🔴 Instructor không tạo trial thứ ba trong cùng tháng.'];
        yield 'G8-063 🟢 Trial→paid thật reuse enrollment và giữ progress.' => ['G8-063', 'G8-063 🟢 Trial→paid thật reuse enrollment và giữ progress.'];
        yield 'G8-064 🟢 Sửa cùng một trial campaign nhiều lần không bị tính thành nhiều campaign trong quota tháng.' => ['G8-064', 'G8-064 🟢 Sửa cùng một trial campaign nhiều lần không bị tính thành nhiều campaign trong quota tháng.'];
        yield 'G8-065 🟢 Instructor ở danh sách quản lý hiện tại không thấy campaign đã `inactive/expired/used_up` nếu API current-list chỉ trả campaign còn quản lý được.' => ['G8-065', 'G8-065 🟢 Instructor ở danh sách quản lý hiện tại không thấy campaign đã `inactive/expired/used_up` nếu API current-list chỉ trả campaign còn quản lý được.'];
        yield 'G8-066 🟢 Admin có thể truy vấn lịch sử campaign đầy đủ theo các trạng thái FINAL mà không làm mất record lịch sử.' => ['G8-066', 'G8-066 🟢 Admin có thể truy vấn lịch sử campaign đầy đủ theo các trạng thái FINAL mà không làm mất record lịch sử.'];
        yield 'G8-067 🔴 Instructor A không xem detail/history campaign thuộc instructor B.' => ['G8-067', 'G8-067 🔴 Instructor A không xem detail/history campaign thuộc instructor B.'];
        yield 'G8-068 🟢 Khi campaign thay đổi trạng thái làm mất hiệu lực, `sale_price` cache được đồng bộ lại đúng giá hiện hành.' => ['G8-068', 'G8-068 🟢 Khi campaign thay đổi trạng thái làm mất hiệu lực, `sale_price` cache được đồng bộ lại đúng giá hiện hành.'];
    }
}