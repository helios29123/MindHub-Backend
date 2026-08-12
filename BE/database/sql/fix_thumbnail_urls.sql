-- =============================================================================
-- FIX THUMBNAIL URLS — MindHub
-- =============================================================================
-- Bối cảnh:
--   Ảnh public phục vụ từ site "mindhub-media" (/www/wwwroot/mindhub-media),
--   truy cập qua base URL: https://mindhub.io.vn/mindhub-media
--   (Frontend đã đổi VITE_MEDIA_BASE_URL sang base này.)
--
--   Cột courses.thumbnail_url lưu path tương đối, ví dụ: /thumbnails/backend_interview.jpg
--   -> URL cuối = https://mindhub.io.vn/mindhub-media/thumbnails/backend_interview.jpg
--
-- Sau khi đổi base, 15/27 ảnh đã hiển thị. 12 khóa còn lỗi do thumbnail_url
-- trỏ sai prefix (/demo/courses/... hoặc /thumbnails/courses/...) — các thư mục
-- đó không tồn tại trong mindhub-media.
--
-- Chia làm 2 nhóm:
--   (A) 3 khóa: file đích ĐÃ CÓ sẵn -> chỉ cần chạy UPDATE là xong.
--   (B) 9 khóa: file CHƯA có -> phải UPLOAD ảnh vào /www/wwwroot/mindhub-media/thumbnails/
--       trước, rồi mới chạy UPDATE (nếu chạy trước, ảnh vẫn 404 tới khi upload).
--
-- Khuyến nghị: sao lưu trước khi chạy.
--   mysqldump -u <user> -p mindhub courses > courses_backup.sql
-- =============================================================================


-- -----------------------------------------------------------------------------
-- (A) ÁP DỤNG ĐƯỢC NGAY — file đích đã tồn tại trên mindhub-media/thumbnails
-- -----------------------------------------------------------------------------
UPDATE courses SET thumbnail_url = '/thumbnails/ai_learning.jpg'
  WHERE thumbnail_url = '/demo/courses/ai-learning.jpg';

UPDATE courses SET thumbnail_url = '/thumbnails/react_elearning.jpg'
  WHERE thumbnail_url = '/demo/courses/react-elearning.jpg';

UPDATE courses SET thumbnail_url = '/thumbnails/git-github.jpg'
  WHERE thumbnail_url = '/demo/courses/git-github.jpg';


-- -----------------------------------------------------------------------------
-- (B) CHỈ CHẠY SAU KHI ĐÃ UPLOAD 9 FILE ẢNH DƯỚI ĐÂY VÀO:
--     /www/wwwroot/mindhub-media/thumbnails/
--
--     Danh sách 9 file cần upload (đúng tên):
--       1. php-mysql.jpg
--       2. content-marketing-web.jpg
--       3. landing-page-conversion.jpg
--       4. present-web-project.jpg
--       5. problem-solving-webdev.jpg
--       6. seo-for-webdev.jpg
--       7. soft-communication-it.jpg
--       8. teamwork-agile-web.jpg
--       9. web-analytics-ab-testing.jpg
-- -----------------------------------------------------------------------------
UPDATE courses SET thumbnail_url = '/thumbnails/php-mysql.jpg'
  WHERE thumbnail_url = '/demo/courses/php-mysql.jpg';

UPDATE courses SET thumbnail_url = '/thumbnails/content-marketing-web.jpg'
  WHERE thumbnail_url = '/thumbnails/courses/content-marketing-web.jpg';

UPDATE courses SET thumbnail_url = '/thumbnails/landing-page-conversion.jpg'
  WHERE thumbnail_url = '/thumbnails/courses/landing-page-conversion.jpg';

UPDATE courses SET thumbnail_url = '/thumbnails/present-web-project.jpg'
  WHERE thumbnail_url = '/thumbnails/courses/present-web-project.jpg';

UPDATE courses SET thumbnail_url = '/thumbnails/problem-solving-webdev.jpg'
  WHERE thumbnail_url = '/thumbnails/courses/problem-solving-webdev.jpg';

UPDATE courses SET thumbnail_url = '/thumbnails/seo-for-webdev.jpg'
  WHERE thumbnail_url = '/thumbnails/courses/seo-for-webdev.jpg';

UPDATE courses SET thumbnail_url = '/thumbnails/soft-communication-it.jpg'
  WHERE thumbnail_url = '/thumbnails/courses/soft-communication-it.jpg';

UPDATE courses SET thumbnail_url = '/thumbnails/teamwork-agile-web.jpg'
  WHERE thumbnail_url = '/thumbnails/courses/teamwork-agile-web.jpg';

UPDATE courses SET thumbnail_url = '/thumbnails/web-analytics-ab-testing.jpg'
  WHERE thumbnail_url = '/thumbnails/courses/web-analytics-ab-testing.jpg';


-- -----------------------------------------------------------------------------
-- (C) TÙY CHỌN — dọn lỗi tên file có dấu cách (hiện vẫn tải được nhờ browser
--     tự encode %20, nhưng nên chuẩn hóa). CHỈ chạy nếu bạn đã đổi tên file
--     trên đĩa từ "mvp_ web_product.jpg" thành "mvp_web_product.jpg".
-- -----------------------------------------------------------------------------
-- UPDATE courses SET thumbnail_url = '/thumbnails/mvp_web_product.jpg'
--   WHERE thumbnail_url = '/thumbnails/mvp_ web_product.jpg';


-- -----------------------------------------------------------------------------
-- (D) KIỂM TRA — sau khi chạy, không còn dòng nào trỏ prefix sai:
-- -----------------------------------------------------------------------------
SELECT id, slug, thumbnail_url
FROM courses
WHERE thumbnail_url LIKE '/demo/courses/%'
   OR thumbnail_url LIKE '/thumbnails/courses/%';
-- Kỳ vọng: 0 rows (hoặc chỉ còn các khóa thuộc nhóm B chưa upload).
