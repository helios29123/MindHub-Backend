-- =============================================================================
-- FILE: PHATNT_INSTRUCTOR_DEMO_RELATION_FIX.sql
-- MỤC ĐÍCH: Sửa ownership, relation foreign key và bổ sung dữ liệu mẫu
--          đồng nhất cho Giảng viên MindHub 01 (instructor1@mindhub.test)
-- DATABASE: phatnt
-- =============================================================================

START TRANSACTION;

-- 1. Truy vấn động User ID và Instructor Profile ID
SET @instructor_user_id := (
    SELECT id FROM users WHERE email = 'instructor1@mindhub.test' LIMIT 1
);

SET @instructor_profile_id := (
    SELECT id FROM instructor_profiles WHERE user_id = @instructor_user_id LIMIT 1
);

SET @first_category_id := (
    SELECT id FROM categories LIMIT 1
);

-- Nếu chưa có instructor_profile thì tự bổ sung
INSERT INTO instructor_profiles (user_id, bio, expertise, experience_years, level, social_links, created_at, updated_at)
SELECT @instructor_user_id, 'Giảng viên Fullstack với hơn 8 năm kinh nghiệm giảng dạy và phát triển ứng dụng web.', 'Senior Fullstack Engineer', 8, 'Senior', '{"website":"https://mindhub.test"}', NOW(), NOW()
FROM DUAL
WHERE NOT EXISTS (SELECT 1 FROM instructor_profiles WHERE user_id = @instructor_user_id);

-- 2. Đảm bảo toàn bộ 30 khóa học demo thuộc về @instructor_user_id
UPDATE courses 
SET instructor_id = @instructor_user_id 
WHERE id BETWEEN 930001 AND 930030 OR instructor_id = 6 OR instructor_id IS NULL;

-- 3. Chuẩn hóa phân bổ Status 30 khóa học cho Giảng viên 01
-- 14 published, 2 approved, 5 draft, 4 pending_review, 3 rejected, 2 hidden (Tổng = 30)

UPDATE courses SET status = 'published' WHERE id BETWEEN 930001 AND 930014;
UPDATE courses SET status = 'approved' WHERE id BETWEEN 930023 AND 930024;
UPDATE courses SET status = 'draft' WHERE id BETWEEN 930015 AND 930017 OR id IN (930025, 930026);
UPDATE courses SET status = 'pending_review' WHERE id BETWEEN 930018 AND 930020 OR id = 930027;
UPDATE courses SET status = 'rejected' WHERE id BETWEEN 930021 AND 930022 OR id = 930028;
UPDATE courses SET status = 'hidden' WHERE id IN (930029, 930030);

-- 4. Bổ sung các khóa học demo 930025 -> 930030 nếu chưa có
INSERT INTO courses (id, instructor_id, title, slug, short_description, description, price, sale_price, level, language, status, is_featured, total_duration_seconds, created_at, updated_at)
SELECT 930025, @instructor_user_id, 'TypeScript Advanced for Enterprise', 'typescript-advanced-enterprise', 'Khóa học TypeScript nâng cao', 'Nội dung chi tiết', 599000, 399000, 'advanced', 'vi', 'draft', 0, 3600, NOW(), NOW()
FROM DUAL WHERE NOT EXISTS (SELECT 1 FROM courses WHERE id = 930025);

INSERT INTO courses (id, instructor_id, title, slug, short_description, description, price, sale_price, level, language, status, is_featured, total_duration_seconds, created_at, updated_at)
SELECT 930026, @instructor_user_id, 'GraphQL with Node.js & React', 'graphql-nodejs-react', 'Khóa học GraphQL', 'Nội dung chi tiết', 499000, 299000, 'intermediate', 'vi', 'draft', 0, 4800, NOW(), NOW()
FROM DUAL WHERE NOT EXISTS (SELECT 1 FROM courses WHERE id = 930026);

INSERT INTO courses (id, instructor_id, title, slug, short_description, description, price, sale_price, level, language, status, is_featured, total_duration_seconds, created_at, updated_at)
SELECT 930027, @instructor_user_id, 'Microservices Architecture with NestJS', 'microservices-nestjs', 'Kiến trúc Microservices', 'Nội dung chi tiết', 799000, 599000, 'advanced', 'vi', 'pending_review', 0, 7200, NOW(), NOW()
FROM DUAL WHERE NOT EXISTS (SELECT 1 FROM courses WHERE id = 930027);

INSERT INTO courses (id, instructor_id, title, slug, short_description, description, price, sale_price, level, language, status, is_featured, total_duration_seconds, created_at, updated_at)
SELECT 930028, @instructor_user_id, 'Tối ưu hóa Database MySQL 8.0', 'tai-uu-database-mysql', 'Tối ưu database nâng cao', 'Nội dung chi tiết', 399000, 199000, 'intermediate', 'vi', 'rejected', 0, 2500, NOW(), NOW()
FROM DUAL WHERE NOT EXISTS (SELECT 1 FROM courses WHERE id = 930028);

INSERT INTO courses (id, instructor_id, title, slug, short_description, description, price, sale_price, level, language, status, is_featured, total_duration_seconds, created_at, updated_at)
SELECT 930029, @instructor_user_id, 'TailwindCSS 4.0 Pro Mastery', 'tailwindcss-4-pro-mastery', 'Khóa học TailwindCSS mới nhất', 'Nội dung chi tiết', 299000, 199000, 'beginner', 'vi', 'hidden', 0, 1800, NOW(), NOW()
FROM DUAL WHERE NOT EXISTS (SELECT 1 FROM courses WHERE id = 930029);

INSERT INTO courses (id, instructor_id, title, slug, short_description, description, price, sale_price, level, language, status, is_featured, total_duration_seconds, created_at, updated_at)
SELECT 930030, @instructor_user_id, 'DevOps CI/CD với GitHub Actions', 'devops-cicd-github-actions', 'Khóa học DevOps thực chiến', 'Nội dung chi tiết', 699000, 499000, 'advanced', 'vi', 'hidden', 0, 5400, NOW(), NOW()
FROM DUAL WHERE NOT EXISTS (SELECT 1 FROM courses WHERE id = 930030);

-- 5. Cập nhật mối quan hệ revenues trỏ đúng @instructor_user_id
UPDATE revenues 
SET instructor_id = @instructor_user_id 
WHERE course_id IN (SELECT id FROM courses WHERE instructor_id = @instructor_user_id);

-- 6. Đảm bảo danh mục cho các khóa học demo
INSERT INTO course_categories (course_id, category_id, created_at)
SELECT c.id, @first_category_id, NOW()
FROM courses c
WHERE c.instructor_id = @instructor_user_id
  AND NOT EXISTS (SELECT 1 FROM course_categories cc WHERE cc.course_id = c.id);

COMMIT;
