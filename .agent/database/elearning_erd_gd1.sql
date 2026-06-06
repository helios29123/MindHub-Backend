-- ==========================================================
-- E-learning Database Schema - GD1
-- Generated from agreed ERD rules/status/data types
-- Revision: user role added, OAuth column renamed, simplified lesson/enrollment/banner statuses
-- Target: MySQL 8.x / MariaDB compatible as much as possible
-- Charset: utf8mb4
-- ==========================================================

SET NAMES utf8mb4;
SET FOREIGN_KEY_CHECKS = 0;

DROP TABLE IF EXISTS quiz_attempt_answers;
DROP TABLE IF EXISTS quiz_attempts;
DROP TABLE IF EXISTS quiz_options;
DROP TABLE IF EXISTS quiz_questions;
DROP TABLE IF EXISTS quizzes;
DROP TABLE IF EXISTS course_faqs;
DROP TABLE IF EXISTS faqs;
DROP TABLE IF EXISTS banners;
DROP TABLE IF EXISTS withdraw_requests;
DROP TABLE IF EXISTS payout_accounts;
DROP TABLE IF EXISTS revenues;
DROP TABLE IF EXISTS instructor_profiles;
DROP TABLE IF EXISTS comments;
DROP TABLE IF EXISTS wishlist;
DROP TABLE IF EXISTS course_reviews;
DROP TABLE IF EXISTS enrollments;
DROP TABLE IF EXISTS orders;
DROP TABLE IF EXISTS coupons;
DROP TABLE IF EXISTS video_progress;
DROP TABLE IF EXISTS lesson_progress;
DROP TABLE IF EXISTS lesson_assets;
DROP TABLE IF EXISTS lessons;
DROP TABLE IF EXISTS course_sections;
DROP TABLE IF EXISTS course_categories;
DROP TABLE IF EXISTS courses;
DROP TABLE IF EXISTS categories;
DROP TABLE IF EXISTS sessions;
DROP TABLE IF EXISTS users;

SET FOREIGN_KEY_CHECKS = 1;

-- ==========================================================
-- 1. USERS
-- ==========================================================
CREATE TABLE users (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    full_name VARCHAR(255) NOT NULL COMMENT 'Họ tên người dùng',
    email VARCHAR(255) NOT NULL COMMENT 'Email đăng nhập, duy nhất',
    password_hash VARCHAR(255) NULL COMMENT 'Hash mật khẩu; null nếu chỉ đăng nhập OAuth',
    phone VARCHAR(20) NULL COMMENT 'Số điện thoại người dùng',
    oauth_account_login VARCHAR(255) NULL COMMENT 'OAuth account login ID nếu đăng nhập bằng tài khoản bên thứ ba như Google',
    role VARCHAR(30) NOT NULL DEFAULT 'learner' COMMENT 'admin=quản trị viên, instructor=giảng viên, learner=học viên',
    status VARCHAR(30) NOT NULL DEFAULT 'active' COMMENT 'active=hoạt động, inactive=tạm ngưng/chưa kích hoạt, locked=bị khóa',
    email_verified_at TIMESTAMP NULL COMMENT 'Thời điểm xác thực email',
    last_login_at TIMESTAMP NULL COMMENT 'Lần đăng nhập gần nhất',
    locked BOOLEAN NOT NULL DEFAULT FALSE COMMENT 'Cờ khóa nhanh tài khoản',
    locked_reason TEXT NULL COMMENT 'Lý do khóa tài khoản',
    password_reset VARCHAR(255) NULL COMMENT 'Token/hash reset mật khẩu nếu gộp vào users',
    created_at TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    deleted_at TIMESTAMP NULL COMMENT 'Soft delete user',

    CONSTRAINT uq_users_email UNIQUE (email),
    CONSTRAINT uq_users_oauth_account_login UNIQUE (oauth_account_login),
    INDEX idx_users_role (role),
    CONSTRAINT chk_users_role CHECK (role IN ('admin', 'instructor', 'learner')),
    CONSTRAINT chk_users_status CHECK (status IN ('active', 'inactive', 'locked'))
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ==========================================================
-- 2. SESSIONS
-- ==========================================================
CREATE TABLE sessions (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    user_id BIGINT UNSIGNED NOT NULL,
    refresh_token_hash VARCHAR(255) NOT NULL COMMENT 'Chỉ lưu hash refresh token, không lưu token thô',
    device_name VARCHAR(255) NULL,
    ip_address VARCHAR(45) NULL COMMENT 'Hỗ trợ IPv4/IPv6',
    user_agent TEXT NULL,
    expires_at TIMESTAMP NOT NULL COMMENT 'Thời điểm hết hạn phiên',
    revoked_at TIMESTAMP NULL COMMENT 'Thời điểm thu hồi phiên',
    created_at TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP,

    INDEX idx_sessions_user_id (user_id),
    INDEX idx_sessions_refresh_token_hash (refresh_token_hash),
    INDEX idx_sessions_expires_at (expires_at),
    CONSTRAINT fk_sessions_user FOREIGN KEY (user_id) REFERENCES users(id)
        ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ==========================================================
-- 3. CATEGORIES
-- ==========================================================
CREATE TABLE categories (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    parent_id BIGINT UNSIGNED NULL COMMENT 'Danh mục cha; null nếu là danh mục gốc',
    name VARCHAR(255) NOT NULL,
    slug VARCHAR(255) NOT NULL,
    description TEXT NULL,
    sort_order INT UNSIGNED NOT NULL DEFAULT 0,
    status VARCHAR(30) NOT NULL DEFAULT 'active' COMMENT 'active=hiển thị, inactive=ẩn',
    created_at TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    deleted_at TIMESTAMP NULL COMMENT 'Soft delete category',

    CONSTRAINT uq_categories_slug UNIQUE (slug),
    INDEX idx_categories_parent_id (parent_id),
    INDEX idx_categories_status (status),
    CONSTRAINT fk_categories_parent FOREIGN KEY (parent_id) REFERENCES categories(id)
        ON DELETE SET NULL ON UPDATE CASCADE,
    CONSTRAINT chk_categories_status CHECK (status IN ('active', 'inactive'))
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ==========================================================
-- 4. COURSES
-- NOTE: completed_course was intentionally removed because completion is per user/enrollment.
-- ==========================================================
CREATE TABLE courses (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    instructor_id BIGINT UNSIGNED NOT NULL COMMENT 'User đóng vai trò giảng viên',
    title VARCHAR(255) NOT NULL,
    slug VARCHAR(255) NOT NULL,
    short_description VARCHAR(500) NULL,
    description TEXT NULL,
    thumbnail_url VARCHAR(500) NULL,
    intro_video_url VARCHAR(500) NULL,
    price DECIMAL(12,2) NOT NULL DEFAULT 0.00 COMMENT 'Giá gốc khóa học',
    sale_price DECIMAL(12,2) NULL COMMENT 'Giá khuyến mãi; null nếu không sale',
    level VARCHAR(30) NOT NULL DEFAULT 'beginner' COMMENT 'beginner/intermediate/advanced/all_levels',
    language VARCHAR(50) NOT NULL DEFAULT 'vi',
    requirements TEXT NULL COMMENT 'Yêu cầu đầu vào',
    outcomes TEXT NULL COMMENT 'Kết quả đạt được sau khóa học',
    status VARCHAR(30) NOT NULL DEFAULT 'draft' COMMENT 'draft/pending_review/approved/rejected/published/hidden',
    is_featured BOOLEAN NOT NULL DEFAULT FALSE,
    total_duration_seconds INT UNSIGNED NOT NULL DEFAULT 0,
    published_at TIMESTAMP NULL,
    admin_reject_reason TEXT NULL COMMENT 'Lý do admin từ chối khi status=rejected',
    created_at TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    deleted_at TIMESTAMP NULL COMMENT 'Soft delete course',

    CONSTRAINT uq_courses_slug UNIQUE (slug),
    INDEX idx_courses_instructor_id (instructor_id),
    INDEX idx_courses_status (status),
    INDEX idx_courses_featured (is_featured),
    CONSTRAINT fk_courses_instructor FOREIGN KEY (instructor_id) REFERENCES users(id)
        ON DELETE RESTRICT ON UPDATE CASCADE,
    CONSTRAINT chk_courses_status CHECK (status IN ('draft', 'pending_review', 'approved', 'rejected', 'published', 'hidden')),
    CONSTRAINT chk_courses_level CHECK (level IN ('beginner', 'intermediate', 'advanced', 'all_levels')),
    CONSTRAINT chk_courses_price CHECK (price >= 0),
    CONSTRAINT chk_courses_sale_price CHECK (sale_price IS NULL OR sale_price >= 0),
    CONSTRAINT chk_courses_duration CHECK (total_duration_seconds >= 0)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ==========================================================
-- 5. COURSE_CATEGORIES
-- ==========================================================
CREATE TABLE course_categories (
    category_id BIGINT UNSIGNED NOT NULL,
    course_id BIGINT UNSIGNED NOT NULL,
    created_at TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP,

    PRIMARY KEY (category_id, course_id),
    INDEX idx_course_categories_course_id (course_id),
    CONSTRAINT fk_course_categories_category FOREIGN KEY (category_id) REFERENCES categories(id)
        ON DELETE CASCADE ON UPDATE CASCADE,
    CONSTRAINT fk_course_categories_course FOREIGN KEY (course_id) REFERENCES courses(id)
        ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ==========================================================
-- 6. COURSE_SECTIONS
-- ==========================================================
CREATE TABLE course_sections (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    course_id BIGINT UNSIGNED NOT NULL,
    title VARCHAR(255) NOT NULL,
    description TEXT NULL,
    sort_order INT UNSIGNED NOT NULL DEFAULT 0,
    status VARCHAR(30) NOT NULL DEFAULT 'draft' COMMENT 'draft/published/hidden',
    created_at TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    deleted_at TIMESTAMP NULL COMMENT 'Soft delete course section',

    CONSTRAINT uq_course_sections_sort UNIQUE (course_id, sort_order),
    INDEX idx_course_sections_course_id (course_id),
    INDEX idx_course_sections_status (status),
    CONSTRAINT fk_course_sections_course FOREIGN KEY (course_id) REFERENCES courses(id)
        ON DELETE CASCADE ON UPDATE CASCADE,
    CONSTRAINT chk_course_sections_status CHECK (status IN ('draft', 'published', 'hidden'))
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ==========================================================
-- 7. LESSONS
-- NOTE: completed_lesson was intentionally removed because completion is per user/lesson_progress.
-- ==========================================================
CREATE TABLE lessons (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    course_section_id BIGINT UNSIGNED NOT NULL,
    course_id BIGINT UNSIGNED NOT NULL,
    title VARCHAR(255) NOT NULL,
    slug VARCHAR(255) NOT NULL,
    lesson_type VARCHAR(30) NOT NULL DEFAULT 'video' COMMENT 'video=bài video, text=bài chữ',
    content LONGTEXT NULL,
    video_url VARCHAR(500) NULL,
    video_duration_seconds INT UNSIGNED NULL,
    is_preview BOOLEAN NOT NULL DEFAULT FALSE COMMENT 'Chỉ có hiệu lực khi status=published',
    status VARCHAR(30) NOT NULL DEFAULT 'draft' COMMENT 'draft/published/hidden',
    sort_order INT UNSIGNED NOT NULL DEFAULT 0,
    created_at TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    deleted_at TIMESTAMP NULL COMMENT 'Soft delete lesson',

    CONSTRAINT uq_lessons_course_slug UNIQUE (course_id, slug),
    CONSTRAINT uq_lessons_section_sort UNIQUE (course_section_id, sort_order),
    INDEX idx_lessons_course_id (course_id),
    INDEX idx_lessons_section_id (course_section_id),
    INDEX idx_lessons_status (status),
    CONSTRAINT fk_lessons_section FOREIGN KEY (course_section_id) REFERENCES course_sections(id)
        ON DELETE CASCADE ON UPDATE CASCADE,
    CONSTRAINT fk_lessons_course FOREIGN KEY (course_id) REFERENCES courses(id)
        ON DELETE CASCADE ON UPDATE CASCADE,
    CONSTRAINT chk_lessons_type CHECK (lesson_type IN ('video', 'text')),
    CONSTRAINT chk_lessons_status CHECK (status IN ('draft', 'published', 'hidden')),
    CONSTRAINT chk_lessons_video_duration CHECK (video_duration_seconds IS NULL OR video_duration_seconds >= 0)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ==========================================================
-- 8. LESSON_ASSETS
-- ==========================================================
CREATE TABLE lesson_assets (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    lesson_id BIGINT UNSIGNED NOT NULL,
    title VARCHAR(255) NOT NULL,
    file_url VARCHAR(500) NOT NULL,
    file_name VARCHAR(255) NOT NULL,
    file_type VARCHAR(50) NOT NULL,
    file_size BIGINT UNSIGNED NULL COMMENT 'Dung lượng file tính bằng byte',
    note TEXT NULL,
    created_at TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP,
    deleted_at TIMESTAMP NULL COMMENT 'Soft delete asset',

    INDEX idx_lesson_assets_lesson_id (lesson_id),
    CONSTRAINT fk_lesson_assets_lesson FOREIGN KEY (lesson_id) REFERENCES lessons(id)
        ON DELETE CASCADE ON UPDATE CASCADE,
    CONSTRAINT chk_lesson_assets_file_size CHECK (file_size IS NULL OR file_size >= 0)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ==========================================================
-- 9. LESSON_PROGRESS
-- ==========================================================
CREATE TABLE lesson_progress (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    lesson_id BIGINT UNSIGNED NOT NULL,
    user_id BIGINT UNSIGNED NOT NULL,
    status VARCHAR(30) NOT NULL DEFAULT 'not_started' COMMENT 'not_started/in_progress/completed',
    started_at TIMESTAMP NULL,
    completed_at TIMESTAMP NULL,
    learning_duration_seconds INT UNSIGNED NOT NULL DEFAULT 0,
    last_accessed_at TIMESTAMP NULL,
    created_at TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,

    CONSTRAINT uq_lesson_progress_user_lesson UNIQUE (user_id, lesson_id),
    INDEX idx_lesson_progress_lesson_id (lesson_id),
    INDEX idx_lesson_progress_user_id (user_id),
    INDEX idx_lesson_progress_status (status),
    CONSTRAINT fk_lesson_progress_lesson FOREIGN KEY (lesson_id) REFERENCES lessons(id)
        ON DELETE CASCADE ON UPDATE CASCADE,
    CONSTRAINT fk_lesson_progress_user FOREIGN KEY (user_id) REFERENCES users(id)
        ON DELETE CASCADE ON UPDATE CASCADE,
    CONSTRAINT chk_lesson_progress_status CHECK (status IN ('not_started', 'in_progress', 'completed')),
    CONSTRAINT chk_lesson_progress_duration CHECK (learning_duration_seconds >= 0)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ==========================================================
-- 10. VIDEO_PROGRESS
-- ==========================================================
CREATE TABLE video_progress (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    lesson_id BIGINT UNSIGNED NOT NULL,
    user_id BIGINT UNSIGNED NOT NULL,
    current_second INT UNSIGNED NOT NULL DEFAULT 0,
    created_at TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,

    CONSTRAINT uq_video_progress_user_lesson UNIQUE (user_id, lesson_id),
    INDEX idx_video_progress_lesson_id (lesson_id),
    INDEX idx_video_progress_user_id (user_id),
    CONSTRAINT fk_video_progress_lesson FOREIGN KEY (lesson_id) REFERENCES lessons(id)
        ON DELETE CASCADE ON UPDATE CASCADE,
    CONSTRAINT fk_video_progress_user FOREIGN KEY (user_id) REFERENCES users(id)
        ON DELETE CASCADE ON UPDATE CASCADE,
    CONSTRAINT chk_video_progress_current_second CHECK (current_second >= 0)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ==========================================================
-- 11. COUPONS
-- ==========================================================
CREATE TABLE coupons (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    user_id BIGINT UNSIGNED NULL COMMENT 'Người tạo coupon; null nếu hệ thống tạo',
    course_id BIGINT UNSIGNED NULL COMMENT 'Null nếu coupon áp dụng toàn hệ thống',
    code VARCHAR(50) NOT NULL,
    name VARCHAR(255) NOT NULL,
    description TEXT NULL,
    discount_type VARCHAR(30) NOT NULL COMMENT 'percent/fixed',
    discount_value DECIMAL(12,2) NOT NULL DEFAULT 0.00,
    max_order_amount DECIMAL(12,2) NULL COMMENT 'Mức giảm tối đa nếu discount_type=percent',
    usage_limit INT UNSIGNED NULL COMMENT 'Số lượt dùng tối đa; null = không giới hạn',
    used_count INT UNSIGNED NOT NULL DEFAULT 0,
    start_at TIMESTAMP NULL,
    end_at TIMESTAMP NULL,
    status VARCHAR(30) NOT NULL DEFAULT 'active' COMMENT 'active/inactive/expired/used_up',
    created_at TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    deleted_at TIMESTAMP NULL COMMENT 'Soft delete coupon',

    CONSTRAINT uq_coupons_code UNIQUE (code),
    INDEX idx_coupons_user_id (user_id),
    INDEX idx_coupons_course_id (course_id),
    INDEX idx_coupons_status (status),
    CONSTRAINT fk_coupons_user FOREIGN KEY (user_id) REFERENCES users(id)
        ON DELETE SET NULL ON UPDATE CASCADE,
    CONSTRAINT fk_coupons_course FOREIGN KEY (course_id) REFERENCES courses(id)
        ON DELETE CASCADE ON UPDATE CASCADE,
    CONSTRAINT chk_coupons_discount_type CHECK (discount_type IN ('percent', 'fixed')),
    CONSTRAINT chk_coupons_status CHECK (status IN ('active', 'inactive', 'expired', 'used_up')),
    CONSTRAINT chk_coupons_discount_value CHECK (discount_value >= 0),
    CONSTRAINT chk_coupons_max_amount CHECK (max_order_amount IS NULL OR max_order_amount >= 0),
    CONSTRAINT chk_coupons_usage CHECK (usage_limit IS NULL OR usage_limit >= used_count)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ==========================================================
-- 12. ORDERS
-- ==========================================================
CREATE TABLE orders (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    coupon_id BIGINT UNSIGNED NULL,
    course_id BIGINT UNSIGNED NOT NULL,
    user_id BIGINT UNSIGNED NOT NULL,
    order_code VARCHAR(50) NOT NULL COMMENT 'Mã đơn hàng duy nhất',
    status VARCHAR(30) NOT NULL DEFAULT 'pending' COMMENT 'pending/paid/cancelled/failed/expired',
    price_snapshot DECIMAL(12,2) NOT NULL DEFAULT 0.00 COMMENT 'Giá khóa học tại thời điểm mua',
    payment_method VARCHAR(50) NULL COMMENT 'bank_transfer/momo/vnpay/cash/free...',
    provider_transaction_id VARCHAR(255) NULL COMMENT 'Mã giao dịch do cổng thanh toán trả về',
    amount DECIMAL(12,2) NOT NULL DEFAULT 0.00 COMMENT 'Số tiền thực trả sau giảm giá',
    payment_status VARCHAR(30) NULL DEFAULT 'unpaid' COMMENT 'unpaid/processing/paid/failed; đã bỏ refunded theo chốt',
    paid_at TIMESTAMP NULL,
    created_at TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,

    CONSTRAINT uq_orders_order_code UNIQUE (order_code),
    CONSTRAINT uq_orders_provider_transaction UNIQUE (provider_transaction_id),
    INDEX idx_orders_coupon_id (coupon_id),
    INDEX idx_orders_course_id (course_id),
    INDEX idx_orders_user_id (user_id),
    INDEX idx_orders_status (status),
    INDEX idx_orders_payment_status (payment_status),
    CONSTRAINT fk_orders_coupon FOREIGN KEY (coupon_id) REFERENCES coupons(id)
        ON DELETE SET NULL ON UPDATE CASCADE,
    CONSTRAINT fk_orders_course FOREIGN KEY (course_id) REFERENCES courses(id)
        ON DELETE RESTRICT ON UPDATE CASCADE,
    CONSTRAINT fk_orders_user FOREIGN KEY (user_id) REFERENCES users(id)
        ON DELETE RESTRICT ON UPDATE CASCADE,
    CONSTRAINT chk_orders_status CHECK (status IN ('pending', 'paid', 'cancelled', 'failed', 'expired')),
    CONSTRAINT chk_orders_payment_status CHECK (payment_status IS NULL OR payment_status IN ('unpaid', 'processing', 'paid', 'failed')),
    CONSTRAINT chk_orders_price_snapshot CHECK (price_snapshot >= 0),
    CONSTRAINT chk_orders_amount CHECK (amount >= 0)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ==========================================================
-- 13. ENROLLMENTS
-- ==========================================================
CREATE TABLE enrollments (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    user_id BIGINT UNSIGNED NOT NULL,
    course_id BIGINT UNSIGNED NOT NULL,
    order_id BIGINT UNSIGNED NOT NULL COMMENT 'Ghi danh được sinh từ order đã paid, kể cả coupon 0đ',
    status VARCHAR(30) NOT NULL DEFAULT 'active' COMMENT 'active=đang học, completed=đã hoàn thành; đã bỏ expired/cancelled theo chốt',
    progress_percent DECIMAL(5,2) NOT NULL DEFAULT 0.00,
    enrolled_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    completed_at TIMESTAMP NULL,
    last_accessed_at TIMESTAMP NULL,
    created_at TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,

    CONSTRAINT uq_enrollments_user_course UNIQUE (user_id, course_id),
    CONSTRAINT uq_enrollments_order UNIQUE (order_id),
    INDEX idx_enrollments_user_id (user_id),
    INDEX idx_enrollments_course_id (course_id),
    INDEX idx_enrollments_status (status),
    CONSTRAINT fk_enrollments_user FOREIGN KEY (user_id) REFERENCES users(id)
        ON DELETE RESTRICT ON UPDATE CASCADE,
    CONSTRAINT fk_enrollments_course FOREIGN KEY (course_id) REFERENCES courses(id)
        ON DELETE RESTRICT ON UPDATE CASCADE,
    CONSTRAINT fk_enrollments_order FOREIGN KEY (order_id) REFERENCES orders(id)
        ON DELETE RESTRICT ON UPDATE CASCADE,
    CONSTRAINT chk_enrollments_status CHECK (status IN ('active', 'completed')),
    CONSTRAINT chk_enrollments_progress CHECK (progress_percent >= 0 AND progress_percent <= 100)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ==========================================================
-- 14. COURSE_REVIEWS
-- ==========================================================
CREATE TABLE course_reviews (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    order_id BIGINT UNSIGNED NOT NULL COMMENT 'Chứng minh người review đã mua khóa',
    rating TINYINT UNSIGNED NOT NULL,
    comment TEXT NULL,
    created_at TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    deleted_at TIMESTAMP NULL COMMENT 'Soft delete review nếu cần ẩn nhưng vẫn giữ lịch sử',

    CONSTRAINT uq_course_reviews_order UNIQUE (order_id),
    INDEX idx_course_reviews_rating (rating),
    CONSTRAINT fk_course_reviews_order FOREIGN KEY (order_id) REFERENCES orders(id)
        ON DELETE RESTRICT ON UPDATE CASCADE,
    CONSTRAINT chk_course_reviews_rating CHECK (rating BETWEEN 1 AND 5)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ==========================================================
-- 15. WISHLIST
-- ==========================================================
CREATE TABLE wishlist (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    user_id BIGINT UNSIGNED NOT NULL,
    course_id BIGINT UNSIGNED NOT NULL,
    created_at TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP,

    CONSTRAINT uq_wishlist_user_course UNIQUE (user_id, course_id),
    INDEX idx_wishlist_user_id (user_id),
    INDEX idx_wishlist_course_id (course_id),
    CONSTRAINT fk_wishlist_user FOREIGN KEY (user_id) REFERENCES users(id)
        ON DELETE CASCADE ON UPDATE CASCADE,
    CONSTRAINT fk_wishlist_course FOREIGN KEY (course_id) REFERENCES courses(id)
        ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ==========================================================
-- 16. COMMENTS
-- ==========================================================
CREATE TABLE comments (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    parent_id BIGINT UNSIGNED NULL COMMENT 'Bình luận cha nếu là reply',
    user_id BIGINT UNSIGNED NOT NULL,
    order_id BIGINT UNSIGNED NULL COMMENT 'Dùng xác minh user đã mua khóa học chứa lesson',
    lesson_id BIGINT UNSIGNED NOT NULL,
    content TEXT NOT NULL,
    status VARCHAR(30) NOT NULL DEFAULT 'visible' COMMENT 'visible/hidden/deleted; đã bỏ pending theo chốt',
    created_at TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,

    INDEX idx_comments_parent_id (parent_id),
    INDEX idx_comments_user_id (user_id),
    INDEX idx_comments_order_id (order_id),
    INDEX idx_comments_lesson_id (lesson_id),
    INDEX idx_comments_status (status),
    CONSTRAINT fk_comments_parent FOREIGN KEY (parent_id) REFERENCES comments(id)
        ON DELETE SET NULL ON UPDATE CASCADE,
    CONSTRAINT fk_comments_user FOREIGN KEY (user_id) REFERENCES users(id)
        ON DELETE RESTRICT ON UPDATE CASCADE,
    CONSTRAINT fk_comments_order FOREIGN KEY (order_id) REFERENCES orders(id)
        ON DELETE SET NULL ON UPDATE CASCADE,
    CONSTRAINT fk_comments_lesson FOREIGN KEY (lesson_id) REFERENCES lessons(id)
        ON DELETE RESTRICT ON UPDATE CASCADE,
    CONSTRAINT chk_comments_status CHECK (status IN ('visible', 'hidden', 'deleted'))
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ==========================================================
-- 17. INSTRUCTOR_PROFILES
-- ==========================================================
CREATE TABLE instructor_profiles (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    user_id BIGINT UNSIGNED NOT NULL,
    bio TEXT NULL,
    expertise TEXT NULL,
    experience_years TINYINT UNSIGNED NULL,
    level VARCHAR(50) NULL,
    created_at TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,

    CONSTRAINT uq_instructor_profiles_user UNIQUE (user_id),
    CONSTRAINT fk_instructor_profiles_user FOREIGN KEY (user_id) REFERENCES users(id)
        ON DELETE CASCADE ON UPDATE CASCADE,
    CONSTRAINT chk_instructor_profiles_experience CHECK (experience_years IS NULL OR experience_years <= 80)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ==========================================================
-- 18. REVENUES
-- ==========================================================
CREATE TABLE revenues (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    instructor_id BIGINT UNSIGNED NOT NULL,
    course_id BIGINT UNSIGNED NOT NULL,
    order_id BIGINT UNSIGNED NOT NULL,
    gross_amount DECIMAL(12,2) NOT NULL DEFAULT 0.00 COMMENT 'Tổng tiền đơn hàng',
    instructor_amount DECIMAL(12,2) NOT NULL DEFAULT 0.00 COMMENT 'Tiền giảng viên nhận',
    platform_fee_amount DECIMAL(12,2) NOT NULL DEFAULT 0.00 COMMENT 'Phí nền tảng',
    status VARCHAR(30) NOT NULL DEFAULT 'pending' COMMENT 'pending/available/withdrawn/cancelled',
    earned_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    created_at TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP,

    CONSTRAINT uq_revenues_order UNIQUE (order_id),
    INDEX idx_revenues_instructor_id (instructor_id),
    INDEX idx_revenues_course_id (course_id),
    INDEX idx_revenues_status (status),
    CONSTRAINT fk_revenues_instructor FOREIGN KEY (instructor_id) REFERENCES users(id)
        ON DELETE RESTRICT ON UPDATE CASCADE,
    CONSTRAINT fk_revenues_course FOREIGN KEY (course_id) REFERENCES courses(id)
        ON DELETE RESTRICT ON UPDATE CASCADE,
    CONSTRAINT fk_revenues_order FOREIGN KEY (order_id) REFERENCES orders(id)
        ON DELETE RESTRICT ON UPDATE CASCADE,
    CONSTRAINT chk_revenues_status CHECK (status IN ('pending', 'available', 'withdrawn', 'cancelled')),
    CONSTRAINT chk_revenues_amounts CHECK (gross_amount >= 0 AND instructor_amount >= 0 AND platform_fee_amount >= 0)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ==========================================================
-- 19. PAYOUT_ACCOUNTS
-- ==========================================================
CREATE TABLE payout_accounts (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    user_id BIGINT UNSIGNED NOT NULL,
    provider VARCHAR(50) NOT NULL COMMENT 'bank/momo/paypal...',
    account_number VARCHAR(100) NOT NULL,
    account_name VARCHAR(255) NOT NULL,
    connected_at TIMESTAMP NULL,
    status VARCHAR(30) NOT NULL DEFAULT 'pending_verification' COMMENT 'active/inactive/pending_verification/rejected',
    created_at TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    deleted_at TIMESTAMP NULL COMMENT 'Soft delete payout account; không xóa cứng dữ liệu tài chính',

    CONSTRAINT uq_payout_accounts_identity UNIQUE (user_id, provider, account_number),
    INDEX idx_payout_accounts_user_id (user_id),
    INDEX idx_payout_accounts_status (status),
    CONSTRAINT fk_payout_accounts_user FOREIGN KEY (user_id) REFERENCES users(id)
        ON DELETE RESTRICT ON UPDATE CASCADE,
    CONSTRAINT chk_payout_accounts_status CHECK (status IN ('active', 'inactive', 'pending_verification', 'rejected'))
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ==========================================================
-- 20. WITHDRAW_REQUESTS
-- ==========================================================
CREATE TABLE withdraw_requests (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    user_id BIGINT UNSIGNED NOT NULL,
    payout_account_id BIGINT UNSIGNED NOT NULL,
    amount DECIMAL(12,2) NOT NULL,
    status VARCHAR(30) NOT NULL DEFAULT 'pending' COMMENT 'pending/approved/rejected/paid/cancelled',
    requested_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    approved_at TIMESTAMP NULL,
    paid_at TIMESTAMP NULL,
    rejected_reason TEXT NULL,
    provider_payout_id VARCHAR(255) NULL,
    account_number_snapshot VARCHAR(100) NOT NULL COMMENT 'Snapshot STK tại lúc yêu cầu rút',
    account_name_snapshot VARCHAR(255) NOT NULL COMMENT 'Snapshot tên chủ TK tại lúc yêu cầu rút',
    created_at TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,

    INDEX idx_withdraw_requests_user_id (user_id),
    INDEX idx_withdraw_requests_payout_account_id (payout_account_id),
    INDEX idx_withdraw_requests_status (status),
    CONSTRAINT fk_withdraw_requests_user FOREIGN KEY (user_id) REFERENCES users(id)
        ON DELETE RESTRICT ON UPDATE CASCADE,
    CONSTRAINT fk_withdraw_requests_payout_account FOREIGN KEY (payout_account_id) REFERENCES payout_accounts(id)
        ON DELETE RESTRICT ON UPDATE CASCADE,
    CONSTRAINT chk_withdraw_requests_status CHECK (status IN ('pending', 'approved', 'rejected', 'paid', 'cancelled')),
    CONSTRAINT chk_withdraw_requests_amount CHECK (amount > 0)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ==========================================================
-- 21. BANNERS
-- ==========================================================
CREATE TABLE banners (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    title VARCHAR(255) NOT NULL,
    image_url VARCHAR(500) NOT NULL,
    target_url VARCHAR(500) NULL,
    position VARCHAR(50) NOT NULL DEFAULT 'home',
    sort_order INT UNSIGNED NOT NULL DEFAULT 0,
    start_at TIMESTAMP NULL,
    end_at TIMESTAMP NULL,
    status VARCHAR(30) NOT NULL DEFAULT 'inactive' COMMENT 'active=đang hiển thị, inactive=đang ẩn',
    created_at TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    deleted_at TIMESTAMP NULL COMMENT 'Soft delete banner',

    INDEX idx_banners_position_status (position, status),
    INDEX idx_banners_sort_order (sort_order),
    CONSTRAINT chk_banners_status CHECK (status IN ('active', 'inactive'))
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ==========================================================
-- 22. FAQS
-- ==========================================================
CREATE TABLE faqs (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    question VARCHAR(500) NOT NULL,
    answer TEXT NOT NULL,
    type VARCHAR(50) NOT NULL DEFAULT 'general',
    status VARCHAR(30) NOT NULL DEFAULT 'active' COMMENT 'active/inactive',
    sort_order INT UNSIGNED NOT NULL DEFAULT 0,
    created_at TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    deleted_at TIMESTAMP NULL COMMENT 'Soft delete FAQ để còn đối chứng nội dung câu trả lời',

    INDEX idx_faqs_type_status (type, status),
    INDEX idx_faqs_sort_order (sort_order),
    CONSTRAINT chk_faqs_status CHECK (status IN ('active', 'inactive'))
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ==========================================================
-- 23. COURSE_FAQS
-- Soft delete according to final decision, to keep evidence of which FAQ was linked to a course.
-- ==========================================================
CREATE TABLE course_faqs (
    faq_id BIGINT UNSIGNED NOT NULL,
    course_id BIGINT UNSIGNED NOT NULL,
    sort_order INT UNSIGNED NOT NULL DEFAULT 0,
    created_at TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP,
    deleted_at TIMESTAMP NULL COMMENT 'Soft delete liên kết FAQ-course để còn đối chất nếu có tranh chấp',

    PRIMARY KEY (faq_id, course_id),
    CONSTRAINT uq_course_faqs_course_sort UNIQUE (course_id, sort_order),
    INDEX idx_course_faqs_course_id (course_id),
    INDEX idx_course_faqs_deleted_at (deleted_at),
    CONSTRAINT fk_course_faqs_faq FOREIGN KEY (faq_id) REFERENCES faqs(id)
        ON DELETE RESTRICT ON UPDATE CASCADE,
    CONSTRAINT fk_course_faqs_course FOREIGN KEY (course_id) REFERENCES courses(id)
        ON DELETE RESTRICT ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ==========================================================
-- 24. QUIZZES
-- ==========================================================
CREATE TABLE quizzes (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    course_id BIGINT UNSIGNED NOT NULL,
    lesson_id BIGINT UNSIGNED NULL,
    title VARCHAR(255) NOT NULL,
    description TEXT NULL,
    passing_score DECIMAL(6,2) NOT NULL DEFAULT 0.00,
    status VARCHAR(30) NOT NULL DEFAULT 'draft' COMMENT 'draft/published/hidden',
    created_at TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    deleted_at TIMESTAMP NULL COMMENT 'Soft delete quiz',

    INDEX idx_quizzes_course_id (course_id),
    INDEX idx_quizzes_lesson_id (lesson_id),
    INDEX idx_quizzes_status (status),
    CONSTRAINT fk_quizzes_course FOREIGN KEY (course_id) REFERENCES courses(id)
        ON DELETE CASCADE ON UPDATE CASCADE,
    CONSTRAINT fk_quizzes_lesson FOREIGN KEY (lesson_id) REFERENCES lessons(id)
        ON DELETE SET NULL ON UPDATE CASCADE,
    CONSTRAINT chk_quizzes_status CHECK (status IN ('draft', 'published', 'hidden')),
    CONSTRAINT chk_quizzes_passing_score CHECK (passing_score >= 0)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ==========================================================
-- 25. QUIZ_QUESTIONS
-- ==========================================================
CREATE TABLE quiz_questions (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    quiz_id BIGINT UNSIGNED NOT NULL,
    question_text TEXT NOT NULL,
    question_type VARCHAR(30) NOT NULL DEFAULT 'single_choice' COMMENT 'single_choice/multiple_choice/true_false',
    score DECIMAL(6,2) NOT NULL DEFAULT 1.00,
    sort_order INT UNSIGNED NOT NULL DEFAULT 0,
    explanation TEXT NULL,
    created_at TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP,

    CONSTRAINT uq_quiz_questions_sort UNIQUE (quiz_id, sort_order),
    INDEX idx_quiz_questions_quiz_id (quiz_id),
    CONSTRAINT fk_quiz_questions_quiz FOREIGN KEY (quiz_id) REFERENCES quizzes(id)
        ON DELETE CASCADE ON UPDATE CASCADE,
    CONSTRAINT chk_quiz_questions_type CHECK (question_type IN ('single_choice', 'multiple_choice', 'true_false')),
    CONSTRAINT chk_quiz_questions_score CHECK (score >= 0)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ==========================================================
-- 26. QUIZ_OPTIONS
-- ==========================================================
CREATE TABLE quiz_options (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    question_id BIGINT UNSIGNED NOT NULL,
    option_text TEXT NOT NULL,
    is_correct BOOLEAN NOT NULL DEFAULT FALSE,
    sort_order INT UNSIGNED NOT NULL DEFAULT 0,
    created_at TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP,

    CONSTRAINT uq_quiz_options_sort UNIQUE (question_id, sort_order),
    INDEX idx_quiz_options_question_id (question_id),
    CONSTRAINT fk_quiz_options_question FOREIGN KEY (question_id) REFERENCES quiz_questions(id)
        ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ==========================================================
-- 27. QUIZ_ATTEMPTS
-- ==========================================================
CREATE TABLE quiz_attempts (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    quiz_id BIGINT UNSIGNED NOT NULL,
    user_id BIGINT UNSIGNED NOT NULL,
    attempt_number INT UNSIGNED NOT NULL DEFAULT 1,
    score DECIMAL(6,2) NULL,
    total_score DECIMAL(6,2) NOT NULL DEFAULT 0.00,
    passed BOOLEAN NOT NULL DEFAULT FALSE,
    status VARCHAR(30) NOT NULL DEFAULT 'in_progress' COMMENT 'in_progress/submitted; đã bỏ graded và cancelled theo chốt',
    started_at TIMESTAMP NULL,
    submitted_at TIMESTAMP NULL,
    created_at TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP,

    CONSTRAINT uq_quiz_attempts_number UNIQUE (quiz_id, user_id, attempt_number),
    INDEX idx_quiz_attempts_quiz_id (quiz_id),
    INDEX idx_quiz_attempts_user_id (user_id),
    INDEX idx_quiz_attempts_status (status),
    CONSTRAINT fk_quiz_attempts_quiz FOREIGN KEY (quiz_id) REFERENCES quizzes(id)
        ON DELETE RESTRICT ON UPDATE CASCADE,
    CONSTRAINT fk_quiz_attempts_user FOREIGN KEY (user_id) REFERENCES users(id)
        ON DELETE RESTRICT ON UPDATE CASCADE,
    CONSTRAINT chk_quiz_attempts_status CHECK (status IN ('in_progress', 'submitted')),
    CONSTRAINT chk_quiz_attempts_scores CHECK ((score IS NULL OR score >= 0) AND total_score >= 0)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ==========================================================
-- 28. QUIZ_ATTEMPT_ANSWERS
-- ==========================================================
CREATE TABLE quiz_attempt_answers (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    question_id BIGINT UNSIGNED NOT NULL,
    attempt_id BIGINT UNSIGNED NOT NULL,
    option_id BIGINT UNSIGNED NULL COMMENT 'Null nếu câu hỏi không có option phù hợp hoặc cần mở rộng sau',
    is_correct BOOLEAN NOT NULL DEFAULT FALSE,
    score_earned DECIMAL(6,2) NOT NULL DEFAULT 0.00,
    created_at TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP,

    CONSTRAINT uq_quiz_attempt_answers_question UNIQUE (attempt_id, question_id),
    INDEX idx_quiz_attempt_answers_question_id (question_id),
    INDEX idx_quiz_attempt_answers_attempt_id (attempt_id),
    INDEX idx_quiz_attempt_answers_option_id (option_id),
    CONSTRAINT fk_quiz_attempt_answers_question FOREIGN KEY (question_id) REFERENCES quiz_questions(id)
        ON DELETE RESTRICT ON UPDATE CASCADE,
    CONSTRAINT fk_quiz_attempt_answers_attempt FOREIGN KEY (attempt_id) REFERENCES quiz_attempts(id)
        ON DELETE CASCADE ON UPDATE CASCADE,
    CONSTRAINT fk_quiz_attempt_answers_option FOREIGN KEY (option_id) REFERENCES quiz_options(id)
        ON DELETE SET NULL ON UPDATE CASCADE,
    CONSTRAINT chk_quiz_attempt_answers_score CHECK (score_earned >= 0)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ==========================================================
-- BUSINESS NOTES
-- ==========================================================
-- 1. Database handles hard constraints: FK, unique, check, default, indexes.
-- 2. Laravel Service/Policy should handle business flows:
--    - Only published courses can be purchased.
--    - Only paid orders can create enrollments/revenues.
--    - Coupon validation and used_count increment.
--    - Status transition validation.
--    - Permission checks for instructor/admin/member.
-- 3. Financial/history tables should not be hard-deleted:
--    orders, revenues, withdraw_requests, enrollments, quiz_attempts.
-- 4. Snapshot fields currently kept:
--    orders.price_snapshot, orders.amount,
--    revenues.gross_amount/instructor_amount/platform_fee_amount,
--    withdraw_requests.account_number_snapshot/account_name_snapshot.
