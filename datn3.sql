-- phpMyAdmin SQL Dump
-- version 5.2.0
-- https://www.phpmyadmin.net/
--
-- Host: localhost:3306
-- Generation Time: Aug 01, 2026 at 04:46 AM
-- Server version: 8.0.30
-- PHP Version: 8.3.31

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Database: `datn`
--

-- --------------------------------------------------------

--
-- Table structure for table `ai_lesson_summaries`
--

CREATE TABLE `ai_lesson_summaries` (
  `id` bigint UNSIGNED NOT NULL,
  `lesson_id` bigint UNSIGNED NOT NULL COMMENT 'Lesson được AI tóm tắt',
  `course_id` bigint UNSIGNED NOT NULL COMMENT 'Course chứa lesson',
  `summary` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL COMMENT 'Nội dung tóm tắt do AI sinh ra',
  `key_points` json DEFAULT NULL COMMENT 'Danh sách ý chính dạng JSON nếu có',
  `summary_type` varchar(30) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'short' COMMENT 'short/detailed/bullet',
  `language` varchar(10) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'vi' COMMENT 'Ngôn ngữ tóm tắt, ví dụ vi/en',
  `source_content_hash` varchar(64) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL COMMENT 'SHA-256 hash của nội dung lesson.content tại thời điểm tạo summary',
  `model_name` varchar(100) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL COMMENT 'Tên model AI đã dùng',
  `generated_by_user_id` bigint UNSIGNED DEFAULT NULL COMMENT 'User kích hoạt tạo summary; null nếu system tạo',
  `generated_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP COMMENT 'Thời điểm sinh summary',
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_vietnamese_ci;

--
-- Dumping data for table `ai_lesson_summaries`
--

INSERT INTO `ai_lesson_summaries` (`id`, `lesson_id`, `course_id`, `summary`, `key_points`, `summary_type`, `language`, `source_content_hash`, `model_name`, `generated_by_user_id`, `generated_at`, `created_at`, `updated_at`) VALUES
(1, 2, 1, 'Bài học giải thích vai trò của Repository, Service và Resource trong Laravel API. Controller chỉ điều phối, Service xử lý nghiệp vụ, Repository xử lý query và Resource chuẩn hóa response.', '[\"Controller không chứa business logic chính\", \"Service xử lý nghiệp vụ\", \"Repository xử lý query\", \"Resource che field nhạy cảm\"]', 'short', 'vi', '6cc3964de4712ab85707c7db60b4e2dcd1451576ca1f8f66aba3fb65af78fb09', 'demo-ai-summary', 4, '2026-06-22 14:30:00', '2026-06-22 14:30:00', '2026-06-22 14:30:00'),
(2, 3, 1, 'Custom session sử dụng bảng sessions để lưu refresh_token_hash, expires_at và revoked_at. Middleware cần từ chối session hết hạn hoặc đã revoke, đồng thời kiểm tra user active.', '[\"Không lưu refresh token thô\", \"expires_at xác định hết hạn\", \"revoked_at xác định phiên bị thu hồi\", \"active.user chặn locked/inactive\"]', 'bullet', 'vi', 'e6449281ddd8ee6ea59753cfd3e43971b9df3a5568a7032293cf06bbfa6600e7', 'demo-ai-summary', 4, '2026-06-22 14:35:00', '2026-06-22 14:35:00', '2026-06-22 14:35:00'),
(3, 11, 6, 'Khi dùng AI để tóm tắt bài học, hệ thống chỉ gửi nội dung cần xử lý và không gửi token, mật khẩu, secret hoặc dữ liệu cá nhân nhạy cảm.', '[\"Giới hạn input/token\", \"Không gửi secret\", \"Có fallback khi provider lỗi\", \"Cache bằng source_content_hash\"]', 'short', 'vi', 'a69f6c85f9a54e27c57ad84c4ae30bb453a88e120ca77fdd2b5b27755c0f7d40', 'demo-ai-summary', 3, '2026-06-18 05:00:00', '2026-06-18 05:00:00', '2026-06-18 05:00:00');

-- --------------------------------------------------------

--
-- Table structure for table `banners`
--

CREATE TABLE `banners` (
  `id` bigint UNSIGNED NOT NULL,
  `title` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `image_url` varchar(500) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `target_url` varchar(500) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `position` varchar(50) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'home',
  `sort_order` int UNSIGNED NOT NULL DEFAULT '0',
  `start_at` timestamp NULL DEFAULT NULL,
  `end_at` timestamp NULL DEFAULT NULL,
  `status` varchar(30) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'inactive' COMMENT 'active=đang hiển thị, inactive=đang ẩn',
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  `deleted_at` timestamp NULL DEFAULT NULL COMMENT 'Soft delete banner'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_vietnamese_ci;

--
-- Dumping data for table `banners`
--

INSERT INTO `banners` (`id`, `title`, `image_url`, `target_url`, `position`, `sort_order`, `start_at`, `end_at`, `status`, `created_at`, `updated_at`, `deleted_at`) VALUES
(1, 'Học Laravel REST API cho đồ án tốt nghiệp', '/demo/banners/banner-laravel-api.jpg', '/courses/laravel-rest-api-tu-co-ban-den-trien-khai', 'home', 1, '2025-12-31 17:00:00', '2026-12-31 16:59:59', 'active', '2026-01-01 01:00:00', '2026-06-01 01:00:00', NULL),
(2, 'AI hỗ trợ học tập cá nhân hóa', '/demo/banners/banner-ai-learning.jpg', '/courses/ai-ung-dung-cho-hoc-tap-ca-nhan-hoa', 'home', 2, '2025-12-31 17:00:00', '2026-12-31 16:59:59', 'active', '2026-01-01 01:10:00', '2026-06-01 01:10:00', NULL),
(3, 'Banner inactive demo', '/demo/banners/banner-inactive.jpg', NULL, 'home', 3, '2025-12-31 17:00:00', '2026-12-31 16:59:59', 'inactive', '2026-01-01 01:20:00', '2026-06-01 01:20:00', NULL);

-- --------------------------------------------------------

--
-- Table structure for table `categories`
--

CREATE TABLE `categories` (
  `id` bigint UNSIGNED NOT NULL,
  `parent_id` bigint UNSIGNED DEFAULT NULL COMMENT 'Danh mục cha; null nếu là danh mục gốc',
  `name` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `slug` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `description` text CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci,
  `sort_order` int UNSIGNED NOT NULL DEFAULT '0',
  `status` varchar(30) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'active' COMMENT 'active=hiển thị, inactive=ẩn',
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  `deleted_at` timestamp NULL DEFAULT NULL COMMENT 'Soft delete category'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_vietnamese_ci;

--
-- Dumping data for table `categories`
--

INSERT INTO `categories` (`id`, `parent_id`, `name`, `slug`, `description`, `sort_order`, `status`, `created_at`, `updated_at`, `deleted_at`) VALUES
(1, NULL, 'Lập trình', 'lap-trinh', 'Các khóa học lập trình từ cơ bản đến nâng cao.', 1, 'active', '2026-01-03 01:00:00', '2026-01-03 01:00:00', NULL),
(2, 1, 'Web Development', 'web-development', 'Frontend, backend và full-stack web.', 1, 'active', '2026-01-03 01:05:00', '2026-01-03 01:05:00', NULL),
(3, 1, 'Backend', 'backend', 'API, cơ sở dữ liệu, bảo mật backend.', 2, 'active', '2026-01-03 01:10:00', '2026-07-31 12:06:55', NULL),
(4, 1, 'Frontend', 'frontend', 'Giao diện người dùng hiện đại.', 3, 'active', '2026-01-03 01:15:00', '2026-01-03 01:15:00', NULL),
(5, NULL, 'AI và Dữ liệu', 'ai-va-du-lieu', 'AI ứng dụng, phân tích dữ liệu và tự động hóa.', 2, 'active', '2026-01-03 01:20:00', '2026-01-03 01:20:00', NULL),
(6, NULL, 'DevOps', 'devops', 'Triển khai, Docker, CI/CD và vận hành.', 3, 'active', '2026-01-03 01:25:00', '2026-01-03 01:25:00', NULL),
(7, NULL, 'Kinh doanh số', 'kinh-doanh-so', 'Danh mục inactive để test filter public.', 4, 'inactive', '2026-01-03 01:30:00', '2026-01-03 01:30:00', NULL);

-- --------------------------------------------------------

--
-- Table structure for table `certificates`
--

CREATE TABLE `certificates` (
  `id` bigint UNSIGNED NOT NULL,
  `user_id` bigint UNSIGNED NOT NULL COMMENT 'Học viên được cấp chứng chỉ',
  `course_id` bigint UNSIGNED NOT NULL COMMENT 'Khóa học được cấp chứng chỉ',
  `enrollment_id` bigint UNSIGNED NOT NULL COMMENT 'Enrollment đã hoàn thành, mỗi enrollment chỉ có một certificate',
  `certificate_code` varchar(100) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL COMMENT 'Mã chứng chỉ duy nhất dùng để xác thực',
  `certificate_url` varchar(500) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL COMMENT 'URL file chứng chỉ nếu đã render PDF/image',
  `issued_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP COMMENT 'Thời điểm cấp chứng chỉ',
  `status` varchar(30) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'active' COMMENT 'active=hiệu lực, revoked=thu hồi',
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  `deleted_at` timestamp NULL DEFAULT NULL COMMENT 'Soft delete certificate'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_vietnamese_ci;

--
-- Dumping data for table `certificates`
--

INSERT INTO `certificates` (`id`, `user_id`, `course_id`, `enrollment_id`, `certificate_code`, `certificate_url`, `issued_at`, `status`, `created_at`, `updated_at`, `deleted_at`) VALUES
(1, 6, 1, 2, 'MH-CERT-2026-0001', '/demo/certificates/MH-CERT-2026-0001.pdf', '2026-05-03 02:05:00', 'active', '2026-05-03 02:05:00', '2026-05-03 02:05:00', NULL);

-- --------------------------------------------------------

--
-- Table structure for table `comments`
--

CREATE TABLE `comments` (
  `id` bigint UNSIGNED NOT NULL,
  `parent_id` bigint UNSIGNED DEFAULT NULL COMMENT 'Bình luận cha nếu là reply',
  `user_id` bigint UNSIGNED NOT NULL,
  `order_id` bigint UNSIGNED DEFAULT NULL COMMENT 'Dùng xác minh user đã mua khóa học chứa lesson',
  `lesson_id` bigint UNSIGNED NOT NULL,
  `content` text CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `status` varchar(30) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'visible' COMMENT 'visible/hidden/deleted; đã bỏ pending theo chốt',
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_vietnamese_ci;

--
-- Dumping data for table `comments`
--

INSERT INTO `comments` (`id`, `parent_id`, `user_id`, `order_id`, `lesson_id`, `content`, `status`, `created_at`, `updated_at`) VALUES
(1, NULL, 4, 1, 3, 'Em muốn hỏi refresh_token_hash nên so sánh trực tiếp hay dùng hash_equals?', 'visible', '2026-06-22 13:40:00', '2026-06-22 13:40:00'),
(2, 1, 2, NULL, 3, 'Nên hash token client gửi lên rồi dùng so sánh an toàn, không lưu refresh token thô trong DB.', 'visible', '2026-06-22 14:00:00', '2026-06-22 14:00:00'),
(3, NULL, 6, 6, 6, 'Flow paid -> enrollment -> revenue rất hữu ích khi test payment.', 'visible', '2026-05-03 02:10:00', '2026-05-03 02:10:00'),
(4, NULL, 4, 1, 2, 'Comment hidden dùng để test moderation.', 'hidden', '2026-06-20 02:00:00', '2026-06-20 02:10:00');

-- --------------------------------------------------------

--
-- Table structure for table `coupons`
--

CREATE TABLE `coupons` (
  `id` bigint UNSIGNED NOT NULL,
  `user_id` bigint UNSIGNED DEFAULT NULL COMMENT 'Người tạo coupon; null nếu hệ thống tạo',
  `course_id` bigint UNSIGNED DEFAULT NULL COMMENT 'Null nếu coupon áp dụng toàn hệ thống',
  `code` varchar(50) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `name` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `description` text CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci,
  `discount_type` varchar(30) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL COMMENT 'percent/fixed',
  `discount_value` decimal(12,2) NOT NULL DEFAULT '0.00',
  `max_order_amount` decimal(12,2) DEFAULT NULL COMMENT 'Mức giảm tối đa nếu discount_type=percent',
  `usage_limit` int UNSIGNED DEFAULT NULL COMMENT 'Số lượt dùng tối đa; null = không giới hạn',
  `used_count` int UNSIGNED NOT NULL DEFAULT '0',
  `start_at` timestamp NULL DEFAULT NULL,
  `end_at` timestamp NULL DEFAULT NULL,
  `status` varchar(30) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'active' COMMENT 'active/inactive/expired/used_up',
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  `deleted_at` timestamp NULL DEFAULT NULL COMMENT 'Soft delete coupon'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_vietnamese_ci;

--
-- Dumping data for table `coupons`
--

INSERT INTO `coupons` (`id`, `user_id`, `course_id`, `code`, `name`, `description`, `discount_type`, `discount_value`, `max_order_amount`, `usage_limit`, `used_count`, `start_at`, `end_at`, `status`, `created_at`, `updated_at`, `deleted_at`) VALUES
(1, 1, NULL, 'WELCOME100', 'Giảm 100K chào mừng', 'Mã giảm 100.000đ cho đơn hàng đầu tiên.', 'fixed', '100000.00', NULL, 100, 2, '2025-12-31 17:00:00', '2026-12-31 16:59:59', 'active', '2026-01-01 03:00:00', '2026-06-01 03:00:00', NULL),
(2, 1, NULL, 'GLOBAL10', 'Giảm 10% toàn hệ thống', 'Mã giảm 10%, tối đa 50.000đ.', 'percent', '10.00', '50000.00', NULL, 1, '2025-12-31 17:00:00', '2026-12-31 16:59:59', 'active', '2026-01-01 03:05:00', '2026-06-01 03:00:00', NULL),
(3, 1, NULL, 'OLD50', 'Mã đã hết hạn', 'Dùng để test coupon expired.', 'fixed', '50000.00', NULL, 10, 0, '2025-12-31 17:00:00', '2026-01-31 17:00:00', 'expired', '2026-01-01 03:10:00', '2026-01-31 17:00:00', NULL),
(4, 1, NULL, 'OFFLINE20', 'Mã inactive', 'Dùng để test coupon inactive.', 'percent', '20.00', '80000.00', 50, 0, '2025-12-31 17:00:00', '2026-12-31 16:59:59', 'inactive', '2026-01-01 03:15:00', '2026-06-01 03:00:00', NULL),
(5, 1, NULL, 'FULLUSED', 'Mã hết lượt', 'Dùng để test coupon used_up.', 'fixed', '30000.00', NULL, 10, 10, '2025-12-31 17:00:00', '2026-12-31 16:59:59', 'used_up', '2026-01-01 03:20:00', '2026-06-01 03:00:00', NULL),
(6, 2, 7, 'FREEGIT', 'Miễn phí Git', 'Mã course-specific cho khóa Git & GitHub.', 'fixed', '299000.00', NULL, 100, 0, '2025-12-31 17:00:00', '2026-12-31 16:59:59', 'active', '2026-01-01 03:25:00', '2026-06-01 03:00:00', NULL),
(7, 2, 1, 'LARAVEL50', 'Laravel giảm 50%', 'Mã chỉ áp dụng cho khóa Laravel REST API.', 'percent', '50.00', '150000.00', 20, 1, '2026-05-31 17:00:00', '2026-12-31 16:59:59', 'active', '2026-06-01 03:30:00', '2026-06-01 03:30:00', NULL);

-- --------------------------------------------------------

--
-- Table structure for table `courses`
--

CREATE TABLE `courses` (
  `id` bigint UNSIGNED NOT NULL,
  `instructor_id` bigint UNSIGNED NOT NULL COMMENT 'User đóng vai trò giảng viên',
  `title` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `slug` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `short_description` varchar(500) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `description` text CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci,
  `thumbnail_url` varchar(500) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `intro_video_url` varchar(500) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `price` decimal(12,2) NOT NULL DEFAULT '0.00' COMMENT 'Giá gốc khóa học',
  `sale_price` decimal(12,2) DEFAULT NULL COMMENT 'Giá khuyến mãi; null nếu không sale',
  `level` varchar(30) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'beginner' COMMENT 'beginner/intermediate/advanced/all_levels',
  `language` varchar(50) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'vi',
  `requirements` text CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci COMMENT 'Yêu cầu đầu vào',
  `outcomes` text CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci COMMENT 'Kết quả đạt được sau khóa học',
  `status` varchar(30) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'draft' COMMENT 'draft/pending_review/approved/rejected/published/hidden',
  `is_featured` tinyint(1) NOT NULL DEFAULT '0',
  `total_duration_seconds` int UNSIGNED NOT NULL DEFAULT '0',
  `published_at` timestamp NULL DEFAULT NULL,
  `admin_reject_reason` text CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci COMMENT 'Lý do admin từ chối khi status=rejected',
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  `deleted_at` timestamp NULL DEFAULT NULL COMMENT 'Soft delete course'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_vietnamese_ci;

--
-- Dumping data for table `courses`
--

INSERT INTO `courses` (`id`, `instructor_id`, `title`, `slug`, `short_description`, `description`, `thumbnail_url`, `intro_video_url`, `price`, `sale_price`, `level`, `language`, `requirements`, `outcomes`, `status`, `is_featured`, `total_duration_seconds`, `published_at`, `admin_reject_reason`, `created_at`, `updated_at`, `deleted_at`) VALUES
(1, 2, 'Laravel REST API từ cơ bản đến triển khai', 'laravel-rest-api-tu-co-ban-den-trien-khai', 'Xây dựng REST API Laravel theo Repository/Service, auth session custom và payment flow.', 'Khóa học thực chiến dành cho sinh viên làm đồ án và junior backend muốn nắm quy trình xây dựng API e-learning bằng Laravel.', '/demo/courses/laravel-rest-api.jpg', '/demo/videos/laravel-intro.mp4', '499000.00', '299000.00', 'beginner', 'vi', 'Biết PHP cơ bản, đã cài Laragon hoặc môi trường PHP/MySQL.', 'Thiết kế API chuẩn, xử lý auth, payment, learning progress và test bằng Postman/PowerShell.', 'published', 1, 12600, '2026-02-01 02:00:00', NULL, '2026-01-10 01:00:00', '2026-06-20 03:00:00', NULL),
(2, 2, 'PHP & MySQL nền tảng cho Backend', 'php-mysql-nen-tang-cho-backend', 'Nắm nền tảng PHP, MySQL, CRUD, transaction và thiết kế database.', 'Khóa học nền tảng giúp người học hiểu cách dữ liệu đi từ form đến database và quay về API response.', '/demo/courses/php-mysql.jpg', '/demo/videos/php-mysql-intro.mp4', '399000.00', NULL, 'beginner', 'vi', 'Biết HTML cơ bản là lợi thế.', 'Viết CRUD an toàn, hiểu khóa chính/khóa ngoại, transaction và validate dữ liệu.', 'published', 1, 9600, '2026-02-10 02:00:00', NULL, '2026-01-12 01:00:00', '2026-06-15 03:00:00', NULL),
(3, 3, 'React Frontend cho trang E-learning', 'react-frontend-cho-trang-e-learning', 'Xây giao diện catalog, course detail, cart và learning dashboard.', 'Khóa học giúp frontend developer xây trang e-learning có component, state, call API và UI/UX rõ ràng.', '/demo/courses/react-elearning.jpg', '/demo/videos/react-intro.mp4', '599000.00', '449000.00', 'intermediate', 'vi', 'Biết JavaScript ES6 và HTML/CSS.', 'Tạo SPA học trực tuyến, tích hợp API và xử lý trạng thái loading/error.', 'published', 1, 14400, '2026-03-01 02:00:00', NULL, '2026-01-20 01:00:00', '2026-06-16 03:00:00', NULL),
(4, 2, 'NodeJS Hidden Draft API', 'nodejs-hidden-draft-api', 'Course draft dùng để test chặn mua khóa chưa public.', 'Dữ liệu demo trạng thái draft, không hiển thị cho learner public.', '/demo/courses/node-draft.jpg', NULL, '350000.00', NULL, 'beginner', 'vi', 'JavaScript cơ bản.', 'Không dùng để trình chiếu public.', 'draft', 0, 3600, NULL, NULL, '2026-02-01 01:00:00', '2026-02-01 01:00:00', NULL),
(5, 2, 'NodeJS Hidden Course', 'nodejs-hidden-course', 'Course hidden dùng để test filter và authorization.', 'Dữ liệu demo trạng thái hidden, không cho learner mua/thêm wishlist nếu API public lọc status.', '/demo/courses/node-hidden.jpg', NULL, '450000.00', '299000.00', 'intermediate', 'vi', 'JavaScript cơ bản.', 'Không dùng để trình chiếu public.', 'hidden', 0, 4800, '2026-02-15 02:00:00', NULL, '2026-02-01 01:15:00', '2026-03-01 01:15:00', NULL),
(6, 3, 'AI ứng dụng cho học tập cá nhân hóa', 'ai-ung-dung-cho-hoc-tap-ca-nhan-hoa', 'Demo các tính năng AI: tóm tắt bài học, gợi ý khóa học, phân tích điểm yếu.', 'Khóa học mô phỏng cách tích hợp AI vào hệ thống học trực tuyến mà không gửi dữ liệu nhạy cảm.', '/demo/courses/ai-learning.jpg', '/demo/videos/ai-intro.mp4', '699000.00', '499000.00', 'all_levels', 'vi', 'Có kiến thức web/API cơ bản.', 'Hiểu workflow AI summary, draft quiz, recommendation và cảnh báo rủi ro bỏ học.', 'published', 1, 10800, '2026-04-01 02:00:00', NULL, '2026-02-15 01:00:00', '2026-06-18 03:00:00', NULL),
(7, 2, 'Git & GitHub cho sinh viên làm đồ án', 'git-github-cho-sinh-vien-lam-do-an', 'Khóa miễn phí giúp quản lý source code, branch, commit và pull request.', 'Nội dung ngắn gọn để team đồ án phối hợp code backend/frontend hiệu quả.', '/demo/courses/git-github.jpg', '/demo/videos/git-intro.mp4', '0.00', '0.00', 'beginner', 'vi', 'Có máy tính và tài khoản GitHub.', 'Biết clone, branch, commit, push, pull request và xử lý conflict cơ bản.', 'published', 0, 5400, '2026-01-25 02:00:00', NULL, '2026-01-15 01:00:00', '2026-06-01 03:00:00', NULL),
(8, 3, 'Advanced Laravel Architecture', 'advanced-laravel-architecture', 'Course pending review dùng test checklist/review/publish flow.', 'Dữ liệu demo trạng thái pending_review để instructor/admin test kiểm duyệt.', '/demo/courses/advanced-laravel.jpg', NULL, '899000.00', '699000.00', 'advanced', 'vi', 'Đã làm Laravel API thực tế.', 'Tách module, tối ưu service/repository và audit business flow.', 'pending_review', 0, 7200, NULL, NULL, '2026-06-01 01:00:00', '2026-06-18 01:00:00', NULL),
(17, 33, 'VIDEO SEC 01 02 Private Course', 'video-sec-01-02-private-course', 'Course seed for VIDEO-SEC-01 and VIDEO-SEC-02.', 'Seed course dùng để test ẩn raw video_url và lưu video private storage.', NULL, NULL, '199000.00', '99000.00', 'beginner', 'vi', NULL, NULL, 'published', 0, 3600, '2026-06-29 19:40:49', NULL, '2026-06-29 19:40:49', '2026-06-29 19:40:49', NULL),
(18, 34, 'VIDEO SEC Other Instructor Course', 'video-sec-01-02-other-instructor-course', 'Course seed owned by instructor 2.', 'Dùng để test instructor 1 không được upload video bài học của instructor 2.', NULL, NULL, '199000.00', NULL, 'beginner', 'vi', NULL, NULL, 'published', 0, 1800, '2026-06-29 19:40:49', NULL, '2026-06-29 19:40:49', '2026-06-29 19:40:49', NULL),
(19, 33, 'VIDEO SEC 03 04 05 Course', 'video-sec-03-04-05-course', 'Course seed for signed URL, stream, and watermark info.', 'Seed course dùng để test VIDEO-SEC-03/04/05.', NULL, NULL, '199000.00', '99000.00', 'beginner', 'vi', NULL, NULL, 'published', 0, 3600, '2026-06-29 20:08:33', NULL, '2026-06-29 20:08:33', '2026-06-29 20:08:33', NULL);

-- --------------------------------------------------------

--
-- Table structure for table `course_categories`
--

CREATE TABLE `course_categories` (
  `category_id` bigint UNSIGNED NOT NULL,
  `course_id` bigint UNSIGNED NOT NULL,
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `course_categories`
--

INSERT INTO `course_categories` (`category_id`, `course_id`, `created_at`) VALUES
(2, 1, '2026-02-01 02:05:00'),
(2, 2, '2026-02-10 02:05:00'),
(2, 3, '2026-03-01 02:05:00'),
(3, 1, '2026-02-01 02:05:00'),
(3, 2, '2026-02-10 02:05:00'),
(3, 4, '2026-02-01 01:05:00'),
(3, 5, '2026-02-01 01:20:00'),
(3, 8, '2026-06-01 01:05:00'),
(4, 3, '2026-03-01 02:05:00'),
(5, 6, '2026-04-01 02:05:00'),
(6, 7, '2026-01-25 02:05:00');

-- --------------------------------------------------------

--
-- Table structure for table `course_faqs`
--

CREATE TABLE `course_faqs` (
  `faq_id` bigint UNSIGNED NOT NULL,
  `course_id` bigint UNSIGNED NOT NULL,
  `sort_order` int UNSIGNED NOT NULL DEFAULT '0',
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  `deleted_at` timestamp NULL DEFAULT NULL COMMENT 'Soft delete liên kết FAQ-course để còn đối chất nếu có tranh chấp'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `course_faqs`
--

INSERT INTO `course_faqs` (`faq_id`, `course_id`, `sort_order`, `created_at`, `deleted_at`) VALUES
(1, 1, 1, '2026-02-01 02:00:00', NULL),
(1, 6, 1, '2026-04-01 02:00:00', NULL),
(2, 1, 2, '2026-02-01 02:05:00', NULL),
(3, 1, 3, '2026-02-01 02:10:00', NULL);

-- --------------------------------------------------------

--
-- Table structure for table `course_reviews`
--

CREATE TABLE `course_reviews` (
  `id` bigint UNSIGNED NOT NULL,
  `order_id` bigint UNSIGNED NOT NULL COMMENT 'Chứng minh người review đã mua khóa',
  `rating` tinyint UNSIGNED NOT NULL,
  `comment` text CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci,
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  `deleted_at` timestamp NULL DEFAULT NULL COMMENT 'Soft delete review nếu cần ẩn nhưng vẫn giữ lịch sử'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_vietnamese_ci;

--
-- Dumping data for table `course_reviews`
--

INSERT INTO `course_reviews` (`id`, `order_id`, `rating`, `comment`, `created_at`, `updated_at`, `deleted_at`) VALUES
(1, 1, 5, 'Khóa Laravel rất sát với đồ án, phần payment và custom session dễ hiểu.', '2026-06-22 02:00:00', '2026-06-22 02:00:00', NULL),
(2, 6, 4, 'Nội dung đầy đủ, có thể thêm nhiều ví dụ test API hơn.', '2026-05-04 03:00:00', '2026-05-04 03:00:00', NULL),
(3, 7, 4, 'Phần transaction MySQL giúp mình hiểu rõ xử lý dữ liệu tài chính.', '2026-06-22 03:00:00', '2026-06-22 03:00:00', NULL);

-- --------------------------------------------------------

--
-- Table structure for table `course_sections`
--

CREATE TABLE `course_sections` (
  `id` bigint UNSIGNED NOT NULL,
  `course_id` bigint UNSIGNED NOT NULL,
  `title` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `description` text CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci,
  `sort_order` int UNSIGNED NOT NULL DEFAULT '0',
  `status` varchar(30) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'draft' COMMENT 'draft/published/hidden',
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  `deleted_at` timestamp NULL DEFAULT NULL COMMENT 'Soft delete course section'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_vietnamese_ci;

--
-- Dumping data for table `course_sections`
--

INSERT INTO `course_sections` (`id`, `course_id`, `title`, `description`, `sort_order`, `status`, `created_at`, `updated_at`, `deleted_at`) VALUES
(1, 1, 'Khởi động Laravel API', 'Cài đặt project, hiểu route, controller, request và response.', 1, 'published', '2026-01-10 02:00:00', '2026-06-20 03:00:00', NULL),
(2, 1, 'Auth session custom', 'Login, logout, refresh token, quản lý session và thiết bị.', 2, 'published', '2026-01-10 02:10:00', '2026-06-20 03:00:00', NULL),
(3, 1, 'Payment và Learning flow', 'Order, coupon, enrollment, tiến độ học và quiz.', 3, 'published', '2026-01-10 02:20:00', '2026-06-20 03:00:00', NULL),
(4, 1, 'Nội dung ẩn demo', 'Section hidden để test filter.', 4, 'hidden', '2026-01-10 02:30:00', '2026-06-20 03:00:00', NULL),
(5, 2, 'PHP nền tảng', 'Biến, hàm, mảng, request/response cơ bản.', 1, 'published', '2026-01-12 02:00:00', '2026-06-15 03:00:00', NULL),
(6, 2, 'MySQL thực chiến', 'CRUD, khóa ngoại, transaction.', 2, 'published', '2026-01-12 02:10:00', '2026-06-15 03:00:00', NULL),
(7, 3, 'React UI Foundation', 'Component, props, state và layout.', 1, 'published', '2026-01-20 02:00:00', '2026-06-16 03:00:00', NULL),
(8, 3, 'Tích hợp API e-learning', 'Call API, auth token và error state.', 2, 'published', '2026-01-20 02:10:00', '2026-06-16 03:00:00', NULL),
(9, 6, 'AI trong sản phẩm học tập', 'Tóm tắt bài, tạo quiz nháp và phân tích điểm yếu.', 1, 'published', '2026-02-15 02:00:00', '2026-06-18 03:00:00', NULL),
(10, 7, 'Git cơ bản', 'Commit, branch và pull request.', 1, 'published', '2026-01-15 02:00:00', '2026-06-01 03:00:00', NULL),
(11, 4, 'Draft section', 'Dữ liệu draft.', 1, 'draft', '2026-02-01 02:00:00', '2026-02-01 02:00:00', NULL),
(12, 8, 'Kiến trúc nâng cao', 'Service, repository và module boundary.', 1, 'published', '2026-06-01 02:00:00', '2026-06-18 01:00:00', NULL),
(21, 17, 'VIDEO SEC Section 01', 'Section dùng để test bảo mật video.', 1, 'published', '2026-06-29 19:40:49', '2026-06-29 19:40:49', NULL),
(22, 18, 'VIDEO SEC Other Instructor Section', 'Section thuộc instructor 2.', 1, 'published', '2026-06-29 19:40:49', '2026-06-29 19:40:49', NULL),
(23, 19, 'VIDEO SEC 03 04 05 Section', 'Section dùng để test signed video stream.', 1, 'published', '2026-06-29 20:08:33', '2026-06-29 20:08:33', NULL);

-- --------------------------------------------------------

--
-- Table structure for table `enrollments`
--

CREATE TABLE `enrollments` (
  `id` bigint UNSIGNED NOT NULL,
  `user_id` bigint UNSIGNED NOT NULL,
  `course_id` bigint UNSIGNED NOT NULL,
  `order_id` bigint UNSIGNED NOT NULL COMMENT 'Ghi danh được sinh từ order đã paid, kể cả coupon 0đ',
  `status` varchar(30) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'active' COMMENT 'active=đang học, completed=đã hoàn thành; đã bỏ expired/cancelled theo chốt',
  `progress_percent` decimal(5,2) NOT NULL DEFAULT '0.00',
  `enrolled_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `completed_at` timestamp NULL DEFAULT NULL,
  `last_accessed_at` timestamp NULL DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_vietnamese_ci;

--
-- Dumping data for table `enrollments`
--

INSERT INTO `enrollments` (`id`, `user_id`, `course_id`, `order_id`, `status`, `progress_percent`, `enrolled_at`, `completed_at`, `last_accessed_at`, `created_at`, `updated_at`) VALUES
(1, 4, 1, 1, 'active', '50.00', '2026-06-20 00:50:00', NULL, '2026-06-22 13:35:00', '2026-06-20 00:50:00', '2026-06-22 13:35:00'),
(2, 6, 1, 6, 'completed', '100.00', '2026-05-01 00:40:00', '2026-05-03 02:00:00', '2026-05-03 01:50:00', '2026-05-01 00:40:00', '2026-05-03 02:00:00'),
(3, 4, 2, 7, 'active', '25.00', '2026-06-21 01:30:00', NULL, '2026-06-21 02:10:00', '2026-06-21 01:30:00', '2026-06-21 02:10:00'),
(4, 30, 17, 11, 'active', '0.00', '2026-06-29 19:40:49', NULL, NULL, '2026-06-29 19:40:49', '2026-06-29 19:40:49'),
(5, 30, 19, 12, 'active', '0.00', '2026-06-29 20:08:34', NULL, NULL, '2026-06-29 20:08:34', '2026-06-29 20:08:34');

-- --------------------------------------------------------

--
-- Table structure for table `faqs`
--

CREATE TABLE `faqs` (
  `id` bigint UNSIGNED NOT NULL,
  `question` varchar(500) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `answer` text CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `type` varchar(50) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'general',
  `status` varchar(30) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'active' COMMENT 'active/inactive',
  `sort_order` int UNSIGNED NOT NULL DEFAULT '0',
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  `deleted_at` timestamp NULL DEFAULT NULL COMMENT 'Soft delete FAQ để còn đối chứng nội dung câu trả lời'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_vietnamese_ci;

--
-- Dumping data for table `faqs`
--

INSERT INTO `faqs` (`id`, `question`, `answer`, `type`, `status`, `sort_order`, `created_at`, `updated_at`, `deleted_at`) VALUES
(1, 'Tôi có được học lại khóa đã mua không?', 'Có. Sau khi thanh toán thành công và có enrollment active, bạn có thể vào học lại bất kỳ lúc nào theo chính sách của nền tảng.', 'general', 'active', 1, '2026-01-01 01:00:00', '2026-01-01 01:00:00', NULL),
(2, 'Nếu thanh toán thất bại thì sao?', 'Đơn hàng thất bại không tạo enrollment. Bạn có thể dùng chức năng thanh toán lại nếu đơn còn hợp lệ.', 'payment', 'active', 2, '2026-01-01 01:05:00', '2026-01-01 01:05:00', NULL),
(3, 'Khóa Laravel có phù hợp cho người mới không?', 'Có. Khóa bắt đầu từ route, controller, request/resource rồi đi tới auth session, payment và learning flow.', 'course', 'active', 3, '2026-01-01 01:10:00', '2026-01-01 01:10:00', NULL),
(4, 'FAQ inactive demo', 'Dữ liệu này dùng để test filter status inactive.', 'general', 'inactive', 4, '2026-01-01 01:15:00', '2026-01-01 01:15:00', NULL);

-- --------------------------------------------------------

--
-- Table structure for table `instructor_profiles`
--

CREATE TABLE `instructor_profiles` (
  `id` bigint UNSIGNED NOT NULL,
  `user_id` bigint UNSIGNED NOT NULL,
  `bio` text CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci,
  `expertise` text CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci,
  `experience_years` tinyint UNSIGNED DEFAULT NULL,
  `level` varchar(50) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_vietnamese_ci;

--
-- Dumping data for table `instructor_profiles`
--

INSERT INTO `instructor_profiles` (`id`, `user_id`, `bio`, `expertise`, `experience_years`, `level`, `created_at`, `updated_at`) VALUES
(1, 2, 'Backend Developer chuyên Laravel, MySQL và thiết kế REST API cho sản phẩm giáo dục.', 'Laravel, PHP, MySQL, API Design, Payment Flow', 6, 'Senior Backend Instructor', '2026-01-05 01:00:00', '2026-06-20 03:00:00'),
(2, 3, 'Frontend/AI Product Mentor, tập trung React, UI/UX và ứng dụng AI trong học tập.', 'React, UI/UX, AI Product, Learning Analytics', 5, 'Senior Frontend & AI Instructor', '2026-01-05 01:10:00', '2026-06-18 03:00:00');

-- --------------------------------------------------------

--
-- Table structure for table `lessons`
--

CREATE TABLE `lessons` (
  `id` bigint UNSIGNED NOT NULL,
  `course_section_id` bigint UNSIGNED NOT NULL,
  `course_id` bigint UNSIGNED NOT NULL,
  `title` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `slug` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `lesson_type` varchar(30) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'video' COMMENT 'video=bài video, text=bài chữ',
  `content` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci,
  `video_url` varchar(500) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `video_duration_seconds` int UNSIGNED DEFAULT NULL,
  `is_preview` tinyint(1) NOT NULL DEFAULT '0' COMMENT 'Chỉ có hiệu lực khi status=published',
  `status` varchar(30) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'draft' COMMENT 'draft/published/hidden',
  `sort_order` int UNSIGNED NOT NULL DEFAULT '0',
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  `deleted_at` timestamp NULL DEFAULT NULL COMMENT 'Soft delete lesson'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_vietnamese_ci;

--
-- Dumping data for table `lessons`
--

INSERT INTO `lessons` (`id`, `course_section_id`, `course_id`, `title`, `slug`, `lesson_type`, `content`, `video_url`, `video_duration_seconds`, `is_preview`, `status`, `sort_order`, `created_at`, `updated_at`, `deleted_at`) VALUES
(1, 1, 1, 'Giới thiệu REST API trong Laravel', 'gioi-thieu-rest-api-trong-laravel', 'video', 'Giới thiệu cách xây dựng REST API trong Laravel, cấu trúc route, controller, request và resource cho dự án MindHub.', '/demo/videos/laravel-01-intro.mp4', 900, 1, 'published', 1, '2026-01-10 03:00:00', '2026-06-20 03:00:00', NULL),
(2, 1, 1, 'Repository Service Resource là gì?', 'repository-service-resource-la-gi', 'text', 'Repository chứa query, Service chứa business logic, Controller chỉ điều phối request response. Quy tắc này giúp code dễ test và dễ bảo trì.', NULL, NULL, 0, 'published', 2, '2026-01-10 03:10:00', '2026-06-20 03:00:00', NULL),
(3, 2, 1, 'Custom session và refresh token', 'custom-session-va-refresh-token', 'video', 'Custom session dùng bảng sessions, lưu refresh_token_hash, expires_at và revoked_at. Middleware auth.session kiểm tra access token và active.user kiểm tra trạng thái tài khoản.', '/demo/videos/laravel-03-session.mp4', 1800, 0, 'published', 1, '2026-01-10 03:20:00', '2026-06-20 03:00:00', NULL),
(4, 4, 1, 'Lesson hidden demo', 'lesson-hidden-demo', 'text', 'Bài học hidden dùng để test learner không được xem nội dung ẩn.', NULL, NULL, 0, 'hidden', 1, '2026-01-10 03:30:00', '2026-06-20 03:00:00', NULL),
(5, 4, 1, 'Lesson draft demo', 'lesson-draft-demo', 'text', 'Bài học draft dùng để test filter draft.', NULL, NULL, 0, 'draft', 2, '2026-01-10 03:40:00', '2026-06-20 03:00:00', NULL),
(6, 3, 1, 'Payment và enrollment sau khi paid', 'payment-va-enrollment-sau-khi-paid', 'video', 'Payment flow gồm tạo order pending, áp coupon, xác nhận paid, tạo enrollment và revenue. Không cấp quyền học trước khi paid.', '/demo/videos/laravel-06-payment.mp4', 2100, 0, 'published', 1, '2026-01-10 03:50:00', '2026-06-20 03:00:00', NULL),
(7, 5, 2, 'PHP request response cơ bản', 'php-request-response-co-ban', 'video', 'PHP xử lý request, validate input và trả dữ liệu qua JSON response.', '/demo/videos/php-01-request-response.mp4', 1200, 1, 'published', 1, '2026-01-12 03:00:00', '2026-06-15 03:00:00', NULL),
(8, 6, 2, 'MySQL transaction trong thanh toán', 'mysql-transaction-trong-thanh-toan', 'text', 'MySQL transaction giúp đảm bảo order, enrollment và revenue không bị lệch dữ liệu.', NULL, NULL, 0, 'published', 1, '2026-01-12 03:10:00', '2026-06-15 03:00:00', NULL),
(9, 7, 3, 'Component hóa giao diện khóa học', 'component-hoa-giao-dien-khoa-hoc', 'video', 'React component nên tách theo UI state, dữ liệu API và hành vi người dùng.', '/demo/videos/react-01-components.mp4', 1500, 1, 'published', 1, '2026-01-20 03:00:00', '2026-06-16 03:00:00', NULL),
(10, 8, 3, 'Learning dashboard với API thật', 'learning-dashboard-voi-api-that', 'text', 'Learning dashboard cần trạng thái loading, empty, error và dữ liệu progress rõ ràng.', NULL, NULL, 0, 'published', 1, '2026-01-20 03:10:00', '2026-06-16 03:00:00', NULL),
(11, 9, 6, 'AI tóm tắt bài học an toàn', 'ai-tom-tat-bai-hoc-an-toan', 'text', 'AI summary chỉ dùng nội dung bài học, không gửi token hay dữ liệu nhạy cảm lên provider.', NULL, NULL, 1, 'published', 1, '2026-02-15 03:00:00', '2026-06-18 03:00:00', NULL),
(12, 9, 6, 'AI phân tích điểm yếu sau quiz', 'ai-phan-tich-diem-yeu-sau-quiz', 'video', 'AI phân tích điểm yếu dựa trên quiz_attempt_answers.option_id, score_earned và explanation của câu hỏi.', '/demo/videos/ai-02-quiz-weakness.mp4', 1800, 0, 'published', 2, '2026-02-15 03:10:00', '2026-06-18 03:00:00', NULL),
(13, 10, 7, 'Git branch và pull request', 'git-branch-va-pull-request', 'video', 'Git branch giúp team đồ án làm song song, pull request giúp review trước khi merge.', '/demo/videos/git-01-branch-pr.mp4', 900, 1, 'published', 1, '2026-01-15 03:00:00', '2026-06-01 03:00:00', NULL),
(14, 11, 4, 'NodeJS draft lesson', 'nodejs-draft-lesson', 'text', 'Nội dung draft NodeJS chưa public.', NULL, NULL, 0, 'draft', 1, '2026-02-01 03:00:00', '2026-02-01 03:00:00', NULL),
(15, 12, 8, 'Module boundary trong Laravel', 'module-boundary-trong-laravel', 'text', 'Advanced Laravel dùng module boundary để giảm phụ thuộc chéo giữa Auth, Payment và Learning.', NULL, NULL, 0, 'published', 1, '2026-06-01 03:00:00', '2026-06-18 01:00:00', NULL),
(29, 21, 17, 'VIDEO SEC Published Private Video Lesson', 'video-sec-lesson-published-private', 'video', 'Lesson dùng để test learner response không lộ raw video_url.', 'videos/lessons/29/50f5236e-a375-4d48-9851-8e59ce3e7d0c.mp4', 777, 0, 'published', 1, '2026-06-29 19:40:49', '2026-06-29 19:46:58', NULL),
(30, 21, 17, 'VIDEO SEC Hidden Video Lesson', 'video-sec-lesson-hidden-private', 'video', 'Lesson hidden dùng để test bị chặn.', 'videos/lessons/seed/video-sec-hidden-original.mp4', 600, 0, 'hidden', 2, '2026-06-29 19:40:49', '2026-06-29 19:40:49', NULL),
(31, 22, 18, 'VIDEO SEC Other Instructor Lesson', 'video-sec-other-instructor-lesson', 'video', 'Lesson thuộc instructor 2 để test ownership.', 'videos/lessons/seed/video-sec-other-original.mp4', 300, 0, 'published', 1, '2026-06-29 19:40:49', '2026-06-29 19:40:49', NULL),
(32, 23, 19, 'VIDEO SEC 030405 Private Stream Lesson', 'video-sec-030405-video-private', 'video', 'Lesson dùng để test signed stream URL và backend stream.', 'videos/lessons/32/stream-private-video.mp4', 777, 0, 'published', 1, '2026-06-29 20:08:33', '2026-06-29 20:08:34', NULL),
(33, 23, 19, 'VIDEO SEC 030405 Text Lesson', 'video-sec-030405-text-lesson', 'text', 'Text lesson dùng để test video-url trả 422.', NULL, NULL, 0, 'published', 2, '2026-06-29 20:08:33', '2026-06-29 20:08:33', NULL),
(34, 23, 19, 'VIDEO SEC 030405 Hidden Video', 'video-sec-030405-hidden-video', 'video', 'Hidden lesson dùng để test bị chặn.', 'videos/lessons/seed/hidden.mp4', 300, 0, 'hidden', 3, '2026-06-29 20:08:34', '2026-06-29 20:08:34', NULL),
(35, 23, 19, 'VIDEO SEC 030405 Missing File Video', 'video-sec-030405-missing-file-video', 'video', 'Lesson có video_url nhưng file không tồn tại.', 'videos/lessons/missing/video-sec-030405-missing.mp4', 300, 0, 'published', 4, '2026-06-29 20:08:34', '2026-06-29 20:08:34', NULL);

-- --------------------------------------------------------

--
-- Table structure for table `lesson_assets`
--

CREATE TABLE `lesson_assets` (
  `id` bigint UNSIGNED NOT NULL,
  `lesson_id` bigint UNSIGNED NOT NULL,
  `title` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `file_url` varchar(500) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `file_name` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `file_type` varchar(50) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `file_size` bigint UNSIGNED DEFAULT NULL COMMENT 'Dung lượng file tính bằng byte',
  `note` text CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci,
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  `deleted_at` timestamp NULL DEFAULT NULL COMMENT 'Soft delete asset'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_vietnamese_ci;

--
-- Dumping data for table `lesson_assets`
--

INSERT INTO `lesson_assets` (`id`, `lesson_id`, `title`, `file_url`, `file_name`, `file_type`, `file_size`, `note`, `created_at`, `deleted_at`) VALUES
(1, 1, 'Slide giới thiệu Laravel API', '/demo/assets/laravel-api-intro.pdf', 'laravel-api-intro.pdf', 'pdf', 1250000, 'Tài liệu dùng cho bài mở đầu.', '2026-01-10 04:00:00', NULL),
(2, 2, 'Checklist Repository Service Resource', '/demo/assets/repository-service-checklist.pdf', 'repository-service-checklist.pdf', 'pdf', 850000, 'Checklist code sạch cho backend.', '2026-01-10 04:05:00', NULL),
(3, 3, 'Sơ đồ custom session flow', '/demo/assets/custom-session-flow.png', 'custom-session-flow.png', 'image/png', 420000, 'Minh họa login/refresh/logout.', '2026-01-10 04:10:00', NULL),
(4, 11, 'Prompt mẫu AI summary', '/demo/assets/ai-summary-prompt.md', 'ai-summary-prompt.md', 'text/markdown', 12000, 'Prompt demo không chứa dữ liệu nhạy cảm.', '2026-02-15 04:00:00', NULL),
(8, 29, 'VIDEO SEC Raw Asset Should Be Hidden', 'https://example.com/raw-video-sec-asset.pdf', 'video-sec-asset.pdf', 'pdf', 10240, 'Raw file_url này không được lộ trong lesson response learner.', '2026-06-29 19:40:49', NULL);

-- --------------------------------------------------------

--
-- Table structure for table `lesson_notes`
--

CREATE TABLE `lesson_notes` (
  `id` bigint UNSIGNED NOT NULL,
  `user_id` bigint UNSIGNED NOT NULL COMMENT 'Người tạo ghi chú',
  `course_id` bigint UNSIGNED NOT NULL COMMENT 'Khóa học chứa lesson',
  `lesson_id` bigint UNSIGNED NOT NULL COMMENT 'Bài học được ghi chú',
  `content` text CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL COMMENT 'Nội dung ghi chú cá nhân',
  `note_time_second` int UNSIGNED DEFAULT NULL COMMENT 'Mốc thời gian video, null nếu không gắn với video time',
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  `deleted_at` timestamp NULL DEFAULT NULL COMMENT 'Soft delete note'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_vietnamese_ci;

--
-- Dumping data for table `lesson_notes`
--

INSERT INTO `lesson_notes` (`id`, `user_id`, `course_id`, `lesson_id`, `content`, `note_time_second`, `created_at`, `updated_at`, `deleted_at`) VALUES
(1, 4, 1, 3, 'Chỗ này cần nhớ: revoked_at khác NULL thì middleware phải từ chối session.', 950, '2026-06-22 13:30:00', '2026-06-22 13:30:00', NULL),
(2, 4, 1, 2, 'Service không query trực tiếp quá nhiều, Repository chịu trách nhiệm query.', NULL, '2026-06-20 01:45:00', '2026-06-20 01:45:00', NULL),
(3, 6, 1, 6, 'Payment paid mới tạo revenue và enrollment, không tạo khi pending.', 1200, '2026-05-03 01:30:00', '2026-05-03 01:30:00', NULL);

-- --------------------------------------------------------

--
-- Table structure for table `lesson_progress`
--

CREATE TABLE `lesson_progress` (
  `id` bigint UNSIGNED NOT NULL,
  `lesson_id` bigint UNSIGNED NOT NULL,
  `user_id` bigint UNSIGNED NOT NULL,
  `status` varchar(30) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'not_started' COMMENT 'not_started/in_progress/completed',
  `started_at` timestamp NULL DEFAULT NULL,
  `completed_at` timestamp NULL DEFAULT NULL,
  `learning_duration_seconds` int UNSIGNED NOT NULL DEFAULT '0',
  `last_accessed_at` timestamp NULL DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_vietnamese_ci;

--
-- Dumping data for table `lesson_progress`
--

INSERT INTO `lesson_progress` (`id`, `lesson_id`, `user_id`, `status`, `started_at`, `completed_at`, `learning_duration_seconds`, `last_accessed_at`, `created_at`, `updated_at`) VALUES
(1, 1, 4, 'completed', '2026-06-20 01:00:00', '2026-06-20 01:20:00', 1100, '2026-06-20 01:20:00', '2026-06-20 01:00:00', '2026-06-20 01:20:00'),
(2, 2, 4, 'completed', '2026-06-20 01:25:00', '2026-06-20 01:50:00', 1500, '2026-06-20 01:50:00', '2026-06-20 01:25:00', '2026-06-20 01:50:00'),
(3, 3, 4, 'in_progress', '2026-06-22 13:00:00', NULL, 980, '2026-06-22 13:35:00', '2026-06-22 13:00:00', '2026-06-22 13:35:00'),
(4, 7, 4, 'in_progress', '2026-06-21 02:00:00', NULL, 420, '2026-06-21 02:10:00', '2026-06-21 02:00:00', '2026-06-21 02:10:00'),
(5, 1, 6, 'completed', '2026-05-01 01:00:00', '2026-05-01 01:20:00', 1000, '2026-05-01 01:20:00', '2026-05-01 01:00:00', '2026-05-01 01:20:00'),
(6, 2, 6, 'completed', '2026-05-01 01:30:00', '2026-05-01 02:00:00', 1600, '2026-05-01 02:00:00', '2026-05-01 01:30:00', '2026-05-01 02:00:00'),
(7, 3, 6, 'completed', '2026-05-02 01:00:00', '2026-05-02 01:45:00', 2200, '2026-05-02 01:45:00', '2026-05-02 01:00:00', '2026-05-02 01:45:00'),
(8, 6, 6, 'completed', '2026-05-03 01:00:00', '2026-05-03 01:50:00', 2300, '2026-05-03 01:50:00', '2026-05-03 01:00:00', '2026-05-03 01:50:00'),
(9, 29, 30, 'in_progress', '2026-06-29 19:46:56', NULL, 0, '2026-06-29 19:46:56', '2026-06-29 19:46:56', '2026-06-29 19:46:56');

-- --------------------------------------------------------

--
-- Table structure for table `migrations`
--

CREATE TABLE `migrations` (
  `id` int UNSIGNED NOT NULL,
  `migration` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `batch` int NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `notifications`
--

CREATE TABLE `notifications` (
  `id` bigint UNSIGNED NOT NULL,
  `user_id` bigint UNSIGNED NOT NULL COMMENT 'Người nhận thông báo',
  `type` varchar(50) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL COMMENT 'Loại thông báo, validate ở code, không dùng enum DB',
  `title` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL COMMENT 'Tiêu đề thông báo',
  `message` text CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL COMMENT 'Nội dung thông báo',
  `data` json DEFAULT NULL COMMENT 'Dữ liệu điều hướng/phụ trợ như order_id, course_id, lesson_id',
  `action_url` varchar(500) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL COMMENT 'URL điều hướng khi người dùng bấm thông báo',
  `channel` varchar(30) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'database' COMMENT 'Kênh thông báo, validate ở code, không dùng enum DB',
  `read_at` timestamp NULL DEFAULT NULL COMMENT 'NULL = chưa đọc, có giá trị = đã đọc',
  `email_to` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL COMMENT 'Email người nhận nếu có gửi mail',
  `email_status` varchar(30) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL COMMENT 'Trạng thái gửi email, validate ở code, không dùng enum DB',
  `email_sent_at` timestamp NULL DEFAULT NULL COMMENT 'Thời điểm gửi email thành công',
  `email_error` text CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci COMMENT 'Lỗi gửi email nếu thất bại',
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  `deleted_at` timestamp NULL DEFAULT NULL COMMENT 'Soft delete notification'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `notifications`
--

INSERT INTO `notifications` (`id`, `user_id`, `type`, `title`, `message`, `data`, `action_url`, `channel`, `read_at`, `email_to`, `email_status`, `email_sent_at`, `email_error`, `created_at`, `updated_at`, `deleted_at`) VALUES
(1, 4, 'payment_paid', 'Thanh toán thành công', 'Bạn đã được ghi danh vào khóa Laravel REST API.', '{\"order_id\": 1, \"course_id\": 1}', '/me/courses/1', 'database', '2026-06-20 01:10:00', 'learner1@mindhub.test', 'sent', '2026-06-20 00:51:00', NULL, '2026-06-20 00:51:00', '2026-06-20 01:10:00', NULL),
(2, 4, 'learning_resume', 'Bạn đang học dở bài Custom session', 'Tiếp tục từ giây 980 trong bài Custom session và refresh token.', '{\"course_id\": 1, \"lesson_id\": 3, \"current_second\": 980}', '/learn/lessons/3', 'database', NULL, NULL, NULL, NULL, NULL, '2026-06-22 13:40:00', '2026-06-22 13:40:00', NULL),
(3, 5, 'payment_pending', 'Đơn hàng đang chờ thanh toán', 'Đơn ORD-2026-0002 của bạn đang chờ thanh toán.', '{\"order_id\": 2, \"course_id\": 1}', '/orders/2', 'database', NULL, 'learner2@mindhub.test', 'queued', NULL, NULL, '2026-06-22 13:31:00', '2026-06-22 13:31:00', NULL),
(4, 6, 'certificate_issued', 'Chứng chỉ đã được cấp', 'Chúc mừng bạn đã hoàn thành khóa Laravel REST API.', '{\"course_id\": 1, \"certificate_id\": 1}', '/certificates/MH-CERT-2026-0001', 'database', '2026-05-03 03:00:00', 'learner.completed@mindhub.test', 'sent', '2026-05-03 02:05:00', NULL, '2026-05-03 02:05:00', '2026-05-03 03:00:00', NULL),
(5, 2, 'revenue_available', 'Doanh thu có thể rút', 'Bạn có doanh thu khả dụng từ khóa Laravel REST API.', '{\"order_id\": 1, \"revenue_id\": 1}', '/instructor/revenues', 'database', NULL, 'instructor1@mindhub.test', 'sent', '2026-06-20 01:00:00', NULL, '2026-06-20 01:00:00', '2026-06-20 01:00:00', NULL);

-- --------------------------------------------------------

--
-- Table structure for table `orders`
--

CREATE TABLE `orders` (
  `id` bigint UNSIGNED NOT NULL,
  `coupon_id` bigint UNSIGNED DEFAULT NULL,
  `course_id` bigint UNSIGNED NOT NULL,
  `user_id` bigint UNSIGNED NOT NULL,
  `order_code` varchar(50) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL COMMENT 'Mã đơn hàng duy nhất',
  `status` varchar(30) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'pending' COMMENT 'pending/paid/cancelled/failed/expired',
  `price_snapshot` decimal(12,2) NOT NULL DEFAULT '0.00' COMMENT 'Giá khóa học tại thời điểm mua',
  `payment_method` varchar(50) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL COMMENT 'bank_transfer/momo/vnpay/cash/free...',
  `provider_transaction_id` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL COMMENT 'Mã giao dịch do cổng thanh toán trả về',
  `amount` decimal(12,2) NOT NULL DEFAULT '0.00' COMMENT 'Số tiền thực trả sau giảm giá',
  `payment_status` varchar(30) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT 'unpaid' COMMENT 'unpaid/processing/paid/failed; đã bỏ refunded theo chốt',
  `paid_at` timestamp NULL DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_vietnamese_ci;

--
-- Dumping data for table `orders`
--

INSERT INTO `orders` (`id`, `coupon_id`, `course_id`, `user_id`, `order_code`, `status`, `price_snapshot`, `payment_method`, `provider_transaction_id`, `amount`, `payment_status`, `paid_at`, `created_at`, `updated_at`) VALUES
(1, 2, 1, 4, 'ORD-2026-0001', 'paid', '299000.00', 'vnpay', 'VNPAY-DEMO-0001', '269100.00', 'paid', '2026-06-20 00:50:00', '2026-06-20 00:45:00', '2026-06-20 00:50:00'),
(2, NULL, 1, 5, 'ORD-2026-0002', 'pending', '299000.00', NULL, NULL, '299000.00', 'unpaid', NULL, '2026-06-22 13:30:00', '2026-06-22 13:30:00'),
(3, NULL, 2, 5, 'ORD-2026-0003', 'failed', '399000.00', 'vnpay', 'VNPAY-DEMO-0003', '399000.00', 'failed', NULL, '2026-06-18 05:00:00', '2026-06-18 05:15:00'),
(4, NULL, 3, 5, 'ORD-2026-0004', 'cancelled', '449000.00', NULL, NULL, '449000.00', 'unpaid', NULL, '2026-06-17 02:00:00', '2026-06-17 02:20:00'),
(5, NULL, 6, 5, 'ORD-2026-0005', 'expired', '499000.00', NULL, NULL, '499000.00', 'unpaid', NULL, '2026-05-01 02:00:00', '2026-05-02 02:00:00'),
(6, NULL, 1, 6, 'ORD-2026-0006', 'paid', '299000.00', 'momo', 'MOMO-DEMO-0006', '299000.00', 'paid', '2026-05-01 00:40:00', '2026-05-01 00:30:00', '2026-05-01 00:40:00'),
(7, NULL, 2, 4, 'ORD-2026-0007', 'paid', '399000.00', 'bank_transfer', 'BANK-DEMO-0007', '399000.00', 'paid', '2026-06-21 01:30:00', '2026-06-21 01:00:00', '2026-06-21 01:30:00'),
(8, NULL, 7, 5, 'ORD-2026-0008', 'pending', '0.00', 'free', NULL, '0.00', 'unpaid', NULL, '2026-06-23 01:00:00', '2026-06-23 01:00:00'),
(9, NULL, 3, 4, 'ORD-2026-0009', 'pending', '449000.00', NULL, NULL, '449000.00', 'unpaid', NULL, '2026-06-23 01:05:00', '2026-06-23 01:05:00'),
(10, NULL, 6, 4, 'ORD-2026-0010', 'failed', '499000.00', 'vnpay', 'VNPAY-DEMO-0010', '499000.00', 'failed', NULL, '2026-06-19 07:00:00', '2026-06-19 07:10:00'),
(11, NULL, 17, 30, 'VIDEO_SEC_ORDER_17_30', 'paid', '0.00', 'seed', 'VIDEO_SEC_SEED_29', '99000.00', 'paid', '2026-06-29 19:40:49', '2026-06-29 19:40:49', '2026-06-29 19:40:49'),
(12, NULL, 19, 30, 'VIDEO_SEC_030405_ORDER_19_30', 'paid', '99000.00', 'seed', 'VIDEO_SEC_030405_SEED_19_30', '99000.00', 'paid', '2026-06-29 20:08:34', '2026-06-29 20:08:34', '2026-06-29 20:08:34');

-- --------------------------------------------------------

--
-- Table structure for table `payout_accounts`
--

CREATE TABLE `payout_accounts` (
  `id` bigint UNSIGNED NOT NULL,
  `user_id` bigint UNSIGNED NOT NULL,
  `provider` varchar(50) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL COMMENT 'bank/momo/paypal...',
  `account_number` varchar(100) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `account_name` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `connected_at` timestamp NULL DEFAULT NULL,
  `status` varchar(30) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'pending_verification' COMMENT 'active/inactive/pending_verification/rejected',
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  `deleted_at` timestamp NULL DEFAULT NULL COMMENT 'Soft delete payout account; không xóa cứng dữ liệu tài chính'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_vietnamese_ci;

--
-- Dumping data for table `payout_accounts`
--

INSERT INTO `payout_accounts` (`id`, `user_id`, `provider`, `account_number`, `account_name`, `connected_at`, `status`, `created_at`, `updated_at`, `deleted_at`) VALUES
(1, 2, 'bank', '970400000001', 'NGUYEN MINH KHOA', '2026-02-01 01:00:00', 'active', '2026-02-01 01:00:00', '2026-02-01 01:00:00', NULL),
(2, 3, 'bank', '970400000002', 'TRAN HA LINH', NULL, 'pending_verification', '2026-02-02 01:00:00', '2026-02-02 01:00:00', NULL);

-- --------------------------------------------------------

--
-- Table structure for table `quizzes`
--

CREATE TABLE `quizzes` (
  `id` bigint UNSIGNED NOT NULL,
  `course_id` bigint UNSIGNED NOT NULL,
  `lesson_id` bigint UNSIGNED DEFAULT NULL,
  `title` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `description` text CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci,
  `passing_score` decimal(6,2) NOT NULL DEFAULT '0.00',
  `status` varchar(30) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'draft' COMMENT 'draft/published/hidden',
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  `deleted_at` timestamp NULL DEFAULT NULL COMMENT 'Soft delete quiz'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_vietnamese_ci;

--
-- Dumping data for table `quizzes`
--

INSERT INTO `quizzes` (`id`, `course_id`, `lesson_id`, `title`, `description`, `passing_score`, `status`, `created_at`, `updated_at`, `deleted_at`) VALUES
(1, 1, 6, 'Quiz cuối khóa Laravel API', 'Kiểm tra kiến thức về auth session, payment và repository/service.', '70.00', 'published', '2026-01-10 05:00:00', '2026-06-20 03:00:00', NULL),
(2, 6, 12, 'Quiz AI Learning Flow', 'Kiểm tra nguyên tắc dùng AI an toàn trong nền tảng học tập.', '70.00', 'published', '2026-02-15 05:00:00', '2026-06-18 03:00:00', NULL),
(3, 1, NULL, 'Quiz draft demo', 'Dữ liệu quiz draft để test filter.', '50.00', 'draft', '2026-01-10 05:10:00', '2026-01-10 05:10:00', NULL);

-- --------------------------------------------------------

--
-- Table structure for table `quiz_attempts`
--

CREATE TABLE `quiz_attempts` (
  `id` bigint UNSIGNED NOT NULL,
  `quiz_id` bigint UNSIGNED NOT NULL,
  `user_id` bigint UNSIGNED NOT NULL,
  `attempt_number` int UNSIGNED NOT NULL DEFAULT '1',
  `score` decimal(6,2) DEFAULT NULL,
  `total_score` decimal(6,2) NOT NULL DEFAULT '0.00',
  `passed` tinyint(1) NOT NULL DEFAULT '0',
  `status` varchar(30) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'in_progress' COMMENT 'in_progress/submitted; đã bỏ graded và cancelled theo chốt',
  `started_at` timestamp NULL DEFAULT NULL,
  `submitted_at` timestamp NULL DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_vietnamese_ci;

--
-- Dumping data for table `quiz_attempts`
--

INSERT INTO `quiz_attempts` (`id`, `quiz_id`, `user_id`, `attempt_number`, `score`, `total_score`, `passed`, `status`, `started_at`, `submitted_at`, `created_at`) VALUES
(1, 1, 4, 1, '2.00', '3.00', 0, 'submitted', '2026-06-22 14:10:00', '2026-06-22 14:18:00', '2026-06-22 14:10:00'),
(2, 1, 6, 1, '3.00', '3.00', 1, 'submitted', '2026-05-03 02:00:00', '2026-05-03 02:08:00', '2026-05-03 02:00:00');

-- --------------------------------------------------------

--
-- Table structure for table `quiz_attempt_answers`
--

CREATE TABLE `quiz_attempt_answers` (
  `id` bigint UNSIGNED NOT NULL,
  `question_id` bigint UNSIGNED NOT NULL,
  `attempt_id` bigint UNSIGNED NOT NULL,
  `option_id` bigint UNSIGNED DEFAULT NULL COMMENT 'Null nếu câu hỏi không có option phù hợp hoặc cần mở rộng sau',
  `is_correct` tinyint(1) NOT NULL DEFAULT '0',
  `score_earned` decimal(6,2) NOT NULL DEFAULT '0.00',
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_vietnamese_ci;

--
-- Dumping data for table `quiz_attempt_answers`
--

INSERT INTO `quiz_attempt_answers` (`id`, `question_id`, `attempt_id`, `option_id`, `is_correct`, `score_earned`, `created_at`) VALUES
(1, 1, 1, 2, 1, '1.00', '2026-06-22 14:18:00'),
(2, 2, 1, 4, 0, '0.00', '2026-06-22 14:18:00'),
(3, 3, 1, 7, 1, '1.00', '2026-06-22 14:18:00'),
(4, 1, 2, 2, 1, '1.00', '2026-05-03 02:08:00'),
(5, 2, 2, 5, 1, '1.00', '2026-05-03 02:08:00'),
(6, 3, 2, 7, 1, '1.00', '2026-05-03 02:08:00');

-- --------------------------------------------------------

--
-- Table structure for table `quiz_options`
--

CREATE TABLE `quiz_options` (
  `id` bigint UNSIGNED NOT NULL,
  `question_id` bigint UNSIGNED NOT NULL,
  `option_text` text CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `is_correct` tinyint(1) NOT NULL DEFAULT '0',
  `sort_order` int UNSIGNED NOT NULL DEFAULT '0',
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `quiz_options`
--

INSERT INTO `quiz_options` (`id`, `question_id`, `option_text`, `is_correct`, `sort_order`, `created_at`) VALUES
(1, 1, 'Controller', 0, 1, '2026-01-10 05:40:00'),
(2, 1, 'Service', 1, 2, '2026-01-10 05:40:00'),
(3, 1, 'Migration', 0, 3, '2026-01-10 05:40:00'),
(4, 2, 'Đúng', 0, 1, '2026-01-10 05:45:00'),
(5, 2, 'Sai', 1, 2, '2026-01-10 05:45:00'),
(6, 3, 'Có, tạo ngay khi user bấm mua', 0, 1, '2026-01-10 05:50:00'),
(7, 3, 'Không, chỉ tạo sau khi paid', 1, 2, '2026-01-10 05:50:00'),
(8, 4, 'Nội dung bài học công khai cần tóm tắt', 0, 1, '2026-02-15 05:40:00'),
(9, 4, 'Password, token, secret hoặc dữ liệu nhạy cảm', 1, 2, '2026-02-15 05:40:00'),
(10, 5, 'Đúng', 1, 1, '2026-02-15 05:45:00'),
(11, 5, 'Sai', 0, 2, '2026-02-15 05:45:00');

-- --------------------------------------------------------

--
-- Table structure for table `quiz_questions`
--

CREATE TABLE `quiz_questions` (
  `id` bigint UNSIGNED NOT NULL,
  `quiz_id` bigint UNSIGNED NOT NULL,
  `question_text` text CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `question_type` varchar(30) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'single_choice' COMMENT 'single_choice/multiple_choice/true_false',
  `score` decimal(6,2) NOT NULL DEFAULT '1.00',
  `sort_order` int UNSIGNED NOT NULL DEFAULT '0',
  `explanation` text CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci,
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_vietnamese_ci;

--
-- Dumping data for table `quiz_questions`
--

INSERT INTO `quiz_questions` (`id`, `quiz_id`, `question_text`, `question_type`, `score`, `sort_order`, `explanation`, `created_at`) VALUES
(1, 1, 'Trong kiến trúc đã chốt, business logic chính nên đặt ở đâu?', 'single_choice', '1.00', 1, 'Controller chỉ điều phối, business logic nên nằm trong Service.', '2026-01-10 05:20:00'),
(2, 1, 'Project GD1 dùng Sanctum làm cơ chế token chính.', 'true_false', '1.00', 2, 'Sai. Project dùng custom session với auth.session và bảng sessions.', '2026-01-10 05:25:00'),
(3, 1, 'Khi order còn pending/unpaid thì hệ thống có được tạo enrollment chưa?', 'single_choice', '1.00', 3, 'Không. Enrollment chỉ tạo sau khi payment được xác nhận paid.', '2026-01-10 05:30:00'),
(4, 2, 'Khi gọi AI provider, dữ liệu nào không được gửi?', 'single_choice', '1.00', 1, 'Không gửi password, token, secret hoặc dữ liệu nhạy cảm.', '2026-02-15 05:20:00'),
(5, 2, 'AI summary có thể cache theo lesson_id, summary_type, language và source_content_hash.', 'true_false', '1.00', 2, 'Đúng theo bảng ai_lesson_summaries đã chốt.', '2026-02-15 05:25:00');

-- --------------------------------------------------------

--
-- Table structure for table `revenues`
--

CREATE TABLE `revenues` (
  `id` bigint UNSIGNED NOT NULL,
  `instructor_id` bigint UNSIGNED NOT NULL,
  `course_id` bigint UNSIGNED NOT NULL,
  `order_id` bigint UNSIGNED NOT NULL,
  `gross_amount` decimal(12,2) NOT NULL DEFAULT '0.00' COMMENT 'Tổng tiền đơn hàng',
  `instructor_amount` decimal(12,2) NOT NULL DEFAULT '0.00' COMMENT 'Tiền giảng viên nhận',
  `platform_fee_amount` decimal(12,2) NOT NULL DEFAULT '0.00' COMMENT 'Phí nền tảng',
  `status` varchar(30) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'pending' COMMENT 'pending/available/withdrawn/cancelled',
  `earned_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_vietnamese_ci;

--
-- Dumping data for table `revenues`
--

INSERT INTO `revenues` (`id`, `instructor_id`, `course_id`, `order_id`, `gross_amount`, `instructor_amount`, `platform_fee_amount`, `status`, `earned_at`, `created_at`) VALUES
(1, 2, 1, 1, '269100.00', '188370.00', '80730.00', 'available', '2026-06-20 00:50:00', '2026-06-20 00:50:00'),
(2, 2, 1, 6, '299000.00', '209300.00', '89700.00', 'withdrawn', '2026-05-01 00:40:00', '2026-05-01 00:40:00'),
(3, 2, 2, 7, '399000.00', '279300.00', '119700.00', 'pending', '2026-06-21 01:30:00', '2026-06-21 01:30:00');

-- --------------------------------------------------------

--
-- Table structure for table `sessions`
--

CREATE TABLE `sessions` (
  `id` bigint UNSIGNED NOT NULL,
  `user_id` bigint UNSIGNED NOT NULL,
  `refresh_token_hash` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL COMMENT 'Chỉ lưu hash refresh token, không lưu token thô',
  `device_name` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `ip_address` varchar(45) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL COMMENT 'Hỗ trợ IPv4/IPv6',
  `user_agent` text CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci,
  `expires_at` timestamp NOT NULL COMMENT 'Thời điểm hết hạn phiên',
  `revoked_at` timestamp NULL DEFAULT NULL COMMENT 'Thời điểm thu hồi phiên',
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `sessions`
--

INSERT INTO `sessions` (`id`, `user_id`, `refresh_token_hash`, `device_name`, `ip_address`, `user_agent`, `expires_at`, `revoked_at`, `created_at`) VALUES
(1, 4, 'demo_hash_learner1_active_chrome', 'Chrome on Windows', '127.0.0.1', 'Mozilla/5.0 Chrome MindHub Demo', '2026-07-23 16:59:59', NULL, '2026-06-23 00:40:00'),
(2, 4, 'demo_hash_learner1_expired_mobile', 'Safari on iPhone', '10.0.0.12', 'Mobile Safari MindHub Demo', '2026-05-01 16:59:59', NULL, '2026-04-01 01:00:00'),
(3, 4, 'demo_hash_learner1_revoked_edge', 'Edge on Windows', '10.0.0.13', 'Microsoft Edge MindHub Demo', '2026-07-01 16:59:59', '2026-06-01 02:00:00', '2026-05-20 01:00:00'),
(4, 5, 'demo_hash_learner2_active_chrome', 'Chrome on MacBook', '10.0.0.14', 'Mozilla/5.0 Chrome macOS MindHub Demo', '2026-07-22 16:59:59', NULL, '2026-06-22 14:00:00'),
(5, 7, 'demo_hash_locked_user_active', 'Firefox on Windows', '10.0.0.15', 'Firefox MindHub Demo', '2026-07-01 16:59:59', NULL, '2026-04-01 03:00:00'),
(6, 10, 'demo_hash_limit_device_1', 'Chrome on Windows', '10.0.0.21', 'Chrome MindHub Demo', '2026-07-23 16:59:59', NULL, '2026-06-22 23:00:00'),
(7, 10, 'demo_hash_limit_device_2', 'Safari on iPad', '10.0.0.22', 'Safari iPad MindHub Demo', '2026-07-23 16:59:59', NULL, '2026-06-22 23:10:00'),
(8, 10, 'demo_hash_limit_expired', 'Old Android', '10.0.0.23', 'Android WebView MindHub Demo', '2026-03-01 16:59:59', NULL, '2026-02-01 05:00:00'),
(9, 30, 'd4763071fe99abe3d65aae60ef48181a23ac34480287986af08f4a36bf2ecc70', 'VIDEO_SEC_TERMINAL_TEST', '127.0.0.1', 'Mozilla/5.0 (Windows NT; Windows NT 10.0; en-US) WindowsPowerShell/5.1.26100.8655', '2026-07-29 19:46:53', NULL, '2026-06-29 19:46:53'),
(10, 31, '3bc96db6b159b915b73af032bd60d0cccb34a86de88527684050be8609a40d17', 'VIDEO_SEC_TERMINAL_TEST', '127.0.0.1', 'Mozilla/5.0 (Windows NT; Windows NT 10.0; en-US) WindowsPowerShell/5.1.26100.8655', '2026-07-29 19:46:54', NULL, '2026-06-29 19:46:54'),
(11, 33, '27d2fd7111c3b123c401e653fe89768779c05f80709762720504ee14aa8768f1', 'VIDEO_SEC_TERMINAL_TEST', '127.0.0.1', 'Mozilla/5.0 (Windows NT; Windows NT 10.0; en-US) WindowsPowerShell/5.1.26100.8655', '2026-07-29 19:46:54', NULL, '2026-06-29 19:46:54'),
(12, 35, '3114ead54f3a058d6d0913f5102484805ab64a0ad38516b45c5b001866f3d057', 'VIDEO_SEC_TERMINAL_TEST', '127.0.0.1', 'Mozilla/5.0 (Windows NT; Windows NT 10.0; en-US) WindowsPowerShell/5.1.26100.8655', '2026-07-29 19:46:55', NULL, '2026-06-29 19:46:55'),
(13, 30, 'e617ccd1b3acb7e3b08f3af199029cab4dcc8429213eaa92a3de0cba106a72b8', 'VIDEO_SEC_030405_TERMINAL_TEST', '127.0.0.1', 'Mozilla/5.0 (Windows NT; Windows NT 10.0; en-US) WindowsPowerShell/5.1.26100.8655', '2026-07-29 20:04:56', NULL, '2026-06-29 20:04:56'),
(14, 31, '34877e9e02f2f68344af8f531ffdbdf98eb2b3a8446dbbc6bfa00585e3e6ce5d', 'VIDEO_SEC_030405_TERMINAL_TEST', '127.0.0.1', 'Mozilla/5.0 (Windows NT; Windows NT 10.0; en-US) WindowsPowerShell/5.1.26100.8655', '2026-07-29 20:04:57', NULL, '2026-06-29 20:04:57'),
(15, 33, '0bc3b4605257d378d119b324f08277a02022164c1bd2b7570dbf228f344cabf2', 'VIDEO_SEC_030405_TERMINAL_TEST', '127.0.0.1', 'Mozilla/5.0 (Windows NT; Windows NT 10.0; en-US) WindowsPowerShell/5.1.26100.8655', '2026-07-29 20:04:57', NULL, '2026-06-29 20:04:57'),
(16, 35, '7ce196b1e419494169d3ccd910758e1b014417b15e81ba7bd9686653d47bcb9d', 'VIDEO_SEC_030405_TERMINAL_TEST', '127.0.0.1', 'Mozilla/5.0 (Windows NT; Windows NT 10.0; en-US) WindowsPowerShell/5.1.26100.8655', '2026-07-29 20:04:58', NULL, '2026-06-29 20:04:58'),
(17, 30, '943e74f04278705d8b944d10d92a69d5f9da615ee56f461d4eb57eccf7e651e8', 'VIDEO_SEC_030405_TERMINAL_TEST', '127.0.0.1', 'Mozilla/5.0 (Windows NT; Windows NT 10.0; en-US) WindowsPowerShell/5.1.26100.8655', '2026-07-29 20:10:33', NULL, '2026-06-29 20:10:33'),
(18, 31, 'ffb4ce8b3a851dcae311f6ff086593b957b6a906df0e5c046c4232e77cf77c9a', 'VIDEO_SEC_030405_TERMINAL_TEST', '127.0.0.1', 'Mozilla/5.0 (Windows NT; Windows NT 10.0; en-US) WindowsPowerShell/5.1.26100.8655', '2026-07-29 20:10:33', NULL, '2026-06-29 20:10:33'),
(19, 33, 'daa3e1c9c15a9defa1ceeb3c88641549cb253b0cf5a8e211a7b115026f8463d2', 'VIDEO_SEC_030405_TERMINAL_TEST', '127.0.0.1', 'Mozilla/5.0 (Windows NT; Windows NT 10.0; en-US) WindowsPowerShell/5.1.26100.8655', '2026-07-29 20:10:34', NULL, '2026-06-29 20:10:34'),
(20, 35, 'ddf287fe4ffba6cb26723cbd7866c62425c604967588d91d7f889af009b0f1aa', 'VIDEO_SEC_030405_TERMINAL_TEST', '127.0.0.1', 'Mozilla/5.0 (Windows NT; Windows NT 10.0; en-US) WindowsPowerShell/5.1.26100.8655', '2026-07-29 20:10:34', NULL, '2026-06-29 20:10:34'),
(21, 30, 'c0d6af4094b7c3e7ac96b24ca5b0830d8600d34cf0caf45689d661bce12f9220', 'VIDEO_SEC_RANGE_RETEST', '127.0.0.1', 'Mozilla/5.0 (Windows NT; Windows NT 10.0; en-US) WindowsPowerShell/5.1.26100.8655', '2026-07-29 20:12:47', NULL, '2026-06-29 20:12:47'),
(22, 36, '6a956f114006ed51c29b8f771b5a5408c4bb96d26ad25b6db84c2b2ca4aa1681', 'api_client', '127.0.0.1', 'Mozilla/5.0 (Windows NT; Windows NT 10.0; en-US) WindowsPowerShell/5.1.26100.8875', '2026-08-30 11:00:28', NULL, '2026-07-31 11:00:28'),
(23, 1, '2c2dd5cc668549c43d6a2a1a3f9a3d42d0c358214a4d84db5abf2467320e80de', 'api_client', '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/150.0.0.0 Safari/537.36', '2026-08-30 11:24:05', NULL, '2026-07-31 11:24:05'),
(24, 37, '1ca60de7d0a70274f772f3c20e7a200ba6f61b210113abaca539308650bacbfe', 'api_client', '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/150.0.0.0 Safari/537.36', '2026-08-30 11:26:23', NULL, '2026-07-31 11:26:23'),
(25, 37, '0854ca47a6b8ae0b0a794c8e267b6254296a5840bc98a85da91e2979319870e1', 'api_client', '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/150.0.0.0 Safari/537.36', '2026-08-30 11:26:39', NULL, '2026-07-31 11:26:39'),
(26, 37, 'c6479e370ea8ecaf960bdb4128942b05e5398d37f505cacd468d1de3f790b02b', 'api_client', '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/150.0.0.0 Safari/537.36', '2026-08-30 11:27:10', NULL, '2026-07-31 11:27:10'),
(27, 1, '143d8026ed3cfae252128ad6815c918683efc656725cfd69ac4ee6bb5f061f12', 'api_client', '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/150.0.0.0 Safari/537.36', '2026-08-30 11:31:05', NULL, '2026-07-31 11:31:05'),
(28, 37, '31a0e4c9768e02d8aee520be941b34d09dfbf456a1ccde11c5865612128d092f', 'api_client', '127.0.0.1', 'node', '2026-08-30 11:59:48', NULL, '2026-07-31 11:59:48'),
(29, 1, '514de07b4fe7db1d2c468d576bac41a37fd1a4ab0c48d1273970878c48302219', 'api_client', '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/150.0.0.0 Safari/537.36', '2026-08-30 12:34:57', NULL, '2026-07-31 12:34:57'),
(30, 37, 'e39c70c978583294cf5e3985c888e0deb2d9cd092ef2604df638c74e87331d08', 'api_client', '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/150.0.0.0 Safari/537.36', '2026-08-30 21:33:05', NULL, '2026-07-31 21:33:05');

-- --------------------------------------------------------

--
-- Table structure for table `users`
--

CREATE TABLE `users` (
  `id` bigint UNSIGNED NOT NULL,
  `full_name` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL COMMENT 'Họ tên người dùng',
  `email` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL COMMENT 'Email đăng nhập, duy nhất',
  `password_hash` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL COMMENT 'Hash mật khẩu; null nếu chỉ đăng nhập OAuth',
  `phone` varchar(20) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL COMMENT 'Số điện thoại người dùng',
  `oauth_account_login` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL COMMENT 'OAuth account login ID nếu đăng nhập bằng tài khoản bên thứ ba như Google',
  `role` varchar(30) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'learner' COMMENT 'admin=quản trị viên, instructor=giảng viên, learner=học viên',
  `status` varchar(30) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'active' COMMENT 'active=hoạt động, inactive=tạm ngưng/chưa kích hoạt, locked=bị khóa',
  `email_verified_at` timestamp NULL DEFAULT NULL COMMENT 'Thời điểm xác thực email',
  `last_login_at` timestamp NULL DEFAULT NULL COMMENT 'Lần đăng nhập gần nhất',
  `locked` tinyint(1) NOT NULL DEFAULT '0' COMMENT 'Cờ khóa nhanh tài khoản',
  `locked_reason` text CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci COMMENT 'Lý do khóa tài khoản',
  `password_reset` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL COMMENT 'Token/hash reset mật khẩu nếu gộp vào users',
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  `deleted_at` timestamp NULL DEFAULT NULL COMMENT 'Soft delete user'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_vietnamese_ci;

--
-- Dumping data for table `users`
--

INSERT INTO `users` (`id`, `full_name`, `email`, `password_hash`, `phone`, `oauth_account_login`, `role`, `status`, `email_verified_at`, `last_login_at`, `locked`, `locked_reason`, `password_reset`, `created_at`, `updated_at`, `deleted_at`) VALUES
(1, 'MindHub Admin', 'admin@mindhub.test', '$2y$12$j6h5YFXMJndp1aI7e1GCgeFc.pUUPNyT/tW8bzfHpxz03sUG.OXXC', '0900000001', NULL, 'admin', 'active', '2026-01-05 01:00:00', '2026-07-31 12:34:57', 0, NULL, NULL, '2026-01-01 01:00:00', '2026-07-31 12:34:57', NULL),
(2, 'Nguyễn Minh Khoa', 'instructor1@mindhub.test', '$2y$12$j6h5YFXMJndp1aI7e1GCgeFc.pUUPNyT/tW8bzfHpxz03sUG.OXXC', '0900000002', NULL, 'instructor', 'active', '2026-01-05 01:05:00', '2026-06-22 12:20:00', 0, NULL, NULL, '2026-01-01 01:05:00', '2026-06-22 12:20:00', NULL),
(3, 'Trần Hà Linh', 'instructor2@mindhub.test', '$2y$12$j6h5YFXMJndp1aI7e1GCgeFc.pUUPNyT/tW8bzfHpxz03sUG.OXXC', '0900000003', NULL, 'instructor', 'active', '2026-01-05 01:10:00', '2026-06-21 09:45:00', 0, NULL, NULL, '2026-01-01 01:10:00', '2026-06-21 09:45:00', NULL),
(4, 'Lê Gia Bảo', 'learner1@mindhub.test', '$2y$12$j6h5YFXMJndp1aI7e1GCgeFc.pUUPNyT/tW8bzfHpxz03sUG.OXXC', '0900000004', NULL, 'learner', 'active', '2026-01-05 02:00:00', '2026-06-23 00:40:00', 0, NULL, NULL, '2026-01-02 02:00:00', '2026-06-23 00:40:00', NULL),
(5, 'Phạm Anh Thư', 'learner2@mindhub.test', '$2y$12$j6h5YFXMJndp1aI7e1GCgeFc.pUUPNyT/tW8bzfHpxz03sUG.OXXC', '0900000005', NULL, 'learner', 'active', '2026-01-05 02:05:00', '2026-06-22 14:00:00', 0, NULL, NULL, '2026-01-02 02:05:00', '2026-06-22 14:00:00', NULL),
(6, 'Đỗ Hoàng Nam', 'learner.completed@mindhub.test', '$2y$12$j6h5YFXMJndp1aI7e1GCgeFc.pUUPNyT/tW8bzfHpxz03sUG.OXXC', '0900000006', NULL, 'learner', 'active', '2026-01-05 02:10:00', '2026-06-20 03:30:00', 0, NULL, NULL, '2026-01-02 02:10:00', '2026-06-20 03:30:00', NULL),
(7, 'Tài khoản bị khóa', 'locked@mindhub.test', '$2y$12$j6h5YFXMJndp1aI7e1GCgeFc.pUUPNyT/tW8bzfHpxz03sUG.OXXC', '0900000007', NULL, 'learner', 'locked', '2026-01-05 02:15:00', '2026-04-01 03:00:00', 1, 'Demo tài khoản bị khóa để test active.user middleware.', NULL, '2026-01-02 02:15:00', '2026-04-01 03:00:00', NULL),
(8, 'Tài khoản inactive', 'inactive@mindhub.test', '$2y$12$j6h5YFXMJndp1aI7e1GCgeFc.pUUPNyT/tW8bzfHpxz03sUG.OXXC', '0900000008', NULL, 'learner', 'inactive', NULL, NULL, 0, NULL, NULL, '2026-01-02 02:20:00', '2026-01-02 02:20:00', NULL),
(9, 'OAuth Only Learner', 'oauth.only@mindhub.test', NULL, NULL, 'google-oauth-demo-9001', 'learner', 'active', '2026-01-05 02:25:00', '2026-06-10 04:00:00', 0, NULL, NULL, '2026-01-02 02:25:00', '2026-06-10 04:00:00', NULL),
(10, 'Learner Device Limit', 'learner.limit@mindhub.test', '$2y$12$j6h5YFXMJndp1aI7e1GCgeFc.pUUPNyT/tW8bzfHpxz03sUG.OXXC', '0900000010', NULL, 'learner', 'active', '2026-01-05 02:30:00', '2026-06-22 23:30:00', 0, NULL, NULL, '2026-01-02 02:30:00', '2026-06-22 23:30:00', NULL),
(11, 'Learner Empty State', 'learner.empty@mindhub.test', '$2y$12$j6h5YFXMJndp1aI7e1GCgeFc.pUUPNyT/tW8bzfHpxz03sUG.OXXC', '0900000011', NULL, 'learner', 'active', '2026-01-05 02:35:00', NULL, 0, NULL, NULL, '2026-01-02 02:35:00', '2026-01-02 02:35:00', NULL),
(30, 'VIDEO SEC LEARNER PUGW', 'video.sec.learner.bought@example.com', '$2y$12$BHLgVFfTM7ffK3Hg0tTw4.b.Q37i8E7U6NIW39b4jtIAr9yiqqVIq', '0948984094', NULL, 'learner', 'active', '2026-06-29 20:08:32', '2026-06-29 20:12:47', 0, NULL, NULL, '2026-06-29 20:08:32', '2026-06-29 20:12:47', NULL),
(31, 'VIDEO SEC LEARNER 4WAP', 'video.sec.learner.new@example.com', '$2y$12$TCEB1pB4gQPx4vKm0.PO0.CEChdj7OQF7bY3uODss.l9NFIKDFxz2', '0941261507', NULL, 'learner', 'active', '2026-06-29 20:08:33', '2026-06-29 20:10:33', 0, NULL, NULL, '2026-06-29 20:08:33', '2026-06-29 20:10:33', NULL),
(32, 'VIDEO SEC LEARNER 7EPG', 'video.sec.learner.inactive@example.com', '$2y$12$IHPRwrnPPiEWZAW9ah5QkOAP4JgPONMLSWedGyAhdIotqqN6Q2v4a', '0937198378', NULL, 'learner', 'inactive', '2026-06-29 20:08:33', NULL, 0, NULL, NULL, '2026-06-29 20:08:33', '2026-06-29 20:08:33', NULL),
(33, 'VIDEO SEC INSTRUCTOR M5IN', 'video.sec.instructor.1@example.com', '$2y$12$TaUMEE4H4KFu8uPfNCgaZObWtraCcvv8.H9w4nx84xbEeFdh3VnaC', '0967697818', NULL, 'instructor', 'active', '2026-06-29 20:08:33', '2026-06-29 20:10:34', 0, NULL, NULL, '2026-06-29 20:08:33', '2026-06-29 20:10:34', NULL),
(34, 'VIDEO SEC INSTRUCTOR 2FI8', 'video.sec.instructor.2@example.com', '$2y$12$QGatHJS7hNX4pmGKhIGiVew.309wS5zuBW/Qw7wNe3a8vcMbx7LsK', '0952724996', NULL, 'instructor', 'active', '2026-06-29 19:40:48', NULL, 0, NULL, NULL, '2026-06-29 19:40:48', '2026-06-29 19:40:48', NULL),
(35, 'VIDEO SEC ADMIN G6XD', 'video.sec.admin@example.com', '$2y$12$F287PhBGwt/duMpJfl/UheU8Oo7JAcvQD5RWYqL74EIaFB3p2nqB6', '0991063211', NULL, 'admin', 'active', '2026-06-29 20:08:33', '2026-06-29 20:10:34', 0, NULL, NULL, '2026-06-29 20:08:33', '2026-06-29 20:10:34', NULL),
(36, 'Admin Preview', 'admin.preview@mindhub.test', '$2y$12$1GVMl/FzeEbvcbeExIqbJ.Qno.DDGU5pyXpDZUuIA1sM3xobPfGPW', NULL, NULL, 'admin', 'active', '2026-07-31 11:00:03', '2026-07-31 11:00:28', 0, NULL, NULL, '2026-07-31 11:00:03', '2026-07-31 11:00:28', NULL),
(37, 'Admin Test 2', 'admin2@mindhub.test', '$2y$12$xfjdfAkl7eljjgxHGn5Aj.pwKRBB1.ApiJg1D5JXbbewahOlQ3vN.', NULL, NULL, 'admin', 'active', '2026-07-31 11:25:50', '2026-07-31 21:33:05', 0, NULL, NULL, '2026-07-31 11:25:50', '2026-07-31 21:33:05', NULL);

-- --------------------------------------------------------

--
-- Table structure for table `user_category_interests`
--

CREATE TABLE `user_category_interests` (
  `id` bigint UNSIGNED NOT NULL,
  `user_id` bigint UNSIGNED NOT NULL COMMENT 'Người dùng chọn chủ đề quan tâm',
  `category_id` bigint UNSIGNED NOT NULL COMMENT 'Danh mục/chủ đề quan tâm',
  `interest_level` tinyint UNSIGNED NOT NULL DEFAULT '3' COMMENT 'Mức độ quan tâm từ 1 đến 5',
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_vietnamese_ci;

--
-- Dumping data for table `user_category_interests`
--

INSERT INTO `user_category_interests` (`id`, `user_id`, `category_id`, `interest_level`, `created_at`, `updated_at`) VALUES
(1, 4, 3, 5, '2026-06-01 01:00:00', '2026-06-01 01:00:00'),
(2, 4, 5, 4, '2026-06-01 01:05:00', '2026-06-01 01:05:00'),
(3, 5, 2, 5, '2026-06-01 01:10:00', '2026-06-01 01:10:00'),
(4, 5, 4, 4, '2026-06-01 01:15:00', '2026-06-01 01:15:00'),
(5, 5, 5, 3, '2026-06-01 01:20:00', '2026-06-01 01:20:00');

-- --------------------------------------------------------

--
-- Table structure for table `video_progress`
--

CREATE TABLE `video_progress` (
  `id` bigint UNSIGNED NOT NULL,
  `lesson_id` bigint UNSIGNED NOT NULL,
  `user_id` bigint UNSIGNED NOT NULL,
  `current_second` int UNSIGNED NOT NULL DEFAULT '0',
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_vietnamese_ci;

--
-- Dumping data for table `video_progress`
--

INSERT INTO `video_progress` (`id`, `lesson_id`, `user_id`, `current_second`, `created_at`, `updated_at`) VALUES
(1, 1, 4, 900, '2026-06-20 01:00:00', '2026-06-20 01:20:00'),
(2, 3, 4, 980, '2026-06-22 13:00:00', '2026-06-22 13:35:00'),
(3, 7, 4, 420, '2026-06-21 02:00:00', '2026-06-21 02:10:00'),
(4, 1, 6, 900, '2026-05-01 01:00:00', '2026-05-01 01:20:00'),
(5, 3, 6, 1800, '2026-05-02 01:00:00', '2026-05-02 01:45:00'),
(6, 6, 6, 2100, '2026-05-03 01:00:00', '2026-05-03 01:50:00');

-- --------------------------------------------------------

--
-- Table structure for table `wishlist`
--

CREATE TABLE `wishlist` (
  `id` bigint UNSIGNED NOT NULL,
  `user_id` bigint UNSIGNED NOT NULL,
  `course_id` bigint UNSIGNED NOT NULL,
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `wishlist`
--

INSERT INTO `wishlist` (`id`, `user_id`, `course_id`, `created_at`) VALUES
(1, 5, 1, '2026-06-18 03:00:00'),
(2, 5, 3, '2026-06-18 03:05:00'),
(3, 5, 6, '2026-06-18 03:10:00'),
(4, 4, 3, '2026-06-19 04:00:00'),
(5, 4, 6, '2026-06-19 04:05:00');

-- --------------------------------------------------------

--
-- Table structure for table `withdraw_requests`
--

CREATE TABLE `withdraw_requests` (
  `id` bigint UNSIGNED NOT NULL,
  `user_id` bigint UNSIGNED NOT NULL,
  `payout_account_id` bigint UNSIGNED NOT NULL,
  `amount` decimal(12,2) NOT NULL,
  `status` varchar(30) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'pending' COMMENT 'pending/approved/rejected/paid/cancelled',
  `requested_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `approved_at` timestamp NULL DEFAULT NULL,
  `paid_at` timestamp NULL DEFAULT NULL,
  `rejected_reason` text CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci,
  `provider_payout_id` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `account_number_snapshot` varchar(100) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL COMMENT 'Snapshot STK tại lúc yêu cầu rút',
  `account_name_snapshot` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL COMMENT 'Snapshot tên chủ TK tại lúc yêu cầu rút',
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_vietnamese_ci;

--
-- Dumping data for table `withdraw_requests`
--

INSERT INTO `withdraw_requests` (`id`, `user_id`, `payout_account_id`, `amount`, `status`, `requested_at`, `approved_at`, `paid_at`, `rejected_reason`, `provider_payout_id`, `account_number_snapshot`, `account_name_snapshot`, `created_at`, `updated_at`) VALUES
(1, 2, 1, '209300.00', 'paid', '2026-05-05 01:00:00', '2026-05-05 03:00:00', '2026-05-06 02:00:00', NULL, 'PAYOUT-DEMO-0001', '970400000001', 'NGUYEN MINH KHOA', '2026-05-05 01:00:00', '2026-05-06 02:00:00'),
(2, 2, 1, '188370.00', 'pending', '2026-06-22 01:00:00', NULL, NULL, NULL, NULL, '970400000001', 'NGUYEN MINH KHOA', '2026-06-22 01:00:00', '2026-06-22 01:00:00');

--
-- Indexes for dumped tables
--

--
-- Indexes for table `ai_lesson_summaries`
--
ALTER TABLE `ai_lesson_summaries`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `uq_ai_lesson_summaries_cache` (`lesson_id`,`summary_type`,`language`,`source_content_hash`),
  ADD KEY `idx_ai_lesson_summaries_lesson_id` (`lesson_id`),
  ADD KEY `idx_ai_lesson_summaries_course_id` (`course_id`),
  ADD KEY `idx_ai_lesson_summaries_lookup` (`lesson_id`,`summary_type`,`language`),
  ADD KEY `idx_ai_lesson_summaries_generated_at` (`generated_at`),
  ADD KEY `fk_ai_lesson_summaries_generated_by_user` (`generated_by_user_id`);

--
-- Indexes for table `banners`
--
ALTER TABLE `banners`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_banners_position_status` (`position`,`status`),
  ADD KEY `idx_banners_sort_order` (`sort_order`);

--
-- Indexes for table `categories`
--
ALTER TABLE `categories`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `uq_categories_slug` (`slug`),
  ADD KEY `idx_categories_parent_id` (`parent_id`),
  ADD KEY `idx_categories_status` (`status`);

--
-- Indexes for table `certificates`
--
ALTER TABLE `certificates`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `uq_certificates_code` (`certificate_code`),
  ADD UNIQUE KEY `uq_certificates_enrollment` (`enrollment_id`),
  ADD KEY `idx_certificates_user_id` (`user_id`),
  ADD KEY `idx_certificates_course_id` (`course_id`),
  ADD KEY `idx_certificates_status` (`status`),
  ADD KEY `idx_certificates_issued_at` (`issued_at`);

--
-- Indexes for table `comments`
--
ALTER TABLE `comments`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_comments_parent_id` (`parent_id`),
  ADD KEY `idx_comments_user_id` (`user_id`),
  ADD KEY `idx_comments_order_id` (`order_id`),
  ADD KEY `idx_comments_lesson_id` (`lesson_id`),
  ADD KEY `idx_comments_status` (`status`);

--
-- Indexes for table `coupons`
--
ALTER TABLE `coupons`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `uq_coupons_code` (`code`),
  ADD KEY `idx_coupons_user_id` (`user_id`),
  ADD KEY `idx_coupons_course_id` (`course_id`),
  ADD KEY `idx_coupons_status` (`status`);

--
-- Indexes for table `courses`
--
ALTER TABLE `courses`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `uq_courses_slug` (`slug`),
  ADD KEY `idx_courses_instructor_id` (`instructor_id`),
  ADD KEY `idx_courses_status` (`status`),
  ADD KEY `idx_courses_featured` (`is_featured`);

--
-- Indexes for table `course_categories`
--
ALTER TABLE `course_categories`
  ADD PRIMARY KEY (`category_id`,`course_id`),
  ADD KEY `idx_course_categories_course_id` (`course_id`);

--
-- Indexes for table `course_faqs`
--
ALTER TABLE `course_faqs`
  ADD PRIMARY KEY (`faq_id`,`course_id`),
  ADD UNIQUE KEY `uq_course_faqs_course_sort` (`course_id`,`sort_order`),
  ADD KEY `idx_course_faqs_course_id` (`course_id`),
  ADD KEY `idx_course_faqs_deleted_at` (`deleted_at`);

--
-- Indexes for table `course_reviews`
--
ALTER TABLE `course_reviews`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `uq_course_reviews_order` (`order_id`),
  ADD KEY `idx_course_reviews_rating` (`rating`);

--
-- Indexes for table `course_sections`
--
ALTER TABLE `course_sections`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `uq_course_sections_sort` (`course_id`,`sort_order`),
  ADD KEY `idx_course_sections_course_id` (`course_id`),
  ADD KEY `idx_course_sections_status` (`status`);

--
-- Indexes for table `enrollments`
--
ALTER TABLE `enrollments`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `uq_enrollments_user_course` (`user_id`,`course_id`),
  ADD UNIQUE KEY `uq_enrollments_order` (`order_id`),
  ADD KEY `idx_enrollments_user_id` (`user_id`),
  ADD KEY `idx_enrollments_course_id` (`course_id`),
  ADD KEY `idx_enrollments_status` (`status`);

--
-- Indexes for table `faqs`
--
ALTER TABLE `faqs`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_faqs_type_status` (`type`,`status`),
  ADD KEY `idx_faqs_sort_order` (`sort_order`);

--
-- Indexes for table `instructor_profiles`
--
ALTER TABLE `instructor_profiles`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `uq_instructor_profiles_user` (`user_id`);

--
-- Indexes for table `lessons`
--
ALTER TABLE `lessons`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `uq_lessons_course_slug` (`course_id`,`slug`),
  ADD UNIQUE KEY `uq_lessons_section_sort` (`course_section_id`,`sort_order`),
  ADD KEY `idx_lessons_course_id` (`course_id`),
  ADD KEY `idx_lessons_section_id` (`course_section_id`),
  ADD KEY `idx_lessons_status` (`status`);

--
-- Indexes for table `lesson_assets`
--
ALTER TABLE `lesson_assets`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_lesson_assets_lesson_id` (`lesson_id`);

--
-- Indexes for table `lesson_notes`
--
ALTER TABLE `lesson_notes`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_lesson_notes_user_lesson` (`user_id`,`lesson_id`),
  ADD KEY `idx_lesson_notes_user_course` (`user_id`,`course_id`),
  ADD KEY `idx_lesson_notes_lesson_id` (`lesson_id`),
  ADD KEY `idx_lesson_notes_note_time_second` (`note_time_second`),
  ADD KEY `fk_lesson_notes_course` (`course_id`);

--
-- Indexes for table `lesson_progress`
--
ALTER TABLE `lesson_progress`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `uq_lesson_progress_user_lesson` (`user_id`,`lesson_id`),
  ADD KEY `idx_lesson_progress_lesson_id` (`lesson_id`),
  ADD KEY `idx_lesson_progress_user_id` (`user_id`),
  ADD KEY `idx_lesson_progress_status` (`status`);

--
-- Indexes for table `migrations`
--
ALTER TABLE `migrations`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `notifications`
--
ALTER TABLE `notifications`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_notifications_user_id` (`user_id`),
  ADD KEY `idx_notifications_user_read_at` (`user_id`,`read_at`),
  ADD KEY `idx_notifications_type` (`type`),
  ADD KEY `idx_notifications_email_status` (`email_status`),
  ADD KEY `idx_notifications_created_at` (`created_at`);

--
-- Indexes for table `orders`
--
ALTER TABLE `orders`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `uq_orders_order_code` (`order_code`),
  ADD UNIQUE KEY `uq_orders_provider_transaction` (`provider_transaction_id`),
  ADD KEY `idx_orders_coupon_id` (`coupon_id`),
  ADD KEY `idx_orders_course_id` (`course_id`),
  ADD KEY `idx_orders_user_id` (`user_id`),
  ADD KEY `idx_orders_status` (`status`),
  ADD KEY `idx_orders_payment_status` (`payment_status`);

--
-- Indexes for table `payout_accounts`
--
ALTER TABLE `payout_accounts`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `uq_payout_accounts_identity` (`user_id`,`provider`,`account_number`),
  ADD KEY `idx_payout_accounts_user_id` (`user_id`),
  ADD KEY `idx_payout_accounts_status` (`status`);

--
-- Indexes for table `quizzes`
--
ALTER TABLE `quizzes`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_quizzes_course_id` (`course_id`),
  ADD KEY `idx_quizzes_lesson_id` (`lesson_id`),
  ADD KEY `idx_quizzes_status` (`status`);

--
-- Indexes for table `quiz_attempts`
--
ALTER TABLE `quiz_attempts`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `uq_quiz_attempts_number` (`quiz_id`,`user_id`,`attempt_number`),
  ADD KEY `idx_quiz_attempts_quiz_id` (`quiz_id`),
  ADD KEY `idx_quiz_attempts_user_id` (`user_id`),
  ADD KEY `idx_quiz_attempts_status` (`status`);

--
-- Indexes for table `quiz_attempt_answers`
--
ALTER TABLE `quiz_attempt_answers`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `uq_quiz_attempt_answers_question` (`attempt_id`,`question_id`),
  ADD KEY `idx_quiz_attempt_answers_question_id` (`question_id`),
  ADD KEY `idx_quiz_attempt_answers_attempt_id` (`attempt_id`),
  ADD KEY `idx_quiz_attempt_answers_option_id` (`option_id`);

--
-- Indexes for table `quiz_options`
--
ALTER TABLE `quiz_options`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `uq_quiz_options_sort` (`question_id`,`sort_order`),
  ADD KEY `idx_quiz_options_question_id` (`question_id`);

--
-- Indexes for table `quiz_questions`
--
ALTER TABLE `quiz_questions`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `uq_quiz_questions_sort` (`quiz_id`,`sort_order`),
  ADD KEY `idx_quiz_questions_quiz_id` (`quiz_id`);

--
-- Indexes for table `revenues`
--
ALTER TABLE `revenues`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `uq_revenues_order` (`order_id`),
  ADD KEY `idx_revenues_instructor_id` (`instructor_id`),
  ADD KEY `idx_revenues_course_id` (`course_id`),
  ADD KEY `idx_revenues_status` (`status`);

--
-- Indexes for table `sessions`
--
ALTER TABLE `sessions`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_sessions_user_id` (`user_id`),
  ADD KEY `idx_sessions_refresh_token_hash` (`refresh_token_hash`),
  ADD KEY `idx_sessions_expires_at` (`expires_at`);

--
-- Indexes for table `users`
--
ALTER TABLE `users`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `uq_users_email` (`email`),
  ADD UNIQUE KEY `uq_users_oauth_account_login` (`oauth_account_login`),
  ADD KEY `idx_users_role` (`role`);

--
-- Indexes for table `user_category_interests`
--
ALTER TABLE `user_category_interests`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `uq_user_category_interests_user_category` (`user_id`,`category_id`),
  ADD KEY `idx_user_category_interests_user_id` (`user_id`),
  ADD KEY `idx_user_category_interests_category_id` (`category_id`),
  ADD KEY `idx_user_category_interests_level` (`interest_level`);

--
-- Indexes for table `video_progress`
--
ALTER TABLE `video_progress`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `uq_video_progress_user_lesson` (`user_id`,`lesson_id`),
  ADD KEY `idx_video_progress_lesson_id` (`lesson_id`),
  ADD KEY `idx_video_progress_user_id` (`user_id`);

--
-- Indexes for table `wishlist`
--
ALTER TABLE `wishlist`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `uq_wishlist_user_course` (`user_id`,`course_id`),
  ADD KEY `idx_wishlist_user_id` (`user_id`),
  ADD KEY `idx_wishlist_course_id` (`course_id`);

--
-- Indexes for table `withdraw_requests`
--
ALTER TABLE `withdraw_requests`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_withdraw_requests_user_id` (`user_id`),
  ADD KEY `idx_withdraw_requests_payout_account_id` (`payout_account_id`),
  ADD KEY `idx_withdraw_requests_status` (`status`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `ai_lesson_summaries`
--
ALTER TABLE `ai_lesson_summaries`
  MODIFY `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT for table `banners`
--
ALTER TABLE `banners`
  MODIFY `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT for table `categories`
--
ALTER TABLE `categories`
  MODIFY `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=8;

--
-- AUTO_INCREMENT for table `certificates`
--
ALTER TABLE `certificates`
  MODIFY `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT for table `comments`
--
ALTER TABLE `comments`
  MODIFY `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- AUTO_INCREMENT for table `coupons`
--
ALTER TABLE `coupons`
  MODIFY `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=8;

--
-- AUTO_INCREMENT for table `courses`
--
ALTER TABLE `courses`
  MODIFY `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=20;

--
-- AUTO_INCREMENT for table `course_reviews`
--
ALTER TABLE `course_reviews`
  MODIFY `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT for table `course_sections`
--
ALTER TABLE `course_sections`
  MODIFY `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=24;

--
-- AUTO_INCREMENT for table `enrollments`
--
ALTER TABLE `enrollments`
  MODIFY `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=6;

--
-- AUTO_INCREMENT for table `faqs`
--
ALTER TABLE `faqs`
  MODIFY `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- AUTO_INCREMENT for table `instructor_profiles`
--
ALTER TABLE `instructor_profiles`
  MODIFY `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT for table `lessons`
--
ALTER TABLE `lessons`
  MODIFY `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=36;

--
-- AUTO_INCREMENT for table `lesson_assets`
--
ALTER TABLE `lesson_assets`
  MODIFY `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=9;

--
-- AUTO_INCREMENT for table `lesson_notes`
--
ALTER TABLE `lesson_notes`
  MODIFY `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT for table `lesson_progress`
--
ALTER TABLE `lesson_progress`
  MODIFY `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=10;

--
-- AUTO_INCREMENT for table `migrations`
--
ALTER TABLE `migrations`
  MODIFY `id` int UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `notifications`
--
ALTER TABLE `notifications`
  MODIFY `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=6;

--
-- AUTO_INCREMENT for table `orders`
--
ALTER TABLE `orders`
  MODIFY `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=13;

--
-- AUTO_INCREMENT for table `payout_accounts`
--
ALTER TABLE `payout_accounts`
  MODIFY `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT for table `quizzes`
--
ALTER TABLE `quizzes`
  MODIFY `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT for table `quiz_attempts`
--
ALTER TABLE `quiz_attempts`
  MODIFY `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT for table `quiz_attempt_answers`
--
ALTER TABLE `quiz_attempt_answers`
  MODIFY `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=7;

--
-- AUTO_INCREMENT for table `quiz_options`
--
ALTER TABLE `quiz_options`
  MODIFY `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=12;

--
-- AUTO_INCREMENT for table `quiz_questions`
--
ALTER TABLE `quiz_questions`
  MODIFY `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=6;

--
-- AUTO_INCREMENT for table `revenues`
--
ALTER TABLE `revenues`
  MODIFY `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT for table `sessions`
--
ALTER TABLE `sessions`
  MODIFY `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=31;

--
-- AUTO_INCREMENT for table `users`
--
ALTER TABLE `users`
  MODIFY `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=38;

--
-- AUTO_INCREMENT for table `user_category_interests`
--
ALTER TABLE `user_category_interests`
  MODIFY `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=6;

--
-- AUTO_INCREMENT for table `video_progress`
--
ALTER TABLE `video_progress`
  MODIFY `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=7;

--
-- AUTO_INCREMENT for table `wishlist`
--
ALTER TABLE `wishlist`
  MODIFY `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=6;

--
-- AUTO_INCREMENT for table `withdraw_requests`
--
ALTER TABLE `withdraw_requests`
  MODIFY `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `ai_lesson_summaries`
--
ALTER TABLE `ai_lesson_summaries`
  ADD CONSTRAINT `fk_ai_lesson_summaries_course` FOREIGN KEY (`course_id`) REFERENCES `courses` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `fk_ai_lesson_summaries_generated_by_user` FOREIGN KEY (`generated_by_user_id`) REFERENCES `users` (`id`) ON DELETE SET NULL ON UPDATE CASCADE,
  ADD CONSTRAINT `fk_ai_lesson_summaries_lesson` FOREIGN KEY (`lesson_id`) REFERENCES `lessons` (`id`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Constraints for table `categories`
--
ALTER TABLE `categories`
  ADD CONSTRAINT `fk_categories_parent` FOREIGN KEY (`parent_id`) REFERENCES `categories` (`id`) ON DELETE SET NULL ON UPDATE CASCADE;

--
-- Constraints for table `certificates`
--
ALTER TABLE `certificates`
  ADD CONSTRAINT `fk_certificates_course` FOREIGN KEY (`course_id`) REFERENCES `courses` (`id`) ON DELETE RESTRICT ON UPDATE CASCADE,
  ADD CONSTRAINT `fk_certificates_enrollment` FOREIGN KEY (`enrollment_id`) REFERENCES `enrollments` (`id`) ON DELETE RESTRICT ON UPDATE CASCADE,
  ADD CONSTRAINT `fk_certificates_user` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE RESTRICT ON UPDATE CASCADE;

--
-- Constraints for table `comments`
--
ALTER TABLE `comments`
  ADD CONSTRAINT `fk_comments_lesson` FOREIGN KEY (`lesson_id`) REFERENCES `lessons` (`id`) ON DELETE RESTRICT ON UPDATE CASCADE,
  ADD CONSTRAINT `fk_comments_order` FOREIGN KEY (`order_id`) REFERENCES `orders` (`id`) ON DELETE SET NULL ON UPDATE CASCADE,
  ADD CONSTRAINT `fk_comments_parent` FOREIGN KEY (`parent_id`) REFERENCES `comments` (`id`) ON DELETE SET NULL ON UPDATE CASCADE,
  ADD CONSTRAINT `fk_comments_user` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE RESTRICT ON UPDATE CASCADE;

--
-- Constraints for table `coupons`
--
ALTER TABLE `coupons`
  ADD CONSTRAINT `fk_coupons_course` FOREIGN KEY (`course_id`) REFERENCES `courses` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `fk_coupons_user` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE SET NULL ON UPDATE CASCADE;

--
-- Constraints for table `courses`
--
ALTER TABLE `courses`
  ADD CONSTRAINT `fk_courses_instructor` FOREIGN KEY (`instructor_id`) REFERENCES `users` (`id`) ON DELETE RESTRICT ON UPDATE CASCADE;

--
-- Constraints for table `course_categories`
--
ALTER TABLE `course_categories`
  ADD CONSTRAINT `fk_course_categories_category` FOREIGN KEY (`category_id`) REFERENCES `categories` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `fk_course_categories_course` FOREIGN KEY (`course_id`) REFERENCES `courses` (`id`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Constraints for table `course_faqs`
--
ALTER TABLE `course_faqs`
  ADD CONSTRAINT `fk_course_faqs_course` FOREIGN KEY (`course_id`) REFERENCES `courses` (`id`) ON DELETE RESTRICT ON UPDATE CASCADE,
  ADD CONSTRAINT `fk_course_faqs_faq` FOREIGN KEY (`faq_id`) REFERENCES `faqs` (`id`) ON DELETE RESTRICT ON UPDATE CASCADE;

--
-- Constraints for table `course_reviews`
--
ALTER TABLE `course_reviews`
  ADD CONSTRAINT `fk_course_reviews_order` FOREIGN KEY (`order_id`) REFERENCES `orders` (`id`) ON DELETE RESTRICT ON UPDATE CASCADE;

--
-- Constraints for table `course_sections`
--
ALTER TABLE `course_sections`
  ADD CONSTRAINT `fk_course_sections_course` FOREIGN KEY (`course_id`) REFERENCES `courses` (`id`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Constraints for table `enrollments`
--
ALTER TABLE `enrollments`
  ADD CONSTRAINT `fk_enrollments_course` FOREIGN KEY (`course_id`) REFERENCES `courses` (`id`) ON DELETE RESTRICT ON UPDATE CASCADE,
  ADD CONSTRAINT `fk_enrollments_order` FOREIGN KEY (`order_id`) REFERENCES `orders` (`id`) ON DELETE RESTRICT ON UPDATE CASCADE,
  ADD CONSTRAINT `fk_enrollments_user` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE RESTRICT ON UPDATE CASCADE;

--
-- Constraints for table `instructor_profiles`
--
ALTER TABLE `instructor_profiles`
  ADD CONSTRAINT `fk_instructor_profiles_user` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Constraints for table `lessons`
--
ALTER TABLE `lessons`
  ADD CONSTRAINT `fk_lessons_course` FOREIGN KEY (`course_id`) REFERENCES `courses` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `fk_lessons_section` FOREIGN KEY (`course_section_id`) REFERENCES `course_sections` (`id`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Constraints for table `lesson_assets`
--
ALTER TABLE `lesson_assets`
  ADD CONSTRAINT `fk_lesson_assets_lesson` FOREIGN KEY (`lesson_id`) REFERENCES `lessons` (`id`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Constraints for table `lesson_notes`
--
ALTER TABLE `lesson_notes`
  ADD CONSTRAINT `fk_lesson_notes_course` FOREIGN KEY (`course_id`) REFERENCES `courses` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `fk_lesson_notes_lesson` FOREIGN KEY (`lesson_id`) REFERENCES `lessons` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `fk_lesson_notes_user` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Constraints for table `lesson_progress`
--
ALTER TABLE `lesson_progress`
  ADD CONSTRAINT `fk_lesson_progress_lesson` FOREIGN KEY (`lesson_id`) REFERENCES `lessons` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `fk_lesson_progress_user` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Constraints for table `notifications`
--
ALTER TABLE `notifications`
  ADD CONSTRAINT `fk_notifications_user` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Constraints for table `orders`
--
ALTER TABLE `orders`
  ADD CONSTRAINT `fk_orders_coupon` FOREIGN KEY (`coupon_id`) REFERENCES `coupons` (`id`) ON DELETE SET NULL ON UPDATE CASCADE,
  ADD CONSTRAINT `fk_orders_course` FOREIGN KEY (`course_id`) REFERENCES `courses` (`id`) ON DELETE RESTRICT ON UPDATE CASCADE,
  ADD CONSTRAINT `fk_orders_user` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE RESTRICT ON UPDATE CASCADE;

--
-- Constraints for table `payout_accounts`
--
ALTER TABLE `payout_accounts`
  ADD CONSTRAINT `fk_payout_accounts_user` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE RESTRICT ON UPDATE CASCADE;

--
-- Constraints for table `quizzes`
--
ALTER TABLE `quizzes`
  ADD CONSTRAINT `fk_quizzes_course` FOREIGN KEY (`course_id`) REFERENCES `courses` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `fk_quizzes_lesson` FOREIGN KEY (`lesson_id`) REFERENCES `lessons` (`id`) ON DELETE SET NULL ON UPDATE CASCADE;

--
-- Constraints for table `quiz_attempts`
--
ALTER TABLE `quiz_attempts`
  ADD CONSTRAINT `fk_quiz_attempts_quiz` FOREIGN KEY (`quiz_id`) REFERENCES `quizzes` (`id`) ON DELETE RESTRICT ON UPDATE CASCADE,
  ADD CONSTRAINT `fk_quiz_attempts_user` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE RESTRICT ON UPDATE CASCADE;

--
-- Constraints for table `quiz_attempt_answers`
--
ALTER TABLE `quiz_attempt_answers`
  ADD CONSTRAINT `fk_quiz_attempt_answers_attempt` FOREIGN KEY (`attempt_id`) REFERENCES `quiz_attempts` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `fk_quiz_attempt_answers_option` FOREIGN KEY (`option_id`) REFERENCES `quiz_options` (`id`) ON DELETE SET NULL ON UPDATE CASCADE,
  ADD CONSTRAINT `fk_quiz_attempt_answers_question` FOREIGN KEY (`question_id`) REFERENCES `quiz_questions` (`id`) ON DELETE RESTRICT ON UPDATE CASCADE;

--
-- Constraints for table `quiz_options`
--
ALTER TABLE `quiz_options`
  ADD CONSTRAINT `fk_quiz_options_question` FOREIGN KEY (`question_id`) REFERENCES `quiz_questions` (`id`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Constraints for table `quiz_questions`
--
ALTER TABLE `quiz_questions`
  ADD CONSTRAINT `fk_quiz_questions_quiz` FOREIGN KEY (`quiz_id`) REFERENCES `quizzes` (`id`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Constraints for table `revenues`
--
ALTER TABLE `revenues`
  ADD CONSTRAINT `fk_revenues_course` FOREIGN KEY (`course_id`) REFERENCES `courses` (`id`) ON DELETE RESTRICT ON UPDATE CASCADE,
  ADD CONSTRAINT `fk_revenues_instructor` FOREIGN KEY (`instructor_id`) REFERENCES `users` (`id`) ON DELETE RESTRICT ON UPDATE CASCADE,
  ADD CONSTRAINT `fk_revenues_order` FOREIGN KEY (`order_id`) REFERENCES `orders` (`id`) ON DELETE RESTRICT ON UPDATE CASCADE;

--
-- Constraints for table `sessions`
--
ALTER TABLE `sessions`
  ADD CONSTRAINT `fk_sessions_user` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Constraints for table `user_category_interests`
--
ALTER TABLE `user_category_interests`
  ADD CONSTRAINT `fk_user_category_interests_category` FOREIGN KEY (`category_id`) REFERENCES `categories` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `fk_user_category_interests_user` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Constraints for table `video_progress`
--
ALTER TABLE `video_progress`
  ADD CONSTRAINT `fk_video_progress_lesson` FOREIGN KEY (`lesson_id`) REFERENCES `lessons` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `fk_video_progress_user` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Constraints for table `wishlist`
--
ALTER TABLE `wishlist`
  ADD CONSTRAINT `fk_wishlist_course` FOREIGN KEY (`course_id`) REFERENCES `courses` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `fk_wishlist_user` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Constraints for table `withdraw_requests`
--
ALTER TABLE `withdraw_requests`
  ADD CONSTRAINT `fk_withdraw_requests_payout_account` FOREIGN KEY (`payout_account_id`) REFERENCES `payout_accounts` (`id`) ON DELETE RESTRICT ON UPDATE CASCADE,
  ADD CONSTRAINT `fk_withdraw_requests_user` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE RESTRICT ON UPDATE CASCADE;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
