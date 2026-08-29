<?php

namespace Tests\Feature\Final\Group1;

use PHPUnit\Framework\Attributes\DataProvider;
use Tests\Feature\Final\Support\FinalFeatureTestCase;
use Tests\Feature\Final\Support\SourceAwareExecutor;

final class DatabaseFinalTest extends FinalFeatureTestCase
{
    #[DataProvider('cases')]
    public function test_nghiep_vu_final(string $id, string $nhan): void
    {
        SourceAwareExecutor::run($this, $id, $nhan);
    }

    public static function cases(): iterable
    {
        yield 'G1-001 🟢 Migration fresh tạo đủ toàn bộ bảng FINAL mà không lỗi.' => ['G1-001', 'G1-001 🟢 Migration fresh tạo đủ toàn bộ bảng FINAL mà không lỗi.'];
        yield 'G1-002 🟢 `users` tồn tại `password_hash` và không còn `password`.' => ['G1-002', 'G1-002 🟢 `users` tồn tại `password_hash` và không còn `password`.'];
        yield 'G1-003 🟢 Các bảng nghiệp vụ chính không còn `deleted_at`.' => ['G1-003', 'G1-003 🟢 Các bảng nghiệp vụ chính không còn `deleted_at`.'];
        yield 'G1-004 🟢 `courses` dùng `course_level`, không còn `level` legacy.' => ['G1-004', 'G1-004 🟢 `courses` dùng `course_level`, không còn `level` legacy.'];
        yield 'G1-005 🟢 `courses` không còn `discount_percent`.' => ['G1-005', 'G1-005 🟢 `courses` không còn `discount_percent`.'];
        yield 'G1-006 🟢 `revenues` không có cột `status` legacy.' => ['G1-006', 'G1-006 🟢 `revenues` không có cột `status` legacy.'];
        yield 'G1-007 🟢 `wishlist` dùng composite primary key `(user_id, course_id)`.' => ['G1-007', 'G1-007 🟢 `wishlist` dùng composite primary key `(user_id, course_id)`.'];
        yield 'G1-008 🟢 `orders` có `price_snapshot`.' => ['G1-008', 'G1-008 🟢 `orders` có `price_snapshot`.'];
        yield 'G1-009 🟢 `orders` có `discount_amount`.' => ['G1-009', 'G1-009 🟢 `orders` có `discount_amount`.'];
        yield 'G1-010 🟢 `orders` có `commission_rule_id`.' => ['G1-010', 'G1-010 🟢 `orders` có `commission_rule_id`.'];
        yield 'G1-011 🟢 `orders.order_code` bắt buộc và unique.' => ['G1-011', 'G1-011 🟢 `orders.order_code` bắt buộc và unique.'];
        yield 'G1-012 🟢 `revenues.order_id` unique: một order chỉ sinh tối đa một revenue.' => ['G1-012', 'G1-012 🟢 `revenues.order_id` unique: một order chỉ sinh tối đa một revenue.'];
        yield 'G1-013 🟢 `revenues` có `commission_rule_id` để truy vết rule đã áp dụng.' => ['G1-013', 'G1-013 🟢 `revenues` có `commission_rule_id` để truy vết rule đã áp dụng.'];
        yield 'G1-014 🟢 `sessions` có `refresh_token_hash`.' => ['G1-014', 'G1-014 🟢 `sessions` có `refresh_token_hash`.'];
        yield 'G1-015 🟢 `user_otps` có `code_hash`, không lưu OTP dạng plain text.' => ['G1-015', 'G1-015 🟢 `user_otps` có `code_hash`, không lưu OTP dạng plain text.'];
        yield 'G1-016 🟢 `withdrawal_revenues` có composite key đúng.' => ['G1-016', 'G1-016 🟢 `withdrawal_revenues` có composite key đúng.'];
        yield 'G1-017 🟢 `payout_accounts.user_id` là FK hợp lệ.' => ['G1-017', 'G1-017 🟢 `payout_accounts.user_id` là FK hợp lệ.'];
        yield 'G1-018 🟢 `orders.user_id` là FK hợp lệ.' => ['G1-018', 'G1-018 🟢 `orders.user_id` là FK hợp lệ.'];
        yield 'G1-019 🟢 `orders.course_id` là FK hợp lệ.' => ['G1-019', 'G1-019 🟢 `orders.course_id` là FK hợp lệ.'];
        yield 'G1-020 🟢 `orders.commission_rule_id` là FK hợp lệ.' => ['G1-020', 'G1-020 🟢 `orders.commission_rule_id` là FK hợp lệ.'];
        yield 'G1-021 🔴 Không cho insert order với `user_id` không tồn tại.' => ['G1-021', 'G1-021 🔴 Không cho insert order với `user_id` không tồn tại.'];
        yield 'G1-022 🔴 Không cho insert order với `course_id` không tồn tại.' => ['G1-022', 'G1-022 🔴 Không cho insert order với `course_id` không tồn tại.'];
        yield 'G1-023 🔴 Không cho insert revenue với `order_id` không tồn tại.' => ['G1-023', 'G1-023 🔴 Không cho insert revenue với `order_id` không tồn tại.'];
        yield 'G1-024 🔴 Không cho insert payout account với `user_id` không tồn tại.' => ['G1-024', 'G1-024 🔴 Không cho insert payout account với `user_id` không tồn tại.'];
        yield 'G1-025 🔴 Không cho insert enrollment với `order_id` không tồn tại.' => ['G1-025', 'G1-025 🔴 Không cho insert enrollment với `order_id` không tồn tại.'];
        yield 'G1-026 🟢 Xóa user cascade đúng những bảng được phép cascade.' => ['G1-026', 'G1-026 🟢 Xóa user cascade đúng những bảng được phép cascade.'];
        yield 'G1-027 🟢 Xóa course cascade đúng pivot/quan hệ được phép.' => ['G1-027', 'G1-027 🟢 Xóa course cascade đúng pivot/quan hệ được phép.'];
        yield 'G1-028 🟢 Xóa category cascade đúng pivot `course_categories`.' => ['G1-028', 'G1-028 🟢 Xóa category cascade đúng pivot `course_categories`.'];
        yield 'G1-029 🔴 Foreign key tài chính không được cascade làm mất lịch sử revenue/withdrawal ngoài thiết kế FINAL.' => ['G1-029', 'G1-029 🔴 Foreign key tài chính không được cascade làm mất lịch sử revenue/withdrawal ngoài thiết kế FINAL.'];
        yield 'G1-030 🟢 Unique email trên `users` hoạt động.' => ['G1-030', 'G1-030 🟢 Unique email trên `users` hoạt động.'];
        yield 'G1-031 🟢 Unique phone trên `users` hoạt động khi phone khác NULL.' => ['G1-031', 'G1-031 🟢 Unique phone trên `users` hoạt động khi phone khác NULL.'];
        yield 'G1-032 🟢 Nhiều user có phone NULL vẫn tạo được.' => ['G1-032', 'G1-032 🟢 Nhiều user có phone NULL vẫn tạo được.'];
        yield 'G1-033 🟢 Unique slug trên `courses` hoạt động.' => ['G1-033', 'G1-033 🟢 Unique slug trên `courses` hoạt động.'];
        yield 'G1-034 🟢 Unique slug trên `categories` hoạt động.' => ['G1-034', 'G1-034 🟢 Unique slug trên `categories` hoạt động.'];
        yield 'G1-035 🟢 Unique `refresh_token_hash` trên sessions hoạt động.' => ['G1-035', 'G1-035 🟢 Unique `refresh_token_hash` trên sessions hoạt động.'];
        yield 'G1-036 🟢 Unique `provider_payout_id` trên withdrawal hoạt động nếu có giá trị.' => ['G1-036', 'G1-036 🟢 Unique `provider_payout_id` trên withdrawal hoạt động nếu có giá trị.'];
        yield 'G1-037 🟢 ENUM/status của `courses` đúng tập giá trị FINAL.' => ['G1-037', 'G1-037 🟢 ENUM/status của `courses` đúng tập giá trị FINAL.'];
        yield 'G1-038 🟢 ENUM/status của `orders` đúng tập giá trị FINAL.' => ['G1-038', 'G1-038 🟢 ENUM/status của `orders` đúng tập giá trị FINAL.'];
        yield 'G1-039 🟢 ENUM/payment_status của `orders` đúng tập giá trị FINAL.' => ['G1-039', 'G1-039 🟢 ENUM/payment_status của `orders` đúng tập giá trị FINAL.'];
        yield 'G1-040 🟢 ENUM/status của `payout_accounts` đúng tập giá trị FINAL.' => ['G1-040', 'G1-040 🟢 ENUM/status của `payout_accounts` đúng tập giá trị FINAL.'];
        yield 'G1-041 🟢 ENUM/status của `withdraw_requests` đúng tập giá trị FINAL.' => ['G1-041', 'G1-041 🟢 ENUM/status của `withdraw_requests` đúng tập giá trị FINAL.'];
        yield 'G1-042 🟢 ENUM/role của `users` đúng learner/instructor/admin.' => ['G1-042', 'G1-042 🟢 ENUM/role của `users` đúng learner/instructor/admin.'];
        yield 'G1-043 🟢 ENUM/status của `users` đúng active/inactive/suspended.' => ['G1-043', 'G1-043 🟢 ENUM/status của `users` đúng active/inactive/suspended.'];
        yield 'G1-044 🔴 Giá trị enum ngoài FINAL bị DB từ chối.' => ['G1-044', 'G1-044 🔴 Giá trị enum ngoài FINAL bị DB từ chối.'];
        yield 'G1-045 🟢 Decimal tiền lưu chính xác số nguyên VND cần dùng.' => ['G1-045', 'G1-045 🟢 Decimal tiền lưu chính xác số nguyên VND cần dùng.'];
        yield 'G1-046 🟢 `price_snapshot`, `discount_amount`, `amount` không bị trôi số.' => ['G1-046', 'G1-046 🟢 `price_snapshot`, `discount_amount`, `amount` không bị trôi số.'];
        yield 'G1-047 🟢 Rate commission lưu đúng precision.' => ['G1-047', 'G1-047 🟢 Rate commission lưu đúng precision.'];
        yield 'G1-048 🟢 Timestamp nullable hoạt động đúng ở các trạng thái chưa xử lý.' => ['G1-048', 'G1-048 🟢 Timestamp nullable hoạt động đúng ở các trạng thái chưa xử lý.'];
        yield 'G1-049 🟢 Timestamp được ghi khi trạng thái chuyển đúng thời điểm.' => ['G1-049', 'G1-049 🟢 Timestamp được ghi khi trạng thái chuyển đúng thời điểm.'];
        yield 'G1-050 🟢 JSON fields như requirements/outcomes lưu và đọc được mảng rỗng.' => ['G1-050', 'G1-050 🟢 JSON fields như requirements/outcomes lưu và đọc được mảng rỗng.'];
        yield 'G1-051 🟢 Course cho phép `sale_price = NULL` khi không có campaign.' => ['G1-051', 'G1-051 🟢 Course cho phép `sale_price = NULL` khi không có campaign.'];
        yield 'G1-052 🟢 Order cho phép coupon_id NULL khi mua không giảm giá.' => ['G1-052', 'G1-052 🟢 Order cho phép coupon_id NULL khi mua không giảm giá.'];
        yield 'G1-053 🟢 Order trial cho phép amount = 0 đúng trường hợp trial.' => ['G1-053', 'G1-053 🟢 Order trial cho phép amount = 0 đúng trường hợp trial.'];
        yield 'G1-054 🔴 Order thường amount = 0 bị business layer chặn.' => ['G1-054', 'G1-054 🔴 Order thường amount = 0 bị business layer chặn.'];
        yield 'G1-055 🟢 Migration có thể chạy lại từ database rỗng.' => ['G1-055', 'G1-055 🟢 Migration có thể chạy lại từ database rỗng.'];
        yield 'G1-056 🟢 `migrate:fresh` trên `test11111` PASS.' => ['G1-056', 'G1-056 🟢 `migrate:fresh` trên `test11111` PASS.'];
        yield 'G1-057 🟢 `artisan about` boot app PASS.' => ['G1-057', 'G1-057 🟢 `artisan about` boot app PASS.'];
        yield 'G1-058 🟢 `route:list` compile toàn bộ route PASS.' => ['G1-058', 'G1-058 🟢 `route:list` compile toàn bộ route PASS.'];
        yield 'G1-059 🟢 `git diff --check` không có whitespace error.' => ['G1-059', 'G1-059 🟢 `git diff --check` không có whitespace error.'];
        yield 'G1-060 🟢 Model fillable/casts không nhắc field legacy đã bỏ.' => ['G1-060', 'G1-060 🟢 Model fillable/casts không nhắc field legacy đã bỏ.'];
        yield 'G1-061 🔴 Repository không query `deleted_at` ở schema FINAL.' => ['G1-061', 'G1-061 🔴 Repository không query `deleted_at` ở schema FINAL.'];
        yield 'G1-062 🔴 Service không query `courses.discount_percent`.' => ['G1-062', 'G1-062 🔴 Service không query `courses.discount_percent`.'];
        yield 'G1-063 🔴 Auth code không query `users.password`.' => ['G1-063', 'G1-063 🔴 Auth code không query `users.password`.'];
        yield 'G1-064 🟢 Fixture test tự sinh dữ liệu unique, không phụ thuộc dữ liệu có sẵn.' => ['G1-064', 'G1-064 🟢 Fixture test tự sinh dữ liệu unique, không phụ thuộc dữ liệu có sẵn.'];
        yield 'G1-065 🟢 Mỗi test rollback dữ liệu sau khi chạy.' => ['G1-065', 'G1-065 🟢 Mỗi test rollback dữ liệu sau khi chạy.'];
        yield 'G1-066 🟢 Chạy cùng bộ test hai lần liên tiếp vẫn PASS.' => ['G1-066', 'G1-066 🟢 Chạy cùng bộ test hai lần liên tiếp vẫn PASS.'];
        yield 'G1-067 🟢 Chạy riêng từng group không phụ thuộc group khác.' => ['G1-067', 'G1-067 🟢 Chạy riêng từng group không phụ thuộc group khác.'];
        yield 'G1-068 🟢 Tất cả foreign key/index/unique quan trọng vẫn tồn tại sau `migrate:fresh` và đúng tên/ý nghĩa FINAL.' => ['G1-068', 'G1-068 🟢 Tất cả foreign key/index/unique quan trọng vẫn tồn tại sau `migrate:fresh` và đúng tên/ý nghĩa FINAL.'];
        yield 'G1-069 🟢 Charset/collation của schema lưu và đọc đúng tiếng Việt có dấu.' => ['G1-069', 'G1-069 🟢 Charset/collation của schema lưu và đọc đúng tiếng Việt có dấu.'];
        yield 'G1-070 🟢 Các index/unique quan trọng dùng cho khóa ngoại, lookup order_code, email, slug và provider id tồn tại sau `migrate:fresh`.' => ['G1-070', 'G1-070 🟢 Các index/unique quan trọng dùng cho khóa ngoại, lookup order_code, email, slug và provider id tồn tại sau `migrate:fresh`.'];
    }
}