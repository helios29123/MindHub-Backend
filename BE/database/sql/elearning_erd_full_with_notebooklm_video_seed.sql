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


-- ==========================================================
-- ADD-ON TABLES APPENDED BELOW - DB cũ giữ nguyên, chỉ thêm bảng mới đã chốt
-- ==========================================================

-- ==========================================================
-- MindHub GD1 Add-on Schema
-- Purpose: Bổ sung các bảng đã chốt, không sửa bảng/cột/status của DB cũ
-- Run after: elearning_erd_gd1.sql
-- Target: MySQL 8.x / MariaDB compatible as much as possible
-- Charset: utf8mb4
-- ==========================================================

SET NAMES utf8mb4;
SET FOREIGN_KEY_CHECKS = 0;

-- Chỉ drop các bảng add-on mới nếu cần chạy lại file này trong môi trường dev/test.
-- Không drop/sửa bất kỳ bảng cũ nào của GD1.
DROP TABLE IF EXISTS ai_lesson_summaries;
DROP TABLE IF EXISTS user_category_interests;
DROP TABLE IF EXISTS lesson_notes;
DROP TABLE IF EXISTS certificates;
DROP TABLE IF EXISTS notifications;

SET FOREIGN_KEY_CHECKS = 1;

-- ==========================================================
-- 1. NOTIFICATIONS
-- Ghi chú đã chốt: không dùng enum/check enum cho notification.
-- Type/channel/email_status để dạng VARCHAR, validate ở Laravel Service/FormRequest.
-- ==========================================================
CREATE TABLE notifications (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    user_id BIGINT UNSIGNED NOT NULL COMMENT 'Người nhận thông báo',
    type VARCHAR(50) NOT NULL COMMENT 'Loại thông báo, validate ở code, không dùng enum DB',
    title VARCHAR(255) NOT NULL COMMENT 'Tiêu đề thông báo',
    message TEXT NOT NULL COMMENT 'Nội dung thông báo',
    data JSON NULL COMMENT 'Dữ liệu điều hướng/phụ trợ như order_id, course_id, lesson_id',
    action_url VARCHAR(500) NULL COMMENT 'URL điều hướng khi người dùng bấm thông báo',
    channel VARCHAR(30) NOT NULL DEFAULT 'database' COMMENT 'Kênh thông báo, validate ở code, không dùng enum DB',
    read_at TIMESTAMP NULL COMMENT 'NULL = chưa đọc, có giá trị = đã đọc',
    email_to VARCHAR(255) NULL COMMENT 'Email người nhận nếu có gửi mail',
    email_status VARCHAR(30) NULL COMMENT 'Trạng thái gửi email, validate ở code, không dùng enum DB',
    email_sent_at TIMESTAMP NULL COMMENT 'Thời điểm gửi email thành công',
    email_error TEXT NULL COMMENT 'Lỗi gửi email nếu thất bại',
    created_at TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    deleted_at TIMESTAMP NULL COMMENT 'Soft delete notification',

    INDEX idx_notifications_user_id (user_id),
    INDEX idx_notifications_user_read_at (user_id, read_at),
    INDEX idx_notifications_type (type),
    INDEX idx_notifications_email_status (email_status),
    INDEX idx_notifications_created_at (created_at),
    CONSTRAINT fk_notifications_user FOREIGN KEY (user_id) REFERENCES users(id)
        ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ==========================================================
-- 2. CERTIFICATES
-- Ghi chú đã chốt: mỗi enrollment chỉ có 1 certificate.
-- ==========================================================
CREATE TABLE certificates (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    user_id BIGINT UNSIGNED NOT NULL COMMENT 'Học viên được cấp chứng chỉ',
    course_id BIGINT UNSIGNED NOT NULL COMMENT 'Khóa học được cấp chứng chỉ',
    enrollment_id BIGINT UNSIGNED NOT NULL COMMENT 'Enrollment đã hoàn thành, mỗi enrollment chỉ có một certificate',
    certificate_code VARCHAR(100) NOT NULL COMMENT 'Mã chứng chỉ duy nhất dùng để xác thực',
    certificate_url VARCHAR(500) NULL COMMENT 'URL file chứng chỉ nếu đã render PDF/image',
    issued_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP COMMENT 'Thời điểm cấp chứng chỉ',
    status VARCHAR(30) NOT NULL DEFAULT 'active' COMMENT 'active=hiệu lực, revoked=thu hồi',
    created_at TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    deleted_at TIMESTAMP NULL COMMENT 'Soft delete certificate',

    CONSTRAINT uq_certificates_code UNIQUE (certificate_code),
    CONSTRAINT uq_certificates_enrollment UNIQUE (enrollment_id),
    INDEX idx_certificates_user_id (user_id),
    INDEX idx_certificates_course_id (course_id),
    INDEX idx_certificates_status (status),
    INDEX idx_certificates_issued_at (issued_at),
    CONSTRAINT fk_certificates_user FOREIGN KEY (user_id) REFERENCES users(id)
        ON DELETE RESTRICT ON UPDATE CASCADE,
    CONSTRAINT fk_certificates_course FOREIGN KEY (course_id) REFERENCES courses(id)
        ON DELETE RESTRICT ON UPDATE CASCADE,
    CONSTRAINT fk_certificates_enrollment FOREIGN KEY (enrollment_id) REFERENCES enrollments(id)
        ON DELETE RESTRICT ON UPDATE CASCADE,
    CONSTRAINT chk_certificates_status CHECK (status IN ('active', 'revoked'))
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ==========================================================
-- 3. LESSON_NOTES
-- Ghi chú đã chốt: cho phép nhiều note trong cùng một lesson.
-- ==========================================================
CREATE TABLE lesson_notes (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    user_id BIGINT UNSIGNED NOT NULL COMMENT 'Người tạo ghi chú',
    course_id BIGINT UNSIGNED NOT NULL COMMENT 'Khóa học chứa lesson',
    lesson_id BIGINT UNSIGNED NOT NULL COMMENT 'Bài học được ghi chú',
    content TEXT NOT NULL COMMENT 'Nội dung ghi chú cá nhân',
    note_time_second INT UNSIGNED NULL COMMENT 'Mốc thời gian video, null nếu không gắn với video time',
    created_at TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    deleted_at TIMESTAMP NULL COMMENT 'Soft delete note',

    INDEX idx_lesson_notes_user_lesson (user_id, lesson_id),
    INDEX idx_lesson_notes_user_course (user_id, course_id),
    INDEX idx_lesson_notes_lesson_id (lesson_id),
    INDEX idx_lesson_notes_note_time_second (note_time_second),
    CONSTRAINT fk_lesson_notes_user FOREIGN KEY (user_id) REFERENCES users(id)
        ON DELETE CASCADE ON UPDATE CASCADE,
    CONSTRAINT fk_lesson_notes_course FOREIGN KEY (course_id) REFERENCES courses(id)
        ON DELETE CASCADE ON UPDATE CASCADE,
    CONSTRAINT fk_lesson_notes_lesson FOREIGN KEY (lesson_id) REFERENCES lessons(id)
        ON DELETE CASCADE ON UPDATE CASCADE,
    CONSTRAINT chk_lesson_notes_time CHECK (note_time_second IS NULL OR note_time_second >= 0)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ==========================================================
-- 4. USER_CATEGORY_INTERESTS
-- Ghi chú đã chốt: không cần deleted_at.
-- ==========================================================
CREATE TABLE user_category_interests (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    user_id BIGINT UNSIGNED NOT NULL COMMENT 'Người dùng chọn chủ đề quan tâm',
    category_id BIGINT UNSIGNED NOT NULL COMMENT 'Danh mục/chủ đề quan tâm',
    interest_level TINYINT UNSIGNED NOT NULL DEFAULT 3 COMMENT 'Mức độ quan tâm từ 1 đến 5',
    created_at TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,

    CONSTRAINT uq_user_category_interests_user_category UNIQUE (user_id, category_id),
    INDEX idx_user_category_interests_user_id (user_id),
    INDEX idx_user_category_interests_category_id (category_id),
    INDEX idx_user_category_interests_level (interest_level),
    CONSTRAINT fk_user_category_interests_user FOREIGN KEY (user_id) REFERENCES users(id)
        ON DELETE CASCADE ON UPDATE CASCADE,
    CONSTRAINT fk_user_category_interests_category FOREIGN KEY (category_id) REFERENCES categories(id)
        ON DELETE CASCADE ON UPDATE CASCADE,
    CONSTRAINT chk_user_category_interests_level CHECK (interest_level BETWEEN 1 AND 5)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ==========================================================
-- 5. AI_LESSON_SUMMARIES
-- Ghi chú đã chốt: thêm summary_type, language, source_content_hash để cache AI đúng ngữ cảnh.
-- ==========================================================
CREATE TABLE ai_lesson_summaries (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    lesson_id BIGINT UNSIGNED NOT NULL COMMENT 'Lesson được AI tóm tắt',
    course_id BIGINT UNSIGNED NOT NULL COMMENT 'Course chứa lesson',
    summary LONGTEXT NOT NULL COMMENT 'Nội dung tóm tắt do AI sinh ra',
    key_points JSON NULL COMMENT 'Danh sách ý chính dạng JSON nếu có',
    summary_type VARCHAR(30) NOT NULL DEFAULT 'short' COMMENT 'short/detailed/bullet',
    language VARCHAR(10) NOT NULL DEFAULT 'vi' COMMENT 'Ngôn ngữ tóm tắt, ví dụ vi/en',
    source_content_hash VARCHAR(64) NOT NULL COMMENT 'SHA-256 hash của nội dung lesson.content tại thời điểm tạo summary',
    model_name VARCHAR(100) NULL COMMENT 'Tên model AI đã dùng',
    generated_by_user_id BIGINT UNSIGNED NULL COMMENT 'User kích hoạt tạo summary; null nếu system tạo',
    generated_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP COMMENT 'Thời điểm sinh summary',
    created_at TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,

    CONSTRAINT uq_ai_lesson_summaries_cache UNIQUE (lesson_id, summary_type, language, source_content_hash),
    INDEX idx_ai_lesson_summaries_lesson_id (lesson_id),
    INDEX idx_ai_lesson_summaries_course_id (course_id),
    INDEX idx_ai_lesson_summaries_lookup (lesson_id, summary_type, language),
    INDEX idx_ai_lesson_summaries_generated_at (generated_at),
    CONSTRAINT fk_ai_lesson_summaries_lesson FOREIGN KEY (lesson_id) REFERENCES lessons(id)
        ON DELETE CASCADE ON UPDATE CASCADE,
    CONSTRAINT fk_ai_lesson_summaries_course FOREIGN KEY (course_id) REFERENCES courses(id)
        ON DELETE CASCADE ON UPDATE CASCADE,
    CONSTRAINT fk_ai_lesson_summaries_generated_by_user FOREIGN KEY (generated_by_user_id) REFERENCES users(id)
        ON DELETE SET NULL ON UPDATE CASCADE,
    CONSTRAINT chk_ai_lesson_summaries_type CHECK (summary_type IN ('short', 'detailed', 'bullet')),
    CONSTRAINT chk_ai_lesson_summaries_language CHECK (language IN ('vi', 'en')),
    CONSTRAINT chk_ai_lesson_summaries_hash_length CHECK (CHAR_LENGTH(source_content_hash) = 64)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ==========================================================
-- ADD-ON BUSINESS NOTES
-- ==========================================================
-- 1. File này chỉ bổ sung bảng mới, không sửa bảng/cột/status của GD1 cũ.
-- 2. notifications không dùng enum/check enum ở DB; type/channel/email_status validate ở Laravel.
-- 3. certificates: mỗi enrollment chỉ được cấp 1 certificate qua uq_certificates_enrollment.
-- 4. lesson_notes: một user có thể tạo nhiều note trong cùng lesson.
-- 5. user_category_interests: không có deleted_at; bỏ quan tâm thì xóa dòng.
-- 6. ai_lesson_summaries: cache theo lesson_id + summary_type + language + source_content_hash.
-- 7. Quyền truy cập, ownership, status flow chi tiết xử lý ở Laravel Service/Policy.


-- ==========================================================
-- MindHub GD1 Demo Seed Data
-- Run after: elearning_erd_full.sql
-- Purpose: dữ liệu mẫu để trình chiếu sản phẩm và test API GD1
-- Demo password for normal password users: 12345678
-- Lưu ý: chỉ dùng môi trường dev/test/demo, không dùng production.
-- ==========================================================

SET NAMES utf8mb4;
SET FOREIGN_KEY_CHECKS = 0;

TRUNCATE TABLE ai_lesson_summaries;

TRUNCATE TABLE user_category_interests;

TRUNCATE TABLE lesson_notes;

TRUNCATE TABLE certificates;

TRUNCATE TABLE notifications;

TRUNCATE TABLE quiz_attempt_answers;

TRUNCATE TABLE quiz_attempts;

TRUNCATE TABLE quiz_options;

TRUNCATE TABLE quiz_questions;

TRUNCATE TABLE quizzes;

TRUNCATE TABLE course_faqs;

TRUNCATE TABLE faqs;

TRUNCATE TABLE banners;

TRUNCATE TABLE withdraw_requests;

TRUNCATE TABLE payout_accounts;

TRUNCATE TABLE revenues;

TRUNCATE TABLE instructor_profiles;

TRUNCATE TABLE comments;

TRUNCATE TABLE wishlist;

TRUNCATE TABLE course_reviews;

TRUNCATE TABLE enrollments;

TRUNCATE TABLE orders;

TRUNCATE TABLE coupons;

TRUNCATE TABLE video_progress;

TRUNCATE TABLE lesson_progress;

TRUNCATE TABLE lesson_assets;

TRUNCATE TABLE lessons;

TRUNCATE TABLE course_sections;

TRUNCATE TABLE course_categories;

TRUNCATE TABLE courses;

TRUNCATE TABLE categories;

TRUNCATE TABLE sessions;

TRUNCATE TABLE users;

SET FOREIGN_KEY_CHECKS = 1;


INSERT INTO users (id, full_name, email, password_hash, phone, oauth_account_login, role, status, email_verified_at, last_login_at, locked, locked_reason, password_reset, created_at, updated_at, deleted_at) VALUES
    (1, 'MindHub Admin', 'admin@mindhub.test', '$2y$12$j6h5YFXMJndp1aI7e1GCgeFc.pUUPNyT/tW8bzfHpxz03sUG.OXXC', '0900000001', NULL, 'admin', 'active', '2026-01-05 08:00:00', '2026-06-23 08:10:00', 0, NULL, NULL, '2026-01-01 08:00:00', '2026-06-23 08:10:00', NULL),
    (2, 'Nguyễn Minh Khoa', 'instructor1@mindhub.test', '$2y$12$j6h5YFXMJndp1aI7e1GCgeFc.pUUPNyT/tW8bzfHpxz03sUG.OXXC', '0900000002', NULL, 'instructor', 'active', '2026-01-05 08:05:00', '2026-06-22 19:20:00', 0, NULL, NULL, '2026-01-01 08:05:00', '2026-06-22 19:20:00', NULL),
    (3, 'Trần Hà Linh', 'instructor2@mindhub.test', '$2y$12$j6h5YFXMJndp1aI7e1GCgeFc.pUUPNyT/tW8bzfHpxz03sUG.OXXC', '0900000003', NULL, 'instructor', 'active', '2026-01-05 08:10:00', '2026-06-21 16:45:00', 0, NULL, NULL, '2026-01-01 08:10:00', '2026-06-21 16:45:00', NULL),
    (4, 'Lê Gia Bảo', 'learner1@mindhub.test', '$2y$12$j6h5YFXMJndp1aI7e1GCgeFc.pUUPNyT/tW8bzfHpxz03sUG.OXXC', '0900000004', NULL, 'learner', 'active', '2026-01-05 09:00:00', '2026-06-23 07:40:00', 0, NULL, NULL, '2026-01-02 09:00:00', '2026-06-23 07:40:00', NULL),
    (5, 'Phạm Anh Thư', 'learner2@mindhub.test', '$2y$12$j6h5YFXMJndp1aI7e1GCgeFc.pUUPNyT/tW8bzfHpxz03sUG.OXXC', '0900000005', NULL, 'learner', 'active', '2026-01-05 09:05:00', '2026-06-22 21:00:00', 0, NULL, NULL, '2026-01-02 09:05:00', '2026-06-22 21:00:00', NULL),
    (6, 'Đỗ Hoàng Nam', 'learner.completed@mindhub.test', '$2y$12$j6h5YFXMJndp1aI7e1GCgeFc.pUUPNyT/tW8bzfHpxz03sUG.OXXC', '0900000006', NULL, 'learner', 'active', '2026-01-05 09:10:00', '2026-06-20 10:30:00', 0, NULL, NULL, '2026-01-02 09:10:00', '2026-06-20 10:30:00', NULL),
    (7, 'Tài khoản bị khóa', 'locked@mindhub.test', '$2y$12$j6h5YFXMJndp1aI7e1GCgeFc.pUUPNyT/tW8bzfHpxz03sUG.OXXC', '0900000007', NULL, 'learner', 'locked', '2026-01-05 09:15:00', '2026-04-01 10:00:00', 1, 'Demo tài khoản bị khóa để test active.user middleware.', NULL, '2026-01-02 09:15:00', '2026-04-01 10:00:00', NULL),
    (8, 'Tài khoản inactive', 'inactive@mindhub.test', '$2y$12$j6h5YFXMJndp1aI7e1GCgeFc.pUUPNyT/tW8bzfHpxz03sUG.OXXC', '0900000008', NULL, 'learner', 'inactive', NULL, NULL, 0, NULL, NULL, '2026-01-02 09:20:00', '2026-01-02 09:20:00', NULL),
    (9, 'OAuth Only Learner', 'oauth.only@mindhub.test', NULL, NULL, 'google-oauth-demo-9001', 'learner', 'active', '2026-01-05 09:25:00', '2026-06-10 11:00:00', 0, NULL, NULL, '2026-01-02 09:25:00', '2026-06-10 11:00:00', NULL),
    (10, 'Learner Device Limit', 'learner.limit@mindhub.test', '$2y$12$j6h5YFXMJndp1aI7e1GCgeFc.pUUPNyT/tW8bzfHpxz03sUG.OXXC', '0900000010', NULL, 'learner', 'active', '2026-01-05 09:30:00', '2026-06-23 06:30:00', 0, NULL, NULL, '2026-01-02 09:30:00', '2026-06-23 06:30:00', NULL),
    (11, 'Learner Empty State', 'learner.empty@mindhub.test', '$2y$12$j6h5YFXMJndp1aI7e1GCgeFc.pUUPNyT/tW8bzfHpxz03sUG.OXXC', '0900000011', NULL, 'learner', 'active', '2026-01-05 09:35:00', NULL, 0, NULL, NULL, '2026-01-02 09:35:00', '2026-01-02 09:35:00', NULL);

INSERT INTO sessions (id, user_id, refresh_token_hash, device_name, ip_address, user_agent, expires_at, revoked_at, created_at) VALUES
    (1, 4, 'demo_hash_learner1_active_chrome', 'Chrome on Windows', '127.0.0.1', 'Mozilla/5.0 Chrome MindHub Demo', '2026-07-23 23:59:59', NULL, '2026-06-23 07:40:00'),
    (2, 4, 'demo_hash_learner1_expired_mobile', 'Safari on iPhone', '10.0.0.12', 'Mobile Safari MindHub Demo', '2026-05-01 23:59:59', NULL, '2026-04-01 08:00:00'),
    (3, 4, 'demo_hash_learner1_revoked_edge', 'Edge on Windows', '10.0.0.13', 'Microsoft Edge MindHub Demo', '2026-07-01 23:59:59', '2026-06-01 09:00:00', '2026-05-20 08:00:00'),
    (4, 5, 'demo_hash_learner2_active_chrome', 'Chrome on MacBook', '10.0.0.14', 'Mozilla/5.0 Chrome macOS MindHub Demo', '2026-07-22 23:59:59', NULL, '2026-06-22 21:00:00'),
    (5, 7, 'demo_hash_locked_user_active', 'Firefox on Windows', '10.0.0.15', 'Firefox MindHub Demo', '2026-07-01 23:59:59', NULL, '2026-04-01 10:00:00'),
    (6, 10, 'demo_hash_limit_device_1', 'Chrome on Windows', '10.0.0.21', 'Chrome MindHub Demo', '2026-07-23 23:59:59', NULL, '2026-06-23 06:00:00'),
    (7, 10, 'demo_hash_limit_device_2', 'Safari on iPad', '10.0.0.22', 'Safari iPad MindHub Demo', '2026-07-23 23:59:59', NULL, '2026-06-23 06:10:00'),
    (8, 10, 'demo_hash_limit_expired', 'Old Android', '10.0.0.23', 'Android WebView MindHub Demo', '2026-03-01 23:59:59', NULL, '2026-02-01 12:00:00');

INSERT INTO categories (id, parent_id, name, slug, description, sort_order, status, created_at, updated_at, deleted_at) VALUES
    (1, NULL, 'Lập trình', 'lap-trinh', 'Các khóa học lập trình từ cơ bản đến nâng cao.', 1, 'active', '2026-01-03 08:00:00', '2026-01-03 08:00:00', NULL),
    (2, 1, 'Web Development', 'web-development', 'Frontend, backend và full-stack web.', 1, 'active', '2026-01-03 08:05:00', '2026-01-03 08:05:00', NULL),
    (3, 1, 'Backend', 'backend', 'API, cơ sở dữ liệu, bảo mật backend.', 2, 'active', '2026-01-03 08:10:00', '2026-01-03 08:10:00', NULL),
    (4, 1, 'Frontend', 'frontend', 'Giao diện người dùng hiện đại.', 3, 'active', '2026-01-03 08:15:00', '2026-01-03 08:15:00', NULL),
    (5, NULL, 'AI và Dữ liệu', 'ai-va-du-lieu', 'AI ứng dụng, phân tích dữ liệu và tự động hóa.', 2, 'active', '2026-01-03 08:20:00', '2026-01-03 08:20:00', NULL),
    (6, NULL, 'DevOps', 'devops', 'Triển khai, Docker, CI/CD và vận hành.', 3, 'active', '2026-01-03 08:25:00', '2026-01-03 08:25:00', NULL),
    (7, NULL, 'Kinh doanh số', 'kinh-doanh-so', 'Danh mục inactive để test filter public.', 4, 'inactive', '2026-01-03 08:30:00', '2026-01-03 08:30:00', NULL);

INSERT INTO courses (id, instructor_id, title, slug, short_description, description, thumbnail_url, intro_video_url, price, sale_price, level, language, requirements, outcomes, status, is_featured, total_duration_seconds, published_at, admin_reject_reason, created_at, updated_at, deleted_at) VALUES
    (1, 2, 'Laravel REST API từ cơ bản đến triển khai', 'laravel-rest-api-tu-co-ban-den-trien-khai', 'Xây dựng REST API Laravel theo Repository/Service, auth session custom và payment flow.', 'Khóa học thực chiến dành cho sinh viên làm đồ án và junior backend muốn nắm quy trình xây dựng API e-learning bằng Laravel.', '/demo/courses/laravel-rest-api.jpg', '/demo/videos/laravel-intro.mp4', 499000.0, 299000.0, 'beginner', 'vi', 'Biết PHP cơ bản, đã cài Laragon hoặc môi trường PHP/MySQL.', 'Thiết kế API chuẩn, xử lý auth, payment, learning progress và test bằng Postman/PowerShell.', 'published', 1, 12600, '2026-02-01 09:00:00', NULL, '2026-01-10 08:00:00', '2026-06-20 10:00:00', NULL),
    (2, 2, 'PHP & MySQL nền tảng cho Backend', 'php-mysql-nen-tang-cho-backend', 'Nắm nền tảng PHP, MySQL, CRUD, transaction và thiết kế database.', 'Khóa học nền tảng giúp người học hiểu cách dữ liệu đi từ form đến database và quay về API response.', '/demo/courses/php-mysql.jpg', '/demo/videos/php-mysql-intro.mp4', 399000.0, NULL, 'beginner', 'vi', 'Biết HTML cơ bản là lợi thế.', 'Viết CRUD an toàn, hiểu khóa chính/khóa ngoại, transaction và validate dữ liệu.', 'published', 1, 9600, '2026-02-10 09:00:00', NULL, '2026-01-12 08:00:00', '2026-06-15 10:00:00', NULL),
    (3, 3, 'React Frontend cho trang E-learning', 'react-frontend-cho-trang-e-learning', 'Xây giao diện catalog, course detail, cart và learning dashboard.', 'Khóa học giúp frontend developer xây trang e-learning có component, state, call API và UI/UX rõ ràng.', '/demo/courses/react-elearning.jpg', '/demo/videos/react-intro.mp4', 599000.0, 449000.0, 'intermediate', 'vi', 'Biết JavaScript ES6 và HTML/CSS.', 'Tạo SPA học trực tuyến, tích hợp API và xử lý trạng thái loading/error.', 'published', 1, 14400, '2026-03-01 09:00:00', NULL, '2026-01-20 08:00:00', '2026-06-16 10:00:00', NULL),
    (4, 2, 'NodeJS Hidden Draft API', 'nodejs-hidden-draft-api', 'Course draft dùng để test chặn mua khóa chưa public.', 'Dữ liệu demo trạng thái draft, không hiển thị cho learner public.', '/demo/courses/node-draft.jpg', NULL, 350000.0, NULL, 'beginner', 'vi', 'JavaScript cơ bản.', 'Không dùng để trình chiếu public.', 'draft', 0, 3600, NULL, NULL, '2026-02-01 08:00:00', '2026-02-01 08:00:00', NULL),
    (5, 2, 'NodeJS Hidden Course', 'nodejs-hidden-course', 'Course hidden dùng để test filter và authorization.', 'Dữ liệu demo trạng thái hidden, không cho learner mua/thêm wishlist nếu API public lọc status.', '/demo/courses/node-hidden.jpg', NULL, 450000.0, 299000.0, 'intermediate', 'vi', 'JavaScript cơ bản.', 'Không dùng để trình chiếu public.', 'hidden', 0, 4800, '2026-02-15 09:00:00', NULL, '2026-02-01 08:15:00', '2026-03-01 08:15:00', NULL),
    (6, 3, 'AI ứng dụng cho học tập cá nhân hóa', 'ai-ung-dung-cho-hoc-tap-ca-nhan-hoa', 'Demo các tính năng AI: tóm tắt bài học, gợi ý khóa học, phân tích điểm yếu.', 'Khóa học mô phỏng cách tích hợp AI vào hệ thống học trực tuyến mà không gửi dữ liệu nhạy cảm.', '/demo/courses/ai-learning.jpg', '/demo/videos/ai-intro.mp4', 699000.0, 499000.0, 'all_levels', 'vi', 'Có kiến thức web/API cơ bản.', 'Hiểu workflow AI summary, draft quiz, recommendation và cảnh báo rủi ro bỏ học.', 'published', 1, 10800, '2026-04-01 09:00:00', NULL, '2026-02-15 08:00:00', '2026-06-18 10:00:00', NULL),
    (7, 2, 'Git & GitHub cho sinh viên làm đồ án', 'git-github-cho-sinh-vien-lam-do-an', 'Khóa miễn phí giúp quản lý source code, branch, commit và pull request.', 'Nội dung ngắn gọn để team đồ án phối hợp code backend/frontend hiệu quả.', '/demo/courses/git-github.jpg', '/demo/videos/git-intro.mp4', 0.0, 0.0, 'beginner', 'vi', 'Có máy tính và tài khoản GitHub.', 'Biết clone, branch, commit, push, pull request và xử lý conflict cơ bản.', 'published', 0, 5400, '2026-01-25 09:00:00', NULL, '2026-01-15 08:00:00', '2026-06-01 10:00:00', NULL),
    (8, 3, 'Advanced Laravel Architecture', 'advanced-laravel-architecture', 'Course pending review dùng test checklist/review/publish flow.', 'Dữ liệu demo trạng thái pending_review để instructor/admin test kiểm duyệt.', '/demo/courses/advanced-laravel.jpg', NULL, 899000.0, 699000.0, 'advanced', 'vi', 'Đã làm Laravel API thực tế.', 'Tách module, tối ưu service/repository và audit business flow.', 'pending_review', 0, 7200, NULL, NULL, '2026-06-01 08:00:00', '2026-06-18 08:00:00', NULL);

INSERT INTO course_categories (category_id, course_id, created_at) VALUES
    (2, 1, '2026-02-01 09:05:00'),
    (3, 1, '2026-02-01 09:05:00'),
    (2, 2, '2026-02-10 09:05:00'),
    (3, 2, '2026-02-10 09:05:00'),
    (2, 3, '2026-03-01 09:05:00'),
    (4, 3, '2026-03-01 09:05:00'),
    (3, 4, '2026-02-01 08:05:00'),
    (3, 5, '2026-02-01 08:20:00'),
    (5, 6, '2026-04-01 09:05:00'),
    (6, 7, '2026-01-25 09:05:00'),
    (3, 8, '2026-06-01 08:05:00');

INSERT INTO course_sections (id, course_id, title, description, sort_order, status, created_at, updated_at, deleted_at) VALUES
    (1, 1, 'Khởi động Laravel API', 'Cài đặt project, hiểu route, controller, request và response.', 1, 'published', '2026-01-10 09:00:00', '2026-06-20 10:00:00', NULL),
    (2, 1, 'Auth session custom', 'Login, logout, refresh token, quản lý session và thiết bị.', 2, 'published', '2026-01-10 09:10:00', '2026-06-20 10:00:00', NULL),
    (3, 1, 'Payment và Learning flow', 'Order, coupon, enrollment, tiến độ học và quiz.', 3, 'published', '2026-01-10 09:20:00', '2026-06-20 10:00:00', NULL),
    (4, 1, 'Nội dung ẩn demo', 'Section hidden để test filter.', 4, 'hidden', '2026-01-10 09:30:00', '2026-06-20 10:00:00', NULL),
    (5, 2, 'PHP nền tảng', 'Biến, hàm, mảng, request/response cơ bản.', 1, 'published', '2026-01-12 09:00:00', '2026-06-15 10:00:00', NULL),
    (6, 2, 'MySQL thực chiến', 'CRUD, khóa ngoại, transaction.', 2, 'published', '2026-01-12 09:10:00', '2026-06-15 10:00:00', NULL),
    (7, 3, 'React UI Foundation', 'Component, props, state và layout.', 1, 'published', '2026-01-20 09:00:00', '2026-06-16 10:00:00', NULL),
    (8, 3, 'Tích hợp API e-learning', 'Call API, auth token và error state.', 2, 'published', '2026-01-20 09:10:00', '2026-06-16 10:00:00', NULL),
    (9, 6, 'AI trong sản phẩm học tập', 'Tóm tắt bài, tạo quiz nháp và phân tích điểm yếu.', 1, 'published', '2026-02-15 09:00:00', '2026-06-18 10:00:00', NULL),
    (10, 7, 'Git cơ bản', 'Commit, branch và pull request.', 1, 'published', '2026-01-15 09:00:00', '2026-06-01 10:00:00', NULL),
    (11, 4, 'Draft section', 'Dữ liệu draft.', 1, 'draft', '2026-02-01 09:00:00', '2026-02-01 09:00:00', NULL),
    (12, 8, 'Kiến trúc nâng cao', 'Service, repository và module boundary.', 1, 'published', '2026-06-01 09:00:00', '2026-06-18 08:00:00', NULL);

INSERT INTO lessons (id, course_section_id, course_id, title, slug, lesson_type, content, video_url, video_duration_seconds, is_preview, status, sort_order, created_at, updated_at, deleted_at) VALUES
    (1, 1, 1, 'Giới thiệu REST API trong Laravel', 'gioi-thieu-rest-api-trong-laravel', 'video', 'Giới thiệu cách xây dựng REST API trong Laravel, cấu trúc route, controller, request và resource cho dự án MindHub.', '/demo/videos/laravel-01-intro.mp4', 900, 1, 'published', 1, '2026-01-10 10:00:00', '2026-06-20 10:00:00', NULL),
    (2, 1, 1, 'Repository Service Resource là gì?', 'repository-service-resource-la-gi', 'text', 'Repository chứa query, Service chứa business logic, Controller chỉ điều phối request response. Quy tắc này giúp code dễ test và dễ bảo trì.', NULL, NULL, 0, 'published', 2, '2026-01-10 10:10:00', '2026-06-20 10:00:00', NULL),
    (3, 2, 1, 'Custom session và refresh token', 'custom-session-va-refresh-token', 'video', 'Custom session dùng bảng sessions, lưu refresh_token_hash, expires_at và revoked_at. Middleware auth.session kiểm tra access token và active.user kiểm tra trạng thái tài khoản.', '/demo/videos/laravel-03-session.mp4', 1800, 0, 'published', 1, '2026-01-10 10:20:00', '2026-06-20 10:00:00', NULL),
    (4, 4, 1, 'Lesson hidden demo', 'lesson-hidden-demo', 'text', 'Bài học hidden dùng để test learner không được xem nội dung ẩn.', NULL, NULL, 0, 'hidden', 1, '2026-01-10 10:30:00', '2026-06-20 10:00:00', NULL),
    (5, 4, 1, 'Lesson draft demo', 'lesson-draft-demo', 'text', 'Bài học draft dùng để test filter draft.', NULL, NULL, 0, 'draft', 2, '2026-01-10 10:40:00', '2026-06-20 10:00:00', NULL),
    (6, 3, 1, 'Payment và enrollment sau khi paid', 'payment-va-enrollment-sau-khi-paid', 'video', 'Payment flow gồm tạo order pending, áp coupon, xác nhận paid, tạo enrollment và revenue. Không cấp quyền học trước khi paid.', '/demo/videos/laravel-06-payment.mp4', 2100, 0, 'published', 1, '2026-01-10 10:50:00', '2026-06-20 10:00:00', NULL),
    (7, 5, 2, 'PHP request response cơ bản', 'php-request-response-co-ban', 'video', 'PHP xử lý request, validate input và trả dữ liệu qua JSON response.', '/demo/videos/php-01-request-response.mp4', 1200, 1, 'published', 1, '2026-01-12 10:00:00', '2026-06-15 10:00:00', NULL),
    (8, 6, 2, 'MySQL transaction trong thanh toán', 'mysql-transaction-trong-thanh-toan', 'text', 'MySQL transaction giúp đảm bảo order, enrollment và revenue không bị lệch dữ liệu.', NULL, NULL, 0, 'published', 1, '2026-01-12 10:10:00', '2026-06-15 10:00:00', NULL),
    (9, 7, 3, 'Component hóa giao diện khóa học', 'component-hoa-giao-dien-khoa-hoc', 'video', 'React component nên tách theo UI state, dữ liệu API và hành vi người dùng.', '/demo/videos/react-01-components.mp4', 1500, 1, 'published', 1, '2026-01-20 10:00:00', '2026-06-16 10:00:00', NULL),
    (10, 8, 3, 'Learning dashboard với API thật', 'learning-dashboard-voi-api-that', 'text', 'Learning dashboard cần trạng thái loading, empty, error và dữ liệu progress rõ ràng.', NULL, NULL, 0, 'published', 1, '2026-01-20 10:10:00', '2026-06-16 10:00:00', NULL),
    (11, 9, 6, 'AI tóm tắt bài học an toàn', 'ai-tom-tat-bai-hoc-an-toan', 'text', 'AI summary chỉ dùng nội dung bài học, không gửi token hay dữ liệu nhạy cảm lên provider.', NULL, NULL, 1, 'published', 1, '2026-02-15 10:00:00', '2026-06-18 10:00:00', NULL),
    (12, 9, 6, 'AI phân tích điểm yếu sau quiz', 'ai-phan-tich-diem-yeu-sau-quiz', 'video', 'AI phân tích điểm yếu dựa trên quiz_attempt_answers.option_id, score_earned và explanation của câu hỏi.', '/demo/videos/ai-02-quiz-weakness.mp4', 1800, 0, 'published', 2, '2026-02-15 10:10:00', '2026-06-18 10:00:00', NULL),
    (13, 10, 7, 'Git branch và pull request', 'git-branch-va-pull-request', 'video', 'Git branch giúp team đồ án làm song song, pull request giúp review trước khi merge.', '/demo/videos/git-01-branch-pr.mp4', 900, 1, 'published', 1, '2026-01-15 10:00:00', '2026-06-01 10:00:00', NULL),
    (14, 11, 4, 'NodeJS draft lesson', 'nodejs-draft-lesson', 'text', 'Nội dung draft NodeJS chưa public.', NULL, NULL, 0, 'draft', 1, '2026-02-01 10:00:00', '2026-02-01 10:00:00', NULL),
    (15, 12, 8, 'Module boundary trong Laravel', 'module-boundary-trong-laravel', 'text', 'Advanced Laravel dùng module boundary để giảm phụ thuộc chéo giữa Auth, Payment và Learning.', NULL, NULL, 0, 'published', 1, '2026-06-01 10:00:00', '2026-06-18 08:00:00', NULL);

INSERT INTO lesson_assets (id, lesson_id, title, file_url, file_name, file_type, file_size, note, created_at, deleted_at) VALUES
    (1, 1, 'Slide giới thiệu Laravel API', '/demo/assets/laravel-api-intro.pdf', 'laravel-api-intro.pdf', 'pdf', 1250000, 'Tài liệu dùng cho bài mở đầu.', '2026-01-10 11:00:00', NULL),
    (2, 2, 'Checklist Repository Service Resource', '/demo/assets/repository-service-checklist.pdf', 'repository-service-checklist.pdf', 'pdf', 850000, 'Checklist code sạch cho backend.', '2026-01-10 11:05:00', NULL),
    (3, 3, 'Sơ đồ custom session flow', '/demo/assets/custom-session-flow.png', 'custom-session-flow.png', 'image/png', 420000, 'Minh họa login/refresh/logout.', '2026-01-10 11:10:00', NULL),
    (4, 11, 'Prompt mẫu AI summary', '/demo/assets/ai-summary-prompt.md', 'ai-summary-prompt.md', 'text/markdown', 12000, 'Prompt demo không chứa dữ liệu nhạy cảm.', '2026-02-15 11:00:00', NULL);

INSERT INTO lesson_progress (id, lesson_id, user_id, status, started_at, completed_at, learning_duration_seconds, last_accessed_at, created_at, updated_at) VALUES
    (1, 1, 4, 'completed', '2026-06-20 08:00:00', '2026-06-20 08:20:00', 1100, '2026-06-20 08:20:00', '2026-06-20 08:00:00', '2026-06-20 08:20:00'),
    (2, 2, 4, 'completed', '2026-06-20 08:25:00', '2026-06-20 08:50:00', 1500, '2026-06-20 08:50:00', '2026-06-20 08:25:00', '2026-06-20 08:50:00'),
    (3, 3, 4, 'in_progress', '2026-06-22 20:00:00', NULL, 980, '2026-06-22 20:35:00', '2026-06-22 20:00:00', '2026-06-22 20:35:00'),
    (4, 7, 4, 'in_progress', '2026-06-21 09:00:00', NULL, 420, '2026-06-21 09:10:00', '2026-06-21 09:00:00', '2026-06-21 09:10:00'),
    (5, 1, 6, 'completed', '2026-05-01 08:00:00', '2026-05-01 08:20:00', 1000, '2026-05-01 08:20:00', '2026-05-01 08:00:00', '2026-05-01 08:20:00'),
    (6, 2, 6, 'completed', '2026-05-01 08:30:00', '2026-05-01 09:00:00', 1600, '2026-05-01 09:00:00', '2026-05-01 08:30:00', '2026-05-01 09:00:00'),
    (7, 3, 6, 'completed', '2026-05-02 08:00:00', '2026-05-02 08:45:00', 2200, '2026-05-02 08:45:00', '2026-05-02 08:00:00', '2026-05-02 08:45:00'),
    (8, 6, 6, 'completed', '2026-05-03 08:00:00', '2026-05-03 08:50:00', 2300, '2026-05-03 08:50:00', '2026-05-03 08:00:00', '2026-05-03 08:50:00');

INSERT INTO video_progress (id, lesson_id, user_id, current_second, created_at, updated_at) VALUES
    (1, 1, 4, 900, '2026-06-20 08:00:00', '2026-06-20 08:20:00'),
    (2, 3, 4, 980, '2026-06-22 20:00:00', '2026-06-22 20:35:00'),
    (3, 7, 4, 420, '2026-06-21 09:00:00', '2026-06-21 09:10:00'),
    (4, 1, 6, 900, '2026-05-01 08:00:00', '2026-05-01 08:20:00'),
    (5, 3, 6, 1800, '2026-05-02 08:00:00', '2026-05-02 08:45:00'),
    (6, 6, 6, 2100, '2026-05-03 08:00:00', '2026-05-03 08:50:00');

INSERT INTO coupons (id, user_id, course_id, code, name, description, discount_type, discount_value, max_order_amount, usage_limit, used_count, start_at, end_at, status, created_at, updated_at, deleted_at) VALUES
    (1, 1, NULL, 'WELCOME100', 'Giảm 100K chào mừng', 'Mã giảm 100.000đ cho đơn hàng đầu tiên.', 'fixed', 100000.0, NULL, 100, 2, '2026-01-01 00:00:00', '2026-12-31 23:59:59', 'active', '2026-01-01 10:00:00', '2026-06-01 10:00:00', NULL),
    (2, 1, NULL, 'GLOBAL10', 'Giảm 10% toàn hệ thống', 'Mã giảm 10%, tối đa 50.000đ.', 'percent', 10.0, 50000.0, NULL, 1, '2026-01-01 00:00:00', '2026-12-31 23:59:59', 'active', '2026-01-01 10:05:00', '2026-06-01 10:00:00', NULL),
    (3, 1, NULL, 'OLD50', 'Mã đã hết hạn', 'Dùng để test coupon expired.', 'fixed', 50000.0, NULL, 10, 0, '2026-01-01 00:00:00', '2026-02-01 00:00:00', 'expired', '2026-01-01 10:10:00', '2026-02-01 00:00:00', NULL),
    (4, 1, NULL, 'OFFLINE20', 'Mã inactive', 'Dùng để test coupon inactive.', 'percent', 20.0, 80000.0, 50, 0, '2026-01-01 00:00:00', '2026-12-31 23:59:59', 'inactive', '2026-01-01 10:15:00', '2026-06-01 10:00:00', NULL),
    (5, 1, NULL, 'FULLUSED', 'Mã hết lượt', 'Dùng để test coupon used_up.', 'fixed', 30000.0, NULL, 10, 10, '2026-01-01 00:00:00', '2026-12-31 23:59:59', 'used_up', '2026-01-01 10:20:00', '2026-06-01 10:00:00', NULL),
    (6, 2, 7, 'FREEGIT', 'Miễn phí Git', 'Mã course-specific cho khóa Git & GitHub.', 'fixed', 299000.0, NULL, 100, 0, '2026-01-01 00:00:00', '2026-12-31 23:59:59', 'active', '2026-01-01 10:25:00', '2026-06-01 10:00:00', NULL),
    (7, 2, 1, 'LARAVEL50', 'Laravel giảm 50%', 'Mã chỉ áp dụng cho khóa Laravel REST API.', 'percent', 50.0, 150000.0, 20, 1, '2026-06-01 00:00:00', '2026-12-31 23:59:59', 'active', '2026-06-01 10:30:00', '2026-06-01 10:30:00', NULL);

INSERT INTO orders (id, coupon_id, course_id, user_id, order_code, status, price_snapshot, payment_method, provider_transaction_id, amount, payment_status, paid_at, created_at, updated_at) VALUES
    (1, 2, 1, 4, 'ORD-2026-0001', 'paid', 299000.0, 'vnpay', 'VNPAY-DEMO-0001', 269100.0, 'paid', '2026-06-20 07:50:00', '2026-06-20 07:45:00', '2026-06-20 07:50:00'),
    (2, NULL, 1, 5, 'ORD-2026-0002', 'pending', 299000.0, NULL, NULL, 299000.0, 'unpaid', NULL, '2026-06-22 20:30:00', '2026-06-22 20:30:00'),
    (3, NULL, 2, 5, 'ORD-2026-0003', 'failed', 399000.0, 'vnpay', 'VNPAY-DEMO-0003', 399000.0, 'failed', NULL, '2026-06-18 12:00:00', '2026-06-18 12:15:00'),
    (4, NULL, 3, 5, 'ORD-2026-0004', 'cancelled', 449000.0, NULL, NULL, 449000.0, 'unpaid', NULL, '2026-06-17 09:00:00', '2026-06-17 09:20:00'),
    (5, NULL, 6, 5, 'ORD-2026-0005', 'expired', 499000.0, NULL, NULL, 499000.0, 'unpaid', NULL, '2026-05-01 09:00:00', '2026-05-02 09:00:00'),
    (6, NULL, 1, 6, 'ORD-2026-0006', 'paid', 299000.0, 'momo', 'MOMO-DEMO-0006', 299000.0, 'paid', '2026-05-01 07:40:00', '2026-05-01 07:30:00', '2026-05-01 07:40:00'),
    (7, NULL, 2, 4, 'ORD-2026-0007', 'paid', 399000.0, 'bank_transfer', 'BANK-DEMO-0007', 399000.0, 'paid', '2026-06-21 08:30:00', '2026-06-21 08:00:00', '2026-06-21 08:30:00'),
    (8, NULL, 7, 5, 'ORD-2026-0008', 'pending', 0.0, 'free', NULL, 0.0, 'unpaid', NULL, '2026-06-23 08:00:00', '2026-06-23 08:00:00'),
    (9, NULL, 3, 4, 'ORD-2026-0009', 'pending', 449000.0, NULL, NULL, 449000.0, 'unpaid', NULL, '2026-06-23 08:05:00', '2026-06-23 08:05:00'),
    (10, NULL, 6, 4, 'ORD-2026-0010', 'failed', 499000.0, 'vnpay', 'VNPAY-DEMO-0010', 499000.0, 'failed', NULL, '2026-06-19 14:00:00', '2026-06-19 14:10:00');

INSERT INTO enrollments (id, user_id, course_id, order_id, status, progress_percent, enrolled_at, completed_at, last_accessed_at, created_at, updated_at) VALUES
    (1, 4, 1, 1, 'active', 50.0, '2026-06-20 07:50:00', NULL, '2026-06-22 20:35:00', '2026-06-20 07:50:00', '2026-06-22 20:35:00'),
    (2, 6, 1, 6, 'completed', 100.0, '2026-05-01 07:40:00', '2026-05-03 09:00:00', '2026-05-03 08:50:00', '2026-05-01 07:40:00', '2026-05-03 09:00:00'),
    (3, 4, 2, 7, 'active', 25.0, '2026-06-21 08:30:00', NULL, '2026-06-21 09:10:00', '2026-06-21 08:30:00', '2026-06-21 09:10:00');

INSERT INTO course_reviews (id, order_id, rating, comment, created_at, updated_at, deleted_at) VALUES
    (1, 1, 5, 'Khóa Laravel rất sát với đồ án, phần payment và custom session dễ hiểu.', '2026-06-22 09:00:00', '2026-06-22 09:00:00', NULL),
    (2, 6, 4, 'Nội dung đầy đủ, có thể thêm nhiều ví dụ test API hơn.', '2026-05-04 10:00:00', '2026-05-04 10:00:00', NULL),
    (3, 7, 4, 'Phần transaction MySQL giúp mình hiểu rõ xử lý dữ liệu tài chính.', '2026-06-22 10:00:00', '2026-06-22 10:00:00', NULL);

INSERT INTO wishlist (id, user_id, course_id, created_at) VALUES
    (1, 5, 1, '2026-06-18 10:00:00'),
    (2, 5, 3, '2026-06-18 10:05:00'),
    (3, 5, 6, '2026-06-18 10:10:00'),
    (4, 4, 3, '2026-06-19 11:00:00'),
    (5, 4, 6, '2026-06-19 11:05:00');

INSERT INTO comments (id, parent_id, user_id, order_id, lesson_id, content, status, created_at, updated_at) VALUES
    (1, NULL, 4, 1, 3, 'Em muốn hỏi refresh_token_hash nên so sánh trực tiếp hay dùng hash_equals?', 'visible', '2026-06-22 20:40:00', '2026-06-22 20:40:00'),
    (2, 1, 2, NULL, 3, 'Nên hash token client gửi lên rồi dùng so sánh an toàn, không lưu refresh token thô trong DB.', 'visible', '2026-06-22 21:00:00', '2026-06-22 21:00:00'),
    (3, NULL, 6, 6, 6, 'Flow paid -> enrollment -> revenue rất hữu ích khi test payment.', 'visible', '2026-05-03 09:10:00', '2026-05-03 09:10:00'),
    (4, NULL, 4, 1, 2, 'Comment hidden dùng để test moderation.', 'hidden', '2026-06-20 09:00:00', '2026-06-20 09:10:00');

INSERT INTO instructor_profiles (id, user_id, bio, expertise, experience_years, level, created_at, updated_at) VALUES
    (1, 2, 'Backend Developer chuyên Laravel, MySQL và thiết kế REST API cho sản phẩm giáo dục.', 'Laravel, PHP, MySQL, API Design, Payment Flow', 6, 'Senior Backend Instructor', '2026-01-05 08:00:00', '2026-06-20 10:00:00'),
    (2, 3, 'Frontend/AI Product Mentor, tập trung React, UI/UX và ứng dụng AI trong học tập.', 'React, UI/UX, AI Product, Learning Analytics', 5, 'Senior Frontend & AI Instructor', '2026-01-05 08:10:00', '2026-06-18 10:00:00');

INSERT INTO revenues (id, instructor_id, course_id, order_id, gross_amount, instructor_amount, platform_fee_amount, status, earned_at, created_at) VALUES
    (1, 2, 1, 1, 269100.0, 188370.0, 80730.0, 'available', '2026-06-20 07:50:00', '2026-06-20 07:50:00'),
    (2, 2, 1, 6, 299000.0, 209300.0, 89700.0, 'withdrawn', '2026-05-01 07:40:00', '2026-05-01 07:40:00'),
    (3, 2, 2, 7, 399000.0, 279300.0, 119700.0, 'pending', '2026-06-21 08:30:00', '2026-06-21 08:30:00');

INSERT INTO payout_accounts (id, user_id, provider, account_number, account_name, connected_at, status, created_at, updated_at, deleted_at) VALUES
    (1, 2, 'bank', '970400000001', 'NGUYEN MINH KHOA', '2026-02-01 08:00:00', 'active', '2026-02-01 08:00:00', '2026-02-01 08:00:00', NULL),
    (2, 3, 'bank', '970400000002', 'TRAN HA LINH', NULL, 'pending_verification', '2026-02-02 08:00:00', '2026-02-02 08:00:00', NULL);

INSERT INTO withdraw_requests (id, user_id, payout_account_id, amount, status, requested_at, approved_at, paid_at, rejected_reason, provider_payout_id, account_number_snapshot, account_name_snapshot, created_at, updated_at) VALUES
    (1, 2, 1, 209300.0, 'paid', '2026-05-05 08:00:00', '2026-05-05 10:00:00', '2026-05-06 09:00:00', NULL, 'PAYOUT-DEMO-0001', '970400000001', 'NGUYEN MINH KHOA', '2026-05-05 08:00:00', '2026-05-06 09:00:00'),
    (2, 2, 1, 188370.0, 'pending', '2026-06-22 08:00:00', NULL, NULL, NULL, NULL, '970400000001', 'NGUYEN MINH KHOA', '2026-06-22 08:00:00', '2026-06-22 08:00:00');

INSERT INTO banners (id, title, image_url, target_url, position, sort_order, start_at, end_at, status, created_at, updated_at, deleted_at) VALUES
    (1, 'Học Laravel REST API cho đồ án tốt nghiệp', '/demo/banners/banner-laravel-api.jpg', '/courses/laravel-rest-api-tu-co-ban-den-trien-khai', 'home', 1, '2026-01-01 00:00:00', '2026-12-31 23:59:59', 'active', '2026-01-01 08:00:00', '2026-06-01 08:00:00', NULL),
    (2, 'AI hỗ trợ học tập cá nhân hóa', '/demo/banners/banner-ai-learning.jpg', '/courses/ai-ung-dung-cho-hoc-tap-ca-nhan-hoa', 'home', 2, '2026-01-01 00:00:00', '2026-12-31 23:59:59', 'active', '2026-01-01 08:10:00', '2026-06-01 08:10:00', NULL),
    (3, 'Banner inactive demo', '/demo/banners/banner-inactive.jpg', NULL, 'home', 3, '2026-01-01 00:00:00', '2026-12-31 23:59:59', 'inactive', '2026-01-01 08:20:00', '2026-06-01 08:20:00', NULL);

INSERT INTO faqs (id, question, answer, type, status, sort_order, created_at, updated_at, deleted_at) VALUES
    (1, 'Tôi có được học lại khóa đã mua không?', 'Có. Sau khi thanh toán thành công và có enrollment active, bạn có thể vào học lại bất kỳ lúc nào theo chính sách của nền tảng.', 'general', 'active', 1, '2026-01-01 08:00:00', '2026-01-01 08:00:00', NULL),
    (2, 'Nếu thanh toán thất bại thì sao?', 'Đơn hàng thất bại không tạo enrollment. Bạn có thể dùng chức năng thanh toán lại nếu đơn còn hợp lệ.', 'payment', 'active', 2, '2026-01-01 08:05:00', '2026-01-01 08:05:00', NULL),
    (3, 'Khóa Laravel có phù hợp cho người mới không?', 'Có. Khóa bắt đầu từ route, controller, request/resource rồi đi tới auth session, payment và learning flow.', 'course', 'active', 3, '2026-01-01 08:10:00', '2026-01-01 08:10:00', NULL),
    (4, 'FAQ inactive demo', 'Dữ liệu này dùng để test filter status inactive.', 'general', 'inactive', 4, '2026-01-01 08:15:00', '2026-01-01 08:15:00', NULL);

INSERT INTO course_faqs (faq_id, course_id, sort_order, created_at, deleted_at) VALUES
    (1, 1, 1, '2026-02-01 09:00:00', NULL),
    (2, 1, 2, '2026-02-01 09:05:00', NULL),
    (3, 1, 3, '2026-02-01 09:10:00', NULL),
    (1, 6, 1, '2026-04-01 09:00:00', NULL);

INSERT INTO quizzes (id, course_id, lesson_id, title, description, passing_score, status, created_at, updated_at, deleted_at) VALUES
    (1, 1, 6, 'Quiz cuối khóa Laravel API', 'Kiểm tra kiến thức về auth session, payment và repository/service.', 70.0, 'published', '2026-01-10 12:00:00', '2026-06-20 10:00:00', NULL),
    (2, 6, 12, 'Quiz AI Learning Flow', 'Kiểm tra nguyên tắc dùng AI an toàn trong nền tảng học tập.', 70.0, 'published', '2026-02-15 12:00:00', '2026-06-18 10:00:00', NULL),
    (3, 1, NULL, 'Quiz draft demo', 'Dữ liệu quiz draft để test filter.', 50.0, 'draft', '2026-01-10 12:10:00', '2026-01-10 12:10:00', NULL);

INSERT INTO quiz_questions (id, quiz_id, question_text, question_type, score, sort_order, explanation, created_at) VALUES
    (1, 1, 'Trong kiến trúc đã chốt, business logic chính nên đặt ở đâu?', 'single_choice', 1.0, 1, 'Controller chỉ điều phối, business logic nên nằm trong Service.', '2026-01-10 12:20:00'),
    (2, 1, 'Project GD1 dùng Sanctum làm cơ chế token chính.', 'true_false', 1.0, 2, 'Sai. Project dùng custom session với auth.session và bảng sessions.', '2026-01-10 12:25:00'),
    (3, 1, 'Khi order còn pending/unpaid thì hệ thống có được tạo enrollment chưa?', 'single_choice', 1.0, 3, 'Không. Enrollment chỉ tạo sau khi payment được xác nhận paid.', '2026-01-10 12:30:00'),
    (4, 2, 'Khi gọi AI provider, dữ liệu nào không được gửi?', 'single_choice', 1.0, 1, 'Không gửi password, token, secret hoặc dữ liệu nhạy cảm.', '2026-02-15 12:20:00'),
    (5, 2, 'AI summary có thể cache theo lesson_id, summary_type, language và source_content_hash.', 'true_false', 1.0, 2, 'Đúng theo bảng ai_lesson_summaries đã chốt.', '2026-02-15 12:25:00');

INSERT INTO quiz_options (id, question_id, option_text, is_correct, sort_order, created_at) VALUES
    (1, 1, 'Controller', 0, 1, '2026-01-10 12:40:00'),
    (2, 1, 'Service', 1, 2, '2026-01-10 12:40:00'),
    (3, 1, 'Migration', 0, 3, '2026-01-10 12:40:00'),
    (4, 2, 'Đúng', 0, 1, '2026-01-10 12:45:00'),
    (5, 2, 'Sai', 1, 2, '2026-01-10 12:45:00'),
    (6, 3, 'Có, tạo ngay khi user bấm mua', 0, 1, '2026-01-10 12:50:00'),
    (7, 3, 'Không, chỉ tạo sau khi paid', 1, 2, '2026-01-10 12:50:00'),
    (8, 4, 'Nội dung bài học công khai cần tóm tắt', 0, 1, '2026-02-15 12:40:00'),
    (9, 4, 'Password, token, secret hoặc dữ liệu nhạy cảm', 1, 2, '2026-02-15 12:40:00'),
    (10, 5, 'Đúng', 1, 1, '2026-02-15 12:45:00'),
    (11, 5, 'Sai', 0, 2, '2026-02-15 12:45:00');

INSERT INTO quiz_attempts (id, quiz_id, user_id, attempt_number, score, total_score, passed, status, started_at, submitted_at, created_at) VALUES
    (1, 1, 4, 1, 2.0, 3.0, 0, 'submitted', '2026-06-22 21:10:00', '2026-06-22 21:18:00', '2026-06-22 21:10:00'),
    (2, 1, 6, 1, 3.0, 3.0, 1, 'submitted', '2026-05-03 09:00:00', '2026-05-03 09:08:00', '2026-05-03 09:00:00');

INSERT INTO quiz_attempt_answers (id, question_id, attempt_id, option_id, is_correct, score_earned, created_at) VALUES
    (1, 1, 1, 2, 1, 1.0, '2026-06-22 21:18:00'),
    (2, 2, 1, 4, 0, 0.0, '2026-06-22 21:18:00'),
    (3, 3, 1, 7, 1, 1.0, '2026-06-22 21:18:00'),
    (4, 1, 2, 2, 1, 1.0, '2026-05-03 09:08:00'),
    (5, 2, 2, 5, 1, 1.0, '2026-05-03 09:08:00'),
    (6, 3, 2, 7, 1, 1.0, '2026-05-03 09:08:00');

INSERT INTO notifications (id, user_id, type, title, message, data, action_url, channel, read_at, email_to, email_status, email_sent_at, email_error, created_at, updated_at, deleted_at) VALUES
    (1, 4, 'payment_paid', 'Thanh toán thành công', 'Bạn đã được ghi danh vào khóa Laravel REST API.', '{"order_id":1,"course_id":1}', '/me/courses/1', 'database', '2026-06-20 08:10:00', 'learner1@mindhub.test', 'sent', '2026-06-20 07:51:00', NULL, '2026-06-20 07:51:00', '2026-06-20 08:10:00', NULL),
    (2, 4, 'learning_resume', 'Bạn đang học dở bài Custom session', 'Tiếp tục từ giây 980 trong bài Custom session và refresh token.', '{"course_id":1,"lesson_id":3,"current_second":980}', '/learn/lessons/3', 'database', NULL, NULL, NULL, NULL, NULL, '2026-06-22 20:40:00', '2026-06-22 20:40:00', NULL),
    (3, 5, 'payment_pending', 'Đơn hàng đang chờ thanh toán', 'Đơn ORD-2026-0002 của bạn đang chờ thanh toán.', '{"order_id":2,"course_id":1}', '/orders/2', 'database', NULL, 'learner2@mindhub.test', 'queued', NULL, NULL, '2026-06-22 20:31:00', '2026-06-22 20:31:00', NULL),
    (4, 6, 'certificate_issued', 'Chứng chỉ đã được cấp', 'Chúc mừng bạn đã hoàn thành khóa Laravel REST API.', '{"certificate_id":1,"course_id":1}', '/certificates/MH-CERT-2026-0001', 'database', '2026-05-03 10:00:00', 'learner.completed@mindhub.test', 'sent', '2026-05-03 09:05:00', NULL, '2026-05-03 09:05:00', '2026-05-03 10:00:00', NULL),
    (5, 2, 'revenue_available', 'Doanh thu có thể rút', 'Bạn có doanh thu khả dụng từ khóa Laravel REST API.', '{"revenue_id":1,"order_id":1}', '/instructor/revenues', 'database', NULL, 'instructor1@mindhub.test', 'sent', '2026-06-20 08:00:00', NULL, '2026-06-20 08:00:00', '2026-06-20 08:00:00', NULL);

INSERT INTO certificates (id, user_id, course_id, enrollment_id, certificate_code, certificate_url, issued_at, status, created_at, updated_at, deleted_at) VALUES
    (1, 6, 1, 2, 'MH-CERT-2026-0001', '/demo/certificates/MH-CERT-2026-0001.pdf', '2026-05-03 09:05:00', 'active', '2026-05-03 09:05:00', '2026-05-03 09:05:00', NULL);

INSERT INTO lesson_notes (id, user_id, course_id, lesson_id, content, note_time_second, created_at, updated_at, deleted_at) VALUES
    (1, 4, 1, 3, 'Chỗ này cần nhớ: revoked_at khác NULL thì middleware phải từ chối session.', 950, '2026-06-22 20:30:00', '2026-06-22 20:30:00', NULL),
    (2, 4, 1, 2, 'Service không query trực tiếp quá nhiều, Repository chịu trách nhiệm query.', NULL, '2026-06-20 08:45:00', '2026-06-20 08:45:00', NULL),
    (3, 6, 1, 6, 'Payment paid mới tạo revenue và enrollment, không tạo khi pending.', 1200, '2026-05-03 08:30:00', '2026-05-03 08:30:00', NULL);

INSERT INTO user_category_interests (id, user_id, category_id, interest_level, created_at, updated_at) VALUES
    (1, 4, 3, 5, '2026-06-01 08:00:00', '2026-06-01 08:00:00'),
    (2, 4, 5, 4, '2026-06-01 08:05:00', '2026-06-01 08:05:00'),
    (3, 5, 2, 5, '2026-06-01 08:10:00', '2026-06-01 08:10:00'),
    (4, 5, 4, 4, '2026-06-01 08:15:00', '2026-06-01 08:15:00'),
    (5, 5, 5, 3, '2026-06-01 08:20:00', '2026-06-01 08:20:00');

INSERT INTO ai_lesson_summaries (id, lesson_id, course_id, summary, key_points, summary_type, language, source_content_hash, model_name, generated_by_user_id, generated_at, created_at, updated_at) VALUES
    (1, 2, 1, 'Bài học giải thích vai trò của Repository, Service và Resource trong Laravel API. Controller chỉ điều phối, Service xử lý nghiệp vụ, Repository xử lý query và Resource chuẩn hóa response.', '["Controller không chứa business logic chính","Service xử lý nghiệp vụ","Repository xử lý query","Resource che field nhạy cảm"]', 'short', 'vi', '6cc3964de4712ab85707c7db60b4e2dcd1451576ca1f8f66aba3fb65af78fb09', 'demo-ai-summary', 4, '2026-06-22 21:30:00', '2026-06-22 21:30:00', '2026-06-22 21:30:00'),
    (2, 3, 1, 'Custom session sử dụng bảng sessions để lưu refresh_token_hash, expires_at và revoked_at. Middleware cần từ chối session hết hạn hoặc đã revoke, đồng thời kiểm tra user active.', '["Không lưu refresh token thô","expires_at xác định hết hạn","revoked_at xác định phiên bị thu hồi","active.user chặn locked/inactive"]', 'bullet', 'vi', 'e6449281ddd8ee6ea59753cfd3e43971b9df3a5568a7032293cf06bbfa6600e7', 'demo-ai-summary', 4, '2026-06-22 21:35:00', '2026-06-22 21:35:00', '2026-06-22 21:35:00'),
    (3, 11, 6, 'Khi dùng AI để tóm tắt bài học, hệ thống chỉ gửi nội dung cần xử lý và không gửi token, mật khẩu, secret hoặc dữ liệu cá nhân nhạy cảm.', '["Giới hạn input/token","Không gửi secret","Có fallback khi provider lỗi","Cache bằng source_content_hash"]', 'short', 'vi', 'a69f6c85f9a54e27c57ad84c4ae30bb453a88e120ca77fdd2b5b27755c0f7d40', 'demo-ai-summary', 3, '2026-06-18 12:00:00', '2026-06-18 12:00:00', '2026-06-18 12:00:00');


-- Reset AUTO_INCREMENT để insert dữ liệu mới không bị trùng ID demo.

ALTER TABLE users AUTO_INCREMENT = 12;

ALTER TABLE sessions AUTO_INCREMENT = 9;

ALTER TABLE categories AUTO_INCREMENT = 8;

ALTER TABLE courses AUTO_INCREMENT = 9;

ALTER TABLE course_sections AUTO_INCREMENT = 13;

ALTER TABLE lessons AUTO_INCREMENT = 16;

ALTER TABLE lesson_assets AUTO_INCREMENT = 5;

ALTER TABLE lesson_progress AUTO_INCREMENT = 9;

ALTER TABLE video_progress AUTO_INCREMENT = 7;

ALTER TABLE coupons AUTO_INCREMENT = 8;

ALTER TABLE orders AUTO_INCREMENT = 11;

ALTER TABLE enrollments AUTO_INCREMENT = 4;

ALTER TABLE course_reviews AUTO_INCREMENT = 4;

ALTER TABLE wishlist AUTO_INCREMENT = 6;

ALTER TABLE comments AUTO_INCREMENT = 5;

ALTER TABLE instructor_profiles AUTO_INCREMENT = 3;

ALTER TABLE revenues AUTO_INCREMENT = 4;

ALTER TABLE payout_accounts AUTO_INCREMENT = 3;

ALTER TABLE withdraw_requests AUTO_INCREMENT = 3;

ALTER TABLE banners AUTO_INCREMENT = 4;

ALTER TABLE faqs AUTO_INCREMENT = 5;

ALTER TABLE quizzes AUTO_INCREMENT = 4;

ALTER TABLE quiz_questions AUTO_INCREMENT = 6;

ALTER TABLE quiz_options AUTO_INCREMENT = 12;

ALTER TABLE quiz_attempts AUTO_INCREMENT = 3;

ALTER TABLE quiz_attempt_answers AUTO_INCREMENT = 7;

ALTER TABLE notifications AUTO_INCREMENT = 6;

ALTER TABLE certificates AUTO_INCREMENT = 2;

ALTER TABLE lesson_notes AUTO_INCREMENT = 4;

ALTER TABLE user_category_interests AUTO_INCREMENT = 6;

ALTER TABLE ai_lesson_summaries AUTO_INCREMENT = 4;


-- ==========================================================
-- Demo accounts
-- Password for all non-OAuth demo accounts: 12345678
-- admin@mindhub.test / instructor1@mindhub.test / instructor2@mindhub.test
-- learner1@mindhub.test / learner2@mindhub.test / learner.completed@mindhub.test
-- locked@mindhub.test / inactive@mindhub.test / learner.limit@mindhub.test / learner.empty@mindhub.test
-- oauth.only@mindhub.test has password_hash = NULL, dùng test OAuth-only.
-- ==========================================================

-- ==========================================================
-- MINDHUB NOTEBOOKLM VIDEO COURSE SEED - APPENDED BY CHATGPT
-- Nội dung sinh từ các file Markdown prompt NotebookLM trong cuộc trò chuyện.
-- Chỉ thêm categories/courses/course_categories/course_sections/lessons.
-- Video URL lưu dạng relative path: /videos/{course_folder}/{file_name}
-- ==========================================================

SET FOREIGN_KEY_CHECKS = 0;

INSERT INTO categories (id, parent_id, name, slug, description, sort_order, status, created_at, updated_at, deleted_at) VALUES
    (8, NULL, 'AI & Học tập thông minh', 'ai-hoc-tap-thong-minh', 'Danh mục demo MindHub: AI & Học tập thông minh.', 101, 'active', '2026-06-27 08:00:00', '2026-06-27 08:00:00', NULL),
    (9, 8, 'AI Learning', 'ai-learning', 'Danh mục demo MindHub: AI Learning.', 1, 'active', '2026-06-27 08:00:00', '2026-06-27 08:00:00', NULL),
    (10, NULL, 'Backend & API Testing', 'backend-api-testing', 'Danh mục demo MindHub: Backend & API Testing.', 102, 'active', '2026-06-27 08:00:00', '2026-06-27 08:00:00', NULL),
    (11, 10, 'Postman API Testing', 'postman-api-testing', 'Danh mục demo MindHub: Postman API Testing.', 1, 'active', '2026-06-27 08:00:00', '2026-06-27 08:00:00', NULL),
    (12, NULL, 'Công cụ lập trình & Làm việc nhóm', 'cong-cu-lap-trinh-lam-viec-nhom', 'Danh mục demo MindHub: Công cụ lập trình & Làm việc nhóm.', 103, 'active', '2026-06-27 08:00:00', '2026-06-27 08:00:00', NULL),
    (13, 12, 'Git & GitHub', 'git-github', 'Danh mục demo MindHub: Git & GitHub.', 1, 'active', '2026-06-27 08:00:00', '2026-06-27 08:00:00', NULL),
    (14, NULL, 'Database & Data Modeling', 'database-data-modeling', 'Danh mục demo MindHub: Database & Data Modeling.', 104, 'active', '2026-06-27 08:00:00', '2026-06-27 08:00:00', NULL),
    (15, 14, 'MySQL Database Design', 'mysql-database-design', 'Danh mục demo MindHub: MySQL Database Design.', 1, 'active', '2026-06-27 08:00:00', '2026-06-27 08:00:00', NULL),
    (16, NULL, 'DevOps & Triển khai', 'devops-trien-khai', 'Danh mục demo MindHub: DevOps & Triển khai.', 105, 'active', '2026-06-27 08:00:00', '2026-06-27 08:00:00', NULL),
    (17, 16, 'Deploy VPS aaPanel', 'deploy-vps-aapanel', 'Danh mục demo MindHub: Deploy VPS aaPanel.', 1, 'active', '2026-06-27 08:00:00', '2026-06-27 08:00:00', NULL),
    (18, NULL, 'Frontend UI UX', 'frontend-ui-ux', 'Danh mục demo MindHub: Frontend UI UX.', 106, 'active', '2026-06-27 08:00:00', '2026-06-27 08:00:00', NULL),
    (19, 18, 'Tailwind CSS', 'tailwind-css', 'Danh mục demo MindHub: Tailwind CSS.', 1, 'active', '2026-06-27 08:00:00', '2026-06-27 08:00:00', NULL),
    (20, NULL, 'Kinh doanh số & Freelance Web', 'kinh-doanh-so-va-freelance-web', 'Danh mục demo MindHub: Kinh doanh số & Freelance Web.', 107, 'active', '2026-06-27 08:00:00', '2026-06-27 08:00:00', NULL),
    (21, 20, 'Freelance Web Developer', 'freelance-web-developer', 'Danh mục demo MindHub: Freelance Web Developer.', 1, 'active', '2026-06-27 08:00:00', '2026-06-27 08:00:00', NULL),
    (22, 20, 'Sản phẩm số & SaaS', 'san-pham-so-va-saas', 'Danh mục demo MindHub: Sản phẩm số & SaaS.', 1, 'active', '2026-06-27 08:00:00', '2026-06-27 08:00:00', NULL),
    (23, NULL, 'Kỹ năng mềm cho lập trình viên', 'ky-nang-mem-cho-lap-trinh-vien', 'Danh mục demo MindHub: Kỹ năng mềm cho lập trình viên.', 111, 'active', '2026-06-27 08:00:00', '2026-06-27 08:00:00', NULL),
    (24, 23, 'Giao tiếp & Làm việc nhóm', 'giao-tiep-va-lam-viec-nhom', 'Danh mục demo MindHub: Giao tiếp & Làm việc nhóm.', 1, 'active', '2026-06-27 08:00:00', '2026-06-27 08:00:00', NULL),
    (25, 23, 'Tư duy nghề nghiệp & Phỏng vấn', 'tu-duy-nghe-nghiep-va-phong-van', 'Danh mục demo MindHub: Tư duy nghề nghiệp & Phỏng vấn.', 1, 'active', '2026-06-27 08:00:00', '2026-06-27 08:00:00', NULL),
    (26, NULL, 'Lập trình Web', 'lap-trinh-web', 'Danh mục demo MindHub: Lập trình Web.', 115, 'active', '2026-06-27 08:00:00', '2026-06-27 08:00:00', NULL),
    (27, 26, 'Backend Laravel', 'backend-laravel', 'Danh mục demo MindHub: Backend Laravel.', 1, 'active', '2026-06-27 08:00:00', '2026-06-27 08:00:00', NULL),
    (28, 26, 'Frontend React', 'frontend-react', 'Danh mục demo MindHub: Frontend React.', 1, 'active', '2026-06-27 08:00:00', '2026-06-27 08:00:00', NULL),
    (29, NULL, 'Marketing Internet cho Web Developer', 'marketing-internet-cho-web-developer', 'Danh mục demo MindHub: Marketing Internet cho Web Developer.', 117, 'active', '2026-06-27 08:00:00', '2026-06-27 08:00:00', NULL),
    (30, 29, 'Landing Page & Conversion', 'landing-page-va-conversion', 'Danh mục demo MindHub: Landing Page & Conversion.', 1, 'active', '2026-06-27 08:00:00', '2026-06-27 08:00:00', NULL),
    (31, 29, 'SEO & Content Website', 'seo-va-content-website', 'Danh mục demo MindHub: SEO & Content Website.', 1, 'active', '2026-06-27 08:00:00', '2026-06-27 08:00:00', NULL),
    (32, NULL, 'Payment & E-commerce', 'payment-e-commerce', 'Danh mục demo MindHub: Payment & E-commerce.', 121, 'active', '2026-06-27 08:00:00', '2026-06-27 08:00:00', NULL),
    (33, 32, 'VNPay Laravel', 'vnpay-laravel', 'Danh mục demo MindHub: VNPay Laravel.', 1, 'active', '2026-06-27 08:00:00', '2026-06-27 08:00:00', NULL),
    (34, NULL, 'Định hướng nghề nghiệp & Phỏng vấn', 'dinh-huong-nghe-nghiep-phong-van', 'Danh mục demo MindHub: Định hướng nghề nghiệp & Phỏng vấn.', 122, 'active', '2026-06-27 08:00:00', '2026-06-27 08:00:00', NULL),
    (35, 34, 'Lộ trình Web Developer', 'lo-trinh-web-developer', 'Danh mục demo MindHub: Lộ trình Web Developer.', 1, 'active', '2026-06-27 08:00:00', '2026-06-27 08:00:00', NULL),
    (36, 34, 'Phỏng vấn Backend Developer', 'phong-van-backend-developer', 'Danh mục demo MindHub: Phỏng vấn Backend Developer.', 1, 'active', '2026-06-27 08:00:00', '2026-06-27 08:00:00', NULL);

INSERT INTO courses (id, instructor_id, title, slug, short_description, description, thumbnail_url, intro_video_url, price, sale_price, level, language, requirements, outcomes, status, is_featured, total_duration_seconds, published_at, admin_reject_reason, created_at, updated_at, deleted_at) VALUES
    (9, 3, 'AI Learning cho sinh viên IT và E-learning', 'ai-learning-cho-sinh-vien-it-va-e-learning', 'Khóa học demo MindHub gồm 30 video, bám theo lộ trình AI Learning.', 'Khóa học được seed từ file prompt NotebookLM MindHub_AI_LEARNING_30_NotebookLM_Prompts.md. Nội dung dùng cho demo sản phẩm và có thể dùng làm tài liệu học bổ trợ thực tế cho lập trình web.', '/thumbnails/courses/ai-learning.jpg', '/videos/ai-learning/ai-learning-01-ai-trong-hoc-tap-la-gi.mp4', 499000.0, 299000.0, 'beginner', 'vi', 'Có tinh thần tự học, biết sử dụng máy tính cơ bản. Một số khóa kỹ thuật yêu cầu biết HTML/CSS, JavaScript, PHP hoặc Laravel cơ bản.', 'Hoàn thành 30 bài học, nắm được kiến thức chính và biết áp dụng vào đồ án hoặc dự án web thực tế.', 'published', 1, 18000, '2026-06-27 08:00:00', NULL, '2026-06-27 08:00:00', '2026-06-27 08:00:00', NULL),
    (10, 2, 'Postman API Testing cho Laravel Backend', 'postman-api-testing-cho-laravel-backend', 'Khóa học demo MindHub gồm 30 video, bám theo lộ trình Postman API Testing.', 'Khóa học được seed từ file prompt NotebookLM MindHub_POSTMAN_API_TESTING_30_NotebookLM_Prompts.md. Nội dung dùng cho demo sản phẩm và có thể dùng làm tài liệu học bổ trợ thực tế cho lập trình web.', '/thumbnails/courses/postman-api-testing.jpg', '/videos/postman-api-testing/postman-api-testing-01-postman-la-gi-va-vi-sao-backend-fresher-nen-biet.mp4', 499000.0, 299000.0, 'beginner', 'vi', 'Có tinh thần tự học, biết sử dụng máy tính cơ bản. Một số khóa kỹ thuật yêu cầu biết HTML/CSS, JavaScript, PHP hoặc Laravel cơ bản.', 'Hoàn thành 30 bài học, nắm được kiến thức chính và biết áp dụng vào đồ án hoặc dự án web thực tế.', 'published', 1, 18000, '2026-06-27 08:00:00', NULL, '2026-06-27 08:00:00', '2026-06-27 08:00:00', NULL),
    (11, 2, 'Git & GitHub làm việc nhóm đồ án', 'git-github-lam-viec-nhom-do-an', 'Khóa học demo MindHub gồm 30 video, bám theo lộ trình Git & GitHub.', 'Khóa học được seed từ file prompt NotebookLM MindHub_GIT_GITHUB_30_NotebookLM_Prompts.md. Nội dung dùng cho demo sản phẩm và có thể dùng làm tài liệu học bổ trợ thực tế cho lập trình web.', '/thumbnails/courses/git-github.jpg', '/videos/git-github/git-github-01-vi-sao-lam-do-an-nhom-phai-dung-git.mp4', 199000.0, 99000.0, 'beginner', 'vi', 'Có tinh thần tự học, biết sử dụng máy tính cơ bản. Một số khóa kỹ thuật yêu cầu biết HTML/CSS, JavaScript, PHP hoặc Laravel cơ bản.', 'Hoàn thành 30 bài học, nắm được kiến thức chính và biết áp dụng vào đồ án hoặc dự án web thực tế.', 'published', 1, 18000, '2026-06-27 08:00:00', NULL, '2026-06-27 08:00:00', '2026-06-27 08:00:00', NULL),
    (12, 2, 'MySQL Database Design cho dự án E-learning', 'mysql-database-design-cho-du-an-e-learning', 'Khóa học demo MindHub gồm 30 video, bám theo lộ trình MySQL Database Design.', 'Khóa học được seed từ file prompt NotebookLM MindHub_MYSQL_DATABASE_DESIGN_30_NotebookLM_Prompts.md. Nội dung dùng cho demo sản phẩm và có thể dùng làm tài liệu học bổ trợ thực tế cho lập trình web.', '/thumbnails/courses/mysql-database-design.jpg', '/videos/mysql-database-design/mysql-database-design-01-database-trong-he-thong-e-learning-dung-de-lam-gi.mp4', 499000.0, 299000.0, 'beginner', 'vi', 'Có tinh thần tự học, biết sử dụng máy tính cơ bản. Một số khóa kỹ thuật yêu cầu biết HTML/CSS, JavaScript, PHP hoặc Laravel cơ bản.', 'Hoàn thành 30 bài học, nắm được kiến thức chính và biết áp dụng vào đồ án hoặc dự án web thực tế.', 'published', 1, 18000, '2026-06-27 08:00:00', NULL, '2026-06-27 08:00:00', '2026-06-27 08:00:00', NULL),
    (13, 3, 'Deploy Fullstack Laravel React lên VPS aaPanel', 'deploy-fullstack-laravel-react-len-vps-aapanel', 'Khóa học demo MindHub gồm 30 video, bám theo lộ trình Deploy VPS aaPanel.', 'Khóa học được seed từ file prompt NotebookLM MindHub_DEPLOY_VPS_AAPANEL_30_NotebookLM_Prompts.md. Nội dung dùng cho demo sản phẩm và có thể dùng làm tài liệu học bổ trợ thực tế cho lập trình web.', '/thumbnails/courses/deploy-vps-aapanel.jpg', '/videos/deploy-vps-aapanel/deploy-vps-aapanel-01-deploy-fullstack-la-gi.mp4', 499000.0, 299000.0, 'beginner', 'vi', 'Có tinh thần tự học, biết sử dụng máy tính cơ bản. Một số khóa kỹ thuật yêu cầu biết HTML/CSS, JavaScript, PHP hoặc Laravel cơ bản.', 'Hoàn thành 30 bài học, nắm được kiến thức chính và biết áp dụng vào đồ án hoặc dự án web thực tế.', 'published', 1, 18000, '2026-06-27 08:00:00', NULL, '2026-06-27 08:00:00', '2026-06-27 08:00:00', NULL),
    (14, 3, 'Tailwind CSS UI cho Dashboard E-learning', 'tailwind-css-ui-cho-dashboard-e-learning', 'Khóa học demo MindHub gồm 30 video, bám theo lộ trình Tailwind CSS.', 'Khóa học được seed từ file prompt NotebookLM MindHub_TAILWIND_UI_ELEARNING_30_NotebookLM_Prompts.md. Nội dung dùng cho demo sản phẩm và có thể dùng làm tài liệu học bổ trợ thực tế cho lập trình web.', '/thumbnails/courses/tailwind-ui-elearning.jpg', '/videos/tailwind-ui-elearning/tailwind-ui-elearning-01-tailwind-css-la-gi-va-phu-hop-voi-do-an-nhu-the-nao.mp4', 499000.0, 299000.0, 'beginner', 'vi', 'Có tinh thần tự học, biết sử dụng máy tính cơ bản. Một số khóa kỹ thuật yêu cầu biết HTML/CSS, JavaScript, PHP hoặc Laravel cơ bản.', 'Hoàn thành 30 bài học, nắm được kiến thức chính và biết áp dụng vào đồ án hoặc dự án web thực tế.', 'published', 1, 18000, '2026-06-27 08:00:00', NULL, '2026-06-27 08:00:00', '2026-06-27 08:00:00', NULL),
    (15, 2, 'Báo giá hợp đồng và quản lý dự án web nhỏ', 'bao-gia-hop-dong-va-quan-ly-du-an-web-nho', 'Khóa học demo MindHub gồm 20 video, bám theo lộ trình Freelance Web Developer.', 'Khóa học được seed từ file prompt NotebookLM MindHub_BIZ_WEB_PROJECT_MGMT_20_NotebookLM_Prompts.md. Nội dung dùng cho demo sản phẩm và có thể dùng làm tài liệu học bổ trợ thực tế cho lập trình web.', '/thumbnails/courses/web-project-management.jpg', '/videos/web-project-management/web-project-management-01-vi-sao-phai-chot-scope-truoc-khi-bao-gia.mp4', 299000.0, 199000.0, 'beginner', 'vi', 'Có tinh thần tự học, biết sử dụng máy tính cơ bản. Một số khóa kỹ thuật yêu cầu biết HTML/CSS, JavaScript, PHP hoặc Laravel cơ bản.', 'Hoàn thành 20 bài học, nắm được kiến thức chính và biết áp dụng vào đồ án hoặc dự án web thực tế.', 'published', 1, 12000, '2026-06-27 08:00:00', NULL, '2026-06-27 08:00:00', '2026-06-27 08:00:00', NULL),
    (16, 2, 'Freelance Web Developer từ Portfolio đến khách hàng đầu tiên', 'freelance-web-developer-tu-portfolio-den-khach-hang-dau-tien', 'Khóa học demo MindHub gồm 20 video, bám theo lộ trình Freelance Web Developer.', 'Khóa học được seed từ file prompt NotebookLM MindHub_BIZ_FREELANCE_WEB_20_NotebookLM_Prompts.md. Nội dung dùng cho demo sản phẩm và có thể dùng làm tài liệu học bổ trợ thực tế cho lập trình web.', '/thumbnails/courses/freelance-web-developer.jpg', '/videos/freelance-web-developer/freelance-web-developer-01-freelance-web-developer-la-gi.mp4', 299000.0, 199000.0, 'beginner', 'vi', 'Có tinh thần tự học, biết sử dụng máy tính cơ bản. Một số khóa kỹ thuật yêu cầu biết HTML/CSS, JavaScript, PHP hoặc Laravel cơ bản.', 'Hoàn thành 20 bài học, nắm được kiến thức chính và biết áp dụng vào đồ án hoặc dự án web thực tế.', 'published', 1, 12000, '2026-06-27 08:00:00', NULL, '2026-06-27 08:00:00', '2026-06-27 08:00:00', NULL),
    (17, 2, 'Tư duy sản phẩm SaaS cho lập trình viên Web', 'tu-duy-san-pham-saas-cho-lap-trinh-vien-web', 'Khóa học demo MindHub gồm 20 video, bám theo lộ trình Sản phẩm số & SaaS.', 'Khóa học được seed từ file prompt NotebookLM MindHub_BIZ_SAAS_WEBDEV_20_NotebookLM_Prompts.md. Nội dung dùng cho demo sản phẩm và có thể dùng làm tài liệu học bổ trợ thực tế cho lập trình web.', '/thumbnails/courses/saas-product-thinking.jpg', '/videos/saas-product-thinking/saas-product-thinking-01-saas-la-gi-va-khac-website-thong-thuong-the-nao.mp4', 299000.0, 199000.0, 'beginner', 'vi', 'Có tinh thần tự học, biết sử dụng máy tính cơ bản. Một số khóa kỹ thuật yêu cầu biết HTML/CSS, JavaScript, PHP hoặc Laravel cơ bản.', 'Hoàn thành 20 bài học, nắm được kiến thức chính và biết áp dụng vào đồ án hoặc dự án web thực tế.', 'published', 1, 12000, '2026-06-27 08:00:00', NULL, '2026-06-27 08:00:00', '2026-06-27 08:00:00', NULL),
    (18, 2, 'Xây MVP sản phẩm Web cho người mới', 'xay-mvp-san-pham-web-cho-nguoi-moi', 'Khóa học demo MindHub gồm 20 video, bám theo lộ trình Sản phẩm số & SaaS.', 'Khóa học được seed từ file prompt NotebookLM MindHub_BIZ_MVP_WEB_20_NotebookLM_Prompts.md. Nội dung dùng cho demo sản phẩm và có thể dùng làm tài liệu học bổ trợ thực tế cho lập trình web.', '/thumbnails/courses/mvp-web-product.jpg', '/videos/mvp-web-product/mvp-web-product-01-mvp-la-gi-va-vi-sao-nguoi-lam-web-nen-biet.mp4', 299000.0, 199000.0, 'beginner', 'vi', 'Có tinh thần tự học, biết sử dụng máy tính cơ bản. Một số khóa kỹ thuật yêu cầu biết HTML/CSS, JavaScript, PHP hoặc Laravel cơ bản.', 'Hoàn thành 20 bài học, nắm được kiến thức chính và biết áp dụng vào đồ án hoặc dự án web thực tế.', 'published', 1, 12000, '2026-06-27 08:00:00', NULL, '2026-06-27 08:00:00', '2026-06-27 08:00:00', NULL),
    (19, 2, 'Giao tiếp trong team IT cho Web Developer', 'giao-tiep-trong-team-it-cho-web-developer', 'Khóa học demo MindHub gồm 20 video, bám theo lộ trình Giao tiếp & Làm việc nhóm.', 'Khóa học được seed từ file prompt NotebookLM MindHub_SOFT_COMM_IT_20_NotebookLM_Prompts.md. Nội dung dùng cho demo sản phẩm và có thể dùng làm tài liệu học bổ trợ thực tế cho lập trình web.', '/thumbnails/courses/soft-communication-it.jpg', '/videos/soft-communication-it/soft-communication-it-01-vi-sao-lap-trinh-vien-web-can-giao-tiep-tot.mp4', 299000.0, 199000.0, 'beginner', 'vi', 'Có tinh thần tự học, biết sử dụng máy tính cơ bản. Một số khóa kỹ thuật yêu cầu biết HTML/CSS, JavaScript, PHP hoặc Laravel cơ bản.', 'Hoàn thành 20 bài học, nắm được kiến thức chính và biết áp dụng vào đồ án hoặc dự án web thực tế.', 'published', 1, 12000, '2026-06-27 08:00:00', NULL, '2026-06-27 08:00:00', '2026-06-27 08:00:00', NULL),
    (20, 2, 'Teamwork Agile cho dự án Web sinh viên', 'teamwork-agile-cho-du-an-web-sinh-vien', 'Khóa học demo MindHub gồm 20 video, bám theo lộ trình Giao tiếp & Làm việc nhóm.', 'Khóa học được seed từ file prompt NotebookLM MindHub_SOFT_TEAMWORK_AGILE_20_NotebookLM_Prompts.md. Nội dung dùng cho demo sản phẩm và có thể dùng làm tài liệu học bổ trợ thực tế cho lập trình web.', '/thumbnails/courses/teamwork-agile-web.jpg', '/videos/teamwork-agile-web/teamwork-agile-web-01-teamwork-trong-du-an-web-khac-lam-bai-ca-nhan-the-nao.mp4', 299000.0, 199000.0, 'beginner', 'vi', 'Có tinh thần tự học, biết sử dụng máy tính cơ bản. Một số khóa kỹ thuật yêu cầu biết HTML/CSS, JavaScript, PHP hoặc Laravel cơ bản.', 'Hoàn thành 20 bài học, nắm được kiến thức chính và biết áp dụng vào đồ án hoặc dự án web thực tế.', 'published', 1, 12000, '2026-06-27 08:00:00', NULL, '2026-06-27 08:00:00', '2026-06-27 08:00:00', NULL),
    (21, 2, 'Kỹ năng trình bày project Web khi phỏng vấn', 'ky-nang-trinh-bay-project-web-khi-phong-van', 'Khóa học demo MindHub gồm 20 video, bám theo lộ trình Tư duy nghề nghiệp & Phỏng vấn.', 'Khóa học được seed từ file prompt NotebookLM MindHub_SOFT_PRESENT_PROJECT_20_NotebookLM_Prompts.md. Nội dung dùng cho demo sản phẩm và có thể dùng làm tài liệu học bổ trợ thực tế cho lập trình web.', '/thumbnails/courses/present-web-project.jpg', '/videos/present-web-project/present-web-project-01-vi-sao-can-biet-trinh-bay-project-web.mp4', 299000.0, 199000.0, 'beginner', 'vi', 'Có tinh thần tự học, biết sử dụng máy tính cơ bản. Một số khóa kỹ thuật yêu cầu biết HTML/CSS, JavaScript, PHP hoặc Laravel cơ bản.', 'Hoàn thành 20 bài học, nắm được kiến thức chính và biết áp dụng vào đồ án hoặc dự án web thực tế.', 'published', 1, 12000, '2026-06-27 08:00:00', NULL, '2026-06-27 08:00:00', '2026-06-27 08:00:00', NULL),
    (22, 3, 'Tư duy giải quyết vấn đề cho lập trình viên Web', 'tu-duy-giai-quyet-van-de-cho-lap-trinh-vien-web', 'Khóa học demo MindHub gồm 20 video, bám theo lộ trình Tư duy nghề nghiệp & Phỏng vấn.', 'Khóa học được seed từ file prompt NotebookLM MindHub_SOFT_PROBLEM_SOLVING_20_NotebookLM_Prompts.md. Nội dung dùng cho demo sản phẩm và có thể dùng làm tài liệu học bổ trợ thực tế cho lập trình web.', '/thumbnails/courses/problem-solving-webdev.jpg', '/videos/problem-solving-webdev/problem-solving-webdev-01-vi-sao-khong-nen-lao-vao-code-khi-chua-hieu-yeu-cau.mp4', 299000.0, 199000.0, 'beginner', 'vi', 'Có tinh thần tự học, biết sử dụng máy tính cơ bản. Một số khóa kỹ thuật yêu cầu biết HTML/CSS, JavaScript, PHP hoặc Laravel cơ bản.', 'Hoàn thành 20 bài học, nắm được kiến thức chính và biết áp dụng vào đồ án hoặc dự án web thực tế.', 'published', 1, 12000, '2026-06-27 08:00:00', NULL, '2026-06-27 08:00:00', '2026-06-27 08:00:00', NULL),
    (23, 3, 'React E-learning Frontend từ cơ bản đến thực chiến', 'react-e-learning-frontend-tu-co-ban-den-thuc-chien', 'Khóa học demo MindHub gồm 30 video, bám theo lộ trình Frontend React.', 'Khóa học được seed từ file prompt NotebookLM MindHub_REACT_ELEARNING_30_NotebookLM_Prompts.md. Nội dung dùng cho demo sản phẩm và có thể dùng làm tài liệu học bổ trợ thực tế cho lập trình web.', '/thumbnails/courses/react-elearning.jpg', '/videos/react-elearning/react-elearning-01-react-la-gi-va-vi-sao-dung-cho-e-learning.mp4', 499000.0, 299000.0, 'beginner', 'vi', 'Có tinh thần tự học, biết sử dụng máy tính cơ bản. Một số khóa kỹ thuật yêu cầu biết HTML/CSS, JavaScript, PHP hoặc Laravel cơ bản.', 'Hoàn thành 30 bài học, nắm được kiến thức chính và biết áp dụng vào đồ án hoặc dự án web thực tế.', 'published', 1, 18000, '2026-06-27 08:00:00', NULL, '2026-06-27 08:00:00', '2026-06-27 08:00:00', NULL),
    (24, 3, 'Thiết kế Landing Page chuyển đổi cao cho sản phẩm Web', 'thiet-ke-landing-page-chuyen-doi-cao-cho-san-pham-web', 'Khóa học demo MindHub gồm 20 video, bám theo lộ trình Landing Page & Conversion.', 'Khóa học được seed từ file prompt NotebookLM MindHub_MKT_LANDING_CONVERSION_20_NotebookLM_Prompts.md. Nội dung dùng cho demo sản phẩm và có thể dùng làm tài liệu học bổ trợ thực tế cho lập trình web.', '/thumbnails/courses/landing-page-conversion.jpg', '/videos/landing-page-conversion/landing-page-conversion-01-landing-page-la-gi-va-khac-homepage-the-nao.mp4', 299000.0, 199000.0, 'beginner', 'vi', 'Có tinh thần tự học, biết sử dụng máy tính cơ bản. Một số khóa kỹ thuật yêu cầu biết HTML/CSS, JavaScript, PHP hoặc Laravel cơ bản.', 'Hoàn thành 20 bài học, nắm được kiến thức chính và biết áp dụng vào đồ án hoặc dự án web thực tế.', 'published', 1, 12000, '2026-06-27 08:00:00', NULL, '2026-06-27 08:00:00', '2026-06-27 08:00:00', NULL),
    (25, 3, 'Web Analytics và A/B Testing cơ bản', 'web-analytics-va-a-b-testing-co-ban', 'Khóa học demo MindHub gồm 20 video, bám theo lộ trình Landing Page & Conversion.', 'Khóa học được seed từ file prompt NotebookLM MindHub_MKT_ANALYTICS_ABTEST_20_NotebookLM_Prompts.md. Nội dung dùng cho demo sản phẩm và có thể dùng làm tài liệu học bổ trợ thực tế cho lập trình web.', '/thumbnails/courses/web-analytics-ab-testing.jpg', '/videos/web-analytics-ab-testing/web-analytics-ab-testing-01-web-analytics-la-gi-va-vi-sao-web-developer-nen-biet.mp4', 299000.0, 199000.0, 'beginner', 'vi', 'Có tinh thần tự học, biết sử dụng máy tính cơ bản. Một số khóa kỹ thuật yêu cầu biết HTML/CSS, JavaScript, PHP hoặc Laravel cơ bản.', 'Hoàn thành 20 bài học, nắm được kiến thức chính và biết áp dụng vào đồ án hoặc dự án web thực tế.', 'published', 1, 12000, '2026-06-27 08:00:00', NULL, '2026-06-27 08:00:00', '2026-06-27 08:00:00', NULL),
    (26, 3, 'Content Marketing cho Landing Page và Blog công nghệ', 'content-marketing-cho-landing-page-va-blog-cong-nghe', 'Khóa học demo MindHub gồm 20 video, bám theo lộ trình SEO & Content Website.', 'Khóa học được seed từ file prompt NotebookLM MindHub_MKT_CONTENT_WEB_20_NotebookLM_Prompts.md. Nội dung dùng cho demo sản phẩm và có thể dùng làm tài liệu học bổ trợ thực tế cho lập trình web.', '/thumbnails/courses/content-marketing-web.jpg', '/videos/content-marketing-web/content-marketing-web-01-content-marketing-la-gi-trong-website-cong-nghe.mp4', 299000.0, 199000.0, 'beginner', 'vi', 'Có tinh thần tự học, biết sử dụng máy tính cơ bản. Một số khóa kỹ thuật yêu cầu biết HTML/CSS, JavaScript, PHP hoặc Laravel cơ bản.', 'Hoàn thành 20 bài học, nắm được kiến thức chính và biết áp dụng vào đồ án hoặc dự án web thực tế.', 'published', 1, 12000, '2026-06-27 08:00:00', NULL, '2026-06-27 08:00:00', '2026-06-27 08:00:00', NULL),
    (27, 3, 'SEO cơ bản cho Web Developer', 'seo-co-ban-cho-web-developer', 'Khóa học demo MindHub gồm 20 video, bám theo lộ trình SEO & Content Website.', 'Khóa học được seed từ file prompt NotebookLM MindHub_MKT_SEO_WEBDEV_20_NotebookLM_Prompts.md. Nội dung dùng cho demo sản phẩm và có thể dùng làm tài liệu học bổ trợ thực tế cho lập trình web.', '/thumbnails/courses/seo-for-webdev.jpg', '/videos/seo-for-webdev/seo-for-webdev-01-seo-la-gi-va-vi-sao-web-developer-nen-biet.mp4', 299000.0, 199000.0, 'beginner', 'vi', 'Có tinh thần tự học, biết sử dụng máy tính cơ bản. Một số khóa kỹ thuật yêu cầu biết HTML/CSS, JavaScript, PHP hoặc Laravel cơ bản.', 'Hoàn thành 20 bài học, nắm được kiến thức chính và biết áp dụng vào đồ án hoặc dự án web thực tế.', 'published', 1, 12000, '2026-06-27 08:00:00', NULL, '2026-06-27 08:00:00', '2026-06-27 08:00:00', NULL),
    (28, 2, 'Tích hợp VNPay Payment với Laravel E-learning', 'tich-hop-vnpay-payment-voi-laravel-e-learning', 'Khóa học demo MindHub gồm 30 video, bám theo lộ trình VNPay Laravel.', 'Khóa học được seed từ file prompt NotebookLM MindHub_VNPAY_LARAVEL_PAYMENT_30_NotebookLM_Prompts.md. Nội dung dùng cho demo sản phẩm và có thể dùng làm tài liệu học bổ trợ thực tế cho lập trình web.', '/thumbnails/courses/vnpay-laravel-payment.jpg', '/videos/vnpay-laravel-payment/vnpay-laravel-payment-01-payment-flow-trong-e-learning-hoat-dong-the-nao.mp4', 599000.0, 399000.0, 'intermediate', 'vi', 'Có tinh thần tự học, biết sử dụng máy tính cơ bản. Một số khóa kỹ thuật yêu cầu biết HTML/CSS, JavaScript, PHP hoặc Laravel cơ bản.', 'Hoàn thành 30 bài học, nắm được kiến thức chính và biết áp dụng vào đồ án hoặc dự án web thực tế.', 'published', 1, 18000, '2026-06-27 08:00:00', NULL, '2026-06-27 08:00:00', '2026-06-27 08:00:00', NULL),
    (29, 2, 'Lộ trình xin việc Web Developer cho sinh viên IT', 'lo-trinh-xin-viec-web-developer-cho-sinh-vien-it', 'Khóa học demo MindHub gồm 30 video, bám theo lộ trình Lộ trình Web Developer.', 'Khóa học được seed từ file prompt NotebookLM MindHub_CAREER_WEBDEV_30_NotebookLM_Prompts.md. Nội dung dùng cho demo sản phẩm và có thể dùng làm tài liệu học bổ trợ thực tế cho lập trình web.', '/thumbnails/courses/career-webdev.jpg', '/videos/career-webdev/career-webdev-01-web-developer-can-hoc-gi-de-di-thuc-tap.mp4', 499000.0, 299000.0, 'beginner', 'vi', 'Có tinh thần tự học, biết sử dụng máy tính cơ bản. Một số khóa kỹ thuật yêu cầu biết HTML/CSS, JavaScript, PHP hoặc Laravel cơ bản.', 'Hoàn thành 30 bài học, nắm được kiến thức chính và biết áp dụng vào đồ án hoặc dự án web thực tế.', 'published', 1, 18000, '2026-06-27 08:00:00', NULL, '2026-06-27 08:00:00', '2026-06-27 08:00:00', NULL),
    (30, 2, 'Phỏng vấn Backend Developer Fresher', 'phong-van-backend-developer-fresher', 'Khóa học demo MindHub gồm 30 video, bám theo lộ trình Phỏng vấn Backend Developer.', 'Khóa học được seed từ file prompt NotebookLM MindHub_BACKEND_INTERVIEW_30_NotebookLM_Prompts.md. Nội dung dùng cho demo sản phẩm và có thể dùng làm tài liệu học bổ trợ thực tế cho lập trình web.', '/thumbnails/courses/backend-interview.jpg', '/videos/backend-interview/backend-interview-01-backend-developer-fresher-can-biet-gi.mp4', 499000.0, 299000.0, 'beginner', 'vi', 'Có tinh thần tự học, biết sử dụng máy tính cơ bản. Một số khóa kỹ thuật yêu cầu biết HTML/CSS, JavaScript, PHP hoặc Laravel cơ bản.', 'Hoàn thành 30 bài học, nắm được kiến thức chính và biết áp dụng vào đồ án hoặc dự án web thực tế.', 'published', 1, 18000, '2026-06-27 08:00:00', NULL, '2026-06-27 08:00:00', '2026-06-27 08:00:00', NULL);

INSERT INTO course_categories (category_id, course_id, created_at) VALUES
    (9, 9, '2026-06-27 08:00:00'),
    (11, 10, '2026-06-27 08:00:00'),
    (13, 11, '2026-06-27 08:00:00'),
    (15, 12, '2026-06-27 08:00:00'),
    (17, 13, '2026-06-27 08:00:00'),
    (19, 14, '2026-06-27 08:00:00'),
    (21, 15, '2026-06-27 08:00:00'),
    (21, 16, '2026-06-27 08:00:00'),
    (22, 17, '2026-06-27 08:00:00'),
    (22, 18, '2026-06-27 08:00:00'),
    (24, 19, '2026-06-27 08:00:00'),
    (24, 20, '2026-06-27 08:00:00'),
    (25, 21, '2026-06-27 08:00:00'),
    (25, 22, '2026-06-27 08:00:00'),
    (28, 23, '2026-06-27 08:00:00'),
    (30, 24, '2026-06-27 08:00:00'),
    (30, 25, '2026-06-27 08:00:00'),
    (31, 26, '2026-06-27 08:00:00'),
    (31, 27, '2026-06-27 08:00:00'),
    (33, 28, '2026-06-27 08:00:00'),
    (35, 29, '2026-06-27 08:00:00'),
    (36, 30, '2026-06-27 08:00:00');

INSERT INTO course_sections (id, course_id, title, description, sort_order, status, created_at, updated_at, deleted_at) VALUES
    (13, 9, 'Nhập môn AI trong học tập', 'Chương 1 của khóa AI Learning cho sinh viên IT và E-learning.', 1, 'published', '2026-06-27 08:00:00', '2026-06-27 08:00:00', NULL),
    (14, 9, 'Dùng AI để học lập trình hiệu quả', 'Chương 2 của khóa AI Learning cho sinh viên IT và E-learning.', 2, 'published', '2026-06-27 08:00:00', '2026-06-27 08:00:00', NULL),
    (15, 9, 'AI cho đồ án E-learning MindHub', 'Chương 3 của khóa AI Learning cho sinh viên IT và E-learning.', 3, 'published', '2026-06-27 08:00:00', '2026-06-27 08:00:00', NULL),
    (16, 9, 'Kỹ năng prompt thực chiến', 'Chương 4 của khóa AI Learning cho sinh viên IT và E-learning.', 4, 'published', '2026-06-27 08:00:00', '2026-06-27 08:00:00', NULL),
    (17, 9, 'Đạo đức, kiểm chứng và an toàn khi dùng AI', 'Chương 5 của khóa AI Learning cho sinh viên IT và E-learning.', 5, 'published', '2026-06-27 08:00:00', '2026-06-27 08:00:00', NULL),
    (18, 9, 'Xây workflow học tập với AI', 'Chương 6 của khóa AI Learning cho sinh viên IT và E-learning.', 6, 'published', '2026-06-27 08:00:00', '2026-06-27 08:00:00', NULL),
    (19, 10, 'Làm quen với Postman và API Testing', 'Chương 1 của khóa Postman API Testing cho Laravel Backend.', 1, 'published', '2026-06-27 08:00:00', '2026-06-27 08:00:00', NULL),
    (20, 10, 'Test API Auth và token', 'Chương 2 của khóa Postman API Testing cho Laravel Backend.', 2, 'published', '2026-06-27 08:00:00', '2026-06-27 08:00:00', NULL),
    (21, 10, 'Collection, environment và dữ liệu test', 'Chương 3 của khóa Postman API Testing cho Laravel Backend.', 3, 'published', '2026-06-27 08:00:00', '2026-06-27 08:00:00', NULL),
    (22, 10, 'Test flow nghiệp vụ E-learning', 'Chương 4 của khóa Postman API Testing cho Laravel Backend.', 4, 'published', '2026-06-27 08:00:00', '2026-06-27 08:00:00', NULL),
    (23, 10, 'Test case thất bại và debug lỗi', 'Chương 5 của khóa Postman API Testing cho Laravel Backend.', 5, 'published', '2026-06-27 08:00:00', '2026-06-27 08:00:00', NULL),
    (24, 10, 'Hoàn thiện bộ test API cho demo đồ án', 'Chương 6 của khóa Postman API Testing cho Laravel Backend.', 6, 'published', '2026-06-27 08:00:00', '2026-06-27 08:00:00', NULL),
    (25, 11, 'Làm quen với Git & GitHub trong đồ án', 'Chương 1 của khóa Git & GitHub làm việc nhóm đồ án.', 1, 'published', '2026-06-27 08:00:00', '2026-06-27 08:00:00', NULL),
    (26, 11, 'Commit, push, pull và branch hằng ngày', 'Chương 2 của khóa Git & GitHub làm việc nhóm đồ án.', 2, 'published', '2026-06-27 08:00:00', '2026-06-27 08:00:00', NULL),
    (27, 11, 'Pull request và workflow làm việc nhóm', 'Chương 3 của khóa Git & GitHub làm việc nhóm đồ án.', 3, 'published', '2026-06-27 08:00:00', '2026-06-27 08:00:00', NULL),
    (28, 11, 'Conflict và lỗi Git thường gặp', 'Chương 4 của khóa Git & GitHub làm việc nhóm đồ án.', 4, 'published', '2026-06-27 08:00:00', '2026-06-27 08:00:00', NULL),
    (29, 11, 'README, bảo mật repo và trình bày GitHub', 'Chương 5 của khóa Git & GitHub làm việc nhóm đồ án.', 5, 'published', '2026-06-27 08:00:00', '2026-06-27 08:00:00', NULL),
    (30, 11, 'Thực chiến Git cho dự án MindHub', 'Chương 6 của khóa Git & GitHub làm việc nhóm đồ án.', 6, 'published', '2026-06-27 08:00:00', '2026-06-27 08:00:00', NULL),
    (31, 12, 'Nền tảng thiết kế database', 'Chương 1 của khóa MySQL Database Design cho dự án E-learning.', 1, 'published', '2026-06-27 08:00:00', '2026-06-27 08:00:00', NULL),
    (32, 12, 'Thiết kế dữ liệu khóa học', 'Chương 2 của khóa MySQL Database Design cho dự án E-learning.', 2, 'published', '2026-06-27 08:00:00', '2026-06-27 08:00:00', NULL),
    (33, 12, 'Thiết kế dữ liệu học tập', 'Chương 3 của khóa MySQL Database Design cho dự án E-learning.', 3, 'published', '2026-06-27 08:00:00', '2026-06-27 08:00:00', NULL),
    (34, 12, 'Order payment và revenue', 'Chương 4 của khóa MySQL Database Design cho dự án E-learning.', 4, 'published', '2026-06-27 08:00:00', '2026-06-27 08:00:00', NULL),
    (35, 12, 'Tối ưu query và chất lượng dữ liệu', 'Chương 5 của khóa MySQL Database Design cho dự án E-learning.', 5, 'published', '2026-06-27 08:00:00', '2026-06-27 08:00:00', NULL),
    (36, 12, 'Checklist database trước khi code backend', 'Chương 6 của khóa MySQL Database Design cho dự án E-learning.', 6, 'published', '2026-06-27 08:00:00', '2026-06-27 08:00:00', NULL),
    (37, 13, 'Hiểu kiến trúc deploy fullstack', 'Chương 1 của khóa Deploy Fullstack Laravel React lên VPS aaPanel.', 1, 'published', '2026-06-27 08:00:00', '2026-06-27 08:00:00', NULL),
    (38, 13, 'Chuẩn bị VPS domain và aaPanel', 'Chương 2 của khóa Deploy Fullstack Laravel React lên VPS aaPanel.', 2, 'published', '2026-06-27 08:00:00', '2026-06-27 08:00:00', NULL),
    (39, 13, 'Deploy Laravel API', 'Chương 3 của khóa Deploy Fullstack Laravel React lên VPS aaPanel.', 3, 'published', '2026-06-27 08:00:00', '2026-06-27 08:00:00', NULL),
    (40, 13, 'Deploy React Frontend', 'Chương 4 của khóa Deploy Fullstack Laravel React lên VPS aaPanel.', 4, 'published', '2026-06-27 08:00:00', '2026-06-27 08:00:00', NULL),
    (41, 13, 'Media server và dữ liệu demo', 'Chương 5 của khóa Deploy Fullstack Laravel React lên VPS aaPanel.', 5, 'published', '2026-06-27 08:00:00', '2026-06-27 08:00:00', NULL),
    (42, 13, 'Fix lỗi và checklist production', 'Chương 6 của khóa Deploy Fullstack Laravel React lên VPS aaPanel.', 6, 'published', '2026-06-27 08:00:00', '2026-06-27 08:00:00', NULL),
    (43, 14, 'Nền tảng Tailwind cho UI E-learning', 'Chương 1 của khóa Tailwind CSS UI cho Dashboard E-learning.', 1, 'published', '2026-06-27 08:00:00', '2026-06-27 08:00:00', NULL),
    (44, 14, 'Thiết kế landing page và course listing', 'Chương 2 của khóa Tailwind CSS UI cho Dashboard E-learning.', 2, 'published', '2026-06-27 08:00:00', '2026-06-27 08:00:00', NULL),
    (45, 14, 'Thiết kế course detail và checkout', 'Chương 3 của khóa Tailwind CSS UI cho Dashboard E-learning.', 3, 'published', '2026-06-27 08:00:00', '2026-06-27 08:00:00', NULL),
    (46, 14, 'Thiết kế learning dashboard và video page', 'Chương 4 của khóa Tailwind CSS UI cho Dashboard E-learning.', 4, 'published', '2026-06-27 08:00:00', '2026-06-27 08:00:00', NULL),
    (47, 14, 'Thiết kế admin instructor dashboard', 'Chương 5 của khóa Tailwind CSS UI cho Dashboard E-learning.', 5, 'published', '2026-06-27 08:00:00', '2026-06-27 08:00:00', NULL),
    (48, 14, 'Hoàn thiện UI trước ngày demo', 'Chương 6 của khóa Tailwind CSS UI cho Dashboard E-learning.', 6, 'published', '2026-06-27 08:00:00', '2026-06-27 08:00:00', NULL),
    (49, 15, 'Hiểu phạm vi dự án web nhỏ', 'Chương 1 của khóa Báo giá hợp đồng và quản lý dự án web nhỏ.', 1, 'published', '2026-06-27 08:00:00', '2026-06-27 08:00:00', NULL),
    (50, 15, 'Báo giá và hợp đồng cơ bản', 'Chương 2 của khóa Báo giá hợp đồng và quản lý dự án web nhỏ.', 2, 'published', '2026-06-27 08:00:00', '2026-06-27 08:00:00', NULL),
    (51, 15, 'Quản lý tiến độ và giao tiếp', 'Chương 3 của khóa Báo giá hợp đồng và quản lý dự án web nhỏ.', 3, 'published', '2026-06-27 08:00:00', '2026-06-27 08:00:00', NULL),
    (52, 15, 'Bàn giao bảo trì và hậu dự án', 'Chương 4 của khóa Báo giá hợp đồng và quản lý dự án web nhỏ.', 4, 'published', '2026-06-27 08:00:00', '2026-06-27 08:00:00', NULL),
    (53, 16, 'Chuẩn bị nền tảng freelance', 'Chương 1 của khóa Freelance Web Developer từ Portfolio đến khách hàng đầu tiên.', 1, 'published', '2026-06-27 08:00:00', '2026-06-27 08:00:00', NULL),
    (54, 16, 'Xây portfolio và hồ sơ bán dịch vụ', 'Chương 2 của khóa Freelance Web Developer từ Portfolio đến khách hàng đầu tiên.', 2, 'published', '2026-06-27 08:00:00', '2026-06-27 08:00:00', NULL),
    (55, 16, 'Tìm và trao đổi với khách hàng', 'Chương 3 của khóa Freelance Web Developer từ Portfolio đến khách hàng đầu tiên.', 3, 'published', '2026-06-27 08:00:00', '2026-06-27 08:00:00', NULL),
    (56, 16, 'Giao dự án và phát triển lâu dài', 'Chương 4 của khóa Freelance Web Developer từ Portfolio đến khách hàng đầu tiên.', 4, 'published', '2026-06-27 08:00:00', '2026-06-27 08:00:00', NULL),
    (57, 17, 'Nhập môn SaaS cho Web Developer', 'Chương 1 của khóa Tư duy sản phẩm SaaS cho lập trình viên Web.', 1, 'published', '2026-06-27 08:00:00', '2026-06-27 08:00:00', NULL),
    (58, 17, 'Thiết kế tính năng SaaS cơ bản', 'Chương 2 của khóa Tư duy sản phẩm SaaS cho lập trình viên Web.', 2, 'published', '2026-06-27 08:00:00', '2026-06-27 08:00:00', NULL),
    (59, 17, 'Vận hành sản phẩm SaaS', 'Chương 3 của khóa Tư duy sản phẩm SaaS cho lập trình viên Web.', 3, 'published', '2026-06-27 08:00:00', '2026-06-27 08:00:00', NULL),
    (60, 17, 'Áp dụng SaaS vào portfolio web', 'Chương 4 của khóa Tư duy sản phẩm SaaS cho lập trình viên Web.', 4, 'published', '2026-06-27 08:00:00', '2026-06-27 08:00:00', NULL),
    (61, 18, 'Hiểu MVP và tư duy sản phẩm', 'Chương 1 của khóa Xây MVP sản phẩm Web cho người mới.', 1, 'published', '2026-06-27 08:00:00', '2026-06-27 08:00:00', NULL),
    (62, 18, 'Thiết kế MVP vừa sức', 'Chương 2 của khóa Xây MVP sản phẩm Web cho người mới.', 2, 'published', '2026-06-27 08:00:00', '2026-06-27 08:00:00', NULL),
    (63, 18, 'Xây dựng và đo lường MVP', 'Chương 3 của khóa Xây MVP sản phẩm Web cho người mới.', 3, 'published', '2026-06-27 08:00:00', '2026-06-27 08:00:00', NULL),
    (64, 18, 'Từ MVP đến portfolio và startup nhỏ', 'Chương 4 của khóa Xây MVP sản phẩm Web cho người mới.', 4, 'published', '2026-06-27 08:00:00', '2026-06-27 08:00:00', NULL),
    (65, 19, 'Nền tảng giao tiếp trong team IT', 'Chương 1 của khóa Giao tiếp trong team IT cho Web Developer.', 1, 'published', '2026-06-27 08:00:00', '2026-06-27 08:00:00', NULL),
    (66, 19, 'Giao tiếp khi làm Backend Frontend', 'Chương 2 của khóa Giao tiếp trong team IT cho Web Developer.', 2, 'published', '2026-06-27 08:00:00', '2026-06-27 08:00:00', NULL),
    (67, 19, 'Giao tiếp với giảng viên khách hàng và nhà tuyển dụng', 'Chương 3 của khóa Giao tiếp trong team IT cho Web Developer.', 3, 'published', '2026-06-27 08:00:00', '2026-06-27 08:00:00', NULL),
    (68, 19, 'Thực hành giao tiếp chuyên nghiệp', 'Chương 4 của khóa Giao tiếp trong team IT cho Web Developer.', 4, 'published', '2026-06-27 08:00:00', '2026-06-27 08:00:00', NULL),
    (69, 20, 'Hiểu teamwork trong dự án web', 'Chương 1 của khóa Teamwork Agile cho dự án Web sinh viên.', 1, 'published', '2026-06-27 08:00:00', '2026-06-27 08:00:00', NULL),
    (70, 20, 'Agile Scrum đơn giản cho sinh viên', 'Chương 2 của khóa Teamwork Agile cho dự án Web sinh viên.', 2, 'published', '2026-06-27 08:00:00', '2026-06-27 08:00:00', NULL),
    (71, 20, 'Quản lý task và tiến độ', 'Chương 3 của khóa Teamwork Agile cho dự án Web sinh viên.', 3, 'published', '2026-06-27 08:00:00', '2026-06-27 08:00:00', NULL),
    (72, 20, 'Teamwork trước ngày demo', 'Chương 4 của khóa Teamwork Agile cho dự án Web sinh viên.', 4, 'published', '2026-06-27 08:00:00', '2026-06-27 08:00:00', NULL),
    (73, 21, 'Chuẩn bị câu chuyện project', 'Chương 1 của khóa Kỹ năng trình bày project Web khi phỏng vấn.', 1, 'published', '2026-06-27 08:00:00', '2026-06-27 08:00:00', NULL),
    (74, 21, 'Trình bày kỹ thuật Backend Frontend', 'Chương 2 của khóa Kỹ năng trình bày project Web khi phỏng vấn.', 2, 'published', '2026-06-27 08:00:00', '2026-06-27 08:00:00', NULL),
    (75, 21, 'Demo sản phẩm thuyết phục', 'Chương 3 của khóa Kỹ năng trình bày project Web khi phỏng vấn.', 3, 'published', '2026-06-27 08:00:00', '2026-06-27 08:00:00', NULL),
    (76, 21, 'Luyện trả lời phỏng vấn project', 'Chương 4 của khóa Kỹ năng trình bày project Web khi phỏng vấn.', 4, 'published', '2026-06-27 08:00:00', '2026-06-27 08:00:00', NULL),
    (77, 22, 'Hiểu vấn đề trước khi code', 'Chương 1 của khóa Tư duy giải quyết vấn đề cho lập trình viên Web.', 1, 'published', '2026-06-27 08:00:00', '2026-06-27 08:00:00', NULL),
    (78, 22, 'Tư duy debug và phân tích lỗi', 'Chương 2 của khóa Tư duy giải quyết vấn đề cho lập trình viên Web.', 2, 'published', '2026-06-27 08:00:00', '2026-06-27 08:00:00', NULL),
    (79, 22, 'Tư duy thiết kế giải pháp', 'Chương 3 của khóa Tư duy giải quyết vấn đề cho lập trình viên Web.', 3, 'published', '2026-06-27 08:00:00', '2026-06-27 08:00:00', NULL),
    (80, 22, 'Áp dụng vào phỏng vấn và đồ án', 'Chương 4 của khóa Tư duy giải quyết vấn đề cho lập trình viên Web.', 4, 'published', '2026-06-27 08:00:00', '2026-06-27 08:00:00', NULL),
    (81, 23, 'Khởi động React cho dự án E-learning', 'Chương 1 của khóa React E-learning Frontend từ cơ bản đến thực chiến.', 1, 'published', '2026-06-27 08:00:00', '2026-06-27 08:00:00', NULL),
    (82, 23, 'Routing, UI và trang khóa học', 'Chương 2 của khóa React E-learning Frontend từ cơ bản đến thực chiến.', 2, 'published', '2026-06-27 08:00:00', '2026-06-27 08:00:00', NULL),
    (83, 23, 'Authentication frontend và phân quyền giao diện', 'Chương 3 của khóa React E-learning Frontend từ cơ bản đến thực chiến.', 3, 'published', '2026-06-27 08:00:00', '2026-06-27 08:00:00', NULL),
    (84, 23, 'Learning UI và video player', 'Chương 4 của khóa React E-learning Frontend từ cơ bản đến thực chiến.', 4, 'published', '2026-06-27 08:00:00', '2026-06-27 08:00:00', NULL),
    (85, 23, 'State, form và trải nghiệm người dùng', 'Chương 5 của khóa React E-learning Frontend từ cơ bản đến thực chiến.', 5, 'published', '2026-06-27 08:00:00', '2026-06-27 08:00:00', NULL),
    (86, 23, 'Hoàn thiện frontend E-learning MindHub', 'Chương 6 của khóa React E-learning Frontend từ cơ bản đến thực chiến.', 6, 'published', '2026-06-27 08:00:00', '2026-06-27 08:00:00', NULL),
    (87, 24, 'Hiểu landing page chuyển đổi', 'Chương 1 của khóa Thiết kế Landing Page chuyển đổi cao cho sản phẩm Web.', 1, 'published', '2026-06-27 08:00:00', '2026-06-27 08:00:00', NULL),
    (88, 24, 'Cấu trúc landing page hiệu quả', 'Chương 2 của khóa Thiết kế Landing Page chuyển đổi cao cho sản phẩm Web.', 2, 'published', '2026-06-27 08:00:00', '2026-06-27 08:00:00', NULL),
    (89, 24, 'UI UX và copywriting cho landing page', 'Chương 3 của khóa Thiết kế Landing Page chuyển đổi cao cho sản phẩm Web.', 3, 'published', '2026-06-27 08:00:00', '2026-06-27 08:00:00', NULL),
    (90, 24, 'Đo lường và cải thiện landing page', 'Chương 4 của khóa Thiết kế Landing Page chuyển đổi cao cho sản phẩm Web.', 4, 'published', '2026-06-27 08:00:00', '2026-06-27 08:00:00', NULL),
    (91, 25, 'Nhập môn Web Analytics', 'Chương 1 của khóa Web Analytics và A/B Testing cơ bản.', 1, 'published', '2026-06-27 08:00:00', '2026-06-27 08:00:00', NULL),
    (92, 25, 'Đo lường hành vi người dùng', 'Chương 2 của khóa Web Analytics và A/B Testing cơ bản.', 2, 'published', '2026-06-27 08:00:00', '2026-06-27 08:00:00', NULL),
    (93, 25, 'A/B Testing cơ bản', 'Chương 3 của khóa Web Analytics và A/B Testing cơ bản.', 3, 'published', '2026-06-27 08:00:00', '2026-06-27 08:00:00', NULL),
    (94, 25, 'Áp dụng vào web project', 'Chương 4 của khóa Web Analytics và A/B Testing cơ bản.', 4, 'published', '2026-06-27 08:00:00', '2026-06-27 08:00:00', NULL),
    (95, 26, 'Nền tảng content marketing', 'Chương 1 của khóa Content Marketing cho Landing Page và Blog công nghệ.', 1, 'published', '2026-06-27 08:00:00', '2026-06-27 08:00:00', NULL),
    (96, 26, 'Viết nội dung cho landing page', 'Chương 2 của khóa Content Marketing cho Landing Page và Blog công nghệ.', 2, 'published', '2026-06-27 08:00:00', '2026-06-27 08:00:00', NULL),
    (97, 26, 'Viết blog và tài liệu hỗ trợ SEO', 'Chương 3 của khóa Content Marketing cho Landing Page và Blog công nghệ.', 3, 'published', '2026-06-27 08:00:00', '2026-06-27 08:00:00', NULL),
    (98, 26, 'Content cho sản phẩm web thật', 'Chương 4 của khóa Content Marketing cho Landing Page và Blog công nghệ.', 4, 'published', '2026-06-27 08:00:00', '2026-06-27 08:00:00', NULL),
    (99, 27, 'Nhập môn SEO cho người làm web', 'Chương 1 của khóa SEO cơ bản cho Web Developer.', 1, 'published', '2026-06-27 08:00:00', '2026-06-27 08:00:00', NULL),
    (100, 27, 'SEO kỹ thuật cơ bản', 'Chương 2 của khóa SEO cơ bản cho Web Developer.', 2, 'published', '2026-06-27 08:00:00', '2026-06-27 08:00:00', NULL),
    (101, 27, 'SEO cho dự án E-learning', 'Chương 3 của khóa SEO cơ bản cho Web Developer.', 3, 'published', '2026-06-27 08:00:00', '2026-06-27 08:00:00', NULL),
    (102, 27, 'Đo lường và cải thiện SEO', 'Chương 4 của khóa SEO cơ bản cho Web Developer.', 4, 'published', '2026-06-27 08:00:00', '2026-06-27 08:00:00', NULL),
    (103, 28, 'Tổng quan thanh toán trong E-learning', 'Chương 1 của khóa Tích hợp VNPay Payment với Laravel E-learning.', 1, 'published', '2026-06-27 08:00:00', '2026-06-27 08:00:00', NULL),
    (104, 28, 'Chuẩn bị cấu hình VNPay trong Laravel', 'Chương 2 của khóa Tích hợp VNPay Payment với Laravel E-learning.', 2, 'published', '2026-06-27 08:00:00', '2026-06-27 08:00:00', NULL),
    (105, 28, 'Tạo payment URL và chuyển hướng thanh toán', 'Chương 3 của khóa Tích hợp VNPay Payment với Laravel E-learning.', 3, 'published', '2026-06-27 08:00:00', '2026-06-27 08:00:00', NULL),
    (106, 28, 'Xử lý return và IPN', 'Chương 4 của khóa Tích hợp VNPay Payment với Laravel E-learning.', 4, 'published', '2026-06-27 08:00:00', '2026-06-27 08:00:00', NULL),
    (107, 28, 'Enrollment revenue và transaction', 'Chương 5 của khóa Tích hợp VNPay Payment với Laravel E-learning.', 5, 'published', '2026-06-27 08:00:00', '2026-06-27 08:00:00', NULL),
    (108, 28, 'Hoàn thiện module payment cho demo', 'Chương 6 của khóa Tích hợp VNPay Payment với Laravel E-learning.', 6, 'published', '2026-06-27 08:00:00', '2026-06-27 08:00:00', NULL),
    (109, 29, 'Hiểu nghề Web Developer', 'Chương 1 của khóa Lộ trình xin việc Web Developer cho sinh viên IT.', 1, 'published', '2026-06-27 08:00:00', '2026-06-27 08:00:00', NULL),
    (110, 29, 'Lộ trình học Frontend, Backend và Full-stack', 'Chương 2 của khóa Lộ trình xin việc Web Developer cho sinh viên IT.', 2, 'published', '2026-06-27 08:00:00', '2026-06-27 08:00:00', NULL),
    (111, 29, 'CV IT cho sinh viên chưa có kinh nghiệm', 'Chương 3 của khóa Lộ trình xin việc Web Developer cho sinh viên IT.', 3, 'published', '2026-06-27 08:00:00', '2026-06-27 08:00:00', NULL),
    (112, 29, 'GitHub và Portfolio cá nhân', 'Chương 4 của khóa Lộ trình xin việc Web Developer cho sinh viên IT.', 4, 'published', '2026-06-27 08:00:00', '2026-06-27 08:00:00', NULL),
    (113, 29, 'Chuẩn bị phỏng vấn Fresher', 'Chương 5 của khóa Lộ trình xin việc Web Developer cho sinh viên IT.', 5, 'published', '2026-06-27 08:00:00', '2026-06-27 08:00:00', NULL),
    (114, 29, 'Lộ trình 90 ngày sẵn sàng xin việc', 'Chương 6 của khóa Lộ trình xin việc Web Developer cho sinh viên IT.', 6, 'published', '2026-06-27 08:00:00', '2026-06-27 08:00:00', NULL),
    (115, 30, 'Tổng quan phỏng vấn Backend Fresher', 'Chương 1 của khóa Phỏng vấn Backend Developer Fresher.', 1, 'published', '2026-06-27 08:00:00', '2026-06-27 08:00:00', NULL),
    (116, 30, 'HTTP và REST API nền tảng', 'Chương 2 của khóa Phỏng vấn Backend Developer Fresher.', 2, 'published', '2026-06-27 08:00:00', '2026-06-27 08:00:00', NULL),
    (117, 30, 'Authentication, Authorization và bảo mật API', 'Chương 3 của khóa Phỏng vấn Backend Developer Fresher.', 3, 'published', '2026-06-27 08:00:00', '2026-06-27 08:00:00', NULL),
    (118, 30, 'Database, transaction và xử lý dữ liệu', 'Chương 4 của khóa Phỏng vấn Backend Developer Fresher.', 4, 'published', '2026-06-27 08:00:00', '2026-06-27 08:00:00', NULL),
    (119, 30, 'Laravel Backend Architecture', 'Chương 5 của khóa Phỏng vấn Backend Developer Fresher.', 5, 'published', '2026-06-27 08:00:00', '2026-06-27 08:00:00', NULL),
    (120, 30, 'Thực chiến phỏng vấn Backend Fresher', 'Chương 6 của khóa Phỏng vấn Backend Developer Fresher.', 6, 'published', '2026-06-27 08:00:00', '2026-06-27 08:00:00', NULL);

INSERT INTO lessons (id, course_section_id, course_id, title, slug, lesson_type, content, video_url, video_duration_seconds, is_preview, status, sort_order, created_at, updated_at, deleted_at) VALUES
    (16, 13, 9, 'AI trong học tập là gì?', 'ai-trong-hoc-tap-la-gi', 'video', 'Nội dung video: AI trong học tập là gì?. File seed theo course_folder ai-learning.', '/videos/ai-learning/ai-learning-01-ai-trong-hoc-tap-la-gi.mp4', 600, 1, 'published', 1, '2026-06-27 08:00:00', '2026-06-27 08:00:00', NULL),
    (17, 13, 9, 'AI có thể hỗ trợ sinh viên IT như thế nào?', 'ai-co-the-ho-tro-sinh-vien-it-nhu-the-nao', 'video', 'Nội dung video: AI có thể hỗ trợ sinh viên IT như thế nào?. File seed theo course_folder ai-learning.', '/videos/ai-learning/ai-learning-02-ai-co-the-ho-tro-sinh-vien-it-nhu-the-nao.mp4', 600, 0, 'published', 2, '2026-06-27 08:00:00', '2026-06-27 08:00:00', NULL),
    (18, 13, 9, 'Prompt là gì và vì sao quan trọng?', 'prompt-la-gi-va-vi-sao-quan-trong', 'video', 'Nội dung video: Prompt là gì và vì sao quan trọng?. File seed theo course_folder ai-learning.', '/videos/ai-learning/ai-learning-03-prompt-la-gi-va-vi-sao-quan-trong.mp4', 600, 0, 'published', 3, '2026-06-27 08:00:00', '2026-06-27 08:00:00', NULL),
    (19, 13, 9, 'Cách đặt câu hỏi để AI trả lời đúng hơn', 'cach-dat-cau-hoi-de-ai-tra-loi-dung-hon', 'video', 'Nội dung video: Cách đặt câu hỏi để AI trả lời đúng hơn. File seed theo course_folder ai-learning.', '/videos/ai-learning/ai-learning-04-cach-dat-cau-hoi-de-ai-tra-loi-dung-hon.mp4', 600, 0, 'published', 4, '2026-06-27 08:00:00', '2026-06-27 08:00:00', NULL),
    (20, 13, 9, 'Những giới hạn của AI mà người học phải biết', 'nhung-gioi-han-cua-ai-ma-nguoi-hoc-phai-biet', 'video', 'Nội dung video: Những giới hạn của AI mà người học phải biết. File seed theo course_folder ai-learning.', '/videos/ai-learning/ai-learning-05-nhung-gioi-han-cua-ai-ma-nguoi-hoc-phai-biet.mp4', 600, 0, 'published', 5, '2026-06-27 08:00:00', '2026-06-27 08:00:00', NULL),
    (21, 14, 9, 'Dùng AI giải thích code và lỗi lập trình', 'dung-ai-giai-thich-code-va-loi-lap-trinh', 'video', 'Nội dung video: Dùng AI giải thích code và lỗi lập trình. File seed theo course_folder ai-learning.', '/videos/ai-learning/ai-learning-06-dung-ai-giai-thich-code-va-loi-lap-trinh.mp4', 600, 0, 'published', 1, '2026-06-27 08:00:00', '2026-06-27 08:00:00', NULL),
    (22, 14, 9, 'Dùng AI tạo lộ trình học cá nhân', 'dung-ai-tao-lo-trinh-hoc-ca-nhan', 'video', 'Nội dung video: Dùng AI tạo lộ trình học cá nhân. File seed theo course_folder ai-learning.', '/videos/ai-learning/ai-learning-07-dung-ai-tao-lo-trinh-hoc-ca-nhan.mp4', 600, 0, 'published', 2, '2026-06-27 08:00:00', '2026-06-27 08:00:00', NULL),
    (23, 14, 9, 'Dùng AI học HTML CSS JavaScript hiệu quả', 'dung-ai-hoc-html-css-javascript-hieu-qua', 'video', 'Nội dung video: Dùng AI học HTML CSS JavaScript hiệu quả. File seed theo course_folder ai-learning.', '/videos/ai-learning/ai-learning-08-dung-ai-hoc-html-css-javascript-hieu-qua.mp4', 600, 0, 'published', 3, '2026-06-27 08:00:00', '2026-06-27 08:00:00', NULL),
    (24, 14, 9, 'Dùng AI học Laravel Backend hiệu quả', 'dung-ai-hoc-laravel-backend-hieu-qua', 'video', 'Nội dung video: Dùng AI học Laravel Backend hiệu quả. File seed theo course_folder ai-learning.', '/videos/ai-learning/ai-learning-09-dung-ai-hoc-laravel-backend-hieu-qua.mp4', 600, 0, 'published', 4, '2026-06-27 08:00:00', '2026-06-27 08:00:00', NULL),
    (25, 14, 9, 'Dùng AI học React Frontend hiệu quả', 'dung-ai-hoc-react-frontend-hieu-qua', 'video', 'Nội dung video: Dùng AI học React Frontend hiệu quả. File seed theo course_folder ai-learning.', '/videos/ai-learning/ai-learning-10-dung-ai-hoc-react-frontend-hieu-qua.mp4', 600, 0, 'published', 5, '2026-06-27 08:00:00', '2026-06-27 08:00:00', NULL),
    (26, 15, 9, 'Ứng dụng AI để tóm tắt bài học', 'ung-dung-ai-de-tom-tat-bai-hoc', 'video', 'Nội dung video: Ứng dụng AI để tóm tắt bài học. File seed theo course_folder ai-learning.', '/videos/ai-learning/ai-learning-11-ung-dung-ai-de-tom-tat-bai-hoc.mp4', 600, 0, 'published', 1, '2026-06-27 08:00:00', '2026-06-27 08:00:00', NULL),
    (27, 15, 9, 'Ứng dụng AI để tạo quiz từ lesson', 'ung-dung-ai-de-tao-quiz-tu-lesson', 'video', 'Nội dung video: Ứng dụng AI để tạo quiz từ lesson. File seed theo course_folder ai-learning.', '/videos/ai-learning/ai-learning-12-ung-dung-ai-de-tao-quiz-tu-lesson.mp4', 600, 0, 'published', 2, '2026-06-27 08:00:00', '2026-06-27 08:00:00', NULL),
    (28, 15, 9, 'AI gợi ý khóa học cá nhân hóa', 'ai-goi-y-khoa-hoc-ca-nhan-hoa', 'video', 'Nội dung video: AI gợi ý khóa học cá nhân hóa. File seed theo course_folder ai-learning.', '/videos/ai-learning/ai-learning-13-ai-goi-y-khoa-hoc-ca-nhan-hoa.mp4', 600, 0, 'published', 3, '2026-06-27 08:00:00', '2026-06-27 08:00:00', NULL),
    (29, 15, 9, 'AI phân tích điểm yếu sau quiz', 'ai-phan-tich-diem-yeu-sau-quiz', 'video', 'Nội dung video: AI phân tích điểm yếu sau quiz. File seed theo course_folder ai-learning.', '/videos/ai-learning/ai-learning-14-ai-phan-tich-diem-yeu-sau-quiz.mp4', 600, 0, 'published', 4, '2026-06-27 08:00:00', '2026-06-27 08:00:00', NULL),
    (30, 15, 9, 'AI cảnh báo nguy cơ bỏ học', 'ai-canh-bao-nguy-co-bo-hoc', 'video', 'Nội dung video: AI cảnh báo nguy cơ bỏ học. File seed theo course_folder ai-learning.', '/videos/ai-learning/ai-learning-15-ai-canh-bao-nguy-co-bo-hoc.mp4', 600, 0, 'published', 5, '2026-06-27 08:00:00', '2026-06-27 08:00:00', NULL),
    (31, 16, 9, 'Prompt tạo nội dung video bài học', 'prompt-tao-noi-dung-video-bai-hoc', 'video', 'Nội dung video: Prompt tạo nội dung video bài học. File seed theo course_folder ai-learning.', '/videos/ai-learning/ai-learning-16-prompt-tao-noi-dung-video-bai-hoc.mp4', 600, 0, 'published', 1, '2026-06-27 08:00:00', '2026-06-27 08:00:00', NULL),
    (32, 16, 9, 'Prompt viết README và tài liệu đồ án', 'prompt-viet-readme-va-tai-lieu-do-an', 'video', 'Nội dung video: Prompt viết README và tài liệu đồ án. File seed theo course_folder ai-learning.', '/videos/ai-learning/ai-learning-17-prompt-viet-readme-va-tai-lieu-do-an.mp4', 600, 0, 'published', 2, '2026-06-27 08:00:00', '2026-06-27 08:00:00', NULL),
    (33, 16, 9, 'Prompt review code Laravel và React', 'prompt-review-code-laravel-va-react', 'video', 'Nội dung video: Prompt review code Laravel và React. File seed theo course_folder ai-learning.', '/videos/ai-learning/ai-learning-18-prompt-review-code-laravel-va-react.mp4', 600, 0, 'published', 3, '2026-06-27 08:00:00', '2026-06-27 08:00:00', NULL),
    (34, 16, 9, 'Prompt tạo test case cho API', 'prompt-tao-test-case-cho-api', 'video', 'Nội dung video: Prompt tạo test case cho API. File seed theo course_folder ai-learning.', '/videos/ai-learning/ai-learning-19-prompt-tao-test-case-cho-api.mp4', 600, 0, 'published', 4, '2026-06-27 08:00:00', '2026-06-27 08:00:00', NULL),
    (35, 16, 9, 'Prompt chuẩn bị câu trả lời phỏng vấn IT', 'prompt-chuan-bi-cau-tra-loi-phong-van-it', 'video', 'Nội dung video: Prompt chuẩn bị câu trả lời phỏng vấn IT. File seed theo course_folder ai-learning.', '/videos/ai-learning/ai-learning-20-prompt-chuan-bi-cau-tra-loi-phong-van-it.mp4', 600, 0, 'published', 5, '2026-06-27 08:00:00', '2026-06-27 08:00:00', NULL),
    (36, 17, 9, 'Vì sao không nên copy nguyên câu trả lời của AI?', 'vi-sao-khong-nen-copy-nguyen-cau-tra-loi-cua-ai', 'video', 'Nội dung video: Vì sao không nên copy nguyên câu trả lời của AI?. File seed theo course_folder ai-learning.', '/videos/ai-learning/ai-learning-21-vi-sao-khong-nen-copy-nguyen-cau-tra-loi-cua-ai.mp4', 600, 0, 'published', 1, '2026-06-27 08:00:00', '2026-06-27 08:00:00', NULL),
    (37, 17, 9, 'Cách kiểm chứng thông tin AI tạo ra', 'cach-kiem-chung-thong-tin-ai-tao-ra', 'video', 'Nội dung video: Cách kiểm chứng thông tin AI tạo ra. File seed theo course_folder ai-learning.', '/videos/ai-learning/ai-learning-22-cach-kiem-chung-thong-tin-ai-tao-ra.mp4', 600, 0, 'published', 2, '2026-06-27 08:00:00', '2026-06-27 08:00:00', NULL),
    (38, 17, 9, 'Bảo mật dữ liệu khi dùng AI trong đồ án', 'bao-mat-du-lieu-khi-dung-ai-trong-do-an', 'video', 'Nội dung video: Bảo mật dữ liệu khi dùng AI trong đồ án. File seed theo course_folder ai-learning.', '/videos/ai-learning/ai-learning-23-bao-mat-du-lieu-khi-dung-ai-trong-do-an.mp4', 600, 0, 'published', 3, '2026-06-27 08:00:00', '2026-06-27 08:00:00', NULL),
    (39, 17, 9, 'Tránh phụ thuộc AI khi học lập trình', 'tranh-phu-thuoc-ai-khi-hoc-lap-trinh', 'video', 'Nội dung video: Tránh phụ thuộc AI khi học lập trình. File seed theo course_folder ai-learning.', '/videos/ai-learning/ai-learning-24-tranh-phu-thuoc-ai-khi-hoc-lap-trinh.mp4', 600, 0, 'published', 4, '2026-06-27 08:00:00', '2026-06-27 08:00:00', NULL),
    (40, 17, 9, 'Cách dùng AI trung thực trong CV và phỏng vấn', 'cach-dung-ai-trung-thuc-trong-cv-va-phong-van', 'video', 'Nội dung video: Cách dùng AI trung thực trong CV và phỏng vấn. File seed theo course_folder ai-learning.', '/videos/ai-learning/ai-learning-25-cach-dung-ai-trung-thuc-trong-cv-va-phong-van.mp4', 600, 0, 'published', 5, '2026-06-27 08:00:00', '2026-06-27 08:00:00', NULL),
    (41, 18, 9, 'Quy trình học một chủ đề mới bằng AI', 'quy-trinh-hoc-mot-chu-de-moi-bang-ai', 'video', 'Nội dung video: Quy trình học một chủ đề mới bằng AI. File seed theo course_folder ai-learning.', '/videos/ai-learning/ai-learning-26-quy-trinh-hoc-mot-chu-de-moi-bang-ai.mp4', 600, 0, 'published', 1, '2026-06-27 08:00:00', '2026-06-27 08:00:00', NULL),
    (42, 18, 9, 'Tạo ghi chú học tập và checklist bằng AI', 'tao-ghi-chu-hoc-tap-va-checklist-bang-ai', 'video', 'Nội dung video: Tạo ghi chú học tập và checklist bằng AI. File seed theo course_folder ai-learning.', '/videos/ai-learning/ai-learning-27-tao-ghi-chu-hoc-tap-va-checklist-bang-ai.mp4', 600, 0, 'published', 2, '2026-06-27 08:00:00', '2026-06-27 08:00:00', NULL),
    (43, 18, 9, 'Dùng AI luyện phỏng vấn hằng ngày', 'dung-ai-luyen-phong-van-hang-ngay', 'video', 'Nội dung video: Dùng AI luyện phỏng vấn hằng ngày. File seed theo course_folder ai-learning.', '/videos/ai-learning/ai-learning-28-dung-ai-luyen-phong-van-hang-ngay.mp4', 600, 0, 'published', 3, '2026-06-27 08:00:00', '2026-06-27 08:00:00', NULL),
    (44, 18, 9, 'Xây trợ lý học tập cá nhân cho sinh viên IT', 'xay-tro-ly-hoc-tap-ca-nhan-cho-sinh-vien-it', 'video', 'Nội dung video: Xây trợ lý học tập cá nhân cho sinh viên IT. File seed theo course_folder ai-learning.', '/videos/ai-learning/ai-learning-29-xay-tro-ly-hoc-tap-ca-nhan-cho-sinh-vien-it.mp4', 600, 0, 'published', 4, '2026-06-27 08:00:00', '2026-06-27 08:00:00', NULL),
    (45, 18, 9, 'Tổng kết khóa học AI Learning', 'tong-ket-khoa-hoc-ai-learning', 'video', 'Nội dung video: Tổng kết khóa học AI Learning. File seed theo course_folder ai-learning.', '/videos/ai-learning/ai-learning-30-tong-ket-khoa-hoc-ai-learning.mp4', 600, 0, 'published', 5, '2026-06-27 08:00:00', '2026-06-27 08:00:00', NULL),
    (46, 19, 10, 'Postman là gì và vì sao Backend Fresher nên biết?', 'postman-la-gi-va-vi-sao-backend-fresher-nen-biet', 'video', 'Nội dung video: Postman là gì và vì sao Backend Fresher nên biết?. File seed theo course_folder postman-api-testing.', '/videos/postman-api-testing/postman-api-testing-01-postman-la-gi-va-vi-sao-backend-fresher-nen-biet.mp4', 600, 1, 'published', 1, '2026-06-27 08:00:00', '2026-06-27 08:00:00', NULL),
    (47, 19, 10, 'Cài đặt Postman và tạo workspace đầu tiên', 'cai-dat-postman-va-tao-workspace-dau-tien', 'video', 'Nội dung video: Cài đặt Postman và tạo workspace đầu tiên. File seed theo course_folder postman-api-testing.', '/videos/postman-api-testing/postman-api-testing-02-cai-dat-postman-va-tao-workspace-dau-tien.mp4', 600, 0, 'published', 2, '2026-06-27 08:00:00', '2026-06-27 08:00:00', NULL),
    (48, 19, 10, 'Hiểu method, URL, header, body và response trong Postman', 'hieu-method-url-header-body-va-response-trong-postman', 'video', 'Nội dung video: Hiểu method, URL, header, body và response trong Postman. File seed theo course_folder postman-api-testing.', '/videos/postman-api-testing/postman-api-testing-03-hieu-method-url-header-body-va-response-trong-postman.mp4', 600, 0, 'published', 3, '2026-06-27 08:00:00', '2026-06-27 08:00:00', NULL),
    (49, 19, 10, 'Tạo request GET POST PUT PATCH DELETE đầu tiên', 'tao-request-get-post-put-patch-delete-dau-tien', 'video', 'Nội dung video: Tạo request GET POST PUT PATCH DELETE đầu tiên. File seed theo course_folder postman-api-testing.', '/videos/postman-api-testing/postman-api-testing-04-tao-request-get-post-put-patch-delete-dau-tien.mp4', 600, 0, 'published', 4, '2026-06-27 08:00:00', '2026-06-27 08:00:00', NULL),
    (50, 19, 10, 'Đọc status code và JSON response khi test API', 'doc-status-code-va-json-response-khi-test-api', 'video', 'Nội dung video: Đọc status code và JSON response khi test API. File seed theo course_folder postman-api-testing.', '/videos/postman-api-testing/postman-api-testing-05-doc-status-code-va-json-response-khi-test-api.mp4', 600, 0, 'published', 5, '2026-06-27 08:00:00', '2026-06-27 08:00:00', NULL),
    (51, 20, 10, 'Test API register bằng Postman', 'test-api-register-bang-postman', 'video', 'Nội dung video: Test API register bằng Postman. File seed theo course_folder postman-api-testing.', '/videos/postman-api-testing/postman-api-testing-06-test-api-register-bang-postman.mp4', 600, 0, 'published', 1, '2026-06-27 08:00:00', '2026-06-27 08:00:00', NULL),
    (52, 20, 10, 'Test API login và lưu access token', 'test-api-login-va-luu-access-token', 'video', 'Nội dung video: Test API login và lưu access token. File seed theo course_folder postman-api-testing.', '/videos/postman-api-testing/postman-api-testing-07-test-api-login-va-luu-access-token.mp4', 600, 0, 'published', 2, '2026-06-27 08:00:00', '2026-06-27 08:00:00', NULL),
    (53, 20, 10, 'Gửi Authorization header cho API protected', 'gui-authorization-header-cho-api-protected', 'video', 'Nội dung video: Gửi Authorization header cho API protected. File seed theo course_folder postman-api-testing.', '/videos/postman-api-testing/postman-api-testing-08-gui-authorization-header-cho-api-protected.mp4', 600, 0, 'published', 3, '2026-06-27 08:00:00', '2026-06-27 08:00:00', NULL),
    (54, 20, 10, 'Test logout và refresh token', 'test-logout-va-refresh-token', 'video', 'Nội dung video: Test logout và refresh token. File seed theo course_folder postman-api-testing.', '/videos/postman-api-testing/postman-api-testing-09-test-logout-va-refresh-token.mp4', 600, 0, 'published', 4, '2026-06-27 08:00:00', '2026-06-27 08:00:00', NULL),
    (55, 20, 10, 'Xử lý lỗi 401 403 khi test API', 'xu-ly-loi-401-403-khi-test-api', 'video', 'Nội dung video: Xử lý lỗi 401 403 khi test API. File seed theo course_folder postman-api-testing.', '/videos/postman-api-testing/postman-api-testing-10-xu-ly-loi-401-403-khi-test-api.mp4', 600, 0, 'published', 5, '2026-06-27 08:00:00', '2026-06-27 08:00:00', NULL),
    (56, 21, 10, 'Tạo Postman Collection cho dự án Laravel', 'tao-postman-collection-cho-du-an-laravel', 'video', 'Nội dung video: Tạo Postman Collection cho dự án Laravel. File seed theo course_folder postman-api-testing.', '/videos/postman-api-testing/postman-api-testing-11-tao-postman-collection-cho-du-an-laravel.mp4', 600, 0, 'published', 1, '2026-06-27 08:00:00', '2026-06-27 08:00:00', NULL),
    (57, 21, 10, 'Dùng environment cho base_url và token', 'dung-environment-cho-base-url-va-token', 'video', 'Nội dung video: Dùng environment cho base_url và token. File seed theo course_folder postman-api-testing.', '/videos/postman-api-testing/postman-api-testing-12-dung-environment-cho-base-url-va-token.mp4', 600, 0, 'published', 2, '2026-06-27 08:00:00', '2026-06-27 08:00:00', NULL),
    (58, 21, 10, 'Dùng variable để lưu user_id course_id order_id', 'dung-variable-de-luu-user-id-course-id-order-id', 'video', 'Nội dung video: Dùng variable để lưu user_id course_id order_id. File seed theo course_folder postman-api-testing.', '/videos/postman-api-testing/postman-api-testing-13-dung-variable-de-luu-user-id-course-id-order-id.mp4', 600, 0, 'published', 3, '2026-06-27 08:00:00', '2026-06-27 08:00:00', NULL),
    (59, 21, 10, 'Viết pre-request script cơ bản', 'viet-pre-request-script-co-ban', 'video', 'Nội dung video: Viết pre-request script cơ bản. File seed theo course_folder postman-api-testing.', '/videos/postman-api-testing/postman-api-testing-14-viet-pre-request-script-co-ban.mp4', 600, 0, 'published', 4, '2026-06-27 08:00:00', '2026-06-27 08:00:00', NULL),
    (60, 21, 10, 'Viết test script kiểm tra status và response', 'viet-test-script-kiem-tra-status-va-response', 'video', 'Nội dung video: Viết test script kiểm tra status và response. File seed theo course_folder postman-api-testing.', '/videos/postman-api-testing/postman-api-testing-15-viet-test-script-kiem-tra-status-va-response.mp4', 600, 0, 'published', 5, '2026-06-27 08:00:00', '2026-06-27 08:00:00', NULL),
    (61, 22, 10, 'Test flow xem danh sách khóa học', 'test-flow-xem-danh-sach-khoa-hoc', 'video', 'Nội dung video: Test flow xem danh sách khóa học. File seed theo course_folder postman-api-testing.', '/videos/postman-api-testing/postman-api-testing-16-test-flow-xem-danh-sach-khoa-hoc.mp4', 600, 0, 'published', 1, '2026-06-27 08:00:00', '2026-06-27 08:00:00', NULL),
    (62, 22, 10, 'Test flow chi tiết khóa học và lesson overview', 'test-flow-chi-tiet-khoa-hoc-va-lesson-overview', 'video', 'Nội dung video: Test flow chi tiết khóa học và lesson overview. File seed theo course_folder postman-api-testing.', '/videos/postman-api-testing/postman-api-testing-17-test-flow-chi-tiet-khoa-hoc-va-lesson-overview.mp4', 600, 0, 'published', 2, '2026-06-27 08:00:00', '2026-06-27 08:00:00', NULL),
    (63, 22, 10, 'Test flow tạo order và áp coupon', 'test-flow-tao-order-va-ap-coupon', 'video', 'Nội dung video: Test flow tạo order và áp coupon. File seed theo course_folder postman-api-testing.', '/videos/postman-api-testing/postman-api-testing-18-test-flow-tao-order-va-ap-coupon.mp4', 600, 0, 'published', 3, '2026-06-27 08:00:00', '2026-06-27 08:00:00', NULL),
    (64, 22, 10, 'Test flow thanh toán và tạo enrollment', 'test-flow-thanh-toan-va-tao-enrollment', 'video', 'Nội dung video: Test flow thanh toán và tạo enrollment. File seed theo course_folder postman-api-testing.', '/videos/postman-api-testing/postman-api-testing-19-test-flow-thanh-toan-va-tao-enrollment.mp4', 600, 0, 'published', 4, '2026-06-27 08:00:00', '2026-06-27 08:00:00', NULL),
    (65, 22, 10, 'Test flow học bài và lưu tiến độ', 'test-flow-hoc-bai-va-luu-tien-do', 'video', 'Nội dung video: Test flow học bài và lưu tiến độ. File seed theo course_folder postman-api-testing.', '/videos/postman-api-testing/postman-api-testing-20-test-flow-hoc-bai-va-luu-tien-do.mp4', 600, 0, 'published', 5, '2026-06-27 08:00:00', '2026-06-27 08:00:00', NULL),
    (66, 23, 10, 'Test validation lỗi 422 trong API', 'test-validation-loi-422-trong-api', 'video', 'Nội dung video: Test validation lỗi 422 trong API. File seed theo course_folder postman-api-testing.', '/videos/postman-api-testing/postman-api-testing-21-test-validation-loi-422-trong-api.mp4', 600, 0, 'published', 1, '2026-06-27 08:00:00', '2026-06-27 08:00:00', NULL),
    (67, 23, 10, 'Test not found 404 và conflict 409', 'test-not-found-404-va-conflict-409', 'video', 'Nội dung video: Test not found 404 và conflict 409. File seed theo course_folder postman-api-testing.', '/videos/postman-api-testing/postman-api-testing-22-test-not-found-404-va-conflict-409.mp4', 600, 0, 'published', 2, '2026-06-27 08:00:00', '2026-06-27 08:00:00', NULL),
    (68, 23, 10, 'Test permission và ownership trong API', 'test-permission-va-ownership-trong-api', 'video', 'Nội dung video: Test permission và ownership trong API. File seed theo course_folder postman-api-testing.', '/videos/postman-api-testing/postman-api-testing-23-test-permission-va-ownership-trong-api.mp4', 600, 0, 'published', 3, '2026-06-27 08:00:00', '2026-06-27 08:00:00', NULL),
    (69, 23, 10, 'Debug lỗi 500 bằng response và Laravel log', 'debug-loi-500-bang-response-va-laravel-log', 'video', 'Nội dung video: Debug lỗi 500 bằng response và Laravel log. File seed theo course_folder postman-api-testing.', '/videos/postman-api-testing/postman-api-testing-24-debug-loi-500-bang-response-va-laravel-log.mp4', 600, 0, 'published', 4, '2026-06-27 08:00:00', '2026-06-27 08:00:00', NULL),
    (70, 23, 10, 'Ghi báo cáo test API rõ ràng', 'ghi-bao-cao-test-api-ro-rang', 'video', 'Nội dung video: Ghi báo cáo test API rõ ràng. File seed theo course_folder postman-api-testing.', '/videos/postman-api-testing/postman-api-testing-25-ghi-bao-cao-test-api-ro-rang.mp4', 600, 0, 'published', 5, '2026-06-27 08:00:00', '2026-06-27 08:00:00', NULL),
    (71, 24, 10, 'Sắp xếp collection theo module Auth Catalog Payment Learning', 'sap-xep-collection-theo-module-auth-catalog-payment-learning', 'video', 'Nội dung video: Sắp xếp collection theo module Auth Catalog Payment Learning. File seed theo course_folder postman-api-testing.', '/videos/postman-api-testing/postman-api-testing-26-sap-xep-collection-theo-module-auth-catalog-payment-learning.mp4', 600, 0, 'published', 1, '2026-06-27 08:00:00', '2026-06-27 08:00:00', NULL),
    (72, 24, 10, 'Chuẩn bị dữ liệu seed để test ổn định', 'chuan-bi-du-lieu-seed-de-test-on-dinh', 'video', 'Nội dung video: Chuẩn bị dữ liệu seed để test ổn định. File seed theo course_folder postman-api-testing.', '/videos/postman-api-testing/postman-api-testing-27-chuan-bi-du-lieu-seed-de-test-on-dinh.mp4', 600, 0, 'published', 2, '2026-06-27 08:00:00', '2026-06-27 08:00:00', NULL),
    (73, 24, 10, 'Export collection và environment cho team', 'export-collection-va-environment-cho-team', 'video', 'Nội dung video: Export collection và environment cho team. File seed theo course_folder postman-api-testing.', '/videos/postman-api-testing/postman-api-testing-28-export-collection-va-environment-cho-team.mp4', 600, 0, 'published', 3, '2026-06-27 08:00:00', '2026-06-27 08:00:00', NULL),
    (74, 24, 10, 'Checklist test API trước ngày demo', 'checklist-test-api-truoc-ngay-demo', 'video', 'Nội dung video: Checklist test API trước ngày demo. File seed theo course_folder postman-api-testing.', '/videos/postman-api-testing/postman-api-testing-29-checklist-test-api-truoc-ngay-demo.mp4', 600, 0, 'published', 4, '2026-06-27 08:00:00', '2026-06-27 08:00:00', NULL),
    (75, 24, 10, 'Tổng kết khóa học Postman API Testing', 'tong-ket-khoa-hoc-postman-api-testing', 'video', 'Nội dung video: Tổng kết khóa học Postman API Testing. File seed theo course_folder postman-api-testing.', '/videos/postman-api-testing/postman-api-testing-30-tong-ket-khoa-hoc-postman-api-testing.mp4', 600, 0, 'published', 5, '2026-06-27 08:00:00', '2026-06-27 08:00:00', NULL),
    (76, 25, 11, 'Vì sao làm đồ án nhóm phải dùng Git?', 'vi-sao-lam-do-an-nhom-phai-dung-git', 'video', 'Nội dung video: Vì sao làm đồ án nhóm phải dùng Git?. File seed theo course_folder git-github.', '/videos/git-github/git-github-01-vi-sao-lam-do-an-nhom-phai-dung-git.mp4', 600, 1, 'published', 1, '2026-06-27 08:00:00', '2026-06-27 08:00:00', NULL),
    (77, 25, 11, 'Git, GitHub và repository khác nhau thế nào?', 'git-github-va-repository-khac-nhau-the-nao', 'video', 'Nội dung video: Git, GitHub và repository khác nhau thế nào?. File seed theo course_folder git-github.', '/videos/git-github/git-github-02-git-github-va-repository-khac-nhau-the-nao.mp4', 600, 0, 'published', 2, '2026-06-27 08:00:00', '2026-06-27 08:00:00', NULL),
    (78, 25, 11, 'Cài Git và cấu hình tài khoản GitHub', 'cai-git-va-cau-hinh-tai-khoan-github', 'video', 'Nội dung video: Cài Git và cấu hình tài khoản GitHub. File seed theo course_folder git-github.', '/videos/git-github/git-github-03-cai-git-va-cau-hinh-tai-khoan-github.mp4', 600, 0, 'published', 3, '2026-06-27 08:00:00', '2026-06-27 08:00:00', NULL),
    (79, 25, 11, 'Tạo repository đầu tiên cho đồ án nhóm', 'tao-repository-dau-tien-cho-do-an-nhom', 'video', 'Nội dung video: Tạo repository đầu tiên cho đồ án nhóm. File seed theo course_folder git-github.', '/videos/git-github/git-github-04-tao-repository-dau-tien-cho-do-an-nhom.mp4', 600, 0, 'published', 4, '2026-06-27 08:00:00', '2026-06-27 08:00:00', NULL),
    (80, 25, 11, 'Clone project và hiểu cấu trúc repository', 'clone-project-va-hieu-cau-truc-repository', 'video', 'Nội dung video: Clone project và hiểu cấu trúc repository. File seed theo course_folder git-github.', '/videos/git-github/git-github-05-clone-project-va-hieu-cau-truc-repository.mp4', 600, 0, 'published', 5, '2026-06-27 08:00:00', '2026-06-27 08:00:00', NULL),
    (81, 26, 11, 'Git status, add và commit hoạt động ra sao?', 'git-status-add-va-commit-hoat-dong-ra-sao', 'video', 'Nội dung video: Git status, add và commit hoạt động ra sao?. File seed theo course_folder git-github.', '/videos/git-github/git-github-06-git-status-add-va-commit-hoat-dong-ra-sao.mp4', 600, 0, 'published', 1, '2026-06-27 08:00:00', '2026-06-27 08:00:00', NULL),
    (82, 26, 11, 'Viết commit message dễ hiểu', 'viet-commit-message-de-hieu', 'video', 'Nội dung video: Viết commit message dễ hiểu. File seed theo course_folder git-github.', '/videos/git-github/git-github-07-viet-commit-message-de-hieu.mp4', 600, 0, 'published', 2, '2026-06-27 08:00:00', '2026-06-27 08:00:00', NULL),
    (83, 26, 11, 'Push code lên GitHub đúng cách', 'push-code-len-github-dung-cach', 'video', 'Nội dung video: Push code lên GitHub đúng cách. File seed theo course_folder git-github.', '/videos/git-github/git-github-08-push-code-len-github-dung-cach.mp4', 600, 0, 'published', 3, '2026-06-27 08:00:00', '2026-06-27 08:00:00', NULL),
    (84, 26, 11, 'Pull code mới về máy trước khi làm việc', 'pull-code-moi-ve-may-truoc-khi-lam-viec', 'video', 'Nội dung video: Pull code mới về máy trước khi làm việc. File seed theo course_folder git-github.', '/videos/git-github/git-github-09-pull-code-moi-ve-may-truoc-khi-lam-viec.mp4', 600, 0, 'published', 4, '2026-06-27 08:00:00', '2026-06-27 08:00:00', NULL),
    (85, 26, 11, 'Branch là gì và cách chia nhánh', 'branch-la-gi-va-cach-chia-nhanh', 'video', 'Nội dung video: Branch là gì và cách chia nhánh. File seed theo course_folder git-github.', '/videos/git-github/git-github-10-branch-la-gi-va-cach-chia-nhanh.mp4', 600, 0, 'published', 5, '2026-06-27 08:00:00', '2026-06-27 08:00:00', NULL),
    (86, 27, 11, 'Pull request và review code cơ bản', 'pull-request-va-review-code-co-ban', 'video', 'Nội dung video: Pull request và review code cơ bản. File seed theo course_folder git-github.', '/videos/git-github/git-github-11-pull-request-va-review-code-co-ban.mp4', 600, 0, 'published', 1, '2026-06-27 08:00:00', '2026-06-27 08:00:00', NULL),
    (87, 27, 11, 'Workflow Git cho team Backend và Frontend', 'workflow-git-cho-team-backend-va-frontend', 'video', 'Nội dung video: Workflow Git cho team Backend và Frontend. File seed theo course_folder git-github.', '/videos/git-github/git-github-12-workflow-git-cho-team-backend-va-frontend.mp4', 600, 0, 'published', 2, '2026-06-27 08:00:00', '2026-06-27 08:00:00', NULL),
    (88, 27, 11, 'Dùng GitHub Issues để chia task', 'dung-github-issues-de-chia-task', 'video', 'Nội dung video: Dùng GitHub Issues để chia task. File seed theo course_folder git-github.', '/videos/git-github/git-github-13-dung-github-issues-de-chia-task.mp4', 600, 0, 'published', 3, '2026-06-27 08:00:00', '2026-06-27 08:00:00', NULL),
    (89, 27, 11, 'Gắn branch, commit và pull request với task đồ án', 'gan-branch-commit-va-pull-request-voi-task-do-an', 'video', 'Nội dung video: Gắn branch, commit và pull request với task đồ án. File seed theo course_folder git-github.', '/videos/git-github/git-github-14-gan-branch-commit-va-pull-request-voi-task-do-an.mp4', 600, 0, 'published', 4, '2026-06-27 08:00:00', '2026-06-27 08:00:00', NULL),
    (90, 27, 11, 'Quy tắc làm việc nhóm để tránh đè code', 'quy-tac-lam-viec-nhom-de-tranh-de-code', 'video', 'Nội dung video: Quy tắc làm việc nhóm để tránh đè code. File seed theo course_folder git-github.', '/videos/git-github/git-github-15-quy-tac-lam-viec-nhom-de-tranh-de-code.mp4', 600, 0, 'published', 5, '2026-06-27 08:00:00', '2026-06-27 08:00:00', NULL),
    (91, 28, 11, 'Xử lý conflict khi nhiều người sửa cùng file', 'xu-ly-conflict-khi-nhieu-nguoi-sua-cung-file', 'video', 'Nội dung video: Xử lý conflict khi nhiều người sửa cùng file. File seed theo course_folder git-github.', '/videos/git-github/git-github-16-xu-ly-conflict-khi-nhieu-nguoi-sua-cung-file.mp4', 600, 0, 'published', 1, '2026-06-27 08:00:00', '2026-06-27 08:00:00', NULL),
    (92, 28, 11, 'Push bị từ chối thì xử lý thế nào?', 'push-bi-tu-choi-thi-xu-ly-the-nao', 'video', 'Nội dung video: Push bị từ chối thì xử lý thế nào?. File seed theo course_folder git-github.', '/videos/git-github/git-github-17-push-bi-tu-choi-thi-xu-ly-the-nao.mp4', 600, 0, 'published', 2, '2026-06-27 08:00:00', '2026-06-27 08:00:00', NULL),
    (93, 28, 11, 'Commit nhầm file và cách phòng tránh', 'commit-nham-file-va-cach-phong-tranh', 'video', 'Nội dung video: Commit nhầm file và cách phòng tránh. File seed theo course_folder git-github.', '/videos/git-github/git-github-18-commit-nham-file-va-cach-phong-tranh.mp4', 600, 0, 'published', 3, '2026-06-27 08:00:00', '2026-06-27 08:00:00', NULL),
    (94, 28, 11, 'Lỡ code trực tiếp trên main thì xử lý ra sao?', 'lo-code-truc-tiep-tren-main-thi-xu-ly-ra-sao', 'video', 'Nội dung video: Lỡ code trực tiếp trên main thì xử lý ra sao?. File seed theo course_folder git-github.', '/videos/git-github/git-github-19-lo-code-truc-tiep-tren-main-thi-xu-ly-ra-sao.mp4', 600, 0, 'published', 4, '2026-06-27 08:00:00', '2026-06-27 08:00:00', NULL),
    (95, 28, 11, 'Những lỗi Git thường gặp và cách xử lý', 'nhung-loi-git-thuong-gap-va-cach-xu-ly', 'video', 'Nội dung video: Những lỗi Git thường gặp và cách xử lý. File seed theo course_folder git-github.', '/videos/git-github/git-github-20-nhung-loi-git-thuong-gap-va-cach-xu-ly.mp4', 600, 0, 'published', 5, '2026-06-27 08:00:00', '2026-06-27 08:00:00', NULL),
    (96, 29, 11, 'Viết README setup project cho team', 'viet-readme-setup-project-cho-team', 'video', 'Nội dung video: Viết README setup project cho team. File seed theo course_folder git-github.', '/videos/git-github/git-github-21-viet-readme-setup-project-cho-team.mp4', 600, 0, 'published', 1, '2026-06-27 08:00:00', '2026-06-27 08:00:00', NULL),
    (97, 29, 11, 'Dùng .gitignore và .env.example trong đồ án', 'dung-gitignore-va-env-example-trong-do-an', 'video', 'Nội dung video: Dùng .gitignore và .env.example trong đồ án. File seed theo course_folder git-github.', '/videos/git-github/git-github-22-dung-gitignore-va-env-example-trong-do-an.mp4', 600, 0, 'published', 2, '2026-06-27 08:00:00', '2026-06-27 08:00:00', NULL),
    (98, 29, 11, 'Không commit file nhạy cảm lên GitHub', 'khong-commit-file-nhay-cam-len-github', 'video', 'Nội dung video: Không commit file nhạy cảm lên GitHub. File seed theo course_folder git-github.', '/videos/git-github/git-github-23-khong-commit-file-nhay-cam-len-github.mp4', 600, 0, 'published', 3, '2026-06-27 08:00:00', '2026-06-27 08:00:00', NULL),
    (99, 29, 11, 'Cách trình bày repository đồ án cho giảng viên và nhà tuyển dụng', 'cach-trinh-bay-repository-do-an-cho-giang-vien-va-nha-tuyen-dung', 'video', 'Nội dung video: Cách trình bày repository đồ án cho giảng viên và nhà tuyển dụng. File seed theo course_folder git-github.', '/videos/git-github/git-github-24-cach-trinh-bay-repository-do-an-cho-giang-vien-va-nha-tuyen-dung.mp4', 600, 0, 'published', 4, '2026-06-27 08:00:00', '2026-06-27 08:00:00', NULL),
    (100, 29, 11, 'GitHub profile cho sinh viên IT', 'github-profile-cho-sinh-vien-it', 'video', 'Nội dung video: GitHub profile cho sinh viên IT. File seed theo course_folder git-github.', '/videos/git-github/git-github-25-github-profile-cho-sinh-vien-it.mp4', 600, 0, 'published', 5, '2026-06-27 08:00:00', '2026-06-27 08:00:00', NULL),
    (101, 30, 11, 'Quy trình Git cho dự án Laravel Backend', 'quy-trinh-git-cho-du-an-laravel-backend', 'video', 'Nội dung video: Quy trình Git cho dự án Laravel Backend. File seed theo course_folder git-github.', '/videos/git-github/git-github-26-quy-trinh-git-cho-du-an-laravel-backend.mp4', 600, 0, 'published', 1, '2026-06-27 08:00:00', '2026-06-27 08:00:00', NULL),
    (102, 30, 11, 'Quy trình Git cho dự án React Frontend', 'quy-trinh-git-cho-du-an-react-frontend', 'video', 'Nội dung video: Quy trình Git cho dự án React Frontend. File seed theo course_folder git-github.', '/videos/git-github/git-github-27-quy-trinh-git-cho-du-an-react-frontend.mp4', 600, 0, 'published', 2, '2026-06-27 08:00:00', '2026-06-27 08:00:00', NULL),
    (103, 30, 11, 'Quy trình merge code trước ngày demo', 'quy-trinh-merge-code-truoc-ngay-demo', 'video', 'Nội dung video: Quy trình merge code trước ngày demo. File seed theo course_folder git-github.', '/videos/git-github/git-github-28-quy-trinh-merge-code-truoc-ngay-demo.mp4', 600, 0, 'published', 3, '2026-06-27 08:00:00', '2026-06-27 08:00:00', NULL),
    (104, 30, 11, 'Checklist GitHub trước khi nộp đồ án', 'checklist-github-truoc-khi-nop-do-an', 'video', 'Nội dung video: Checklist GitHub trước khi nộp đồ án. File seed theo course_folder git-github.', '/videos/git-github/git-github-29-checklist-github-truoc-khi-nop-do-an.mp4', 600, 0, 'published', 4, '2026-06-27 08:00:00', '2026-06-27 08:00:00', NULL),
    (105, 30, 11, 'Tổng kết khóa học Git & GitHub làm việc nhóm đồ án', 'tong-ket-khoa-hoc-git-va-github-lam-viec-nhom-do-an', 'video', 'Nội dung video: Tổng kết khóa học Git & GitHub làm việc nhóm đồ án. File seed theo course_folder git-github.', '/videos/git-github/git-github-30-tong-ket-khoa-hoc-git-va-github-lam-viec-nhom-do-an.mp4', 600, 0, 'published', 5, '2026-06-27 08:00:00', '2026-06-27 08:00:00', NULL),
    (106, 31, 12, 'Database trong hệ thống E-learning dùng để làm gì?', 'database-trong-he-thong-e-learning-dung-de-lam-gi', 'video', 'Nội dung video: Database trong hệ thống E-learning dùng để làm gì?. File seed theo course_folder mysql-database-design.', '/videos/mysql-database-design/mysql-database-design-01-database-trong-he-thong-e-learning-dung-de-lam-gi.mp4', 600, 1, 'published', 1, '2026-06-27 08:00:00', '2026-06-27 08:00:00', NULL),
    (107, 31, 12, 'Table column record và data type trong MySQL', 'table-column-record-va-data-type-trong-mysql', 'video', 'Nội dung video: Table column record và data type trong MySQL. File seed theo course_folder mysql-database-design.', '/videos/mysql-database-design/mysql-database-design-02-table-column-record-va-data-type-trong-mysql.mp4', 600, 0, 'published', 2, '2026-06-27 08:00:00', '2026-06-27 08:00:00', NULL),
    (108, 31, 12, 'Primary key foreign key và unique constraint', 'primary-key-foreign-key-va-unique-constraint', 'video', 'Nội dung video: Primary key foreign key và unique constraint. File seed theo course_folder mysql-database-design.', '/videos/mysql-database-design/mysql-database-design-03-primary-key-foreign-key-va-unique-constraint.mp4', 600, 0, 'published', 3, '2026-06-27 08:00:00', '2026-06-27 08:00:00', NULL),
    (109, 31, 12, 'One-to-many many-to-many và one-to-one', 'one-to-many-many-to-many-va-one-to-one', 'video', 'Nội dung video: One-to-many many-to-many và one-to-one. File seed theo course_folder mysql-database-design.', '/videos/mysql-database-design/mysql-database-design-04-one-to-many-many-to-many-va-one-to-one.mp4', 600, 0, 'published', 4, '2026-06-27 08:00:00', '2026-06-27 08:00:00', NULL),
    (110, 31, 12, 'Cách đọc ERD cho sinh viên IT', 'cach-doc-erd-cho-sinh-vien-it', 'video', 'Nội dung video: Cách đọc ERD cho sinh viên IT. File seed theo course_folder mysql-database-design.', '/videos/mysql-database-design/mysql-database-design-05-cach-doc-erd-cho-sinh-vien-it.mp4', 600, 0, 'published', 5, '2026-06-27 08:00:00', '2026-06-27 08:00:00', NULL),
    (111, 32, 12, 'Thiết kế bảng users và roles', 'thiet-ke-bang-users-va-roles', 'video', 'Nội dung video: Thiết kế bảng users và roles. File seed theo course_folder mysql-database-design.', '/videos/mysql-database-design/mysql-database-design-06-thiet-ke-bang-users-va-roles.mp4', 600, 0, 'published', 1, '2026-06-27 08:00:00', '2026-06-27 08:00:00', NULL),
    (112, 32, 12, 'Thiết kế categories và course_categories', 'thiet-ke-categories-va-course-categories', 'video', 'Nội dung video: Thiết kế categories và course_categories. File seed theo course_folder mysql-database-design.', '/videos/mysql-database-design/mysql-database-design-07-thiet-ke-categories-va-course-categories.mp4', 600, 0, 'published', 2, '2026-06-27 08:00:00', '2026-06-27 08:00:00', NULL),
    (113, 32, 12, 'Thiết kế courses cho nền tảng E-learning', 'thiet-ke-courses-cho-nen-tang-e-learning', 'video', 'Nội dung video: Thiết kế courses cho nền tảng E-learning. File seed theo course_folder mysql-database-design.', '/videos/mysql-database-design/mysql-database-design-08-thiet-ke-courses-cho-nen-tang-e-learning.mp4', 600, 0, 'published', 3, '2026-06-27 08:00:00', '2026-06-27 08:00:00', NULL),
    (114, 32, 12, 'Thiết kế course_sections và lessons', 'thiet-ke-course-sections-va-lessons', 'video', 'Nội dung video: Thiết kế course_sections và lessons. File seed theo course_folder mysql-database-design.', '/videos/mysql-database-design/mysql-database-design-09-thiet-ke-course-sections-va-lessons.mp4', 600, 0, 'published', 4, '2026-06-27 08:00:00', '2026-06-27 08:00:00', NULL),
    (115, 32, 12, 'Lưu video_url và asset_url sao cho đúng', 'luu-video-url-va-asset-url-sao-cho-dung', 'video', 'Nội dung video: Lưu video_url và asset_url sao cho đúng. File seed theo course_folder mysql-database-design.', '/videos/mysql-database-design/mysql-database-design-10-luu-video-url-va-asset-url-sao-cho-dung.mp4', 600, 0, 'published', 5, '2026-06-27 08:00:00', '2026-06-27 08:00:00', NULL),
    (116, 33, 12, 'Thiết kế enrollments sau khi mua khóa học', 'thiet-ke-enrollments-sau-khi-mua-khoa-hoc', 'video', 'Nội dung video: Thiết kế enrollments sau khi mua khóa học. File seed theo course_folder mysql-database-design.', '/videos/mysql-database-design/mysql-database-design-11-thiet-ke-enrollments-sau-khi-mua-khoa-hoc.mp4', 600, 0, 'published', 1, '2026-06-27 08:00:00', '2026-06-27 08:00:00', NULL),
    (117, 33, 12, 'Thiết kế lesson_progress và video_progress', 'thiet-ke-lesson-progress-va-video-progress', 'video', 'Nội dung video: Thiết kế lesson_progress và video_progress. File seed theo course_folder mysql-database-design.', '/videos/mysql-database-design/mysql-database-design-12-thiet-ke-lesson-progress-va-video-progress.mp4', 600, 0, 'published', 2, '2026-06-27 08:00:00', '2026-06-27 08:00:00', NULL),
    (118, 33, 12, 'Thiết kế lesson_notes cho ghi chú cá nhân', 'thiet-ke-lesson-notes-cho-ghi-chu-ca-nhan', 'video', 'Nội dung video: Thiết kế lesson_notes cho ghi chú cá nhân. File seed theo course_folder mysql-database-design.', '/videos/mysql-database-design/mysql-database-design-13-thiet-ke-lesson-notes-cho-ghi-chu-ca-nhan.mp4', 600, 0, 'published', 3, '2026-06-27 08:00:00', '2026-06-27 08:00:00', NULL),
    (119, 33, 12, 'Thiết kế quiz questions options và attempts', 'thiet-ke-quiz-questions-options-va-attempts', 'video', 'Nội dung video: Thiết kế quiz questions options và attempts. File seed theo course_folder mysql-database-design.', '/videos/mysql-database-design/mysql-database-design-14-thiet-ke-quiz-questions-options-va-attempts.mp4', 600, 0, 'published', 4, '2026-06-27 08:00:00', '2026-06-27 08:00:00', NULL),
    (120, 33, 12, 'Tính progress_percent như thế nào cho hợp lý', 'tinh-progress-percent-nhu-the-nao-cho-hop-ly', 'video', 'Nội dung video: Tính progress_percent như thế nào cho hợp lý. File seed theo course_folder mysql-database-design.', '/videos/mysql-database-design/mysql-database-design-15-tinh-progress-percent-nhu-the-nao-cho-hop-ly.mp4', 600, 0, 'published', 5, '2026-06-27 08:00:00', '2026-06-27 08:00:00', NULL),
    (121, 34, 12, 'Thiết kế orders cho đơn hàng khóa học', 'thiet-ke-orders-cho-don-hang-khoa-hoc', 'video', 'Nội dung video: Thiết kế orders cho đơn hàng khóa học. File seed theo course_folder mysql-database-design.', '/videos/mysql-database-design/mysql-database-design-16-thiet-ke-orders-cho-don-hang-khoa-hoc.mp4', 600, 0, 'published', 1, '2026-06-27 08:00:00', '2026-06-27 08:00:00', NULL),
    (122, 34, 12, 'Thiết kế coupons và kiểm tra điều kiện sử dụng', 'thiet-ke-coupons-va-kiem-tra-dieu-kien-su-dung', 'video', 'Nội dung video: Thiết kế coupons và kiểm tra điều kiện sử dụng. File seed theo course_folder mysql-database-design.', '/videos/mysql-database-design/mysql-database-design-17-thiet-ke-coupons-va-kiem-tra-dieu-kien-su-dung.mp4', 600, 0, 'published', 2, '2026-06-27 08:00:00', '2026-06-27 08:00:00', NULL),
    (123, 34, 12, 'Thiết kế payment_status và order status', 'thiet-ke-payment-status-va-order-status', 'video', 'Nội dung video: Thiết kế payment_status và order status. File seed theo course_folder mysql-database-design.', '/videos/mysql-database-design/mysql-database-design-18-thiet-ke-payment-status-va-order-status.mp4', 600, 0, 'published', 3, '2026-06-27 08:00:00', '2026-06-27 08:00:00', NULL),
    (124, 34, 12, 'Thiết kế revenues cho instructor và platform', 'thiet-ke-revenues-cho-instructor-va-platform', 'video', 'Nội dung video: Thiết kế revenues cho instructor và platform. File seed theo course_folder mysql-database-design.', '/videos/mysql-database-design/mysql-database-design-19-thiet-ke-revenues-cho-instructor-va-platform.mp4', 600, 0, 'published', 4, '2026-06-27 08:00:00', '2026-06-27 08:00:00', NULL),
    (125, 34, 12, 'Vì sao nghiệp vụ thanh toán cần transaction', 'vi-sao-nghiep-vu-thanh-toan-can-transaction', 'video', 'Nội dung video: Vì sao nghiệp vụ thanh toán cần transaction. File seed theo course_folder mysql-database-design.', '/videos/mysql-database-design/mysql-database-design-20-vi-sao-nghiep-vu-thanh-toan-can-transaction.mp4', 600, 0, 'published', 5, '2026-06-27 08:00:00', '2026-06-27 08:00:00', NULL),
    (126, 35, 12, 'JOIN dữ liệu course instructor và category', 'join-du-lieu-course-instructor-va-category', 'video', 'Nội dung video: JOIN dữ liệu course instructor và category. File seed theo course_folder mysql-database-design.', '/videos/mysql-database-design/mysql-database-design-21-join-du-lieu-course-instructor-va-category.mp4', 600, 0, 'published', 1, '2026-06-27 08:00:00', '2026-06-27 08:00:00', NULL),
    (127, 35, 12, 'Index là gì và khi nào nên dùng?', 'index-la-gi-va-khi-nao-nen-dung', 'video', 'Nội dung video: Index là gì và khi nào nên dùng?. File seed theo course_folder mysql-database-design.', '/videos/mysql-database-design/mysql-database-design-22-index-la-gi-va-khi-nao-nen-dung.mp4', 600, 0, 'published', 2, '2026-06-27 08:00:00', '2026-06-27 08:00:00', NULL),
    (128, 35, 12, 'Tránh N+1 query từ góc nhìn database', 'tranh-n-plus-1-query-tu-goc-nhin-database', 'video', 'Nội dung video: Tránh N+1 query từ góc nhìn database. File seed theo course_folder mysql-database-design.', '/videos/mysql-database-design/mysql-database-design-23-tranh-n-plus-1-query-tu-goc-nhin-database.mp4', 600, 0, 'published', 3, '2026-06-27 08:00:00', '2026-06-27 08:00:00', NULL),
    (129, 35, 12, 'Soft delete và trạng thái dữ liệu', 'soft-delete-va-trang-thai-du-lieu', 'video', 'Nội dung video: Soft delete và trạng thái dữ liệu. File seed theo course_folder mysql-database-design.', '/videos/mysql-database-design/mysql-database-design-24-soft-delete-va-trang-thai-du-lieu.mp4', 600, 0, 'published', 4, '2026-06-27 08:00:00', '2026-06-27 08:00:00', NULL),
    (130, 35, 12, 'Dữ liệu demo seed cho trình chiếu sản phẩm', 'du-lieu-demo-seed-cho-trinh-chieu-san-pham', 'video', 'Nội dung video: Dữ liệu demo seed cho trình chiếu sản phẩm. File seed theo course_folder mysql-database-design.', '/videos/mysql-database-design/mysql-database-design-25-du-lieu-demo-seed-cho-trinh-chieu-san-pham.mp4', 600, 0, 'published', 5, '2026-06-27 08:00:00', '2026-06-27 08:00:00', NULL),
    (131, 36, 12, 'Kiểm tra naming convention của bảng và cột', 'kiem-tra-naming-convention-cua-bang-va-cot', 'video', 'Nội dung video: Kiểm tra naming convention của bảng và cột. File seed theo course_folder mysql-database-design.', '/videos/mysql-database-design/mysql-database-design-26-kiem-tra-naming-convention-cua-bang-va-cot.mp4', 600, 0, 'published', 1, '2026-06-27 08:00:00', '2026-06-27 08:00:00', NULL),
    (132, 36, 12, 'Kiểm tra constraint và relationship trước khi code', 'kiem-tra-constraint-va-relationship-truoc-khi-code', 'video', 'Nội dung video: Kiểm tra constraint và relationship trước khi code. File seed theo course_folder mysql-database-design.', '/videos/mysql-database-design/mysql-database-design-27-kiem-tra-constraint-va-relationship-truoc-khi-code.mp4', 600, 0, 'published', 2, '2026-06-27 08:00:00', '2026-06-27 08:00:00', NULL),
    (133, 36, 12, 'Kiểm tra dữ liệu mẫu có đủ flow demo', 'kiem-tra-du-lieu-mau-co-du-flow-demo', 'video', 'Nội dung video: Kiểm tra dữ liệu mẫu có đủ flow demo. File seed theo course_folder mysql-database-design.', '/videos/mysql-database-design/mysql-database-design-28-kiem-tra-du-lieu-mau-co-du-flow-demo.mp4', 600, 0, 'published', 3, '2026-06-27 08:00:00', '2026-06-27 08:00:00', NULL),
    (134, 36, 12, 'Checklist lỗi database thường gặp trong đồ án', 'checklist-loi-database-thuong-gap-trong-do-an', 'video', 'Nội dung video: Checklist lỗi database thường gặp trong đồ án. File seed theo course_folder mysql-database-design.', '/videos/mysql-database-design/mysql-database-design-29-checklist-loi-database-thuong-gap-trong-do-an.mp4', 600, 0, 'published', 4, '2026-06-27 08:00:00', '2026-06-27 08:00:00', NULL),
    (135, 36, 12, 'Tổng kết khóa học MySQL Database Design', 'tong-ket-khoa-hoc-mysql-database-design', 'video', 'Nội dung video: Tổng kết khóa học MySQL Database Design. File seed theo course_folder mysql-database-design.', '/videos/mysql-database-design/mysql-database-design-30-tong-ket-khoa-hoc-mysql-database-design.mp4', 600, 0, 'published', 5, '2026-06-27 08:00:00', '2026-06-27 08:00:00', NULL),
    (136, 37, 13, 'Deploy fullstack là gì?', 'deploy-fullstack-la-gi', 'video', 'Nội dung video: Deploy fullstack là gì?. File seed theo course_folder deploy-vps-aapanel.', '/videos/deploy-vps-aapanel/deploy-vps-aapanel-01-deploy-fullstack-la-gi.mp4', 600, 1, 'published', 1, '2026-06-27 08:00:00', '2026-06-27 08:00:00', NULL),
    (137, 37, 13, 'Frontend API và media domain khác nhau thế nào?', 'frontend-api-va-media-domain-khac-nhau-the-nao', 'video', 'Nội dung video: Frontend API và media domain khác nhau thế nào?. File seed theo course_folder deploy-vps-aapanel.', '/videos/deploy-vps-aapanel/deploy-vps-aapanel-02-frontend-api-va-media-domain-khac-nhau-the-nao.mp4', 600, 0, 'published', 2, '2026-06-27 08:00:00', '2026-06-27 08:00:00', NULL),
    (138, 37, 13, 'Vì sao nên tách domain frontend api và media?', 'vi-sao-nen-tach-domain-frontend-api-va-media', 'video', 'Nội dung video: Vì sao nên tách domain frontend api và media?. File seed theo course_folder deploy-vps-aapanel.', '/videos/deploy-vps-aapanel/deploy-vps-aapanel-03-vi-sao-nen-tach-domain-frontend-api-va-media.mp4', 600, 0, 'published', 3, '2026-06-27 08:00:00', '2026-06-27 08:00:00', NULL),
    (139, 37, 13, 'DNS domain và server IP hoạt động ra sao?', 'dns-domain-va-server-ip-hoat-dong-ra-sao', 'video', 'Nội dung video: DNS domain và server IP hoạt động ra sao?. File seed theo course_folder deploy-vps-aapanel.', '/videos/deploy-vps-aapanel/deploy-vps-aapanel-04-dns-domain-va-server-ip-hoat-dong-ra-sao.mp4', 600, 0, 'published', 4, '2026-06-27 08:00:00', '2026-06-27 08:00:00', NULL),
    (140, 37, 13, 'Cấu trúc thư mục chuẩn trên aaPanel', 'cau-truc-thu-muc-chuan-tren-aapanel', 'video', 'Nội dung video: Cấu trúc thư mục chuẩn trên aaPanel. File seed theo course_folder deploy-vps-aapanel.', '/videos/deploy-vps-aapanel/deploy-vps-aapanel-05-cau-truc-thu-muc-chuan-tren-aapanel.mp4', 600, 0, 'published', 5, '2026-06-27 08:00:00', '2026-06-27 08:00:00', NULL),
    (141, 38, 13, 'Checklist trước khi deploy lên VPS', 'checklist-truoc-khi-deploy-len-vps', 'video', 'Nội dung video: Checklist trước khi deploy lên VPS. File seed theo course_folder deploy-vps-aapanel.', '/videos/deploy-vps-aapanel/deploy-vps-aapanel-06-checklist-truoc-khi-deploy-len-vps.mp4', 600, 0, 'published', 1, '2026-06-27 08:00:00', '2026-06-27 08:00:00', NULL),
    (142, 38, 13, 'Tạo website frontend trên aaPanel', 'tao-website-frontend-tren-aapanel', 'video', 'Nội dung video: Tạo website frontend trên aaPanel. File seed theo course_folder deploy-vps-aapanel.', '/videos/deploy-vps-aapanel/deploy-vps-aapanel-07-tao-website-frontend-tren-aapanel.mp4', 600, 0, 'published', 2, '2026-06-27 08:00:00', '2026-06-27 08:00:00', NULL),
    (143, 38, 13, 'Tạo website api cho Laravel public folder', 'tao-website-api-cho-laravel-public-folder', 'video', 'Nội dung video: Tạo website api cho Laravel public folder. File seed theo course_folder deploy-vps-aapanel.', '/videos/deploy-vps-aapanel/deploy-vps-aapanel-08-tao-website-api-cho-laravel-public-folder.mp4', 600, 0, 'published', 3, '2026-06-27 08:00:00', '2026-06-27 08:00:00', NULL),
    (144, 38, 13, 'Tạo website media để lưu video và ảnh', 'tao-website-media-de-luu-video-va-anh', 'video', 'Nội dung video: Tạo website media để lưu video và ảnh. File seed theo course_folder deploy-vps-aapanel.', '/videos/deploy-vps-aapanel/deploy-vps-aapanel-09-tao-website-media-de-luu-video-va-anh.mp4', 600, 0, 'published', 4, '2026-06-27 08:00:00', '2026-06-27 08:00:00', NULL),
    (145, 38, 13, 'Bật SSL cho domain và subdomain', 'bat-ssl-cho-domain-va-subdomain', 'video', 'Nội dung video: Bật SSL cho domain và subdomain. File seed theo course_folder deploy-vps-aapanel.', '/videos/deploy-vps-aapanel/deploy-vps-aapanel-10-bat-ssl-cho-domain-va-subdomain.mp4', 600, 0, 'published', 5, '2026-06-27 08:00:00', '2026-06-27 08:00:00', NULL),
    (146, 39, 13, 'Upload code Laravel lên server', 'upload-code-laravel-len-server', 'video', 'Nội dung video: Upload code Laravel lên server. File seed theo course_folder deploy-vps-aapanel.', '/videos/deploy-vps-aapanel/deploy-vps-aapanel-11-upload-code-laravel-len-server.mp4', 600, 0, 'published', 1, '2026-06-27 08:00:00', '2026-06-27 08:00:00', NULL),
    (147, 39, 13, 'Cấu hình .env production cho Laravel', 'cau-hinh-env-production-cho-laravel', 'video', 'Nội dung video: Cấu hình .env production cho Laravel. File seed theo course_folder deploy-vps-aapanel.', '/videos/deploy-vps-aapanel/deploy-vps-aapanel-12-cau-hinh-env-production-cho-laravel.mp4', 600, 0, 'published', 2, '2026-06-27 08:00:00', '2026-06-27 08:00:00', NULL),
    (148, 39, 13, 'Composer install và quyền thư mục storage bootstrap cache', 'composer-install-va-quyen-thu-muc-storage-bootstrap-cache', 'video', 'Nội dung video: Composer install và quyền thư mục storage bootstrap cache. File seed theo course_folder deploy-vps-aapanel.', '/videos/deploy-vps-aapanel/deploy-vps-aapanel-13-composer-install-va-quyen-thu-muc-storage-bootstrap-cache.mp4', 600, 0, 'published', 3, '2026-06-27 08:00:00', '2026-06-27 08:00:00', NULL),
    (149, 39, 13, 'Chạy migrate seed và cache config', 'chay-migrate-seed-va-cache-config', 'video', 'Nội dung video: Chạy migrate seed và cache config. File seed theo course_folder deploy-vps-aapanel.', '/videos/deploy-vps-aapanel/deploy-vps-aapanel-14-chay-migrate-seed-va-cache-config.mp4', 600, 0, 'published', 4, '2026-06-27 08:00:00', '2026-06-27 08:00:00', NULL),
    (150, 39, 13, 'Kiểm tra API health và lỗi Laravel log', 'kiem-tra-api-health-va-loi-laravel-log', 'video', 'Nội dung video: Kiểm tra API health và lỗi Laravel log. File seed theo course_folder deploy-vps-aapanel.', '/videos/deploy-vps-aapanel/deploy-vps-aapanel-15-kiem-tra-api-health-va-loi-laravel-log.mp4', 600, 0, 'published', 5, '2026-06-27 08:00:00', '2026-06-27 08:00:00', NULL),
    (151, 40, 13, 'Cấu hình VITE_API_BASE_URL và VITE_MEDIA_BASE_URL', 'cau-hinh-vite-api-base-url-va-vite-media-base-url', 'video', 'Nội dung video: Cấu hình VITE_API_BASE_URL và VITE_MEDIA_BASE_URL. File seed theo course_folder deploy-vps-aapanel.', '/videos/deploy-vps-aapanel/deploy-vps-aapanel-16-cau-hinh-vite-api-base-url-va-vite-media-base-url.mp4', 600, 0, 'published', 1, '2026-06-27 08:00:00', '2026-06-27 08:00:00', NULL),
    (152, 40, 13, 'Build React Vite cho production', 'build-react-vite-cho-production', 'video', 'Nội dung video: Build React Vite cho production. File seed theo course_folder deploy-vps-aapanel.', '/videos/deploy-vps-aapanel/deploy-vps-aapanel-17-build-react-vite-cho-production.mp4', 600, 0, 'published', 2, '2026-06-27 08:00:00', '2026-06-27 08:00:00', NULL),
    (153, 40, 13, 'Upload dist lên website frontend', 'upload-dist-len-website-frontend', 'video', 'Nội dung video: Upload dist lên website frontend. File seed theo course_folder deploy-vps-aapanel.', '/videos/deploy-vps-aapanel/deploy-vps-aapanel-18-upload-dist-len-website-frontend.mp4', 600, 0, 'published', 3, '2026-06-27 08:00:00', '2026-06-27 08:00:00', NULL),
    (154, 40, 13, 'Cấu hình fallback cho React Router', 'cau-hinh-fallback-cho-react-router', 'video', 'Nội dung video: Cấu hình fallback cho React Router. File seed theo course_folder deploy-vps-aapanel.', '/videos/deploy-vps-aapanel/deploy-vps-aapanel-19-cau-hinh-fallback-cho-react-router.mp4', 600, 0, 'published', 4, '2026-06-27 08:00:00', '2026-06-27 08:00:00', NULL),
    (155, 40, 13, 'Kiểm tra frontend gọi API qua HTTPS', 'kiem-tra-frontend-goi-api-qua-https', 'video', 'Nội dung video: Kiểm tra frontend gọi API qua HTTPS. File seed theo course_folder deploy-vps-aapanel.', '/videos/deploy-vps-aapanel/deploy-vps-aapanel-20-kiem-tra-frontend-goi-api-qua-https.mp4', 600, 0, 'published', 5, '2026-06-27 08:00:00', '2026-06-27 08:00:00', NULL),
    (156, 41, 13, 'Tạo cây thư mục video thumbnails assets certificates', 'tao-cay-thu-muc-video-thumbnails-assets-certificates', 'video', 'Nội dung video: Tạo cây thư mục video thumbnails assets certificates. File seed theo course_folder deploy-vps-aapanel.', '/videos/deploy-vps-aapanel/deploy-vps-aapanel-21-tao-cay-thu-muc-video-thumbnails-assets-certificates.mp4', 600, 0, 'published', 1, '2026-06-27 08:00:00', '2026-06-27 08:00:00', NULL),
    (157, 41, 13, 'Upload video khóa học theo course_folder', 'upload-video-khoa-hoc-theo-course-folder', 'video', 'Nội dung video: Upload video khóa học theo course_folder. File seed theo course_folder deploy-vps-aapanel.', '/videos/deploy-vps-aapanel/deploy-vps-aapanel-22-upload-video-khoa-hoc-theo-course-folder.mp4', 600, 0, 'published', 2, '2026-06-27 08:00:00', '2026-06-27 08:00:00', NULL),
    (158, 41, 13, 'DB nên lưu relative video_url như thế nào?', 'db-nen-luu-relative-video-url-nhu-the-nao', 'video', 'Nội dung video: DB nên lưu relative video_url như thế nào?. File seed theo course_folder deploy-vps-aapanel.', '/videos/deploy-vps-aapanel/deploy-vps-aapanel-23-db-nen-luu-relative-video-url-nhu-the-nao.mp4', 600, 0, 'published', 3, '2026-06-27 08:00:00', '2026-06-27 08:00:00', NULL),
    (159, 41, 13, 'Kiểm tra public URL của file media', 'kiem-tra-public-url-cua-file-media', 'video', 'Nội dung video: Kiểm tra public URL của file media. File seed theo course_folder deploy-vps-aapanel.', '/videos/deploy-vps-aapanel/deploy-vps-aapanel-24-kiem-tra-public-url-cua-file-media.mp4', 600, 0, 'published', 4, '2026-06-27 08:00:00', '2026-06-27 08:00:00', NULL),
    (160, 41, 13, 'Backup media và database trước ngày demo', 'backup-media-va-database-truoc-ngay-demo', 'video', 'Nội dung video: Backup media và database trước ngày demo. File seed theo course_folder deploy-vps-aapanel.', '/videos/deploy-vps-aapanel/deploy-vps-aapanel-25-backup-media-va-database-truoc-ngay-demo.mp4', 600, 0, 'published', 5, '2026-06-27 08:00:00', '2026-06-27 08:00:00', NULL),
    (161, 42, 13, 'Fix lỗi 500 Laravel sau khi deploy', 'fix-loi-500-laravel-sau-khi-deploy', 'video', 'Nội dung video: Fix lỗi 500 Laravel sau khi deploy. File seed theo course_folder deploy-vps-aapanel.', '/videos/deploy-vps-aapanel/deploy-vps-aapanel-26-fix-loi-500-laravel-sau-khi-deploy.mp4', 600, 0, 'published', 1, '2026-06-27 08:00:00', '2026-06-27 08:00:00', NULL),
    (162, 42, 13, 'Fix lỗi CORS khi frontend gọi API', 'fix-loi-cors-khi-frontend-goi-api', 'video', 'Nội dung video: Fix lỗi CORS khi frontend gọi API. File seed theo course_folder deploy-vps-aapanel.', '/videos/deploy-vps-aapanel/deploy-vps-aapanel-27-fix-loi-cors-khi-frontend-goi-api.mp4', 600, 0, 'published', 2, '2026-06-27 08:00:00', '2026-06-27 08:00:00', NULL),
    (163, 42, 13, 'Fix lỗi route refresh 404 trong React', 'fix-loi-route-refresh-404-trong-react', 'video', 'Nội dung video: Fix lỗi route refresh 404 trong React. File seed theo course_folder deploy-vps-aapanel.', '/videos/deploy-vps-aapanel/deploy-vps-aapanel-28-fix-loi-route-refresh-404-trong-react.mp4', 600, 0, 'published', 3, '2026-06-27 08:00:00', '2026-06-27 08:00:00', NULL),
    (164, 42, 13, 'Checklist bảo mật cơ bản sau deploy', 'checklist-bao-mat-co-ban-sau-deploy', 'video', 'Nội dung video: Checklist bảo mật cơ bản sau deploy. File seed theo course_folder deploy-vps-aapanel.', '/videos/deploy-vps-aapanel/deploy-vps-aapanel-29-checklist-bao-mat-co-ban-sau-deploy.mp4', 600, 0, 'published', 4, '2026-06-27 08:00:00', '2026-06-27 08:00:00', NULL),
    (165, 42, 13, 'Tổng kết khóa học Deploy VPS aaPanel', 'tong-ket-khoa-hoc-deploy-vps-aapanel', 'video', 'Nội dung video: Tổng kết khóa học Deploy VPS aaPanel. File seed theo course_folder deploy-vps-aapanel.', '/videos/deploy-vps-aapanel/deploy-vps-aapanel-30-tong-ket-khoa-hoc-deploy-vps-aapanel.mp4', 600, 0, 'published', 5, '2026-06-27 08:00:00', '2026-06-27 08:00:00', NULL),
    (166, 43, 14, 'Tailwind CSS là gì và phù hợp với đồ án như thế nào?', 'tailwind-css-la-gi-va-phu-hop-voi-do-an-nhu-the-nao', 'video', 'Nội dung video: Tailwind CSS là gì và phù hợp với đồ án như thế nào?. File seed theo course_folder tailwind-ui-elearning.', '/videos/tailwind-ui-elearning/tailwind-ui-elearning-01-tailwind-css-la-gi-va-phu-hop-voi-do-an-nhu-the-nao.mp4', 600, 1, 'published', 1, '2026-06-27 08:00:00', '2026-06-27 08:00:00', NULL),
    (167, 43, 14, 'Utility class và tư duy thiết kế bằng Tailwind', 'utility-class-va-tu-duy-thiet-ke-bang-tailwind', 'video', 'Nội dung video: Utility class và tư duy thiết kế bằng Tailwind. File seed theo course_folder tailwind-ui-elearning.', '/videos/tailwind-ui-elearning/tailwind-ui-elearning-02-utility-class-va-tu-duy-thiet-ke-bang-tailwind.mp4', 600, 0, 'published', 2, '2026-06-27 08:00:00', '2026-06-27 08:00:00', NULL),
    (168, 43, 14, 'Thiết lập màu sắc typography spacing cho MindHub', 'thiet-lap-mau-sac-typography-spacing-cho-mindhub', 'video', 'Nội dung video: Thiết lập màu sắc typography spacing cho MindHub. File seed theo course_folder tailwind-ui-elearning.', '/videos/tailwind-ui-elearning/tailwind-ui-elearning-03-thiet-lap-mau-sac-typography-spacing-cho-mindhub.mp4', 600, 0, 'published', 3, '2026-06-27 08:00:00', '2026-06-27 08:00:00', NULL),
    (169, 43, 14, 'Responsive design trong Tailwind', 'responsive-design-trong-tailwind', 'video', 'Nội dung video: Responsive design trong Tailwind. File seed theo course_folder tailwind-ui-elearning.', '/videos/tailwind-ui-elearning/tailwind-ui-elearning-04-responsive-design-trong-tailwind.mp4', 600, 0, 'published', 4, '2026-06-27 08:00:00', '2026-06-27 08:00:00', NULL),
    (170, 43, 14, 'Dark mode và trạng thái hover focus active', 'dark-mode-va-trang-thai-hover-focus-active', 'video', 'Nội dung video: Dark mode và trạng thái hover focus active. File seed theo course_folder tailwind-ui-elearning.', '/videos/tailwind-ui-elearning/tailwind-ui-elearning-05-dark-mode-va-trang-thai-hover-focus-active.mp4', 600, 0, 'published', 5, '2026-06-27 08:00:00', '2026-06-27 08:00:00', NULL),
    (171, 44, 14, 'Thiết kế hero section cho E-learning', 'thiet-ke-hero-section-cho-e-learning', 'video', 'Nội dung video: Thiết kế hero section cho E-learning. File seed theo course_folder tailwind-ui-elearning.', '/videos/tailwind-ui-elearning/tailwind-ui-elearning-06-thiet-ke-hero-section-cho-e-learning.mp4', 600, 0, 'published', 1, '2026-06-27 08:00:00', '2026-06-27 08:00:00', NULL),
    (172, 44, 14, 'Thiết kế category section và search bar', 'thiet-ke-category-section-va-search-bar', 'video', 'Nội dung video: Thiết kế category section và search bar. File seed theo course_folder tailwind-ui-elearning.', '/videos/tailwind-ui-elearning/tailwind-ui-elearning-07-thiet-ke-category-section-va-search-bar.mp4', 600, 0, 'published', 2, '2026-06-27 08:00:00', '2026-06-27 08:00:00', NULL),
    (173, 44, 14, 'Thiết kế course card đẹp và dễ đọc', 'thiet-ke-course-card-dep-va-de-doc', 'video', 'Nội dung video: Thiết kế course card đẹp và dễ đọc. File seed theo course_folder tailwind-ui-elearning.', '/videos/tailwind-ui-elearning/tailwind-ui-elearning-08-thiet-ke-course-card-dep-va-de-doc.mp4', 600, 0, 'published', 3, '2026-06-27 08:00:00', '2026-06-27 08:00:00', NULL),
    (174, 44, 14, 'Thiết kế course grid responsive', 'thiet-ke-course-grid-responsive', 'video', 'Nội dung video: Thiết kế course grid responsive. File seed theo course_folder tailwind-ui-elearning.', '/videos/tailwind-ui-elearning/tailwind-ui-elearning-09-thiet-ke-course-grid-responsive.mp4', 600, 0, 'published', 4, '2026-06-27 08:00:00', '2026-06-27 08:00:00', NULL),
    (175, 44, 14, 'Thiết kế empty state khi không có khóa học', 'thiet-ke-empty-state-khi-khong-co-khoa-hoc', 'video', 'Nội dung video: Thiết kế empty state khi không có khóa học. File seed theo course_folder tailwind-ui-elearning.', '/videos/tailwind-ui-elearning/tailwind-ui-elearning-10-thiet-ke-empty-state-khi-khong-co-khoa-hoc.mp4', 600, 0, 'published', 5, '2026-06-27 08:00:00', '2026-06-27 08:00:00', NULL),
    (176, 45, 14, 'Thiết kế trang chi tiết khóa học', 'thiet-ke-trang-chi-tiet-khoa-hoc', 'video', 'Nội dung video: Thiết kế trang chi tiết khóa học. File seed theo course_folder tailwind-ui-elearning.', '/videos/tailwind-ui-elearning/tailwind-ui-elearning-11-thiet-ke-trang-chi-tiet-khoa-hoc.mp4', 600, 0, 'published', 1, '2026-06-27 08:00:00', '2026-06-27 08:00:00', NULL),
    (177, 45, 14, 'Thiết kế outline chương và bài học', 'thiet-ke-outline-chuong-va-bai-hoc', 'video', 'Nội dung video: Thiết kế outline chương và bài học. File seed theo course_folder tailwind-ui-elearning.', '/videos/tailwind-ui-elearning/tailwind-ui-elearning-12-thiet-ke-outline-chuong-va-bai-hoc.mp4', 600, 0, 'published', 2, '2026-06-27 08:00:00', '2026-06-27 08:00:00', NULL),
    (178, 45, 14, 'Thiết kế pricing box và CTA mua khóa học', 'thiet-ke-pricing-box-va-cta-mua-khoa-hoc', 'video', 'Nội dung video: Thiết kế pricing box và CTA mua khóa học. File seed theo course_folder tailwind-ui-elearning.', '/videos/tailwind-ui-elearning/tailwind-ui-elearning-13-thiet-ke-pricing-box-va-cta-mua-khoa-hoc.mp4', 600, 0, 'published', 3, '2026-06-27 08:00:00', '2026-06-27 08:00:00', NULL),
    (179, 45, 14, 'Thiết kế form coupon và order summary', 'thiet-ke-form-coupon-va-order-summary', 'video', 'Nội dung video: Thiết kế form coupon và order summary. File seed theo course_folder tailwind-ui-elearning.', '/videos/tailwind-ui-elearning/tailwind-ui-elearning-14-thiet-ke-form-coupon-va-order-summary.mp4', 600, 0, 'published', 4, '2026-06-27 08:00:00', '2026-06-27 08:00:00', NULL),
    (180, 45, 14, 'Thiết kế trạng thái paid pending failed cho đơn hàng', 'thiet-ke-trang-thai-paid-pending-failed-cho-don-hang', 'video', 'Nội dung video: Thiết kế trạng thái paid pending failed cho đơn hàng. File seed theo course_folder tailwind-ui-elearning.', '/videos/tailwind-ui-elearning/tailwind-ui-elearning-15-thiet-ke-trang-thai-paid-pending-failed-cho-don-hang.mp4', 600, 0, 'published', 5, '2026-06-27 08:00:00', '2026-06-27 08:00:00', NULL),
    (181, 46, 14, 'Thiết kế learning dashboard cho learner', 'thiet-ke-learning-dashboard-cho-learner', 'video', 'Nội dung video: Thiết kế learning dashboard cho learner. File seed theo course_folder tailwind-ui-elearning.', '/videos/tailwind-ui-elearning/tailwind-ui-elearning-16-thiet-ke-learning-dashboard-cho-learner.mp4', 600, 0, 'published', 1, '2026-06-27 08:00:00', '2026-06-27 08:00:00', NULL),
    (182, 46, 14, 'Thiết kế progress card và course progress', 'thiet-ke-progress-card-va-course-progress', 'video', 'Nội dung video: Thiết kế progress card và course progress. File seed theo course_folder tailwind-ui-elearning.', '/videos/tailwind-ui-elearning/tailwind-ui-elearning-17-thiet-ke-progress-card-va-course-progress.mp4', 600, 0, 'published', 2, '2026-06-27 08:00:00', '2026-06-27 08:00:00', NULL),
    (183, 46, 14, 'Thiết kế lesson video layout', 'thiet-ke-lesson-video-layout', 'video', 'Nội dung video: Thiết kế lesson video layout. File seed theo course_folder tailwind-ui-elearning.', '/videos/tailwind-ui-elearning/tailwind-ui-elearning-18-thiet-ke-lesson-video-layout.mp4', 600, 0, 'published', 3, '2026-06-27 08:00:00', '2026-06-27 08:00:00', NULL),
    (184, 46, 14, 'Thiết kế sidebar chương bài học', 'thiet-ke-sidebar-chuong-bai-hoc', 'video', 'Nội dung video: Thiết kế sidebar chương bài học. File seed theo course_folder tailwind-ui-elearning.', '/videos/tailwind-ui-elearning/tailwind-ui-elearning-19-thiet-ke-sidebar-chuong-bai-hoc.mp4', 600, 0, 'published', 4, '2026-06-27 08:00:00', '2026-06-27 08:00:00', NULL),
    (185, 46, 14, 'Thiết kế notes panel trong màn học', 'thiet-ke-notes-panel-trong-man-hoc', 'video', 'Nội dung video: Thiết kế notes panel trong màn học. File seed theo course_folder tailwind-ui-elearning.', '/videos/tailwind-ui-elearning/tailwind-ui-elearning-20-thiet-ke-notes-panel-trong-man-hoc.mp4', 600, 0, 'published', 5, '2026-06-27 08:00:00', '2026-06-27 08:00:00', NULL),
    (186, 47, 14, 'Thiết kế dashboard cards và chart placeholder', 'thiet-ke-dashboard-cards-va-chart-placeholder', 'video', 'Nội dung video: Thiết kế dashboard cards và chart placeholder. File seed theo course_folder tailwind-ui-elearning.', '/videos/tailwind-ui-elearning/tailwind-ui-elearning-21-thiet-ke-dashboard-cards-va-chart-placeholder.mp4', 600, 0, 'published', 1, '2026-06-27 08:00:00', '2026-06-27 08:00:00', NULL),
    (187, 47, 14, 'Thiết kế table quản lý khóa học', 'thiet-ke-table-quan-ly-khoa-hoc', 'video', 'Nội dung video: Thiết kế table quản lý khóa học. File seed theo course_folder tailwind-ui-elearning.', '/videos/tailwind-ui-elearning/tailwind-ui-elearning-22-thiet-ke-table-quan-ly-khoa-hoc.mp4', 600, 0, 'published', 2, '2026-06-27 08:00:00', '2026-06-27 08:00:00', NULL),
    (188, 47, 14, 'Thiết kế form tạo khóa học', 'thiet-ke-form-tao-khoa-hoc', 'video', 'Nội dung video: Thiết kế form tạo khóa học. File seed theo course_folder tailwind-ui-elearning.', '/videos/tailwind-ui-elearning/tailwind-ui-elearning-23-thiet-ke-form-tao-khoa-hoc.mp4', 600, 0, 'published', 3, '2026-06-27 08:00:00', '2026-06-27 08:00:00', NULL),
    (189, 47, 14, 'Thiết kế modal upload video và thumbnail', 'thiet-ke-modal-upload-video-va-thumbnail', 'video', 'Nội dung video: Thiết kế modal upload video và thumbnail. File seed theo course_folder tailwind-ui-elearning.', '/videos/tailwind-ui-elearning/tailwind-ui-elearning-24-thiet-ke-modal-upload-video-va-thumbnail.mp4', 600, 0, 'published', 4, '2026-06-27 08:00:00', '2026-06-27 08:00:00', NULL),
    (190, 47, 14, 'Thiết kế checklist publish course', 'thiet-ke-checklist-publish-course', 'video', 'Nội dung video: Thiết kế checklist publish course. File seed theo course_folder tailwind-ui-elearning.', '/videos/tailwind-ui-elearning/tailwind-ui-elearning-25-thiet-ke-checklist-publish-course.mp4', 600, 0, 'published', 5, '2026-06-27 08:00:00', '2026-06-27 08:00:00', NULL),
    (191, 48, 14, 'Chuẩn hóa component button input badge', 'chuan-hoa-component-button-input-badge', 'video', 'Nội dung video: Chuẩn hóa component button input badge. File seed theo course_folder tailwind-ui-elearning.', '/videos/tailwind-ui-elearning/tailwind-ui-elearning-26-chuan-hoa-component-button-input-badge.mp4', 600, 0, 'published', 1, '2026-06-27 08:00:00', '2026-06-27 08:00:00', NULL),
    (192, 48, 14, 'Xử lý loading skeleton và error state', 'xu-ly-loading-skeleton-va-error-state', 'video', 'Nội dung video: Xử lý loading skeleton và error state. File seed theo course_folder tailwind-ui-elearning.', '/videos/tailwind-ui-elearning/tailwind-ui-elearning-27-xu-ly-loading-skeleton-va-error-state.mp4', 600, 0, 'published', 2, '2026-06-27 08:00:00', '2026-06-27 08:00:00', NULL),
    (193, 48, 14, 'Kiểm tra responsive mobile tablet desktop', 'kiem-tra-responsive-mobile-tablet-desktop', 'video', 'Nội dung video: Kiểm tra responsive mobile tablet desktop. File seed theo course_folder tailwind-ui-elearning.', '/videos/tailwind-ui-elearning/tailwind-ui-elearning-28-kiem-tra-responsive-mobile-tablet-desktop.mp4', 600, 0, 'published', 3, '2026-06-27 08:00:00', '2026-06-27 08:00:00', NULL),
    (194, 48, 14, 'Checklist UI trước khi trình chiếu sản phẩm', 'checklist-ui-truoc-khi-trinh-chieu-san-pham', 'video', 'Nội dung video: Checklist UI trước khi trình chiếu sản phẩm. File seed theo course_folder tailwind-ui-elearning.', '/videos/tailwind-ui-elearning/tailwind-ui-elearning-29-checklist-ui-truoc-khi-trinh-chieu-san-pham.mp4', 600, 0, 'published', 4, '2026-06-27 08:00:00', '2026-06-27 08:00:00', NULL),
    (195, 48, 14, 'Tổng kết khóa học Tailwind UI E-learning', 'tong-ket-khoa-hoc-tailwind-ui-e-learning', 'video', 'Nội dung video: Tổng kết khóa học Tailwind UI E-learning. File seed theo course_folder tailwind-ui-elearning.', '/videos/tailwind-ui-elearning/tailwind-ui-elearning-30-tong-ket-khoa-hoc-tailwind-ui-e-learning.mp4', 600, 0, 'published', 5, '2026-06-27 08:00:00', '2026-06-27 08:00:00', NULL),
    (196, 49, 15, 'Vì sao phải chốt scope trước khi báo giá?', 'vi-sao-phai-chot-scope-truoc-khi-bao-gia', 'video', 'Nội dung video: Vì sao phải chốt scope trước khi báo giá?. File seed theo course_folder web-project-management.', '/videos/web-project-management/web-project-management-01-vi-sao-phai-chot-scope-truoc-khi-bao-gia.mp4', 600, 1, 'published', 1, '2026-06-27 08:00:00', '2026-06-27 08:00:00', NULL),
    (197, 49, 15, 'Cách phân loại landing page website giới thiệu và web app', 'cach-phan-loai-landing-page-website-gioi-thieu-va-web-app', 'video', 'Nội dung video: Cách phân loại landing page website giới thiệu và web app. File seed theo course_folder web-project-management.', '/videos/web-project-management/web-project-management-02-cach-phan-loai-landing-page-website-gioi-thieu-va-web-app.mp4', 600, 0, 'published', 2, '2026-06-27 08:00:00', '2026-06-27 08:00:00', NULL),
    (198, 49, 15, 'Cách bóc tách tính năng thành hạng mục', 'cach-boc-tach-tinh-nang-thanh-hang-muc', 'video', 'Nội dung video: Cách bóc tách tính năng thành hạng mục. File seed theo course_folder web-project-management.', '/videos/web-project-management/web-project-management-03-cach-boc-tach-tinh-nang-thanh-hang-muc.mp4', 600, 0, 'published', 3, '2026-06-27 08:00:00', '2026-06-27 08:00:00', NULL),
    (199, 49, 15, 'Cách xác định phần khách hàng phải cung cấp', 'cach-xac-dinh-phan-khach-hang-phai-cung-cap', 'video', 'Nội dung video: Cách xác định phần khách hàng phải cung cấp. File seed theo course_folder web-project-management.', '/videos/web-project-management/web-project-management-04-cach-xac-dinh-phan-khach-hang-phai-cung-cap.mp4', 600, 0, 'published', 4, '2026-06-27 08:00:00', '2026-06-27 08:00:00', NULL),
    (200, 49, 15, 'Cách tránh nhận dự án vượt khả năng', 'cach-tranh-nhan-du-an-vuot-kha-nang', 'video', 'Nội dung video: Cách tránh nhận dự án vượt khả năng. File seed theo course_folder web-project-management.', '/videos/web-project-management/web-project-management-05-cach-tranh-nhan-du-an-vuot-kha-nang.mp4', 600, 0, 'published', 5, '2026-06-27 08:00:00', '2026-06-27 08:00:00', NULL),
    (201, 50, 15, 'Các yếu tố ảnh hưởng đến giá một website', 'cac-yeu-to-anh-huong-den-gia-mot-website', 'video', 'Nội dung video: Các yếu tố ảnh hưởng đến giá một website. File seed theo course_folder web-project-management.', '/videos/web-project-management/web-project-management-06-cac-yeu-to-anh-huong-den-gia-mot-website.mp4', 600, 0, 'published', 1, '2026-06-27 08:00:00', '2026-06-27 08:00:00', NULL),
    (202, 50, 15, 'Cách báo giá theo gói đơn giản cho người mới', 'cach-bao-gia-theo-goi-don-gian-cho-nguoi-moi', 'video', 'Nội dung video: Cách báo giá theo gói đơn giản cho người mới. File seed theo course_folder web-project-management.', '/videos/web-project-management/web-project-management-07-cach-bao-gia-theo-goi-don-gian-cho-nguoi-moi.mp4', 600, 0, 'published', 2, '2026-06-27 08:00:00', '2026-06-27 08:00:00', NULL),
    (203, 50, 15, 'Cách ghi rõ phạm vi chỉnh sửa', 'cach-ghi-ro-pham-vi-chinh-sua', 'video', 'Nội dung video: Cách ghi rõ phạm vi chỉnh sửa. File seed theo course_folder web-project-management.', '/videos/web-project-management/web-project-management-08-cach-ghi-ro-pham-vi-chinh-sua.mp4', 600, 0, 'published', 3, '2026-06-27 08:00:00', '2026-06-27 08:00:00', NULL),
    (204, 50, 15, 'Những điều khoản cơ bản nên có trong hợp đồng', 'nhung-dieu-khoan-co-ban-nen-co-trong-hop-dong', 'video', 'Nội dung video: Những điều khoản cơ bản nên có trong hợp đồng. File seed theo course_folder web-project-management.', '/videos/web-project-management/web-project-management-09-nhung-dieu-khoan-co-ban-nen-co-trong-hop-dong.mp4', 600, 0, 'published', 4, '2026-06-27 08:00:00', '2026-06-27 08:00:00', NULL),
    (205, 50, 15, 'Cách xử lý đặt cọc thanh toán và bàn giao', 'cach-xu-ly-dat-coc-thanh-toan-va-ban-giao', 'video', 'Nội dung video: Cách xử lý đặt cọc thanh toán và bàn giao. File seed theo course_folder web-project-management.', '/videos/web-project-management/web-project-management-10-cach-xu-ly-dat-coc-thanh-toan-va-ban-giao.mp4', 600, 0, 'published', 5, '2026-06-27 08:00:00', '2026-06-27 08:00:00', NULL),
    (206, 51, 15, 'Cách lập timeline dự án web 2 đến 4 tuần', 'cach-lap-timeline-du-an-web-2-den-4-tuan', 'video', 'Nội dung video: Cách lập timeline dự án web 2 đến 4 tuần. File seed theo course_folder web-project-management.', '/videos/web-project-management/web-project-management-11-cach-lap-timeline-du-an-web-2-den-4-tuan.mp4', 600, 0, 'published', 1, '2026-06-27 08:00:00', '2026-06-27 08:00:00', NULL),
    (207, 51, 15, 'Cách gửi update tiến độ cho khách hàng', 'cach-gui-update-tien-do-cho-khach-hang', 'video', 'Nội dung video: Cách gửi update tiến độ cho khách hàng. File seed theo course_folder web-project-management.', '/videos/web-project-management/web-project-management-12-cach-gui-update-tien-do-cho-khach-hang.mp4', 600, 0, 'published', 2, '2026-06-27 08:00:00', '2026-06-27 08:00:00', NULL),
    (208, 51, 15, 'Cách nhận feedback mà không bị loạn yêu cầu', 'cach-nhan-feedback-ma-khong-bi-loan-yeu-cau', 'video', 'Nội dung video: Cách nhận feedback mà không bị loạn yêu cầu. File seed theo course_folder web-project-management.', '/videos/web-project-management/web-project-management-13-cach-nhan-feedback-ma-khong-bi-loan-yeu-cau.mp4', 600, 0, 'published', 3, '2026-06-27 08:00:00', '2026-06-27 08:00:00', NULL),
    (209, 51, 15, 'Cách quản lý file hình ảnh nội dung và tài khoản', 'cach-quan-ly-file-hinh-anh-noi-dung-va-tai-khoan', 'video', 'Nội dung video: Cách quản lý file hình ảnh nội dung và tài khoản. File seed theo course_folder web-project-management.', '/videos/web-project-management/web-project-management-14-cach-quan-ly-file-hinh-anh-noi-dung-va-tai-khoan.mp4', 600, 0, 'published', 4, '2026-06-27 08:00:00', '2026-06-27 08:00:00', NULL),
    (210, 51, 15, 'Cách xử lý khi dự án bị trễ', 'cach-xu-ly-khi-du-an-bi-tre', 'video', 'Nội dung video: Cách xử lý khi dự án bị trễ. File seed theo course_folder web-project-management.', '/videos/web-project-management/web-project-management-15-cach-xu-ly-khi-du-an-bi-tre.mp4', 600, 0, 'published', 5, '2026-06-27 08:00:00', '2026-06-27 08:00:00', NULL),
    (211, 52, 15, 'Checklist trước khi bàn giao website', 'checklist-truoc-khi-ban-giao-website', 'video', 'Nội dung video: Checklist trước khi bàn giao website. File seed theo course_folder web-project-management.', '/videos/web-project-management/web-project-management-16-checklist-truoc-khi-ban-giao-website.mp4', 600, 0, 'published', 1, '2026-06-27 08:00:00', '2026-06-27 08:00:00', NULL),
    (212, 52, 15, 'Cách viết tài liệu hướng dẫn sử dụng ngắn', 'cach-viet-tai-lieu-huong-dan-su-dung-ngan', 'video', 'Nội dung video: Cách viết tài liệu hướng dẫn sử dụng ngắn. File seed theo course_folder web-project-management.', '/videos/web-project-management/web-project-management-17-cach-viet-tai-lieu-huong-dan-su-dung-ngan.mp4', 600, 0, 'published', 2, '2026-06-27 08:00:00', '2026-06-27 08:00:00', NULL),
    (213, 52, 15, 'Cách báo giá bảo trì hosting domain và chỉnh sửa', 'cach-bao-gia-bao-tri-hosting-domain-va-chinh-sua', 'video', 'Nội dung video: Cách báo giá bảo trì hosting domain và chỉnh sửa. File seed theo course_folder web-project-management.', '/videos/web-project-management/web-project-management-18-cach-bao-gia-bao-tri-hosting-domain-va-chinh-sua.mp4', 600, 0, 'published', 3, '2026-06-27 08:00:00', '2026-06-27 08:00:00', NULL),
    (214, 52, 15, 'Cách lưu source code và backup sau dự án', 'cach-luu-source-code-va-backup-sau-du-an', 'video', 'Nội dung video: Cách lưu source code và backup sau dự án. File seed theo course_folder web-project-management.', '/videos/web-project-management/web-project-management-19-cach-luu-source-code-va-backup-sau-du-an.mp4', 600, 0, 'published', 4, '2026-06-27 08:00:00', '2026-06-27 08:00:00', NULL),
    (215, 52, 15, 'Tổng kết khóa học quản lý dự án web nhỏ', 'tong-ket-khoa-hoc-quan-ly-du-an-web-nho', 'video', 'Nội dung video: Tổng kết khóa học quản lý dự án web nhỏ. File seed theo course_folder web-project-management.', '/videos/web-project-management/web-project-management-20-tong-ket-khoa-hoc-quan-ly-du-an-web-nho.mp4', 600, 0, 'published', 5, '2026-06-27 08:00:00', '2026-06-27 08:00:00', NULL),
    (216, 53, 16, 'Freelance Web Developer là gì?', 'freelance-web-developer-la-gi', 'video', 'Nội dung video: Freelance Web Developer là gì?. File seed theo course_folder freelance-web-developer.', '/videos/freelance-web-developer/freelance-web-developer-01-freelance-web-developer-la-gi.mp4', 600, 1, 'published', 1, '2026-06-27 08:00:00', '2026-06-27 08:00:00', NULL),
    (217, 53, 16, 'Người mới nên nhận loại dự án web nào?', 'nguoi-moi-nen-nhan-loai-du-an-web-nao', 'video', 'Nội dung video: Người mới nên nhận loại dự án web nào?. File seed theo course_folder freelance-web-developer.', '/videos/freelance-web-developer/freelance-web-developer-02-nguoi-moi-nen-nhan-loai-du-an-web-nao.mp4', 600, 0, 'published', 2, '2026-06-27 08:00:00', '2026-06-27 08:00:00', NULL),
    (218, 53, 16, 'Portfolio cần có gì trước khi tìm khách hàng?', 'portfolio-can-co-gi-truoc-khi-tim-khach-hang', 'video', 'Nội dung video: Portfolio cần có gì trước khi tìm khách hàng?. File seed theo course_folder freelance-web-developer.', '/videos/freelance-web-developer/freelance-web-developer-03-portfolio-can-co-gi-truoc-khi-tim-khach-hang.mp4', 600, 0, 'published', 3, '2026-06-27 08:00:00', '2026-06-27 08:00:00', NULL),
    (219, 53, 16, 'Cách chọn niche dịch vụ web phù hợp', 'cach-chon-niche-dich-vu-web-phu-hop', 'video', 'Nội dung video: Cách chọn niche dịch vụ web phù hợp. File seed theo course_folder freelance-web-developer.', '/videos/freelance-web-developer/freelance-web-developer-04-cach-chon-niche-dich-vu-web-phu-hop.mp4', 600, 0, 'published', 4, '2026-06-27 08:00:00', '2026-06-27 08:00:00', NULL),
    (220, 53, 16, 'Những sai lầm khiến freelancer mới dễ mất tiền', 'nhung-sai-lam-khien-freelancer-moi-de-mat-tien', 'video', 'Nội dung video: Những sai lầm khiến freelancer mới dễ mất tiền. File seed theo course_folder freelance-web-developer.', '/videos/freelance-web-developer/freelance-web-developer-05-nhung-sai-lam-khien-freelancer-moi-de-mat-tien.mp4', 600, 0, 'published', 5, '2026-06-27 08:00:00', '2026-06-27 08:00:00', NULL),
    (221, 54, 16, 'Cách trình bày 3 dự án web nổi bật', 'cach-trinh-bay-3-du-an-web-noi-bat', 'video', 'Nội dung video: Cách trình bày 3 dự án web nổi bật. File seed theo course_folder freelance-web-developer.', '/videos/freelance-web-developer/freelance-web-developer-06-cach-trinh-bay-3-du-an-web-noi-bat.mp4', 600, 0, 'published', 1, '2026-06-27 08:00:00', '2026-06-27 08:00:00', NULL),
    (222, 54, 16, 'Cách viết mô tả dịch vụ làm landing page', 'cach-viet-mo-ta-dich-vu-lam-landing-page', 'video', 'Nội dung video: Cách viết mô tả dịch vụ làm landing page. File seed theo course_folder freelance-web-developer.', '/videos/freelance-web-developer/freelance-web-developer-07-cach-viet-mo-ta-dich-vu-lam-landing-page.mp4', 600, 0, 'published', 2, '2026-06-27 08:00:00', '2026-06-27 08:00:00', NULL),
    (223, 54, 16, 'Cách dùng GitHub và demo để tăng độ tin cậy', 'cach-dung-github-va-demo-de-tang-do-tin-cay', 'video', 'Nội dung video: Cách dùng GitHub và demo để tăng độ tin cậy. File seed theo course_folder freelance-web-developer.', '/videos/freelance-web-developer/freelance-web-developer-08-cach-dung-github-va-demo-de-tang-do-tin-cay.mp4', 600, 0, 'published', 3, '2026-06-27 08:00:00', '2026-06-27 08:00:00', NULL),
    (224, 54, 16, 'Cách viết case study ngắn cho project cá nhân', 'cach-viet-case-study-ngan-cho-project-ca-nhan', 'video', 'Nội dung video: Cách viết case study ngắn cho project cá nhân. File seed theo course_folder freelance-web-developer.', '/videos/freelance-web-developer/freelance-web-developer-09-cach-viet-case-study-ngan-cho-project-ca-nhan.mp4', 600, 0, 'published', 4, '2026-06-27 08:00:00', '2026-06-27 08:00:00', NULL),
    (225, 54, 16, 'Cách chuẩn bị mẫu tin nhắn giới thiệu dịch vụ', 'cach-chuan-bi-mau-tin-nhan-gioi-thieu-dich-vu', 'video', 'Nội dung video: Cách chuẩn bị mẫu tin nhắn giới thiệu dịch vụ. File seed theo course_folder freelance-web-developer.', '/videos/freelance-web-developer/freelance-web-developer-10-cach-chuan-bi-mau-tin-nhan-gioi-thieu-dich-vu.mp4', 600, 0, 'published', 5, '2026-06-27 08:00:00', '2026-06-27 08:00:00', NULL),
    (226, 55, 16, 'Tìm khách hàng đầu tiên ở đâu?', 'tim-khach-hang-dau-tien-o-dau', 'video', 'Nội dung video: Tìm khách hàng đầu tiên ở đâu?. File seed theo course_folder freelance-web-developer.', '/videos/freelance-web-developer/freelance-web-developer-11-tim-khach-hang-dau-tien-o-dau.mp4', 600, 0, 'published', 1, '2026-06-27 08:00:00', '2026-06-27 08:00:00', NULL),
    (227, 55, 16, 'Cách hỏi nhu cầu khách hàng trước khi báo giá', 'cach-hoi-nhu-cau-khach-hang-truoc-khi-bao-gia', 'video', 'Nội dung video: Cách hỏi nhu cầu khách hàng trước khi báo giá. File seed theo course_folder freelance-web-developer.', '/videos/freelance-web-developer/freelance-web-developer-12-cach-hoi-nhu-cau-khach-hang-truoc-khi-bao-gia.mp4', 600, 0, 'published', 2, '2026-06-27 08:00:00', '2026-06-27 08:00:00', NULL),
    (228, 55, 16, 'Cách giải thích phạm vi website bằng ngôn ngữ dễ hiểu', 'cach-giai-thich-pham-vi-website-bang-ngon-ngu-de-hieu', 'video', 'Nội dung video: Cách giải thích phạm vi website bằng ngôn ngữ dễ hiểu. File seed theo course_folder freelance-web-developer.', '/videos/freelance-web-developer/freelance-web-developer-13-cach-giai-thich-pham-vi-website-bang-ngon-ngu-de-hieu.mp4', 600, 0, 'published', 3, '2026-06-27 08:00:00', '2026-06-27 08:00:00', NULL),
    (229, 55, 16, 'Cách xử lý khách hàng muốn thêm chức năng liên tục', 'cach-xu-ly-khach-hang-muon-them-chuc-nang-lien-tuc', 'video', 'Nội dung video: Cách xử lý khách hàng muốn thêm chức năng liên tục. File seed theo course_folder freelance-web-developer.', '/videos/freelance-web-developer/freelance-web-developer-14-cach-xu-ly-khach-hang-muon-them-chuc-nang-lien-tuc.mp4', 600, 0, 'published', 4, '2026-06-27 08:00:00', '2026-06-27 08:00:00', NULL),
    (230, 55, 16, 'Cách giữ giao tiếp chuyên nghiệp trong dự án nhỏ', 'cach-giu-giao-tiep-chuyen-nghiep-trong-du-an-nho', 'video', 'Nội dung video: Cách giữ giao tiếp chuyên nghiệp trong dự án nhỏ. File seed theo course_folder freelance-web-developer.', '/videos/freelance-web-developer/freelance-web-developer-15-cach-giu-giao-tiep-chuyen-nghiep-trong-du-an-nho.mp4', 600, 0, 'published', 5, '2026-06-27 08:00:00', '2026-06-27 08:00:00', NULL),
    (231, 56, 16, 'Checklist bàn giao website cho khách hàng', 'checklist-ban-giao-website-cho-khach-hang', 'video', 'Nội dung video: Checklist bàn giao website cho khách hàng. File seed theo course_folder freelance-web-developer.', '/videos/freelance-web-developer/freelance-web-developer-16-checklist-ban-giao-website-cho-khach-hang.mp4', 600, 0, 'published', 1, '2026-06-27 08:00:00', '2026-06-27 08:00:00', NULL),
    (232, 56, 16, 'Cách hướng dẫn khách hàng sử dụng website', 'cach-huong-dan-khach-hang-su-dung-website', 'video', 'Nội dung video: Cách hướng dẫn khách hàng sử dụng website. File seed theo course_folder freelance-web-developer.', '/videos/freelance-web-developer/freelance-web-developer-17-cach-huong-dan-khach-hang-su-dung-website.mp4', 600, 0, 'published', 2, '2026-06-27 08:00:00', '2026-06-27 08:00:00', NULL),
    (233, 56, 16, 'Cách xin feedback và testimonial sau dự án', 'cach-xin-feedback-va-testimonial-sau-du-an', 'video', 'Nội dung video: Cách xin feedback và testimonial sau dự án. File seed theo course_folder freelance-web-developer.', '/videos/freelance-web-developer/freelance-web-developer-18-cach-xin-feedback-va-testimonial-sau-du-an.mp4', 600, 0, 'published', 3, '2026-06-27 08:00:00', '2026-06-27 08:00:00', NULL),
    (234, 56, 16, 'Cách biến một dự án nhỏ thành portfolio tốt hơn', 'cach-bien-mot-du-an-nho-thanh-portfolio-tot-hon', 'video', 'Nội dung video: Cách biến một dự án nhỏ thành portfolio tốt hơn. File seed theo course_folder freelance-web-developer.', '/videos/freelance-web-developer/freelance-web-developer-19-cach-bien-mot-du-an-nho-thanh-portfolio-tot-hon.mp4', 600, 0, 'published', 4, '2026-06-27 08:00:00', '2026-06-27 08:00:00', NULL),
    (235, 56, 16, 'Tổng kết khóa học Freelance Web Developer', 'tong-ket-khoa-hoc-freelance-web-developer', 'video', 'Nội dung video: Tổng kết khóa học Freelance Web Developer. File seed theo course_folder freelance-web-developer.', '/videos/freelance-web-developer/freelance-web-developer-20-tong-ket-khoa-hoc-freelance-web-developer.mp4', 600, 0, 'published', 5, '2026-06-27 08:00:00', '2026-06-27 08:00:00', NULL),
    (236, 57, 17, 'SaaS là gì và khác website thông thường thế nào?', 'saas-la-gi-va-khac-website-thong-thuong-the-nao', 'video', 'Nội dung video: SaaS là gì và khác website thông thường thế nào?. File seed theo course_folder saas-product-thinking.', '/videos/saas-product-thinking/saas-product-thinking-01-saas-la-gi-va-khac-website-thong-thuong-the-nao.mp4', 600, 1, 'published', 1, '2026-06-27 08:00:00', '2026-06-27 08:00:00', NULL),
    (237, 57, 17, 'Vì sao lập trình viên web nên hiểu tư duy SaaS?', 'vi-sao-lap-trinh-vien-web-nen-hieu-tu-duy-saas', 'video', 'Nội dung video: Vì sao lập trình viên web nên hiểu tư duy SaaS?. File seed theo course_folder saas-product-thinking.', '/videos/saas-product-thinking/saas-product-thinking-02-vi-sao-lap-trinh-vien-web-nen-hieu-tu-duy-saas.mp4', 600, 0, 'published', 2, '2026-06-27 08:00:00', '2026-06-27 08:00:00', NULL),
    (238, 57, 17, 'User subscription plan và billing là gì?', 'user-subscription-plan-va-billing-la-gi', 'video', 'Nội dung video: User subscription plan và billing là gì?. File seed theo course_folder saas-product-thinking.', '/videos/saas-product-thinking/saas-product-thinking-03-user-subscription-plan-va-billing-la-gi.mp4', 600, 0, 'published', 3, '2026-06-27 08:00:00', '2026-06-27 08:00:00', NULL),
    (239, 57, 17, 'Multi-tenant là gì ở mức dễ hiểu?', 'multi-tenant-la-gi-o-muc-de-hieu', 'video', 'Nội dung video: Multi-tenant là gì ở mức dễ hiểu?. File seed theo course_folder saas-product-thinking.', '/videos/saas-product-thinking/saas-product-thinking-04-multi-tenant-la-gi-o-muc-de-hieu.mp4', 600, 0, 'published', 4, '2026-06-27 08:00:00', '2026-06-27 08:00:00', NULL),
    (240, 57, 17, 'Những ví dụ SaaS gần với sinh viên IT', 'nhung-vi-du-saas-gan-voi-sinh-vien-it', 'video', 'Nội dung video: Những ví dụ SaaS gần với sinh viên IT. File seed theo course_folder saas-product-thinking.', '/videos/saas-product-thinking/saas-product-thinking-05-nhung-vi-du-saas-gan-voi-sinh-vien-it.mp4', 600, 0, 'published', 5, '2026-06-27 08:00:00', '2026-06-27 08:00:00', NULL),
    (241, 58, 17, 'Cách xác định user role trong SaaS', 'cach-xac-dinh-user-role-trong-saas', 'video', 'Nội dung video: Cách xác định user role trong SaaS. File seed theo course_folder saas-product-thinking.', '/videos/saas-product-thinking/saas-product-thinking-06-cach-xac-dinh-user-role-trong-saas.mp4', 600, 0, 'published', 1, '2026-06-27 08:00:00', '2026-06-27 08:00:00', NULL),
    (242, 58, 17, 'Cách thiết kế onboarding cho người dùng mới', 'cach-thiet-ke-onboarding-cho-nguoi-dung-moi', 'video', 'Nội dung video: Cách thiết kế onboarding cho người dùng mới. File seed theo course_folder saas-product-thinking.', '/videos/saas-product-thinking/saas-product-thinking-07-cach-thiet-ke-onboarding-cho-nguoi-dung-moi.mp4', 600, 0, 'published', 2, '2026-06-27 08:00:00', '2026-06-27 08:00:00', NULL),
    (243, 58, 17, 'Cách tổ chức dashboard cho sản phẩm SaaS', 'cach-to-chuc-dashboard-cho-san-pham-saas', 'video', 'Nội dung video: Cách tổ chức dashboard cho sản phẩm SaaS. File seed theo course_folder saas-product-thinking.', '/videos/saas-product-thinking/saas-product-thinking-08-cach-to-chuc-dashboard-cho-san-pham-saas.mp4', 600, 0, 'published', 3, '2026-06-27 08:00:00', '2026-06-27 08:00:00', NULL),
    (244, 58, 17, 'Cách nghĩ về permission và giới hạn gói dịch vụ', 'cach-nghi-ve-permission-va-gioi-han-goi-dich-vu', 'video', 'Nội dung video: Cách nghĩ về permission và giới hạn gói dịch vụ. File seed theo course_folder saas-product-thinking.', '/videos/saas-product-thinking/saas-product-thinking-09-cach-nghi-ve-permission-va-gioi-han-goi-dich-vu.mp4', 600, 0, 'published', 4, '2026-06-27 08:00:00', '2026-06-27 08:00:00', NULL),
    (245, 58, 17, 'Cách viết roadmap tính năng nhỏ', 'cach-viet-roadmap-tinh-nang-nho', 'video', 'Nội dung video: Cách viết roadmap tính năng nhỏ. File seed theo course_folder saas-product-thinking.', '/videos/saas-product-thinking/saas-product-thinking-10-cach-viet-roadmap-tinh-nang-nho.mp4', 600, 0, 'published', 5, '2026-06-27 08:00:00', '2026-06-27 08:00:00', NULL),
    (246, 59, 17, 'Metrics cơ bản của SaaS cần biết', 'metrics-co-ban-cua-saas-can-biet', 'video', 'Nội dung video: Metrics cơ bản của SaaS cần biết. File seed theo course_folder saas-product-thinking.', '/videos/saas-product-thinking/saas-product-thinking-11-metrics-co-ban-cua-saas-can-biet.mp4', 600, 0, 'published', 1, '2026-06-27 08:00:00', '2026-06-27 08:00:00', NULL),
    (247, 59, 17, 'Activation retention churn là gì?', 'activation-retention-churn-la-gi', 'video', 'Nội dung video: Activation retention churn là gì?. File seed theo course_folder saas-product-thinking.', '/videos/saas-product-thinking/saas-product-thinking-12-activation-retention-churn-la-gi.mp4', 600, 0, 'published', 2, '2026-06-27 08:00:00', '2026-06-27 08:00:00', NULL),
    (248, 59, 17, 'Cách thu feedback và ưu tiên cải tiến', 'cach-thu-feedback-va-uu-tien-cai-tien', 'video', 'Nội dung video: Cách thu feedback và ưu tiên cải tiến. File seed theo course_folder saas-product-thinking.', '/videos/saas-product-thinking/saas-product-thinking-13-cach-thu-feedback-va-uu-tien-cai-tien.mp4', 600, 0, 'published', 3, '2026-06-27 08:00:00', '2026-06-27 08:00:00', NULL),
    (249, 59, 17, 'Cách viết changelog và thông báo cập nhật', 'cach-viet-changelog-va-thong-bao-cap-nhat', 'video', 'Nội dung video: Cách viết changelog và thông báo cập nhật. File seed theo course_folder saas-product-thinking.', '/videos/saas-product-thinking/saas-product-thinking-14-cach-viet-changelog-va-thong-bao-cap-nhat.mp4', 600, 0, 'published', 4, '2026-06-27 08:00:00', '2026-06-27 08:00:00', NULL),
    (250, 59, 17, 'Cách hỗ trợ người dùng khi có lỗi', 'cach-ho-tro-nguoi-dung-khi-co-loi', 'video', 'Nội dung video: Cách hỗ trợ người dùng khi có lỗi. File seed theo course_folder saas-product-thinking.', '/videos/saas-product-thinking/saas-product-thinking-15-cach-ho-tro-nguoi-dung-khi-co-loi.mp4', 600, 0, 'published', 5, '2026-06-27 08:00:00', '2026-06-27 08:00:00', NULL),
    (251, 60, 17, 'Cách biến đồ án E-learning thành ý tưởng SaaS', 'cach-bien-do-an-e-learning-thanh-y-tuong-saas', 'video', 'Nội dung video: Cách biến đồ án E-learning thành ý tưởng SaaS. File seed theo course_folder saas-product-thinking.', '/videos/saas-product-thinking/saas-product-thinking-16-cach-bien-do-an-e-learning-thanh-y-tuong-saas.mp4', 600, 0, 'published', 1, '2026-06-27 08:00:00', '2026-06-27 08:00:00', NULL),
    (252, 60, 17, 'Cách trình bày SaaS case study trong CV', 'cach-trinh-bay-saas-case-study-trong-cv', 'video', 'Nội dung video: Cách trình bày SaaS case study trong CV. File seed theo course_folder saas-product-thinking.', '/videos/saas-product-thinking/saas-product-thinking-17-cach-trinh-bay-saas-case-study-trong-cv.mp4', 600, 0, 'published', 2, '2026-06-27 08:00:00', '2026-06-27 08:00:00', NULL),
    (253, 60, 17, 'Cách demo dashboard subscription và analytics', 'cach-demo-dashboard-subscription-va-analytics', 'video', 'Nội dung video: Cách demo dashboard subscription và analytics. File seed theo course_folder saas-product-thinking.', '/videos/saas-product-thinking/saas-product-thinking-18-cach-demo-dashboard-subscription-va-analytics.mp4', 600, 0, 'published', 3, '2026-06-27 08:00:00', '2026-06-27 08:00:00', NULL),
    (254, 60, 17, 'Checklist sản phẩm SaaS mini cho Web Developer', 'checklist-san-pham-saas-mini-cho-web-developer', 'video', 'Nội dung video: Checklist sản phẩm SaaS mini cho Web Developer. File seed theo course_folder saas-product-thinking.', '/videos/saas-product-thinking/saas-product-thinking-19-checklist-san-pham-saas-mini-cho-web-developer.mp4', 600, 0, 'published', 4, '2026-06-27 08:00:00', '2026-06-27 08:00:00', NULL),
    (255, 60, 17, 'Tổng kết khóa học tư duy SaaS cho Web Developer', 'tong-ket-khoa-hoc-tu-duy-saas-cho-web-developer', 'video', 'Nội dung video: Tổng kết khóa học tư duy SaaS cho Web Developer. File seed theo course_folder saas-product-thinking.', '/videos/saas-product-thinking/saas-product-thinking-20-tong-ket-khoa-hoc-tu-duy-saas-cho-web-developer.mp4', 600, 0, 'published', 5, '2026-06-27 08:00:00', '2026-06-27 08:00:00', NULL),
    (256, 61, 18, 'MVP là gì và vì sao người làm web nên biết?', 'mvp-la-gi-va-vi-sao-nguoi-lam-web-nen-biet', 'video', 'Nội dung video: MVP là gì và vì sao người làm web nên biết?. File seed theo course_folder mvp-web-product.', '/videos/mvp-web-product/mvp-web-product-01-mvp-la-gi-va-vi-sao-nguoi-lam-web-nen-biet.mp4', 600, 1, 'published', 1, '2026-06-27 08:00:00', '2026-06-27 08:00:00', NULL),
    (257, 61, 18, 'Sản phẩm web khác project học tập như thế nào?', 'san-pham-web-khac-project-hoc-tap-nhu-the-nao', 'video', 'Nội dung video: Sản phẩm web khác project học tập như thế nào?. File seed theo course_folder mvp-web-product.', '/videos/mvp-web-product/mvp-web-product-02-san-pham-web-khac-project-hoc-tap-nhu-the-nao.mp4', 600, 0, 'published', 2, '2026-06-27 08:00:00', '2026-06-27 08:00:00', NULL),
    (258, 61, 18, 'Cách tìm vấn đề nhỏ đáng giải quyết', 'cach-tim-van-de-nho-dang-giai-quyet', 'video', 'Nội dung video: Cách tìm vấn đề nhỏ đáng giải quyết. File seed theo course_folder mvp-web-product.', '/videos/mvp-web-product/mvp-web-product-03-cach-tim-van-de-nho-dang-giai-quyet.mp4', 600, 0, 'published', 3, '2026-06-27 08:00:00', '2026-06-27 08:00:00', NULL),
    (259, 61, 18, 'Cách xác định người dùng mục tiêu', 'cach-xac-dinh-nguoi-dung-muc-tieu', 'video', 'Nội dung video: Cách xác định người dùng mục tiêu. File seed theo course_folder mvp-web-product.', '/videos/mvp-web-product/mvp-web-product-04-cach-xac-dinh-nguoi-dung-muc-tieu.mp4', 600, 0, 'published', 4, '2026-06-27 08:00:00', '2026-06-27 08:00:00', NULL),
    (260, 61, 18, 'Những lỗi khi làm MVP quá lớn ngay từ đầu', 'nhung-loi-khi-lam-mvp-qua-lon-ngay-tu-dau', 'video', 'Nội dung video: Những lỗi khi làm MVP quá lớn ngay từ đầu. File seed theo course_folder mvp-web-product.', '/videos/mvp-web-product/mvp-web-product-05-nhung-loi-khi-lam-mvp-qua-lon-ngay-tu-dau.mp4', 600, 0, 'published', 5, '2026-06-27 08:00:00', '2026-06-27 08:00:00', NULL),
    (261, 62, 18, 'Cách viết problem solution cho ý tưởng web', 'cach-viet-problem-solution-cho-y-tuong-web', 'video', 'Nội dung video: Cách viết problem solution cho ý tưởng web. File seed theo course_folder mvp-web-product.', '/videos/mvp-web-product/mvp-web-product-06-cach-viet-problem-solution-cho-y-tuong-web.mp4', 600, 0, 'published', 1, '2026-06-27 08:00:00', '2026-06-27 08:00:00', NULL),
    (262, 62, 18, 'Cách chọn 3 tính năng cốt lõi đầu tiên', 'cach-chon-3-tinh-nang-cot-loi-dau-tien', 'video', 'Nội dung video: Cách chọn 3 tính năng cốt lõi đầu tiên. File seed theo course_folder mvp-web-product.', '/videos/mvp-web-product/mvp-web-product-07-cach-chon-3-tinh-nang-cot-loi-dau-tien.mp4', 600, 0, 'published', 2, '2026-06-27 08:00:00', '2026-06-27 08:00:00', NULL),
    (263, 62, 18, 'Cách vẽ user flow đơn giản', 'cach-ve-user-flow-don-gian', 'video', 'Nội dung video: Cách vẽ user flow đơn giản. File seed theo course_folder mvp-web-product.', '/videos/mvp-web-product/mvp-web-product-08-cach-ve-user-flow-don-gian.mp4', 600, 0, 'published', 3, '2026-06-27 08:00:00', '2026-06-27 08:00:00', NULL),
    (264, 62, 18, 'Cách thiết kế database tối thiểu cho MVP', 'cach-thiet-ke-database-toi-thieu-cho-mvp', 'video', 'Nội dung video: Cách thiết kế database tối thiểu cho MVP. File seed theo course_folder mvp-web-product.', '/videos/mvp-web-product/mvp-web-product-09-cach-thiet-ke-database-toi-thieu-cho-mvp.mp4', 600, 0, 'published', 4, '2026-06-27 08:00:00', '2026-06-27 08:00:00', NULL),
    (265, 62, 18, 'Cách ưu tiên chức năng bằng impact effort', 'cach-uu-tien-chuc-nang-bang-impact-effort', 'video', 'Nội dung video: Cách ưu tiên chức năng bằng impact effort. File seed theo course_folder mvp-web-product.', '/videos/mvp-web-product/mvp-web-product-10-cach-uu-tien-chuc-nang-bang-impact-effort.mp4', 600, 0, 'published', 5, '2026-06-27 08:00:00', '2026-06-27 08:00:00', NULL),
    (266, 63, 18, 'Cách tạo landing page kiểm tra nhu cầu', 'cach-tao-landing-page-kiem-tra-nhu-cau', 'video', 'Nội dung video: Cách tạo landing page kiểm tra nhu cầu. File seed theo course_folder mvp-web-product.', '/videos/mvp-web-product/mvp-web-product-11-cach-tao-landing-page-kiem-tra-nhu-cau.mp4', 600, 0, 'published', 1, '2026-06-27 08:00:00', '2026-06-27 08:00:00', NULL),
    (267, 63, 18, 'Cách làm prototype trước khi code toàn bộ', 'cach-lam-prototype-truoc-khi-code-toan-bo', 'video', 'Nội dung video: Cách làm prototype trước khi code toàn bộ. File seed theo course_folder mvp-web-product.', '/videos/mvp-web-product/mvp-web-product-12-cach-lam-prototype-truoc-khi-code-toan-bo.mp4', 600, 0, 'published', 2, '2026-06-27 08:00:00', '2026-06-27 08:00:00', NULL),
    (268, 63, 18, 'Cách dùng analytics để đo hành vi người dùng', 'cach-dung-analytics-de-do-hanh-vi-nguoi-dung', 'video', 'Nội dung video: Cách dùng analytics để đo hành vi người dùng. File seed theo course_folder mvp-web-product.', '/videos/mvp-web-product/mvp-web-product-13-cach-dung-analytics-de-do-hanh-vi-nguoi-dung.mp4', 600, 0, 'published', 3, '2026-06-27 08:00:00', '2026-06-27 08:00:00', NULL),
    (269, 63, 18, 'Cách nhận feedback từ người dùng đầu tiên', 'cach-nhan-feedback-tu-nguoi-dung-dau-tien', 'video', 'Nội dung video: Cách nhận feedback từ người dùng đầu tiên. File seed theo course_folder mvp-web-product.', '/videos/mvp-web-product/mvp-web-product-14-cach-nhan-feedback-tu-nguoi-dung-dau-tien.mp4', 600, 0, 'published', 4, '2026-06-27 08:00:00', '2026-06-27 08:00:00', NULL),
    (270, 63, 18, 'Cách quyết định giữ sửa hay bỏ tính năng', 'cach-quyet-dinh-giu-sua-hay-bo-tinh-nang', 'video', 'Nội dung video: Cách quyết định giữ sửa hay bỏ tính năng. File seed theo course_folder mvp-web-product.', '/videos/mvp-web-product/mvp-web-product-15-cach-quyet-dinh-giu-sua-hay-bo-tinh-nang.mp4', 600, 0, 'published', 5, '2026-06-27 08:00:00', '2026-06-27 08:00:00', NULL),
    (271, 64, 18, 'Cách trình bày MVP trong portfolio', 'cach-trinh-bay-mvp-trong-portfolio', 'video', 'Nội dung video: Cách trình bày MVP trong portfolio. File seed theo course_folder mvp-web-product.', '/videos/mvp-web-product/mvp-web-product-16-cach-trinh-bay-mvp-trong-portfolio.mp4', 600, 0, 'published', 1, '2026-06-27 08:00:00', '2026-06-27 08:00:00', NULL),
    (272, 64, 18, 'Cách viết case study sản phẩm cá nhân', 'cach-viet-case-study-san-pham-ca-nhan', 'video', 'Nội dung video: Cách viết case study sản phẩm cá nhân. File seed theo course_folder mvp-web-product.', '/videos/mvp-web-product/mvp-web-product-17-cach-viet-case-study-san-pham-ca-nhan.mp4', 600, 0, 'published', 2, '2026-06-27 08:00:00', '2026-06-27 08:00:00', NULL),
    (273, 64, 18, 'Cách chuẩn bị demo MVP cho nhà tuyển dụng', 'cach-chuan-bi-demo-mvp-cho-nha-tuyen-dung', 'video', 'Nội dung video: Cách chuẩn bị demo MVP cho nhà tuyển dụng. File seed theo course_folder mvp-web-product.', '/videos/mvp-web-product/mvp-web-product-18-cach-chuan-bi-demo-mvp-cho-nha-tuyen-dung.mp4', 600, 0, 'published', 3, '2026-06-27 08:00:00', '2026-06-27 08:00:00', NULL),
    (274, 64, 18, 'Cách phát triển MVP thành SaaS nhỏ', 'cach-phat-trien-mvp-thanh-saas-nho', 'video', 'Nội dung video: Cách phát triển MVP thành SaaS nhỏ. File seed theo course_folder mvp-web-product.', '/videos/mvp-web-product/mvp-web-product-19-cach-phat-trien-mvp-thanh-saas-nho.mp4', 600, 0, 'published', 4, '2026-06-27 08:00:00', '2026-06-27 08:00:00', NULL),
    (275, 64, 18, 'Tổng kết khóa học xây MVP sản phẩm Web', 'tong-ket-khoa-hoc-xay-mvp-san-pham-web', 'video', 'Nội dung video: Tổng kết khóa học xây MVP sản phẩm Web. File seed theo course_folder mvp-web-product.', '/videos/mvp-web-product/mvp-web-product-20-tong-ket-khoa-hoc-xay-mvp-san-pham-web.mp4', 600, 0, 'published', 5, '2026-06-27 08:00:00', '2026-06-27 08:00:00', NULL),
    (276, 65, 19, 'Vì sao lập trình viên web cần giao tiếp tốt?', 'vi-sao-lap-trinh-vien-web-can-giao-tiep-tot', 'video', 'Nội dung video: Vì sao lập trình viên web cần giao tiếp tốt?. File seed theo course_folder soft-communication-it.', '/videos/soft-communication-it/soft-communication-it-01-vi-sao-lap-trinh-vien-web-can-giao-tiep-tot.mp4', 600, 1, 'published', 1, '2026-06-27 08:00:00', '2026-06-27 08:00:00', NULL),
    (277, 65, 19, 'Cách trình bày vấn đề kỹ thuật cho người không chuyên', 'cach-trinh-bay-van-de-ky-thuat-cho-nguoi-khong-chuyen', 'video', 'Nội dung video: Cách trình bày vấn đề kỹ thuật cho người không chuyên. File seed theo course_folder soft-communication-it.', '/videos/soft-communication-it/soft-communication-it-02-cach-trinh-bay-van-de-ky-thuat-cho-nguoi-khong-chuyen.mp4', 600, 0, 'published', 2, '2026-06-27 08:00:00', '2026-06-27 08:00:00', NULL),
    (278, 65, 19, 'Cách hỏi khi gặp lỗi mà không làm mất thời gian của team', 'cach-hoi-khi-gap-loi-ma-khong-lam-mat-thoi-gian-cua-team', 'video', 'Nội dung video: Cách hỏi khi gặp lỗi mà không làm mất thời gian của team. File seed theo course_folder soft-communication-it.', '/videos/soft-communication-it/soft-communication-it-03-cach-hoi-khi-gap-loi-ma-khong-lam-mat-thoi-gian-cua-team.mp4', 600, 0, 'published', 3, '2026-06-27 08:00:00', '2026-06-27 08:00:00', NULL),
    (279, 65, 19, 'Cách báo cáo tiến độ task rõ ràng', 'cach-bao-cao-tien-do-task-ro-rang', 'video', 'Nội dung video: Cách báo cáo tiến độ task rõ ràng. File seed theo course_folder soft-communication-it.', '/videos/soft-communication-it/soft-communication-it-04-cach-bao-cao-tien-do-task-ro-rang.mp4', 600, 0, 'published', 4, '2026-06-27 08:00:00', '2026-06-27 08:00:00', NULL),
    (280, 65, 19, 'Cách ghi chú quyết định kỹ thuật sau cuộc họp', 'cach-ghi-chu-quyet-dinh-ky-thuat-sau-cuoc-hop', 'video', 'Nội dung video: Cách ghi chú quyết định kỹ thuật sau cuộc họp. File seed theo course_folder soft-communication-it.', '/videos/soft-communication-it/soft-communication-it-05-cach-ghi-chu-quyet-dinh-ky-thuat-sau-cuoc-hop.mp4', 600, 0, 'published', 5, '2026-06-27 08:00:00', '2026-06-27 08:00:00', NULL),
    (281, 66, 19, 'Cách thống nhất API contract giữa Backend và Frontend', 'cach-thong-nhat-api-contract-giua-backend-va-frontend', 'video', 'Nội dung video: Cách thống nhất API contract giữa Backend và Frontend. File seed theo course_folder soft-communication-it.', '/videos/soft-communication-it/soft-communication-it-06-cach-thong-nhat-api-contract-giua-backend-va-frontend.mp4', 600, 0, 'published', 1, '2026-06-27 08:00:00', '2026-06-27 08:00:00', NULL),
    (282, 66, 19, 'Cách trao đổi khi response API thay đổi', 'cach-trao-doi-khi-response-api-thay-doi', 'video', 'Nội dung video: Cách trao đổi khi response API thay đổi. File seed theo course_folder soft-communication-it.', '/videos/soft-communication-it/soft-communication-it-07-cach-trao-doi-khi-response-api-thay-doi.mp4', 600, 0, 'published', 2, '2026-06-27 08:00:00', '2026-06-27 08:00:00', NULL),
    (283, 66, 19, 'Cách mô tả bug để teammate tái hiện được', 'cach-mo-ta-bug-de-teammate-tai-hien-duoc', 'video', 'Nội dung video: Cách mô tả bug để teammate tái hiện được. File seed theo course_folder soft-communication-it.', '/videos/soft-communication-it/soft-communication-it-08-cach-mo-ta-bug-de-teammate-tai-hien-duoc.mp4', 600, 0, 'published', 3, '2026-06-27 08:00:00', '2026-06-27 08:00:00', NULL),
    (284, 66, 19, 'Cách feedback UI UX mà không gây căng thẳng', 'cach-feedback-ui-ux-ma-khong-gay-cang-thang', 'video', 'Nội dung video: Cách feedback UI UX mà không gây căng thẳng. File seed theo course_folder soft-communication-it.', '/videos/soft-communication-it/soft-communication-it-09-cach-feedback-ui-ux-ma-khong-gay-cang-thang.mp4', 600, 0, 'published', 4, '2026-06-27 08:00:00', '2026-06-27 08:00:00', NULL),
    (285, 66, 19, 'Cách xử lý hiểu lầm khi chia module đồ án', 'cach-xu-ly-hieu-lam-khi-chia-module-do-an', 'video', 'Nội dung video: Cách xử lý hiểu lầm khi chia module đồ án. File seed theo course_folder soft-communication-it.', '/videos/soft-communication-it/soft-communication-it-10-cach-xu-ly-hieu-lam-khi-chia-module-do-an.mp4', 600, 0, 'published', 5, '2026-06-27 08:00:00', '2026-06-27 08:00:00', NULL),
    (286, 67, 19, 'Cách trình bày demo sản phẩm trong 5 phút', 'cach-trinh-bay-demo-san-pham-trong-5-phut', 'video', 'Nội dung video: Cách trình bày demo sản phẩm trong 5 phút. File seed theo course_folder soft-communication-it.', '/videos/soft-communication-it/soft-communication-it-11-cach-trinh-bay-demo-san-pham-trong-5-phut.mp4', 600, 0, 'published', 1, '2026-06-27 08:00:00', '2026-06-27 08:00:00', NULL),
    (287, 67, 19, 'Cách giải thích tính năng web bằng ngôn ngữ nghiệp vụ', 'cach-giai-thich-tinh-nang-web-bang-ngon-ngu-nghiep-vu', 'video', 'Nội dung video: Cách giải thích tính năng web bằng ngôn ngữ nghiệp vụ. File seed theo course_folder soft-communication-it.', '/videos/soft-communication-it/soft-communication-it-12-cach-giai-thich-tinh-nang-web-bang-ngon-ngu-nghiep-vu.mp4', 600, 0, 'published', 2, '2026-06-27 08:00:00', '2026-06-27 08:00:00', NULL),
    (288, 67, 19, 'Cách trả lời khi không biết câu hỏi kỹ thuật', 'cach-tra-loi-khi-khong-biet-cau-hoi-ky-thuat', 'video', 'Nội dung video: Cách trả lời khi không biết câu hỏi kỹ thuật. File seed theo course_folder soft-communication-it.', '/videos/soft-communication-it/soft-communication-it-13-cach-tra-loi-khi-khong-biet-cau-hoi-ky-thuat.mp4', 600, 0, 'published', 3, '2026-06-27 08:00:00', '2026-06-27 08:00:00', NULL),
    (289, 67, 19, 'Cách hỏi lại yêu cầu để tránh làm sai chức năng', 'cach-hoi-lai-yeu-cau-de-tranh-lam-sai-chuc-nang', 'video', 'Nội dung video: Cách hỏi lại yêu cầu để tránh làm sai chức năng. File seed theo course_folder soft-communication-it.', '/videos/soft-communication-it/soft-communication-it-14-cach-hoi-lai-yeu-cau-de-tranh-lam-sai-chuc-nang.mp4', 600, 0, 'published', 4, '2026-06-27 08:00:00', '2026-06-27 08:00:00', NULL),
    (290, 67, 19, 'Cách viết email hoặc tin nhắn chuyên nghiệp trong dự án', 'cach-viet-email-hoac-tin-nhan-chuyen-nghiep-trong-du-an', 'video', 'Nội dung video: Cách viết email hoặc tin nhắn chuyên nghiệp trong dự án. File seed theo course_folder soft-communication-it.', '/videos/soft-communication-it/soft-communication-it-15-cach-viet-email-hoac-tin-nhan-chuyen-nghiep-trong-du-an.mp4', 600, 0, 'published', 5, '2026-06-27 08:00:00', '2026-06-27 08:00:00', NULL),
    (291, 68, 19, 'Mẫu daily update cho sinh viên làm đồ án', 'mau-daily-update-cho-sinh-vien-lam-do-an', 'video', 'Nội dung video: Mẫu daily update cho sinh viên làm đồ án. File seed theo course_folder soft-communication-it.', '/videos/soft-communication-it/soft-communication-it-16-mau-daily-update-cho-sinh-vien-lam-do-an.mp4', 600, 0, 'published', 1, '2026-06-27 08:00:00', '2026-06-27 08:00:00', NULL),
    (292, 68, 19, 'Mẫu báo lỗi API cho team Backend', 'mau-bao-loi-api-cho-team-backend', 'video', 'Nội dung video: Mẫu báo lỗi API cho team Backend. File seed theo course_folder soft-communication-it.', '/videos/soft-communication-it/soft-communication-it-17-mau-bao-loi-api-cho-team-backend.mp4', 600, 0, 'published', 2, '2026-06-27 08:00:00', '2026-06-27 08:00:00', NULL),
    (293, 68, 19, 'Mẫu mô tả task GitHub Issue rõ ràng', 'mau-mo-ta-task-github-issue-ro-rang', 'video', 'Nội dung video: Mẫu mô tả task GitHub Issue rõ ràng. File seed theo course_folder soft-communication-it.', '/videos/soft-communication-it/soft-communication-it-18-mau-mo-ta-task-github-issue-ro-rang.mp4', 600, 0, 'published', 3, '2026-06-27 08:00:00', '2026-06-27 08:00:00', NULL),
    (294, 68, 19, 'Checklist giao tiếp trước ngày demo', 'checklist-giao-tiep-truoc-ngay-demo', 'video', 'Nội dung video: Checklist giao tiếp trước ngày demo. File seed theo course_folder soft-communication-it.', '/videos/soft-communication-it/soft-communication-it-19-checklist-giao-tiep-truoc-ngay-demo.mp4', 600, 0, 'published', 4, '2026-06-27 08:00:00', '2026-06-27 08:00:00', NULL),
    (295, 68, 19, 'Tổng kết khóa học giao tiếp trong team IT', 'tong-ket-khoa-hoc-giao-tiep-trong-team-it', 'video', 'Nội dung video: Tổng kết khóa học giao tiếp trong team IT. File seed theo course_folder soft-communication-it.', '/videos/soft-communication-it/soft-communication-it-20-tong-ket-khoa-hoc-giao-tiep-trong-team-it.mp4', 600, 0, 'published', 5, '2026-06-27 08:00:00', '2026-06-27 08:00:00', NULL),
    (296, 69, 20, 'Teamwork trong dự án web khác làm bài cá nhân thế nào?', 'teamwork-trong-du-an-web-khac-lam-bai-ca-nhan-the-nao', 'video', 'Nội dung video: Teamwork trong dự án web khác làm bài cá nhân thế nào?. File seed theo course_folder teamwork-agile-web.', '/videos/teamwork-agile-web/teamwork-agile-web-01-teamwork-trong-du-an-web-khac-lam-bai-ca-nhan-the-nao.mp4', 600, 1, 'published', 1, '2026-06-27 08:00:00', '2026-06-27 08:00:00', NULL),
    (297, 69, 20, 'Vai trò Backend Frontend Tester UI UX trong nhóm', 'vai-tro-backend-frontend-tester-ui-ux-trong-nhom', 'video', 'Nội dung video: Vai trò Backend Frontend Tester UI UX trong nhóm. File seed theo course_folder teamwork-agile-web.', '/videos/teamwork-agile-web/teamwork-agile-web-02-vai-tro-backend-frontend-tester-ui-ux-trong-nhom.mp4', 600, 0, 'published', 2, '2026-06-27 08:00:00', '2026-06-27 08:00:00', NULL),
    (298, 69, 20, 'Cách chia module để tránh chồng chéo', 'cach-chia-module-de-tranh-chong-cheo', 'video', 'Nội dung video: Cách chia module để tránh chồng chéo. File seed theo course_folder teamwork-agile-web.', '/videos/teamwork-agile-web/teamwork-agile-web-03-cach-chia-module-de-tranh-chong-cheo.mp4', 600, 0, 'published', 3, '2026-06-27 08:00:00', '2026-06-27 08:00:00', NULL),
    (299, 69, 20, 'Cách thống nhất deadline và phạm vi đồ án', 'cach-thong-nhat-deadline-va-pham-vi-do-an', 'video', 'Nội dung video: Cách thống nhất deadline và phạm vi đồ án. File seed theo course_folder teamwork-agile-web.', '/videos/teamwork-agile-web/teamwork-agile-web-04-cach-thong-nhat-deadline-va-pham-vi-do-an.mp4', 600, 0, 'published', 4, '2026-06-27 08:00:00', '2026-06-27 08:00:00', NULL),
    (300, 69, 20, 'Những lỗi teamwork khiến đồ án bị trễ', 'nhung-loi-teamwork-khien-do-an-bi-tre', 'video', 'Nội dung video: Những lỗi teamwork khiến đồ án bị trễ. File seed theo course_folder teamwork-agile-web.', '/videos/teamwork-agile-web/teamwork-agile-web-05-nhung-loi-teamwork-khien-do-an-bi-tre.mp4', 600, 0, 'published', 5, '2026-06-27 08:00:00', '2026-06-27 08:00:00', NULL),
    (301, 70, 20, 'Agile là gì theo cách dễ hiểu?', 'agile-la-gi-theo-cach-de-hieu', 'video', 'Nội dung video: Agile là gì theo cách dễ hiểu?. File seed theo course_folder teamwork-agile-web.', '/videos/teamwork-agile-web/teamwork-agile-web-06-agile-la-gi-theo-cach-de-hieu.mp4', 600, 0, 'published', 1, '2026-06-27 08:00:00', '2026-06-27 08:00:00', NULL),
    (302, 70, 20, 'Sprint là gì và áp dụng vào đồ án ra sao?', 'sprint-la-gi-va-ap-dung-vao-do-an-ra-sao', 'video', 'Nội dung video: Sprint là gì và áp dụng vào đồ án ra sao?. File seed theo course_folder teamwork-agile-web.', '/videos/teamwork-agile-web/teamwork-agile-web-07-sprint-la-gi-va-ap-dung-vao-do-an-ra-sao.mp4', 600, 0, 'published', 2, '2026-06-27 08:00:00', '2026-06-27 08:00:00', NULL),
    (303, 70, 20, 'Daily meeting nên nói gì cho đúng trọng tâm?', 'daily-meeting-nen-noi-gi-cho-dung-trong-tam', 'video', 'Nội dung video: Daily meeting nên nói gì cho đúng trọng tâm?. File seed theo course_folder teamwork-agile-web.', '/videos/teamwork-agile-web/teamwork-agile-web-08-daily-meeting-nen-noi-gi-cho-dung-trong-tam.mp4', 600, 0, 'published', 3, '2026-06-27 08:00:00', '2026-06-27 08:00:00', NULL),
    (304, 70, 20, 'Sprint planning cho nhóm làm MindHub', 'sprint-planning-cho-nhom-lam-mindhub', 'video', 'Nội dung video: Sprint planning cho nhóm làm MindHub. File seed theo course_folder teamwork-agile-web.', '/videos/teamwork-agile-web/teamwork-agile-web-09-sprint-planning-cho-nhom-lam-mindhub.mp4', 600, 0, 'published', 4, '2026-06-27 08:00:00', '2026-06-27 08:00:00', NULL),
    (305, 70, 20, 'Retrospective sau mỗi sprint để cải thiện team', 'retrospective-sau-moi-sprint-de-cai-thien-team', 'video', 'Nội dung video: Retrospective sau mỗi sprint để cải thiện team. File seed theo course_folder teamwork-agile-web.', '/videos/teamwork-agile-web/teamwork-agile-web-10-retrospective-sau-moi-sprint-de-cai-thien-team.mp4', 600, 0, 'published', 5, '2026-06-27 08:00:00', '2026-06-27 08:00:00', NULL),
    (306, 71, 20, 'Dùng GitHub Issues để chia task nhóm', 'dung-github-issues-de-chia-task-nhom', 'video', 'Nội dung video: Dùng GitHub Issues để chia task nhóm. File seed theo course_folder teamwork-agile-web.', '/videos/teamwork-agile-web/teamwork-agile-web-11-dung-github-issues-de-chia-task-nhom.mp4', 600, 0, 'published', 1, '2026-06-27 08:00:00', '2026-06-27 08:00:00', NULL),
    (307, 71, 20, 'Cách viết task có acceptance criteria', 'cach-viet-task-co-acceptance-criteria', 'video', 'Nội dung video: Cách viết task có acceptance criteria. File seed theo course_folder teamwork-agile-web.', '/videos/teamwork-agile-web/teamwork-agile-web-12-cach-viet-task-co-acceptance-criteria.mp4', 600, 0, 'published', 2, '2026-06-27 08:00:00', '2026-06-27 08:00:00', NULL),
    (308, 71, 20, 'Cách theo dõi tiến độ không gây áp lực độc hại', 'cach-theo-doi-tien-do-khong-gay-ap-luc-doc-hai', 'video', 'Nội dung video: Cách theo dõi tiến độ không gây áp lực độc hại. File seed theo course_folder teamwork-agile-web.', '/videos/teamwork-agile-web/teamwork-agile-web-13-cach-theo-doi-tien-do-khong-gay-ap-luc-doc-hai.mp4', 600, 0, 'published', 3, '2026-06-27 08:00:00', '2026-06-27 08:00:00', NULL),
    (309, 71, 20, 'Cách xử lý thành viên chậm task', 'cach-xu-ly-thanh-vien-cham-task', 'video', 'Nội dung video: Cách xử lý thành viên chậm task. File seed theo course_folder teamwork-agile-web.', '/videos/teamwork-agile-web/teamwork-agile-web-14-cach-xu-ly-thanh-vien-cham-task.mp4', 600, 0, 'published', 4, '2026-06-27 08:00:00', '2026-06-27 08:00:00', NULL),
    (310, 71, 20, 'Cách chốt scope khi gần deadline', 'cach-chot-scope-khi-gan-deadline', 'video', 'Nội dung video: Cách chốt scope khi gần deadline. File seed theo course_folder teamwork-agile-web.', '/videos/teamwork-agile-web/teamwork-agile-web-15-cach-chot-scope-khi-gan-deadline.mp4', 600, 0, 'published', 5, '2026-06-27 08:00:00', '2026-06-27 08:00:00', NULL),
    (311, 72, 20, 'Checklist tổng hợp code trước ngày demo', 'checklist-tong-hop-code-truoc-ngay-demo', 'video', 'Nội dung video: Checklist tổng hợp code trước ngày demo. File seed theo course_folder teamwork-agile-web.', '/videos/teamwork-agile-web/teamwork-agile-web-16-checklist-tong-hop-code-truoc-ngay-demo.mp4', 600, 0, 'published', 1, '2026-06-27 08:00:00', '2026-06-27 08:00:00', NULL),
    (312, 72, 20, 'Cách phân vai khi thuyết trình sản phẩm', 'cach-phan-vai-khi-thuyet-trinh-san-pham', 'video', 'Nội dung video: Cách phân vai khi thuyết trình sản phẩm. File seed theo course_folder teamwork-agile-web.', '/videos/teamwork-agile-web/teamwork-agile-web-17-cach-phan-vai-khi-thuyet-trinh-san-pham.mp4', 600, 0, 'published', 2, '2026-06-27 08:00:00', '2026-06-27 08:00:00', NULL),
    (313, 72, 20, 'Cách chuẩn bị câu hỏi phản biện theo module', 'cach-chuan-bi-cau-hoi-phan-bien-theo-module', 'video', 'Nội dung video: Cách chuẩn bị câu hỏi phản biện theo module. File seed theo course_folder teamwork-agile-web.', '/videos/teamwork-agile-web/teamwork-agile-web-18-cach-chuan-bi-cau-hoi-phan-bien-theo-module.mp4', 600, 0, 'published', 3, '2026-06-27 08:00:00', '2026-06-27 08:00:00', NULL),
    (314, 72, 20, 'Cách xử lý sự cố khi demo bị lỗi', 'cach-xu-ly-su-co-khi-demo-bi-loi', 'video', 'Nội dung video: Cách xử lý sự cố khi demo bị lỗi. File seed theo course_folder teamwork-agile-web.', '/videos/teamwork-agile-web/teamwork-agile-web-19-cach-xu-ly-su-co-khi-demo-bi-loi.mp4', 600, 0, 'published', 4, '2026-06-27 08:00:00', '2026-06-27 08:00:00', NULL),
    (315, 72, 20, 'Tổng kết khóa học Teamwork Agile cho dự án Web', 'tong-ket-khoa-hoc-teamwork-agile-cho-du-an-web', 'video', 'Nội dung video: Tổng kết khóa học Teamwork Agile cho dự án Web. File seed theo course_folder teamwork-agile-web.', '/videos/teamwork-agile-web/teamwork-agile-web-20-tong-ket-khoa-hoc-teamwork-agile-cho-du-an-web.mp4', 600, 0, 'published', 5, '2026-06-27 08:00:00', '2026-06-27 08:00:00', NULL),
    (316, 73, 21, 'Vì sao cần biết trình bày project web?', 'vi-sao-can-biet-trinh-bay-project-web', 'video', 'Nội dung video: Vì sao cần biết trình bày project web?. File seed theo course_folder present-web-project.', '/videos/present-web-project/present-web-project-01-vi-sao-can-biet-trinh-bay-project-web.mp4', 600, 1, 'published', 1, '2026-06-27 08:00:00', '2026-06-27 08:00:00', NULL),
    (317, 73, 21, 'Cách giới thiệu project trong 60 giây', 'cach-gioi-thieu-project-trong-60-giay', 'video', 'Nội dung video: Cách giới thiệu project trong 60 giây. File seed theo course_folder present-web-project.', '/videos/present-web-project/present-web-project-02-cach-gioi-thieu-project-trong-60-giay.mp4', 600, 0, 'published', 2, '2026-06-27 08:00:00', '2026-06-27 08:00:00', NULL),
    (318, 73, 21, 'Cách nói rõ vấn đề sản phẩm giải quyết', 'cach-noi-ro-van-de-san-pham-giai-quyet', 'video', 'Nội dung video: Cách nói rõ vấn đề sản phẩm giải quyết. File seed theo course_folder present-web-project.', '/videos/present-web-project/present-web-project-03-cach-noi-ro-van-de-san-pham-giai-quyet.mp4', 600, 0, 'published', 3, '2026-06-27 08:00:00', '2026-06-27 08:00:00', NULL),
    (319, 73, 21, 'Cách trình bày vai trò cá nhân trong team', 'cach-trinh-bay-vai-tro-ca-nhan-trong-team', 'video', 'Nội dung video: Cách trình bày vai trò cá nhân trong team. File seed theo course_folder present-web-project.', '/videos/present-web-project/present-web-project-04-cach-trinh-bay-vai-tro-ca-nhan-trong-team.mp4', 600, 0, 'published', 4, '2026-06-27 08:00:00', '2026-06-27 08:00:00', NULL),
    (320, 73, 21, 'Cách tránh phóng đại phần mình không làm', 'cach-tranh-phong-dai-phan-minh-khong-lam', 'video', 'Nội dung video: Cách tránh phóng đại phần mình không làm. File seed theo course_folder present-web-project.', '/videos/present-web-project/present-web-project-05-cach-tranh-phong-dai-phan-minh-khong-lam.mp4', 600, 0, 'published', 5, '2026-06-27 08:00:00', '2026-06-27 08:00:00', NULL),
    (321, 74, 21, 'Cách trình bày kiến trúc React Laravel MySQL', 'cach-trinh-bay-kien-truc-react-laravel-mysql', 'video', 'Nội dung video: Cách trình bày kiến trúc React Laravel MySQL. File seed theo course_folder present-web-project.', '/videos/present-web-project/present-web-project-06-cach-trinh-bay-kien-truc-react-laravel-mysql.mp4', 600, 0, 'published', 1, '2026-06-27 08:00:00', '2026-06-27 08:00:00', NULL),
    (322, 74, 21, 'Cách giải thích API flow cho người phỏng vấn', 'cach-giai-thich-api-flow-cho-nguoi-phong-van', 'video', 'Nội dung video: Cách giải thích API flow cho người phỏng vấn. File seed theo course_folder present-web-project.', '/videos/present-web-project/present-web-project-07-cach-giai-thich-api-flow-cho-nguoi-phong-van.mp4', 600, 0, 'published', 2, '2026-06-27 08:00:00', '2026-06-27 08:00:00', NULL),
    (323, 74, 21, 'Cách nói về database và quan hệ bảng', 'cach-noi-ve-database-va-quan-he-bang', 'video', 'Nội dung video: Cách nói về database và quan hệ bảng. File seed theo course_folder present-web-project.', '/videos/present-web-project/present-web-project-08-cach-noi-ve-database-va-quan-he-bang.mp4', 600, 0, 'published', 3, '2026-06-27 08:00:00', '2026-06-27 08:00:00', NULL),
    (324, 74, 21, 'Cách trình bày module payment learning auth', 'cach-trinh-bay-module-payment-learning-auth', 'video', 'Nội dung video: Cách trình bày module payment learning auth. File seed theo course_folder present-web-project.', '/videos/present-web-project/present-web-project-09-cach-trinh-bay-module-payment-learning-auth.mp4', 600, 0, 'published', 4, '2026-06-27 08:00:00', '2026-06-27 08:00:00', NULL),
    (325, 74, 21, 'Cách nói về bảo mật và phân quyền trong project', 'cach-noi-ve-bao-mat-va-phan-quyen-trong-project', 'video', 'Nội dung video: Cách nói về bảo mật và phân quyền trong project. File seed theo course_folder present-web-project.', '/videos/present-web-project/present-web-project-10-cach-noi-ve-bao-mat-va-phan-quyen-trong-project.mp4', 600, 0, 'published', 5, '2026-06-27 08:00:00', '2026-06-27 08:00:00', NULL),
    (326, 75, 21, 'Cách chuẩn bị demo flow chính của web app', 'cach-chuan-bi-demo-flow-chinh-cua-web-app', 'video', 'Nội dung video: Cách chuẩn bị demo flow chính của web app. File seed theo course_folder present-web-project.', '/videos/present-web-project/present-web-project-11-cach-chuan-bi-demo-flow-chinh-cua-web-app.mp4', 600, 0, 'published', 1, '2026-06-27 08:00:00', '2026-06-27 08:00:00', NULL),
    (327, 75, 21, 'Cách xử lý khi demo bị lỗi nhẹ', 'cach-xu-ly-khi-demo-bi-loi-nhe', 'video', 'Nội dung video: Cách xử lý khi demo bị lỗi nhẹ. File seed theo course_folder present-web-project.', '/videos/present-web-project/present-web-project-12-cach-xu-ly-khi-demo-bi-loi-nhe.mp4', 600, 0, 'published', 2, '2026-06-27 08:00:00', '2026-06-27 08:00:00', NULL),
    (328, 75, 21, 'Cách dùng dữ liệu demo để kể câu chuyện sản phẩm', 'cach-dung-du-lieu-demo-de-ke-cau-chuyen-san-pham', 'video', 'Nội dung video: Cách dùng dữ liệu demo để kể câu chuyện sản phẩm. File seed theo course_folder present-web-project.', '/videos/present-web-project/present-web-project-13-cach-dung-du-lieu-demo-de-ke-cau-chuyen-san-pham.mp4', 600, 0, 'published', 3, '2026-06-27 08:00:00', '2026-06-27 08:00:00', NULL),
    (329, 75, 21, 'Cách trình bày GitHub README và tài liệu', 'cach-trinh-bay-github-readme-va-tai-lieu', 'video', 'Nội dung video: Cách trình bày GitHub README và tài liệu. File seed theo course_folder present-web-project.', '/videos/present-web-project/present-web-project-14-cach-trinh-bay-github-readme-va-tai-lieu.mp4', 600, 0, 'published', 4, '2026-06-27 08:00:00', '2026-06-27 08:00:00', NULL),
    (330, 75, 21, 'Cách kết nối project với vị trí ứng tuyển', 'cach-ket-noi-project-voi-vi-tri-ung-tuyen', 'video', 'Nội dung video: Cách kết nối project với vị trí ứng tuyển. File seed theo course_folder present-web-project.', '/videos/present-web-project/present-web-project-15-cach-ket-noi-project-voi-vi-tri-ung-tuyen.mp4', 600, 0, 'published', 5, '2026-06-27 08:00:00', '2026-06-27 08:00:00', NULL),
    (331, 76, 21, 'Câu hỏi thường gặp về đồ án web', 'cau-hoi-thuong-gap-ve-do-an-web', 'video', 'Nội dung video: Câu hỏi thường gặp về đồ án web. File seed theo course_folder present-web-project.', '/videos/present-web-project/present-web-project-16-cau-hoi-thuong-gap-ve-do-an-web.mp4', 600, 0, 'published', 1, '2026-06-27 08:00:00', '2026-06-27 08:00:00', NULL),
    (332, 76, 21, 'Cách trả lời câu hỏi vì sao chọn công nghệ này', 'cach-tra-loi-cau-hoi-vi-sao-chon-cong-nghe-nay', 'video', 'Nội dung video: Cách trả lời câu hỏi vì sao chọn công nghệ này. File seed theo course_folder present-web-project.', '/videos/present-web-project/present-web-project-17-cach-tra-loi-cau-hoi-vi-sao-chon-cong-nghe-nay.mp4', 600, 0, 'published', 2, '2026-06-27 08:00:00', '2026-06-27 08:00:00', NULL),
    (333, 76, 21, 'Cách trả lời khi bị hỏi sâu phần chưa rõ', 'cach-tra-loi-khi-bi-hoi-sau-phan-chua-ro', 'video', 'Nội dung video: Cách trả lời khi bị hỏi sâu phần chưa rõ. File seed theo course_folder present-web-project.', '/videos/present-web-project/present-web-project-18-cach-tra-loi-khi-bi-hoi-sau-phan-chua-ro.mp4', 600, 0, 'published', 3, '2026-06-27 08:00:00', '2026-06-27 08:00:00', NULL),
    (334, 76, 21, 'Checklist chuẩn bị trước buổi phỏng vấn', 'checklist-chuan-bi-truoc-buoi-phong-van', 'video', 'Nội dung video: Checklist chuẩn bị trước buổi phỏng vấn. File seed theo course_folder present-web-project.', '/videos/present-web-project/present-web-project-19-checklist-chuan-bi-truoc-buoi-phong-van.mp4', 600, 0, 'published', 4, '2026-06-27 08:00:00', '2026-06-27 08:00:00', NULL),
    (335, 76, 21, 'Tổng kết khóa học trình bày project Web', 'tong-ket-khoa-hoc-trinh-bay-project-web', 'video', 'Nội dung video: Tổng kết khóa học trình bày project Web. File seed theo course_folder present-web-project.', '/videos/present-web-project/present-web-project-20-tong-ket-khoa-hoc-trinh-bay-project-web.mp4', 600, 0, 'published', 5, '2026-06-27 08:00:00', '2026-06-27 08:00:00', NULL),
    (336, 77, 22, 'Vì sao không nên lao vào code khi chưa hiểu yêu cầu?', 'vi-sao-khong-nen-lao-vao-code-khi-chua-hieu-yeu-cau', 'video', 'Nội dung video: Vì sao không nên lao vào code khi chưa hiểu yêu cầu?. File seed theo course_folder problem-solving-webdev.', '/videos/problem-solving-webdev/problem-solving-webdev-01-vi-sao-khong-nen-lao-vao-code-khi-chua-hieu-yeu-cau.mp4', 600, 1, 'published', 1, '2026-06-27 08:00:00', '2026-06-27 08:00:00', NULL),
    (337, 77, 22, 'Cách bóc tách yêu cầu thành user flow', 'cach-boc-tach-yeu-cau-thanh-user-flow', 'video', 'Nội dung video: Cách bóc tách yêu cầu thành user flow. File seed theo course_folder problem-solving-webdev.', '/videos/problem-solving-webdev/problem-solving-webdev-02-cach-boc-tach-yeu-cau-thanh-user-flow.mp4', 600, 0, 'published', 2, '2026-06-27 08:00:00', '2026-06-27 08:00:00', NULL),
    (338, 77, 22, 'Cách đặt câu hỏi để làm rõ nghiệp vụ', 'cach-dat-cau-hoi-de-lam-ro-nghiep-vu', 'video', 'Nội dung video: Cách đặt câu hỏi để làm rõ nghiệp vụ. File seed theo course_folder problem-solving-webdev.', '/videos/problem-solving-webdev/problem-solving-webdev-03-cach-dat-cau-hoi-de-lam-ro-nghiep-vu.mp4', 600, 0, 'published', 3, '2026-06-27 08:00:00', '2026-06-27 08:00:00', NULL),
    (339, 77, 22, 'Cách xác định input output của một chức năng', 'cach-xac-dinh-input-output-cua-mot-chuc-nang', 'video', 'Nội dung video: Cách xác định input output của một chức năng. File seed theo course_folder problem-solving-webdev.', '/videos/problem-solving-webdev/problem-solving-webdev-04-cach-xac-dinh-input-output-cua-mot-chuc-nang.mp4', 600, 0, 'published', 4, '2026-06-27 08:00:00', '2026-06-27 08:00:00', NULL),
    (340, 77, 22, 'Cách viết checklist trước khi bắt đầu task', 'cach-viet-checklist-truoc-khi-bat-dau-task', 'video', 'Nội dung video: Cách viết checklist trước khi bắt đầu task. File seed theo course_folder problem-solving-webdev.', '/videos/problem-solving-webdev/problem-solving-webdev-05-cach-viet-checklist-truoc-khi-bat-dau-task.mp4', 600, 0, 'published', 5, '2026-06-27 08:00:00', '2026-06-27 08:00:00', NULL),
    (341, 78, 22, 'Debug là gì và vì sao người mới hay sửa mò?', 'debug-la-gi-va-vi-sao-nguoi-moi-hay-sua-mo', 'video', 'Nội dung video: Debug là gì và vì sao người mới hay sửa mò?. File seed theo course_folder problem-solving-webdev.', '/videos/problem-solving-webdev/problem-solving-webdev-06-debug-la-gi-va-vi-sao-nguoi-moi-hay-sua-mo.mp4', 600, 0, 'published', 1, '2026-06-27 08:00:00', '2026-06-27 08:00:00', NULL),
    (342, 78, 22, 'Cách tái hiện lỗi trước khi sửa', 'cach-tai-hien-loi-truoc-khi-sua', 'video', 'Nội dung video: Cách tái hiện lỗi trước khi sửa. File seed theo course_folder problem-solving-webdev.', '/videos/problem-solving-webdev/problem-solving-webdev-07-cach-tai-hien-loi-truoc-khi-sua.mp4', 600, 0, 'published', 2, '2026-06-27 08:00:00', '2026-06-27 08:00:00', NULL),
    (343, 78, 22, 'Cách đọc log và khoanh vùng nguyên nhân', 'cach-doc-log-va-khoanh-vung-nguyen-nhan', 'video', 'Nội dung video: Cách đọc log và khoanh vùng nguyên nhân. File seed theo course_folder problem-solving-webdev.', '/videos/problem-solving-webdev/problem-solving-webdev-08-cach-doc-log-va-khoanh-vung-nguyen-nhan.mp4', 600, 0, 'published', 3, '2026-06-27 08:00:00', '2026-06-27 08:00:00', NULL),
    (344, 78, 22, 'Cách phân biệt lỗi frontend backend database', 'cach-phan-biet-loi-frontend-backend-database', 'video', 'Nội dung video: Cách phân biệt lỗi frontend backend database. File seed theo course_folder problem-solving-webdev.', '/videos/problem-solving-webdev/problem-solving-webdev-09-cach-phan-biet-loi-frontend-backend-database.mp4', 600, 0, 'published', 4, '2026-06-27 08:00:00', '2026-06-27 08:00:00', NULL),
    (345, 78, 22, 'Cách ghi lại lỗi đã sửa để học nhanh hơn', 'cach-ghi-lai-loi-da-sua-de-hoc-nhanh-hon', 'video', 'Nội dung video: Cách ghi lại lỗi đã sửa để học nhanh hơn. File seed theo course_folder problem-solving-webdev.', '/videos/problem-solving-webdev/problem-solving-webdev-10-cach-ghi-lai-loi-da-sua-de-hoc-nhanh-hon.mp4', 600, 0, 'published', 5, '2026-06-27 08:00:00', '2026-06-27 08:00:00', NULL),
    (346, 79, 22, 'Cách chọn giải pháp đơn giản trước khi tối ưu', 'cach-chon-giai-phap-don-gian-truoc-khi-toi-uu', 'video', 'Nội dung video: Cách chọn giải pháp đơn giản trước khi tối ưu. File seed theo course_folder problem-solving-webdev.', '/videos/problem-solving-webdev/problem-solving-webdev-11-cach-chon-giai-phap-don-gian-truoc-khi-toi-uu.mp4', 600, 0, 'published', 1, '2026-06-27 08:00:00', '2026-06-27 08:00:00', NULL),
    (347, 79, 22, 'Cách so sánh nhiều hướng xử lý một API', 'cach-so-sanh-nhieu-huong-xu-ly-mot-api', 'video', 'Nội dung video: Cách so sánh nhiều hướng xử lý một API. File seed theo course_folder problem-solving-webdev.', '/videos/problem-solving-webdev/problem-solving-webdev-12-cach-so-sanh-nhieu-huong-xu-ly-mot-api.mp4', 600, 0, 'published', 2, '2026-06-27 08:00:00', '2026-06-27 08:00:00', NULL),
    (348, 79, 22, 'Cách nghĩ về edge case trong web app', 'cach-nghi-ve-edge-case-trong-web-app', 'video', 'Nội dung video: Cách nghĩ về edge case trong web app. File seed theo course_folder problem-solving-webdev.', '/videos/problem-solving-webdev/problem-solving-webdev-13-cach-nghi-ve-edge-case-trong-web-app.mp4', 600, 0, 'published', 3, '2026-06-27 08:00:00', '2026-06-27 08:00:00', NULL),
    (349, 79, 22, 'Cách dùng flowchart để mô tả nghiệp vụ', 'cach-dung-flowchart-de-mo-ta-nghiep-vu', 'video', 'Nội dung video: Cách dùng flowchart để mô tả nghiệp vụ. File seed theo course_folder problem-solving-webdev.', '/videos/problem-solving-webdev/problem-solving-webdev-14-cach-dung-flowchart-de-mo-ta-nghiep-vu.mp4', 600, 0, 'published', 4, '2026-06-27 08:00:00', '2026-06-27 08:00:00', NULL),
    (350, 79, 22, 'Cách tránh over-engineering trong đồ án', 'cach-tranh-over-engineering-trong-do-an', 'video', 'Nội dung video: Cách tránh over-engineering trong đồ án. File seed theo course_folder problem-solving-webdev.', '/videos/problem-solving-webdev/problem-solving-webdev-15-cach-tranh-over-engineering-trong-do-an.mp4', 600, 0, 'published', 5, '2026-06-27 08:00:00', '2026-06-27 08:00:00', NULL),
    (351, 80, 22, 'Cách trình bày suy nghĩ khi gặp câu hỏi khó', 'cach-trinh-bay-suy-nghi-khi-gap-cau-hoi-kho', 'video', 'Nội dung video: Cách trình bày suy nghĩ khi gặp câu hỏi khó. File seed theo course_folder problem-solving-webdev.', '/videos/problem-solving-webdev/problem-solving-webdev-16-cach-trinh-bay-suy-nghi-khi-gap-cau-hoi-kho.mp4', 600, 0, 'published', 1, '2026-06-27 08:00:00', '2026-06-27 08:00:00', NULL),
    (352, 80, 22, 'Cách nói về bug đã từng xử lý trong project', 'cach-noi-ve-bug-da-tung-xu-ly-trong-project', 'video', 'Nội dung video: Cách nói về bug đã từng xử lý trong project. File seed theo course_folder problem-solving-webdev.', '/videos/problem-solving-webdev/problem-solving-webdev-17-cach-noi-ve-bug-da-tung-xu-ly-trong-project.mp4', 600, 0, 'published', 2, '2026-06-27 08:00:00', '2026-06-27 08:00:00', NULL),
    (353, 80, 22, 'Cách giải thích quyết định kỹ thuật trong đồ án', 'cach-giai-thich-quyet-dinh-ky-thuat-trong-do-an', 'video', 'Nội dung video: Cách giải thích quyết định kỹ thuật trong đồ án. File seed theo course_folder problem-solving-webdev.', '/videos/problem-solving-webdev/problem-solving-webdev-18-cach-giai-thich-quyet-dinh-ky-thuat-trong-do-an.mp4', 600, 0, 'published', 3, '2026-06-27 08:00:00', '2026-06-27 08:00:00', NULL),
    (354, 80, 22, 'Checklist problem solving trước phỏng vấn', 'checklist-problem-solving-truoc-phong-van', 'video', 'Nội dung video: Checklist problem solving trước phỏng vấn. File seed theo course_folder problem-solving-webdev.', '/videos/problem-solving-webdev/problem-solving-webdev-19-checklist-problem-solving-truoc-phong-van.mp4', 600, 0, 'published', 4, '2026-06-27 08:00:00', '2026-06-27 08:00:00', NULL),
    (355, 80, 22, 'Tổng kết khóa học tư duy giải quyết vấn đề', 'tong-ket-khoa-hoc-tu-duy-giai-quyet-van-de', 'video', 'Nội dung video: Tổng kết khóa học tư duy giải quyết vấn đề. File seed theo course_folder problem-solving-webdev.', '/videos/problem-solving-webdev/problem-solving-webdev-20-tong-ket-khoa-hoc-tu-duy-giai-quyet-van-de.mp4', 600, 0, 'published', 5, '2026-06-27 08:00:00', '2026-06-27 08:00:00', NULL),
    (356, 81, 23, 'React là gì và vì sao dùng cho E-learning?', 'react-la-gi-va-vi-sao-dung-cho-e-learning', 'video', 'Nội dung video: React là gì và vì sao dùng cho E-learning?. File seed theo course_folder react-elearning.', '/videos/react-elearning/react-elearning-01-react-la-gi-va-vi-sao-dung-cho-e-learning.mp4', 600, 1, 'published', 1, '2026-06-27 08:00:00', '2026-06-27 08:00:00', NULL),
    (357, 81, 23, 'Cài đặt React Vite và cấu trúc project frontend', 'cai-dat-react-vite-va-cau-truc-project-frontend', 'video', 'Nội dung video: Cài đặt React Vite và cấu trúc project frontend. File seed theo course_folder react-elearning.', '/videos/react-elearning/react-elearning-02-cai-dat-react-vite-va-cau-truc-project-frontend.mp4', 600, 0, 'published', 2, '2026-06-27 08:00:00', '2026-06-27 08:00:00', NULL),
    (358, 81, 23, 'Component, props và state trong React', 'component-props-va-state-trong-react', 'video', 'Nội dung video: Component, props và state trong React. File seed theo course_folder react-elearning.', '/videos/react-elearning/react-elearning-03-component-props-va-state-trong-react.mp4', 600, 0, 'published', 3, '2026-06-27 08:00:00', '2026-06-27 08:00:00', NULL),
    (359, 81, 23, 'Tổ chức layout Header, Footer và App Shell', 'to-chuc-layout-header-footer-va-app-shell', 'video', 'Nội dung video: Tổ chức layout Header, Footer và App Shell. File seed theo course_folder react-elearning.', '/videos/react-elearning/react-elearning-04-to-chuc-layout-header-footer-va-app-shell.mp4', 600, 0, 'published', 4, '2026-06-27 08:00:00', '2026-06-27 08:00:00', NULL),
    (360, 81, 23, 'Kết nối React với Laravel API bằng environment', 'ket-noi-react-voi-laravel-api-bang-environment', 'video', 'Nội dung video: Kết nối React với Laravel API bằng environment. File seed theo course_folder react-elearning.', '/videos/react-elearning/react-elearning-05-ket-noi-react-voi-laravel-api-bang-environment.mp4', 600, 0, 'published', 5, '2026-06-27 08:00:00', '2026-06-27 08:00:00', NULL),
    (361, 82, 23, 'React Router cho trang Home, Course List và Course Detail', 'react-router-cho-trang-home-course-list-va-course-detail', 'video', 'Nội dung video: React Router cho trang Home, Course List và Course Detail. File seed theo course_folder react-elearning.', '/videos/react-elearning/react-elearning-06-react-router-cho-trang-home-course-list-va-course-detail.mp4', 600, 0, 'published', 1, '2026-06-27 08:00:00', '2026-06-27 08:00:00', NULL),
    (362, 82, 23, 'Thiết kế Course Card và Course Grid', 'thiet-ke-course-card-va-course-grid', 'video', 'Nội dung video: Thiết kế Course Card và Course Grid. File seed theo course_folder react-elearning.', '/videos/react-elearning/react-elearning-07-thiet-ke-course-card-va-course-grid.mp4', 600, 0, 'published', 2, '2026-06-27 08:00:00', '2026-06-27 08:00:00', NULL),
    (363, 82, 23, 'Gọi API danh sách khóa học và xử lý loading', 'goi-api-danh-sach-khoa-hoc-va-xu-ly-loading', 'video', 'Nội dung video: Gọi API danh sách khóa học và xử lý loading. File seed theo course_folder react-elearning.', '/videos/react-elearning/react-elearning-08-goi-api-danh-sach-khoa-hoc-va-xu-ly-loading.mp4', 600, 0, 'published', 3, '2026-06-27 08:00:00', '2026-06-27 08:00:00', NULL),
    (364, 82, 23, 'Xử lý search, filter và sort khóa học trên frontend', 'xu-ly-search-filter-va-sort-khoa-hoc-tren-frontend', 'video', 'Nội dung video: Xử lý search, filter và sort khóa học trên frontend. File seed theo course_folder react-elearning.', '/videos/react-elearning/react-elearning-09-xu-ly-search-filter-va-sort-khoa-hoc-tren-frontend.mp4', 600, 0, 'published', 4, '2026-06-27 08:00:00', '2026-06-27 08:00:00', NULL),
    (365, 82, 23, 'Hiển thị Course Detail với chương và bài học', 'hien-thi-course-detail-voi-chuong-va-bai-hoc', 'video', 'Nội dung video: Hiển thị Course Detail với chương và bài học. File seed theo course_folder react-elearning.', '/videos/react-elearning/react-elearning-10-hien-thi-course-detail-voi-chuong-va-bai-hoc.mp4', 600, 0, 'published', 5, '2026-06-27 08:00:00', '2026-06-27 08:00:00', NULL),
    (366, 83, 23, 'Form đăng nhập và đăng ký trong React', 'form-dang-nhap-va-dang-ky-trong-react', 'video', 'Nội dung video: Form đăng nhập và đăng ký trong React. File seed theo course_folder react-elearning.', '/videos/react-elearning/react-elearning-11-form-dang-nhap-va-dang-ky-trong-react.mp4', 600, 0, 'published', 1, '2026-06-27 08:00:00', '2026-06-27 08:00:00', NULL),
    (367, 83, 23, 'Lưu token và gửi Authorization header', 'luu-token-va-gui-authorization-header', 'video', 'Nội dung video: Lưu token và gửi Authorization header. File seed theo course_folder react-elearning.', '/videos/react-elearning/react-elearning-12-luu-token-va-gui-authorization-header.mp4', 600, 0, 'published', 2, '2026-06-27 08:00:00', '2026-06-27 08:00:00', NULL),
    (368, 83, 23, 'Protected Route cho learner và instructor', 'protected-route-cho-learner-va-instructor', 'video', 'Nội dung video: Protected Route cho learner và instructor. File seed theo course_folder react-elearning.', '/videos/react-elearning/react-elearning-13-protected-route-cho-learner-va-instructor.mp4', 600, 0, 'published', 3, '2026-06-27 08:00:00', '2026-06-27 08:00:00', NULL),
    (369, 83, 23, 'Xử lý trạng thái user sau khi refresh trang', 'xu-ly-trang-thai-user-sau-khi-refresh-trang', 'video', 'Nội dung video: Xử lý trạng thái user sau khi refresh trang. File seed theo course_folder react-elearning.', '/videos/react-elearning/react-elearning-14-xu-ly-trang-thai-user-sau-khi-refresh-trang.mp4', 600, 0, 'published', 4, '2026-06-27 08:00:00', '2026-06-27 08:00:00', NULL),
    (370, 83, 23, 'Logout và xử lý lỗi 401 403 trên frontend', 'logout-va-xu-ly-loi-401-403-tren-frontend', 'video', 'Nội dung video: Logout và xử lý lỗi 401 403 trên frontend. File seed theo course_folder react-elearning.', '/videos/react-elearning/react-elearning-15-logout-va-xu-ly-loi-401-403-tren-frontend.mp4', 600, 0, 'published', 5, '2026-06-27 08:00:00', '2026-06-27 08:00:00', NULL),
    (371, 84, 23, 'Xây trang học video cho learner', 'xay-trang-hoc-video-cho-learner', 'video', 'Nội dung video: Xây trang học video cho learner. File seed theo course_folder react-elearning.', '/videos/react-elearning/react-elearning-16-xay-trang-hoc-video-cho-learner.mp4', 600, 0, 'published', 1, '2026-06-27 08:00:00', '2026-06-27 08:00:00', NULL),
    (372, 84, 23, 'Hiển thị sidebar chương và danh sách bài học', 'hien-thi-sidebar-chuong-va-danh-sach-bai-hoc', 'video', 'Nội dung video: Hiển thị sidebar chương và danh sách bài học. File seed theo course_folder react-elearning.', '/videos/react-elearning/react-elearning-17-hien-thi-sidebar-chuong-va-danh-sach-bai-hoc.mp4', 600, 0, 'published', 2, '2026-06-27 08:00:00', '2026-06-27 08:00:00', NULL),
    (373, 84, 23, 'Phát video từ media domain trong React', 'phat-video-tu-media-domain-trong-react', 'video', 'Nội dung video: Phát video từ media domain trong React. File seed theo course_folder react-elearning.', '/videos/react-elearning/react-elearning-18-phat-video-tu-media-domain-trong-react.mp4', 600, 0, 'published', 3, '2026-06-27 08:00:00', '2026-06-27 08:00:00', NULL),
    (374, 84, 23, 'Lưu tiến độ học video và lesson progress', 'luu-tien-do-hoc-video-va-lesson-progress', 'video', 'Nội dung video: Lưu tiến độ học video và lesson progress. File seed theo course_folder react-elearning.', '/videos/react-elearning/react-elearning-19-luu-tien-do-hoc-video-va-lesson-progress.mp4', 600, 0, 'published', 4, '2026-06-27 08:00:00', '2026-06-27 08:00:00', NULL),
    (375, 84, 23, 'Giao diện ghi chú bài học trong màn học', 'giao-dien-ghi-chu-bai-hoc-trong-man-hoc', 'video', 'Nội dung video: Giao diện ghi chú bài học trong màn học. File seed theo course_folder react-elearning.', '/videos/react-elearning/react-elearning-20-giao-dien-ghi-chu-bai-hoc-trong-man-hoc.mp4', 600, 0, 'published', 5, '2026-06-27 08:00:00', '2026-06-27 08:00:00', NULL),
    (376, 85, 23, 'Quản lý state loading, error và empty state', 'quan-ly-state-loading-error-va-empty-state', 'video', 'Nội dung video: Quản lý state loading, error và empty state. File seed theo course_folder react-elearning.', '/videos/react-elearning/react-elearning-21-quan-ly-state-loading-error-va-empty-state.mp4', 600, 0, 'published', 1, '2026-06-27 08:00:00', '2026-06-27 08:00:00', NULL),
    (377, 85, 23, 'Validate form frontend nhưng không thay thế backend', 'validate-form-frontend-nhung-khong-thay-the-backend', 'video', 'Nội dung video: Validate form frontend nhưng không thay thế backend. File seed theo course_folder react-elearning.', '/videos/react-elearning/react-elearning-22-validate-form-frontend-nhung-khong-thay-the-backend.mp4', 600, 0, 'published', 2, '2026-06-27 08:00:00', '2026-06-27 08:00:00', NULL),
    (378, 85, 23, 'Tạo reusable component cho button, input và modal', 'tao-reusable-component-cho-button-input-va-modal', 'video', 'Nội dung video: Tạo reusable component cho button, input và modal. File seed theo course_folder react-elearning.', '/videos/react-elearning/react-elearning-23-tao-reusable-component-cho-button-input-va-modal.mp4', 600, 0, 'published', 3, '2026-06-27 08:00:00', '2026-06-27 08:00:00', NULL),
    (379, 85, 23, 'Tối ưu responsive cho dashboard và lesson page', 'toi-uu-responsive-cho-dashboard-va-lesson-page', 'video', 'Nội dung video: Tối ưu responsive cho dashboard và lesson page. File seed theo course_folder react-elearning.', '/videos/react-elearning/react-elearning-24-toi-uu-responsive-cho-dashboard-va-lesson-page.mp4', 600, 0, 'published', 4, '2026-06-27 08:00:00', '2026-06-27 08:00:00', NULL),
    (380, 85, 23, 'Xử lý thông báo toast và confirm dialog', 'xu-ly-thong-bao-toast-va-confirm-dialog', 'video', 'Nội dung video: Xử lý thông báo toast và confirm dialog. File seed theo course_folder react-elearning.', '/videos/react-elearning/react-elearning-25-xu-ly-thong-bao-toast-va-confirm-dialog.mp4', 600, 0, 'published', 5, '2026-06-27 08:00:00', '2026-06-27 08:00:00', NULL),
    (381, 86, 23, 'Kết nối flow xem khóa học, mua khóa và học bài', 'ket-noi-flow-xem-khoa-hoc-mua-khoa-va-hoc-bai', 'video', 'Nội dung video: Kết nối flow xem khóa học, mua khóa và học bài. File seed theo course_folder react-elearning.', '/videos/react-elearning/react-elearning-26-ket-noi-flow-xem-khoa-hoc-mua-khoa-va-hoc-bai.mp4', 600, 0, 'published', 1, '2026-06-27 08:00:00', '2026-06-27 08:00:00', NULL),
    (382, 86, 23, 'Tích hợp learning dashboard cho learner', 'tich-hop-learning-dashboard-cho-learner', 'video', 'Nội dung video: Tích hợp learning dashboard cho learner. File seed theo course_folder react-elearning.', '/videos/react-elearning/react-elearning-27-tich-hop-learning-dashboard-cho-learner.mp4', 600, 0, 'published', 2, '2026-06-27 08:00:00', '2026-06-27 08:00:00', NULL),
    (383, 86, 23, 'Tối ưu build React trước khi deploy', 'toi-uu-build-react-truoc-khi-deploy', 'video', 'Nội dung video: Tối ưu build React trước khi deploy. File seed theo course_folder react-elearning.', '/videos/react-elearning/react-elearning-28-toi-uu-build-react-truoc-khi-deploy.mp4', 600, 0, 'published', 3, '2026-06-27 08:00:00', '2026-06-27 08:00:00', NULL),
    (384, 86, 23, 'Deploy React frontend lên aaPanel', 'deploy-react-frontend-len-aapanel', 'video', 'Nội dung video: Deploy React frontend lên aaPanel. File seed theo course_folder react-elearning.', '/videos/react-elearning/react-elearning-29-deploy-react-frontend-len-aapanel.mp4', 600, 0, 'published', 4, '2026-06-27 08:00:00', '2026-06-27 08:00:00', NULL),
    (385, 86, 23, 'Tổng kết khóa học React E-learning', 'tong-ket-khoa-hoc-react-e-learning', 'video', 'Nội dung video: Tổng kết khóa học React E-learning. File seed theo course_folder react-elearning.', '/videos/react-elearning/react-elearning-30-tong-ket-khoa-hoc-react-e-learning.mp4', 600, 0, 'published', 5, '2026-06-27 08:00:00', '2026-06-27 08:00:00', NULL),
    (386, 87, 24, 'Landing page là gì và khác homepage thế nào?', 'landing-page-la-gi-va-khac-homepage-the-nao', 'video', 'Nội dung video: Landing page là gì và khác homepage thế nào?. File seed theo course_folder landing-page-conversion.', '/videos/landing-page-conversion/landing-page-conversion-01-landing-page-la-gi-va-khac-homepage-the-nao.mp4', 600, 1, 'published', 1, '2026-06-27 08:00:00', '2026-06-27 08:00:00', NULL),
    (387, 87, 24, 'Conversion là gì trong website sản phẩm?', 'conversion-la-gi-trong-website-san-pham', 'video', 'Nội dung video: Conversion là gì trong website sản phẩm?. File seed theo course_folder landing-page-conversion.', '/videos/landing-page-conversion/landing-page-conversion-02-conversion-la-gi-trong-website-san-pham.mp4', 600, 0, 'published', 2, '2026-06-27 08:00:00', '2026-06-27 08:00:00', NULL),
    (388, 87, 24, 'Cách xác định mục tiêu landing page', 'cach-xac-dinh-muc-tieu-landing-page', 'video', 'Nội dung video: Cách xác định mục tiêu landing page. File seed theo course_folder landing-page-conversion.', '/videos/landing-page-conversion/landing-page-conversion-03-cach-xac-dinh-muc-tieu-landing-page.mp4', 600, 0, 'published', 3, '2026-06-27 08:00:00', '2026-06-27 08:00:00', NULL),
    (389, 87, 24, 'Cách hiểu khách hàng trước khi thiết kế', 'cach-hieu-khach-hang-truoc-khi-thiet-ke', 'video', 'Nội dung video: Cách hiểu khách hàng trước khi thiết kế. File seed theo course_folder landing-page-conversion.', '/videos/landing-page-conversion/landing-page-conversion-04-cach-hieu-khach-hang-truoc-khi-thiet-ke.mp4', 600, 0, 'published', 4, '2026-06-27 08:00:00', '2026-06-27 08:00:00', NULL),
    (390, 87, 24, 'Những lỗi landing page khiến người dùng thoát nhanh', 'nhung-loi-landing-page-khien-nguoi-dung-thoat-nhanh', 'video', 'Nội dung video: Những lỗi landing page khiến người dùng thoát nhanh. File seed theo course_folder landing-page-conversion.', '/videos/landing-page-conversion/landing-page-conversion-05-nhung-loi-landing-page-khien-nguoi-dung-thoat-nhanh.mp4', 600, 0, 'published', 5, '2026-06-27 08:00:00', '2026-06-27 08:00:00', NULL),
    (391, 88, 24, 'Hero section cần có những gì?', 'hero-section-can-co-nhung-gi', 'video', 'Nội dung video: Hero section cần có những gì?. File seed theo course_folder landing-page-conversion.', '/videos/landing-page-conversion/landing-page-conversion-06-hero-section-can-co-nhung-gi.mp4', 600, 0, 'published', 1, '2026-06-27 08:00:00', '2026-06-27 08:00:00', NULL),
    (392, 88, 24, 'Cách trình bày problem solution rõ ràng', 'cach-trinh-bay-problem-solution-ro-rang', 'video', 'Nội dung video: Cách trình bày problem solution rõ ràng. File seed theo course_folder landing-page-conversion.', '/videos/landing-page-conversion/landing-page-conversion-07-cach-trinh-bay-problem-solution-ro-rang.mp4', 600, 0, 'published', 2, '2026-06-27 08:00:00', '2026-06-27 08:00:00', NULL),
    (393, 88, 24, 'Cách viết benefits thay vì chỉ liệt kê features', 'cach-viet-benefits-thay-vi-chi-liet-ke-features', 'video', 'Nội dung video: Cách viết benefits thay vì chỉ liệt kê features. File seed theo course_folder landing-page-conversion.', '/videos/landing-page-conversion/landing-page-conversion-08-cach-viet-benefits-thay-vi-chi-liet-ke-features.mp4', 600, 0, 'published', 3, '2026-06-27 08:00:00', '2026-06-27 08:00:00', NULL),
    (394, 88, 24, 'Cách dùng social proof testimonial case study', 'cach-dung-social-proof-testimonial-case-study', 'video', 'Nội dung video: Cách dùng social proof testimonial case study. File seed theo course_folder landing-page-conversion.', '/videos/landing-page-conversion/landing-page-conversion-09-cach-dung-social-proof-testimonial-case-study.mp4', 600, 0, 'published', 4, '2026-06-27 08:00:00', '2026-06-27 08:00:00', NULL),
    (395, 88, 24, 'Cách đặt CTA theo từng giai đoạn đọc trang', 'cach-dat-cta-theo-tung-giai-doan-doc-trang', 'video', 'Nội dung video: Cách đặt CTA theo từng giai đoạn đọc trang. File seed theo course_folder landing-page-conversion.', '/videos/landing-page-conversion/landing-page-conversion-10-cach-dat-cta-theo-tung-giai-doan-doc-trang.mp4', 600, 0, 'published', 5, '2026-06-27 08:00:00', '2026-06-27 08:00:00', NULL),
    (396, 89, 24, 'Cách dùng visual hierarchy để dẫn mắt người dùng', 'cach-dung-visual-hierarchy-de-dan-mat-nguoi-dung', 'video', 'Nội dung video: Cách dùng visual hierarchy để dẫn mắt người dùng. File seed theo course_folder landing-page-conversion.', '/videos/landing-page-conversion/landing-page-conversion-11-cach-dung-visual-hierarchy-de-dan-mat-nguoi-dung.mp4', 600, 0, 'published', 1, '2026-06-27 08:00:00', '2026-06-27 08:00:00', NULL),
    (397, 89, 24, 'Cách phối màu typography và khoảng trắng', 'cach-phoi-mau-typography-va-khoang-trang', 'video', 'Nội dung video: Cách phối màu typography và khoảng trắng. File seed theo course_folder landing-page-conversion.', '/videos/landing-page-conversion/landing-page-conversion-12-cach-phoi-mau-typography-va-khoang-trang.mp4', 600, 0, 'published', 2, '2026-06-27 08:00:00', '2026-06-27 08:00:00', NULL),
    (398, 89, 24, 'Cách viết headline subheadline dễ hiểu', 'cach-viet-headline-subheadline-de-hieu', 'video', 'Nội dung video: Cách viết headline subheadline dễ hiểu. File seed theo course_folder landing-page-conversion.', '/videos/landing-page-conversion/landing-page-conversion-13-cach-viet-headline-subheadline-de-hieu.mp4', 600, 0, 'published', 3, '2026-06-27 08:00:00', '2026-06-27 08:00:00', NULL),
    (399, 89, 24, 'Cách thiết kế form liên hệ không gây ngại', 'cach-thiet-ke-form-lien-he-khong-gay-ngai', 'video', 'Nội dung video: Cách thiết kế form liên hệ không gây ngại. File seed theo course_folder landing-page-conversion.', '/videos/landing-page-conversion/landing-page-conversion-14-cach-thiet-ke-form-lien-he-khong-gay-ngai.mp4', 600, 0, 'published', 4, '2026-06-27 08:00:00', '2026-06-27 08:00:00', NULL),
    (400, 89, 24, 'Cách tối ưu landing page trên mobile', 'cach-toi-uu-landing-page-tren-mobile', 'video', 'Nội dung video: Cách tối ưu landing page trên mobile. File seed theo course_folder landing-page-conversion.', '/videos/landing-page-conversion/landing-page-conversion-15-cach-toi-uu-landing-page-tren-mobile.mp4', 600, 0, 'published', 5, '2026-06-27 08:00:00', '2026-06-27 08:00:00', NULL),
    (401, 90, 24, 'Các chỉ số cần theo dõi trên landing page', 'cac-chi-so-can-theo-doi-tren-landing-page', 'video', 'Nội dung video: Các chỉ số cần theo dõi trên landing page. File seed theo course_folder landing-page-conversion.', '/videos/landing-page-conversion/landing-page-conversion-16-cac-chi-so-can-theo-doi-tren-landing-page.mp4', 600, 0, 'published', 1, '2026-06-27 08:00:00', '2026-06-27 08:00:00', NULL),
    (402, 90, 24, 'A/B testing cơ bản cho headline và CTA', 'a-b-testing-co-ban-cho-headline-va-cta', 'video', 'Nội dung video: A/B testing cơ bản cho headline và CTA. File seed theo course_folder landing-page-conversion.', '/videos/landing-page-conversion/landing-page-conversion-17-a-b-testing-co-ban-cho-headline-va-cta.mp4', 600, 0, 'published', 2, '2026-06-27 08:00:00', '2026-06-27 08:00:00', NULL),
    (403, 90, 24, 'Cách dùng heatmap và analytics ở mức nhập môn', 'cach-dung-heatmap-va-analytics-o-muc-nhap-mon', 'video', 'Nội dung video: Cách dùng heatmap và analytics ở mức nhập môn. File seed theo course_folder landing-page-conversion.', '/videos/landing-page-conversion/landing-page-conversion-18-cach-dung-heatmap-va-analytics-o-muc-nhap-mon.mp4', 600, 0, 'published', 3, '2026-06-27 08:00:00', '2026-06-27 08:00:00', NULL),
    (404, 90, 24, 'Checklist landing page trước khi chạy quảng cáo', 'checklist-landing-page-truoc-khi-chay-quang-cao', 'video', 'Nội dung video: Checklist landing page trước khi chạy quảng cáo. File seed theo course_folder landing-page-conversion.', '/videos/landing-page-conversion/landing-page-conversion-19-checklist-landing-page-truoc-khi-chay-quang-cao.mp4', 600, 0, 'published', 4, '2026-06-27 08:00:00', '2026-06-27 08:00:00', NULL),
    (405, 90, 24, 'Tổng kết khóa học Landing Page chuyển đổi cao', 'tong-ket-khoa-hoc-landing-page-chuyen-doi-cao', 'video', 'Nội dung video: Tổng kết khóa học Landing Page chuyển đổi cao. File seed theo course_folder landing-page-conversion.', '/videos/landing-page-conversion/landing-page-conversion-20-tong-ket-khoa-hoc-landing-page-chuyen-doi-cao.mp4', 600, 0, 'published', 5, '2026-06-27 08:00:00', '2026-06-27 08:00:00', NULL),
    (406, 91, 25, 'Web Analytics là gì và vì sao Web Developer nên biết?', 'web-analytics-la-gi-va-vi-sao-web-developer-nen-biet', 'video', 'Nội dung video: Web Analytics là gì và vì sao Web Developer nên biết?. File seed theo course_folder web-analytics-ab-testing.', '/videos/web-analytics-ab-testing/web-analytics-ab-testing-01-web-analytics-la-gi-va-vi-sao-web-developer-nen-biet.mp4', 600, 1, 'published', 1, '2026-06-27 08:00:00', '2026-06-27 08:00:00', NULL),
    (407, 91, 25, 'Page view session user conversion là gì?', 'page-view-session-user-conversion-la-gi', 'video', 'Nội dung video: Page view session user conversion là gì?. File seed theo course_folder web-analytics-ab-testing.', '/videos/web-analytics-ab-testing/web-analytics-ab-testing-02-page-view-session-user-conversion-la-gi.mp4', 600, 0, 'published', 2, '2026-06-27 08:00:00', '2026-06-27 08:00:00', NULL),
    (408, 91, 25, 'Event tracking dùng để theo dõi hành động nào?', 'event-tracking-dung-de-theo-doi-hanh-dong-nao', 'video', 'Nội dung video: Event tracking dùng để theo dõi hành động nào?. File seed theo course_folder web-analytics-ab-testing.', '/videos/web-analytics-ab-testing/web-analytics-ab-testing-03-event-tracking-dung-de-theo-doi-hanh-dong-nao.mp4', 600, 0, 'published', 3, '2026-06-27 08:00:00', '2026-06-27 08:00:00', NULL),
    (409, 91, 25, 'Funnel cơ bản trong website E-learning', 'funnel-co-ban-trong-website-e-learning', 'video', 'Nội dung video: Funnel cơ bản trong website E-learning. File seed theo course_folder web-analytics-ab-testing.', '/videos/web-analytics-ab-testing/web-analytics-ab-testing-04-funnel-co-ban-trong-website-e-learning.mp4', 600, 0, 'published', 4, '2026-06-27 08:00:00', '2026-06-27 08:00:00', NULL),
    (410, 91, 25, 'Những chỉ số không nên hiểu sai khi mới bắt đầu', 'nhung-chi-so-khong-nen-hieu-sai-khi-moi-bat-dau', 'video', 'Nội dung video: Những chỉ số không nên hiểu sai khi mới bắt đầu. File seed theo course_folder web-analytics-ab-testing.', '/videos/web-analytics-ab-testing/web-analytics-ab-testing-05-nhung-chi-so-khong-nen-hieu-sai-khi-moi-bat-dau.mp4', 600, 0, 'published', 5, '2026-06-27 08:00:00', '2026-06-27 08:00:00', NULL),
    (411, 92, 25, 'Theo dõi click CTA trên landing page', 'theo-doi-click-cta-tren-landing-page', 'video', 'Nội dung video: Theo dõi click CTA trên landing page. File seed theo course_folder web-analytics-ab-testing.', '/videos/web-analytics-ab-testing/web-analytics-ab-testing-06-theo-doi-click-cta-tren-landing-page.mp4', 600, 0, 'published', 1, '2026-06-27 08:00:00', '2026-06-27 08:00:00', NULL),
    (412, 92, 25, 'Theo dõi form submit và lỗi form', 'theo-doi-form-submit-va-loi-form', 'video', 'Nội dung video: Theo dõi form submit và lỗi form. File seed theo course_folder web-analytics-ab-testing.', '/videos/web-analytics-ab-testing/web-analytics-ab-testing-07-theo-doi-form-submit-va-loi-form.mp4', 600, 0, 'published', 2, '2026-06-27 08:00:00', '2026-06-27 08:00:00', NULL),
    (413, 92, 25, 'Theo dõi người dùng xem course detail', 'theo-doi-nguoi-dung-xem-course-detail', 'video', 'Nội dung video: Theo dõi người dùng xem course detail. File seed theo course_folder web-analytics-ab-testing.', '/videos/web-analytics-ab-testing/web-analytics-ab-testing-08-theo-doi-nguoi-dung-xem-course-detail.mp4', 600, 0, 'published', 3, '2026-06-27 08:00:00', '2026-06-27 08:00:00', NULL),
    (414, 92, 25, 'Theo dõi video lesson progress ở mức sản phẩm', 'theo-doi-video-lesson-progress-o-muc-san-pham', 'video', 'Nội dung video: Theo dõi video lesson progress ở mức sản phẩm. File seed theo course_folder web-analytics-ab-testing.', '/videos/web-analytics-ab-testing/web-analytics-ab-testing-09-theo-doi-video-lesson-progress-o-muc-san-pham.mp4', 600, 0, 'published', 4, '2026-06-27 08:00:00', '2026-06-27 08:00:00', NULL),
    (415, 92, 25, 'Theo dõi drop-off trong flow checkout', 'theo-doi-drop-off-trong-flow-checkout', 'video', 'Nội dung video: Theo dõi drop-off trong flow checkout. File seed theo course_folder web-analytics-ab-testing.', '/videos/web-analytics-ab-testing/web-analytics-ab-testing-10-theo-doi-drop-off-trong-flow-checkout.mp4', 600, 0, 'published', 5, '2026-06-27 08:00:00', '2026-06-27 08:00:00', NULL),
    (416, 93, 25, 'A/B Testing là gì và dùng khi nào?', 'a-b-testing-la-gi-va-dung-khi-nao', 'video', 'Nội dung video: A/B Testing là gì và dùng khi nào?. File seed theo course_folder web-analytics-ab-testing.', '/videos/web-analytics-ab-testing/web-analytics-ab-testing-11-a-b-testing-la-gi-va-dung-khi-nao.mp4', 600, 0, 'published', 1, '2026-06-27 08:00:00', '2026-06-27 08:00:00', NULL),
    (417, 93, 25, 'Chọn một giả thuyết test rõ ràng', 'chon-mot-gia-thuyet-test-ro-rang', 'video', 'Nội dung video: Chọn một giả thuyết test rõ ràng. File seed theo course_folder web-analytics-ab-testing.', '/videos/web-analytics-ab-testing/web-analytics-ab-testing-12-chon-mot-gia-thuyet-test-ro-rang.mp4', 600, 0, 'published', 2, '2026-06-27 08:00:00', '2026-06-27 08:00:00', NULL),
    (418, 93, 25, 'Test headline CTA layout và form', 'test-headline-cta-layout-va-form', 'video', 'Nội dung video: Test headline CTA layout và form. File seed theo course_folder web-analytics-ab-testing.', '/videos/web-analytics-ab-testing/web-analytics-ab-testing-13-test-headline-cta-layout-va-form.mp4', 600, 0, 'published', 3, '2026-06-27 08:00:00', '2026-06-27 08:00:00', NULL),
    (419, 93, 25, 'Những lỗi A/B Testing người mới hay mắc', 'nhung-loi-a-b-testing-nguoi-moi-hay-mac', 'video', 'Nội dung video: Những lỗi A/B Testing người mới hay mắc. File seed theo course_folder web-analytics-ab-testing.', '/videos/web-analytics-ab-testing/web-analytics-ab-testing-14-nhung-loi-a-b-testing-nguoi-moi-hay-mac.mp4', 600, 0, 'published', 4, '2026-06-27 08:00:00', '2026-06-27 08:00:00', NULL),
    (420, 93, 25, 'Cách đọc kết quả test một cách thận trọng', 'cach-doc-ket-qua-test-mot-cach-than-trong', 'video', 'Nội dung video: Cách đọc kết quả test một cách thận trọng. File seed theo course_folder web-analytics-ab-testing.', '/videos/web-analytics-ab-testing/web-analytics-ab-testing-15-cach-doc-ket-qua-test-mot-cach-than-trong.mp4', 600, 0, 'published', 5, '2026-06-27 08:00:00', '2026-06-27 08:00:00', NULL),
    (421, 94, 25, 'Thiết kế event naming dễ hiểu cho dev team', 'thiet-ke-event-naming-de-hieu-cho-dev-team', 'video', 'Nội dung video: Thiết kế event naming dễ hiểu cho dev team. File seed theo course_folder web-analytics-ab-testing.', '/videos/web-analytics-ab-testing/web-analytics-ab-testing-16-thiet-ke-event-naming-de-hieu-cho-dev-team.mp4', 600, 0, 'published', 1, '2026-06-27 08:00:00', '2026-06-27 08:00:00', NULL),
    (422, 94, 25, 'Kết hợp analytics với UX improvement', 'ket-hop-analytics-voi-ux-improvement', 'video', 'Nội dung video: Kết hợp analytics với UX improvement. File seed theo course_folder web-analytics-ab-testing.', '/videos/web-analytics-ab-testing/web-analytics-ab-testing-17-ket-hop-analytics-voi-ux-improvement.mp4', 600, 0, 'published', 2, '2026-06-27 08:00:00', '2026-06-27 08:00:00', NULL),
    (423, 94, 25, 'Checklist tracking trước khi deploy landing page', 'checklist-tracking-truoc-khi-deploy-landing-page', 'video', 'Nội dung video: Checklist tracking trước khi deploy landing page. File seed theo course_folder web-analytics-ab-testing.', '/videos/web-analytics-ab-testing/web-analytics-ab-testing-18-checklist-tracking-truoc-khi-deploy-landing-page.mp4', 600, 0, 'published', 3, '2026-06-27 08:00:00', '2026-06-27 08:00:00', NULL),
    (424, 94, 25, 'Cách trình bày insight analytics trong portfolio', 'cach-trinh-bay-insight-analytics-trong-portfolio', 'video', 'Nội dung video: Cách trình bày insight analytics trong portfolio. File seed theo course_folder web-analytics-ab-testing.', '/videos/web-analytics-ab-testing/web-analytics-ab-testing-19-cach-trinh-bay-insight-analytics-trong-portfolio.mp4', 600, 0, 'published', 4, '2026-06-27 08:00:00', '2026-06-27 08:00:00', NULL),
    (425, 94, 25, 'Tổng kết khóa học Web Analytics và A/B Testing', 'tong-ket-khoa-hoc-web-analytics-va-a-b-testing', 'video', 'Nội dung video: Tổng kết khóa học Web Analytics và A/B Testing. File seed theo course_folder web-analytics-ab-testing.', '/videos/web-analytics-ab-testing/web-analytics-ab-testing-20-tong-ket-khoa-hoc-web-analytics-va-a-b-testing.mp4', 600, 0, 'published', 5, '2026-06-27 08:00:00', '2026-06-27 08:00:00', NULL),
    (426, 95, 26, 'Content marketing là gì trong website công nghệ?', 'content-marketing-la-gi-trong-website-cong-nghe', 'video', 'Nội dung video: Content marketing là gì trong website công nghệ?. File seed theo course_folder content-marketing-web.', '/videos/content-marketing-web/content-marketing-web-01-content-marketing-la-gi-trong-website-cong-nghe.mp4', 600, 1, 'published', 1, '2026-06-27 08:00:00', '2026-06-27 08:00:00', NULL),
    (427, 95, 26, 'Vì sao lập trình viên web nên hiểu content?', 'vi-sao-lap-trinh-vien-web-nen-hieu-content', 'video', 'Nội dung video: Vì sao lập trình viên web nên hiểu content?. File seed theo course_folder content-marketing-web.', '/videos/content-marketing-web/content-marketing-web-02-vi-sao-lap-trinh-vien-web-nen-hieu-content.mp4', 600, 0, 'published', 2, '2026-06-27 08:00:00', '2026-06-27 08:00:00', NULL),
    (428, 95, 26, 'Phân biệt landing page blog post và product page', 'phan-biet-landing-page-blog-post-va-product-page', 'video', 'Nội dung video: Phân biệt landing page blog post và product page. File seed theo course_folder content-marketing-web.', '/videos/content-marketing-web/content-marketing-web-03-phan-biet-landing-page-blog-post-va-product-page.mp4', 600, 0, 'published', 3, '2026-06-27 08:00:00', '2026-06-27 08:00:00', NULL),
    (429, 95, 26, 'Cách xác định người đọc trước khi viết nội dung', 'cach-xac-dinh-nguoi-doc-truoc-khi-viet-noi-dung', 'video', 'Nội dung video: Cách xác định người đọc trước khi viết nội dung. File seed theo course_folder content-marketing-web.', '/videos/content-marketing-web/content-marketing-web-04-cach-xac-dinh-nguoi-doc-truoc-khi-viet-noi-dung.mp4', 600, 0, 'published', 4, '2026-06-27 08:00:00', '2026-06-27 08:00:00', NULL),
    (430, 95, 26, 'Cách biến tính năng kỹ thuật thành lợi ích người dùng', 'cach-bien-tinh-nang-ky-thuat-thanh-loi-ich-nguoi-dung', 'video', 'Nội dung video: Cách biến tính năng kỹ thuật thành lợi ích người dùng. File seed theo course_folder content-marketing-web.', '/videos/content-marketing-web/content-marketing-web-05-cach-bien-tinh-nang-ky-thuat-thanh-loi-ich-nguoi-dung.mp4', 600, 0, 'published', 5, '2026-06-27 08:00:00', '2026-06-27 08:00:00', NULL),
    (431, 96, 26, 'Cách viết hero section rõ giá trị', 'cach-viet-hero-section-ro-gia-tri', 'video', 'Nội dung video: Cách viết hero section rõ giá trị. File seed theo course_folder content-marketing-web.', '/videos/content-marketing-web/content-marketing-web-06-cach-viet-hero-section-ro-gia-tri.mp4', 600, 0, 'published', 1, '2026-06-27 08:00:00', '2026-06-27 08:00:00', NULL),
    (432, 96, 26, 'Cách viết section tính năng không sáo rỗng', 'cach-viet-section-tinh-nang-khong-sao-rong', 'video', 'Nội dung video: Cách viết section tính năng không sáo rỗng. File seed theo course_folder content-marketing-web.', '/videos/content-marketing-web/content-marketing-web-07-cach-viet-section-tinh-nang-khong-sao-rong.mp4', 600, 0, 'published', 2, '2026-06-27 08:00:00', '2026-06-27 08:00:00', NULL),
    (433, 96, 26, 'Cách viết CTA tự nhiên và thuyết phục', 'cach-viet-cta-tu-nhien-va-thuyet-phuc', 'video', 'Nội dung video: Cách viết CTA tự nhiên và thuyết phục. File seed theo course_folder content-marketing-web.', '/videos/content-marketing-web/content-marketing-web-08-cach-viet-cta-tu-nhien-va-thuyet-phuc.mp4', 600, 0, 'published', 3, '2026-06-27 08:00:00', '2026-06-27 08:00:00', NULL),
    (434, 96, 26, 'Cách viết FAQ thật hơn cho landing page', 'cach-viet-faq-that-hon-cho-landing-page', 'video', 'Nội dung video: Cách viết FAQ thật hơn cho landing page. File seed theo course_folder content-marketing-web.', '/videos/content-marketing-web/content-marketing-web-09-cach-viet-faq-that-hon-cho-landing-page.mp4', 600, 0, 'published', 4, '2026-06-27 08:00:00', '2026-06-27 08:00:00', NULL),
    (435, 96, 26, 'Cách viết footer và thông tin doanh nghiệp tĩnh', 'cach-viet-footer-va-thong-tin-doanh-nghiep-tinh', 'video', 'Nội dung video: Cách viết footer và thông tin doanh nghiệp tĩnh. File seed theo course_folder content-marketing-web.', '/videos/content-marketing-web/content-marketing-web-10-cach-viet-footer-va-thong-tin-doanh-nghiep-tinh.mp4', 600, 0, 'published', 5, '2026-06-27 08:00:00', '2026-06-27 08:00:00', NULL),
    (436, 97, 26, 'Cách chọn chủ đề blog cho website E-learning', 'cach-chon-chu-de-blog-cho-website-e-learning', 'video', 'Nội dung video: Cách chọn chủ đề blog cho website E-learning. File seed theo course_folder content-marketing-web.', '/videos/content-marketing-web/content-marketing-web-11-cach-chon-chu-de-blog-cho-website-e-learning.mp4', 600, 0, 'published', 1, '2026-06-27 08:00:00', '2026-06-27 08:00:00', NULL),
    (437, 97, 26, 'Cách viết outline bài blog công nghệ', 'cach-viet-outline-bai-blog-cong-nghe', 'video', 'Nội dung video: Cách viết outline bài blog công nghệ. File seed theo course_folder content-marketing-web.', '/videos/content-marketing-web/content-marketing-web-12-cach-viet-outline-bai-blog-cong-nghe.mp4', 600, 0, 'published', 2, '2026-06-27 08:00:00', '2026-06-27 08:00:00', NULL),
    (438, 97, 26, 'Cách dùng ví dụ code mà người mới hiểu được', 'cach-dung-vi-du-code-ma-nguoi-moi-hieu-duoc', 'video', 'Nội dung video: Cách dùng ví dụ code mà người mới hiểu được. File seed theo course_folder content-marketing-web.', '/videos/content-marketing-web/content-marketing-web-13-cach-dung-vi-du-code-ma-nguoi-moi-hieu-duoc.mp4', 600, 0, 'published', 3, '2026-06-27 08:00:00', '2026-06-27 08:00:00', NULL),
    (439, 97, 26, 'Cách viết tutorial có bước rõ ràng', 'cach-viet-tutorial-co-buoc-ro-rang', 'video', 'Nội dung video: Cách viết tutorial có bước rõ ràng. File seed theo course_folder content-marketing-web.', '/videos/content-marketing-web/content-marketing-web-14-cach-viet-tutorial-co-buoc-ro-rang.mp4', 600, 0, 'published', 4, '2026-06-27 08:00:00', '2026-06-27 08:00:00', NULL),
    (440, 97, 26, 'Cách tái sử dụng content cho README và portfolio', 'cach-tai-su-dung-content-cho-readme-va-portfolio', 'video', 'Nội dung video: Cách tái sử dụng content cho README và portfolio. File seed theo course_folder content-marketing-web.', '/videos/content-marketing-web/content-marketing-web-15-cach-tai-su-dung-content-cho-readme-va-portfolio.mp4', 600, 0, 'published', 5, '2026-06-27 08:00:00', '2026-06-27 08:00:00', NULL),
    (441, 98, 26, 'Cách kiểm tra nội dung có khớp giao diện không', 'cach-kiem-tra-noi-dung-co-khop-giao-dien-khong', 'video', 'Nội dung video: Cách kiểm tra nội dung có khớp giao diện không. File seed theo course_folder content-marketing-web.', '/videos/content-marketing-web/content-marketing-web-16-cach-kiem-tra-noi-dung-co-khop-giao-dien-khong.mp4', 600, 0, 'published', 1, '2026-06-27 08:00:00', '2026-06-27 08:00:00', NULL),
    (442, 98, 26, 'Cách phối hợp content với UI UX', 'cach-phoi-hop-content-voi-ui-ux', 'video', 'Nội dung video: Cách phối hợp content với UI UX. File seed theo course_folder content-marketing-web.', '/videos/content-marketing-web/content-marketing-web-17-cach-phoi-hop-content-voi-ui-ux.mp4', 600, 0, 'published', 2, '2026-06-27 08:00:00', '2026-06-27 08:00:00', NULL),
    (443, 98, 26, 'Cách dùng AI hỗ trợ viết content nhưng không copy mù', 'cach-dung-ai-ho-tro-viet-content-nhung-khong-copy-mu', 'video', 'Nội dung video: Cách dùng AI hỗ trợ viết content nhưng không copy mù. File seed theo course_folder content-marketing-web.', '/videos/content-marketing-web/content-marketing-web-18-cach-dung-ai-ho-tro-viet-content-nhung-khong-copy-mu.mp4', 600, 0, 'published', 3, '2026-06-27 08:00:00', '2026-06-27 08:00:00', NULL),
    (444, 98, 26, 'Checklist content trước khi đưa website lên production', 'checklist-content-truoc-khi-dua-website-len-production', 'video', 'Nội dung video: Checklist content trước khi đưa website lên production. File seed theo course_folder content-marketing-web.', '/videos/content-marketing-web/content-marketing-web-19-checklist-content-truoc-khi-dua-website-len-production.mp4', 600, 0, 'published', 4, '2026-06-27 08:00:00', '2026-06-27 08:00:00', NULL),
    (445, 98, 26, 'Tổng kết khóa học Content Marketing cho Web', 'tong-ket-khoa-hoc-content-marketing-cho-web', 'video', 'Nội dung video: Tổng kết khóa học Content Marketing cho Web. File seed theo course_folder content-marketing-web.', '/videos/content-marketing-web/content-marketing-web-20-tong-ket-khoa-hoc-content-marketing-cho-web.mp4', 600, 0, 'published', 5, '2026-06-27 08:00:00', '2026-06-27 08:00:00', NULL),
    (446, 99, 27, 'SEO là gì và vì sao Web Developer nên biết?', 'seo-la-gi-va-vi-sao-web-developer-nen-biet', 'video', 'Nội dung video: SEO là gì và vì sao Web Developer nên biết?. File seed theo course_folder seo-for-webdev.', '/videos/seo-for-webdev/seo-for-webdev-01-seo-la-gi-va-vi-sao-web-developer-nen-biet.mp4', 600, 1, 'published', 1, '2026-06-27 08:00:00', '2026-06-27 08:00:00', NULL),
    (447, 99, 27, 'Google nhìn một trang web như thế nào?', 'google-nhin-mot-trang-web-nhu-the-nao', 'video', 'Nội dung video: Google nhìn một trang web như thế nào?. File seed theo course_folder seo-for-webdev.', '/videos/seo-for-webdev/seo-for-webdev-02-google-nhin-mot-trang-web-nhu-the-nao.mp4', 600, 0, 'published', 2, '2026-06-27 08:00:00', '2026-06-27 08:00:00', NULL),
    (448, 99, 27, 'Từ khóa search intent và nội dung hữu ích', 'tu-khoa-search-intent-va-noi-dung-huu-ich', 'video', 'Nội dung video: Từ khóa search intent và nội dung hữu ích. File seed theo course_folder seo-for-webdev.', '/videos/seo-for-webdev/seo-for-webdev-03-tu-khoa-search-intent-va-noi-dung-huu-ich.mp4', 600, 0, 'published', 3, '2026-06-27 08:00:00', '2026-06-27 08:00:00', NULL),
    (449, 99, 27, 'URL slug title meta description là gì?', 'url-slug-title-meta-description-la-gi', 'video', 'Nội dung video: URL slug title meta description là gì?. File seed theo course_folder seo-for-webdev.', '/videos/seo-for-webdev/seo-for-webdev-04-url-slug-title-meta-description-la-gi.mp4', 600, 0, 'published', 4, '2026-06-27 08:00:00', '2026-06-27 08:00:00', NULL),
    (450, 99, 27, 'Những hiểu lầm phổ biến về SEO kỹ thuật', 'nhung-hieu-lam-pho-bien-ve-seo-ky-thuat', 'video', 'Nội dung video: Những hiểu lầm phổ biến về SEO kỹ thuật. File seed theo course_folder seo-for-webdev.', '/videos/seo-for-webdev/seo-for-webdev-05-nhung-hieu-lam-pho-bien-ve-seo-ky-thuat.mp4', 600, 0, 'published', 5, '2026-06-27 08:00:00', '2026-06-27 08:00:00', NULL),
    (451, 100, 27, 'Cấu trúc heading H1 H2 H3 cho trang khóa học', 'cau-truc-heading-h1-h2-h3-cho-trang-khoa-hoc', 'video', 'Nội dung video: Cấu trúc heading H1 H2 H3 cho trang khóa học. File seed theo course_folder seo-for-webdev.', '/videos/seo-for-webdev/seo-for-webdev-06-cau-truc-heading-h1-h2-h3-cho-trang-khoa-hoc.mp4', 600, 0, 'published', 1, '2026-06-27 08:00:00', '2026-06-27 08:00:00', NULL),
    (452, 100, 27, 'Internal link và breadcrumb trong website E-learning', 'internal-link-va-breadcrumb-trong-website-e-learning', 'video', 'Nội dung video: Internal link và breadcrumb trong website E-learning. File seed theo course_folder seo-for-webdev.', '/videos/seo-for-webdev/seo-for-webdev-07-internal-link-va-breadcrumb-trong-website-e-learning.mp4', 600, 0, 'published', 2, '2026-06-27 08:00:00', '2026-06-27 08:00:00', NULL),
    (453, 100, 27, 'Tối ưu ảnh thumbnail alt text và file name', 'toi-uu-anh-thumbnail-alt-text-va-file-name', 'video', 'Nội dung video: Tối ưu ảnh thumbnail alt text và file name. File seed theo course_folder seo-for-webdev.', '/videos/seo-for-webdev/seo-for-webdev-08-toi-uu-anh-thumbnail-alt-text-va-file-name.mp4', 600, 0, 'published', 3, '2026-06-27 08:00:00', '2026-06-27 08:00:00', NULL),
    (454, 100, 27, 'Sitemap robots txt và canonical cơ bản', 'sitemap-robots-txt-va-canonical-co-ban', 'video', 'Nội dung video: Sitemap robots txt và canonical cơ bản. File seed theo course_folder seo-for-webdev.', '/videos/seo-for-webdev/seo-for-webdev-09-sitemap-robots-txt-va-canonical-co-ban.mp4', 600, 0, 'published', 4, '2026-06-27 08:00:00', '2026-06-27 08:00:00', NULL),
    (455, 100, 27, 'Tốc độ tải trang ảnh hưởng SEO ra sao?', 'toc-do-tai-trang-anh-huong-seo-ra-sao', 'video', 'Nội dung video: Tốc độ tải trang ảnh hưởng SEO ra sao?. File seed theo course_folder seo-for-webdev.', '/videos/seo-for-webdev/seo-for-webdev-10-toc-do-tai-trang-anh-huong-seo-ra-sao.mp4', 600, 0, 'published', 5, '2026-06-27 08:00:00', '2026-06-27 08:00:00', NULL),
    (456, 101, 27, 'Cách viết title SEO cho trang khóa học', 'cach-viet-title-seo-cho-trang-khoa-hoc', 'video', 'Nội dung video: Cách viết title SEO cho trang khóa học. File seed theo course_folder seo-for-webdev.', '/videos/seo-for-webdev/seo-for-webdev-11-cach-viet-title-seo-cho-trang-khoa-hoc.mp4', 600, 0, 'published', 1, '2026-06-27 08:00:00', '2026-06-27 08:00:00', NULL),
    (457, 101, 27, 'Cách viết mô tả khóa học có ích cho người học', 'cach-viet-mo-ta-khoa-hoc-co-ich-cho-nguoi-hoc', 'video', 'Nội dung video: Cách viết mô tả khóa học có ích cho người học. File seed theo course_folder seo-for-webdev.', '/videos/seo-for-webdev/seo-for-webdev-12-cach-viet-mo-ta-khoa-hoc-co-ich-cho-nguoi-hoc.mp4', 600, 0, 'published', 2, '2026-06-27 08:00:00', '2026-06-27 08:00:00', NULL),
    (458, 101, 27, 'Cách tổ chức category course lesson thân thiện SEO', 'cach-to-chuc-category-course-lesson-than-thien-seo', 'video', 'Nội dung video: Cách tổ chức category course lesson thân thiện SEO. File seed theo course_folder seo-for-webdev.', '/videos/seo-for-webdev/seo-for-webdev-13-cach-to-chuc-category-course-lesson-than-thien-seo.mp4', 600, 0, 'published', 3, '2026-06-27 08:00:00', '2026-06-27 08:00:00', NULL),
    (459, 101, 27, 'Schema cơ bản cho Course và FAQ', 'schema-co-ban-cho-course-va-faq', 'video', 'Nội dung video: Schema cơ bản cho Course và FAQ. File seed theo course_folder seo-for-webdev.', '/videos/seo-for-webdev/seo-for-webdev-14-schema-co-ban-cho-course-va-faq.mp4', 600, 0, 'published', 4, '2026-06-27 08:00:00', '2026-06-27 08:00:00', NULL),
    (460, 101, 27, 'Cách tránh duplicate content trong web khóa học', 'cach-tranh-duplicate-content-trong-web-khoa-hoc', 'video', 'Nội dung video: Cách tránh duplicate content trong web khóa học. File seed theo course_folder seo-for-webdev.', '/videos/seo-for-webdev/seo-for-webdev-15-cach-tranh-duplicate-content-trong-web-khoa-hoc.mp4', 600, 0, 'published', 5, '2026-06-27 08:00:00', '2026-06-27 08:00:00', NULL),
    (461, 102, 27, 'Google Search Console dùng để làm gì?', 'google-search-console-dung-de-lam-gi', 'video', 'Nội dung video: Google Search Console dùng để làm gì?. File seed theo course_folder seo-for-webdev.', '/videos/seo-for-webdev/seo-for-webdev-16-google-search-console-dung-de-lam-gi.mp4', 600, 0, 'published', 1, '2026-06-27 08:00:00', '2026-06-27 08:00:00', NULL),
    (462, 102, 27, 'Chỉ số impression click CTR position là gì?', 'chi-so-impression-click-ctr-position-la-gi', 'video', 'Nội dung video: Chỉ số impression click CTR position là gì?. File seed theo course_folder seo-for-webdev.', '/videos/seo-for-webdev/seo-for-webdev-17-chi-so-impression-click-ctr-position-la-gi.mp4', 600, 0, 'published', 2, '2026-06-27 08:00:00', '2026-06-27 08:00:00', NULL),
    (463, 102, 27, 'Cách kiểm tra index cơ bản cho website mới', 'cach-kiem-tra-index-co-ban-cho-website-moi', 'video', 'Nội dung video: Cách kiểm tra index cơ bản cho website mới. File seed theo course_folder seo-for-webdev.', '/videos/seo-for-webdev/seo-for-webdev-18-cach-kiem-tra-index-co-ban-cho-website-moi.mp4', 600, 0, 'published', 3, '2026-06-27 08:00:00', '2026-06-27 08:00:00', NULL),
    (464, 102, 27, 'Checklist SEO trước khi public landing page', 'checklist-seo-truoc-khi-public-landing-page', 'video', 'Nội dung video: Checklist SEO trước khi public landing page. File seed theo course_folder seo-for-webdev.', '/videos/seo-for-webdev/seo-for-webdev-19-checklist-seo-truoc-khi-public-landing-page.mp4', 600, 0, 'published', 4, '2026-06-27 08:00:00', '2026-06-27 08:00:00', NULL),
    (465, 102, 27, 'Tổng kết khóa học SEO cơ bản cho Web Developer', 'tong-ket-khoa-hoc-seo-co-ban-cho-web-developer', 'video', 'Nội dung video: Tổng kết khóa học SEO cơ bản cho Web Developer. File seed theo course_folder seo-for-webdev.', '/videos/seo-for-webdev/seo-for-webdev-20-tong-ket-khoa-hoc-seo-co-ban-cho-web-developer.mp4', 600, 0, 'published', 5, '2026-06-27 08:00:00', '2026-06-27 08:00:00', NULL),
    (466, 103, 28, 'Payment flow trong E-learning hoạt động thế nào?', 'payment-flow-trong-e-learning-hoat-dong-the-nao', 'video', 'Nội dung video: Payment flow trong E-learning hoạt động thế nào?. File seed theo course_folder vnpay-laravel-payment.', '/videos/vnpay-laravel-payment/vnpay-laravel-payment-01-payment-flow-trong-e-learning-hoat-dong-the-nao.mp4', 600, 1, 'published', 1, '2026-06-27 08:00:00', '2026-06-27 08:00:00', NULL),
    (467, 103, 28, 'Order payment_status và enrollment khác nhau ra sao?', 'order-payment-status-va-enrollment-khac-nhau-ra-sao', 'video', 'Nội dung video: Order payment_status và enrollment khác nhau ra sao?. File seed theo course_folder vnpay-laravel-payment.', '/videos/vnpay-laravel-payment/vnpay-laravel-payment-02-order-payment-status-va-enrollment-khac-nhau-ra-sao.mp4', 600, 0, 'published', 2, '2026-06-27 08:00:00', '2026-06-27 08:00:00', NULL),
    (468, 103, 28, 'VNPay sandbox là gì?', 'vnpay-sandbox-la-gi', 'video', 'Nội dung video: VNPay sandbox là gì?. File seed theo course_folder vnpay-laravel-payment.', '/videos/vnpay-laravel-payment/vnpay-laravel-payment-03-vnpay-sandbox-la-gi.mp4', 600, 0, 'published', 3, '2026-06-27 08:00:00', '2026-06-27 08:00:00', NULL),
    (469, 103, 28, 'Return URL và IPN URL khác nhau thế nào?', 'return-url-va-ipn-url-khac-nhau-the-nao', 'video', 'Nội dung video: Return URL và IPN URL khác nhau thế nào?. File seed theo course_folder vnpay-laravel-payment.', '/videos/vnpay-laravel-payment/vnpay-laravel-payment-04-return-url-va-ipn-url-khac-nhau-the-nao.mp4', 600, 0, 'published', 4, '2026-06-27 08:00:00', '2026-06-27 08:00:00', NULL),
    (470, 103, 28, 'Những rủi ro khi xử lý thanh toán online', 'nhung-rui-ro-khi-xu-ly-thanh-toan-online', 'video', 'Nội dung video: Những rủi ro khi xử lý thanh toán online. File seed theo course_folder vnpay-laravel-payment.', '/videos/vnpay-laravel-payment/vnpay-laravel-payment-05-nhung-rui-ro-khi-xu-ly-thanh-toan-online.mp4', 600, 0, 'published', 5, '2026-06-27 08:00:00', '2026-06-27 08:00:00', NULL),
    (471, 104, 28, 'Các biến cấu hình VNPay cần có', 'cac-bien-cau-hinh-vnpay-can-co', 'video', 'Nội dung video: Các biến cấu hình VNPay cần có. File seed theo course_folder vnpay-laravel-payment.', '/videos/vnpay-laravel-payment/vnpay-laravel-payment-06-cac-bien-cau-hinh-vnpay-can-co.mp4', 600, 0, 'published', 1, '2026-06-27 08:00:00', '2026-06-27 08:00:00', NULL),
    (472, 104, 28, 'Lưu TMN Code và Hash Secret an toàn', 'luu-tmn-code-va-hash-secret-an-toan', 'video', 'Nội dung video: Lưu TMN Code và Hash Secret an toàn. File seed theo course_folder vnpay-laravel-payment.', '/videos/vnpay-laravel-payment/vnpay-laravel-payment-07-luu-tmn-code-va-hash-secret-an-toan.mp4', 600, 0, 'published', 2, '2026-06-27 08:00:00', '2026-06-27 08:00:00', NULL),
    (473, 104, 28, 'Tạo config vnpay trong Laravel', 'tao-config-vnpay-trong-laravel', 'video', 'Nội dung video: Tạo config vnpay trong Laravel. File seed theo course_folder vnpay-laravel-payment.', '/videos/vnpay-laravel-payment/vnpay-laravel-payment-08-tao-config-vnpay-trong-laravel.mp4', 600, 0, 'published', 3, '2026-06-27 08:00:00', '2026-06-27 08:00:00', NULL),
    (474, 104, 28, 'Thiết kế bảng orders cho thanh toán khóa học', 'thiet-ke-bang-orders-cho-thanh-toan-khoa-hoc', 'video', 'Nội dung video: Thiết kế bảng orders cho thanh toán khóa học. File seed theo course_folder vnpay-laravel-payment.', '/videos/vnpay-laravel-payment/vnpay-laravel-payment-09-thiet-ke-bang-orders-cho-thanh-toan-khoa-hoc.mp4', 600, 0, 'published', 4, '2026-06-27 08:00:00', '2026-06-27 08:00:00', NULL),
    (475, 104, 28, 'Tạo order pending trước khi sang VNPay', 'tao-order-pending-truoc-khi-sang-vnpay', 'video', 'Nội dung video: Tạo order pending trước khi sang VNPay. File seed theo course_folder vnpay-laravel-payment.', '/videos/vnpay-laravel-payment/vnpay-laravel-payment-10-tao-order-pending-truoc-khi-sang-vnpay.mp4', 600, 0, 'published', 5, '2026-06-27 08:00:00', '2026-06-27 08:00:00', NULL),
    (476, 105, 28, 'Tạo payment URL VNPay từ order', 'tao-payment-url-vnpay-tu-order', 'video', 'Nội dung video: Tạo payment URL VNPay từ order. File seed theo course_folder vnpay-laravel-payment.', '/videos/vnpay-laravel-payment/vnpay-laravel-payment-11-tao-payment-url-vnpay-tu-order.mp4', 600, 0, 'published', 1, '2026-06-27 08:00:00', '2026-06-27 08:00:00', NULL),
    (477, 105, 28, 'Build query params cho VNPay đúng cách', 'build-query-params-cho-vnpay-dung-cach', 'video', 'Nội dung video: Build query params cho VNPay đúng cách. File seed theo course_folder vnpay-laravel-payment.', '/videos/vnpay-laravel-payment/vnpay-laravel-payment-12-build-query-params-cho-vnpay-dung-cach.mp4', 600, 0, 'published', 2, '2026-06-27 08:00:00', '2026-06-27 08:00:00', NULL),
    (478, 105, 28, 'Tạo secure hash và kiểm tra chữ ký', 'tao-secure-hash-va-kiem-tra-chu-ky', 'video', 'Nội dung video: Tạo secure hash và kiểm tra chữ ký. File seed theo course_folder vnpay-laravel-payment.', '/videos/vnpay-laravel-payment/vnpay-laravel-payment-13-tao-secure-hash-va-kiem-tra-chu-ky.mp4', 600, 0, 'published', 3, '2026-06-27 08:00:00', '2026-06-27 08:00:00', NULL),
    (479, 105, 28, 'Frontend nhận payment_url và redirect người dùng', 'frontend-nhan-payment-url-va-redirect-nguoi-dung', 'video', 'Nội dung video: Frontend nhận payment_url và redirect người dùng. File seed theo course_folder vnpay-laravel-payment.', '/videos/vnpay-laravel-payment/vnpay-laravel-payment-14-frontend-nhan-payment-url-va-redirect-nguoi-dung.mp4', 600, 0, 'published', 4, '2026-06-27 08:00:00', '2026-06-27 08:00:00', NULL),
    (480, 105, 28, 'Test tạo thanh toán bằng Postman', 'test-tao-thanh-toan-bang-postman', 'video', 'Nội dung video: Test tạo thanh toán bằng Postman. File seed theo course_folder vnpay-laravel-payment.', '/videos/vnpay-laravel-payment/vnpay-laravel-payment-15-test-tao-thanh-toan-bang-postman.mp4', 600, 0, 'published', 5, '2026-06-27 08:00:00', '2026-06-27 08:00:00', NULL),
    (481, 106, 28, 'Xử lý VNPay return URL sau khi người dùng thanh toán', 'xu-ly-vnpay-return-url-sau-khi-nguoi-dung-thanh-toan', 'video', 'Nội dung video: Xử lý VNPay return URL sau khi người dùng thanh toán. File seed theo course_folder vnpay-laravel-payment.', '/videos/vnpay-laravel-payment/vnpay-laravel-payment-16-xu-ly-vnpay-return-url-sau-khi-nguoi-dung-thanh-toan.mp4', 600, 0, 'published', 1, '2026-06-27 08:00:00', '2026-06-27 08:00:00', NULL),
    (482, 106, 28, 'Xử lý IPN để cập nhật đơn hàng đáng tin cậy hơn', 'xu-ly-ipn-de-cap-nhat-don-hang-dang-tin-cay-hon', 'video', 'Nội dung video: Xử lý IPN để cập nhật đơn hàng đáng tin cậy hơn. File seed theo course_folder vnpay-laravel-payment.', '/videos/vnpay-laravel-payment/vnpay-laravel-payment-17-xu-ly-ipn-de-cap-nhat-don-hang-dang-tin-cay-hon.mp4', 600, 0, 'published', 2, '2026-06-27 08:00:00', '2026-06-27 08:00:00', NULL),
    (483, 106, 28, 'Kiểm tra amount transaction no và response code', 'kiem-tra-amount-transaction-no-va-response-code', 'video', 'Nội dung video: Kiểm tra amount transaction no và response code. File seed theo course_folder vnpay-laravel-payment.', '/videos/vnpay-laravel-payment/vnpay-laravel-payment-18-kiem-tra-amount-transaction-no-va-response-code.mp4', 600, 0, 'published', 3, '2026-06-27 08:00:00', '2026-06-27 08:00:00', NULL),
    (484, 106, 28, 'Tránh cập nhật paid nhiều lần cho cùng order', 'tranh-cap-nhat-paid-nhieu-lan-cho-cung-order', 'video', 'Nội dung video: Tránh cập nhật paid nhiều lần cho cùng order. File seed theo course_folder vnpay-laravel-payment.', '/videos/vnpay-laravel-payment/vnpay-laravel-payment-19-tranh-cap-nhat-paid-nhieu-lan-cho-cung-order.mp4', 600, 0, 'published', 4, '2026-06-27 08:00:00', '2026-06-27 08:00:00', NULL),
    (485, 106, 28, 'Ghi log thanh toán để debug khi lỗi', 'ghi-log-thanh-toan-de-debug-khi-loi', 'video', 'Nội dung video: Ghi log thanh toán để debug khi lỗi. File seed theo course_folder vnpay-laravel-payment.', '/videos/vnpay-laravel-payment/vnpay-laravel-payment-20-ghi-log-thanh-toan-de-debug-khi-loi.mp4', 600, 0, 'published', 5, '2026-06-27 08:00:00', '2026-06-27 08:00:00', NULL),
    (486, 107, 28, 'Tạo enrollment sau khi order paid', 'tao-enrollment-sau-khi-order-paid', 'video', 'Nội dung video: Tạo enrollment sau khi order paid. File seed theo course_folder vnpay-laravel-payment.', '/videos/vnpay-laravel-payment/vnpay-laravel-payment-21-tao-enrollment-sau-khi-order-paid.mp4', 600, 0, 'published', 1, '2026-06-27 08:00:00', '2026-06-27 08:00:00', NULL),
    (487, 107, 28, 'Tính platform fee và instructor revenue', 'tinh-platform-fee-va-instructor-revenue', 'video', 'Nội dung video: Tính platform fee và instructor revenue. File seed theo course_folder vnpay-laravel-payment.', '/videos/vnpay-laravel-payment/vnpay-laravel-payment-22-tinh-platform-fee-va-instructor-revenue.mp4', 600, 0, 'published', 2, '2026-06-27 08:00:00', '2026-06-27 08:00:00', NULL),
    (488, 107, 28, 'Dùng DB transaction khi cập nhật payment', 'dung-db-transaction-khi-cap-nhat-payment', 'video', 'Nội dung video: Dùng DB transaction khi cập nhật payment. File seed theo course_folder vnpay-laravel-payment.', '/videos/vnpay-laravel-payment/vnpay-laravel-payment-23-dung-db-transaction-khi-cap-nhat-payment.mp4', 600, 0, 'published', 3, '2026-06-27 08:00:00', '2026-06-27 08:00:00', NULL),
    (489, 107, 28, 'Xử lý đơn hàng failed cancelled expired', 'xu-ly-don-hang-failed-cancelled-expired', 'video', 'Nội dung video: Xử lý đơn hàng failed cancelled expired. File seed theo course_folder vnpay-laravel-payment.', '/videos/vnpay-laravel-payment/vnpay-laravel-payment-24-xu-ly-don-hang-failed-cancelled-expired.mp4', 600, 0, 'published', 4, '2026-06-27 08:00:00', '2026-06-27 08:00:00', NULL),
    (490, 107, 28, 'Test case lỗi khi payment callback sai dữ liệu', 'test-case-loi-khi-payment-callback-sai-du-lieu', 'video', 'Nội dung video: Test case lỗi khi payment callback sai dữ liệu. File seed theo course_folder vnpay-laravel-payment.', '/videos/vnpay-laravel-payment/vnpay-laravel-payment-25-test-case-loi-khi-payment-callback-sai-du-lieu.mp4', 600, 0, 'published', 5, '2026-06-27 08:00:00', '2026-06-27 08:00:00', NULL),
    (491, 108, 28, 'Test flow mua khóa học từ frontend đến backend', 'test-flow-mua-khoa-hoc-tu-frontend-den-backend', 'video', 'Nội dung video: Test flow mua khóa học từ frontend đến backend. File seed theo course_folder vnpay-laravel-payment.', '/videos/vnpay-laravel-payment/vnpay-laravel-payment-26-test-flow-mua-khoa-hoc-tu-frontend-den-backend.mp4', 600, 0, 'published', 1, '2026-06-27 08:00:00', '2026-06-27 08:00:00', NULL),
    (492, 108, 28, 'Test retry payment cho order chưa thanh toán', 'test-retry-payment-cho-order-chua-thanh-toan', 'video', 'Nội dung video: Test retry payment cho order chưa thanh toán. File seed theo course_folder vnpay-laravel-payment.', '/videos/vnpay-laravel-payment/vnpay-laravel-payment-27-test-retry-payment-cho-order-chua-thanh-toan.mp4', 600, 0, 'published', 2, '2026-06-27 08:00:00', '2026-06-27 08:00:00', NULL),
    (493, 108, 28, 'Test cancel order chưa thanh toán', 'test-cancel-order-chua-thanh-toan', 'video', 'Nội dung video: Test cancel order chưa thanh toán. File seed theo course_folder vnpay-laravel-payment.', '/videos/vnpay-laravel-payment/vnpay-laravel-payment-28-test-cancel-order-chua-thanh-toan.mp4', 600, 0, 'published', 3, '2026-06-27 08:00:00', '2026-06-27 08:00:00', NULL),
    (494, 108, 28, 'Checklist bảo mật payment trước khi demo', 'checklist-bao-mat-payment-truoc-khi-demo', 'video', 'Nội dung video: Checklist bảo mật payment trước khi demo. File seed theo course_folder vnpay-laravel-payment.', '/videos/vnpay-laravel-payment/vnpay-laravel-payment-29-checklist-bao-mat-payment-truoc-khi-demo.mp4', 600, 0, 'published', 4, '2026-06-27 08:00:00', '2026-06-27 08:00:00', NULL),
    (495, 108, 28, 'Tổng kết khóa học VNPay Laravel Payment', 'tong-ket-khoa-hoc-vnpay-laravel-payment', 'video', 'Nội dung video: Tổng kết khóa học VNPay Laravel Payment. File seed theo course_folder vnpay-laravel-payment.', '/videos/vnpay-laravel-payment/vnpay-laravel-payment-30-tong-ket-khoa-hoc-vnpay-laravel-payment.mp4', 600, 0, 'published', 5, '2026-06-27 08:00:00', '2026-06-27 08:00:00', NULL),
    (496, 109, 29, 'Web Developer cần học gì để đi thực tập?', 'web-developer-can-hoc-gi-de-di-thuc-tap', 'video', 'Nội dung video: Web Developer cần học gì để đi thực tập?. File seed theo course_folder career-webdev.', '/videos/career-webdev/career-webdev-01-web-developer-can-hoc-gi-de-di-thuc-tap.mp4', 600, 1, 'published', 1, '2026-06-27 08:00:00', '2026-06-27 08:00:00', NULL),
    (497, 109, 29, 'Frontend, Backend, Full-stack khác nhau thế nào?', 'frontend-backend-full-stack-khac-nhau-the-nao', 'video', 'Nội dung video: Frontend, Backend, Full-stack khác nhau thế nào?. File seed theo course_folder career-webdev.', '/videos/career-webdev/career-webdev-02-frontend-backend-full-stack-khac-nhau-the-nao.mp4', 600, 0, 'published', 2, '2026-06-27 08:00:00', '2026-06-27 08:00:00', NULL),
    (498, 109, 29, 'Sinh viên IT nên chọn hướng nào trong năm cuối?', 'sinh-vien-it-nen-chon-huong-nao-trong-nam-cuoi', 'video', 'Nội dung video: Sinh viên IT nên chọn hướng nào trong năm cuối?. File seed theo course_folder career-webdev.', '/videos/career-webdev/career-webdev-03-sinh-vien-it-nen-chon-huong-nao-trong-nam-cuoi.mp4', 600, 0, 'published', 3, '2026-06-27 08:00:00', '2026-06-27 08:00:00', NULL),
    (499, 109, 29, 'Fresher, Intern, Junior khác nhau ra sao?', 'fresher-intern-junior-khac-nhau-ra-sao', 'video', 'Nội dung video: Fresher, Intern, Junior khác nhau ra sao?. File seed theo course_folder career-webdev.', '/videos/career-webdev/career-webdev-04-fresher-intern-junior-khac-nhau-ra-sao.mp4', 600, 0, 'published', 4, '2026-06-27 08:00:00', '2026-06-27 08:00:00', NULL),
    (500, 109, 29, 'Nhà tuyển dụng cần gì ở sinh viên IT?', 'nha-tuyen-dung-can-gi-o-sinh-vien-it', 'video', 'Nội dung video: Nhà tuyển dụng cần gì ở sinh viên IT?. File seed theo course_folder career-webdev.', '/videos/career-webdev/career-webdev-05-nha-tuyen-dung-can-gi-o-sinh-vien-it.mp4', 600, 0, 'published', 5, '2026-06-27 08:00:00', '2026-06-27 08:00:00', NULL),
    (501, 110, 29, 'Khi nào nên học Frontend trước?', 'khi-nao-nen-hoc-frontend-truoc', 'video', 'Nội dung video: Khi nào nên học Frontend trước?. File seed theo course_folder career-webdev.', '/videos/career-webdev/career-webdev-06-khi-nao-nen-hoc-frontend-truoc.mp4', 600, 0, 'published', 1, '2026-06-27 08:00:00', '2026-06-27 08:00:00', NULL),
    (502, 110, 29, 'Khi nào nên học Backend trước?', 'khi-nao-nen-hoc-backend-truoc', 'video', 'Nội dung video: Khi nào nên học Backend trước?. File seed theo course_folder career-webdev.', '/videos/career-webdev/career-webdev-07-khi-nao-nen-hoc-backend-truoc.mp4', 600, 0, 'published', 2, '2026-06-27 08:00:00', '2026-06-27 08:00:00', NULL),
    (503, 110, 29, 'Lộ trình học HTML CSS JavaScript', 'lo-trinh-hoc-html-css-javascript', 'video', 'Nội dung video: Lộ trình học HTML CSS JavaScript. File seed theo course_folder career-webdev.', '/videos/career-webdev/career-webdev-08-lo-trinh-hoc-html-css-javascript.mp4', 600, 0, 'published', 3, '2026-06-27 08:00:00', '2026-06-27 08:00:00', NULL),
    (504, 110, 29, 'Lộ trình học PHP Laravel Backend', 'lo-trinh-hoc-php-laravel-backend', 'video', 'Nội dung video: Lộ trình học PHP Laravel Backend. File seed theo course_folder career-webdev.', '/videos/career-webdev/career-webdev-09-lo-trinh-hoc-php-laravel-backend.mp4', 600, 0, 'published', 4, '2026-06-27 08:00:00', '2026-06-27 08:00:00', NULL),
    (505, 110, 29, 'Lộ trình học React và API để làm dự án', 'lo-trinh-hoc-react-va-api-de-lam-du-an', 'video', 'Nội dung video: Lộ trình học React và API để làm dự án. File seed theo course_folder career-webdev.', '/videos/career-webdev/career-webdev-10-lo-trinh-hoc-react-va-api-de-lam-du-an.mp4', 600, 0, 'published', 5, '2026-06-27 08:00:00', '2026-06-27 08:00:00', NULL),
    (506, 111, 29, 'Cách xây CV IT khi chưa có kinh nghiệm', 'cach-xay-cv-it-khi-chua-co-kinh-nghiem', 'video', 'Nội dung video: Cách xây CV IT khi chưa có kinh nghiệm. File seed theo course_folder career-webdev.', '/videos/career-webdev/career-webdev-11-cach-xay-cv-it-khi-chua-co-kinh-nghiem.mp4', 600, 0, 'published', 1, '2026-06-27 08:00:00', '2026-06-27 08:00:00', NULL),
    (507, 111, 29, 'Cách viết mô tả đồ án trong CV', 'cach-viet-mo-ta-do-an-trong-cv', 'video', 'Nội dung video: Cách viết mô tả đồ án trong CV. File seed theo course_folder career-webdev.', '/videos/career-webdev/career-webdev-12-cach-viet-mo-ta-do-an-trong-cv.mp4', 600, 0, 'published', 2, '2026-06-27 08:00:00', '2026-06-27 08:00:00', NULL),
    (508, 111, 29, 'Cách viết phần kỹ năng trong CV IT', 'cach-viet-phan-ky-nang-trong-cv-it', 'video', 'Nội dung video: Cách viết phần kỹ năng trong CV IT. File seed theo course_folder career-webdev.', '/videos/career-webdev/career-webdev-13-cach-viet-phan-ky-nang-trong-cv-it.mp4', 600, 0, 'published', 3, '2026-06-27 08:00:00', '2026-06-27 08:00:00', NULL),
    (509, 111, 29, 'Cách viết phần dự án cá nhân trong CV', 'cach-viet-phan-du-an-ca-nhan-trong-cv', 'video', 'Nội dung video: Cách viết phần dự án cá nhân trong CV. File seed theo course_folder career-webdev.', '/videos/career-webdev/career-webdev-14-cach-viet-phan-du-an-ca-nhan-trong-cv.mp4', 600, 0, 'published', 4, '2026-06-27 08:00:00', '2026-06-27 08:00:00', NULL),
    (510, 111, 29, 'Những lỗi CV khiến sinh viên mất điểm', 'nhung-loi-cv-khien-sinh-vien-mat-diem', 'video', 'Nội dung video: Những lỗi CV khiến sinh viên mất điểm. File seed theo course_folder career-webdev.', '/videos/career-webdev/career-webdev-15-nhung-loi-cv-khien-sinh-vien-mat-diem.mp4', 600, 0, 'published', 5, '2026-06-27 08:00:00', '2026-06-27 08:00:00', NULL),
    (511, 112, 29, 'GitHub profile cần có gì để gây ấn tượng?', 'github-profile-can-co-gi-de-gay-an-tuong', 'video', 'Nội dung video: GitHub profile cần có gì để gây ấn tượng?. File seed theo course_folder career-webdev.', '/videos/career-webdev/career-webdev-16-github-profile-can-co-gi-de-gay-an-tuong.mp4', 600, 0, 'published', 1, '2026-06-27 08:00:00', '2026-06-27 08:00:00', NULL),
    (512, 112, 29, 'Portfolio cá nhân nên có những dự án nào?', 'portfolio-ca-nhan-nen-co-nhung-du-an-nao', 'video', 'Nội dung video: Portfolio cá nhân nên có những dự án nào?. File seed theo course_folder career-webdev.', '/videos/career-webdev/career-webdev-17-portfolio-ca-nhan-nen-co-nhung-du-an-nao.mp4', 600, 0, 'published', 2, '2026-06-27 08:00:00', '2026-06-27 08:00:00', NULL),
    (513, 112, 29, 'Cách viết README cho project trên GitHub', 'cach-viet-readme-cho-project-tren-github', 'video', 'Nội dung video: Cách viết README cho project trên GitHub. File seed theo course_folder career-webdev.', '/videos/career-webdev/career-webdev-18-cach-viet-readme-cho-project-tren-github.mp4', 600, 0, 'published', 3, '2026-06-27 08:00:00', '2026-06-27 08:00:00', NULL),
    (514, 112, 29, 'Cách trình bày đồ án E-learning trong portfolio', 'cach-trinh-bay-do-an-e-learning-trong-portfolio', 'video', 'Nội dung video: Cách trình bày đồ án E-learning trong portfolio. File seed theo course_folder career-webdev.', '/videos/career-webdev/career-webdev-19-cach-trinh-bay-do-an-e-learning-trong-portfolio.mp4', 600, 0, 'published', 4, '2026-06-27 08:00:00', '2026-06-27 08:00:00', NULL),
    (515, 112, 29, 'Checklist portfolio trước khi gửi nhà tuyển dụng', 'checklist-portfolio-truoc-khi-gui-nha-tuyen-dung', 'video', 'Nội dung video: Checklist portfolio trước khi gửi nhà tuyển dụng. File seed theo course_folder career-webdev.', '/videos/career-webdev/career-webdev-20-checklist-portfolio-truoc-khi-gui-nha-tuyen-dung.mp4', 600, 0, 'published', 5, '2026-06-27 08:00:00', '2026-06-27 08:00:00', NULL),
    (516, 113, 29, 'Cách chuẩn bị trước khi đi phỏng vấn fresher', 'cach-chuan-bi-truoc-khi-di-phong-van-fresher', 'video', 'Nội dung video: Cách chuẩn bị trước khi đi phỏng vấn fresher. File seed theo course_folder career-webdev.', '/videos/career-webdev/career-webdev-21-cach-chuan-bi-truoc-khi-di-phong-van-fresher.mp4', 600, 0, 'published', 1, '2026-06-27 08:00:00', '2026-06-27 08:00:00', NULL),
    (517, 113, 29, 'Những lỗi khiến sinh viên rớt phỏng vấn IT', 'nhung-loi-khien-sinh-vien-rot-phong-van-it', 'video', 'Nội dung video: Những lỗi khiến sinh viên rớt phỏng vấn IT. File seed theo course_folder career-webdev.', '/videos/career-webdev/career-webdev-22-nhung-loi-khien-sinh-vien-rot-phong-van-it.mp4', 600, 0, 'published', 2, '2026-06-27 08:00:00', '2026-06-27 08:00:00', NULL),
    (518, 113, 29, 'Cách trả lời khi chưa có kinh nghiệm đi làm', 'cach-tra-loi-khi-chua-co-kinh-nghiem-di-lam', 'video', 'Nội dung video: Cách trả lời khi chưa có kinh nghiệm đi làm. File seed theo course_folder career-webdev.', '/videos/career-webdev/career-webdev-23-cach-tra-loi-khi-chua-co-kinh-nghiem-di-lam.mp4', 600, 0, 'published', 3, '2026-06-27 08:00:00', '2026-06-27 08:00:00', NULL),
    (519, 113, 29, 'Cách nói về điểm mạnh và điểm yếu', 'cach-noi-ve-diem-manh-va-diem-yeu', 'video', 'Nội dung video: Cách nói về điểm mạnh và điểm yếu. File seed theo course_folder career-webdev.', '/videos/career-webdev/career-webdev-24-cach-noi-ve-diem-manh-va-diem-yeu.mp4', 600, 0, 'published', 4, '2026-06-27 08:00:00', '2026-06-27 08:00:00', NULL),
    (520, 113, 29, 'Cách hỏi ngược lại nhà tuyển dụng', 'cach-hoi-nguoc-lai-nha-tuyen-dung', 'video', 'Nội dung video: Cách hỏi ngược lại nhà tuyển dụng. File seed theo course_folder career-webdev.', '/videos/career-webdev/career-webdev-25-cach-hoi-nguoc-lai-nha-tuyen-dung.mp4', 600, 0, 'published', 5, '2026-06-27 08:00:00', '2026-06-27 08:00:00', NULL),
    (521, 114, 29, 'Lộ trình 90 ngày để sẵn sàng xin internship', 'lo-trinh-90-ngay-de-san-sang-xin-internship', 'video', 'Nội dung video: Lộ trình 90 ngày để sẵn sàng xin internship. File seed theo course_folder career-webdev.', '/videos/career-webdev/career-webdev-26-lo-trinh-90-ngay-de-san-sang-xin-internship.mp4', 600, 0, 'published', 1, '2026-06-27 08:00:00', '2026-06-27 08:00:00', NULL),
    (522, 114, 29, '30 ngày đầu: củng cố nền tảng web', '30-ngay-dau-cung-co-nen-tang-web', 'video', 'Nội dung video: 30 ngày đầu: củng cố nền tảng web. File seed theo course_folder career-webdev.', '/videos/career-webdev/career-webdev-27-30-ngay-dau-cung-co-nen-tang-web.mp4', 600, 0, 'published', 2, '2026-06-27 08:00:00', '2026-06-27 08:00:00', NULL),
    (523, 114, 29, '30 ngày tiếp theo: hoàn thiện project và GitHub', '30-ngay-tiep-theo-hoan-thien-project-va-github', 'video', 'Nội dung video: 30 ngày tiếp theo: hoàn thiện project và GitHub. File seed theo course_folder career-webdev.', '/videos/career-webdev/career-webdev-28-30-ngay-tiep-theo-hoan-thien-project-va-github.mp4', 600, 0, 'published', 3, '2026-06-27 08:00:00', '2026-06-27 08:00:00', NULL),
    (524, 114, 29, '30 ngày cuối: luyện phỏng vấn và apply', '30-ngay-cuoi-luyen-phong-van-va-apply', 'video', 'Nội dung video: 30 ngày cuối: luyện phỏng vấn và apply. File seed theo course_folder career-webdev.', '/videos/career-webdev/career-webdev-29-30-ngay-cuoi-luyen-phong-van-va-apply.mp4', 600, 0, 'published', 4, '2026-06-27 08:00:00', '2026-06-27 08:00:00', NULL),
    (525, 114, 29, 'Tổng kết khóa học: từ sinh viên đến Web Developer Fresher', 'tong-ket-khoa-hoc-tu-sinh-vien-den-web-developer-fresher', 'video', 'Nội dung video: Tổng kết khóa học: từ sinh viên đến Web Developer Fresher. File seed theo course_folder career-webdev.', '/videos/career-webdev/career-webdev-30-tong-ket-khoa-hoc-tu-sinh-vien-den-web-developer-fresher.mp4', 600, 0, 'published', 5, '2026-06-27 08:00:00', '2026-06-27 08:00:00', NULL),
    (526, 115, 30, 'Backend Developer Fresher cần biết gì?', 'backend-developer-fresher-can-biet-gi', 'video', 'Nội dung video: Backend Developer Fresher cần biết gì?. File seed theo course_folder backend-interview.', '/videos/backend-interview/backend-interview-01-backend-developer-fresher-can-biet-gi.mp4', 600, 1, 'published', 1, '2026-06-27 08:00:00', '2026-06-27 08:00:00', NULL),
    (527, 115, 30, 'Nhà tuyển dụng hỏi gì ở Backend Fresher?', 'nha-tuyen-dung-hoi-gi-o-backend-fresher', 'video', 'Nội dung video: Nhà tuyển dụng hỏi gì ở Backend Fresher?. File seed theo course_folder backend-interview.', '/videos/backend-interview/backend-interview-02-nha-tuyen-dung-hoi-gi-o-backend-fresher.mp4', 600, 0, 'published', 2, '2026-06-27 08:00:00', '2026-06-27 08:00:00', NULL),
    (528, 115, 30, 'Cách giới thiệu bản thân khi phỏng vấn Backend', 'cach-gioi-thieu-ban-than-khi-phong-van-backend', 'video', 'Nội dung video: Cách giới thiệu bản thân khi phỏng vấn Backend. File seed theo course_folder backend-interview.', '/videos/backend-interview/backend-interview-03-cach-gioi-thieu-ban-than-khi-phong-van-backend.mp4', 600, 0, 'published', 3, '2026-06-27 08:00:00', '2026-06-27 08:00:00', NULL),
    (529, 115, 30, 'Cách trình bày đồ án Backend E-learning', 'cach-trinh-bay-do-an-backend-e-learning', 'video', 'Nội dung video: Cách trình bày đồ án Backend E-learning. File seed theo course_folder backend-interview.', '/videos/backend-interview/backend-interview-04-cach-trinh-bay-do-an-backend-e-learning.mp4', 600, 0, 'published', 4, '2026-06-27 08:00:00', '2026-06-27 08:00:00', NULL),
    (530, 115, 30, 'Những lỗi khiến Backend Fresher mất điểm', 'nhung-loi-khien-backend-fresher-mat-diem', 'video', 'Nội dung video: Những lỗi khiến Backend Fresher mất điểm. File seed theo course_folder backend-interview.', '/videos/backend-interview/backend-interview-05-nhung-loi-khien-backend-fresher-mat-diem.mp4', 600, 0, 'published', 5, '2026-06-27 08:00:00', '2026-06-27 08:00:00', NULL),
    (531, 116, 30, 'REST API là gì và trả lời sao cho dễ hiểu?', 'rest-api-la-gi-va-tra-loi-sao-cho-de-hieu', 'video', 'Nội dung video: REST API là gì và trả lời sao cho dễ hiểu?. File seed theo course_folder backend-interview.', '/videos/backend-interview/backend-interview-06-rest-api-la-gi-va-tra-loi-sao-cho-de-hieu.mp4', 600, 0, 'published', 1, '2026-06-27 08:00:00', '2026-06-27 08:00:00', NULL),
    (532, 116, 30, 'HTTP method GET POST PUT PATCH DELETE khác nhau thế nào?', 'http-method-get-post-put-patch-delete-khac-nhau-the-nao', 'video', 'Nội dung video: HTTP method GET POST PUT PATCH DELETE khác nhau thế nào?. File seed theo course_folder backend-interview.', '/videos/backend-interview/backend-interview-07-http-method-get-post-put-patch-delete-khac-nhau-the-nao.mp4', 600, 0, 'published', 2, '2026-06-27 08:00:00', '2026-06-27 08:00:00', NULL),
    (533, 116, 30, 'Status code thường gặp khi làm REST API', 'status-code-thuong-gap-khi-lam-rest-api', 'video', 'Nội dung video: Status code thường gặp khi làm REST API. File seed theo course_folder backend-interview.', '/videos/backend-interview/backend-interview-08-status-code-thuong-gap-khi-lam-rest-api.mp4', 600, 0, 'published', 3, '2026-06-27 08:00:00', '2026-06-27 08:00:00', NULL),
    (534, 116, 30, 'Request, response, header, body, query params khác nhau thế nào?', 'request-response-header-body-query-params-khac-nhau-the-nao', 'video', 'Nội dung video: Request, response, header, body, query params khác nhau thế nào?. File seed theo course_folder backend-interview.', '/videos/backend-interview/backend-interview-09-request-response-header-body-query-params-khac-nhau-the-nao.mp4', 600, 0, 'published', 4, '2026-06-27 08:00:00', '2026-06-27 08:00:00', NULL),
    (535, 116, 30, 'JSON API, filter, sort và pagination nên giải thích ra sao?', 'json-api-filter-sort-va-pagination-nen-giai-thich-ra-sao', 'video', 'Nội dung video: JSON API, filter, sort và pagination nên giải thích ra sao?. File seed theo course_folder backend-interview.', '/videos/backend-interview/backend-interview-10-json-api-filter-sort-va-pagination-nen-giai-thich-ra-sao.mp4', 600, 0, 'published', 5, '2026-06-27 08:00:00', '2026-06-27 08:00:00', NULL),
    (536, 117, 30, 'Authentication và Authorization khác nhau ra sao?', 'authentication-va-authorization-khac-nhau-ra-sao', 'video', 'Nội dung video: Authentication và Authorization khác nhau ra sao?. File seed theo course_folder backend-interview.', '/videos/backend-interview/backend-interview-11-authentication-va-authorization-khac-nhau-ra-sao.mp4', 600, 0, 'published', 1, '2026-06-27 08:00:00', '2026-06-27 08:00:00', NULL),
    (537, 117, 30, 'Session, token, JWT và refresh token nên hiểu thế nào?', 'session-token-jwt-va-refresh-token-nen-hieu-the-nao', 'video', 'Nội dung video: Session, token, JWT và refresh token nên hiểu thế nào?. File seed theo course_folder backend-interview.', '/videos/backend-interview/backend-interview-12-session-token-jwt-va-refresh-token-nen-hieu-the-nao.mp4', 600, 0, 'published', 2, '2026-06-27 08:00:00', '2026-06-27 08:00:00', NULL),
    (538, 117, 30, 'Middleware và role permission trong backend', 'middleware-va-role-permission-trong-backend', 'video', 'Nội dung video: Middleware và role permission trong backend. File seed theo course_folder backend-interview.', '/videos/backend-interview/backend-interview-13-middleware-va-role-permission-trong-backend.mp4', 600, 0, 'published', 3, '2026-06-27 08:00:00', '2026-06-27 08:00:00', NULL),
    (539, 117, 30, 'Validation dữ liệu trong backend', 'validation-du-lieu-trong-backend', 'video', 'Nội dung video: Validation dữ liệu trong backend. File seed theo course_folder backend-interview.', '/videos/backend-interview/backend-interview-14-validation-du-lieu-trong-backend.mp4', 600, 0, 'published', 4, '2026-06-27 08:00:00', '2026-06-27 08:00:00', NULL),
    (540, 117, 30, 'Bảo mật cơ bản khi làm REST API', 'bao-mat-co-ban-khi-lam-rest-api', 'video', 'Nội dung video: Bảo mật cơ bản khi làm REST API. File seed theo course_folder backend-interview.', '/videos/backend-interview/backend-interview-15-bao-mat-co-ban-khi-lam-rest-api.mp4', 600, 0, 'published', 5, '2026-06-27 08:00:00', '2026-06-27 08:00:00', NULL),
    (541, 118, 30, 'Primary key, foreign key và relationship trong database', 'primary-key-foreign-key-va-relationship-trong-database', 'video', 'Nội dung video: Primary key, foreign key và relationship trong database. File seed theo course_folder backend-interview.', '/videos/backend-interview/backend-interview-16-primary-key-foreign-key-va-relationship-trong-database.mp4', 600, 0, 'published', 1, '2026-06-27 08:00:00', '2026-06-27 08:00:00', NULL),
    (542, 118, 30, 'JOIN, N+1 query và index cơ bản trong phỏng vấn', 'join-n-1-query-va-index-co-ban-trong-phong-van', 'video', 'Nội dung video: JOIN, N+1 query và index cơ bản trong phỏng vấn. File seed theo course_folder backend-interview.', '/videos/backend-interview/backend-interview-17-join-n-1-query-va-index-co-ban-trong-phong-van.mp4', 600, 0, 'published', 2, '2026-06-27 08:00:00', '2026-06-27 08:00:00', NULL),
    (543, 118, 30, 'Transaction là gì và khi nào cần dùng?', 'transaction-la-gi-va-khi-nao-can-dung', 'video', 'Nội dung video: Transaction là gì và khi nào cần dùng?. File seed theo course_folder backend-interview.', '/videos/backend-interview/backend-interview-18-transaction-la-gi-va-khi-nao-can-dung.mp4', 600, 0, 'published', 3, '2026-06-27 08:00:00', '2026-06-27 08:00:00', NULL),
    (544, 118, 30, 'Thiết kế database cho hệ thống E-learning', 'thiet-ke-database-cho-he-thong-e-learning', 'video', 'Nội dung video: Thiết kế database cho hệ thống E-learning. File seed theo course_folder backend-interview.', '/videos/backend-interview/backend-interview-19-thiet-ke-database-cho-he-thong-e-learning.mp4', 600, 0, 'published', 4, '2026-06-27 08:00:00', '2026-06-27 08:00:00', NULL),
    (545, 118, 30, 'Soft delete, audit log và trạng thái dữ liệu', 'soft-delete-audit-log-va-trang-thai-du-lieu', 'video', 'Nội dung video: Soft delete, audit log và trạng thái dữ liệu. File seed theo course_folder backend-interview.', '/videos/backend-interview/backend-interview-20-soft-delete-audit-log-va-trang-thai-du-lieu.mp4', 600, 0, 'published', 5, '2026-06-27 08:00:00', '2026-06-27 08:00:00', NULL),
    (546, 119, 30, 'Laravel route, controller, request và resource', 'laravel-route-controller-request-va-resource', 'video', 'Nội dung video: Laravel route, controller, request và resource. File seed theo course_folder backend-interview.', '/videos/backend-interview/backend-interview-21-laravel-route-controller-request-va-resource.mp4', 600, 0, 'published', 1, '2026-06-27 08:00:00', '2026-06-27 08:00:00', NULL),
    (547, 119, 30, 'Eloquent model, relationship và query scope', 'eloquent-model-relationship-va-query-scope', 'video', 'Nội dung video: Eloquent model, relationship và query scope. File seed theo course_folder backend-interview.', '/videos/backend-interview/backend-interview-22-eloquent-model-relationship-va-query-scope.mp4', 600, 0, 'published', 2, '2026-06-27 08:00:00', '2026-06-27 08:00:00', NULL),
    (548, 119, 30, 'Repository Service Pattern giải thích trong phỏng vấn', 'repository-service-pattern-giai-thich-trong-phong-van', 'video', 'Nội dung video: Repository Service Pattern giải thích trong phỏng vấn. File seed theo course_folder backend-interview.', '/videos/backend-interview/backend-interview-23-repository-service-pattern-giai-thich-trong-phong-van.mp4', 600, 0, 'published', 3, '2026-06-27 08:00:00', '2026-06-27 08:00:00', NULL),
    (549, 119, 30, 'Exception handling và response format trong API', 'exception-handling-va-response-format-trong-api', 'video', 'Nội dung video: Exception handling và response format trong API. File seed theo course_folder backend-interview.', '/videos/backend-interview/backend-interview-24-exception-handling-va-response-format-trong-api.mp4', 600, 0, 'published', 4, '2026-06-27 08:00:00', '2026-06-27 08:00:00', NULL),
    (550, 119, 30, 'Queue, scheduler và command trong Laravel dùng khi nào?', 'queue-scheduler-va-command-trong-laravel-dung-khi-nao', 'video', 'Nội dung video: Queue, scheduler và command trong Laravel dùng khi nào?. File seed theo course_folder backend-interview.', '/videos/backend-interview/backend-interview-25-queue-scheduler-va-command-trong-laravel-dung-khi-nao.mp4', 600, 0, 'published', 5, '2026-06-27 08:00:00', '2026-06-27 08:00:00', NULL),
    (551, 120, 30, 'Cách test API bằng Postman trong phỏng vấn', 'cach-test-api-bang-postman-trong-phong-van', 'video', 'Nội dung video: Cách test API bằng Postman trong phỏng vấn. File seed theo course_folder backend-interview.', '/videos/backend-interview/backend-interview-26-cach-test-api-bang-postman-trong-phong-van.mp4', 600, 0, 'published', 1, '2026-06-27 08:00:00', '2026-06-27 08:00:00', NULL),
    (552, 120, 30, 'Cách debug lỗi backend và đọc log', 'cach-debug-loi-backend-va-doc-log', 'video', 'Nội dung video: Cách debug lỗi backend và đọc log. File seed theo course_folder backend-interview.', '/videos/backend-interview/backend-interview-27-cach-debug-loi-backend-va-doc-log.mp4', 600, 0, 'published', 2, '2026-06-27 08:00:00', '2026-06-27 08:00:00', NULL),
    (553, 120, 30, 'Cách trả lời câu hỏi về payment và enrollment trong đồ án', 'cach-tra-loi-cau-hoi-ve-payment-va-enrollment-trong-do-an', 'video', 'Nội dung video: Cách trả lời câu hỏi về payment và enrollment trong đồ án. File seed theo course_folder backend-interview.', '/videos/backend-interview/backend-interview-28-cach-tra-loi-cau-hoi-ve-payment-va-enrollment-trong-do-an.mp4', 600, 0, 'published', 3, '2026-06-27 08:00:00', '2026-06-27 08:00:00', NULL),
    (554, 120, 30, 'Mock interview Backend Fresher: 10 câu hỏi thường gặp', 'mock-interview-backend-fresher-10-cau-hoi-thuong-gap', 'video', 'Nội dung video: Mock interview Backend Fresher: 10 câu hỏi thường gặp. File seed theo course_folder backend-interview.', '/videos/backend-interview/backend-interview-29-mock-interview-backend-fresher-10-cau-hoi-thuong-gap.mp4', 600, 0, 'published', 4, '2026-06-27 08:00:00', '2026-06-27 08:00:00', NULL),
    (555, 120, 30, 'Tổng kết checklist trước phỏng vấn Backend Developer', 'tong-ket-checklist-truoc-phong-van-backend-developer', 'video', 'Nội dung video: Tổng kết checklist trước phỏng vấn Backend Developer. File seed theo course_folder backend-interview.', '/videos/backend-interview/backend-interview-30-tong-ket-checklist-truoc-phong-van-backend-developer.mp4', 600, 0, 'published', 5, '2026-06-27 08:00:00', '2026-06-27 08:00:00', NULL);

-- Các khóa sau đã có sẵn slug trong file seed gốc nên không insert lại để tránh lỗi UNIQUE(slug):
-- - Laravel REST API từ cơ bản đến triển khai | slug=laravel-rest-api-tu-co-ban-den-trien-khai | file=MindHub_LARAVEL_REST_API_30_NotebookLM_Prompts.md

SET FOREIGN_KEY_CHECKS = 1;
