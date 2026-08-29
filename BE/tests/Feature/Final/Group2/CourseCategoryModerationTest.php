<?php

namespace Tests\Feature\Final\Group2;

use PHPUnit\Framework\Attributes\DataProvider;
use Tests\Feature\Final\Support\FinalFeatureTestCase;
use Tests\Feature\Final\Support\SourceAwareExecutor;

final class CourseCategoryModerationTest extends FinalFeatureTestCase
{
    #[DataProvider('cases')]
    public function test_nghiep_vu_final(string $id, string $nhan): void
    {
        SourceAwareExecutor::run($this, $id, $nhan);
    }

    public static function cases(): iterable
    {
        yield 'G2-001 🟢 Instructor tạo khóa học draft hợp lệ.' => ['G2-001', 'G2-001 🟢 Instructor tạo khóa học draft hợp lệ.'];
        yield 'G2-002 🔴 Learner không được tạo khóa học instructor.' => ['G2-002', 'G2-002 🔴 Learner không được tạo khóa học instructor.'];
        yield 'G2-003 🔴 Instructor không được sửa khóa học của instructor khác.' => ['G2-003', 'G2-003 🔴 Instructor không được sửa khóa học của instructor khác.'];
        yield 'G2-004 🟢 Course draft lưu đúng title, slug, price, course_level.' => ['G2-004', 'G2-004 🟢 Course draft lưu đúng title, slug, price, course_level.'];
        yield 'G2-005 🔴 Trùng slug khóa học bị từ chối.' => ['G2-005', 'G2-005 🔴 Trùng slug khóa học bị từ chối.'];
        yield 'G2-006 🟢 Course lưu giá lớn đúng precision.' => ['G2-006', 'G2-006 🟢 Course lưu giá lớn đúng precision.'];
        yield 'G2-007 🟢 `course_level=beginner` hợp lệ.' => ['G2-007', 'G2-007 🟢 `course_level=beginner` hợp lệ.'];
        yield 'G2-008 🟢 `course_level=intermediate` hợp lệ.' => ['G2-008', 'G2-008 🟢 `course_level=intermediate` hợp lệ.'];
        yield 'G2-009 🟢 `course_level=advanced` hợp lệ.' => ['G2-009', 'G2-009 🟢 `course_level=advanced` hợp lệ.'];
        yield 'G2-010 🟢 `course_level=all_levels` hợp lệ.' => ['G2-010', 'G2-010 🟢 `course_level=all_levels` hợp lệ.'];
        yield 'G2-011 🔴 `course_level` ngoài enum bị từ chối.' => ['G2-011', 'G2-011 🔴 `course_level` ngoài enum bị từ chối.'];
        yield 'G2-012 🟢 Course trạng thái `draft` hợp lệ.' => ['G2-012', 'G2-012 🟢 Course trạng thái `draft` hợp lệ.'];
        yield 'G2-013 🟢 Chuyển draft → pending_review hợp lệ.' => ['G2-013', 'G2-013 🟢 Chuyển draft → pending_review hợp lệ.'];
        yield 'G2-014 🟢 Admin duyệt pending_review → approved.' => ['G2-014', 'G2-014 🟢 Admin duyệt pending_review → approved.'];
        yield 'G2-015 🟢 Approved → published khi đủ điều kiện xuất bản.' => ['G2-015', 'G2-015 🟢 Approved → published khi đủ điều kiện xuất bản.'];
        yield 'G2-016 🟢 Admin reject pending_review → rejected và có lý do.' => ['G2-016', 'G2-016 🟢 Admin reject pending_review → rejected và có lý do.'];
        yield 'G2-017 🔴 Instructor không tự chuyển course sang approved.' => ['G2-017', 'G2-017 🔴 Instructor không tự chuyển course sang approved.'];
        yield 'G2-018 🔴 Instructor không tự publish course đang pending_review.' => ['G2-018', 'G2-018 🔴 Instructor không tự publish course đang pending_review.'];
        yield 'G2-019 🟢 Published → hidden khi cần ẩn khóa học.' => ['G2-019', 'G2-019 🟢 Published → hidden khi cần ẩn khóa học.'];
        yield 'G2-020 🟢 Hidden → published lại khi điều kiện cho phép.' => ['G2-020', 'G2-020 🟢 Hidden → published lại khi điều kiện cho phép.'];
        yield 'G2-021 🔴 Rejected không tự động thành published.' => ['G2-021', 'G2-021 🔴 Rejected không tự động thành published.'];
        yield 'G2-022 🟢 `published_at` chỉ có khi published.' => ['G2-022', 'G2-022 🟢 `published_at` chỉ có khi published.'];
        yield 'G2-023 🟢 `reviewed_by` ghi admin đã duyệt/reject.' => ['G2-023', 'G2-023 🟢 `reviewed_by` ghi admin đã duyệt/reject.'];
        yield 'G2-024 🟢 `admin_reject_reason` chỉ có khi rejected.' => ['G2-024', 'G2-024 🟢 `admin_reject_reason` chỉ có khi rejected.'];
        yield 'G2-025 🟢 Instructor sửa draft bình thường.' => ['G2-025', 'G2-025 🟢 Instructor sửa draft bình thường.'];
        yield 'G2-026 🟢 Instructor sửa rejected để gửi duyệt lại.' => ['G2-026', 'G2-026 🟢 Instructor sửa rejected để gửi duyệt lại.'];
        yield 'G2-027 🔴 Instructor sửa các field quản trị trên course bị chặn.' => ['G2-027', 'G2-027 🔴 Instructor sửa các field quản trị trên course bị chặn.'];
        yield 'G2-028 🔴 Client không mass assign `reviewed_by`.' => ['G2-028', 'G2-028 🔴 Client không mass assign `reviewed_by`.'];
        yield 'G2-029 🔴 Client không mass assign `status=approved`.' => ['G2-029', 'G2-029 🔴 Client không mass assign `status=approved`.'];
        yield 'G2-030 🟢 Category root tạo thành công.' => ['G2-030', 'G2-030 🟢 Category root tạo thành công.'];
        yield 'G2-031 🟢 Category con cấp 2 tạo thành công.' => ['G2-031', 'G2-031 🟢 Category con cấp 2 tạo thành công.'];
        yield 'G2-032 🔴 Category cấp 3 bị từ chối; cấu trúc FINAL chỉ cho tối đa 2 cấp.' => ['G2-032', 'G2-032 🔴 Category cấp 3 bị từ chối; cấu trúc FINAL chỉ cho tối đa 2 cấp.'];
        yield 'G2-033 🔴 Category parent không tồn tại bị từ chối.' => ['G2-033', 'G2-033 🔴 Category parent không tồn tại bị từ chối.'];
        yield 'G2-034 🔴 Category tự làm parent của chính nó bị từ chối.' => ['G2-034', 'G2-034 🔴 Category tự làm parent của chính nó bị từ chối.'];
        yield 'G2-035 🔴 Hai category trùng slug bị từ chối.' => ['G2-035', 'G2-035 🔴 Hai category trùng slug bị từ chối.'];
        yield 'G2-036 🟢 Category active hiển thị ở catalog.' => ['G2-036', 'G2-036 🟢 Category active hiển thị ở catalog.'];
        yield 'G2-037 🟢 Category inactive không được dùng như category public.' => ['G2-037', 'G2-037 🟢 Category inactive không được dùng như category public.'];
        yield 'G2-038 🟢 Một course gắn nhiều category.' => ['G2-038', 'G2-038 🟢 Một course gắn nhiều category.'];
        yield 'G2-039 🟢 Một category gắn nhiều course.' => ['G2-039', 'G2-039 🟢 Một category gắn nhiều course.'];
        yield 'G2-040 🔴 Cặp course-category trùng bị từ chối.' => ['G2-040', 'G2-040 🔴 Cặp course-category trùng bị từ chối.'];
        yield 'G2-041 🟢 Xóa category gỡ pivot nhưng không xóa course.' => ['G2-041', 'G2-041 🟢 Xóa category gỡ pivot nhưng không xóa course.'];
        yield 'G2-042 🟢 Xóa course gỡ pivot nhưng không xóa category.' => ['G2-042', 'G2-042 🟢 Xóa course gỡ pivot nhưng không xóa category.'];
        yield 'G2-043 🟢 Instructor chỉ thấy course của chính mình trong dashboard.' => ['G2-043', 'G2-043 🟢 Instructor chỉ thấy course của chính mình trong dashboard.'];
        yield 'G2-044 🟢 Admin thấy được course của mọi instructor.' => ['G2-044', 'G2-044 🟢 Admin thấy được course của mọi instructor.'];
        yield 'G2-045 🔴 Learner không gọi API quản trị course.' => ['G2-045', 'G2-045 🔴 Learner không gọi API quản trị course.'];
        yield 'G2-046 🟢 Public chỉ thấy course published.' => ['G2-046', 'G2-046 🟢 Public chỉ thấy course published.'];
        yield 'G2-047 🔴 Public không thấy course draft.' => ['G2-047', 'G2-047 🔴 Public không thấy course draft.'];
        yield 'G2-048 🔴 Public không thấy course pending_review.' => ['G2-048', 'G2-048 🔴 Public không thấy course pending_review.'];
        yield 'G2-049 🔴 Public không thấy course rejected.' => ['G2-049', 'G2-049 🔴 Public không thấy course rejected.'];
        yield 'G2-050 🔴 Public không thấy course hidden.' => ['G2-050', 'G2-050 🔴 Public không thấy course hidden.'];
        yield 'G2-051 🟢 Thumbnail URL nullable.' => ['G2-051', 'G2-051 🟢 Thumbnail URL nullable.'];
        yield 'G2-052 🟢 Thumbnail public id nullable.' => ['G2-052', 'G2-052 🟢 Thumbnail public id nullable.'];
        yield 'G2-053 🟢 Intro video URL nullable.' => ['G2-053', 'G2-053 🟢 Intro video URL nullable.'];
        yield 'G2-054 🟢 Intro video id nullable.' => ['G2-054', 'G2-054 🟢 Intro video id nullable.'];
        yield 'G2-055 🟢 Course giữ Bunny/video fields hiện tại sau refactor pricing.' => ['G2-055', 'G2-055 🟢 Course giữ Bunny/video fields hiện tại sau refactor pricing.'];
        yield 'G2-056 🔴 Không làm mất quan hệ section/lesson khi sửa metadata course.' => ['G2-056', 'G2-056 🔴 Không làm mất quan hệ section/lesson khi sửa metadata course.'];
        yield 'G2-057 🟢 Reorder category cập nhật sort_order đúng.' => ['G2-057', 'G2-057 🟢 Reorder category cập nhật sort_order đúng.'];
        yield 'G2-058 🔴 Reorder gửi ID category không tồn tại bị từ chối.' => ['G2-058', 'G2-058 🔴 Reorder gửi ID category không tồn tại bị từ chối.'];
        yield 'G2-059 🟢 API index course filter theo status đúng.' => ['G2-059', 'G2-059 🟢 API index course filter theo status đúng.'];
        yield 'G2-060 🟢 API index course filter theo category đúng.' => ['G2-060', 'G2-060 🟢 API index course filter theo category đúng.'];
        yield 'G2-061 🟢 API index course filter theo level đúng.' => ['G2-061', 'G2-061 🟢 API index course filter theo level đúng.'];
        yield 'G2-062 🟢 API index course sort theo giá đúng.' => ['G2-062', 'G2-062 🟢 API index course sort theo giá đúng.'];
        yield 'G2-063 🟢 API detail course trả đúng instructor.' => ['G2-063', 'G2-063 🟢 API detail course trả đúng instructor.'];
        yield 'G2-064 🟢 API detail course trả đúng category.' => ['G2-064', 'G2-064 🟢 API detail course trả đúng category.'];
        yield 'G2-065 🟢 Course không có coupon thì sale_price NULL hoặc bằng logic cache FINAL.' => ['G2-065', 'G2-065 🟢 Course không có coupon thì sale_price NULL hoặc bằng logic cache FINAL.'];
        yield 'G2-066 🟢 Course có active campaign thì sale_price phản ánh campaign đúng.' => ['G2-066', 'G2-066 🟢 Course có active campaign thì sale_price phản ánh campaign đúng.'];
        yield 'G2-067 🔴 Sửa giá course làm campaign hiện tại vi phạm rule phải bị từ chối hoặc đồng bộ đúng nghiệp vụ.' => ['G2-067', 'G2-067 🔴 Sửa giá course làm campaign hiện tại vi phạm rule phải bị từ chối hoặc đồng bộ đúng nghiệp vụ.'];
        yield 'G2-068 🟢 Concurrent update course không làm mất trạng thái moderation.' => ['G2-068', 'G2-068 🟢 Concurrent update course không làm mất trạng thái moderation.'];
    }
}