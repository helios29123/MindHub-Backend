# Tài liệu DB MindHub từ file SQL

- File nguồn: `mindhub(1).sql`
- Thời gian tạo tài liệu: 2026-07-20 12:25:34
- Tổng số bảng phát hiện: **33**

> Tài liệu này được chuyển từ file SQL để Anti/code AI đọc nhanh khi làm BE/FE. Nội dung tập trung vào schema, quan hệ bảng và các bảng quan trọng cho vai trò giảng viên.

## 1. Tổng quan bảng theo nhóm

### Người dùng & phân quyền

| Bảng | Số cột | Số dòng seed/import ước tính |
|---|---:|---:|
| `instructor_profiles` | 8 | 0 |
| `sessions` | 9 | 0 |
| `user_category_interests` | 6 | 0 |
| `users` | 16 | 0 |

### Khóa học & nội dung

| Bảng | Số cột | Số dòng seed/import ước tính |
|---|---:|---:|
| `ai_lesson_summaries` | 13 | 0 |
| `course_categories` | 3 | 0 |
| `course_faqs` | 5 | 0 |
| `course_reviews` | 7 | 0 |
| `course_sections` | 9 | 0 |
| `courses` | 22 | 0 |
| `lesson_assets` | 10 | 0 |
| `lesson_notes` | 9 | 0 |
| `lesson_progress` | 10 | 0 |
| `lessons` | 15 | 0 |

### Học tập

| Bảng | Số cột | Số dòng seed/import ước tính |
|---|---:|---:|
| `enrollments` | 11 | 0 |
| `video_progress` | 6 | 0 |

### Đơn hàng & thanh toán

| Bảng | Số cột | Số dòng seed/import ước tính |
|---|---:|---:|
| `coupons` | 17 | 0 |
| `orders` | 14 | 0 |
| `payout_accounts` | 10 | 0 |
| `revenues` | 10 | 0 |
| `withdraw_requests` | 14 | 0 |

### Thông báo & tương tác

| Bảng | Số cột | Số dòng seed/import ước tính |
|---|---:|---:|
| `comments` | 9 | 0 |
| `notifications` | 16 | 0 |
| `quiz_attempt_answers` | 7 | 0 |
| `quiz_questions` | 8 | 0 |

### Khác

| Bảng | Số cột | Số dòng seed/import ước tính |
|---|---:|---:|
| `banners` | 12 | 0 |
| `categories` | 10 | 0 |
| `certificates` | 11 | 0 |
| `faqs` | 9 | 0 |
| `quiz_attempts` | 11 | 0 |
| `quiz_options` | 6 | 0 |
| `quizzes` | 10 | 0 |
| `wishlist` | 4 | 0 |

## 2. Các bảng trọng tâm cho vai trò giảng viên

| Bảng | Vai trò trong giao diện/API giảng viên |
|---|---|
| `users` | Thông tin tài khoản học viên/giảng viên/admin. |
| `instructor_profiles` | Hồ sơ chuyên môn, giới thiệu, kinh nghiệm của giảng viên. |
| `courses` | Khóa học do giảng viên tạo/quản lý. |
| `course_sections` | Chương/phần của khóa học. |
| `lessons` | Bài học/video/nội dung trong từng chương. |
| `lesson_assets` | Tài nguyên đính kèm bài học. |
| `categories` | Danh mục khóa học. |
| `course_categories` | Liên kết nhiều-nhiều giữa khóa học và danh mục. |
| `enrollments` | Ghi danh của học viên vào khóa học. |
| `lesson_progress` | Tiến độ học từng bài. |
| `video_progress` | Tiến độ xem video nếu có. |
| `comments` | Hỏi đáp/bình luận giữa học viên và giảng viên. |
| `coupons` | Mã giảm giá do giảng viên tạo. |
| `orders` | Đơn hàng/thanh toán của học viên. |
| `revenues` | Doanh thu gộp, phần giảng viên, phí hệ thống. |
| `payout_accounts` | Tài khoản nhận tiền của giảng viên. |
| `withdraw_requests` | Yêu cầu rút tiền của giảng viên. |
| `notifications` | Thông báo hệ thống cho giảng viên. |

## 3. Quan hệ chính nên nhớ

| Bảng con | Cột | Tham chiếu bảng | Cột tham chiếu | Rule |
|---|---|---|---|---|
| `ai_lesson_summaries` | `course_id` | `courses` | `id` | `ON DELETE CASCADE ON UPDATE CASCADE` |
| `ai_lesson_summaries` | `lesson_id` | `lessons` | `id` | `ON DELETE CASCADE ON UPDATE CASCADE` |
| `categories` | `parent_id` | `categories` | `id` | `ON DELETE SET NULL ON UPDATE CASCADE` |
| `certificates` | `course_id` | `courses` | `id` | `ON UPDATE CASCADE` |
| `certificates` | `user_id` | `users` | `id` | `ON UPDATE CASCADE` |
| `comments` | `lesson_id` | `lessons` | `id` | `ON UPDATE CASCADE` |
| `comments` | `parent_id` | `comments` | `id` | `ON DELETE SET NULL ON UPDATE CASCADE` |
| `coupons` | `course_id` | `courses` | `id` | `ON DELETE CASCADE ON UPDATE CASCADE` |
| `courses` | `instructor_id` | `users` | `id` | `ON UPDATE CASCADE` |
| `course_categories` | `category_id` | `categories` | `id` | `ON DELETE CASCADE ON UPDATE CASCADE` |
| `course_faqs` | `course_id` | `courses` | `id` | `ON UPDATE CASCADE` |
| `course_reviews` | `order_id` | `orders` | `id` | `ON UPDATE CASCADE` |
| `course_sections` | `course_id` | `courses` | `id` | `ON DELETE CASCADE ON UPDATE CASCADE` |
| `enrollments` | `course_id` | `courses` | `id` | `ON UPDATE CASCADE` |
| `enrollments` | `user_id` | `users` | `id` | `ON UPDATE CASCADE` |
| `instructor_profiles` | `user_id` | `users` | `id` | `ON DELETE CASCADE ON UPDATE CASCADE` |
| `lessons` | `course_id` | `courses` | `id` | `ON DELETE CASCADE ON UPDATE CASCADE` |
| `lesson_assets` | `lesson_id` | `lessons` | `id` | `ON DELETE CASCADE ON UPDATE CASCADE` |
| `lesson_notes` | `course_id` | `courses` | `id` | `ON DELETE CASCADE ON UPDATE CASCADE` |
| `lesson_notes` | `user_id` | `users` | `id` | `ON DELETE CASCADE ON UPDATE CASCADE` |
| `lesson_progress` | `lesson_id` | `lessons` | `id` | `ON DELETE CASCADE ON UPDATE CASCADE` |
| `notifications` | `user_id` | `users` | `id` | `ON DELETE CASCADE ON UPDATE CASCADE` |
| `orders` | `coupon_id` | `coupons` | `id` | `ON DELETE SET NULL ON UPDATE CASCADE` |
| `orders` | `user_id` | `users` | `id` | `ON UPDATE CASCADE` |
| `payout_accounts` | `user_id` | `users` | `id` | `ON UPDATE CASCADE` |
| `quizzes` | `course_id` | `courses` | `id` | `ON DELETE CASCADE ON UPDATE CASCADE` |
| `revenues` | `course_id` | `courses` | `id` | `ON UPDATE CASCADE` |
| `revenues` | `order_id` | `orders` | `id` | `ON UPDATE CASCADE` |
| `sessions` | `user_id` | `users` | `id` | `ON DELETE CASCADE ON UPDATE CASCADE` |
| `user_category_interests` | `category_id` | `categories` | `id` | `ON DELETE CASCADE ON UPDATE CASCADE` |
| `video_progress` | `lesson_id` | `lessons` | `id` | `ON DELETE CASCADE ON UPDATE CASCADE` |
| `wishlist` | `course_id` | `courses` | `id` | `ON DELETE CASCADE ON UPDATE CASCADE` |
| `withdraw_requests` | `payout_account_id` | `payout_accounts` | `id` | `ON UPDATE CASCADE` |

## 4. Chi tiết schema từng bảng

### `ai_lesson_summaries`

- Engine/Options: `InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci`
- Số cột: **13**
- Seed/import rows ước tính: **0**

| Cột | Kiểu / ràng buộc | Ghi chú nhanh |
|---|---|---|
| `id` | `bigint(20) UNSIGNED NOT NULL` | required, unsigned |
| `lesson_id` | `bigint(20) UNSIGNED NOT NULL COMMENT 'Lesson được AI tóm tắt'` | required, unsigned, khóa ngoại tiềm năng |
| `course_id` | `bigint(20) UNSIGNED NOT NULL COMMENT 'Course chứa lesson'` | required, unsigned, khóa ngoại tiềm năng |
| `summary` | `longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL COMMENT 'Nội dung tóm tắt do AI sinh ra'` | required |
| `key_points` | `longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL COMMENT 'Danh sách ý chính dạng JSON nếu có' CHECK (json_valid(`key_points`))` | nullable, default NULL |
| `summary_type` | `varchar(30) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'short' COMMENT 'short/detailed/bullet'` | required, default 'short' |
| `language` | `varchar(10) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'vi' COMMENT 'Ngôn ngữ tóm tắt` | required, default 'vi' |
| `source_content_hash` | `varchar(64) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL COMMENT 'SHA-256 hash của nội dung lesson.content tại thời điểm tạo summary'` | required |
| `model_name` | `varchar(100) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL COMMENT 'Tên model AI đã dùng'` | nullable, default NULL |
| `generated_by_user_id` | `bigint(20) UNSIGNED DEFAULT NULL COMMENT 'User kích hoạt tạo summary; null nếu system tạo'` | nullable, default NULL, unsigned, khóa ngoại tiềm năng |
| `generated_at` | `timestamp NOT NULL DEFAULT current_timestamp() COMMENT 'Thời điểm sinh summary'` | required, default current_timestamp() |
| `created_at` | `timestamp NULL DEFAULT current_timestamp()` | default current_timestamp() |
| `updated_at` | `timestamp NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()` | default current_timestamp() |

- Primary key: `id`
- Unique:
  - `uq_ai_lesson_summaries_cache`: `lesson_id, summary_type, language, source_content_hash`
- Index:
  - `idx_ai_lesson_summaries_lesson_id`: `lesson_id`
  - `idx_ai_lesson_summaries_course_id`: `course_id`
  - `idx_ai_lesson_summaries_lookup`: `lesson_id, summary_type, language`
  - `idx_ai_lesson_summaries_generated_at`: `generated_at`
  - `fk_ai_lesson_summaries_generated_by_user`: `generated_by_user_id`
- Foreign keys:
  - `fk_ai_lesson_summaries_course`: `course_id` -> `courses`(`id`) ON DELETE CASCADE ON UPDATE CASCADE
  - `fk_ai_lesson_summaries_lesson`: `lesson_id` -> `lessons`(`id`) ON DELETE CASCADE ON UPDATE CASCADE
- Ràng buộc khác:
  - `ví dụ vi/en'`

### `banners`

- Engine/Options: `InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci`
- Số cột: **12**
- Seed/import rows ước tính: **0**

| Cột | Kiểu / ràng buộc | Ghi chú nhanh |
|---|---|---|
| `id` | `bigint(20) UNSIGNED NOT NULL` | required, unsigned |
| `title` | `varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL` | required |
| `image_url` | `varchar(500) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL` | required |
| `target_url` | `varchar(500) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL` | nullable, default NULL |
| `position` | `varchar(50) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'home'` | required, default 'home' |
| `sort_order` | `int(10) UNSIGNED NOT NULL DEFAULT 0` | required, default 0, unsigned |
| `start_at` | `timestamp NULL DEFAULT NULL` | nullable, default NULL |
| `end_at` | `timestamp NULL DEFAULT NULL` | nullable, default NULL |
| `status` | `varchar(30) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'inactive' COMMENT 'active=đang hiển thị` | required, default 'inactive' |
| `created_at` | `timestamp NULL DEFAULT current_timestamp()` | default current_timestamp() |
| `updated_at` | `timestamp NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()` | default current_timestamp() |
| `deleted_at` | `timestamp NULL DEFAULT NULL COMMENT 'Soft delete banner'` | nullable, default NULL, soft delete |

- Primary key: `id`
- Index:
  - `idx_banners_position_status`: `position, status`
  - `idx_banners_sort_order`: `sort_order`
- Ràng buộc khác:
  - `inactive=đang ẩn'`

### `categories`

- Engine/Options: `InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci`
- Số cột: **10**
- Seed/import rows ước tính: **0**

| Cột | Kiểu / ràng buộc | Ghi chú nhanh |
|---|---|---|
| `id` | `bigint(20) UNSIGNED NOT NULL` | required, unsigned |
| `parent_id` | `bigint(20) UNSIGNED DEFAULT NULL COMMENT 'Danh mục cha; null nếu là danh mục gốc'` | nullable, default NULL, unsigned, khóa ngoại tiềm năng |
| `name` | `varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL` | required |
| `slug` | `varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL` | required |
| `description` | `text CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL` | nullable, default NULL |
| `sort_order` | `int(10) UNSIGNED NOT NULL DEFAULT 0` | required, default 0, unsigned |
| `status` | `varchar(30) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'active' COMMENT 'active=hiển thị` | required, default 'active' |
| `created_at` | `timestamp NULL DEFAULT current_timestamp()` | default current_timestamp() |
| `updated_at` | `timestamp NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()` | default current_timestamp() |
| `deleted_at` | `timestamp NULL DEFAULT NULL COMMENT 'Soft delete category'` | nullable, default NULL, soft delete |

- Primary key: `id`
- Unique:
  - `uq_categories_slug`: `slug`
- Index:
  - `idx_categories_parent_id`: `parent_id`
  - `idx_categories_status`: `status`
- Foreign keys:
  - `fk_categories_parent`: `parent_id` -> `categories`(`id`) ON DELETE SET NULL ON UPDATE CASCADE
- Ràng buộc khác:
  - `inactive=ẩn'`

### `certificates`

- Engine/Options: `InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci`
- Số cột: **11**
- Seed/import rows ước tính: **0**

| Cột | Kiểu / ràng buộc | Ghi chú nhanh |
|---|---|---|
| `id` | `bigint(20) UNSIGNED NOT NULL` | required, unsigned |
| `user_id` | `bigint(20) UNSIGNED NOT NULL COMMENT 'Học viên được cấp chứng chỉ'` | required, unsigned, khóa ngoại tiềm năng |
| `course_id` | `bigint(20) UNSIGNED NOT NULL COMMENT 'Khóa học được cấp chứng chỉ'` | required, unsigned, khóa ngoại tiềm năng |
| `enrollment_id` | `bigint(20) UNSIGNED NOT NULL COMMENT 'Enrollment đã hoàn thành` | required, unsigned, khóa ngoại tiềm năng |
| `certificate_code` | `varchar(100) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL COMMENT 'Mã chứng chỉ duy nhất dùng để xác thực'` | required |
| `certificate_url` | `varchar(500) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL COMMENT 'URL file chứng chỉ nếu đã render PDF/image'` | nullable, default NULL |
| `issued_at` | `timestamp NOT NULL DEFAULT current_timestamp() COMMENT 'Thời điểm cấp chứng chỉ'` | required, default current_timestamp() |
| `status` | `varchar(30) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'active' COMMENT 'active=hiệu lực` | required, default 'active' |
| `created_at` | `timestamp NULL DEFAULT current_timestamp()` | default current_timestamp() |
| `updated_at` | `timestamp NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()` | default current_timestamp() |
| `deleted_at` | `timestamp NULL DEFAULT NULL COMMENT 'Soft delete certificate'` | nullable, default NULL, soft delete |

- Primary key: `id`
- Unique:
  - `uq_certificates_code`: `certificate_code`
  - `uq_certificates_enrollment`: `enrollment_id`
- Index:
  - `idx_certificates_user_id`: `user_id`
  - `idx_certificates_course_id`: `course_id`
  - `idx_certificates_status`: `status`
  - `idx_certificates_issued_at`: `issued_at`
- Foreign keys:
  - `fk_certificates_course`: `course_id` -> `courses`(`id`) ON UPDATE CASCADE
  - `fk_certificates_user`: `user_id` -> `users`(`id`) ON UPDATE CASCADE
- Ràng buộc khác:
  - `mỗi enrollment chỉ có một certificate'`
  - `revoked=thu hồi'`

### `comments`

- Engine/Options: `InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci`
- Số cột: **9**
- Seed/import rows ước tính: **0**

| Cột | Kiểu / ràng buộc | Ghi chú nhanh |
|---|---|---|
| `id` | `bigint(20) UNSIGNED NOT NULL` | required, unsigned |
| `parent_id` | `bigint(20) UNSIGNED DEFAULT NULL COMMENT 'Bình luận cha nếu là reply'` | nullable, default NULL, unsigned, khóa ngoại tiềm năng |
| `user_id` | `bigint(20) UNSIGNED NOT NULL` | required, unsigned, khóa ngoại tiềm năng |
| `order_id` | `bigint(20) UNSIGNED DEFAULT NULL COMMENT 'Dùng xác minh user đã mua khóa học chứa lesson'` | nullable, default NULL, unsigned, khóa ngoại tiềm năng |
| `lesson_id` | `bigint(20) UNSIGNED NOT NULL` | required, unsigned, khóa ngoại tiềm năng |
| `content` | `text CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL` | required |
| `status` | `varchar(30) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'visible' COMMENT 'visible/hidden/deleted; đã bỏ pending theo chốt'` | required, default 'visible' |
| `created_at` | `timestamp NULL DEFAULT current_timestamp()` | default current_timestamp() |
| `updated_at` | `timestamp NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()` | default current_timestamp() |

- Primary key: `id`
- Index:
  - `idx_comments_parent_id`: `parent_id`
  - `idx_comments_user_id`: `user_id`
  - `idx_comments_order_id`: `order_id`
  - `idx_comments_lesson_id`: `lesson_id`
  - `idx_comments_status`: `status`
- Foreign keys:
  - `fk_comments_lesson`: `lesson_id` -> `lessons`(`id`) ON UPDATE CASCADE
  - `fk_comments_parent`: `parent_id` -> `comments`(`id`) ON DELETE SET NULL ON UPDATE CASCADE

### `coupons`

- Engine/Options: `InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci`
- Số cột: **17**
- Seed/import rows ước tính: **0**

| Cột | Kiểu / ràng buộc | Ghi chú nhanh |
|---|---|---|
| `id` | `bigint(20) UNSIGNED NOT NULL` | required, unsigned |
| `user_id` | `bigint(20) UNSIGNED DEFAULT NULL COMMENT 'Người tạo coupon; null nếu hệ thống tạo'` | nullable, default NULL, unsigned, khóa ngoại tiềm năng |
| `course_id` | `bigint(20) UNSIGNED DEFAULT NULL COMMENT 'Null nếu coupon áp dụng toàn hệ thống'` | nullable, default NULL, unsigned, khóa ngoại tiềm năng |
| `code` | `varchar(50) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL` | required |
| `name` | `varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL` | required |
| `description` | `text CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL` | nullable, default NULL |
| `discount_type` | `varchar(30) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL COMMENT 'percent/fixed'` | required |
| `discount_value` | `decimal(12,2) NOT NULL DEFAULT 0.00` | required, default 0.00 |
| `max_order_amount` | `decimal(12,2) DEFAULT NULL COMMENT 'Mức giảm tối đa nếu discount_type=percent'` | nullable, default NULL |
| `usage_limit` | `int(10) UNSIGNED DEFAULT NULL COMMENT 'Số lượt dùng tối đa; null = không giới hạn'` | nullable, default NULL, unsigned |
| `used_count` | `int(10) UNSIGNED NOT NULL DEFAULT 0` | required, default 0, unsigned |
| `start_at` | `timestamp NULL DEFAULT NULL` | nullable, default NULL |
| `end_at` | `timestamp NULL DEFAULT NULL` | nullable, default NULL |
| `status` | `varchar(30) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'active' COMMENT 'active/inactive/expired/used_up'` | required, default 'active' |
| `created_at` | `timestamp NULL DEFAULT current_timestamp()` | default current_timestamp() |
| `updated_at` | `timestamp NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()` | default current_timestamp() |
| `deleted_at` | `timestamp NULL DEFAULT NULL COMMENT 'Soft delete coupon'` | nullable, default NULL, soft delete |

- Primary key: `id`
- Unique:
  - `uq_coupons_code`: `code`
- Index:
  - `idx_coupons_user_id`: `user_id`
  - `idx_coupons_course_id`: `course_id`
  - `idx_coupons_status`: `status`
- Foreign keys:
  - `fk_coupons_course`: `course_id` -> `courses`(`id`) ON DELETE CASCADE ON UPDATE CASCADE

### `course_categories`

- Engine/Options: `InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci`
- Số cột: **3**
- Seed/import rows ước tính: **0**

| Cột | Kiểu / ràng buộc | Ghi chú nhanh |
|---|---|---|
| `category_id` | `bigint(20) UNSIGNED NOT NULL` | required, unsigned, khóa ngoại tiềm năng |
| `course_id` | `bigint(20) UNSIGNED NOT NULL` | required, unsigned, khóa ngoại tiềm năng |
| `created_at` | `timestamp NULL DEFAULT current_timestamp()` | default current_timestamp() |

- Primary key: `category_id, course_id`
- Index:
  - `idx_course_categories_course_id`: `course_id`
- Foreign keys:
  - `fk_course_categories_category`: `category_id` -> `categories`(`id`) ON DELETE CASCADE ON UPDATE CASCADE

### `course_faqs`

- Engine/Options: `InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci`
- Số cột: **5**
- Seed/import rows ước tính: **0**

| Cột | Kiểu / ràng buộc | Ghi chú nhanh |
|---|---|---|
| `faq_id` | `bigint(20) UNSIGNED NOT NULL` | required, unsigned, khóa ngoại tiềm năng |
| `course_id` | `bigint(20) UNSIGNED NOT NULL` | required, unsigned, khóa ngoại tiềm năng |
| `sort_order` | `int(10) UNSIGNED NOT NULL DEFAULT 0` | required, default 0, unsigned |
| `created_at` | `timestamp NULL DEFAULT current_timestamp()` | default current_timestamp() |
| `deleted_at` | `timestamp NULL DEFAULT NULL COMMENT 'Soft delete liên kết FAQ-course để còn đối chất nếu có tranh chấp'` | nullable, default NULL, soft delete |

- Primary key: `faq_id, course_id`
- Unique:
  - `uq_course_faqs_course_sort`: `course_id, sort_order`
- Index:
  - `idx_course_faqs_course_id`: `course_id`
  - `idx_course_faqs_deleted_at`: `deleted_at`
- Foreign keys:
  - `fk_course_faqs_course`: `course_id` -> `courses`(`id`) ON UPDATE CASCADE

### `course_reviews`

- Engine/Options: `InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci`
- Số cột: **7**
- Seed/import rows ước tính: **0**

| Cột | Kiểu / ràng buộc | Ghi chú nhanh |
|---|---|---|
| `id` | `bigint(20) UNSIGNED NOT NULL` | required, unsigned |
| `order_id` | `bigint(20) UNSIGNED NOT NULL COMMENT 'Chứng minh người review đã mua khóa'` | required, unsigned, khóa ngoại tiềm năng |
| `rating` | `tinyint(3) UNSIGNED NOT NULL` | required, unsigned |
| `comment` | `text CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL` | nullable, default NULL |
| `created_at` | `timestamp NULL DEFAULT current_timestamp()` | default current_timestamp() |
| `updated_at` | `timestamp NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()` | default current_timestamp() |
| `deleted_at` | `timestamp NULL DEFAULT NULL COMMENT 'Soft delete review nếu cần ẩn nhưng vẫn giữ lịch sử'` | nullable, default NULL, soft delete |

- Primary key: `id`
- Unique:
  - `uq_course_reviews_order`: `order_id`
- Index:
  - `idx_course_reviews_rating`: `rating`
- Foreign keys:
  - `fk_course_reviews_order`: `order_id` -> `orders`(`id`) ON UPDATE CASCADE

### `course_sections`

- Engine/Options: `InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci`
- Số cột: **9**
- Seed/import rows ước tính: **0**

| Cột | Kiểu / ràng buộc | Ghi chú nhanh |
|---|---|---|
| `id` | `bigint(20) UNSIGNED NOT NULL` | required, unsigned |
| `course_id` | `bigint(20) UNSIGNED NOT NULL` | required, unsigned, khóa ngoại tiềm năng |
| `title` | `varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL` | required |
| `description` | `text CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL` | nullable, default NULL |
| `sort_order` | `int(10) UNSIGNED NOT NULL DEFAULT 0` | required, default 0, unsigned |
| `status` | `varchar(30) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'draft' COMMENT 'draft/published/hidden'` | required, default 'draft' |
| `created_at` | `timestamp NULL DEFAULT current_timestamp()` | default current_timestamp() |
| `updated_at` | `timestamp NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()` | default current_timestamp() |
| `deleted_at` | `timestamp NULL DEFAULT NULL COMMENT 'Soft delete course section'` | nullable, default NULL, soft delete |

- Primary key: `id`
- Unique:
  - `uq_course_sections_sort`: `course_id, sort_order`
- Index:
  - `idx_course_sections_course_id`: `course_id`
  - `idx_course_sections_status`: `status`
- Foreign keys:
  - `fk_course_sections_course`: `course_id` -> `courses`(`id`) ON DELETE CASCADE ON UPDATE CASCADE

### `courses`

- Engine/Options: `InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci`
- Số cột: **22**
- Seed/import rows ước tính: **0**

| Cột | Kiểu / ràng buộc | Ghi chú nhanh |
|---|---|---|
| `id` | `bigint(20) UNSIGNED NOT NULL` | required, unsigned |
| `instructor_id` | `bigint(20) UNSIGNED NOT NULL COMMENT 'User đóng vai trò giảng viên'` | required, unsigned, khóa ngoại tiềm năng |
| `title` | `varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL` | required |
| `slug` | `varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL` | required |
| `short_description` | `varchar(500) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL` | nullable, default NULL |
| `description` | `text CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL` | nullable, default NULL |
| `thumbnail_url` | `varchar(500) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL` | nullable, default NULL |
| `intro_video_url` | `varchar(500) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL` | nullable, default NULL |
| `price` | `decimal(12,2) NOT NULL DEFAULT 0.00 COMMENT 'Giá gốc khóa học'` | required, default 0.00 |
| `sale_price` | `decimal(12,2) DEFAULT NULL COMMENT 'Giá khuyến mãi; null nếu không sale'` | nullable, default NULL |
| `level` | `varchar(30) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'beginner' COMMENT 'beginner/intermediate/advanced/all_levels'` | required, default 'beginner' |
| `language` | `varchar(50) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'vi'` | required, default 'vi' |
| `requirements` | `text CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL COMMENT 'Yêu cầu đầu vào'` | nullable, default NULL |
| `outcomes` | `text CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL COMMENT 'Kết quả đạt được sau khóa học'` | nullable, default NULL |
| `status` | `varchar(30) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'draft' COMMENT 'draft/pending_review/approved/rejected/published/hidden'` | required, default 'draft' |
| `is_featured` | `tinyint(1) NOT NULL DEFAULT 0` | required, default 0 |
| `total_duration_seconds` | `int(10) UNSIGNED NOT NULL DEFAULT 0` | required, default 0, unsigned |
| `published_at` | `timestamp NULL DEFAULT NULL` | nullable, default NULL |
| `admin_reject_reason` | `text CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL COMMENT 'Lý do admin từ chối khi status=rejected'` | nullable, default NULL |
| `created_at` | `timestamp NULL DEFAULT current_timestamp()` | default current_timestamp() |
| `updated_at` | `timestamp NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()` | default current_timestamp() |
| `deleted_at` | `timestamp NULL DEFAULT NULL COMMENT 'Soft delete course'` | nullable, default NULL, soft delete |

- Primary key: `id`
- Unique:
  - `uq_courses_slug`: `slug`
- Index:
  - `idx_courses_instructor_id`: `instructor_id`
  - `idx_courses_status`: `status`
  - `idx_courses_featured`: `is_featured`
- Foreign keys:
  - `fk_courses_instructor`: `instructor_id` -> `users`(`id`) ON UPDATE CASCADE

### `enrollments`

- Engine/Options: `InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci`
- Số cột: **11**
- Seed/import rows ước tính: **0**

| Cột | Kiểu / ràng buộc | Ghi chú nhanh |
|---|---|---|
| `id` | `bigint(20) UNSIGNED NOT NULL` | required, unsigned |
| `user_id` | `bigint(20) UNSIGNED NOT NULL` | required, unsigned, khóa ngoại tiềm năng |
| `course_id` | `bigint(20) UNSIGNED NOT NULL` | required, unsigned, khóa ngoại tiềm năng |
| `order_id` | `bigint(20) UNSIGNED NOT NULL COMMENT 'Ghi danh được sinh từ order đã paid` | required, unsigned, khóa ngoại tiềm năng |
| `status` | `varchar(30) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'active' COMMENT 'active=đang học` | required, default 'active' |
| `progress_percent` | `decimal(5,2) NOT NULL DEFAULT 0.00` | required, default 0.00 |
| `enrolled_at` | `timestamp NOT NULL DEFAULT current_timestamp()` | required, default current_timestamp() |
| `completed_at` | `timestamp NULL DEFAULT NULL` | nullable, default NULL |
| `last_accessed_at` | `timestamp NULL DEFAULT NULL` | nullable, default NULL |
| `created_at` | `timestamp NULL DEFAULT current_timestamp()` | default current_timestamp() |
| `updated_at` | `timestamp NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()` | default current_timestamp() |

- Primary key: `id`
- Unique:
  - `uq_enrollments_user_course`: `user_id, course_id`
  - `uq_enrollments_order`: `order_id`
- Index:
  - `idx_enrollments_user_id`: `user_id`
  - `idx_enrollments_course_id`: `course_id`
  - `idx_enrollments_status`: `status`
- Foreign keys:
  - `fk_enrollments_course`: `course_id` -> `courses`(`id`) ON UPDATE CASCADE
  - `fk_enrollments_user`: `user_id` -> `users`(`id`) ON UPDATE CASCADE
- Ràng buộc khác:
  - `kể cả coupon 0đ'`
  - `completed=đã hoàn thành; đã bỏ expired/cancelled theo chốt'`

### `faqs`

- Engine/Options: `InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci`
- Số cột: **9**
- Seed/import rows ước tính: **0**

| Cột | Kiểu / ràng buộc | Ghi chú nhanh |
|---|---|---|
| `id` | `bigint(20) UNSIGNED NOT NULL` | required, unsigned |
| `question` | `varchar(500) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL` | required |
| `answer` | `text CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL` | required |
| `type` | `varchar(50) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'general'` | required, default 'general' |
| `status` | `varchar(30) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'active' COMMENT 'active/inactive'` | required, default 'active' |
| `sort_order` | `int(10) UNSIGNED NOT NULL DEFAULT 0` | required, default 0, unsigned |
| `created_at` | `timestamp NULL DEFAULT current_timestamp()` | default current_timestamp() |
| `updated_at` | `timestamp NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()` | default current_timestamp() |
| `deleted_at` | `timestamp NULL DEFAULT NULL COMMENT 'Soft delete FAQ để còn đối chứng nội dung câu trả lời'` | nullable, default NULL, soft delete |

- Primary key: `id`
- Index:
  - `idx_faqs_type_status`: `type, status`
  - `idx_faqs_sort_order`: `sort_order`

### `instructor_profiles`

- Engine/Options: `InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci`
- Số cột: **8**
- Seed/import rows ước tính: **0**

| Cột | Kiểu / ràng buộc | Ghi chú nhanh |
|---|---|---|
| `id` | `bigint(20) UNSIGNED NOT NULL` | required, unsigned |
| `user_id` | `bigint(20) UNSIGNED NOT NULL` | required, unsigned, khóa ngoại tiềm năng |
| `bio` | `text CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL` | nullable, default NULL |
| `expertise` | `text CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL` | nullable, default NULL |
| `experience_years` | `tinyint(3) UNSIGNED DEFAULT NULL` | nullable, default NULL, unsigned |
| `level` | `varchar(50) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL` | nullable, default NULL |
| `created_at` | `timestamp NULL DEFAULT current_timestamp()` | default current_timestamp() |
| `updated_at` | `timestamp NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()` | default current_timestamp() |

- Primary key: `id`
- Unique:
  - `uq_instructor_profiles_user`: `user_id`
- Foreign keys:
  - `fk_instructor_profiles_user`: `user_id` -> `users`(`id`) ON DELETE CASCADE ON UPDATE CASCADE

### `lesson_assets`

- Engine/Options: `InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci`
- Số cột: **10**
- Seed/import rows ước tính: **0**

| Cột | Kiểu / ràng buộc | Ghi chú nhanh |
|---|---|---|
| `id` | `bigint(20) UNSIGNED NOT NULL` | required, unsigned |
| `lesson_id` | `bigint(20) UNSIGNED NOT NULL` | required, unsigned, khóa ngoại tiềm năng |
| `title` | `varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL` | required |
| `file_url` | `varchar(500) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL` | required |
| `file_name` | `varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL` | required |
| `file_type` | `varchar(50) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL` | required |
| `file_size` | `bigint(20) UNSIGNED DEFAULT NULL COMMENT 'Dung lượng file tính bằng byte'` | nullable, default NULL, unsigned |
| `note` | `text CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL` | nullable, default NULL |
| `created_at` | `timestamp NULL DEFAULT current_timestamp()` | default current_timestamp() |
| `deleted_at` | `timestamp NULL DEFAULT NULL COMMENT 'Soft delete asset'` | nullable, default NULL, soft delete |

- Primary key: `id`
- Index:
  - `idx_lesson_assets_lesson_id`: `lesson_id`
- Foreign keys:
  - `fk_lesson_assets_lesson`: `lesson_id` -> `lessons`(`id`) ON DELETE CASCADE ON UPDATE CASCADE

### `lesson_notes`

- Engine/Options: `InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci`
- Số cột: **9**
- Seed/import rows ước tính: **0**

| Cột | Kiểu / ràng buộc | Ghi chú nhanh |
|---|---|---|
| `id` | `bigint(20) UNSIGNED NOT NULL` | required, unsigned |
| `user_id` | `bigint(20) UNSIGNED NOT NULL COMMENT 'Người tạo ghi chú'` | required, unsigned, khóa ngoại tiềm năng |
| `course_id` | `bigint(20) UNSIGNED NOT NULL COMMENT 'Khóa học chứa lesson'` | required, unsigned, khóa ngoại tiềm năng |
| `lesson_id` | `bigint(20) UNSIGNED NOT NULL COMMENT 'Bài học được ghi chú'` | required, unsigned, khóa ngoại tiềm năng |
| `content` | `text CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL COMMENT 'Nội dung ghi chú cá nhân'` | required |
| `note_time_second` | `int(10) UNSIGNED DEFAULT NULL COMMENT 'Mốc thời gian video` | nullable, default NULL, unsigned |
| `created_at` | `timestamp NULL DEFAULT current_timestamp()` | default current_timestamp() |
| `updated_at` | `timestamp NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()` | default current_timestamp() |
| `deleted_at` | `timestamp NULL DEFAULT NULL COMMENT 'Soft delete note'` | nullable, default NULL, soft delete |

- Primary key: `id`
- Index:
  - `idx_lesson_notes_user_lesson`: `user_id, lesson_id`
  - `idx_lesson_notes_user_course`: `user_id, course_id`
  - `idx_lesson_notes_lesson_id`: `lesson_id`
  - `idx_lesson_notes_note_time_second`: `note_time_second`
  - `fk_lesson_notes_course`: `course_id`
- Foreign keys:
  - `fk_lesson_notes_course`: `course_id` -> `courses`(`id`) ON DELETE CASCADE ON UPDATE CASCADE
  - `fk_lesson_notes_user`: `user_id` -> `users`(`id`) ON DELETE CASCADE ON UPDATE CASCADE
- Ràng buộc khác:
  - `null nếu không gắn với video time'`

### `lesson_progress`

- Engine/Options: `InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci`
- Số cột: **10**
- Seed/import rows ước tính: **0**

| Cột | Kiểu / ràng buộc | Ghi chú nhanh |
|---|---|---|
| `id` | `bigint(20) UNSIGNED NOT NULL` | required, unsigned |
| `lesson_id` | `bigint(20) UNSIGNED NOT NULL` | required, unsigned, khóa ngoại tiềm năng |
| `user_id` | `bigint(20) UNSIGNED NOT NULL` | required, unsigned, khóa ngoại tiềm năng |
| `status` | `varchar(30) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'not_started' COMMENT 'not_started/in_progress/completed'` | required, default 'not_started' |
| `started_at` | `timestamp NULL DEFAULT NULL` | nullable, default NULL |
| `completed_at` | `timestamp NULL DEFAULT NULL` | nullable, default NULL |
| `learning_duration_seconds` | `int(10) UNSIGNED NOT NULL DEFAULT 0` | required, default 0, unsigned |
| `last_accessed_at` | `timestamp NULL DEFAULT NULL` | nullable, default NULL |
| `created_at` | `timestamp NULL DEFAULT current_timestamp()` | default current_timestamp() |
| `updated_at` | `timestamp NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()` | default current_timestamp() |

- Primary key: `id`
- Unique:
  - `uq_lesson_progress_user_lesson`: `user_id, lesson_id`
- Index:
  - `idx_lesson_progress_lesson_id`: `lesson_id`
  - `idx_lesson_progress_user_id`: `user_id`
  - `idx_lesson_progress_status`: `status`
- Foreign keys:
  - `fk_lesson_progress_lesson`: `lesson_id` -> `lessons`(`id`) ON DELETE CASCADE ON UPDATE CASCADE

### `lessons`

- Engine/Options: `InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci`
- Số cột: **15**
- Seed/import rows ước tính: **0**

| Cột | Kiểu / ràng buộc | Ghi chú nhanh |
|---|---|---|
| `id` | `bigint(20) UNSIGNED NOT NULL` | required, unsigned |
| `course_section_id` | `bigint(20) UNSIGNED NOT NULL` | required, unsigned, khóa ngoại tiềm năng |
| `course_id` | `bigint(20) UNSIGNED NOT NULL` | required, unsigned, khóa ngoại tiềm năng |
| `title` | `varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL` | required |
| `slug` | `varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL` | required |
| `lesson_type` | `varchar(30) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'video' COMMENT 'video=bài video` | required, default 'video' |
| `content` | `longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL` | nullable, default NULL |
| `video_url` | `varchar(500) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL` | nullable, default NULL |
| `video_duration_seconds` | `int(10) UNSIGNED DEFAULT NULL` | nullable, default NULL, unsigned |
| `is_preview` | `tinyint(1) NOT NULL DEFAULT 0 COMMENT 'Chỉ có hiệu lực khi status=published'` | required, default 0 |
| `status` | `varchar(30) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'draft' COMMENT 'draft/published/hidden'` | required, default 'draft' |
| `sort_order` | `int(10) UNSIGNED NOT NULL DEFAULT 0` | required, default 0, unsigned |
| `created_at` | `timestamp NULL DEFAULT current_timestamp()` | default current_timestamp() |
| `updated_at` | `timestamp NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()` | default current_timestamp() |
| `deleted_at` | `timestamp NULL DEFAULT NULL COMMENT 'Soft delete lesson'` | nullable, default NULL, soft delete |

- Primary key: `id`
- Unique:
  - `uq_lessons_course_slug`: `course_id, slug`
  - `uq_lessons_section_sort`: `course_section_id, sort_order`
- Index:
  - `idx_lessons_course_id`: `course_id`
  - `idx_lessons_section_id`: `course_section_id`
  - `idx_lessons_status`: `status`
- Foreign keys:
  - `fk_lessons_course`: `course_id` -> `courses`(`id`) ON DELETE CASCADE ON UPDATE CASCADE
- Ràng buộc khác:
  - `text=bài chữ'`

### `notifications`

- Engine/Options: `InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci`
- Số cột: **16**
- Seed/import rows ước tính: **0**

| Cột | Kiểu / ràng buộc | Ghi chú nhanh |
|---|---|---|
| `id` | `bigint(20) UNSIGNED NOT NULL` | required, unsigned |
| `user_id` | `bigint(20) UNSIGNED NOT NULL COMMENT 'Người nhận thông báo'` | required, unsigned, khóa ngoại tiềm năng |
| `type` | `varchar(50) NOT NULL COMMENT 'Loại thông báo` | required |
| `title` | `varchar(255) NOT NULL COMMENT 'Tiêu đề thông báo'` | required |
| `message` | `text NOT NULL COMMENT 'Nội dung thông báo'` | required |
| `data` | `longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL COMMENT 'Dữ liệu điều hướng/phụ trợ như order_id` | nullable, default NULL |
| `action_url` | `varchar(500) DEFAULT NULL COMMENT 'URL điều hướng khi người dùng bấm thông báo'` | nullable, default NULL |
| `channel` | `varchar(30) NOT NULL DEFAULT 'database' COMMENT 'Kênh thông báo` | required, default 'database' |
| `read_at` | `timestamp NULL DEFAULT NULL COMMENT 'NULL = chưa đọc` | nullable, default NULL |
| `email_to` | `varchar(255) DEFAULT NULL COMMENT 'Email người nhận nếu có gửi mail'` | nullable, default NULL |
| `email_status` | `varchar(30) DEFAULT NULL COMMENT 'Trạng thái gửi email` | nullable, default NULL |
| `email_sent_at` | `timestamp NULL DEFAULT NULL COMMENT 'Thời điểm gửi email thành công'` | nullable, default NULL |
| `email_error` | `text DEFAULT NULL COMMENT 'Lỗi gửi email nếu thất bại'` | nullable, default NULL |
| `created_at` | `timestamp NULL DEFAULT current_timestamp()` | default current_timestamp() |
| `updated_at` | `timestamp NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()` | default current_timestamp() |
| `deleted_at` | `timestamp NULL DEFAULT NULL COMMENT 'Soft delete notification'` | nullable, default NULL, soft delete |

- Primary key: `id`
- Index:
  - `idx_notifications_user_id`: `user_id`
  - `idx_notifications_user_read_at`: `user_id, read_at`
  - `idx_notifications_type`: `type`
  - `idx_notifications_email_status`: `email_status`
  - `idx_notifications_created_at`: `created_at`
- Foreign keys:
  - `fk_notifications_user`: `user_id` -> `users`(`id`) ON DELETE CASCADE ON UPDATE CASCADE
- Ràng buộc khác:
  - `validate ở code`
  - `không dùng enum DB'`
  - `course_id`
  - `lesson_id' CHECK (json_valid(`data`))`
  - `validate ở code`
  - `không dùng enum DB'`
  - `có giá trị = đã đọc'`
  - `validate ở code`
  - `không dùng enum DB'`

### `orders`

- Engine/Options: `InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci`
- Số cột: **14**
- Seed/import rows ước tính: **0**

| Cột | Kiểu / ràng buộc | Ghi chú nhanh |
|---|---|---|
| `id` | `bigint(20) UNSIGNED NOT NULL` | required, unsigned |
| `coupon_id` | `bigint(20) UNSIGNED DEFAULT NULL` | nullable, default NULL, unsigned, khóa ngoại tiềm năng |
| `course_id` | `bigint(20) UNSIGNED NOT NULL` | required, unsigned, khóa ngoại tiềm năng |
| `user_id` | `bigint(20) UNSIGNED NOT NULL` | required, unsigned, khóa ngoại tiềm năng |
| `order_code` | `varchar(50) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL COMMENT 'Mã đơn hàng duy nhất'` | required |
| `status` | `varchar(30) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'pending' COMMENT 'pending/paid/cancelled/failed/expired'` | required, default 'pending' |
| `price_snapshot` | `decimal(12,2) NOT NULL DEFAULT 0.00 COMMENT 'Giá khóa học tại thời điểm mua'` | required, default 0.00 |
| `payment_method` | `varchar(50) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL COMMENT 'bank_transfer/momo/vnpay/cash/free...'` | nullable, default NULL |
| `provider_transaction_id` | `varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL COMMENT 'Mã giao dịch do cổng thanh toán trả về'` | nullable, default NULL, khóa ngoại tiềm năng |
| `amount` | `decimal(12,2) NOT NULL DEFAULT 0.00 COMMENT 'Số tiền thực trả sau giảm giá'` | required, default 0.00 |
| `payment_status` | `varchar(30) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT 'unpaid' COMMENT 'unpaid/processing/paid/failed; đã bỏ refunded theo chốt'` | default 'unpaid' |
| `paid_at` | `timestamp NULL DEFAULT NULL` | nullable, default NULL |
| `created_at` | `timestamp NULL DEFAULT current_timestamp()` | default current_timestamp() |
| `updated_at` | `timestamp NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()` | default current_timestamp() |

- Primary key: `id`
- Unique:
  - `uq_orders_order_code`: `order_code`
  - `uq_orders_provider_transaction`: `provider_transaction_id`
- Index:
  - `idx_orders_coupon_id`: `coupon_id`
  - `idx_orders_course_id`: `course_id`
  - `idx_orders_user_id`: `user_id`
  - `idx_orders_status`: `status`
  - `idx_orders_payment_status`: `payment_status`
- Foreign keys:
  - `fk_orders_coupon`: `coupon_id` -> `coupons`(`id`) ON DELETE SET NULL ON UPDATE CASCADE
  - `fk_orders_user`: `user_id` -> `users`(`id`) ON UPDATE CASCADE

### `payout_accounts`

- Engine/Options: `InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci`
- Số cột: **10**
- Seed/import rows ước tính: **0**

| Cột | Kiểu / ràng buộc | Ghi chú nhanh |
|---|---|---|
| `id` | `bigint(20) UNSIGNED NOT NULL` | required, unsigned |
| `user_id` | `bigint(20) UNSIGNED NOT NULL` | required, unsigned, khóa ngoại tiềm năng |
| `provider` | `varchar(50) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL COMMENT 'bank/momo/paypal...'` | required |
| `account_number` | `varchar(100) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL` | required |
| `account_name` | `varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL` | required |
| `connected_at` | `timestamp NULL DEFAULT NULL` | nullable, default NULL |
| `status` | `varchar(30) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'pending_verification' COMMENT 'active/inactive/pending_verification/rejected'` | required, default 'pending_verification' |
| `created_at` | `timestamp NULL DEFAULT current_timestamp()` | default current_timestamp() |
| `updated_at` | `timestamp NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()` | default current_timestamp() |
| `deleted_at` | `timestamp NULL DEFAULT NULL COMMENT 'Soft delete payout account; không xóa cứng dữ liệu tài chính'` | nullable, default NULL, soft delete |

- Primary key: `id`
- Unique:
  - `uq_payout_accounts_identity`: `user_id, provider, account_number`
- Index:
  - `idx_payout_accounts_user_id`: `user_id`
  - `idx_payout_accounts_status`: `status`
- Foreign keys:
  - `fk_payout_accounts_user`: `user_id` -> `users`(`id`) ON UPDATE CASCADE

### `quiz_attempt_answers`

- Engine/Options: `InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci`
- Số cột: **7**
- Seed/import rows ước tính: **0**

| Cột | Kiểu / ràng buộc | Ghi chú nhanh |
|---|---|---|
| `id` | `bigint(20) UNSIGNED NOT NULL` | required, unsigned |
| `question_id` | `bigint(20) UNSIGNED NOT NULL` | required, unsigned, khóa ngoại tiềm năng |
| `attempt_id` | `bigint(20) UNSIGNED NOT NULL` | required, unsigned, khóa ngoại tiềm năng |
| `option_id` | `bigint(20) UNSIGNED DEFAULT NULL COMMENT 'Null nếu câu hỏi không có option phù hợp hoặc cần mở rộng sau'` | nullable, default NULL, unsigned, khóa ngoại tiềm năng |
| `is_correct` | `tinyint(1) NOT NULL DEFAULT 0` | required, default 0 |
| `score_earned` | `decimal(6,2) NOT NULL DEFAULT 0.00` | required, default 0.00 |
| `created_at` | `timestamp NULL DEFAULT current_timestamp()` | default current_timestamp() |

- Primary key: `id`
- Unique:
  - `uq_quiz_attempt_answers_question`: `attempt_id, question_id`
- Index:
  - `idx_quiz_attempt_answers_question_id`: `question_id`
  - `idx_quiz_attempt_answers_attempt_id`: `attempt_id`
  - `idx_quiz_attempt_answers_option_id`: `option_id`
- Foreign keys:
  - `fk_quiz_attempt_answers_attempt`: `attempt_id` -> `quiz_attempts`(`id`) ON DELETE CASCADE ON UPDATE CASCADE
  - `fk_quiz_attempt_answers_question`: `question_id` -> `quiz_questions`(`id`) ON UPDATE CASCADE

### `quiz_attempts`

- Engine/Options: `InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci`
- Số cột: **11**
- Seed/import rows ước tính: **0**

| Cột | Kiểu / ràng buộc | Ghi chú nhanh |
|---|---|---|
| `id` | `bigint(20) UNSIGNED NOT NULL` | required, unsigned |
| `quiz_id` | `bigint(20) UNSIGNED NOT NULL` | required, unsigned, khóa ngoại tiềm năng |
| `user_id` | `bigint(20) UNSIGNED NOT NULL` | required, unsigned, khóa ngoại tiềm năng |
| `attempt_number` | `int(10) UNSIGNED NOT NULL DEFAULT 1` | required, default 1, unsigned |
| `score` | `decimal(6,2) DEFAULT NULL` | nullable, default NULL |
| `total_score` | `decimal(6,2) NOT NULL DEFAULT 0.00` | required, default 0.00 |
| `passed` | `tinyint(1) NOT NULL DEFAULT 0` | required, default 0 |
| `status` | `varchar(30) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'in_progress' COMMENT 'in_progress/submitted; đã bỏ graded và cancelled theo chốt'` | required, default 'in_progress' |
| `started_at` | `timestamp NULL DEFAULT NULL` | nullable, default NULL |
| `submitted_at` | `timestamp NULL DEFAULT NULL` | nullable, default NULL |
| `created_at` | `timestamp NULL DEFAULT current_timestamp()` | default current_timestamp() |

- Primary key: `id`
- Unique:
  - `uq_quiz_attempts_number`: `quiz_id, user_id, attempt_number`
- Index:
  - `idx_quiz_attempts_quiz_id`: `quiz_id`
  - `idx_quiz_attempts_user_id`: `user_id`
  - `idx_quiz_attempts_status`: `status`
- Foreign keys:
  - `fk_quiz_attempts_quiz`: `quiz_id` -> `quizzes`(`id`) ON UPDATE CASCADE

### `quiz_options`

- Engine/Options: `InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci`
- Số cột: **6**
- Seed/import rows ước tính: **0**

| Cột | Kiểu / ràng buộc | Ghi chú nhanh |
|---|---|---|
| `id` | `bigint(20) UNSIGNED NOT NULL` | required, unsigned |
| `question_id` | `bigint(20) UNSIGNED NOT NULL` | required, unsigned, khóa ngoại tiềm năng |
| `option_text` | `text NOT NULL` | required |
| `is_correct` | `tinyint(1) NOT NULL DEFAULT 0` | required, default 0 |
| `sort_order` | `int(10) UNSIGNED NOT NULL DEFAULT 0` | required, default 0, unsigned |
| `created_at` | `timestamp NULL DEFAULT current_timestamp()` | default current_timestamp() |

- Primary key: `id`
- Unique:
  - `uq_quiz_options_sort`: `question_id, sort_order`
- Index:
  - `idx_quiz_options_question_id`: `question_id`
- Foreign keys:
  - `fk_quiz_options_question`: `question_id` -> `quiz_questions`(`id`) ON DELETE CASCADE ON UPDATE CASCADE

### `quiz_questions`

- Engine/Options: `InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci`
- Số cột: **8**
- Seed/import rows ước tính: **0**

| Cột | Kiểu / ràng buộc | Ghi chú nhanh |
|---|---|---|
| `id` | `bigint(20) UNSIGNED NOT NULL` | required, unsigned |
| `quiz_id` | `bigint(20) UNSIGNED NOT NULL` | required, unsigned, khóa ngoại tiềm năng |
| `question_text` | `text CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL` | required |
| `question_type` | `varchar(30) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'single_choice' COMMENT 'single_choice/multiple_choice/true_false'` | required, default 'single_choice' |
| `score` | `decimal(6,2) NOT NULL DEFAULT 1.00` | required, default 1.00 |
| `sort_order` | `int(10) UNSIGNED NOT NULL DEFAULT 0` | required, default 0, unsigned |
| `explanation` | `text CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL` | nullable, default NULL |
| `created_at` | `timestamp NULL DEFAULT current_timestamp()` | default current_timestamp() |

- Primary key: `id`
- Unique:
  - `uq_quiz_questions_sort`: `quiz_id, sort_order`
- Index:
  - `idx_quiz_questions_quiz_id`: `quiz_id`
- Foreign keys:
  - `fk_quiz_questions_quiz`: `quiz_id` -> `quizzes`(`id`) ON DELETE CASCADE ON UPDATE CASCADE

### `quizzes`

- Engine/Options: `InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci`
- Số cột: **10**
- Seed/import rows ước tính: **0**

| Cột | Kiểu / ràng buộc | Ghi chú nhanh |
|---|---|---|
| `id` | `bigint(20) UNSIGNED NOT NULL` | required, unsigned |
| `course_id` | `bigint(20) UNSIGNED NOT NULL` | required, unsigned, khóa ngoại tiềm năng |
| `lesson_id` | `bigint(20) UNSIGNED DEFAULT NULL` | nullable, default NULL, unsigned, khóa ngoại tiềm năng |
| `title` | `varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL` | required |
| `description` | `text CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL` | nullable, default NULL |
| `passing_score` | `decimal(6,2) NOT NULL DEFAULT 0.00` | required, default 0.00 |
| `status` | `varchar(30) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'draft' COMMENT 'draft/published/hidden'` | required, default 'draft' |
| `created_at` | `timestamp NULL DEFAULT current_timestamp()` | default current_timestamp() |
| `updated_at` | `timestamp NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()` | default current_timestamp() |
| `deleted_at` | `timestamp NULL DEFAULT NULL COMMENT 'Soft delete quiz'` | nullable, default NULL, soft delete |

- Primary key: `id`
- Index:
  - `idx_quizzes_course_id`: `course_id`
  - `idx_quizzes_lesson_id`: `lesson_id`
  - `idx_quizzes_status`: `status`
- Foreign keys:
  - `fk_quizzes_course`: `course_id` -> `courses`(`id`) ON DELETE CASCADE ON UPDATE CASCADE

### `revenues`

- Engine/Options: `InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci`
- Số cột: **10**
- Seed/import rows ước tính: **0**

| Cột | Kiểu / ràng buộc | Ghi chú nhanh |
|---|---|---|
| `id` | `bigint(20) UNSIGNED NOT NULL` | required, unsigned |
| `instructor_id` | `bigint(20) UNSIGNED NOT NULL` | required, unsigned, khóa ngoại tiềm năng |
| `course_id` | `bigint(20) UNSIGNED NOT NULL` | required, unsigned, khóa ngoại tiềm năng |
| `order_id` | `bigint(20) UNSIGNED NOT NULL` | required, unsigned, khóa ngoại tiềm năng |
| `gross_amount` | `decimal(12,2) NOT NULL DEFAULT 0.00 COMMENT 'Tổng tiền đơn hàng'` | required, default 0.00 |
| `instructor_amount` | `decimal(12,2) NOT NULL DEFAULT 0.00 COMMENT 'Tiền giảng viên nhận'` | required, default 0.00 |
| `platform_fee_amount` | `decimal(12,2) NOT NULL DEFAULT 0.00 COMMENT 'Phí nền tảng'` | required, default 0.00 |
| `status` | `varchar(30) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'pending' COMMENT 'pending/available/withdrawn/cancelled'` | required, default 'pending' |
| `earned_at` | `timestamp NOT NULL DEFAULT current_timestamp()` | required, default current_timestamp() |
| `created_at` | `timestamp NULL DEFAULT current_timestamp()` | default current_timestamp() |

- Primary key: `id`
- Unique:
  - `uq_revenues_order`: `order_id`
- Index:
  - `idx_revenues_instructor_id`: `instructor_id`
  - `idx_revenues_course_id`: `course_id`
  - `idx_revenues_status`: `status`
- Foreign keys:
  - `fk_revenues_course`: `course_id` -> `courses`(`id`) ON UPDATE CASCADE
  - `fk_revenues_order`: `order_id` -> `orders`(`id`) ON UPDATE CASCADE

### `sessions`

- Engine/Options: `InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci`
- Số cột: **9**
- Seed/import rows ước tính: **0**

| Cột | Kiểu / ràng buộc | Ghi chú nhanh |
|---|---|---|
| `id` | `bigint(20) UNSIGNED NOT NULL` | required, unsigned |
| `user_id` | `bigint(20) UNSIGNED NOT NULL` | required, unsigned, khóa ngoại tiềm năng |
| `refresh_token_hash` | `varchar(255) NOT NULL COMMENT 'Chỉ lưu hash refresh token` | required |
| `device_name` | `varchar(255) DEFAULT NULL` | nullable, default NULL |
| `ip_address` | `varchar(45) DEFAULT NULL COMMENT 'Hỗ trợ IPv4/IPv6'` | nullable, default NULL |
| `user_agent` | `text DEFAULT NULL` | nullable, default NULL |
| `expires_at` | `timestamp NOT NULL COMMENT 'Thời điểm hết hạn phiên'` | required |
| `revoked_at` | `timestamp NULL DEFAULT NULL COMMENT 'Thời điểm thu hồi phiên'` | nullable, default NULL |
| `created_at` | `timestamp NULL DEFAULT current_timestamp()` | default current_timestamp() |

- Primary key: `id`
- Index:
  - `idx_sessions_user_id`: `user_id`
  - `idx_sessions_refresh_token_hash`: `refresh_token_hash`
  - `idx_sessions_expires_at`: `expires_at`
- Foreign keys:
  - `fk_sessions_user`: `user_id` -> `users`(`id`) ON DELETE CASCADE ON UPDATE CASCADE
- Ràng buộc khác:
  - `không lưu token thô'`

### `user_category_interests`

- Engine/Options: `InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci`
- Số cột: **6**
- Seed/import rows ước tính: **0**

| Cột | Kiểu / ràng buộc | Ghi chú nhanh |
|---|---|---|
| `id` | `bigint(20) UNSIGNED NOT NULL` | required, unsigned |
| `user_id` | `bigint(20) UNSIGNED NOT NULL COMMENT 'Người dùng chọn chủ đề quan tâm'` | required, unsigned, khóa ngoại tiềm năng |
| `category_id` | `bigint(20) UNSIGNED NOT NULL COMMENT 'Danh mục/chủ đề quan tâm'` | required, unsigned, khóa ngoại tiềm năng |
| `interest_level` | `tinyint(3) UNSIGNED NOT NULL DEFAULT 3 COMMENT 'Mức độ quan tâm từ 1 đến 5'` | required, default 3, unsigned |
| `created_at` | `timestamp NULL DEFAULT current_timestamp()` | default current_timestamp() |
| `updated_at` | `timestamp NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()` | default current_timestamp() |

- Primary key: `id`
- Unique:
  - `uq_user_category_interests_user_category`: `user_id, category_id`
- Index:
  - `idx_user_category_interests_user_id`: `user_id`
  - `idx_user_category_interests_category_id`: `category_id`
  - `idx_user_category_interests_level`: `interest_level`
- Foreign keys:
  - `fk_user_category_interests_category`: `category_id` -> `categories`(`id`) ON DELETE CASCADE ON UPDATE CASCADE

### `users`

- Engine/Options: `InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci`
- Số cột: **16**
- Seed/import rows ước tính: **0**

| Cột | Kiểu / ràng buộc | Ghi chú nhanh |
|---|---|---|
| `id` | `bigint(20) UNSIGNED NOT NULL` | required, unsigned |
| `full_name` | `varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL COMMENT 'Họ tên người dùng'` | required |
| `email` | `varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL COMMENT 'Email đăng nhập` | required |
| `password_hash` | `varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL COMMENT 'Hash mật khẩu; null nếu chỉ đăng nhập OAuth'` | nullable, default NULL |
| `phone` | `varchar(20) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL COMMENT 'Số điện thoại người dùng'` | nullable, default NULL |
| `oauth_account_login` | `varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL COMMENT 'OAuth account login ID nếu đăng nhập bằng tài khoản bên thứ ba như Google'` | nullable, default NULL |
| `role` | `varchar(30) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'learner' COMMENT 'admin=quản trị viên` | required, default 'learner' |
| `status` | `varchar(30) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'active' COMMENT 'active=hoạt động` | required, default 'active' |
| `email_verified_at` | `timestamp NULL DEFAULT NULL COMMENT 'Thời điểm xác thực email'` | nullable, default NULL |
| `last_login_at` | `timestamp NULL DEFAULT NULL COMMENT 'Lần đăng nhập gần nhất'` | nullable, default NULL |
| `locked` | `tinyint(1) NOT NULL DEFAULT 0 COMMENT 'Cờ khóa nhanh tài khoản'` | required, default 0 |
| `locked_reason` | `text CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL COMMENT 'Lý do khóa tài khoản'` | nullable, default NULL |
| `password_reset` | `varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL COMMENT 'Token/hash reset mật khẩu nếu gộp vào users'` | nullable, default NULL |
| `created_at` | `timestamp NULL DEFAULT current_timestamp()` | default current_timestamp() |
| `updated_at` | `timestamp NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()` | default current_timestamp() |
| `deleted_at` | `timestamp NULL DEFAULT NULL COMMENT 'Soft delete user'` | nullable, default NULL, soft delete |

- Primary key: `id`
- Unique:
  - `uq_users_email`: `email`
  - `uq_users_oauth_account_login`: `oauth_account_login`
- Index:
  - `idx_users_role`: `role`
- Ràng buộc khác:
  - `duy nhất'`
  - `instructor=giảng viên`
  - `learner=học viên'`
  - `inactive=tạm ngưng/chưa kích hoạt`
  - `locked=bị khóa'`

### `video_progress`

- Engine/Options: `InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci`
- Số cột: **6**
- Seed/import rows ước tính: **0**

| Cột | Kiểu / ràng buộc | Ghi chú nhanh |
|---|---|---|
| `id` | `bigint(20) UNSIGNED NOT NULL` | required, unsigned |
| `lesson_id` | `bigint(20) UNSIGNED NOT NULL` | required, unsigned, khóa ngoại tiềm năng |
| `user_id` | `bigint(20) UNSIGNED NOT NULL` | required, unsigned, khóa ngoại tiềm năng |
| `current_second` | `int(10) UNSIGNED NOT NULL DEFAULT 0` | required, default 0, unsigned |
| `created_at` | `timestamp NULL DEFAULT current_timestamp()` | default current_timestamp() |
| `updated_at` | `timestamp NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()` | default current_timestamp() |

- Primary key: `id`
- Unique:
  - `uq_video_progress_user_lesson`: `user_id, lesson_id`
- Index:
  - `idx_video_progress_lesson_id`: `lesson_id`
  - `idx_video_progress_user_id`: `user_id`
- Foreign keys:
  - `fk_video_progress_lesson`: `lesson_id` -> `lessons`(`id`) ON DELETE CASCADE ON UPDATE CASCADE

### `wishlist`

- Engine/Options: `InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci`
- Số cột: **4**
- Seed/import rows ước tính: **0**

| Cột | Kiểu / ràng buộc | Ghi chú nhanh |
|---|---|---|
| `id` | `bigint(20) UNSIGNED NOT NULL` | required, unsigned |
| `user_id` | `bigint(20) UNSIGNED NOT NULL` | required, unsigned, khóa ngoại tiềm năng |
| `course_id` | `bigint(20) UNSIGNED NOT NULL` | required, unsigned, khóa ngoại tiềm năng |
| `created_at` | `timestamp NULL DEFAULT current_timestamp()` | default current_timestamp() |

- Primary key: `id`
- Unique:
  - `uq_wishlist_user_course`: `user_id, course_id`
- Index:
  - `idx_wishlist_user_id`: `user_id`
  - `idx_wishlist_course_id`: `course_id`
- Foreign keys:
  - `fk_wishlist_course`: `course_id` -> `courses`(`id`) ON DELETE CASCADE ON UPDATE CASCADE

### `withdraw_requests`

- Engine/Options: `InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci`
- Số cột: **14**
- Seed/import rows ước tính: **0**

| Cột | Kiểu / ràng buộc | Ghi chú nhanh |
|---|---|---|
| `id` | `bigint(20) UNSIGNED NOT NULL` | required, unsigned |
| `user_id` | `bigint(20) UNSIGNED NOT NULL` | required, unsigned, khóa ngoại tiềm năng |
| `payout_account_id` | `bigint(20) UNSIGNED NOT NULL` | required, unsigned, khóa ngoại tiềm năng |
| `amount` | `decimal(12,2) NOT NULL` | required |
| `status` | `varchar(30) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'pending' COMMENT 'pending/approved/rejected/paid/cancelled'` | required, default 'pending' |
| `requested_at` | `timestamp NOT NULL DEFAULT current_timestamp()` | required, default current_timestamp() |
| `approved_at` | `timestamp NULL DEFAULT NULL` | nullable, default NULL |
| `paid_at` | `timestamp NULL DEFAULT NULL` | nullable, default NULL |
| `rejected_reason` | `text CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL` | nullable, default NULL |
| `provider_payout_id` | `varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL` | nullable, default NULL, khóa ngoại tiềm năng |
| `account_number_snapshot` | `varchar(100) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL COMMENT 'Snapshot STK tại lúc yêu cầu rút'` | required |
| `account_name_snapshot` | `varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL COMMENT 'Snapshot tên chủ TK tại lúc yêu cầu rút'` | required |
| `created_at` | `timestamp NULL DEFAULT current_timestamp()` | default current_timestamp() |
| `updated_at` | `timestamp NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()` | default current_timestamp() |

- Primary key: `id`
- Index:
  - `idx_withdraw_requests_user_id`: `user_id`
  - `idx_withdraw_requests_payout_account_id`: `payout_account_id`
  - `idx_withdraw_requests_status`: `status`
- Foreign keys:
  - `fk_withdraw_requests_payout_account`: `payout_account_id` -> `payout_accounts`(`id`) ON UPDATE CASCADE

## 5. Ghi chú triển khai API giảng viên dựa trên DB

Các API giảng viên cần luôn kiểm tra quyền sở hữu:

```txt
courses.instructor_id = user đăng nhập
revenues.instructor_id = user đăng nhập
withdraw_requests.user_id = user đăng nhập
payout_accounts.user_id = user đăng nhập
coupons.user_id = user đăng nhập hoặc coupon.course_id thuộc course của user
comments phải thuộc lesson/course của user
```

Không làm trong phạm vi hiện tại:

```txt
quiz
certificate
certificates
quiz_attempts
quiz_questions
quiz_options
```

Công thức chia lợi nhuận đề xuất:

```txt
gross_amount = số tiền học viên thật sự thanh toán
instructor_amount = gross_amount * 70%
platform_fee_amount = gross_amount - instructor_amount
revenue.status = available sau khi thanh toán thành công
```

Công thức số dư rút tiền:

```txt
withdrawable_balance =
SUM(revenues.instructor_amount WHERE status = available)
-
SUM(withdraw_requests.amount WHERE status IN pending, approved)
```
