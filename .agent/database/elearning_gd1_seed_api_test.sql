-- ==========================================================
-- E-learning GD1 Seed Data for API Testing
-- File: elearning_gd1_seed_api_test.sql
-- Purpose: Seed dữ liệu mẫu theo test scenario để test API thành công/thất bại/phân quyền/trạng thái.
-- Target schema: elearning_erd_gd1(4).sql
--
-- CẢNH BÁO:
-- - Chỉ dùng cho local/dev/test database.
-- - File này có xóa dữ liệu ở các bảng GD1 trước khi insert lại seed cố định.
-- - Password test gợi ý cho team: 12345678
-- ==========================================================

SET NAMES utf8mb4;
SET FOREIGN_KEY_CHECKS = 0;

-- Xóa dữ liệu cũ theo thứ tự con -> cha để chạy seed nhiều lần không bị trùng.
DELETE FROM quiz_attempt_answers;
DELETE FROM quiz_attempts;
DELETE FROM quiz_options;
DELETE FROM quiz_questions;
DELETE FROM quizzes;
DELETE FROM course_faqs;
DELETE FROM faqs;
DELETE FROM banners;
DELETE FROM withdraw_requests;
DELETE FROM payout_accounts;
DELETE FROM revenues;
DELETE FROM instructor_profiles;
DELETE FROM comments;
DELETE FROM wishlist;
DELETE FROM course_reviews;
DELETE FROM enrollments;
DELETE FROM orders;
DELETE FROM coupons;
DELETE FROM video_progress;
DELETE FROM lesson_progress;
DELETE FROM lesson_assets;
DELETE FROM lessons;
DELETE FROM course_sections;
DELETE FROM course_categories;
DELETE FROM courses;
DELETE FROM categories;
DELETE FROM sessions;
DELETE FROM users;

ALTER TABLE users AUTO_INCREMENT = 1;
ALTER TABLE sessions AUTO_INCREMENT = 1;
ALTER TABLE categories AUTO_INCREMENT = 1;
ALTER TABLE courses AUTO_INCREMENT = 1;
ALTER TABLE course_sections AUTO_INCREMENT = 1;
ALTER TABLE lessons AUTO_INCREMENT = 1;
ALTER TABLE lesson_assets AUTO_INCREMENT = 1;
ALTER TABLE lesson_progress AUTO_INCREMENT = 1;
ALTER TABLE video_progress AUTO_INCREMENT = 1;
ALTER TABLE coupons AUTO_INCREMENT = 1;
ALTER TABLE orders AUTO_INCREMENT = 1;
ALTER TABLE enrollments AUTO_INCREMENT = 1;
ALTER TABLE course_reviews AUTO_INCREMENT = 1;
ALTER TABLE wishlist AUTO_INCREMENT = 1;
ALTER TABLE comments AUTO_INCREMENT = 1;
ALTER TABLE instructor_profiles AUTO_INCREMENT = 1;
ALTER TABLE revenues AUTO_INCREMENT = 1;
ALTER TABLE payout_accounts AUTO_INCREMENT = 1;
ALTER TABLE withdraw_requests AUTO_INCREMENT = 1;
ALTER TABLE banners AUTO_INCREMENT = 1;
ALTER TABLE faqs AUTO_INCREMENT = 1;
ALTER TABLE quizzes AUTO_INCREMENT = 1;
ALTER TABLE quiz_questions AUTO_INCREMENT = 1;
ALTER TABLE quiz_options AUTO_INCREMENT = 1;
ALTER TABLE quiz_attempts AUTO_INCREMENT = 1;
ALTER TABLE quiz_attempt_answers AUTO_INCREMENT = 1;

SET FOREIGN_KEY_CHECKS = 1;

START TRANSACTION;

-- ==========================================================
-- 1. USERS
-- Test cases covered:
-- AUTH login/register/profile; role authorization; locked/inactive account; OAuth account.
-- Email/password test convention: password = 12345678
-- ==========================================================
INSERT INTO users
(id, full_name, email, password_hash, phone, oauth_account_login, role, status, email_verified_at, last_login_at, locked, locked_reason, password_reset, created_at, updated_at, deleted_at)
VALUES
(1, 'Admin MindHub', 'admin@mindhub.test', '$2y$10$seedhashfor12345678admin000000000000000000000000000', '0900000001', NULL, 'admin', 'active', '2026-06-01 08:00:00', '2026-06-05 08:00:00', FALSE, NULL, NULL, '2026-06-01 08:00:00', '2026-06-05 08:00:00', NULL),
(2, 'Nguyen Van Instructor 1', 'instructor1@mindhub.test', '$2y$10$seedhashfor12345678inst100000000000000000000000000', '0900000002', NULL, 'instructor', 'active', '2026-06-01 08:05:00', '2026-06-05 08:05:00', FALSE, NULL, NULL, '2026-06-01 08:05:00', '2026-06-05 08:05:00', NULL),
(3, 'Tran Thi Instructor 2', 'instructor2@mindhub.test', '$2y$10$seedhashfor12345678inst200000000000000000000000000', '0900000003', NULL, 'instructor', 'active', '2026-06-01 08:10:00', '2026-06-05 08:10:00', FALSE, NULL, NULL, '2026-06-01 08:10:00', '2026-06-05 08:10:00', NULL),
(4, 'Le Van Learner Bought', 'learner1@mindhub.test', '$2y$10$seedhashfor12345678learner1000000000000000000000000', '0900000004', NULL, 'learner', 'active', '2026-06-01 08:15:00', '2026-06-05 08:15:00', FALSE, NULL, NULL, '2026-06-01 08:15:00', '2026-06-05 08:15:00', NULL),
(5, 'Pham Thi Learner New', 'learner2@mindhub.test', '$2y$10$seedhashfor12345678learner2000000000000000000000000', '0900000005', NULL, 'learner', 'active', '2026-06-01 08:20:00', NULL, FALSE, NULL, NULL, '2026-06-01 08:20:00', '2026-06-01 08:20:00', NULL),
(6, 'User Locked', 'locked@mindhub.test', '$2y$10$seedhashfor12345678locked00000000000000000000000000', '0900000006', NULL, 'learner', 'locked', '2026-06-01 08:25:00', NULL, TRUE, 'Khóa để test API chặn đăng nhập và thao tác.', NULL, '2026-06-01 08:25:00', '2026-06-01 08:25:00', NULL),
(7, 'User Inactive', 'inactive@mindhub.test', '$2y$10$seedhashfor12345678inactive000000000000000000000000', '0900000007', NULL, 'learner', 'inactive', NULL, NULL, FALSE, NULL, NULL, '2026-06-01 08:30:00', '2026-06-01 08:30:00', NULL),
(8, 'Google OAuth Learner', 'oauth.learner@mindhub.test', NULL, NULL, 'google-oauth-learner-001', 'learner', 'active', '2026-06-01 08:35:00', '2026-06-05 08:20:00', FALSE, NULL, NULL, '2026-06-01 08:35:00', '2026-06-05 08:20:00', NULL),
(9, 'Learner Completed Course', 'learner.completed@mindhub.test', '$2y$10$seedhashfor12345678complete000000000000000000000000', '0900000009', NULL, 'learner', 'active', '2026-06-01 08:40:00', '2026-06-05 08:30:00', FALSE, NULL, NULL, '2026-06-01 08:40:00', '2026-06-05 08:30:00', NULL);

-- ==========================================================
-- 2. SESSIONS
-- Test cases covered: logout, refresh token, revoked/expired session.
-- ==========================================================
INSERT INTO sessions
(id, user_id, refresh_token_hash, device_name, ip_address, user_agent, expires_at, revoked_at, created_at)
VALUES
(1, 4, 'hash-refresh-learner1-active', 'Chrome Windows', '127.0.0.1', 'Mozilla/5.0 seed active session', '2026-07-05 08:00:00', NULL, '2026-06-05 08:00:00'),
(2, 4, 'hash-refresh-learner1-revoked', 'Mobile Android', '127.0.0.2', 'Mozilla/5.0 seed revoked session', '2026-07-05 08:00:00', '2026-06-05 09:00:00', '2026-06-05 08:30:00'),
(3, 2, 'hash-refresh-instructor1-active', 'Edge Windows', '127.0.0.3', 'Mozilla/5.0 seed instructor session', '2026-07-05 08:00:00', NULL, '2026-06-05 08:10:00'),
(4, 5, 'hash-refresh-learner2-expired', 'Chrome Windows', '127.0.0.4', 'Mozilla/5.0 seed expired session', '2026-05-01 08:00:00', NULL, '2026-04-01 08:00:00');

-- ==========================================================
-- 3. CATEGORIES
-- Test cases covered: catalog filter, active/inactive category, parent-child category.
-- ==========================================================
INSERT INTO categories
(id, parent_id, name, slug, description, sort_order, status, created_at, updated_at, deleted_at)
VALUES
(1, NULL, 'Lập trình Backend', 'lap-trinh-backend', 'Các khóa học backend thực chiến.', 1, 'active', NOW(), NOW(), NULL),
(2, NULL, 'Lập trình Frontend', 'lap-trinh-frontend', 'Các khóa học frontend cho web developer.', 2, 'active', NOW(), NOW(), NULL),
(3, NULL, 'Cơ sở dữ liệu', 'co-so-du-lieu', 'SQL, thiết kế database và tối ưu truy vấn.', 3, 'active', NOW(), NOW(), NULL),
(4, 1, 'Laravel', 'laravel', 'Laravel REST API và backend PHP.', 1, 'active', NOW(), NOW(), NULL),
(5, 2, 'ReactJS', 'reactjs', 'ReactJS căn bản đến thực chiến.', 1, 'active', NOW(), NOW(), NULL),
(6, NULL, 'Danh mục ẩn để test', 'danh-muc-an-de-test', 'Danh mục inactive dùng để test lọc dữ liệu.', 99, 'inactive', NOW(), NOW(), NULL);

-- ==========================================================
-- 4. COURSES
-- Test cases covered: published/draft/pending_review/approved/rejected/hidden, instructor ownership, featured, sale price.
-- ==========================================================
INSERT INTO courses
(id, instructor_id, title, slug, short_description, description, thumbnail_url, intro_video_url, price, sale_price, level, language, requirements, outcomes, status, is_featured, total_duration_seconds, published_at, admin_reject_reason, created_at, updated_at, deleted_at)
VALUES
(1, 2, 'Laravel REST API Cơ Bản', 'laravel-rest-api-co-ban', 'Học Laravel REST API từ nền tảng.', 'Khóa học phục vụ test luồng mua khóa, học bài, review, comment và quiz.', '/uploads/courses/laravel-api.jpg', '/uploads/courses/laravel-intro.mp4', 499000.00, 299000.00, 'beginner', 'vi', 'Biết PHP căn bản.', 'Xây dựng được REST API bằng Laravel.', 'published', TRUE, 5400, '2026-06-01 09:00:00', NULL, NOW(), NOW(), NULL),
(2, 2, 'PHP MVC Nền Tảng', 'php-mvc-nen-tang', 'Khóa học MVC trước khi học Laravel.', 'Dùng để test trạng thái pending_review và admin duyệt/từ chối khóa học.', '/uploads/courses/php-mvc.jpg', NULL, 399000.00, NULL, 'beginner', 'vi', 'Biết PHP cơ bản.', 'Hiểu MVC và tổ chức code PHP.', 'pending_review', FALSE, 3600, NULL, NULL, NOW(), NOW(), NULL),
(3, 2, 'SQL Database Thực Chiến', 'sql-database-thuc-chien', 'Thiết kế database và viết SQL.', 'Dùng để test khóa đã approved nhưng chưa published.', '/uploads/courses/sql.jpg', NULL, 599000.00, 399000.00, 'intermediate', 'vi', 'Biết SQL cơ bản.', 'Thiết kế database tốt hơn.', 'approved', FALSE, 7200, NULL, NULL, NOW(), NOW(), NULL),
(4, 3, 'ReactJS Cơ Bản', 'reactjs-co-ban', 'Khóa ReactJS của instructor 2.', 'Dùng để test phân quyền instructor không được sửa khóa của người khác.', '/uploads/courses/react.jpg', NULL, 699000.00, 499000.00, 'beginner', 'vi', 'Biết HTML CSS JS.', 'Xây dựng được SPA căn bản.', 'draft', FALSE, 4800, NULL, NULL, NOW(), NOW(), NULL),
(5, 3, 'NodeJS Hidden Course', 'nodejs-hidden-course', 'Khóa bị ẩn khỏi public.', 'Dùng để test course hidden không hiển thị và không được mua.', '/uploads/courses/node-hidden.jpg', NULL, 799000.00, NULL, 'intermediate', 'vi', 'Biết JavaScript.', 'Hiểu NodeJS backend.', 'hidden', FALSE, 5200, NULL, NULL, NOW(), NOW(), NULL),
(6, 2, 'Khóa Bị Từ Chối', 'khoa-bi-tu-choi', 'Khóa dùng để test rejected.', 'Admin đã từ chối vì nội dung chưa đủ chất lượng.', '/uploads/courses/rejected.jpg', NULL, 299000.00, NULL, 'beginner', 'vi', 'Không yêu cầu.', 'Không dùng cho public.', 'rejected', FALSE, 1800, NULL, 'Nội dung khóa học còn sơ sài, thiếu bài học mẫu.', NOW(), NOW(), NULL),
(7, 2, 'Git Cơ Bản Miễn Phí', 'git-co-ban-mien-phi', 'Khóa miễn phí dùng để test order 0 đồng.', 'Dùng để test coupon/free flow và enrollment miễn phí.', '/uploads/courses/git-free.jpg', NULL, 0.00, NULL, 'beginner', 'vi', 'Không yêu cầu.', 'Biết Git cơ bản.', 'published', TRUE, 2400, '2026-06-01 10:00:00', NULL, NOW(), NOW(), NULL);

-- ==========================================================
-- 5. COURSE_CATEGORIES
-- ==========================================================
INSERT INTO course_categories (category_id, course_id, created_at)
VALUES
(4, 1, NOW()),
(1, 2, NOW()),
(3, 3, NOW()),
(5, 4, NOW()),
(1, 5, NOW()),
(1, 6, NOW()),
(1, 7, NOW());

-- ==========================================================
-- 6. COURSE_SECTIONS
-- Test cases covered: section sort_order, published/hidden/draft section.
-- ==========================================================
INSERT INTO course_sections
(id, course_id, title, description, sort_order, status, created_at, updated_at, deleted_at)
VALUES
(1, 1, 'Chương 1: Tổng quan Laravel API', 'Làm quen dự án Laravel API.', 1, 'published', NOW(), NOW(), NULL),
(2, 1, 'Chương 2: Route, Controller, Request', 'Xử lý request và validation.', 2, 'published', NOW(), NOW(), NULL),
(3, 1, 'Chương 3: Nội dung ẩn để test', 'Section hidden không nên public.', 3, 'hidden', NOW(), NOW(), NULL),
(4, 2, 'Chương 1: MVC căn bản', 'Section cho khóa pending_review.', 1, 'draft', NOW(), NOW(), NULL),
(5, 4, 'Chương 1: React căn bản', 'Section của instructor 2.', 1, 'draft', NOW(), NOW(), NULL),
(6, 7, 'Chương 1: Git nhập môn', 'Section cho khóa miễn phí.', 1, 'published', NOW(), NOW(), NULL);

-- ==========================================================
-- 7. LESSONS
-- Test cases covered: video/text, preview/non-preview, published/hidden/draft lesson.
-- ==========================================================
INSERT INTO lessons
(id, course_section_id, course_id, title, slug, lesson_type, content, video_url, video_duration_seconds, is_preview, status, sort_order, created_at, updated_at, deleted_at)
VALUES
(1, 1, 1, 'Giới thiệu khóa Laravel API', 'gioi-thieu-khoa-laravel-api', 'video', NULL, '/videos/laravel/intro.mp4', 600, TRUE, 'published', 1, NOW(), NOW(), NULL),
(2, 1, 1, 'Cấu trúc thư mục Laravel', 'cau-truc-thu-muc-laravel', 'text', '<p>Bài học giải thích app, routes, database, resources.</p>', NULL, NULL, FALSE, 'published', 2, NOW(), NOW(), NULL),
(3, 2, 1, 'Route và Controller', 'route-va-controller', 'video', NULL, '/videos/laravel/route-controller.mp4', 1200, FALSE, 'published', 1, NOW(), NOW(), NULL),
(4, 2, 1, 'Form Request Validation', 'form-request-validation', 'text', '<p>Cách validate dữ liệu đầu vào bằng FormRequest.</p>', NULL, NULL, FALSE, 'published', 2, NOW(), NOW(), NULL),
(5, 3, 1, 'Bài học bị ẩn', 'bai-hoc-bi-an', 'video', NULL, '/videos/laravel/hidden.mp4', 800, FALSE, 'hidden', 1, NOW(), NOW(), NULL),
(6, 4, 2, 'MVC là gì', 'mvc-la-gi', 'text', '<p>Giải thích mô hình Model View Controller.</p>', NULL, NULL, TRUE, 'draft', 1, NOW(), NOW(), NULL),
(7, 5, 4, 'React Component là gì', 'react-component-la-gi', 'video', NULL, '/videos/react/component.mp4', 900, TRUE, 'draft', 1, NOW(), NOW(), NULL),
(8, 6, 7, 'Git init và commit đầu tiên', 'git-init-va-commit-dau-tien', 'video', NULL, '/videos/git/init.mp4', 900, TRUE, 'published', 1, NOW(), NOW(), NULL),
(9, 6, 7, 'Git branch và merge', 'git-branch-va-merge', 'text', '<p>Bài học về branch và merge trong Git.</p>', NULL, NULL, FALSE, 'published', 2, NOW(), NOW(), NULL);

-- ==========================================================
-- 8. LESSON_ASSETS
-- Test cases covered: lesson attachments/download resources.
-- ==========================================================
INSERT INTO lesson_assets
(id, lesson_id, title, file_url, file_name, file_type, file_size, note, created_at, deleted_at)
VALUES
(1, 1, 'Slide giới thiệu Laravel API', '/assets/laravel/intro-slide.pdf', 'intro-slide.pdf', 'pdf', 204800, 'Tài liệu preview cho bài 1.', NOW(), NULL),
(2, 3, 'Source code Route Controller', '/assets/laravel/route-controller.zip', 'route-controller.zip', 'zip', 1048576, 'Source code thực hành.', NOW(), NULL),
(3, 8, 'Git command checklist', '/assets/git/git-checklist.pdf', 'git-checklist.pdf', 'pdf', 102400, 'Checklist lệnh Git căn bản.', NOW(), NULL);

-- ==========================================================
-- 9. COUPONS
-- Test cases covered: active/inactive/expired/used_up coupon, course-specific/global coupon, free flow.
-- ==========================================================
INSERT INTO coupons
(id, user_id, course_id, code, name, description, discount_type, discount_value, max_order_amount, usage_limit, used_count, start_at, end_at, status, created_at, updated_at, deleted_at)
VALUES
(1, 1, 1, 'WELCOME100', 'Giảm 100K Laravel', 'Coupon active cho khóa Laravel.', 'fixed', 100000.00, NULL, 100, 1, '2026-06-01 00:00:00', '2026-12-31 23:59:59', 'active', NOW(), NOW(), NULL),
(2, 1, NULL, 'GLOBAL10', 'Giảm 10 phần trăm toàn hệ thống', 'Coupon percent toàn hệ thống.', 'percent', 10.00, 50000.00, 50, 0, '2026-06-01 00:00:00', '2026-12-31 23:59:59', 'active', NOW(), NOW(), NULL),
(3, 1, 1, 'OLD50', 'Coupon hết hạn', 'Dùng để test coupon expired.', 'fixed', 50000.00, NULL, 10, 0, '2026-01-01 00:00:00', '2026-02-01 00:00:00', 'expired', NOW(), NOW(), NULL),
(4, 1, 1, 'OFFLINE20', 'Coupon inactive', 'Dùng để test coupon inactive.', 'percent', 20.00, 100000.00, 10, 0, '2026-06-01 00:00:00', '2026-12-31 23:59:59', 'inactive', NOW(), NOW(), NULL),
(5, 1, 1, 'FULLUSED', 'Coupon hết lượt', 'Dùng để test used_up.', 'fixed', 100000.00, NULL, 1, 1, '2026-06-01 00:00:00', '2026-12-31 23:59:59', 'used_up', NOW(), NOW(), NULL),
(6, 1, 7, 'FREEGIT', 'Coupon 0 đồng cho Git', 'Dùng để test cấp học qua coupon/free course.', 'fixed', 0.00, NULL, 100, 0, '2026-06-01 00:00:00', '2026-12-31 23:59:59', 'active', NOW(), NOW(), NULL);

-- ==========================================================
-- 10. ORDERS
-- Test cases covered:
-- paid -> enrollment; pending/unpaid; failed; cancelled; expired; processing; paid 0đ.
-- ==========================================================
INSERT INTO orders
(id, coupon_id, course_id, user_id, order_code, status, price_snapshot, payment_method, provider_transaction_id, amount, payment_status, paid_at, created_at, updated_at)
VALUES
(1, 1, 1, 4, 'ORD-2026-0001', 'paid', 299000.00, 'vnpay', 'VNPAY-TXN-0001', 199000.00, 'paid', '2026-06-02 09:00:00', '2026-06-02 08:55:00', '2026-06-02 09:00:00'),
(2, NULL, 1, 5, 'ORD-2026-0002', 'pending', 299000.00, 'vnpay', NULL, 299000.00, 'unpaid', NULL, '2026-06-02 10:00:00', '2026-06-02 10:00:00'),
(3, 3, 1, 5, 'ORD-2026-0003', 'failed', 299000.00, 'vnpay', 'VNPAY-TXN-0003-FAILED', 299000.00, 'failed', NULL, '2026-06-02 11:00:00', '2026-06-02 11:05:00'),
(4, NULL, 5, 5, 'ORD-2026-0004', 'cancelled', 799000.00, 'bank_transfer', NULL, 799000.00, 'unpaid', NULL, '2026-06-02 12:00:00', '2026-06-02 12:30:00'),
(5, NULL, 1, 8, 'ORD-2026-0005', 'expired', 299000.00, 'momo', NULL, 299000.00, 'unpaid', NULL, '2026-05-01 08:00:00', '2026-05-02 08:00:00'),
(6, 2, 3, 9, 'ORD-2026-0006', 'paid', 399000.00, 'vnpay', 'VNPAY-TXN-0006', 359100.00, 'paid', '2026-06-03 09:00:00', '2026-06-03 08:55:00', '2026-06-03 09:00:00'),
(7, NULL, 7, 5, 'ORD-2026-0007', 'paid', 0.00, 'free', 'FREE-ORDER-0007', 0.00, 'paid', '2026-06-03 10:00:00', '2026-06-03 09:58:00', '2026-06-03 10:00:00'),
(8, NULL, 1, 9, 'ORD-2026-0008', 'paid', 299000.00, 'vnpay', 'VNPAY-TXN-0008', 299000.00, 'paid', '2026-06-04 09:00:00', '2026-06-04 08:55:00', '2026-06-04 09:00:00'),
(9, NULL, 7, 4, 'ORD-2026-0009', 'paid', 0.00, 'free', 'FREE-ORDER-0009', 0.00, 'paid', '2026-06-04 10:00:00', '2026-06-04 09:58:00', '2026-06-04 10:00:00'),
(10, NULL, 3, 5, 'ORD-2026-0010', 'pending', 399000.00, 'vnpay', NULL, 399000.00, 'processing', NULL, '2026-06-04 11:00:00', '2026-06-04 11:01:00'),
(11, NULL, 1, 8, 'ORD-2026-0011', 'paid', 299000.00, 'vnpay', 'VNPAY-TXN-0011', 299000.00, 'paid', '2026-06-04 12:00:00', '2026-06-04 11:55:00', '2026-06-04 12:00:00');

-- ==========================================================
-- 11. ENROLLMENTS
-- Rule: Chỉ tạo enrollment cho order paid.
-- Test cases covered: active/completed enrollment, duplicate purchase check user_id+course_id.
-- ==========================================================
INSERT INTO enrollments
(id, user_id, course_id, order_id, status, progress_percent, enrolled_at, completed_at, last_accessed_at, created_at, updated_at)
VALUES
(1, 4, 1, 1, 'active', 50.00, '2026-06-02 09:01:00', NULL, '2026-06-05 08:00:00', '2026-06-02 09:01:00', '2026-06-05 08:00:00'),
(2, 9, 3, 6, 'completed', 100.00, '2026-06-03 09:01:00', '2026-06-05 09:00:00', '2026-06-05 09:00:00', '2026-06-03 09:01:00', '2026-06-05 09:00:00'),
(3, 5, 7, 7, 'active', 25.00, '2026-06-03 10:01:00', NULL, '2026-06-05 10:00:00', '2026-06-03 10:01:00', '2026-06-05 10:00:00'),
(4, 9, 1, 8, 'completed', 100.00, '2026-06-04 09:01:00', '2026-06-05 10:00:00', '2026-06-05 10:00:00', '2026-06-04 09:01:00', '2026-06-05 10:00:00'),
(5, 4, 7, 9, 'active', 10.00, '2026-06-04 10:01:00', NULL, '2026-06-05 10:30:00', '2026-06-04 10:01:00', '2026-06-05 10:30:00'),
(6, 8, 1, 11, 'active', 5.00, '2026-06-04 12:01:00', NULL, '2026-06-05 12:00:00', '2026-06-04 12:01:00', '2026-06-05 12:00:00');

-- ==========================================================
-- 12. LESSON_PROGRESS
-- Test cases covered: not_started/in_progress/completed, progress update, course progress calculation.
-- ==========================================================
INSERT INTO lesson_progress
(id, lesson_id, user_id, status, started_at, completed_at, learning_duration_seconds, last_accessed_at, created_at, updated_at)
VALUES
(1, 1, 4, 'completed', '2026-06-02 09:10:00', '2026-06-02 09:20:00', 600, '2026-06-02 09:20:00', NOW(), NOW()),
(2, 2, 4, 'completed', '2026-06-02 09:25:00', '2026-06-02 09:40:00', 900, '2026-06-02 09:40:00', NOW(), NOW()),
(3, 3, 4, 'in_progress', '2026-06-03 08:00:00', NULL, 420, '2026-06-05 08:00:00', NOW(), NOW()),
(4, 4, 4, 'not_started', NULL, NULL, 0, NULL, NOW(), NOW()),
(5, 1, 9, 'completed', '2026-06-04 09:10:00', '2026-06-04 09:20:00', 600, '2026-06-04 09:20:00', NOW(), NOW()),
(6, 2, 9, 'completed', '2026-06-04 09:25:00', '2026-06-04 09:40:00', 900, '2026-06-04 09:40:00', NOW(), NOW()),
(7, 3, 9, 'completed', '2026-06-04 10:00:00', '2026-06-04 10:20:00', 1200, '2026-06-04 10:20:00', NOW(), NOW()),
(8, 4, 9, 'completed', '2026-06-04 10:30:00', '2026-06-04 10:45:00', 900, '2026-06-04 10:45:00', NOW(), NOW()),
(9, 8, 5, 'in_progress', '2026-06-05 10:00:00', NULL, 250, '2026-06-05 10:10:00', NOW(), NOW());

-- ==========================================================
-- 13. VIDEO_PROGRESS
-- Test cases covered: resume video, completed video, in-progress video.
-- ==========================================================
INSERT INTO video_progress
(id, lesson_id, user_id, current_second, created_at, updated_at)
VALUES
(1, 1, 4, 600, NOW(), NOW()),
(2, 3, 4, 420, NOW(), NOW()),
(3, 1, 9, 600, NOW(), NOW()),
(4, 3, 9, 1200, NOW(), NOW()),
(5, 8, 5, 250, NOW(), NOW());

-- ==========================================================
-- 14. COURSE_REVIEWS
-- Rule: Review gắn order_id để chứng minh đã mua khóa.
-- Test cases covered: review valid, one review per order, rating high/low.
-- ==========================================================
INSERT INTO course_reviews
(id, order_id, rating, comment, created_at, updated_at, deleted_at)
VALUES
(1, 1, 5, 'Khóa Laravel API dễ hiểu, phù hợp người mới.', NOW(), NOW(), NULL),
(2, 6, 4, 'Khóa SQL thực chiến ổn, ví dụ rõ.', NOW(), NOW(), NULL),
(3, 8, 2, 'Một số phần cần giải thích kỹ hơn.', NOW(), NOW(), NULL),
(4, 7, 5, 'Khóa Git miễn phí rất hữu ích.', NOW(), NOW(), NULL);

-- ==========================================================
-- 15. WISHLIST
-- Test cases covered: add/remove wishlist, unique user_id+course_id, wishlist course hidden/draft handling.
-- ==========================================================
INSERT INTO wishlist
(id, user_id, course_id, created_at)
VALUES
(1, 5, 1, NOW()),
(2, 5, 3, NOW()),
(3, 4, 4, NOW()),
(4, 8, 7, NOW());

-- ==========================================================
-- 16. COMMENTS
-- Test cases covered: root comment/reply, visible/hidden/deleted, instructor reply, comment on paid lesson.
-- ==========================================================
INSERT INTO comments
(id, parent_id, user_id, order_id, lesson_id, content, status, created_at, updated_at)
VALUES
(1, NULL, 4, 1, 2, 'Bài cấu trúc Laravel giải thích khá dễ hiểu.', 'visible', NOW(), NOW()),
(2, 1, 2, NULL, 2, 'Cảm ơn em, phần sau sẽ đi sâu hơn về Controller và Service.', 'visible', NOW(), NOW()),
(3, NULL, 9, 8, 3, 'Video route và controller hơi nhanh.', 'hidden', NOW(), NOW()),
(4, NULL, 4, 1, 4, 'Bình luận đã xóa mềm để test trạng thái deleted.', 'deleted', NOW(), NOW()),
(5, NULL, 5, 7, 8, 'Em mới học Git, phần init khá dễ hiểu.', 'visible', NOW(), NOW());

-- ==========================================================
-- 17. INSTRUCTOR_PROFILES
-- Test cases covered: view instructor profile, update profile, instructor listing.
-- ==========================================================
INSERT INTO instructor_profiles
(id, user_id, bio, expertise, experience_years, level, created_at, updated_at)
VALUES
(1, 2, 'Giảng viên backend PHP/Laravel, tập trung REST API và thiết kế database.', 'PHP, Laravel, MySQL, REST API', 5, 'senior', NOW(), NOW()),
(2, 3, 'Giảng viên frontend chuyên ReactJS và UI component.', 'JavaScript, ReactJS, HTML, CSS', 3, 'middle', NOW(), NOW());

-- ==========================================================
-- 18. REVENUES
-- Rule: Revenue sinh từ paid orders.
-- Test cases covered: pending/available/withdrawn/cancelled revenue; instructor revenue isolation.
-- ==========================================================
INSERT INTO revenues
(id, instructor_id, course_id, order_id, gross_amount, instructor_amount, platform_fee_amount, status, earned_at, created_at)
VALUES
(1, 2, 1, 1, 199000.00, 139300.00, 59700.00, 'available', '2026-06-02 09:00:00', NOW()),
(2, 2, 3, 6, 359100.00, 251370.00, 107730.00, 'withdrawn', '2026-06-03 09:00:00', NOW()),
(3, 2, 7, 7, 0.00, 0.00, 0.00, 'pending', '2026-06-03 10:00:00', NOW()),
(4, 2, 1, 8, 299000.00, 209300.00, 89700.00, 'cancelled', '2026-06-04 09:00:00', NOW()),
(5, 2, 7, 9, 0.00, 0.00, 0.00, 'pending', '2026-06-04 10:00:00', NOW()),
(6, 2, 1, 11, 299000.00, 209300.00, 89700.00, 'available', '2026-06-04 12:00:00', NOW());

-- ==========================================================
-- 19. PAYOUT_ACCOUNTS
-- Test cases covered: active/inactive/pending_verification/rejected payout account.
-- ==========================================================
INSERT INTO payout_accounts
(id, user_id, provider, account_number, account_name, connected_at, status, created_at, updated_at, deleted_at)
VALUES
(1, 2, 'bank', '9704220000011111', 'NGUYEN VAN INSTRUCTOR 1', '2026-06-01 09:00:00', 'active', NOW(), NOW(), NULL),
(2, 2, 'momo', '0900000002', 'NGUYEN VAN INSTRUCTOR 1', NULL, 'pending_verification', NOW(), NOW(), NULL),
(3, 2, 'bank', '9704220000099999', 'SAI TEN TAI KHOAN', NULL, 'rejected', NOW(), NOW(), NULL),
(4, 3, 'bank', '9704220000022222', 'TRAN THI INSTRUCTOR 2', '2026-06-01 09:30:00', 'inactive', NOW(), NOW(), NULL);

-- ==========================================================
-- 20. WITHDRAW_REQUESTS
-- Test cases covered: pending/approved/rejected/paid/cancelled withdraw request and snapshots.
-- ==========================================================
INSERT INTO withdraw_requests
(id, user_id, payout_account_id, amount, status, requested_at, approved_at, paid_at, rejected_reason, provider_payout_id, account_number_snapshot, account_name_snapshot, created_at, updated_at)
VALUES
(1, 2, 1, 100000.00, 'pending', '2026-06-05 08:00:00', NULL, NULL, NULL, NULL, '9704220000011111', 'NGUYEN VAN INSTRUCTOR 1', NOW(), NOW()),
(2, 2, 1, 150000.00, 'approved', '2026-06-04 08:00:00', '2026-06-04 12:00:00', NULL, NULL, NULL, '9704220000011111', 'NGUYEN VAN INSTRUCTOR 1', NOW(), NOW()),
(3, 2, 1, 80000.00, 'rejected', '2026-06-03 08:00:00', NULL, NULL, 'Số tiền yêu cầu vượt số dư khả dụng tại thời điểm duyệt.', NULL, '9704220000011111', 'NGUYEN VAN INSTRUCTOR 1', NOW(), NOW()),
(4, 2, 1, 200000.00, 'paid', '2026-06-02 08:00:00', '2026-06-02 10:00:00', '2026-06-02 15:00:00', NULL, 'BANK-PAYOUT-0004', '9704220000011111', 'NGUYEN VAN INSTRUCTOR 1', NOW(), NOW()),
(5, 2, 1, 50000.00, 'cancelled', '2026-06-01 08:00:00', NULL, NULL, NULL, NULL, '9704220000011111', 'NGUYEN VAN INSTRUCTOR 1', NOW(), NOW());

-- ==========================================================
-- 21. BANNERS
-- Test cases covered: active/inactive banner, sort_order, position.
-- ==========================================================
INSERT INTO banners
(id, title, image_url, target_url, position, sort_order, start_at, end_at, status, created_at, updated_at, deleted_at)
VALUES
(1, 'Banner khóa Laravel nổi bật', '/uploads/banners/banner-laravel.jpg', '/courses/laravel-rest-api-co-ban', 'home', 1, '2026-06-01 00:00:00', '2026-12-31 23:59:59', 'active', NOW(), NOW(), NULL),
(2, 'Banner khóa Git miễn phí', '/uploads/banners/banner-git.jpg', '/courses/git-co-ban-mien-phi', 'home', 2, '2026-06-01 00:00:00', '2026-12-31 23:59:59', 'active', NOW(), NOW(), NULL),
(3, 'Banner inactive để test', '/uploads/banners/banner-inactive.jpg', NULL, 'home', 99, NULL, NULL, 'inactive', NOW(), NOW(), NULL);

-- ==========================================================
-- 22. FAQS + COURSE_FAQS
-- Test cases covered: general/course FAQ, active/inactive FAQ, soft delete relation.
-- ==========================================================
INSERT INTO faqs
(id, question, answer, type, status, sort_order, created_at, updated_at, deleted_at)
VALUES
(1, 'Tôi có thể học thử trước khi mua không?', 'Có. Một số bài học có trạng thái preview cho phép xem trước.', 'course', 'active', 1, NOW(), NOW(), NULL),
(2, 'Mua khóa học xong có học vĩnh viễn không?', 'Trong giai đoạn 1, enrollment active cho phép học khóa đã mua.', 'course', 'active', 2, NOW(), NOW(), NULL),
(3, 'Thanh toán thất bại thì có được cấp quyền học không?', 'Không. Chỉ order paid mới được tạo enrollment.', 'payment', 'active', 3, NOW(), NOW(), NULL),
(4, 'FAQ inactive để test', 'Nội dung này không hiển thị public.', 'general', 'inactive', 99, NOW(), NOW(), NULL);

INSERT INTO course_faqs
(faq_id, course_id, sort_order, created_at, deleted_at)
VALUES
(1, 1, 1, NOW(), NULL),
(2, 1, 2, NOW(), NULL),
(3, 1, 3, NOW(), NULL),
(1, 7, 1, NOW(), NULL);

-- ==========================================================
-- 23. QUIZZES
-- Test cases covered: published/draft/hidden quiz linked to course/lesson.
-- ==========================================================
INSERT INTO quizzes
(id, course_id, lesson_id, title, description, passing_score, status, created_at, updated_at, deleted_at)
VALUES
(1, 1, 4, 'Quiz Laravel Cơ Bản', 'Kiểm tra kiến thức route, controller, validation.', 6.00, 'published', NOW(), NOW(), NULL),
(2, 1, 3, 'Quiz ẩn để test', 'Quiz hidden không hiển thị public.', 5.00, 'hidden', NOW(), NOW(), NULL),
(3, 2, 6, 'Quiz MVC bản nháp', 'Quiz draft cho khóa pending_review.', 5.00, 'draft', NOW(), NOW(), NULL),
(4, 7, 9, 'Quiz Git Cơ Bản', 'Quiz cho khóa Git miễn phí.', 5.00, 'published', NOW(), NOW(), NULL);

-- ==========================================================
-- 24. QUIZ_QUESTIONS
-- Test cases covered: single_choice, multiple_choice, true_false.
-- ==========================================================
INSERT INTO quiz_questions
(id, quiz_id, question_text, question_type, score, sort_order, explanation, created_at)
VALUES
(1, 1, 'File routes/api.php dùng để làm gì?', 'single_choice', 3.00, 1, 'routes/api.php dùng để định nghĩa API route.', NOW()),
(2, 1, 'Những thành phần nào thường có trong Laravel REST API?', 'multiple_choice', 4.00, 2, 'Controller, FormRequest, Resource, Service thường xuất hiện trong API.', NOW()),
(3, 1, 'FormRequest dùng để validate dữ liệu đầu vào.', 'true_false', 3.00, 3, 'FormRequest giúp tách validation khỏi Controller.', NOW()),
(4, 4, 'Git commit dùng để lưu snapshot thay đổi.', 'true_false', 5.00, 1, 'Commit lưu lại một mốc thay đổi trong repository.', NOW());

-- ==========================================================
-- 25. QUIZ_OPTIONS
-- ==========================================================
INSERT INTO quiz_options
(id, question_id, option_text, is_correct, sort_order, created_at)
VALUES
(1, 1, 'Định nghĩa các API route', TRUE, 1, NOW()),
(2, 1, 'Lưu cấu hình database', FALSE, 2, NOW()),
(3, 1, 'Chứa giao diện Blade', FALSE, 3, NOW()),
(4, 1, 'Lưu file upload của người dùng', FALSE, 4, NOW()),

(5, 2, 'Controller', TRUE, 1, NOW()),
(6, 2, 'FormRequest', TRUE, 2, NOW()),
(7, 2, 'Resource', TRUE, 3, NOW()),
(8, 2, 'File CSS bắt buộc trong mọi API', FALSE, 4, NOW()),

(9, 3, 'Đúng', TRUE, 1, NOW()),
(10, 3, 'Sai', FALSE, 2, NOW()),

(11, 4, 'Đúng', TRUE, 1, NOW()),
(12, 4, 'Sai', FALSE, 2, NOW());

-- ==========================================================
-- 26. QUIZ_ATTEMPTS
-- Test cases covered: in_progress, submitted passed, submitted failed, duplicate attempt_number.
-- ==========================================================
INSERT INTO quiz_attempts
(id, quiz_id, user_id, attempt_number, score, total_score, passed, status, started_at, submitted_at, created_at)
VALUES
(1, 1, 4, 1, 10.00, 10.00, TRUE, 'submitted', '2026-06-05 08:00:00', '2026-06-05 08:10:00', NOW()),
(2, 1, 4, 2, NULL, 10.00, FALSE, 'in_progress', '2026-06-05 09:00:00', NULL, NOW()),
(3, 1, 9, 1, 3.00, 10.00, FALSE, 'submitted', '2026-06-05 09:30:00', '2026-06-05 09:40:00', NOW()),
(4, 4, 5, 1, NULL, 5.00, FALSE, 'in_progress', '2026-06-05 10:00:00', NULL, NOW());

-- ==========================================================
-- 27. QUIZ_ATTEMPT_ANSWERS
-- ==========================================================
INSERT INTO quiz_attempt_answers
(id, question_id, attempt_id, option_id, is_correct, score_earned, created_at)
VALUES
(1, 1, 1, 1, TRUE, 3.00, NOW()),
(2, 2, 1, 5, TRUE, 4.00, NOW()),
(3, 3, 1, 9, TRUE, 3.00, NOW()),

(4, 1, 3, 2, FALSE, 0.00, NOW()),
(5, 2, 3, 8, FALSE, 0.00, NOW()),
(6, 3, 3, 9, TRUE, 3.00, NOW());

COMMIT;

-- ==========================================================
-- TEST ACCOUNT SUMMARY
-- ==========================================================
-- admin@mindhub.test              role=admin       status=active
-- instructor1@mindhub.test        role=instructor  status=active
-- instructor2@mindhub.test        role=instructor  status=active
-- learner1@mindhub.test           role=learner     status=active, đã mua Laravel + Git
-- learner2@mindhub.test           role=learner     status=active, chưa mua Laravel, đã có order pending/failed/free Git
-- learner.completed@mindhub.test  role=learner     status=active, đã hoàn thành khóa
-- locked@mindhub.test             role=learner     status=locked
-- inactive@mindhub.test           role=learner     status=inactive
-- oauth.learner@mindhub.test      role=learner     status=active, OAuth account
--
-- Password test quy ước: 12345678
-- Lưu ý: password_hash trong file là seed placeholder. Khi dùng Laravel thực tế, nên tạo bằng Hash::make('12345678').
-- ==========================================================
