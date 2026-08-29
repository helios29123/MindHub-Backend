<?php

namespace Tests\Feature\Final\Group6;

use PHPUnit\Framework\Attributes\DataProvider;
use Tests\Feature\Final\Support\FinalFeatureTestCase;
use Tests\Feature\Final\Support\SourceAwareExecutor;

final class AuthSessionOtpSecurityTest extends FinalFeatureTestCase
{
    #[DataProvider('cases')]
    public function test_nghiep_vu_final(string $id, string $nhan): void
    {
        SourceAwareExecutor::run($this, $id, $nhan);
    }

    public static function cases(): iterable
    {
        yield 'G6-001 🟢 Learner đăng ký bằng email hợp lệ.' => ['G6-001', 'G6-001 🟢 Learner đăng ký bằng email hợp lệ.'];
        yield 'G6-002 🟢 Instructor đăng ký bằng flow instructor hợp lệ.' => ['G6-002', 'G6-002 🟢 Instructor đăng ký bằng flow instructor hợp lệ.'];
        yield 'G6-003 🔴 Đăng ký email trùng bị từ chối.' => ['G6-003', 'G6-003 🔴 Đăng ký email trùng bị từ chối.'];
        yield 'G6-004 🔴 Đăng ký phone trùng bị từ chối nếu phone có giá trị.' => ['G6-004', 'G6-004 🔴 Đăng ký phone trùng bị từ chối nếu phone có giá trị.'];
        yield 'G6-005 🟢 Nhiều tài khoản phone NULL vẫn hợp lệ.' => ['G6-005', 'G6-005 🟢 Nhiều tài khoản phone NULL vẫn hợp lệ.'];
        yield 'G6-006 🔴 Password quá yếu bị validation từ chối.' => ['G6-006', 'G6-006 🔴 Password quá yếu bị validation từ chối.'];
        yield 'G6-007 🔴 Email sai định dạng bị từ chối.' => ['G6-007', 'G6-007 🔴 Email sai định dạng bị từ chối.'];
        yield 'G6-008 🔴 Role từ client cố ép thành admin bị chặn.' => ['G6-008', 'G6-008 🔴 Role từ client cố ép thành admin bị chặn.'];
        yield 'G6-009 🔴 Client không mass assign `locked`, `status`, `email_verified_at`.' => ['G6-009', 'G6-009 🔴 Client không mass assign `locked`, `status`, `email_verified_at`.'];
        yield 'G6-010 🟢 Password được lưu vào `password_hash`.' => ['G6-010', 'G6-010 🟢 Password được lưu vào `password_hash`.'];
        yield 'G6-011 🔴 Không lưu plain password vào DB.' => ['G6-011', 'G6-011 🔴 Không lưu plain password vào DB.'];
        yield 'G6-012 🟢 Login đúng email/password thành công.' => ['G6-012', 'G6-012 🟢 Login đúng email/password thành công.'];
        yield 'G6-013 🔴 Login sai password bị từ chối.' => ['G6-013', 'G6-013 🔴 Login sai password bị từ chối.'];
        yield 'G6-014 🔴 Login email không tồn tại bị từ chối.' => ['G6-014', 'G6-014 🔴 Login email không tồn tại bị từ chối.'];
        yield 'G6-015 🔴 User inactive không login.' => ['G6-015', 'G6-015 🔴 User inactive không login.'];
        yield 'G6-016 🔴 User suspended không login.' => ['G6-016', 'G6-016 🔴 User suspended không login.'];
        yield 'G6-017 🔴 User locked không login.' => ['G6-017', 'G6-017 🔴 User locked không login.'];
        yield 'G6-018 🟢 last_login_at cập nhật khi login thành công.' => ['G6-018', 'G6-018 🟢 last_login_at cập nhật khi login thành công.'];
        yield 'G6-019 🟢 Login tạo session/refresh token hợp lệ.' => ['G6-019', 'G6-019 🟢 Login tạo session/refresh token hợp lệ.'];
        yield 'G6-020 🟢 Refresh token được lưu dạng hash.' => ['G6-020', 'G6-020 🟢 Refresh token được lưu dạng hash.'];
        yield 'G6-021 🔴 Refresh token raw không xuất hiện trong DB.' => ['G6-021', 'G6-021 🔴 Refresh token raw không xuất hiện trong DB.'];
        yield 'G6-022 🔴 Refresh token giả bị từ chối.' => ['G6-022', 'G6-022 🔴 Refresh token giả bị từ chối.'];
        yield 'G6-023 🔴 Refresh token hết hạn bị từ chối.' => ['G6-023', 'G6-023 🔴 Refresh token hết hạn bị từ chối.'];
        yield 'G6-024 🔴 Refresh token revoked bị từ chối.' => ['G6-024', 'G6-024 🔴 Refresh token revoked bị từ chối.'];
        yield 'G6-025 🟢 Logout revoke session hiện tại.' => ['G6-025', 'G6-025 🟢 Logout revoke session hiện tại.'];
        yield 'G6-026 🔴 Token sau logout không dùng lại được.' => ['G6-026', 'G6-026 🔴 Token sau logout không dùng lại được.'];
        yield 'G6-027 🟢 Session ghi device_name.' => ['G6-027', 'G6-027 🟢 Session ghi device_name.'];
        yield 'G6-028 🟢 Session ghi ip_address.' => ['G6-028', 'G6-028 🟢 Session ghi ip_address.'];
        yield 'G6-029 🟢 Session ghi user_agent.' => ['G6-029', 'G6-029 🟢 Session ghi user_agent.'];
        yield 'G6-030 🔴 User A không dùng token của user B để truy cập tài nguyên riêng.' => ['G6-030', 'G6-030 🔴 User A không dùng token của user B để truy cập tài nguyên riêng.'];
        yield 'G6-031 🔴 Learner không vào route instructor.' => ['G6-031', 'G6-031 🔴 Learner không vào route instructor.'];
        yield 'G6-032 🔴 Instructor không vào route admin.' => ['G6-032', 'G6-032 🔴 Instructor không vào route admin.'];
        yield 'G6-033 🟢 Admin vào route admin hợp lệ.' => ['G6-033', 'G6-033 🟢 Admin vào route admin hợp lệ.'];
        yield 'G6-034 🔴 Request không token vào route auth required trả 401.' => ['G6-034', 'G6-034 🔴 Request không token vào route auth required trả 401.'];
        yield 'G6-035 🔴 Token malformed trả 401.' => ['G6-035', 'G6-035 🔴 Token malformed trả 401.'];
        yield 'G6-036 🔴 Token hết hạn trả 401.' => ['G6-036', 'G6-036 🔴 Token hết hạn trả 401.'];
        yield 'G6-037 🟢 `/auth/me` trả đúng user hiện tại.' => ['G6-037', 'G6-037 🟢 `/auth/me` trả đúng user hiện tại.'];
        yield 'G6-038 🔴 `/auth/me` không leak password_hash.' => ['G6-038', 'G6-038 🔴 `/auth/me` không leak password_hash.'];
        yield 'G6-039 🟢 Forgot password tạo OTP đúng purpose.' => ['G6-039', 'G6-039 🟢 Forgot password tạo OTP đúng purpose.'];
        yield 'G6-040 🟢 OTP được lưu dưới `code_hash`.' => ['G6-040', 'G6-040 🟢 OTP được lưu dưới `code_hash`.'];
        yield 'G6-041 🔴 OTP plain code không được lưu DB.' => ['G6-041', 'G6-041 🔴 OTP plain code không được lưu DB.'];
        yield 'G6-042 🟢 Verify OTP đúng code + đúng purpose thành công.' => ['G6-042', 'G6-042 🟢 Verify OTP đúng code + đúng purpose thành công.'];
        yield 'G6-043 🔴 OTP đúng code nhưng sai purpose bị từ chối.' => ['G6-043', 'G6-043 🔴 OTP đúng code nhưng sai purpose bị từ chối.'];
        yield 'G6-044 🔴 OTP sai code bị từ chối.' => ['G6-044', 'G6-044 🔴 OTP sai code bị từ chối.'];
        yield 'G6-045 🔴 OTP hết hạn bị từ chối.' => ['G6-045', 'G6-045 🔴 OTP hết hạn bị từ chối.'];
        yield 'G6-046 🔴 OTP đã used bị từ chối.' => ['G6-046', 'G6-046 🔴 OTP đã used bị từ chối.'];
        yield 'G6-047 🔴 OTP của user A không dùng cho user B.' => ['G6-047', 'G6-047 🔴 OTP của user A không dùng cho user B.'];
        yield 'G6-048 🟢 attempts tăng khi nhập OTP sai.' => ['G6-048', 'G6-048 🟢 attempts tăng khi nhập OTP sai.'];
        yield 'G6-049 🔴 Quá số attempts bị khóa OTP.' => ['G6-049', 'G6-049 🔴 Quá số attempts bị khóa OTP.'];
        yield 'G6-050 🟢 Resend OTP làm OTP cũ mất hiệu lực theo rule.' => ['G6-050', 'G6-050 🟢 Resend OTP làm OTP cũ mất hiệu lực theo rule.'];
        yield 'G6-051 🟢 Reset password sau OTP hợp lệ đổi password_hash.' => ['G6-051', 'G6-051 🟢 Reset password sau OTP hợp lệ đổi password_hash.'];
        yield 'G6-052 🔴 Reset password không OTP bị từ chối.' => ['G6-052', 'G6-052 🔴 Reset password không OTP bị từ chối.'];
        yield 'G6-053 🔴 Reset password với OTP đã dùng bị từ chối.' => ['G6-053', 'G6-053 🔴 Reset password với OTP đã dùng bị từ chối.'];
        yield 'G6-054 🟢 Đổi payout account yêu cầu purpose OTP riêng.' => ['G6-054', 'G6-054 🟢 Đổi payout account yêu cầu purpose OTP riêng.'];
        yield 'G6-055 🔴 OTP password_reset không dùng để đổi payout account.' => ['G6-055', 'G6-055 🔴 OTP password_reset không dùng để đổi payout account.'];
        yield 'G6-056 🟢 Email verification link đúng hash thành công.' => ['G6-056', 'G6-056 🟢 Email verification link đúng hash thành công.'];
        yield 'G6-057 🔴 Email verification hash sai bị từ chối.' => ['G6-057', 'G6-057 🔴 Email verification hash sai bị từ chối.'];
        yield 'G6-058 🟢 Resend verify email chỉ áp dụng user chưa verify.' => ['G6-058', 'G6-058 🟢 Resend verify email chỉ áp dụng user chưa verify.'];
        yield 'G6-059 🔴 User đã verify không spam resend theo rule.' => ['G6-059', 'G6-059 🔴 User đã verify không spam resend theo rule.'];
        yield 'G6-060 🔴 IDOR profile: user A không sửa profile B.' => ['G6-060', 'G6-060 🔴 IDOR profile: user A không sửa profile B.'];
        yield 'G6-061 🔴 IDOR payout: user A không sửa payout B.' => ['G6-061', 'G6-061 🔴 IDOR payout: user A không sửa payout B.'];
        yield 'G6-062 🔴 Mass assignment không đổi role thành admin.' => ['G6-062', 'G6-062 🔴 Mass assignment không đổi role thành admin.'];
        yield 'G6-063 🔴 SQL injection payload trong email/search không phá query.' => ['G6-063', 'G6-063 🔴 SQL injection payload trong email/search không phá query.'];
        yield 'G6-064 🔴 XSS payload trong profile text được xử lý an toàn ở tầng output/validation phù hợp.' => ['G6-064', 'G6-064 🔴 XSS payload trong profile text được xử lý an toàn ở tầng output/validation phù hợp.'];
        yield 'G6-065 🟢 Xóa user cascade session và OTP đúng schema, không để token sống sót.' => ['G6-065', 'G6-065 🟢 Xóa user cascade session và OTP đúng schema, không để token sống sót.'];
        yield 'G6-066 🔴 OTP resend liên tục không được tạo vô hạn OTP còn hiệu lực đồng thời cho cùng user + purpose.' => ['G6-066', 'G6-066 🔴 OTP resend liên tục không được tạo vô hạn OTP còn hiệu lực đồng thời cho cùng user + purpose.'];
        yield 'G6-067 🟢 OTP mới thay thế/khóa hiệu lực OTP cũ theo đúng cơ chế hiện tại.' => ['G6-067', 'G6-067 🟢 OTP mới thay thế/khóa hiệu lực OTP cũ theo đúng cơ chế hiện tại.'];
    }
}