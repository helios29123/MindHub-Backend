<?php

namespace Tests\Feature\Final\Group4;

use PHPUnit\Framework\Attributes\DataProvider;
use Tests\Feature\Final\Support\FinalFeatureTestCase;
use Tests\Feature\Final\Support\SourceAwareExecutor;

final class RevenueCommissionTest extends FinalFeatureTestCase
{
    #[DataProvider('cases')]
    public function test_nghiep_vu_final(string $id, string $nhan): void
    {
        SourceAwareExecutor::run($this, $id, $nhan);
    }

    public static function cases(): iterable
    {
        yield 'G4-001 🟢 Paid order hợp lệ tạo đúng một revenue.' => ['G4-001', 'G4-001 🟢 Paid order hợp lệ tạo đúng một revenue.'];
        yield 'G4-002 🔴 Pending order không tạo revenue.' => ['G4-002', 'G4-002 🔴 Pending order không tạo revenue.'];
        yield 'G4-003 🔴 Failed order không tạo revenue.' => ['G4-003', 'G4-003 🔴 Failed order không tạo revenue.'];
        yield 'G4-004 🔴 Cancelled order không tạo revenue.' => ['G4-004', 'G4-004 🔴 Cancelled order không tạo revenue.'];
        yield 'G4-005 🔴 Trial zero order không tạo revenue.' => ['G4-005', 'G4-005 🔴 Trial zero order không tạo revenue.'];
        yield 'G4-006 🟢 Revenue lưu đúng instructor_id.' => ['G4-006', 'G4-006 🟢 Revenue lưu đúng instructor_id.'];
        yield 'G4-007 🟢 Revenue lưu đúng course_id.' => ['G4-007', 'G4-007 🟢 Revenue lưu đúng course_id.'];
        yield 'G4-008 🟢 Revenue lưu đúng order_id.' => ['G4-008', 'G4-008 🟢 Revenue lưu đúng order_id.'];
        yield 'G4-009 🟢 Revenue lưu đúng commission_rule_id.' => ['G4-009', 'G4-009 🟢 Revenue lưu đúng commission_rule_id.'];
        yield 'G4-010 🟢 Revenue gross_amount bằng amount order paid.' => ['G4-010', 'G4-010 🟢 Revenue gross_amount bằng amount order paid.'];
        yield 'G4-011 🟢 instructor_amount tính theo instructor_rate snapshot.' => ['G4-011', 'G4-011 🟢 instructor_amount tính theo instructor_rate snapshot.'];
        yield 'G4-012 🟢 platform_fee_amount tính theo platform_rate snapshot.' => ['G4-012', 'G4-012 🟢 platform_fee_amount tính theo platform_rate snapshot.'];
        yield 'G4-013 🟢 instructor_amount + platform_fee_amount = gross_amount.' => ['G4-013', 'G4-013 🟢 instructor_amount + platform_fee_amount = gross_amount.'];
        yield 'G4-014 🟢 Rate 80/20 chia đúng số tiền.' => ['G4-014', 'G4-014 🟢 Rate 80/20 chia đúng số tiền.'];
        yield 'G4-015 🟢 Rate 70/30 chia đúng số tiền.' => ['G4-015', 'G4-015 🟢 Rate 70/30 chia đúng số tiền.'];
        yield 'G4-016 🟢 Rate 75/25 chia đúng số tiền.' => ['G4-016', 'G4-016 🟢 Rate 75/25 chia đúng số tiền.'];
        yield 'G4-017 🟢 Rate có decimal precision chia đúng.' => ['G4-017', 'G4-017 🟢 Rate có decimal precision chia đúng.'];
        yield 'G4-018 🟢 Quy tắc làm tròn VND nhất quán.' => ['G4-018', 'G4-018 🟢 Quy tắc làm tròn VND nhất quán.'];
        yield 'G4-019 🟢 Giá lẻ sau giảm vẫn chia tiền đúng helper rounding.' => ['G4-019', 'G4-019 🟢 Giá lẻ sau giảm vẫn chia tiền đúng helper rounding.'];
        yield 'G4-020 🟢 Thay commission rule sau khi order tạo không đổi revenue của order cũ.' => ['G4-020', 'G4-020 🟢 Thay commission rule sau khi order tạo không đổi revenue của order cũ.'];
        yield 'G4-021 🟢 Order cũ giữ commission_rule_id cũ.' => ['G4-021', 'G4-021 🟢 Order cũ giữ commission_rule_id cũ.'];
        yield 'G4-022 🟢 Order mới dùng rule active mới.' => ['G4-022', 'G4-022 🟢 Order mới dùng rule active mới.'];
        yield 'G4-023 🔴 Không được sửa rate của commission rule đã được order/revenue tham chiếu; phải tạo rule mới.' => ['G4-023', 'G4-023 🔴 Không được sửa rate của commission rule đã được order/revenue tham chiếu; phải tạo rule mới.'];
        yield 'G4-024 🟢 Tạo rule mới thay cho sửa rule cũ.' => ['G4-024', 'G4-024 🟢 Tạo rule mới thay cho sửa rule cũ.'];
        yield 'G4-025 🔴 Không cho tồn tại đồng thời hai commission rule active của scope hiện tại.' => ['G4-025', 'G4-025 🔴 Không cho tồn tại đồng thời hai commission rule active của scope hiện tại.'];
        yield 'G4-026 🟢 Tắt rule cũ rồi bật rule mới thành công.' => ['G4-026', 'G4-026 🟢 Tắt rule cũ rồi bật rule mới thành công.'];
        yield 'G4-027 🟢 Revenue không có `status` legacy; vòng đời dựa trên snapshot/thời gian/allocation theo schema FINAL.' => ['G4-027', 'G4-027 🟢 Revenue không có `status` legacy; vòng đời dựa trên snapshot/thời gian/allocation theo schema FINAL.'];
        yield 'G4-028 🟢 earned_at được ghi khi revenue phát sinh.' => ['G4-028', 'G4-028 🟢 earned_at được ghi khi revenue phát sinh.'];
        yield 'G4-029 🟢 Revenue vẫn tồn tại khi course đổi giá.' => ['G4-029', 'G4-029 🟢 Revenue vẫn tồn tại khi course đổi giá.'];
        yield 'G4-030 🟢 Revenue vẫn tồn tại khi coupon bị inactive.' => ['G4-030', 'G4-030 🟢 Revenue vẫn tồn tại khi coupon bị inactive.'];
        yield 'G4-031 🟢 Revenue vẫn tồn tại khi commission rule bị thay thế.' => ['G4-031', 'G4-031 🟢 Revenue vẫn tồn tại khi commission rule bị thay thế.'];
        yield 'G4-032 🔴 Không tạo duplicate revenue cho cùng order.' => ['G4-032', 'G4-032 🔴 Không tạo duplicate revenue cho cùng order.'];
        yield 'G4-033 🟢 Retry side effect sau timeout không duplicate revenue.' => ['G4-033', 'G4-033 🟢 Retry side effect sau timeout không duplicate revenue.'];
        yield 'G4-034 🟢 Webhook lặp không duplicate revenue.' => ['G4-034', 'G4-034 🟢 Webhook lặp không duplicate revenue.'];
        yield 'G4-035 🟢 `syncMissingPaidOrderRevenues` chỉ backfill paid order có amount > 0.' => ['G4-035', 'G4-035 🟢 `syncMissingPaidOrderRevenues` chỉ backfill paid order có amount > 0.'];
        yield 'G4-036 🔴 `syncMissingPaidOrderRevenues` bỏ qua trial amount=0.' => ['G4-036', 'G4-036 🔴 `syncMissingPaidOrderRevenues` bỏ qua trial amount=0.'];
        yield 'G4-037 🟢 RevenueShareService trả null đúng cho zero-amount order.' => ['G4-037', 'G4-037 🟢 RevenueShareService trả null đúng cho zero-amount order.'];
        yield 'G4-038 🟢 RevenueShareService vẫn tạo revenue cho paid order thường.' => ['G4-038', 'G4-038 🟢 RevenueShareService vẫn tạo revenue cho paid order thường.'];
        yield 'G4-039 🟢 Admin revenue list thấy đúng gross/instructor/platform.' => ['G4-039', 'G4-039 🟢 Admin revenue list thấy đúng gross/instructor/platform.'];
        yield 'G4-040 🟢 Instructor chỉ thấy revenue của mình.' => ['G4-040', 'G4-040 🟢 Instructor chỉ thấy revenue của mình.'];
        yield 'G4-041 🔴 Instructor A không xem revenue của instructor B.' => ['G4-041', 'G4-041 🔴 Instructor A không xem revenue của instructor B.'];
        yield 'G4-042 🟢 Filter revenue theo course đúng.' => ['G4-042', 'G4-042 🟢 Filter revenue theo course đúng.'];
        yield 'G4-043 🟢 Filter revenue theo ngày đúng.' => ['G4-043', 'G4-043 🟢 Filter revenue theo ngày đúng.'];
        yield 'G4-044 🟢 Tổng revenue dashboard bằng tổng dòng phù hợp.' => ['G4-044', 'G4-044 🟢 Tổng revenue dashboard bằng tổng dòng phù hợp.'];
        yield 'G4-045 🟢 Tổng platform fee bằng tổng platform_fee_amount.' => ['G4-045', 'G4-045 🟢 Tổng platform fee bằng tổng platform_fee_amount.'];
        yield 'G4-046 🟢 Tổng instructor earning bằng tổng instructor_amount.' => ['G4-046', 'G4-046 🟢 Tổng instructor earning bằng tổng instructor_amount.'];
        yield 'G4-047 🟢 Revenue có thể được phân bổ vào withdrawal.' => ['G4-047', 'G4-047 🟢 Revenue có thể được phân bổ vào withdrawal.'];
        yield 'G4-048 🟢 Một revenue có thể bị phân bổ theo rule FINAL đúng giới hạn.' => ['G4-048', 'G4-048 🟢 Một revenue có thể bị phân bổ theo rule FINAL đúng giới hạn.'];
        yield 'G4-049 🔴 Tổng allocated_amount không vượt instructor_amount khả dụng.' => ['G4-049', 'G4-049 🔴 Tổng allocated_amount không vượt instructor_amount khả dụng.'];
        yield 'G4-050 🟢 Revenue chưa rút được tính vào available balance theo rule.' => ['G4-050', 'G4-050 🟢 Revenue chưa rút được tính vào available balance theo rule.'];
        yield 'G4-051 🟢 Revenue đã allocate đủ không còn available.' => ['G4-051', 'G4-051 🟢 Revenue đã allocate đủ không còn available.'];
        yield 'G4-052 🟢 Revenue allocate một phần còn phần dư available.' => ['G4-052', 'G4-052 🟢 Revenue allocate một phần còn phần dư available.'];
        yield 'G4-053 🟢 Concurrent withdrawal allocation không chiếm cùng balance hai lần.' => ['G4-053', 'G4-053 🟢 Concurrent withdrawal allocation không chiếm cùng balance hai lần.'];
        yield 'G4-054 🟢 Transaction rollback allocation khi withdrawal tạo lỗi.' => ['G4-054', 'G4-054 🟢 Transaction rollback allocation khi withdrawal tạo lỗi.'];
        yield 'G4-055 🔴 Revenue với instructor khác withdrawal owner không được allocate.' => ['G4-055', 'G4-055 🔴 Revenue với instructor khác withdrawal owner không được allocate.'];
        yield 'G4-056 🔴 Revenue âm hoặc zero không được coi là available payout.' => ['G4-056', 'G4-056 🔴 Revenue âm hoặc zero không được coi là available payout.'];
        yield 'G4-057 🟢 Revenue từ paid order cũ vẫn truy vết được order_code.' => ['G4-057', 'G4-057 🟢 Revenue từ paid order cũ vẫn truy vết được order_code.'];
        yield 'G4-058 🟢 Admin order detail biết paid order có revenue.' => ['G4-058', 'G4-058 🟢 Admin order detail biết paid order có revenue.'];
        yield 'G4-059 🔴 Paid order thiếu revenue được detect là inconsistency.' => ['G4-059', 'G4-059 🔴 Paid order thiếu revenue được detect là inconsistency.'];
        yield 'G4-060 🟢 Trial paid zero không bị đánh inconsistency vì không cần revenue.' => ['G4-060', 'G4-060 🟢 Trial paid zero không bị đánh inconsistency vì không cần revenue.'];
        yield 'G4-061 🟢 Số tiền commission không tính lại theo course price hiện tại.' => ['G4-061', 'G4-061 🟢 Số tiền commission không tính lại theo course price hiện tại.'];
        yield 'G4-062 🟢 Số tiền commission không tính lại theo rule hiện tại.' => ['G4-062', 'G4-062 🟢 Số tiền commission không tính lại theo rule hiện tại.'];
        yield 'G4-063 🟢 Snapshot bảo toàn lịch sử 70/30 khi admin đổi 80/20.' => ['G4-063', 'G4-063 🟢 Snapshot bảo toàn lịch sử 70/30 khi admin đổi 80/20.'];
        yield 'G4-064 🟢 Report lịch sử hiển thị đúng rule đã áp dụng lúc bán.' => ['G4-064', 'G4-064 🟢 Report lịch sử hiển thị đúng rule đã áp dụng lúc bán.'];
        yield 'G4-065 🟢 Không dùng float sai lệch khi tính tiền.' => ['G4-065', 'G4-065 🟢 Không dùng float sai lệch khi tính tiền.'];
        yield 'G4-066 🟢 Các amount cast/serialize API đúng định dạng.' => ['G4-066', 'G4-066 🟢 Các amount cast/serialize API đúng định dạng.'];
        yield 'G4-067 🟢 Revenue FK bảo vệ dữ liệu orphan.' => ['G4-067', 'G4-067 🟢 Revenue FK bảo vệ dữ liệu orphan.'];
        yield 'G4-068 🟢 Xóa user/course theo rule FK không làm mất revenue lịch sử ngoài ý muốn.' => ['G4-068', 'G4-068 🟢 Xóa user/course theo rule FK không làm mất revenue lịch sử ngoài ý muốn.'];
        yield 'G4-069 🟢 Chạy batch sync nhiều lần vẫn idempotent.' => ['G4-069', 'G4-069 🟢 Chạy batch sync nhiều lần vẫn idempotent.'];
        yield 'G4-070 🟢 Concurrent finalize payment + sync revenue không duplicate row.' => ['G4-070', 'G4-070 🟢 Concurrent finalize payment + sync revenue không duplicate row.'];
    }
}