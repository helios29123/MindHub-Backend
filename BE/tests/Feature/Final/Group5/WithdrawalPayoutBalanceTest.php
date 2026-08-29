<?php

namespace Tests\Feature\Final\Group5;

use PHPUnit\Framework\Attributes\DataProvider;
use Tests\Feature\Final\Support\FinalFeatureTestCase;
use Tests\Feature\Final\Support\SourceAwareExecutor;

final class WithdrawalPayoutBalanceTest extends FinalFeatureTestCase
{
    #[DataProvider('cases')]
    public function test_nghiep_vu_final(string $id, string $nhan): void
    {
        SourceAwareExecutor::run($this, $id, $nhan);
    }

    public static function cases(): iterable
    {
        yield 'G5-001 🟢 Instructor tạo payout account pending_verification.' => ['G5-001', 'G5-001 🟢 Instructor tạo payout account pending_verification.'];
        yield 'G5-002 🟢 Admin verify payout account thành verified.' => ['G5-002', 'G5-002 🟢 Admin verify payout account thành verified.'];
        yield 'G5-003 🔴 Payout account chưa verified không được set default.' => ['G5-003', 'G5-003 🔴 Payout account chưa verified không được set default.'];
        yield 'G5-004 🟢 Verified account được set default.' => ['G5-004', 'G5-004 🟢 Verified account được set default.'];
        yield 'G5-005 🔴 Một user không có hai payout account default.' => ['G5-005', 'G5-005 🔴 Một user không có hai payout account default.'];
        yield 'G5-006 🟢 Disable payout account đang không default.' => ['G5-006', 'G5-006 🟢 Disable payout account đang không default.'];
        yield 'G5-007 🟢 Disable payout account default xử lý default theo rule.' => ['G5-007', 'G5-007 🟢 Disable payout account default xử lý default theo rule.'];
        yield 'G5-008 🔴 Learner không được tạo payout account instructor.' => ['G5-008', 'G5-008 🔴 Learner không được tạo payout account instructor.'];
        yield 'G5-009 🔴 Instructor A không xem/sửa payout account của B.' => ['G5-009', 'G5-009 🔴 Instructor A không xem/sửa payout account của B.'];
        yield 'G5-010 🟢 Account number snapshot giữ nguyên trong withdrawal.' => ['G5-010', 'G5-010 🟢 Account number snapshot giữ nguyên trong withdrawal.'];
        yield 'G5-011 🟢 Account name snapshot giữ nguyên trong withdrawal.' => ['G5-011', 'G5-011 🟢 Account name snapshot giữ nguyên trong withdrawal.'];
        yield 'G5-012 🟢 Bank/provider snapshot giữ nguyên trong withdrawal.' => ['G5-012', 'G5-012 🟢 Bank/provider snapshot giữ nguyên trong withdrawal.'];
        yield 'G5-013 🟢 Instructor gửi withdrawal khi đủ available balance.' => ['G5-013', 'G5-013 🟢 Instructor gửi withdrawal khi đủ available balance.'];
        yield 'G5-014 🔴 Withdrawal vượt available balance bị từ chối.' => ['G5-014', 'G5-014 🔴 Withdrawal vượt available balance bị từ chối.'];
        yield 'G5-015 🔴 Withdrawal amount <= 0 bị từ chối.' => ['G5-015', 'G5-015 🔴 Withdrawal amount <= 0 bị từ chối.'];
        yield 'G5-016 🟢 Khi tạo withdrawal, available_balance_before được snapshot.' => ['G5-016', 'G5-016 🟢 Khi tạo withdrawal, available_balance_before được snapshot.'];
        yield 'G5-017 🟢 Khi allocate, available_balance_after đúng.' => ['G5-017', 'G5-017 🟢 Khi allocate, available_balance_after đúng.'];
        yield 'G5-018 🟢 Withdrawal status ban đầu pending.' => ['G5-018', 'G5-018 🟢 Withdrawal status ban đầu pending.'];
        yield 'G5-019 🟢 Admin approve pending → approved.' => ['G5-019', 'G5-019 🟢 Admin approve pending → approved.'];
        yield 'G5-020 🔴 Instructor tự approve withdrawal bị chặn.' => ['G5-020', 'G5-020 🔴 Instructor tự approve withdrawal bị chặn.'];
        yield 'G5-021 🔴 Approve withdrawal của user khác bằng quyền instructor bị chặn.' => ['G5-021', 'G5-021 🔴 Approve withdrawal của user khác bằng quyền instructor bị chặn.'];
        yield 'G5-022 🟢 Approved → processing khi bắt đầu payout.' => ['G5-022', 'G5-022 🟢 Approved → processing khi bắt đầu payout.'];
        yield 'G5-023 🟢 Processing → paid khi chuyển tiền thành công.' => ['G5-023', 'G5-023 🟢 Processing → paid khi chuyển tiền thành công.'];
        yield 'G5-024 🟢 paid_at được ghi khi paid.' => ['G5-024', 'G5-024 🟢 paid_at được ghi khi paid.'];
        yield 'G5-025 🟢 processed_at được ghi theo flow.' => ['G5-025', 'G5-025 🟢 processed_at được ghi theo flow.'];
        yield 'G5-026 🟢 provider_payout_id lưu khi provider trả id.' => ['G5-026', 'G5-026 🟢 provider_payout_id lưu khi provider trả id.'];
        yield 'G5-027 🔴 provider_payout_id duplicate bị từ chối.' => ['G5-027', 'G5-027 🔴 provider_payout_id duplicate bị từ chối.'];
        yield 'G5-028 🟢 Payout lỗi tự động chuyển failed hoặc manual_required đúng rule.' => ['G5-028', 'G5-028 🟢 Payout lỗi tự động chuyển failed hoặc manual_required đúng rule.'];
        yield 'G5-029 🟢 failure_reason lưu khi provider lỗi.' => ['G5-029', 'G5-029 🟢 failure_reason lưu khi provider lỗi.'];
        yield 'G5-030 🟢 Admin có thể mark_failed đúng state.' => ['G5-030', 'G5-030 🟢 Admin có thể mark_failed đúng state.'];
        yield 'G5-031 🟢 Admin có thể chuyển manual_required theo rule.' => ['G5-031', 'G5-031 🟢 Admin có thể chuyển manual_required theo rule.'];
        yield 'G5-032 🟢 Admin manual mark-paid có audit fields cần thiết.' => ['G5-032', 'G5-032 🟢 Admin manual mark-paid có audit fields cần thiết.'];
        yield 'G5-033 🔴 Không approve withdrawal đã paid.' => ['G5-033', 'G5-033 🔴 Không approve withdrawal đã paid.'];
        yield 'G5-034 🔴 Không reject withdrawal đã paid.' => ['G5-034', 'G5-034 🔴 Không reject withdrawal đã paid.'];
        yield 'G5-035 🟢 Admin reject pending withdrawal và lưu rejected_reason.' => ['G5-035', 'G5-035 🟢 Admin reject pending withdrawal và lưu rejected_reason.'];
        yield 'G5-036 🟢 Balance được hoàn lại khi withdrawal reject/cancel đúng rule.' => ['G5-036', 'G5-036 🟢 Balance được hoàn lại khi withdrawal reject/cancel đúng rule.'];
        yield 'G5-037 🟢 Balance không hoàn lại hai lần khi retry callback.' => ['G5-037', 'G5-037 🟢 Balance không hoàn lại hai lần khi retry callback.'];
        yield 'G5-038 🟢 Withdrawal allocation liên kết đúng revenue.' => ['G5-038', 'G5-038 🟢 Withdrawal allocation liên kết đúng revenue.'];
        yield 'G5-039 🟢 Một withdrawal có nhiều revenue allocation.' => ['G5-039', 'G5-039 🟢 Một withdrawal có nhiều revenue allocation.'];
        yield 'G5-040 🔴 Tổng allocation vượt withdrawal amount bị từ chối.' => ['G5-040', 'G5-040 🔴 Tổng allocation vượt withdrawal amount bị từ chối.'];
        yield 'G5-041 🔴 Tổng allocation vượt revenue available bị từ chối.' => ['G5-041', 'G5-041 🔴 Tổng allocation vượt revenue available bị từ chối.'];
        yield 'G5-042 🟢 Concurrent hai withdrawal không tiêu cùng balance.' => ['G5-042', 'G5-042 🟢 Concurrent hai withdrawal không tiêu cùng balance.'];
        yield 'G5-043 🟢 lockForUpdate/transaction bảo vệ race condition balance.' => ['G5-043', 'G5-043 🟢 lockForUpdate/transaction bảo vệ race condition balance.'];
        yield 'G5-044 🟢 Retry provider idempotent không tạo hai payout.' => ['G5-044', 'G5-044 🟢 Retry provider idempotent không tạo hai payout.'];
        yield 'G5-045 🟢 Timeout provider nhưng response retry xử lý đúng.' => ['G5-045', 'G5-045 🟢 Timeout provider nhưng response retry xử lý đúng.'];
        yield 'G5-046 🟢 Manual payout không phá idempotency provider.' => ['G5-046', 'G5-046 🟢 Manual payout không phá idempotency provider.'];
        yield 'G5-047 🟢 Admin list filter pending đúng.' => ['G5-047', 'G5-047 🟢 Admin list filter pending đúng.'];
        yield 'G5-048 🟢 Admin list filter processing đúng.' => ['G5-048', 'G5-048 🟢 Admin list filter processing đúng.'];
        yield 'G5-049 🟢 Admin list filter manual_required đúng.' => ['G5-049', 'G5-049 🟢 Admin list filter manual_required đúng.'];
        yield 'G5-050 🟢 Admin list filter paid đúng.' => ['G5-050', 'G5-050 🟢 Admin list filter paid đúng.'];
        yield 'G5-051 🟢 Admin list filter failed đúng.' => ['G5-051', 'G5-051 🟢 Admin list filter failed đúng.'];
        yield 'G5-052 🟢 Instructor list chỉ thấy withdrawal của mình.' => ['G5-052', 'G5-052 🟢 Instructor list chỉ thấy withdrawal của mình.'];
        yield 'G5-053 🟢 Detail withdrawal trả đúng payout snapshot.' => ['G5-053', 'G5-053 🟢 Detail withdrawal trả đúng payout snapshot.'];
        yield 'G5-054 🔴 Không trả số tài khoản nhạy cảm quá mức cho role không phù hợp.' => ['G5-054', 'G5-054 🔴 Không trả số tài khoản nhạy cảm quá mức cho role không phù hợp.'];
        yield 'G5-055 🟢 OTP đổi payout account được yêu cầu đúng flow.' => ['G5-055', 'G5-055 🟢 OTP đổi payout account được yêu cầu đúng flow.'];
        yield 'G5-056 🔴 OTP sai không đổi payout account.' => ['G5-056', 'G5-056 🔴 OTP sai không đổi payout account.'];
        yield 'G5-057 🔴 OTP hết hạn không đổi payout account.' => ['G5-057', 'G5-057 🔴 OTP hết hạn không đổi payout account.'];
        yield 'G5-058 🔴 OTP đã dùng không được dùng lại.' => ['G5-058', 'G5-058 🔴 OTP đã dùng không được dùng lại.'];
        yield 'G5-059 🟢 Payout account change sau OTP không làm thay đổi snapshot withdrawal cũ.' => ['G5-059', 'G5-059 🟢 Payout account change sau OTP không làm thay đổi snapshot withdrawal cũ.'];
        yield 'G5-060 🟢 Admin chuyển thủ công không sửa revenue snapshot.' => ['G5-060', 'G5-060 🟢 Admin chuyển thủ công không sửa revenue snapshot.'];
        yield 'G5-061 🟢 Paid withdrawal vẫn truy vết được revenue allocations.' => ['G5-061', 'G5-061 🟢 Paid withdrawal vẫn truy vết được revenue allocations.'];
        yield 'G5-062 🟢 Xóa/disable payout account không xóa lịch sử withdrawal.' => ['G5-062', 'G5-062 🟢 Xóa/disable payout account không xóa lịch sử withdrawal.'];
        yield 'G5-063 🟢 Transaction rollback toàn bộ khi allocation lỗi giữa chừng.' => ['G5-063', 'G5-063 🟢 Transaction rollback toàn bộ khi allocation lỗi giữa chừng.'];
    }
}