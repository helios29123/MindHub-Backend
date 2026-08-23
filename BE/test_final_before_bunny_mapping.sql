/*M!999999\- enable the sandbox mode */ 
-- MariaDB dump 10.19-12.3.2-MariaDB, for Linux (x86_64)
--
-- Host: localhost    Database: test_final
-- ------------------------------------------------------
-- Server version	12.3.2-MariaDB

/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;
/*!40103 SET @OLD_TIME_ZONE=@@TIME_ZONE */;
/*!40103 SET TIME_ZONE='+00:00' */;
/*!40014 SET @OLD_UNIQUE_CHECKS=@@UNIQUE_CHECKS, UNIQUE_CHECKS=0 */;
/*!40014 SET @OLD_FOREIGN_KEY_CHECKS=@@FOREIGN_KEY_CHECKS, FOREIGN_KEY_CHECKS=0 */;
/*!40101 SET @OLD_SQL_MODE=@@SQL_MODE, SQL_MODE='NO_AUTO_VALUE_ON_ZERO' */;
/*M!100616 SET @OLD_NOTE_VERBOSITY=@@NOTE_VERBOSITY, NOTE_VERBOSITY=0 */;

--
-- Table structure for table `banners`
--

DROP TABLE IF EXISTS `banners`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `banners` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `title` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `image_url` varchar(2048) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `image_public_id` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `target_url` varchar(2048) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `position` varchar(100) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `sort_order` int(10) unsigned NOT NULL DEFAULT 0,
  `start_at` datetime DEFAULT NULL,
  `end_at` datetime DEFAULT NULL,
  `status` enum('active','inactive') CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'active',
  `created_at` datetime NOT NULL DEFAULT current_timestamp(),
  `updated_at` datetime NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  PRIMARY KEY (`id`),
  UNIQUE KEY `uq_banners_image_public_id` (`image_public_id`),
  KEY `idx_banners_position_status_order` (`position`,`status`,`sort_order`)
) ENGINE=InnoDB AUTO_INCREMENT=4 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_uca1400_ai_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `banners`
--

SET @OLD_AUTOCOMMIT=@@AUTOCOMMIT, @@AUTOCOMMIT=0;
LOCK TABLES `banners` WRITE;
/*!40000 ALTER TABLE `banners` DISABLE KEYS */;
INSERT INTO `banners` VALUES
(1,'Banner khóa Laravel nổi bật','https://example.com/laravel-updated.jpg',NULL,'https://example.com/courses/laravel-new','home',1,'2026-06-10 00:00:00','2026-06-20 00:00:00','active','2026-01-01 08:00:00','2026-08-10 07:15:24'),
(2,'Banner khóa Git miễn phí','/demo/banners/banner-ai-learning.jpg',NULL,'/courses/ai-ung-dung-cho-hoc-tap-ca-nhan-hoa','home',2,'2026-01-01 00:00:00','2026-12-31 23:59:59','active','2026-01-01 08:10:00','2026-08-10 07:15:24'),
(3,'Banner inactive để test','/demo/banners/banner-inactive.jpg',NULL,NULL,'home',3,'2026-01-01 00:00:00','2026-12-31 23:59:59','inactive','2026-01-01 08:20:00','2026-08-10 07:15:24');
/*!40000 ALTER TABLE `banners` ENABLE KEYS */;
UNLOCK TABLES;
COMMIT;
SET AUTOCOMMIT=@OLD_AUTOCOMMIT;

--
-- Table structure for table `categories`
--

DROP TABLE IF EXISTS `categories`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `categories` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `parent_id` bigint(20) unsigned DEFAULT NULL,
  `name` varchar(150) NOT NULL,
  `slug` varchar(180) NOT NULL,
  `description` text DEFAULT NULL,
  `sort_order` int(10) unsigned NOT NULL DEFAULT 0,
  `status` enum('active','inactive') NOT NULL DEFAULT 'active',
  `created_at` datetime NOT NULL DEFAULT current_timestamp(),
  `updated_at` datetime NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  PRIMARY KEY (`id`),
  UNIQUE KEY `uq_categories_slug` (`slug`),
  KEY `idx_categories_parent` (`parent_id`),
  CONSTRAINT `fk_categories_parent` FOREIGN KEY (`parent_id`) REFERENCES `categories` (`id`) ON DELETE SET NULL ON UPDATE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=44 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `categories`
--

SET @OLD_AUTOCOMMIT=@@AUTOCOMMIT, @@AUTOCOMMIT=0;
LOCK TABLES `categories` WRITE;
/*!40000 ALTER TABLE `categories` DISABLE KEYS */;
INSERT INTO `categories` VALUES
(1,NULL,'Lập trình','lap-trinh','Các khóa học lập trình từ cơ bản đến nâng cao.',1,'active','2026-01-03 08:00:00','2026-01-03 08:00:00'),
(2,1,'Web Development','web-development','Frontend, backend và full-stack web.',1,'active','2026-01-03 08:05:00','2026-01-03 08:05:00'),
(3,1,'Backend','backend','API, cơ sở dữ liệu, bảo mật backend.',2,'active','2026-01-03 08:10:00','2026-01-03 08:10:00'),
(4,1,'Frontend','frontend','Giao diện người dùng hiện đại.',3,'active','2026-01-03 08:15:00','2026-01-03 08:15:00'),
(5,NULL,'AI và Dữ liệu','ai-va-du-lieu','AI ứng dụng, phân tích dữ liệu và tự động hóa.',2,'active','2026-01-03 08:20:00','2026-01-03 08:20:00'),
(6,NULL,'DevOps','devops','Triển khai, Docker, CI/CD và vận hành.',3,'active','2026-01-03 08:25:00','2026-01-03 08:25:00'),
(7,NULL,'Kinh doanh số','kinh-doanh-so','Danh mục inactive để test filter public.',4,'inactive','2026-01-03 08:30:00','2026-01-03 08:30:00'),
(8,NULL,'AI & Học tập thông minh','ai-hoc-tap-thong-minh','Danh mục demo MindHub: AI & Học tập thông minh.',5,'active','2026-06-27 08:00:00','2026-08-07 00:47:10'),
(9,8,'AI Learning','ai-learning','Danh mục demo MindHub: AI Learning.',1,'active','2026-06-27 08:00:00','2026-06-27 08:00:00'),
(10,NULL,'Backend & API Testing','backend-api-testing','Danh mục demo MindHub: Backend & API Testing.',6,'active','2026-06-27 08:00:00','2026-08-07 00:47:10'),
(11,10,'Postman API Testing','postman-api-testing','Danh mục demo MindHub: Postman API Testing.',1,'active','2026-06-27 08:00:00','2026-06-27 08:00:00'),
(12,NULL,'Công cụ lập trình & Làm việc nhóm','cong-cu-lap-trinh-lam-viec-nhom','Danh mục demo MindHub: Công cụ lập trình & Làm việc nhóm.',7,'active','2026-06-27 08:00:00','2026-08-07 00:47:10'),
(13,12,'Git & GitHub','git-github','Danh mục demo MindHub: Git & GitHub.',1,'active','2026-06-27 08:00:00','2026-06-27 08:00:00'),
(14,NULL,'Database & Data Modeling','database-data-modeling','Danh mục demo MindHub: Database & Data Modeling.',8,'active','2026-06-27 08:00:00','2026-08-07 00:47:10'),
(15,14,'MySQL Database Design','mysql-database-design','Danh mục demo MindHub: MySQL Database Design.',1,'active','2026-06-27 08:00:00','2026-06-27 08:00:00'),
(16,NULL,'DevOps & Triển khai','devops-trien-khai','Danh mục demo MindHub: DevOps & Triển khai.',9,'active','2026-06-27 08:00:00','2026-08-07 00:47:10'),
(17,16,'Deploy VPS aaPanel','deploy-vps-aapanel','Danh mục demo MindHub: Deploy VPS aaPanel.',1,'active','2026-06-27 08:00:00','2026-06-27 08:00:00'),
(18,NULL,'Frontend UI UX','frontend-ui-ux','Danh mục demo MindHub: Frontend UI UX.',10,'active','2026-06-27 08:00:00','2026-08-07 00:47:10'),
(19,18,'Tailwind CSS','tailwind-css','Danh mục demo MindHub: Tailwind CSS.',1,'active','2026-06-27 08:00:00','2026-06-27 08:00:00'),
(20,NULL,'Kinh doanh số & Freelance Web','kinh-doanh-so-va-freelance-web','Danh mục demo MindHub: Kinh doanh số & Freelance Web.',11,'active','2026-06-27 08:00:00','2026-08-07 00:47:11'),
(21,20,'Freelance Web Developer','freelance-web-developer','Danh mục demo MindHub: Freelance Web Developer.',1,'active','2026-06-27 08:00:00','2026-06-27 08:00:00'),
(22,20,'Sản phẩm số & SaaS','san-pham-so-va-saas','Danh mục demo MindHub: Sản phẩm số & SaaS.',2,'active','2026-06-27 08:00:00','2026-06-27 08:00:00'),
(23,NULL,'Kỹ năng mềm cho lập trình viên','ky-nang-mem-cho-lap-trinh-vien','Danh mục demo MindHub: Kỹ năng mềm cho lập trình viên.',12,'active','2026-06-27 08:00:00','2026-08-07 00:47:11'),
(24,23,'Giao tiếp & Làm việc nhóm','giao-tiep-va-lam-viec-nhom','Danh mục demo MindHub: Giao tiếp & Làm việc nhóm.',1,'active','2026-06-27 08:00:00','2026-06-27 08:00:00'),
(25,23,'Tư duy nghề nghiệp & Phỏng vấn','tu-duy-nghe-nghiep-va-phong-van','Danh mục demo MindHub: Tư duy nghề nghiệp & Phỏng vấn.',2,'active','2026-06-27 08:00:00','2026-06-27 08:00:00'),
(26,NULL,'Lập trình Web','lap-trinh-web','Danh mục demo MindHub: Lập trình Web.',13,'active','2026-06-27 08:00:00','2026-08-07 00:47:11'),
(27,26,'Backend Laravel','backend-laravel','Danh mục demo MindHub: Backend Laravel.',1,'active','2026-06-27 08:00:00','2026-06-27 08:00:00'),
(28,26,'Frontend React','frontend-react','Danh mục demo MindHub: Frontend React.',2,'active','2026-06-27 08:00:00','2026-06-27 08:00:00'),
(29,NULL,'Marketing Internet cho Web Developer','marketing-internet-cho-web-developer','Danh mục demo MindHub: Marketing Internet cho Web Developer.',14,'active','2026-06-27 08:00:00','2026-08-07 00:47:11'),
(30,29,'Landing Page & Conversion','landing-page-va-conversion','Danh mục demo MindHub: Landing Page & Conversion.',1,'active','2026-06-27 08:00:00','2026-06-27 08:00:00'),
(31,29,'SEO & Content Website','seo-va-content-website','Danh mục demo MindHub: SEO & Content Website.',2,'active','2026-06-27 08:00:00','2026-06-27 08:00:00'),
(32,NULL,'Payment & E-commerce','payment-e-commerce','Danh mục demo MindHub: Payment & E-commerce.',15,'active','2026-06-27 08:00:00','2026-08-07 00:47:11'),
(33,32,'VNPay Laravel','vnpay-laravel','Danh mục demo MindHub: VNPay Laravel.',1,'active','2026-06-27 08:00:00','2026-06-27 08:00:00'),
(34,NULL,'Định hướng nghề nghiệp & Phỏng vấn','dinh-huong-nghe-nghiep-phong-van','Danh mục demo MindHub: Định hướng nghề nghiệp & Phỏng vấn.',16,'active','2026-06-27 08:00:00','2026-08-07 00:47:11'),
(35,34,'Lộ trình Web Developer','lo-trinh-web-developer','Danh mục demo MindHub: Lộ trình Web Developer.',1,'active','2026-06-27 08:00:00','2026-06-27 08:00:00'),
(36,34,'Phỏng vấn Backend Developer','phong-van-backend-developer','Danh mục demo MindHub: Phỏng vấn Backend Developer.',2,'active','2026-06-27 08:00:00','2026-06-27 08:00:00'),
(37,NULL,'CATEGORY_ACTIVE Programming','cat-category-active-programming','Danh mục lập trình active.',1,'active','2026-08-06 17:47:13','2026-08-06 17:47:13'),
(38,NULL,'CATEGORY_ACTIVE Design','cat-category-active-design','Danh mục design active.',2,'active','2026-08-06 17:47:13','2026-08-06 17:47:13'),
(39,NULL,'CATEGORY_ACTIVE Marketing','cat-category-active-marketing','Danh mục marketing active.',3,'active','2026-08-06 17:47:13','2026-08-06 17:47:13'),
(40,37,'CATEGORY_ACTIVE Laravel Child','cat-category-active-laravel-child','Danh mục con Laravel active.',1,'active','2026-08-06 17:47:13','2026-08-06 17:47:13'),
(41,37,'CATEGORY_ACTIVE PHP Child','cat-category-active-php-child','Danh mục con PHP active.',2,'active','2026-08-06 17:47:13','2026-08-06 17:47:13'),
(42,NULL,'CATEGORY_INACTIVE Hidden','cat-category-inactive-hidden','Danh mục inactive, không nên public.',4,'inactive','2026-08-06 17:47:13','2026-08-06 17:47:13'),
(43,NULL,'CATEGORY_ACTIVE Soft Deleted Not Public','cat-category-active-soft-deleted','Danh mục active nhưng soft deleted.',5,'active','2026-08-06 17:47:13','2026-08-06 17:47:13');
/*!40000 ALTER TABLE `categories` ENABLE KEYS */;
UNLOCK TABLES;
COMMIT;
SET AUTOCOMMIT=@OLD_AUTOCOMMIT;

--
-- Table structure for table `comments`
--

DROP TABLE IF EXISTS `comments`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `comments` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `parent_id` bigint(20) unsigned DEFAULT NULL,
  `enrollment_id` bigint(20) unsigned DEFAULT NULL,
  `user_id` bigint(20) unsigned NOT NULL,
  `lesson_id` bigint(20) unsigned NOT NULL,
  `content` text CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `status` enum('visible','hidden','deleted') CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'visible',
  `is_official` tinyint(1) NOT NULL DEFAULT 0,
  `created_at` datetime NOT NULL DEFAULT current_timestamp(),
  `updated_at` datetime NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  PRIMARY KEY (`id`),
  KEY `idx_comments_parent` (`parent_id`),
  KEY `idx_comments_enrollment` (`enrollment_id`),
  KEY `idx_comments_user` (`user_id`),
  KEY `idx_comments_lesson_status` (`lesson_id`,`status`,`created_at`),
  CONSTRAINT `fk_comments_enrollment` FOREIGN KEY (`enrollment_id`) REFERENCES `enrollments` (`id`) ON DELETE SET NULL ON UPDATE CASCADE,
  CONSTRAINT `fk_comments_lesson` FOREIGN KEY (`lesson_id`) REFERENCES `lessons` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT `fk_comments_parent` FOREIGN KEY (`parent_id`) REFERENCES `comments` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT `fk_comments_user` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON UPDATE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=5 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_uca1400_ai_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `comments`
--

SET @OLD_AUTOCOMMIT=@@AUTOCOMMIT, @@AUTOCOMMIT=0;
LOCK TABLES `comments` WRITE;
/*!40000 ALTER TABLE `comments` DISABLE KEYS */;
INSERT INTO `comments` VALUES
(1,NULL,1,4,3,'Em muốn hỏi refresh_token_hash nên so sánh trực tiếp hay dùng hash_equals?','visible',1,'2026-06-22 20:40:00','2026-08-10 07:15:23'),
(2,1,NULL,2,3,'Nên hash token client gửi lên rồi dùng so sánh an toàn, không lưu refresh token thô trong DB.','visible',1,'2026-06-22 21:00:00','2026-08-10 07:15:23'),
(3,NULL,2,6,6,'Flow paid -> enrollment -> revenue rất hữu ích khi test payment.','hidden',1,'2026-05-03 09:10:00','2026-08-10 07:15:23'),
(4,NULL,1,4,2,'Comment hidden dùng để test moderation.','deleted',1,'2026-06-20 09:00:00','2026-08-10 07:15:23');
/*!40000 ALTER TABLE `comments` ENABLE KEYS */;
UNLOCK TABLES;
COMMIT;
SET AUTOCOMMIT=@OLD_AUTOCOMMIT;

--
-- Table structure for table `commission_rules`
--

DROP TABLE IF EXISTS `commission_rules`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `commission_rules` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `name` varchar(150) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `description` varchar(1000) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `instructor_rate` decimal(5,4) NOT NULL,
  `platform_rate` decimal(5,4) NOT NULL,
  `is_active` tinyint(1) NOT NULL DEFAULT 0,
  `created_at` datetime NOT NULL DEFAULT current_timestamp(),
  `updated_at` datetime NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  PRIMARY KEY (`id`),
  KEY `idx_commission_rules_active` (`is_active`)
) ENGINE=InnoDB AUTO_INCREMENT=6 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_uca1400_ai_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `commission_rules`
--

SET @OLD_AUTOCOMMIT=@@AUTOCOMMIT, @@AUTOCOMMIT=0;
LOCK TABLES `commission_rules` WRITE;
/*!40000 ALTER TABLE `commission_rules` DISABLE KEYS */;
INSERT INTO `commission_rules` VALUES
(1,'Rule 1','Marketplace mặc định',0.7000,0.3000,1,'2026-08-06 17:47:14','2026-08-06 17:47:14'),
(2,'Rule 2','Nền tảng tự chạy quảng cáo',0.3700,0.6300,0,'2026-08-06 17:47:14','2026-08-06 17:47:14'),
(3,'Rule 3','Chiến dịch quảng bá của admin',0.3700,0.6300,0,'2026-08-06 17:47:14','2026-08-06 17:47:14'),
(4,'Rule 4','Mua bằng coupon do giảng viên tạo',0.9700,0.0300,0,'2026-08-06 17:47:14','2026-08-06 17:47:14'),
(5,'Rule 5','Link giới thiệu của giảng viên',0.9700,0.0300,0,'2026-08-06 17:47:14','2026-08-06 17:47:14');
/*!40000 ALTER TABLE `commission_rules` ENABLE KEYS */;
UNLOCK TABLES;
COMMIT;
SET AUTOCOMMIT=@OLD_AUTOCOMMIT;
/*!50003 SET @saved_cs_client      = @@character_set_client */ ;
/*!50003 SET @saved_cs_results     = @@character_set_results */ ;
/*!50003 SET @saved_col_connection = @@collation_connection */ ;
/*!50003 SET character_set_client  = utf8mb4 */ ;
/*!50003 SET character_set_results = utf8mb4 */ ;
/*!50003 SET collation_connection  = utf8mb4_uca1400_ai_ci */ ;
/*!50003 SET @saved_sql_mode       = @@sql_mode */ ;
/*!50003 SET sql_mode              = 'NO_AUTO_VALUE_ON_ZERO' */ ;
DELIMITER ;;
/*!50003 CREATE*/ /*!50017 DEFINER=``@`localhost`*/ /*!50003 TRIGGER `trg_commission_rules_one_active_bi` BEFORE INSERT ON `commission_rules` FOR EACH ROW BEGIN
    IF NEW.is_active = 1
       AND EXISTS (SELECT 1 FROM `commission_rules` WHERE `is_active` = 1) THEN
        SIGNAL SQLSTATE '45000'
            SET MESSAGE_TEXT = 'Only one commission rule can be active at a time';
    END IF;
END 
*/;;
DELIMITER ;
/*!50003 SET sql_mode              = @saved_sql_mode */ ;
/*!50003 SET character_set_client  = @saved_cs_client */ ;
/*!50003 SET character_set_results = @saved_cs_results */ ;
/*!50003 SET collation_connection  = @saved_col_connection */ ;
/*!50003 SET @saved_cs_client      = @@character_set_client */ ;
/*!50003 SET @saved_cs_results     = @@character_set_results */ ;
/*!50003 SET @saved_col_connection = @@collation_connection */ ;
/*!50003 SET character_set_client  = utf8mb4 */ ;
/*!50003 SET character_set_results = utf8mb4 */ ;
/*!50003 SET collation_connection  = utf8mb4_uca1400_ai_ci */ ;
/*!50003 SET @saved_sql_mode       = @@sql_mode */ ;
/*!50003 SET sql_mode              = 'NO_AUTO_VALUE_ON_ZERO' */ ;
DELIMITER ;;
/*!50003 CREATE*/ /*!50017 DEFINER=``@`localhost`*/ /*!50003 TRIGGER `trg_commission_rules_one_active_bu` BEFORE UPDATE ON `commission_rules` FOR EACH ROW BEGIN
    IF NEW.is_active = 1
       AND EXISTS (
           SELECT 1
           FROM `commission_rules`
           WHERE `is_active` = 1
             AND `id` <> OLD.`id`
       ) THEN
        SIGNAL SQLSTATE '45000'
            SET MESSAGE_TEXT = 'Only one commission rule can be active at a time';
    END IF;

    
    IF (NEW.instructor_rate <> OLD.instructor_rate
        OR NEW.platform_rate <> OLD.platform_rate)
       AND (
           EXISTS (SELECT 1 FROM `orders` WHERE `commission_rule_id` = OLD.`id`)
           OR EXISTS (SELECT 1 FROM `revenues` WHERE `commission_rule_id` = OLD.`id`)
       ) THEN
        SIGNAL SQLSTATE '45000'
            SET MESSAGE_TEXT = 'Referenced commission rule rates are immutable; create a new rule instead';
    END IF;
END 
*/;;
DELIMITER ;
/*!50003 SET sql_mode              = @saved_sql_mode */ ;
/*!50003 SET character_set_client  = @saved_cs_client */ ;
/*!50003 SET character_set_results = @saved_cs_results */ ;
/*!50003 SET collation_connection  = @saved_col_connection */ ;

--
-- Table structure for table `coupons`
--

DROP TABLE IF EXISTS `coupons`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `coupons` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `code` varchar(80) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `course_id` bigint(20) unsigned NOT NULL,
  `discount_type` enum('percent','fixed') CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `discount_value` decimal(15,2) NOT NULL,
  `usage_limit` int(10) unsigned DEFAULT NULL,
  `used_count` int(10) unsigned NOT NULL DEFAULT 0,
  `start_at` datetime DEFAULT NULL,
  `end_at` datetime DEFAULT NULL,
  `status` enum('active','inactive','expired','used_up') CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'active',
  `created_at` datetime NOT NULL DEFAULT current_timestamp(),
  `updated_at` datetime NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  PRIMARY KEY (`id`),
  UNIQUE KEY `uq_coupons_code` (`code`),
  KEY `idx_coupons_course_status` (`course_id`,`status`),
  CONSTRAINT `fk_coupons_course` FOREIGN KEY (`course_id`) REFERENCES `courses` (`id`) ON UPDATE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=8 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_uca1400_ai_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `coupons`
--

SET @OLD_AUTOCOMMIT=@@AUTOCOMMIT, @@AUTOCOMMIT=0;
LOCK TABLES `coupons` WRITE;
/*!40000 ALTER TABLE `coupons` DISABLE KEYS */;
INSERT INTO `coupons` VALUES
(6,'FREEGIT',7,'fixed',299000.00,100,0,'2026-01-01 00:00:00','2026-12-31 23:59:59','active','2026-01-01 10:25:00','2026-06-01 10:00:00'),
(7,'LARAVEL50',1,'percent',50.00,20,1,'2026-06-01 00:00:00','2026-12-31 23:59:59','active','2026-06-01 10:30:00','2026-06-01 10:30:00');
/*!40000 ALTER TABLE `coupons` ENABLE KEYS */;
UNLOCK TABLES;
COMMIT;
SET AUTOCOMMIT=@OLD_AUTOCOMMIT;

--
-- Table structure for table `course_categories`
--

DROP TABLE IF EXISTS `course_categories`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `course_categories` (
  `course_id` bigint(20) unsigned NOT NULL,
  `category_id` bigint(20) unsigned NOT NULL,
  PRIMARY KEY (`course_id`,`category_id`),
  KEY `idx_course_categories_category` (`category_id`,`course_id`),
  CONSTRAINT `fk_course_categories_category` FOREIGN KEY (`category_id`) REFERENCES `categories` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT `fk_course_categories_course` FOREIGN KEY (`course_id`) REFERENCES `courses` (`id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `course_categories`
--

SET @OLD_AUTOCOMMIT=@@AUTOCOMMIT, @@AUTOCOMMIT=0;
LOCK TABLES `course_categories` WRITE;
/*!40000 ALTER TABLE `course_categories` DISABLE KEYS */;
INSERT INTO `course_categories` VALUES
(1,2),
(2,2),
(3,2),
(1,3),
(2,3),
(4,3),
(5,3),
(8,3),
(3,4),
(6,5),
(7,6),
(9,9),
(10,11),
(11,13),
(12,15),
(13,17),
(14,19),
(15,21),
(16,21),
(17,22),
(18,22),
(19,24),
(20,24),
(21,25),
(22,25),
(23,28),
(24,30),
(25,30),
(26,31),
(27,31),
(28,33),
(29,35),
(30,36),
(31,37),
(32,37),
(33,37),
(34,38),
(31,40),
(32,41),
(35,42),
(36,42);
/*!40000 ALTER TABLE `course_categories` ENABLE KEYS */;
UNLOCK TABLES;
COMMIT;
SET AUTOCOMMIT=@OLD_AUTOCOMMIT;

--
-- Table structure for table `course_faqs`
--

DROP TABLE IF EXISTS `course_faqs`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `course_faqs` (
  `faq_id` bigint(20) unsigned NOT NULL,
  `course_id` bigint(20) unsigned NOT NULL,
  `sort_order` int(10) unsigned NOT NULL DEFAULT 0,
  PRIMARY KEY (`faq_id`,`course_id`),
  KEY `idx_course_faqs_course_order` (`course_id`,`sort_order`),
  CONSTRAINT `fk_course_faqs_course` FOREIGN KEY (`course_id`) REFERENCES `courses` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT `fk_course_faqs_faq` FOREIGN KEY (`faq_id`) REFERENCES `faqs` (`id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `course_faqs`
--

SET @OLD_AUTOCOMMIT=@@AUTOCOMMIT, @@AUTOCOMMIT=0;
LOCK TABLES `course_faqs` WRITE;
/*!40000 ALTER TABLE `course_faqs` DISABLE KEYS */;
INSERT INTO `course_faqs` VALUES
(1,1,1),
(2,1,2),
(3,1,3),
(1,6,1);
/*!40000 ALTER TABLE `course_faqs` ENABLE KEYS */;
UNLOCK TABLES;
COMMIT;
SET AUTOCOMMIT=@OLD_AUTOCOMMIT;

--
-- Table structure for table `course_reviews`
--

DROP TABLE IF EXISTS `course_reviews`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `course_reviews` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `order_id` bigint(20) unsigned NOT NULL,
  `rating` tinyint(3) unsigned NOT NULL,
  `comment` text CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `created_at` datetime NOT NULL DEFAULT current_timestamp(),
  `updated_at` datetime NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  PRIMARY KEY (`id`),
  UNIQUE KEY `uq_course_reviews_order` (`order_id`),
  CONSTRAINT `fk_course_reviews_order` FOREIGN KEY (`order_id`) REFERENCES `orders` (`id`) ON UPDATE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=5 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_uca1400_ai_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `course_reviews`
--

SET @OLD_AUTOCOMMIT=@@AUTOCOMMIT, @@AUTOCOMMIT=0;
LOCK TABLES `course_reviews` WRITE;
/*!40000 ALTER TABLE `course_reviews` DISABLE KEYS */;
INSERT INTO `course_reviews` VALUES
(1,1,5,'Khóa Laravel rất sát với đồ án, phần payment và custom session dễ hiểu.','2026-06-22 09:00:00','2026-08-10 07:15:23'),
(2,6,4,'Nội dung đầy đủ, có thể thêm nhiều ví dụ test API hơn.','2026-05-04 10:00:00','2026-08-10 07:15:23'),
(3,7,4,'Phần transaction MySQL giúp mình hiểu rõ xử lý dữ liệu tài chính.','2026-06-22 10:00:00','2026-08-10 07:15:23'),
(4,11,5,'REVIEW_DATA Laravel rất tốt, nội dung dễ hiểu.','2026-08-06 17:47:13','2026-08-10 07:15:23');
/*!40000 ALTER TABLE `course_reviews` ENABLE KEYS */;
UNLOCK TABLES;
COMMIT;
SET AUTOCOMMIT=@OLD_AUTOCOMMIT;

--
-- Table structure for table `course_sections`
--

DROP TABLE IF EXISTS `course_sections`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `course_sections` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `course_id` bigint(20) unsigned NOT NULL,
  `title` varchar(255) NOT NULL,
  `description` text DEFAULT NULL,
  `sort_order` int(10) unsigned NOT NULL DEFAULT 0,
  `status` enum('draft','published','hidden') NOT NULL DEFAULT 'draft',
  `created_at` datetime NOT NULL DEFAULT current_timestamp(),
  `updated_at` datetime NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  PRIMARY KEY (`id`),
  UNIQUE KEY `uq_course_sections_order` (`course_id`,`sort_order`),
  UNIQUE KEY `uq_course_sections_id_course` (`id`,`course_id`),
  CONSTRAINT `fk_course_sections_course` FOREIGN KEY (`course_id`) REFERENCES `courses` (`id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=285 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `course_sections`
--

SET @OLD_AUTOCOMMIT=@@AUTOCOMMIT, @@AUTOCOMMIT=0;
LOCK TABLES `course_sections` WRITE;
/*!40000 ALTER TABLE `course_sections` DISABLE KEYS */;
INSERT INTO `course_sections` VALUES
(1,1,'Khởi động Laravel API','Cài đặt project, hiểu route, controller, request và response.',1,'published','2026-01-10 09:00:00','2026-06-20 10:00:00'),
(2,1,'Auth session custom','Login, logout, refresh token, quản lý session và thiết bị.',2,'published','2026-01-10 09:10:00','2026-06-20 10:00:00'),
(3,1,'Payment và Learning flow','Order, coupon, enrollment, tiến độ học và quiz.',3,'published','2026-01-10 09:20:00','2026-06-20 10:00:00'),
(4,1,'Nội dung ẩn demo','Section hidden để test filter.',4,'hidden','2026-01-10 09:30:00','2026-06-20 10:00:00'),
(5,2,'PHP nền tảng','Biến, hàm, mảng, request/response cơ bản.',1,'published','2026-01-12 09:00:00','2026-06-15 10:00:00'),
(6,2,'MySQL thực chiến','CRUD, khóa ngoại, transaction.',2,'published','2026-01-12 09:10:00','2026-06-15 10:00:00'),
(7,3,'React UI Foundation','Component, props, state và layout.',1,'published','2026-01-20 09:00:00','2026-06-16 10:00:00'),
(8,3,'Tích hợp API e-learning','Call API, auth token và error state.',2,'published','2026-01-20 09:10:00','2026-06-16 10:00:00'),
(9,6,'AI trong sản phẩm học tập','Tóm tắt bài, tạo quiz nháp và phân tích điểm yếu.',1,'published','2026-02-15 09:00:00','2026-06-18 10:00:00'),
(10,7,'Git cơ bản','Commit, branch và pull request.',1,'published','2026-01-15 09:00:00','2026-06-01 10:00:00'),
(11,4,'Draft section','Dữ liệu draft.',1,'draft','2026-02-01 09:00:00','2026-02-01 09:00:00'),
(12,8,'Kiến trúc nâng cao','Service, repository và module boundary.',1,'published','2026-06-01 09:00:00','2026-06-18 08:00:00'),
(13,9,'Nhập môn AI trong học tập','Chương 1 của khóa AI Learning cho sinh viên IT và E-learning.',1,'published','2026-06-27 08:00:00','2026-06-27 08:00:00'),
(14,9,'Dùng AI để học lập trình hiệu quả','Chương 2 của khóa AI Learning cho sinh viên IT và E-learning.',2,'published','2026-06-27 08:00:00','2026-06-27 08:00:00'),
(15,9,'AI cho đồ án E-learning MindHub','Chương 3 của khóa AI Learning cho sinh viên IT và E-learning.',3,'published','2026-06-27 08:00:00','2026-06-27 08:00:00'),
(16,9,'Kỹ năng prompt thực chiến','Chương 4 của khóa AI Learning cho sinh viên IT và E-learning.',4,'published','2026-06-27 08:00:00','2026-06-27 08:00:00'),
(17,9,'Đạo đức, kiểm chứng và an toàn khi dùng AI','Chương 5 của khóa AI Learning cho sinh viên IT và E-learning.',5,'published','2026-06-27 08:00:00','2026-06-27 08:00:00'),
(18,9,'Xây workflow học tập với AI','Chương 6 của khóa AI Learning cho sinh viên IT và E-learning.',6,'published','2026-06-27 08:00:00','2026-06-27 08:00:00'),
(19,10,'Làm quen với Postman và API Testing','Chương 1 của khóa Postman API Testing cho Laravel Backend.',1,'published','2026-06-27 08:00:00','2026-06-27 08:00:00'),
(20,10,'Test API Auth và token','Chương 2 của khóa Postman API Testing cho Laravel Backend.',2,'published','2026-06-27 08:00:00','2026-06-27 08:00:00'),
(21,10,'Collection, environment và dữ liệu test','Chương 3 của khóa Postman API Testing cho Laravel Backend.',3,'published','2026-06-27 08:00:00','2026-06-27 08:00:00'),
(22,10,'Test flow nghiệp vụ E-learning','Chương 4 của khóa Postman API Testing cho Laravel Backend.',4,'published','2026-06-27 08:00:00','2026-06-27 08:00:00'),
(23,10,'Test case thất bại và debug lỗi','Chương 5 của khóa Postman API Testing cho Laravel Backend.',5,'published','2026-06-27 08:00:00','2026-06-27 08:00:00'),
(24,10,'Hoàn thiện bộ test API cho demo đồ án','Chương 6 của khóa Postman API Testing cho Laravel Backend.',6,'published','2026-06-27 08:00:00','2026-06-27 08:00:00'),
(25,11,'Làm quen với Git & GitHub trong đồ án','Chương 1 của khóa Git & GitHub làm việc nhóm đồ án.',1,'published','2026-06-27 08:00:00','2026-06-27 08:00:00'),
(26,11,'Commit, push, pull và branch hằng ngày','Chương 2 của khóa Git & GitHub làm việc nhóm đồ án.',2,'published','2026-06-27 08:00:00','2026-06-27 08:00:00'),
(27,11,'Pull request và workflow làm việc nhóm','Chương 3 của khóa Git & GitHub làm việc nhóm đồ án.',3,'published','2026-06-27 08:00:00','2026-06-27 08:00:00'),
(28,11,'Conflict và lỗi Git thường gặp','Chương 4 của khóa Git & GitHub làm việc nhóm đồ án.',4,'published','2026-06-27 08:00:00','2026-06-27 08:00:00'),
(29,11,'README, bảo mật repo và trình bày GitHub','Chương 5 của khóa Git & GitHub làm việc nhóm đồ án.',5,'published','2026-06-27 08:00:00','2026-06-27 08:00:00'),
(30,11,'Thực chiến Git cho dự án MindHub','Chương 6 của khóa Git & GitHub làm việc nhóm đồ án.',6,'published','2026-06-27 08:00:00','2026-06-27 08:00:00'),
(31,12,'Nền tảng thiết kế database','Chương 1 của khóa MySQL Database Design cho dự án E-learning.',1,'published','2026-06-27 08:00:00','2026-06-27 08:00:00'),
(32,12,'Thiết kế dữ liệu khóa học','Chương 2 của khóa MySQL Database Design cho dự án E-learning.',2,'published','2026-06-27 08:00:00','2026-06-27 08:00:00'),
(33,12,'Thiết kế dữ liệu học tập','Chương 3 của khóa MySQL Database Design cho dự án E-learning.',3,'published','2026-06-27 08:00:00','2026-06-27 08:00:00'),
(34,12,'Order payment và revenue','Chương 4 của khóa MySQL Database Design cho dự án E-learning.',4,'published','2026-06-27 08:00:00','2026-06-27 08:00:00'),
(35,12,'Tối ưu query và chất lượng dữ liệu','Chương 5 của khóa MySQL Database Design cho dự án E-learning.',5,'published','2026-06-27 08:00:00','2026-06-27 08:00:00'),
(36,12,'Checklist database trước khi code backend','Chương 6 của khóa MySQL Database Design cho dự án E-learning.',6,'published','2026-06-27 08:00:00','2026-06-27 08:00:00'),
(37,13,'Hiểu kiến trúc deploy fullstack','Chương 1 của khóa Deploy Fullstack Laravel React lên VPS aaPanel.',1,'published','2026-06-27 08:00:00','2026-06-27 08:00:00'),
(38,13,'Chuẩn bị VPS domain và aaPanel','Chương 2 của khóa Deploy Fullstack Laravel React lên VPS aaPanel.',2,'published','2026-06-27 08:00:00','2026-06-27 08:00:00'),
(39,13,'Deploy Laravel API','Chương 3 của khóa Deploy Fullstack Laravel React lên VPS aaPanel.',3,'published','2026-06-27 08:00:00','2026-06-27 08:00:00'),
(40,13,'Deploy React Frontend','Chương 4 của khóa Deploy Fullstack Laravel React lên VPS aaPanel.',4,'published','2026-06-27 08:00:00','2026-06-27 08:00:00'),
(41,13,'Media server và dữ liệu demo','Chương 5 của khóa Deploy Fullstack Laravel React lên VPS aaPanel.',5,'published','2026-06-27 08:00:00','2026-06-27 08:00:00'),
(42,13,'Fix lỗi và checklist production','Chương 6 của khóa Deploy Fullstack Laravel React lên VPS aaPanel.',6,'published','2026-06-27 08:00:00','2026-06-27 08:00:00'),
(43,14,'Nền tảng Tailwind cho UI E-learning','Chương 1 của khóa Tailwind CSS UI cho Dashboard E-learning.',1,'published','2026-06-27 08:00:00','2026-06-27 08:00:00'),
(44,14,'Thiết kế landing page và course listing','Chương 2 của khóa Tailwind CSS UI cho Dashboard E-learning.',2,'published','2026-06-27 08:00:00','2026-06-27 08:00:00'),
(45,14,'Thiết kế course detail và checkout','Chương 3 của khóa Tailwind CSS UI cho Dashboard E-learning.',3,'published','2026-06-27 08:00:00','2026-06-27 08:00:00'),
(46,14,'Thiết kế learning dashboard và video page','Chương 4 của khóa Tailwind CSS UI cho Dashboard E-learning.',4,'published','2026-06-27 08:00:00','2026-06-27 08:00:00'),
(47,14,'Thiết kế admin instructor dashboard','Chương 5 của khóa Tailwind CSS UI cho Dashboard E-learning.',5,'published','2026-06-27 08:00:00','2026-06-27 08:00:00'),
(48,14,'Hoàn thiện UI trước ngày demo','Chương 6 của khóa Tailwind CSS UI cho Dashboard E-learning.',6,'published','2026-06-27 08:00:00','2026-06-27 08:00:00'),
(49,15,'Hiểu phạm vi dự án web nhỏ','Chương 1 của khóa Báo giá hợp đồng và quản lý dự án web nhỏ.',1,'published','2026-06-27 08:00:00','2026-06-27 08:00:00'),
(50,15,'Báo giá và hợp đồng cơ bản','Chương 2 của khóa Báo giá hợp đồng và quản lý dự án web nhỏ.',2,'published','2026-06-27 08:00:00','2026-06-27 08:00:00'),
(51,15,'Quản lý tiến độ và giao tiếp','Chương 3 của khóa Báo giá hợp đồng và quản lý dự án web nhỏ.',3,'published','2026-06-27 08:00:00','2026-06-27 08:00:00'),
(52,15,'Bàn giao bảo trì và hậu dự án','Chương 4 của khóa Báo giá hợp đồng và quản lý dự án web nhỏ.',4,'published','2026-06-27 08:00:00','2026-06-27 08:00:00'),
(53,16,'Chuẩn bị nền tảng freelance','Chương 1 của khóa Freelance Web Developer từ Portfolio đến khách hàng đầu tiên.',1,'published','2026-06-27 08:00:00','2026-06-27 08:00:00'),
(54,16,'Xây portfolio và hồ sơ bán dịch vụ','Chương 2 của khóa Freelance Web Developer từ Portfolio đến khách hàng đầu tiên.',2,'published','2026-06-27 08:00:00','2026-06-27 08:00:00'),
(55,16,'Tìm và trao đổi với khách hàng','Chương 3 của khóa Freelance Web Developer từ Portfolio đến khách hàng đầu tiên.',3,'published','2026-06-27 08:00:00','2026-06-27 08:00:00'),
(56,16,'Giao dự án và phát triển lâu dài','Chương 4 của khóa Freelance Web Developer từ Portfolio đến khách hàng đầu tiên.',4,'published','2026-06-27 08:00:00','2026-06-27 08:00:00'),
(57,17,'Nhập môn SaaS cho Web Developer','Chương 1 của khóa Tư duy sản phẩm SaaS cho lập trình viên Web.',1,'published','2026-06-27 08:00:00','2026-06-27 08:00:00'),
(58,17,'Thiết kế tính năng SaaS cơ bản','Chương 2 của khóa Tư duy sản phẩm SaaS cho lập trình viên Web.',2,'published','2026-06-27 08:00:00','2026-06-27 08:00:00'),
(59,17,'Vận hành sản phẩm SaaS','Chương 3 của khóa Tư duy sản phẩm SaaS cho lập trình viên Web.',3,'published','2026-06-27 08:00:00','2026-06-27 08:00:00'),
(60,17,'Áp dụng SaaS vào portfolio web','Chương 4 của khóa Tư duy sản phẩm SaaS cho lập trình viên Web.',4,'published','2026-06-27 08:00:00','2026-06-27 08:00:00'),
(61,18,'Hiểu MVP và tư duy sản phẩm','Chương 1 của khóa Xây MVP sản phẩm Web cho người mới.',1,'published','2026-06-27 08:00:00','2026-06-27 08:00:00'),
(62,18,'Thiết kế MVP vừa sức','Chương 2 của khóa Xây MVP sản phẩm Web cho người mới.',2,'published','2026-06-27 08:00:00','2026-06-27 08:00:00'),
(63,18,'Xây dựng và đo lường MVP','Chương 3 của khóa Xây MVP sản phẩm Web cho người mới.',3,'published','2026-06-27 08:00:00','2026-06-27 08:00:00'),
(64,18,'Từ MVP đến portfolio và startup nhỏ','Chương 4 của khóa Xây MVP sản phẩm Web cho người mới.',4,'published','2026-06-27 08:00:00','2026-06-27 08:00:00'),
(65,19,'Nền tảng giao tiếp trong team IT','Chương 1 của khóa Giao tiếp trong team IT cho Web Developer.',1,'published','2026-06-27 08:00:00','2026-06-27 08:00:00'),
(66,19,'Giao tiếp khi làm Backend Frontend','Chương 2 của khóa Giao tiếp trong team IT cho Web Developer.',2,'published','2026-06-27 08:00:00','2026-06-27 08:00:00'),
(67,19,'Giao tiếp với giảng viên khách hàng và nhà tuyển dụng','Chương 3 của khóa Giao tiếp trong team IT cho Web Developer.',3,'published','2026-06-27 08:00:00','2026-06-27 08:00:00'),
(68,19,'Thực hành giao tiếp chuyên nghiệp','Chương 4 của khóa Giao tiếp trong team IT cho Web Developer.',4,'published','2026-06-27 08:00:00','2026-06-27 08:00:00'),
(69,20,'Hiểu teamwork trong dự án web','Chương 1 của khóa Teamwork Agile cho dự án Web sinh viên.',1,'published','2026-06-27 08:00:00','2026-06-27 08:00:00'),
(70,20,'Agile Scrum đơn giản cho sinh viên','Chương 2 của khóa Teamwork Agile cho dự án Web sinh viên.',2,'published','2026-06-27 08:00:00','2026-06-27 08:00:00'),
(71,20,'Quản lý task và tiến độ','Chương 3 của khóa Teamwork Agile cho dự án Web sinh viên.',3,'published','2026-06-27 08:00:00','2026-06-27 08:00:00'),
(72,20,'Teamwork trước ngày demo','Chương 4 của khóa Teamwork Agile cho dự án Web sinh viên.',4,'published','2026-06-27 08:00:00','2026-06-27 08:00:00'),
(73,21,'Chuẩn bị câu chuyện project','Chương 1 của khóa Kỹ năng trình bày project Web khi phỏng vấn.',1,'published','2026-06-27 08:00:00','2026-06-27 08:00:00'),
(74,21,'Trình bày kỹ thuật Backend Frontend','Chương 2 của khóa Kỹ năng trình bày project Web khi phỏng vấn.',2,'published','2026-06-27 08:00:00','2026-06-27 08:00:00'),
(75,21,'Demo sản phẩm thuyết phục','Chương 3 của khóa Kỹ năng trình bày project Web khi phỏng vấn.',3,'published','2026-06-27 08:00:00','2026-06-27 08:00:00'),
(76,21,'Luyện trả lời phỏng vấn project','Chương 4 của khóa Kỹ năng trình bày project Web khi phỏng vấn.',4,'published','2026-06-27 08:00:00','2026-06-27 08:00:00'),
(77,22,'Hiểu vấn đề trước khi code','Chương 1 của khóa Tư duy giải quyết vấn đề cho lập trình viên Web.',1,'published','2026-06-27 08:00:00','2026-06-27 08:00:00'),
(78,22,'Tư duy debug và phân tích lỗi','Chương 2 của khóa Tư duy giải quyết vấn đề cho lập trình viên Web.',2,'published','2026-06-27 08:00:00','2026-06-27 08:00:00'),
(79,22,'Tư duy thiết kế giải pháp','Chương 3 của khóa Tư duy giải quyết vấn đề cho lập trình viên Web.',3,'published','2026-06-27 08:00:00','2026-06-27 08:00:00'),
(80,22,'Áp dụng vào phỏng vấn và đồ án','Chương 4 của khóa Tư duy giải quyết vấn đề cho lập trình viên Web.',4,'published','2026-06-27 08:00:00','2026-06-27 08:00:00'),
(81,23,'Khởi động React cho dự án E-learning','Chương 1 của khóa React E-learning Frontend từ cơ bản đến thực chiến.',1,'published','2026-06-27 08:00:00','2026-06-27 08:00:00'),
(82,23,'Routing, UI và trang khóa học','Chương 2 của khóa React E-learning Frontend từ cơ bản đến thực chiến.',2,'published','2026-06-27 08:00:00','2026-06-27 08:00:00'),
(83,23,'Authentication frontend và phân quyền giao diện','Chương 3 của khóa React E-learning Frontend từ cơ bản đến thực chiến.',3,'published','2026-06-27 08:00:00','2026-06-27 08:00:00'),
(84,23,'Learning UI và video player','Chương 4 của khóa React E-learning Frontend từ cơ bản đến thực chiến.',4,'published','2026-06-27 08:00:00','2026-06-27 08:00:00'),
(85,23,'State, form và trải nghiệm người dùng','Chương 5 của khóa React E-learning Frontend từ cơ bản đến thực chiến.',5,'published','2026-06-27 08:00:00','2026-06-27 08:00:00'),
(86,23,'Hoàn thiện frontend E-learning MindHub','Chương 6 của khóa React E-learning Frontend từ cơ bản đến thực chiến.',6,'published','2026-06-27 08:00:00','2026-06-27 08:00:00'),
(87,24,'Hiểu landing page chuyển đổi','Chương 1 của khóa Thiết kế Landing Page chuyển đổi cao cho sản phẩm Web.',1,'published','2026-06-27 08:00:00','2026-06-27 08:00:00'),
(88,24,'Cấu trúc landing page hiệu quả','Chương 2 của khóa Thiết kế Landing Page chuyển đổi cao cho sản phẩm Web.',2,'published','2026-06-27 08:00:00','2026-06-27 08:00:00'),
(89,24,'UI UX và copywriting cho landing page','Chương 3 của khóa Thiết kế Landing Page chuyển đổi cao cho sản phẩm Web.',3,'published','2026-06-27 08:00:00','2026-06-27 08:00:00'),
(90,24,'Đo lường và cải thiện landing page','Chương 4 của khóa Thiết kế Landing Page chuyển đổi cao cho sản phẩm Web.',4,'published','2026-06-27 08:00:00','2026-06-27 08:00:00'),
(91,25,'Nhập môn Web Analytics','Chương 1 của khóa Web Analytics và A/B Testing cơ bản.',1,'published','2026-06-27 08:00:00','2026-06-27 08:00:00'),
(92,25,'Đo lường hành vi người dùng','Chương 2 của khóa Web Analytics và A/B Testing cơ bản.',2,'published','2026-06-27 08:00:00','2026-06-27 08:00:00'),
(93,25,'A/B Testing cơ bản','Chương 3 của khóa Web Analytics và A/B Testing cơ bản.',3,'published','2026-06-27 08:00:00','2026-06-27 08:00:00'),
(94,25,'Áp dụng vào web project','Chương 4 của khóa Web Analytics và A/B Testing cơ bản.',4,'published','2026-06-27 08:00:00','2026-06-27 08:00:00'),
(95,26,'Nền tảng content marketing','Chương 1 của khóa Content Marketing cho Landing Page và Blog công nghệ.',1,'published','2026-06-27 08:00:00','2026-06-27 08:00:00'),
(96,26,'Viết nội dung cho landing page','Chương 2 của khóa Content Marketing cho Landing Page và Blog công nghệ.',2,'published','2026-06-27 08:00:00','2026-06-27 08:00:00'),
(97,26,'Viết blog và tài liệu hỗ trợ SEO','Chương 3 của khóa Content Marketing cho Landing Page và Blog công nghệ.',3,'published','2026-06-27 08:00:00','2026-06-27 08:00:00'),
(98,26,'Content cho sản phẩm web thật','Chương 4 của khóa Content Marketing cho Landing Page và Blog công nghệ.',4,'published','2026-06-27 08:00:00','2026-06-27 08:00:00'),
(99,27,'Nhập môn SEO cho người làm web','Chương 1 của khóa SEO cơ bản cho Web Developer.',1,'published','2026-06-27 08:00:00','2026-06-27 08:00:00'),
(100,27,'SEO kỹ thuật cơ bản','Chương 2 của khóa SEO cơ bản cho Web Developer.',2,'published','2026-06-27 08:00:00','2026-06-27 08:00:00'),
(101,27,'SEO cho dự án E-learning','Chương 3 của khóa SEO cơ bản cho Web Developer.',3,'published','2026-06-27 08:00:00','2026-06-27 08:00:00'),
(102,27,'Đo lường và cải thiện SEO','Chương 4 của khóa SEO cơ bản cho Web Developer.',4,'published','2026-06-27 08:00:00','2026-06-27 08:00:00'),
(103,28,'Tổng quan thanh toán trong E-learning','Chương 1 của khóa Tích hợp VNPay Payment với Laravel E-learning.',1,'published','2026-06-27 08:00:00','2026-06-27 08:00:00'),
(104,28,'Chuẩn bị cấu hình VNPay trong Laravel','Chương 2 của khóa Tích hợp VNPay Payment với Laravel E-learning.',2,'published','2026-06-27 08:00:00','2026-06-27 08:00:00'),
(105,28,'Tạo payment URL và chuyển hướng thanh toán','Chương 3 của khóa Tích hợp VNPay Payment với Laravel E-learning.',3,'published','2026-06-27 08:00:00','2026-06-27 08:00:00'),
(106,28,'Xử lý return và IPN','Chương 4 của khóa Tích hợp VNPay Payment với Laravel E-learning.',4,'published','2026-06-27 08:00:00','2026-06-27 08:00:00'),
(107,28,'Enrollment revenue và transaction','Chương 5 của khóa Tích hợp VNPay Payment với Laravel E-learning.',5,'published','2026-06-27 08:00:00','2026-06-27 08:00:00'),
(108,28,'Hoàn thiện module payment cho demo','Chương 6 của khóa Tích hợp VNPay Payment với Laravel E-learning.',6,'published','2026-06-27 08:00:00','2026-06-27 08:00:00'),
(109,29,'Hiểu nghề Web Developer','Chương 1 của khóa Lộ trình xin việc Web Developer cho sinh viên IT.',1,'published','2026-06-27 08:00:00','2026-06-27 08:00:00'),
(110,29,'Lộ trình học Frontend, Backend và Full-stack','Chương 2 của khóa Lộ trình xin việc Web Developer cho sinh viên IT.',2,'published','2026-06-27 08:00:00','2026-06-27 08:00:00'),
(111,29,'CV IT cho sinh viên chưa có kinh nghiệm','Chương 3 của khóa Lộ trình xin việc Web Developer cho sinh viên IT.',3,'published','2026-06-27 08:00:00','2026-06-27 08:00:00'),
(112,29,'GitHub và Portfolio cá nhân','Chương 4 của khóa Lộ trình xin việc Web Developer cho sinh viên IT.',4,'published','2026-06-27 08:00:00','2026-06-27 08:00:00'),
(113,29,'Chuẩn bị phỏng vấn Fresher','Chương 5 của khóa Lộ trình xin việc Web Developer cho sinh viên IT.',5,'published','2026-06-27 08:00:00','2026-06-27 08:00:00'),
(114,29,'Lộ trình 90 ngày sẵn sàng xin việc','Chương 6 của khóa Lộ trình xin việc Web Developer cho sinh viên IT.',6,'published','2026-06-27 08:00:00','2026-06-27 08:00:00'),
(115,30,'Tổng quan phỏng vấn Backend Fresher','Chương 1 của khóa Phỏng vấn Backend Developer Fresher.',1,'published','2026-06-27 08:00:00','2026-06-27 08:00:00'),
(116,30,'HTTP và REST API nền tảng','Chương 2 của khóa Phỏng vấn Backend Developer Fresher.',2,'published','2026-06-27 08:00:00','2026-06-27 08:00:00'),
(117,30,'Authentication, Authorization và bảo mật API','Chương 3 của khóa Phỏng vấn Backend Developer Fresher.',3,'published','2026-06-27 08:00:00','2026-06-27 08:00:00'),
(118,30,'Database, transaction và xử lý dữ liệu','Chương 4 của khóa Phỏng vấn Backend Developer Fresher.',4,'published','2026-06-27 08:00:00','2026-06-27 08:00:00'),
(119,30,'Laravel Backend Architecture','Chương 5 của khóa Phỏng vấn Backend Developer Fresher.',5,'published','2026-06-27 08:00:00','2026-06-27 08:00:00'),
(120,30,'Thực chiến phỏng vấn Backend Fresher','Chương 6 của khóa Phỏng vấn Backend Developer Fresher.',6,'published','2026-06-27 08:00:00','2026-06-27 08:00:00'),
(275,454,'Section 6a797a982c4a4',NULL,1,'published','2026-08-10 07:15:36','2026-08-10 07:15:36'),
(276,455,'Section 6a797a98357f6',NULL,1,'published','2026-08-10 07:15:36','2026-08-10 07:15:36'),
(283,461,'Section 1',NULL,2,'published','2026-08-10 07:15:36','2026-08-10 07:15:36'),
(284,461,'Section 2 (First by sort order)',NULL,1,'published','2026-08-10 07:15:36','2026-08-10 07:15:36');
/*!40000 ALTER TABLE `course_sections` ENABLE KEYS */;
UNLOCK TABLES;
COMMIT;
SET AUTOCOMMIT=@OLD_AUTOCOMMIT;

--
-- Table structure for table `courses`
--

DROP TABLE IF EXISTS `courses`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `courses` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `instructor_id` bigint(20) unsigned NOT NULL,
  `title` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `slug` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `short_description` varchar(500) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `description` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `thumbnail_url` varchar(2048) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `thumbnail_public_id` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `intro_video_url` varchar(2048) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `intro_video_id` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `price` decimal(15,2) NOT NULL DEFAULT 0.00,
  `discount_percent` decimal(5,2) NOT NULL DEFAULT 0.00,
  `course_level` enum('beginner','intermediate','advanced','all_levels') CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'beginner',
  `language` varchar(20) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'vi',
  `requirements` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL CHECK (json_valid(`requirements`)),
  `outcomes` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL CHECK (json_valid(`outcomes`)),
  `status` enum('draft','pending_review','approved','rejected','published','hidden') CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'draft',
  `is_featured` tinyint(1) NOT NULL DEFAULT 0,
  `published_at` datetime DEFAULT NULL,
  `reviewed_by` bigint(20) unsigned DEFAULT NULL,
  `admin_reject_reason` varchar(1000) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `created_at` datetime NOT NULL DEFAULT current_timestamp(),
  `updated_at` datetime NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  `sale_price` decimal(15,2) GENERATED ALWAYS AS (round(`price` * (1 - `discount_percent` / 100),2)) STORED,
  PRIMARY KEY (`id`),
  UNIQUE KEY `uq_courses_slug` (`slug`),
  UNIQUE KEY `uq_courses_thumbnail_public_id` (`thumbnail_public_id`),
  UNIQUE KEY `uq_courses_intro_video_id` (`intro_video_id`),
  KEY `idx_courses_instructor_status` (`instructor_id`,`status`),
  KEY `idx_courses_featured` (`is_featured`,`status`),
  KEY `idx_courses_reviewed_by` (`reviewed_by`),
  CONSTRAINT `fk_courses_instructor` FOREIGN KEY (`instructor_id`) REFERENCES `users` (`id`) ON UPDATE CASCADE,
  CONSTRAINT `fk_courses_reviewed_by` FOREIGN KEY (`reviewed_by`) REFERENCES `users` (`id`) ON DELETE SET NULL ON UPDATE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=464 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_uca1400_ai_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `courses`
--

SET @OLD_AUTOCOMMIT=@@AUTOCOMMIT, @@AUTOCOMMIT=0;
LOCK TABLES `courses` WRITE;
/*!40000 ALTER TABLE `courses` DISABLE KEYS */;
INSERT INTO `courses` VALUES
(1,2,'Laravel REST API từ cơ bản đến triển khai','laravel-rest-api-tu-co-ban-den-trien-khai','Xây dựng REST API Laravel theo Repository/Service, auth session custom và payment flow.','Khóa học thực chiến dành cho sinh viên làm đồ án và junior backend muốn nắm quy trình xây dựng API e-learning bằng Laravel.','/demo/courses/laravel-rest-api.jpg',NULL,'/demo/videos/laravel-intro.mp4',NULL,499000.00,0.00,'beginner','vi','\"Biết PHP cơ bản, đã cài Laragon hoặc môi trường PHP/MySQL.\"','\"Thiết kế API chuẩn, xử lý auth, payment, learning progress và test bằng Postman/PowerShell.\"','published',1,'2026-02-01 09:00:00',NULL,NULL,'2026-01-10 08:00:00','2026-06-20 10:00:00',499000.00),
(2,2,'PHP & MySQL nền tảng cho Backend','php-mysql-nen-tang-cho-backend','Nắm nền tảng PHP, MySQL, CRUD, transaction và thiết kế database.','Khóa học nền tảng giúp người học hiểu cách dữ liệu đi từ form đến database và quay về API response.','/demo/courses/php-mysql.jpg',NULL,'/demo/videos/php-mysql-intro.mp4',NULL,399000.00,0.00,'beginner','vi','\"Biết HTML cơ bản là lợi thế.\"','\"Viết CRUD an toàn, hiểu khóa chính/khóa ngoại, transaction và validate dữ liệu.\"','published',1,'2026-02-10 09:00:00',NULL,NULL,'2026-01-12 08:00:00','2026-06-15 10:00:00',399000.00),
(3,3,'React Frontend cho trang E-learning','react-frontend-cho-trang-e-learning','Xây giao diện catalog, course detail, cart và learning dashboard.','Khóa học giúp frontend developer xây trang e-learning có component, state, call API và UI/UX rõ ràng.','/demo/courses/react-elearning.jpg',NULL,'/demo/videos/react-intro.mp4',NULL,599000.00,0.00,'beginner','vi','\"Biết JavaScript ES6 và HTML/CSS.\"','\"Tạo SPA học trực tuyến, tích hợp API và xử lý trạng thái loading/error.\"','published',1,'2026-03-01 09:00:00',NULL,NULL,'2026-01-20 08:00:00','2026-06-16 10:00:00',599000.00),
(4,2,'NodeJS Hidden Draft API','nodejs-hidden-draft-api','Course draft dùng để test chặn mua khóa chưa public.','Dữ liệu demo trạng thái draft, không hiển thị cho learner public.','/demo/courses/node-draft.jpg',NULL,NULL,NULL,350000.00,0.00,'beginner','vi','\"JavaScript cơ bản.\"','\"Không dùng để trình chiếu public.\"','draft',0,NULL,NULL,NULL,'2026-02-01 08:00:00','2026-02-01 08:00:00',350000.00),
(5,2,'NodeJS Hidden Course','nodejs-hidden-course','Course hidden dùng để test filter và authorization.','Dữ liệu demo trạng thái hidden, không cho learner mua/thêm wishlist nếu API public lọc status.','/demo/courses/node-hidden.jpg',NULL,NULL,NULL,450000.00,0.00,'beginner','vi','\"JavaScript cơ bản.\"','\"Không dùng để trình chiếu public.\"','hidden',0,'2026-02-15 09:00:00',NULL,NULL,'2026-02-01 08:15:00','2026-03-01 08:15:00',450000.00),
(6,3,'AI ứng dụng cho học tập cá nhân hóa','ai-ung-dung-cho-hoc-tap-ca-nhan-hoa','Demo các tính năng AI: tóm tắt bài học, gợi ý khóa học, phân tích điểm yếu.','Khóa học mô phỏng cách tích hợp AI vào hệ thống học trực tuyến mà không gửi dữ liệu nhạy cảm.','/demo/courses/ai-learning.jpg',NULL,'/demo/videos/ai-intro.mp4',NULL,699000.00,0.00,'beginner','vi','\"Có kiến thức web/API cơ bản.\"','\"Hiểu workflow AI summary, draft quiz, recommendation và cảnh báo rủi ro bỏ học.\"','published',1,'2026-04-01 09:00:00',NULL,NULL,'2026-02-15 08:00:00','2026-06-18 10:00:00',699000.00),
(7,2,'Git & GitHub cho sinh viên làm đồ án','git-github-cho-sinh-vien-lam-do-an','Khóa miễn phí giúp quản lý source code, branch, commit và pull request.','Nội dung ngắn gọn để team đồ án phối hợp code backend/frontend hiệu quả.','/demo/courses/git-github.jpg',NULL,'/demo/videos/git-intro.mp4',NULL,0.00,0.00,'beginner','vi','\"Có máy tính và tài khoản GitHub.\"','\"Biết clone, branch, commit, push, pull request và xử lý conflict cơ bản.\"','published',0,'2026-01-25 09:00:00',NULL,NULL,'2026-01-15 08:00:00','2026-06-01 10:00:00',0.00),
(8,3,'Advanced Laravel Architecture','advanced-laravel-architecture','Course pending review dùng test checklist/review/publish flow.','Dữ liệu demo trạng thái pending_review để instructor/admin test kiểm duyệt.','/demo/courses/advanced-laravel.jpg',NULL,NULL,NULL,899000.00,0.00,'beginner','vi','\"Đã làm Laravel API thực tế.\"','\"Tách module, tối ưu service/repository và audit business flow.\"','pending_review',0,NULL,NULL,NULL,'2026-06-01 08:00:00','2026-06-18 08:00:00',899000.00),
(9,3,'AI Learning cho sinh viên IT và E-learning','ai-learning-cho-sinh-vien-it-va-e-learning','Khóa học demo MindHub gồm 30 video, bám theo lộ trình AI Learning.','Khóa học được seed từ file prompt NotebookLM MindHub_AI_LEARNING_30_NotebookLM_Prompts.md. Nội dung dùng cho demo sản phẩm và có thể dùng làm tài liệu học bổ trợ thực tế cho lập trình web.','/thumbnails/courses/ai-learning.jpg',NULL,'/videos/ai-learning/ai-learning-01-ai-trong-hoc-tap-la-gi.mp4',NULL,499000.00,0.00,'beginner','vi','\"Có tinh thần tự học, biết sử dụng máy tính cơ bản. Một số khóa kỹ thuật yêu cầu biết HTML/CSS, JavaScript, PHP hoặc Laravel cơ bản.\"','\"Hoàn thành 30 bài học, nắm được kiến thức chính và biết áp dụng vào đồ án hoặc dự án web thực tế.\"','published',1,'2026-06-27 08:00:00',NULL,NULL,'2026-06-27 08:00:00','2026-06-27 08:00:00',499000.00),
(10,2,'Postman API Testing cho Laravel Backend','postman-api-testing-cho-laravel-backend','Khóa học demo MindHub gồm 30 video, bám theo lộ trình Postman API Testing.','Khóa học được seed từ file prompt NotebookLM MindHub_POSTMAN_API_TESTING_30_NotebookLM_Prompts.md. Nội dung dùng cho demo sản phẩm và có thể dùng làm tài liệu học bổ trợ thực tế cho lập trình web.','/thumbnails/courses/postman-api-testing.jpg',NULL,'/videos/postman-api-testing/postman-api-testing-01-postman-la-gi-va-vi-sao-backend-fresher-nen-biet.mp4',NULL,499000.00,0.00,'beginner','vi','\"Có tinh thần tự học, biết sử dụng máy tính cơ bản. Một số khóa kỹ thuật yêu cầu biết HTML/CSS, JavaScript, PHP hoặc Laravel cơ bản.\"','\"Hoàn thành 30 bài học, nắm được kiến thức chính và biết áp dụng vào đồ án hoặc dự án web thực tế.\"','published',1,'2026-06-27 08:00:00',NULL,NULL,'2026-06-27 08:00:00','2026-06-27 08:00:00',499000.00),
(11,2,'Git & GitHub làm việc nhóm đồ án','git-github-lam-viec-nhom-do-an','Khóa học demo MindHub gồm 30 video, bám theo lộ trình Git & GitHub.','Khóa học được seed từ file prompt NotebookLM MindHub_GIT_GITHUB_30_NotebookLM_Prompts.md. Nội dung dùng cho demo sản phẩm và có thể dùng làm tài liệu học bổ trợ thực tế cho lập trình web.','/thumbnails/courses/git-github.jpg',NULL,'/videos/git-github/git-github-01-vi-sao-lam-do-an-nhom-phai-dung-git.mp4',NULL,199000.00,0.00,'beginner','vi','\"Có tinh thần tự học, biết sử dụng máy tính cơ bản. Một số khóa kỹ thuật yêu cầu biết HTML/CSS, JavaScript, PHP hoặc Laravel cơ bản.\"','\"Hoàn thành 30 bài học, nắm được kiến thức chính và biết áp dụng vào đồ án hoặc dự án web thực tế.\"','published',1,'2026-06-27 08:00:00',NULL,NULL,'2026-06-27 08:00:00','2026-06-27 08:00:00',199000.00),
(12,2,'MySQL Database Design cho dự án E-learning','mysql-database-design-cho-du-an-e-learning','Khóa học demo MindHub gồm 30 video, bám theo lộ trình MySQL Database Design.','Khóa học được seed từ file prompt NotebookLM MindHub_MYSQL_DATABASE_DESIGN_30_NotebookLM_Prompts.md. Nội dung dùng cho demo sản phẩm và có thể dùng làm tài liệu học bổ trợ thực tế cho lập trình web.','/thumbnails/courses/mysql-database-design.jpg',NULL,'/videos/mysql-database-design/mysql-database-design-01-database-trong-he-thong-e-learning-dung-de-lam-gi.mp4',NULL,499000.00,0.00,'beginner','vi','\"Có tinh thần tự học, biết sử dụng máy tính cơ bản. Một số khóa kỹ thuật yêu cầu biết HTML/CSS, JavaScript, PHP hoặc Laravel cơ bản.\"','\"Hoàn thành 30 bài học, nắm được kiến thức chính và biết áp dụng vào đồ án hoặc dự án web thực tế.\"','published',1,'2026-06-27 08:00:00',NULL,NULL,'2026-06-27 08:00:00','2026-06-27 08:00:00',499000.00),
(13,3,'Deploy Fullstack Laravel React lên VPS aaPanel','deploy-fullstack-laravel-react-len-vps-aapanel','Khóa học demo MindHub gồm 30 video, bám theo lộ trình Deploy VPS aaPanel.','Khóa học được seed từ file prompt NotebookLM MindHub_DEPLOY_VPS_AAPANEL_30_NotebookLM_Prompts.md. Nội dung dùng cho demo sản phẩm và có thể dùng làm tài liệu học bổ trợ thực tế cho lập trình web.','/thumbnails/courses/deploy-vps-aapanel.jpg',NULL,'/videos/deploy-vps-aapanel/deploy-vps-aapanel-01-deploy-fullstack-la-gi.mp4',NULL,499000.00,0.00,'beginner','vi','\"Có tinh thần tự học, biết sử dụng máy tính cơ bản. Một số khóa kỹ thuật yêu cầu biết HTML/CSS, JavaScript, PHP hoặc Laravel cơ bản.\"','\"Hoàn thành 30 bài học, nắm được kiến thức chính và biết áp dụng vào đồ án hoặc dự án web thực tế.\"','published',1,'2026-06-27 08:00:00',NULL,NULL,'2026-06-27 08:00:00','2026-06-27 08:00:00',499000.00),
(14,3,'Tailwind CSS UI cho Dashboard E-learning','tailwind-css-ui-cho-dashboard-e-learning','Khóa học demo MindHub gồm 30 video, bám theo lộ trình Tailwind CSS.','Khóa học được seed từ file prompt NotebookLM MindHub_TAILWIND_UI_ELEARNING_30_NotebookLM_Prompts.md. Nội dung dùng cho demo sản phẩm và có thể dùng làm tài liệu học bổ trợ thực tế cho lập trình web.','/thumbnails/courses/tailwind-ui-elearning.jpg',NULL,'/videos/tailwind-ui-elearning/tailwind-ui-elearning-01-tailwind-css-la-gi-va-phu-hop-voi-do-an-nhu-the-nao.mp4',NULL,499000.00,0.00,'beginner','vi','\"Có tinh thần tự học, biết sử dụng máy tính cơ bản. Một số khóa kỹ thuật yêu cầu biết HTML/CSS, JavaScript, PHP hoặc Laravel cơ bản.\"','\"Hoàn thành 30 bài học, nắm được kiến thức chính và biết áp dụng vào đồ án hoặc dự án web thực tế.\"','published',1,'2026-06-27 08:00:00',NULL,NULL,'2026-06-27 08:00:00','2026-06-27 08:00:00',499000.00),
(15,2,'Báo giá hợp đồng và quản lý dự án web nhỏ','bao-gia-hop-dong-va-quan-ly-du-an-web-nho','Khóa học demo MindHub gồm 20 video, bám theo lộ trình Freelance Web Developer.','Khóa học được seed từ file prompt NotebookLM MindHub_BIZ_WEB_PROJECT_MGMT_20_NotebookLM_Prompts.md. Nội dung dùng cho demo sản phẩm và có thể dùng làm tài liệu học bổ trợ thực tế cho lập trình web.','/thumbnails/courses/web-project-management.jpg',NULL,'/videos/web-project-management/web-project-management-01-vi-sao-phai-chot-scope-truoc-khi-bao-gia.mp4',NULL,299000.00,0.00,'beginner','vi','\"Có tinh thần tự học, biết sử dụng máy tính cơ bản. Một số khóa kỹ thuật yêu cầu biết HTML/CSS, JavaScript, PHP hoặc Laravel cơ bản.\"','\"Hoàn thành 20 bài học, nắm được kiến thức chính và biết áp dụng vào đồ án hoặc dự án web thực tế.\"','published',1,'2026-06-27 08:00:00',NULL,NULL,'2026-06-27 08:00:00','2026-06-27 08:00:00',299000.00),
(16,2,'Freelance Web Developer từ Portfolio đến khách hàng đầu tiên','freelance-web-developer-tu-portfolio-den-khach-hang-dau-tien','Khóa học demo MindHub gồm 20 video, bám theo lộ trình Freelance Web Developer.','Khóa học được seed từ file prompt NotebookLM MindHub_BIZ_FREELANCE_WEB_20_NotebookLM_Prompts.md. Nội dung dùng cho demo sản phẩm và có thể dùng làm tài liệu học bổ trợ thực tế cho lập trình web.','/thumbnails/courses/freelance-web-developer.jpg',NULL,'/videos/freelance-web-developer/freelance-web-developer-01-freelance-web-developer-la-gi.mp4',NULL,299000.00,0.00,'beginner','vi','\"Có tinh thần tự học, biết sử dụng máy tính cơ bản. Một số khóa kỹ thuật yêu cầu biết HTML/CSS, JavaScript, PHP hoặc Laravel cơ bản.\"','\"Hoàn thành 20 bài học, nắm được kiến thức chính và biết áp dụng vào đồ án hoặc dự án web thực tế.\"','published',1,'2026-06-27 08:00:00',NULL,NULL,'2026-06-27 08:00:00','2026-06-27 08:00:00',299000.00),
(17,2,'Tư duy sản phẩm SaaS cho lập trình viên Web','tu-duy-san-pham-saas-cho-lap-trinh-vien-web','Khóa học demo MindHub gồm 20 video, bám theo lộ trình Sản phẩm số & SaaS.','Khóa học được seed từ file prompt NotebookLM MindHub_BIZ_SAAS_WEBDEV_20_NotebookLM_Prompts.md. Nội dung dùng cho demo sản phẩm và có thể dùng làm tài liệu học bổ trợ thực tế cho lập trình web.','/thumbnails/courses/saas-product-thinking.jpg',NULL,'/videos/saas-product-thinking/saas-product-thinking-01-saas-la-gi-va-khac-website-thong-thuong-the-nao.mp4',NULL,299000.00,0.00,'beginner','vi','\"Có tinh thần tự học, biết sử dụng máy tính cơ bản. Một số khóa kỹ thuật yêu cầu biết HTML/CSS, JavaScript, PHP hoặc Laravel cơ bản.\"','\"Hoàn thành 20 bài học, nắm được kiến thức chính và biết áp dụng vào đồ án hoặc dự án web thực tế.\"','published',1,'2026-06-27 08:00:00',NULL,NULL,'2026-06-27 08:00:00','2026-06-27 08:00:00',299000.00),
(18,2,'Xây MVP sản phẩm Web cho người mới','xay-mvp-san-pham-web-cho-nguoi-moi','Khóa học demo MindHub gồm 20 video, bám theo lộ trình Sản phẩm số & SaaS.','Khóa học được seed từ file prompt NotebookLM MindHub_BIZ_MVP_WEB_20_NotebookLM_Prompts.md. Nội dung dùng cho demo sản phẩm và có thể dùng làm tài liệu học bổ trợ thực tế cho lập trình web.','/thumbnails/courses/mvp-web-product.jpg',NULL,'/videos/mvp-web-product/mvp-web-product-01-mvp-la-gi-va-vi-sao-nguoi-lam-web-nen-biet.mp4',NULL,299000.00,0.00,'beginner','vi','\"Có tinh thần tự học, biết sử dụng máy tính cơ bản. Một số khóa kỹ thuật yêu cầu biết HTML/CSS, JavaScript, PHP hoặc Laravel cơ bản.\"','\"Hoàn thành 20 bài học, nắm được kiến thức chính và biết áp dụng vào đồ án hoặc dự án web thực tế.\"','published',1,'2026-06-27 08:00:00',NULL,NULL,'2026-06-27 08:00:00','2026-06-27 08:00:00',299000.00),
(19,2,'Giao tiếp trong team IT cho Web Developer','giao-tiep-trong-team-it-cho-web-developer','Khóa học demo MindHub gồm 20 video, bám theo lộ trình Giao tiếp & Làm việc nhóm.','Khóa học được seed từ file prompt NotebookLM MindHub_SOFT_COMM_IT_20_NotebookLM_Prompts.md. Nội dung dùng cho demo sản phẩm và có thể dùng làm tài liệu học bổ trợ thực tế cho lập trình web.','/thumbnails/courses/soft-communication-it.jpg',NULL,'/videos/soft-communication-it/soft-communication-it-01-vi-sao-lap-trinh-vien-web-can-giao-tiep-tot.mp4',NULL,299000.00,0.00,'beginner','vi','\"Có tinh thần tự học, biết sử dụng máy tính cơ bản. Một số khóa kỹ thuật yêu cầu biết HTML/CSS, JavaScript, PHP hoặc Laravel cơ bản.\"','\"Hoàn thành 20 bài học, nắm được kiến thức chính và biết áp dụng vào đồ án hoặc dự án web thực tế.\"','published',1,'2026-06-27 08:00:00',NULL,NULL,'2026-06-27 08:00:00','2026-06-27 08:00:00',299000.00),
(20,2,'Teamwork Agile cho dự án Web sinh viên','teamwork-agile-cho-du-an-web-sinh-vien','Khóa học demo MindHub gồm 20 video, bám theo lộ trình Giao tiếp & Làm việc nhóm.','Khóa học được seed từ file prompt NotebookLM MindHub_SOFT_TEAMWORK_AGILE_20_NotebookLM_Prompts.md. Nội dung dùng cho demo sản phẩm và có thể dùng làm tài liệu học bổ trợ thực tế cho lập trình web.','/thumbnails/courses/teamwork-agile-web.jpg',NULL,'/videos/teamwork-agile-web/teamwork-agile-web-01-teamwork-trong-du-an-web-khac-lam-bai-ca-nhan-the-nao.mp4',NULL,299000.00,0.00,'beginner','vi','\"Có tinh thần tự học, biết sử dụng máy tính cơ bản. Một số khóa kỹ thuật yêu cầu biết HTML/CSS, JavaScript, PHP hoặc Laravel cơ bản.\"','\"Hoàn thành 20 bài học, nắm được kiến thức chính và biết áp dụng vào đồ án hoặc dự án web thực tế.\"','published',1,'2026-06-27 08:00:00',NULL,NULL,'2026-06-27 08:00:00','2026-06-27 08:00:00',299000.00),
(21,2,'Kỹ năng trình bày project Web khi phỏng vấn','ky-nang-trinh-bay-project-web-khi-phong-van','Khóa học demo MindHub gồm 20 video, bám theo lộ trình Tư duy nghề nghiệp & Phỏng vấn.','Khóa học được seed từ file prompt NotebookLM MindHub_SOFT_PRESENT_PROJECT_20_NotebookLM_Prompts.md. Nội dung dùng cho demo sản phẩm và có thể dùng làm tài liệu học bổ trợ thực tế cho lập trình web.','/thumbnails/courses/present-web-project.jpg',NULL,'/videos/present-web-project/present-web-project-01-vi-sao-can-biet-trinh-bay-project-web.mp4',NULL,299000.00,0.00,'beginner','vi','\"Có tinh thần tự học, biết sử dụng máy tính cơ bản. Một số khóa kỹ thuật yêu cầu biết HTML/CSS, JavaScript, PHP hoặc Laravel cơ bản.\"','\"Hoàn thành 20 bài học, nắm được kiến thức chính và biết áp dụng vào đồ án hoặc dự án web thực tế.\"','published',1,'2026-06-27 08:00:00',NULL,NULL,'2026-06-27 08:00:00','2026-06-27 08:00:00',299000.00),
(22,3,'Tư duy giải quyết vấn đề cho lập trình viên Web','tu-duy-giai-quyet-van-de-cho-lap-trinh-vien-web','Khóa học demo MindHub gồm 20 video, bám theo lộ trình Tư duy nghề nghiệp & Phỏng vấn.','Khóa học được seed từ file prompt NotebookLM MindHub_SOFT_PROBLEM_SOLVING_20_NotebookLM_Prompts.md. Nội dung dùng cho demo sản phẩm và có thể dùng làm tài liệu học bổ trợ thực tế cho lập trình web.','/thumbnails/courses/problem-solving-webdev.jpg',NULL,'/videos/problem-solving-webdev/problem-solving-webdev-01-vi-sao-khong-nen-lao-vao-code-khi-chua-hieu-yeu-cau.mp4',NULL,299000.00,0.00,'beginner','vi','\"Có tinh thần tự học, biết sử dụng máy tính cơ bản. Một số khóa kỹ thuật yêu cầu biết HTML/CSS, JavaScript, PHP hoặc Laravel cơ bản.\"','\"Hoàn thành 20 bài học, nắm được kiến thức chính và biết áp dụng vào đồ án hoặc dự án web thực tế.\"','published',1,'2026-06-27 08:00:00',NULL,NULL,'2026-06-27 08:00:00','2026-06-27 08:00:00',299000.00),
(23,3,'React E-learning Frontend từ cơ bản đến thực chiến','react-e-learning-frontend-tu-co-ban-den-thuc-chien','Khóa học demo MindHub gồm 30 video, bám theo lộ trình Frontend React.','Khóa học được seed từ file prompt NotebookLM MindHub_REACT_ELEARNING_30_NotebookLM_Prompts.md. Nội dung dùng cho demo sản phẩm và có thể dùng làm tài liệu học bổ trợ thực tế cho lập trình web.','/thumbnails/courses/react-elearning.jpg',NULL,'/videos/react-elearning/react-elearning-01-react-la-gi-va-vi-sao-dung-cho-e-learning.mp4',NULL,499000.00,0.00,'beginner','vi','\"Có tinh thần tự học, biết sử dụng máy tính cơ bản. Một số khóa kỹ thuật yêu cầu biết HTML/CSS, JavaScript, PHP hoặc Laravel cơ bản.\"','\"Hoàn thành 30 bài học, nắm được kiến thức chính và biết áp dụng vào đồ án hoặc dự án web thực tế.\"','published',1,'2026-06-27 08:00:00',NULL,NULL,'2026-06-27 08:00:00','2026-06-27 08:00:00',499000.00),
(24,3,'Thiết kế Landing Page chuyển đổi cao cho sản phẩm Web','thiet-ke-landing-page-chuyen-doi-cao-cho-san-pham-web','Khóa học demo MindHub gồm 20 video, bám theo lộ trình Landing Page & Conversion.','Khóa học được seed từ file prompt NotebookLM MindHub_MKT_LANDING_CONVERSION_20_NotebookLM_Prompts.md. Nội dung dùng cho demo sản phẩm và có thể dùng làm tài liệu học bổ trợ thực tế cho lập trình web.','/thumbnails/courses/landing-page-conversion.jpg',NULL,'/videos/landing-page-conversion/landing-page-conversion-01-landing-page-la-gi-va-khac-homepage-the-nao.mp4',NULL,299000.00,0.00,'beginner','vi','\"Có tinh thần tự học, biết sử dụng máy tính cơ bản. Một số khóa kỹ thuật yêu cầu biết HTML/CSS, JavaScript, PHP hoặc Laravel cơ bản.\"','\"Hoàn thành 20 bài học, nắm được kiến thức chính và biết áp dụng vào đồ án hoặc dự án web thực tế.\"','published',1,'2026-06-27 08:00:00',NULL,NULL,'2026-06-27 08:00:00','2026-06-27 08:00:00',299000.00),
(25,3,'Web Analytics và A/B Testing cơ bản','web-analytics-va-a-b-testing-co-ban','Khóa học demo MindHub gồm 20 video, bám theo lộ trình Landing Page & Conversion.','Khóa học được seed từ file prompt NotebookLM MindHub_MKT_ANALYTICS_ABTEST_20_NotebookLM_Prompts.md. Nội dung dùng cho demo sản phẩm và có thể dùng làm tài liệu học bổ trợ thực tế cho lập trình web.','/thumbnails/courses/web-analytics-ab-testing.jpg',NULL,'/videos/web-analytics-ab-testing/web-analytics-ab-testing-01-web-analytics-la-gi-va-vi-sao-web-developer-nen-biet.mp4',NULL,299000.00,0.00,'beginner','vi','\"Có tinh thần tự học, biết sử dụng máy tính cơ bản. Một số khóa kỹ thuật yêu cầu biết HTML/CSS, JavaScript, PHP hoặc Laravel cơ bản.\"','\"Hoàn thành 20 bài học, nắm được kiến thức chính và biết áp dụng vào đồ án hoặc dự án web thực tế.\"','published',1,'2026-06-27 08:00:00',NULL,NULL,'2026-06-27 08:00:00','2026-06-27 08:00:00',299000.00),
(26,3,'Content Marketing cho Landing Page và Blog công nghệ','content-marketing-cho-landing-page-va-blog-cong-nghe','Khóa học demo MindHub gồm 20 video, bám theo lộ trình SEO & Content Website.','Khóa học được seed từ file prompt NotebookLM MindHub_MKT_CONTENT_WEB_20_NotebookLM_Prompts.md. Nội dung dùng cho demo sản phẩm và có thể dùng làm tài liệu học bổ trợ thực tế cho lập trình web.','/thumbnails/courses/content-marketing-web.jpg',NULL,'/videos/content-marketing-web/content-marketing-web-01-content-marketing-la-gi-trong-website-cong-nghe.mp4',NULL,299000.00,0.00,'beginner','vi','\"Có tinh thần tự học, biết sử dụng máy tính cơ bản. Một số khóa kỹ thuật yêu cầu biết HTML/CSS, JavaScript, PHP hoặc Laravel cơ bản.\"','\"Hoàn thành 20 bài học, nắm được kiến thức chính và biết áp dụng vào đồ án hoặc dự án web thực tế.\"','published',1,'2026-06-27 08:00:00',NULL,NULL,'2026-06-27 08:00:00','2026-06-27 08:00:00',299000.00),
(27,3,'SEO cơ bản cho Web Developer','seo-co-ban-cho-web-developer','Khóa học demo MindHub gồm 20 video, bám theo lộ trình SEO & Content Website.','Khóa học được seed từ file prompt NotebookLM MindHub_MKT_SEO_WEBDEV_20_NotebookLM_Prompts.md. Nội dung dùng cho demo sản phẩm và có thể dùng làm tài liệu học bổ trợ thực tế cho lập trình web.','/thumbnails/courses/seo-for-webdev.jpg',NULL,'/videos/seo-for-webdev/seo-for-webdev-01-seo-la-gi-va-vi-sao-web-developer-nen-biet.mp4',NULL,299000.00,0.00,'beginner','vi','\"Có tinh thần tự học, biết sử dụng máy tính cơ bản. Một số khóa kỹ thuật yêu cầu biết HTML/CSS, JavaScript, PHP hoặc Laravel cơ bản.\"','\"Hoàn thành 20 bài học, nắm được kiến thức chính và biết áp dụng vào đồ án hoặc dự án web thực tế.\"','published',1,'2026-06-27 08:00:00',NULL,NULL,'2026-06-27 08:00:00','2026-06-27 08:00:00',299000.00),
(28,2,'Tích hợp VNPay Payment với Laravel E-learning','tich-hop-vnpay-payment-voi-laravel-e-learning','Khóa học demo MindHub gồm 30 video, bám theo lộ trình VNPay Laravel.','Khóa học được seed từ file prompt NotebookLM MindHub_VNPAY_LARAVEL_PAYMENT_30_NotebookLM_Prompts.md. Nội dung dùng cho demo sản phẩm và có thể dùng làm tài liệu học bổ trợ thực tế cho lập trình web.','/thumbnails/courses/vnpay-laravel-payment.jpg',NULL,'/videos/vnpay-laravel-payment/vnpay-laravel-payment-01-payment-flow-trong-e-learning-hoat-dong-the-nao.mp4',NULL,599000.00,0.00,'beginner','vi','\"Có tinh thần tự học, biết sử dụng máy tính cơ bản. Một số khóa kỹ thuật yêu cầu biết HTML/CSS, JavaScript, PHP hoặc Laravel cơ bản.\"','\"Hoàn thành 30 bài học, nắm được kiến thức chính và biết áp dụng vào đồ án hoặc dự án web thực tế.\"','published',1,'2026-06-27 08:00:00',NULL,NULL,'2026-06-27 08:00:00','2026-06-27 08:00:00',599000.00),
(29,2,'Lộ trình xin việc Web Developer cho sinh viên IT','lo-trinh-xin-viec-web-developer-cho-sinh-vien-it','Khóa học demo MindHub gồm 30 video, bám theo lộ trình Lộ trình Web Developer.','Khóa học được seed từ file prompt NotebookLM MindHub_CAREER_WEBDEV_30_NotebookLM_Prompts.md. Nội dung dùng cho demo sản phẩm và có thể dùng làm tài liệu học bổ trợ thực tế cho lập trình web.','/thumbnails/courses/career-webdev.jpg',NULL,'/videos/career-webdev/career-webdev-01-web-developer-can-hoc-gi-de-di-thuc-tap.mp4',NULL,499000.00,0.00,'beginner','vi','\"Có tinh thần tự học, biết sử dụng máy tính cơ bản. Một số khóa kỹ thuật yêu cầu biết HTML/CSS, JavaScript, PHP hoặc Laravel cơ bản.\"','\"Hoàn thành 30 bài học, nắm được kiến thức chính và biết áp dụng vào đồ án hoặc dự án web thực tế.\"','published',1,'2026-06-27 08:00:00',NULL,NULL,'2026-06-27 08:00:00','2026-06-27 08:00:00',499000.00),
(30,2,'Phỏng vấn Backend Developer Fresher','phong-van-backend-developer-fresher','Khóa học demo MindHub gồm 30 video, bám theo lộ trình Phỏng vấn Backend Developer.','Khóa học được seed từ file prompt NotebookLM MindHub_BACKEND_INTERVIEW_30_NotebookLM_Prompts.md. Nội dung dùng cho demo sản phẩm và có thể dùng làm tài liệu học bổ trợ thực tế cho lập trình web.','/thumbnails/courses/backend-interview.jpg',NULL,'/videos/backend-interview/backend-interview-01-backend-developer-fresher-can-biet-gi.mp4',NULL,499000.00,0.00,'beginner','vi','\"Có tinh thần tự học, biết sử dụng máy tính cơ bản. Một số khóa kỹ thuật yêu cầu biết HTML/CSS, JavaScript, PHP hoặc Laravel cơ bản.\"','\"Hoàn thành 30 bài học, nắm được kiến thức chính và biết áp dụng vào đồ án hoặc dự án web thực tế.\"','published',1,'2026-06-27 08:00:00',NULL,NULL,'2026-06-27 08:00:00','2026-06-27 08:00:00',499000.00),
(31,17,'COURSE_PUBLISHED Laravel API Featured','cat-course-published-laravel-api-featured','Khóa Laravel API public, featured.','Học xây dựng REST API với Laravel, MySQL, Resource, Service, Repository.','https://example.com/images/cat-laravel-api.jpg',NULL,'https://example.com/videos/cat-laravel-api.mp4',NULL,1200000.00,0.00,'beginner','vi','\"Biết PHP cơ bản\"','\"Xây dựng được REST API Laravel\"','published',1,'2026-07-27 17:47:13',NULL,NULL,'2026-08-06 17:47:13','2026-08-06 17:47:13',1200000.00),
(32,17,'COURSE_PUBLISHED PHP MySQL Best Selling','cat-course-published-php-mysql-best-selling','Khóa PHP MySQL nhiều enrollment nhất.','Học PHP, MySQL, database design.','https://example.com/images/cat-php-mysql.jpg',NULL,'https://example.com/videos/cat-php-mysql.mp4',NULL,900000.00,0.00,'beginner','vi','\"Không yêu cầu kinh nghiệm\"','\"Nắm được PHP và MySQL căn bản\"','published',0,'2026-07-17 17:47:13',NULL,NULL,'2026-08-06 17:47:13','2026-08-06 17:47:13',900000.00),
(33,18,'COURSE_PUBLISHED React Latest','cat-course-published-react-latest','Khóa React mới nhất.','Học React, component, state, props.','https://example.com/images/cat-react-latest.jpg',NULL,'https://example.com/videos/cat-react-latest.mp4',NULL,1500000.00,0.00,'beginner','vi','\"Biết HTML, CSS, JavaScript\"','\"Xây dựng được UI React\"','published',1,'2026-08-05 17:47:13',NULL,NULL,'2026-08-06 17:47:13','2026-08-06 17:47:13',1500000.00),
(34,18,'COURSE_PUBLISHED Free UI Design','cat-course-published-free-ui-design','Khóa miễn phí để test sort price_asc.','Học UI design căn bản.','https://example.com/images/cat-free-ui-design.jpg',NULL,'https://example.com/videos/cat-free-ui-design.mp4',NULL,0.00,0.00,'beginner','vi','\"Không yêu cầu\"','\"Biết thiết kế giao diện cơ bản\"','published',0,'2026-08-03 17:47:13',NULL,NULL,'2026-08-06 17:47:13','2026-08-06 17:47:13',0.00),
(35,17,'COURSE_DRAFT Not Public','cat-course-draft-not-public','Course draft, không được public.','Dữ liệu test không public.','https://example.com/images/cat-draft.jpg',NULL,NULL,NULL,100000.00,0.00,'beginner','vi',NULL,NULL,'draft',0,NULL,NULL,NULL,'2026-08-06 17:47:13','2026-08-06 17:47:13',100000.00),
(36,17,'COURSE_HIDDEN Not Public','cat-course-hidden-not-public','Course hidden, không được public.','Dữ liệu test hidden.','https://example.com/images/cat-hidden.jpg',NULL,NULL,NULL,100000.00,0.00,'beginner','vi',NULL,NULL,'hidden',1,'2026-08-04 17:47:13',NULL,NULL,'2026-08-06 17:47:13','2026-08-06 17:47:13',100000.00),
(37,17,'COURSE_PENDING_REVIEW Not Public','cat-course-pending-review-not-public','Course pending_review, không được public.','Dữ liệu test pending review.','https://example.com/images/cat-pending.jpg',NULL,NULL,NULL,200000.00,0.00,'beginner','vi',NULL,NULL,'pending_review',0,NULL,NULL,NULL,'2026-08-06 17:47:13','2026-08-06 17:47:13',200000.00),
(38,17,'COURSE_REJECTED Not Public','cat-course-rejected-not-public','Course rejected, không được public.','Dữ liệu test rejected.','https://example.com/images/cat-rejected.jpg',NULL,NULL,NULL,300000.00,0.00,'beginner','vi',NULL,NULL,'rejected',0,NULL,NULL,'Nội dung chưa đạt yêu cầu.','2026-08-06 17:47:13','2026-08-06 17:47:13',300000.00),
(39,17,'COURSE_APPROVED Not Public','cat-course-approved-not-public','Course approved nhưng chưa published.','Dữ liệu test approved.','https://example.com/images/cat-approved.jpg',NULL,NULL,NULL,400000.00,0.00,'beginner','vi',NULL,NULL,'approved',0,NULL,NULL,NULL,'2026-08-06 17:47:13','2026-08-06 17:47:13',400000.00),
(40,17,'COURSE_PUBLISHED Soft Deleted Not Public','cat-course-published-soft-deleted-not-public','Course published nhưng soft deleted.','Dữ liệu test soft delete course.','https://example.com/images/cat-soft-deleted.jpg',NULL,NULL,NULL,500000.00,0.00,'beginner','vi',NULL,NULL,'published',1,'2026-08-01 17:47:13',NULL,NULL,'2026-08-06 17:47:13','2026-08-06 17:47:13',500000.00),
(453,2,'Draft Course 6a797a98147c8','draft-course-6a797a98147c8',NULL,NULL,NULL,NULL,NULL,NULL,100000.00,0.00,'beginner','vi',NULL,NULL,'draft',0,NULL,NULL,NULL,'2026-08-10 07:15:36','2026-08-10 07:15:36',100000.00),
(454,2,'New Course 6a797a982c4a4','new-course-6a797a982c4a4',NULL,NULL,NULL,NULL,NULL,NULL,100000.00,0.00,'beginner','vi',NULL,NULL,'published',0,NULL,NULL,NULL,'2026-08-10 07:15:36','2026-08-10 07:15:36',100000.00),
(455,2,'Draft Course 6a797a98357f6','draft-course-6a797a98357f6',NULL,NULL,NULL,NULL,NULL,NULL,100000.00,0.00,'beginner','vi',NULL,NULL,'draft',0,NULL,NULL,NULL,'2026-08-10 07:15:36','2026-08-10 07:15:36',100000.00),
(457,2,'Test Course Unpublished 6a797a9896d4a','test-course-unpub-6a797a9896d4a',NULL,NULL,NULL,NULL,NULL,NULL,100000.00,0.00,'beginner','vi',NULL,NULL,'draft',0,NULL,NULL,NULL,'2026-08-10 07:15:36','2026-08-10 07:15:36',100000.00),
(458,2,'Test Course Success 6a797a98a113c','test-course-success-6a797a98a113c',NULL,NULL,NULL,NULL,NULL,NULL,100000.00,0.00,'beginner','vi',NULL,NULL,'published',0,NULL,NULL,NULL,'2026-08-10 07:15:36','2026-08-10 07:15:36',100000.00),
(459,2,'Test Course Next Sec 6a797a98a8bf8','test-course-nextsec-6a797a98a8bf8',NULL,NULL,NULL,NULL,NULL,NULL,100000.00,0.00,'beginner','vi',NULL,NULL,'published',0,NULL,NULL,NULL,'2026-08-10 07:15:36','2026-08-10 07:15:36',100000.00),
(460,2,'Test Course Last 6a797a98ae6a4','test-course-last-6a797a98ae6a4',NULL,NULL,NULL,NULL,NULL,NULL,100000.00,0.00,'beginner','vi',NULL,NULL,'published',0,NULL,NULL,NULL,'2026-08-10 07:15:36','2026-08-10 07:15:36',100000.00),
(461,2,'Latest Enrolled Course 6a797a98f05ea','latest-enrolled-6a797a98f05ea',NULL,NULL,NULL,NULL,NULL,NULL,200000.00,0.00,'beginner','vi',NULL,NULL,'published',0,NULL,NULL,NULL,'2026-08-10 07:15:36','2026-08-10 07:15:36',200000.00),
(462,2,'Empty Course 6a797a99010a1','empty-course-6a797a99010a1',NULL,NULL,NULL,NULL,NULL,NULL,150000.00,0.00,'beginner','vi',NULL,NULL,'published',0,NULL,NULL,NULL,'2026-08-10 07:15:37','2026-08-10 07:15:37',150000.00),
(463,2,'Draft Course 6a797a99926e4','draft-course-6a797a99926e4',NULL,NULL,NULL,NULL,NULL,NULL,100000.00,0.00,'beginner','vi',NULL,NULL,'draft',0,NULL,NULL,NULL,'2026-08-10 07:15:37','2026-08-10 07:15:37',100000.00);
/*!40000 ALTER TABLE `courses` ENABLE KEYS */;
UNLOCK TABLES;
COMMIT;
SET AUTOCOMMIT=@OLD_AUTOCOMMIT;

--
-- Table structure for table `enrollments`
--

DROP TABLE IF EXISTS `enrollments`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `enrollments` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `user_id` bigint(20) unsigned NOT NULL,
  `course_id` bigint(20) unsigned NOT NULL,
  `order_id` bigint(20) unsigned NOT NULL,
  `status` enum('active','completed','inactive') CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'active',
  `progress_percent` decimal(5,2) NOT NULL DEFAULT 0.00,
  `enrolled_at` datetime NOT NULL DEFAULT current_timestamp(),
  `expires_at` datetime DEFAULT NULL,
  `completed_at` datetime DEFAULT NULL,
  `last_accessed_at` datetime DEFAULT NULL,
  `created_at` datetime NOT NULL DEFAULT current_timestamp(),
  `updated_at` datetime NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  PRIMARY KEY (`id`),
  UNIQUE KEY `uq_enrollments_user_course` (`user_id`,`course_id`),
  UNIQUE KEY `uq_enrollments_order` (`order_id`),
  KEY `idx_enrollments_course_status` (`course_id`,`status`),
  CONSTRAINT `fk_enrollments_course` FOREIGN KEY (`course_id`) REFERENCES `courses` (`id`) ON UPDATE CASCADE,
  CONSTRAINT `fk_enrollments_order` FOREIGN KEY (`order_id`) REFERENCES `orders` (`id`) ON UPDATE CASCADE,
  CONSTRAINT `fk_enrollments_user` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON UPDATE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=50 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_uca1400_ai_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `enrollments`
--

SET @OLD_AUTOCOMMIT=@@AUTOCOMMIT, @@AUTOCOMMIT=0;
LOCK TABLES `enrollments` WRITE;
/*!40000 ALTER TABLE `enrollments` DISABLE KEYS */;
INSERT INTO `enrollments` VALUES
(1,4,1,1,'active',0.00,'2026-06-20 07:50:00',NULL,NULL,'2026-06-22 20:35:00','2026-06-20 07:50:00','2026-08-10 07:15:36'),
(2,6,1,6,'completed',100.00,'2026-05-01 07:40:00',NULL,'2026-05-03 09:00:00','2026-05-03 08:50:00','2026-05-01 07:40:00','2026-05-03 09:00:00'),
(3,4,2,7,'active',25.00,'2026-06-21 08:30:00',NULL,NULL,'2026-06-21 09:10:00','2026-06-21 08:30:00','2026-06-21 09:10:00'),
(4,21,31,11,'active',35.50,'2026-07-29 17:47:13',NULL,NULL,'2026-08-05 17:47:13','2026-08-06 17:47:13','2026-08-06 17:47:13'),
(5,22,31,12,'completed',100.00,'2026-07-30 17:47:13',NULL,'2026-08-05 17:47:13','2026-08-05 17:47:13','2026-08-06 17:47:13','2026-08-06 17:47:13'),
(6,21,32,13,'completed',100.00,'2026-07-22 17:47:13',NULL,'2026-08-03 17:47:13','2026-08-03 17:47:13','2026-08-06 17:47:13','2026-08-06 17:47:13'),
(7,22,32,14,'active',60.00,'2026-07-23 17:47:13',NULL,NULL,'2026-08-04 17:47:13','2026-08-06 17:47:13','2026-08-06 17:47:13'),
(8,23,32,15,'active',20.00,'2026-07-24 17:47:13',NULL,NULL,'2026-08-05 17:47:13','2026-08-06 17:47:13','2026-08-06 17:47:13'),
(9,21,33,16,'active',15.00,'2026-08-05 17:47:13',NULL,NULL,'2026-08-06 17:47:13','2026-08-06 17:47:13','2026-08-06 17:47:13'),
(10,22,34,17,'completed',100.00,'2026-08-04 17:47:13',NULL,'2026-08-05 17:47:13','2026-08-05 17:47:13','2026-08-06 17:47:13','2026-08-06 17:47:13'),
(37,4,453,243,'active',0.00,'2026-08-10 07:15:36',NULL,NULL,NULL,'2026-08-10 07:15:36','2026-08-10 07:15:36'),
(38,4,455,244,'active',0.00,'2026-08-10 07:15:36',NULL,NULL,NULL,'2026-08-10 07:15:36','2026-08-10 07:15:36'),
(39,4,457,245,'active',0.00,'2026-08-10 07:15:36',NULL,NULL,NULL,'2026-08-10 07:15:36','2026-08-10 07:15:36'),
(40,4,458,246,'active',0.00,'2026-08-10 07:15:36',NULL,NULL,NULL,'2026-08-10 07:15:36','2026-08-10 07:15:36'),
(41,4,459,247,'active',0.00,'2026-08-10 07:15:36',NULL,NULL,NULL,'2026-08-10 07:15:36','2026-08-10 07:15:36'),
(42,4,460,248,'active',0.00,'2026-08-10 07:15:36',NULL,NULL,NULL,'2026-08-10 07:15:36','2026-08-10 07:15:36'),
(43,4,461,249,'active',0.00,'2026-08-15 07:15:36',NULL,NULL,NULL,'2026-08-10 07:15:36','2026-08-10 07:15:36'),
(44,4,462,250,'active',0.00,'2026-08-20 07:15:37',NULL,NULL,NULL,'2026-08-10 07:15:37','2026-08-10 07:15:37'),
(45,4,463,251,'active',0.00,'2026-08-10 07:15:37',NULL,NULL,NULL,'2026-08-10 07:15:37','2026-08-10 07:15:37'),
(47,24,30,20,'active',0.00,'2026-08-19 02:26:43',NULL,NULL,NULL,'2026-08-18 19:26:43','2026-08-18 19:26:43'),
(48,24,29,274,'active',0.00,'2026-08-20 12:35:49',NULL,NULL,NULL,'2026-08-20 05:35:49','2026-08-20 05:35:49'),
(49,24,31,18,'active',0.00,'2026-08-20 23:33:20',NULL,NULL,NULL,'2026-08-20 16:33:20','2026-08-20 16:33:20');
/*!40000 ALTER TABLE `enrollments` ENABLE KEYS */;
UNLOCK TABLES;
COMMIT;
SET AUTOCOMMIT=@OLD_AUTOCOMMIT;

--
-- Table structure for table `faqs`
--

DROP TABLE IF EXISTS `faqs`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `faqs` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `question` varchar(1000) NOT NULL,
  `answer` text NOT NULL,
  `type` varchar(100) NOT NULL DEFAULT 'general',
  `status` enum('active','inactive') NOT NULL DEFAULT 'active',
  `sort_order` int(10) unsigned NOT NULL DEFAULT 0,
  `created_at` datetime NOT NULL DEFAULT current_timestamp(),
  `updated_at` datetime NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  PRIMARY KEY (`id`),
  KEY `idx_faqs_type_status_order` (`type`,`status`,`sort_order`)
) ENGINE=InnoDB AUTO_INCREMENT=5 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `faqs`
--

SET @OLD_AUTOCOMMIT=@@AUTOCOMMIT, @@AUTOCOMMIT=0;
LOCK TABLES `faqs` WRITE;
/*!40000 ALTER TABLE `faqs` DISABLE KEYS */;
INSERT INTO `faqs` VALUES
(1,'Tôi có được học lại khóa đã mua không?','Có. Sau khi thanh toán thành công và có enrollment active, bạn có thể vào học lại bất kỳ lúc nào theo chính sách của nền tảng.','general','active',1,'2026-01-01 08:00:00','2026-01-01 08:00:00'),
(2,'Nếu thanh toán thất bại thì sao?','Đơn hàng thất bại không tạo enrollment. Bạn có thể dùng chức năng thanh toán lại nếu đơn còn hợp lệ.','payment','active',2,'2026-01-01 08:05:00','2026-01-01 08:05:00'),
(3,'Khóa Laravel có phù hợp cho người mới không?','Có. Khóa bắt đầu từ route, controller, request/resource rồi đi tới auth session, payment và learning flow.','course','active',3,'2026-01-01 08:10:00','2026-01-01 08:10:00'),
(4,'FAQ inactive demo','Dữ liệu này dùng để test filter status inactive.','general','inactive',4,'2026-01-01 08:15:00','2026-01-01 08:15:00');
/*!40000 ALTER TABLE `faqs` ENABLE KEYS */;
UNLOCK TABLES;
COMMIT;
SET AUTOCOMMIT=@OLD_AUTOCOMMIT;

--
-- Table structure for table `instructor_profiles`
--

DROP TABLE IF EXISTS `instructor_profiles`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `instructor_profiles` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `user_id` bigint(20) unsigned NOT NULL,
  `bio` text DEFAULT NULL,
  `expertise` varchar(500) DEFAULT NULL,
  `experience_years` smallint(5) unsigned NOT NULL DEFAULT 0,
  `instructor_rank` enum('bronze','silver','gold','diamond') NOT NULL DEFAULT 'bronze',
  `created_at` datetime NOT NULL DEFAULT current_timestamp(),
  `updated_at` datetime NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  PRIMARY KEY (`id`),
  UNIQUE KEY `uq_instructor_profiles_user` (`user_id`),
  CONSTRAINT `fk_instructor_profiles_user` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=8 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `instructor_profiles`
--

SET @OLD_AUTOCOMMIT=@@AUTOCOMMIT, @@AUTOCOMMIT=0;
LOCK TABLES `instructor_profiles` WRITE;
/*!40000 ALTER TABLE `instructor_profiles` DISABLE KEYS */;
INSERT INTO `instructor_profiles` VALUES
(1,2,'Backend Developer chuyên Laravel, MySQL và thiết kế REST API cho sản phẩm giáo dục.','Laravel, PHP, MySQL, API Design, Payment Flow',6,'bronze','2026-01-05 08:00:00','2026-06-20 10:00:00'),
(2,3,'Frontend/AI Product Mentor, tập trung React, UI/UX và ứng dụng AI trong học tập.','React, UI/UX, AI Product, Learning Analytics',5,'bronze','2026-01-05 08:10:00','2026-06-18 10:00:00'),
(3,17,'Giảng viên Laravel/PHP có kinh nghiệm xây dựng REST API thực tế.','Laravel, PHP, MySQL, REST API',6,'bronze','2026-08-06 17:47:13','2026-08-06 17:47:13'),
(4,18,'Giảng viên Frontend chuyên React và UI Design.','React, JavaScript, UI/UX',5,'bronze','2026-08-06 17:47:13','2026-08-06 17:47:13'),
(5,19,'Profile test instructor inactive.','Testing',2,'bronze','2026-08-06 17:47:13','2026-08-06 17:47:13'),
(6,20,'Profile test instructor locked.','Testing',3,'bronze','2026-08-06 17:47:13','2026-08-06 17:47:13'),
(7,25,'123456789123456789123456789123456789123456789123456789123456789123456789123456789123456789123456789123456789123456789123456789123456789123456789123456789123456789123456789123456789123456789123456789123456789','Development',5,'bronze','2026-08-08 06:05:34','2026-08-08 06:05:34');
/*!40000 ALTER TABLE `instructor_profiles` ENABLE KEYS */;
UNLOCK TABLES;
COMMIT;
SET AUTOCOMMIT=@OLD_AUTOCOMMIT;

--
-- Table structure for table `lesson_assets`
--

DROP TABLE IF EXISTS `lesson_assets`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `lesson_assets` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `lesson_id` bigint(20) unsigned NOT NULL,
  `title` varchar(255) NOT NULL,
  `file_url` varchar(2048) NOT NULL,
  `file_id` varchar(255) DEFAULT NULL,
  `file_name` varchar(255) NOT NULL,
  `file_type` varchar(100) DEFAULT NULL,
  `file_size` bigint(20) unsigned DEFAULT NULL,
  `note` varchar(1000) DEFAULT NULL,
  `created_at` datetime NOT NULL DEFAULT current_timestamp(),
  `updated_at` datetime NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  PRIMARY KEY (`id`),
  UNIQUE KEY `uq_lesson_assets_file_id` (`file_id`),
  KEY `idx_lesson_assets_lesson` (`lesson_id`),
  CONSTRAINT `fk_lesson_assets_lesson` FOREIGN KEY (`lesson_id`) REFERENCES `lessons` (`id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=5 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `lesson_assets`
--

SET @OLD_AUTOCOMMIT=@@AUTOCOMMIT, @@AUTOCOMMIT=0;
LOCK TABLES `lesson_assets` WRITE;
/*!40000 ALTER TABLE `lesson_assets` DISABLE KEYS */;
INSERT INTO `lesson_assets` VALUES
(1,1,'Slide giới thiệu Laravel API','/demo/assets/laravel-api-intro.pdf',NULL,'laravel-api-intro.pdf','pdf',1250000,'Tài liệu dùng cho bài mở đầu.','2026-01-10 11:00:00','2026-08-21 01:31:06'),
(2,2,'Checklist Repository Service Resource','/demo/assets/repository-service-checklist.pdf',NULL,'repository-service-checklist.pdf','pdf',850000,'Checklist code sạch cho backend.','2026-01-10 11:05:00','2026-08-21 01:31:06'),
(3,3,'Sơ đồ custom session flow','/demo/assets/custom-session-flow.png',NULL,'custom-session-flow.png','image/png',420000,'Minh họa login/refresh/logout.','2026-01-10 11:10:00','2026-08-21 01:31:06'),
(4,11,'Prompt mẫu AI summary','/demo/assets/ai-summary-prompt.md',NULL,'ai-summary-prompt.md','text/markdown',12000,'Prompt demo không chứa dữ liệu nhạy cảm.','2026-02-15 11:00:00','2026-08-21 01:31:06');
/*!40000 ALTER TABLE `lesson_assets` ENABLE KEYS */;
UNLOCK TABLES;
COMMIT;
SET AUTOCOMMIT=@OLD_AUTOCOMMIT;

--
-- Table structure for table `lesson_notes`
--

DROP TABLE IF EXISTS `lesson_notes`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `lesson_notes` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `enrollment_id` bigint(20) unsigned NOT NULL,
  `lesson_id` bigint(20) unsigned NOT NULL,
  `content` text NOT NULL,
  `note_time_second` int(10) unsigned DEFAULT NULL,
  `created_at` datetime NOT NULL DEFAULT current_timestamp(),
  `updated_at` datetime NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  PRIMARY KEY (`id`),
  KEY `idx_lesson_notes_enrollment_lesson` (`enrollment_id`,`lesson_id`),
  KEY `fk_lesson_notes_lesson` (`lesson_id`),
  CONSTRAINT `fk_lesson_notes_enrollment` FOREIGN KEY (`enrollment_id`) REFERENCES `enrollments` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT `fk_lesson_notes_lesson` FOREIGN KEY (`lesson_id`) REFERENCES `lessons` (`id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=4 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `lesson_notes`
--

SET @OLD_AUTOCOMMIT=@@AUTOCOMMIT, @@AUTOCOMMIT=0;
LOCK TABLES `lesson_notes` WRITE;
/*!40000 ALTER TABLE `lesson_notes` DISABLE KEYS */;
INSERT INTO `lesson_notes` VALUES
(1,1,3,'Chỗ này cần nhớ: revoked_at khác NULL thì middleware phải từ chối session.',950,'2026-06-22 20:30:00','2026-06-22 20:30:00'),
(2,1,2,'Service không query trực tiếp quá nhiều, Repository chịu trách nhiệm query.',NULL,'2026-06-20 08:45:00','2026-06-20 08:45:00'),
(3,2,6,'Payment paid mới tạo revenue và enrollment, không tạo khi pending.',1200,'2026-05-03 08:30:00','2026-05-03 08:30:00');
/*!40000 ALTER TABLE `lesson_notes` ENABLE KEYS */;
UNLOCK TABLES;
COMMIT;
SET AUTOCOMMIT=@OLD_AUTOCOMMIT;

--
-- Table structure for table `lesson_progress`
--

DROP TABLE IF EXISTS `lesson_progress`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `lesson_progress` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `enrollment_id` bigint(20) unsigned NOT NULL,
  `lesson_id` bigint(20) unsigned NOT NULL,
  `status` enum('not_started','in_progress','completed') NOT NULL DEFAULT 'not_started',
  `started_at` datetime DEFAULT NULL,
  `completed_at` datetime DEFAULT NULL,
  `learning_duration_seconds` int(10) unsigned NOT NULL DEFAULT 0,
  `last_accessed_at` datetime DEFAULT NULL,
  `created_at` datetime NOT NULL DEFAULT current_timestamp(),
  `updated_at` datetime NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  PRIMARY KEY (`id`),
  UNIQUE KEY `uq_lesson_progress_enrollment_lesson` (`enrollment_id`,`lesson_id`),
  KEY `idx_lesson_progress_lesson` (`lesson_id`),
  CONSTRAINT `fk_lesson_progress_enrollment` FOREIGN KEY (`enrollment_id`) REFERENCES `enrollments` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT `fk_lesson_progress_lesson` FOREIGN KEY (`lesson_id`) REFERENCES `lessons` (`id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=31 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `lesson_progress`
--

SET @OLD_AUTOCOMMIT=@@AUTOCOMMIT, @@AUTOCOMMIT=0;
LOCK TABLES `lesson_progress` WRITE;
/*!40000 ALTER TABLE `lesson_progress` DISABLE KEYS */;
INSERT INTO `lesson_progress` VALUES
(5,2,1,'completed','2026-05-01 08:00:00','2026-05-01 08:20:00',1000,'2026-05-01 08:20:00','2026-05-01 08:00:00','2026-05-01 08:20:00'),
(6,2,2,'completed','2026-05-01 08:30:00','2026-05-01 09:00:00',1600,'2026-05-01 09:00:00','2026-05-01 08:30:00','2026-05-01 09:00:00'),
(7,2,3,'completed','2026-05-02 08:00:00','2026-05-02 08:45:00',2200,'2026-05-02 08:45:00','2026-05-02 08:00:00','2026-05-02 08:45:00'),
(8,2,6,'completed','2026-05-03 08:00:00','2026-05-03 08:50:00',2300,'2026-05-03 08:50:00','2026-05-03 08:00:00','2026-05-03 08:50:00'),
(25,47,526,'in_progress','2026-08-18 19:26:46',NULL,600,'2026-08-20 12:55:19','2026-08-18 19:26:46','2026-08-20 12:55:19'),
(26,47,527,'in_progress','2026-08-18 19:26:49',NULL,0,'2026-08-20 12:29:07','2026-08-18 19:26:49','2026-08-20 12:29:07'),
(27,47,528,'in_progress','2026-08-20 04:53:08',NULL,0,'2026-08-20 12:29:07','2026-08-20 04:53:08','2026-08-20 12:29:07'),
(28,47,529,'in_progress','2026-08-20 04:58:19',NULL,0,'2026-08-20 12:29:08','2026-08-20 04:58:19','2026-08-20 12:29:08'),
(29,47,530,'in_progress','2026-08-20 05:31:16',NULL,0,'2026-08-20 12:29:10','2026-08-20 05:31:16','2026-08-20 12:29:10'),
(30,48,496,'in_progress','2026-08-20 05:35:55',NULL,0,'2026-08-20 05:35:55','2026-08-20 05:35:55','2026-08-20 05:35:55');
/*!40000 ALTER TABLE `lesson_progress` ENABLE KEYS */;
UNLOCK TABLES;
COMMIT;
SET AUTOCOMMIT=@OLD_AUTOCOMMIT;

--
-- Table structure for table `lessons`
--

DROP TABLE IF EXISTS `lessons`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `lessons` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `course_section_id` bigint(20) unsigned NOT NULL,
  `course_id` bigint(20) unsigned NOT NULL,
  `title` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `lesson_type` enum('video','text','document') CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'video',
  `content` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `video_url` varchar(2048) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `video_provider` varchar(255) DEFAULT 'local',
  `video_id` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `video_duration_seconds` int(10) unsigned NOT NULL DEFAULT 0,
  `is_preview` tinyint(1) NOT NULL DEFAULT 0,
  `status` enum('draft','published','hidden') CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'draft',
  `sort_order` int(10) unsigned NOT NULL DEFAULT 0,
  `created_at` datetime NOT NULL DEFAULT current_timestamp(),
  `updated_at` datetime NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  PRIMARY KEY (`id`),
  UNIQUE KEY `uq_lessons_section_order` (`course_section_id`,`sort_order`),
  UNIQUE KEY `uq_lessons_video_id` (`video_id`),
  KEY `idx_lessons_course` (`course_id`),
  KEY `fk_lessons_section_course` (`course_section_id`,`course_id`),
  CONSTRAINT `fk_lessons_section_course` FOREIGN KEY (`course_section_id`, `course_id`) REFERENCES `course_sections` (`id`, `course_id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=721 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_uca1400_ai_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `lessons`
--

SET @OLD_AUTOCOMMIT=@@AUTOCOMMIT, @@AUTOCOMMIT=0;
LOCK TABLES `lessons` WRITE;
/*!40000 ALTER TABLE `lessons` DISABLE KEYS */;
INSERT INTO `lessons` VALUES
(1,1,1,'Giới thiệu REST API trong Laravel','video','Giới thiệu cách xây dựng REST API trong Laravel, cấu trúc route, controller, request và resource cho dự án MindHub.','/demo/videos/laravel-01-intro.mp4','local',NULL,900,1,'published',1,'2026-01-10 10:00:00','2026-08-18 19:28:52'),
(2,1,1,'Repository Service Resource là gì?','text','Repository chứa query, Service chứa business logic, Controller chỉ điều phối request response. Quy tắc này giúp code dễ test và dễ bảo trì.',NULL,'local',NULL,0,0,'published',2,'2026-01-10 10:10:00','2026-06-20 10:00:00'),
(3,2,1,'Custom session và refresh token','video','Custom session dùng bảng sessions, lưu refresh_token_hash, expires_at và revoked_at. Middleware auth.session kiểm tra access token và active.user kiểm tra trạng thái tài khoản.','/demo/videos/laravel-03-session.mp4','local',NULL,1800,0,'published',1,'2026-01-10 10:20:00','2026-08-18 19:28:52'),
(4,4,1,'Lesson hidden demo','text','Bài học hidden dùng để test learner không được xem nội dung ẩn.',NULL,'local',NULL,0,0,'hidden',1,'2026-01-10 10:30:00','2026-06-20 10:00:00'),
(5,4,1,'Lesson draft demo','text','Bài học draft dùng để test filter draft.',NULL,'local',NULL,0,0,'draft',2,'2026-01-10 10:40:00','2026-06-20 10:00:00'),
(6,3,1,'Payment và enrollment sau khi paid','video','Payment flow gồm tạo order pending, áp coupon, xác nhận paid, tạo enrollment và revenue. Không cấp quyền học trước khi paid.','/demo/videos/laravel-06-payment.mp4','local',NULL,2100,0,'published',1,'2026-01-10 10:50:00','2026-06-20 10:00:00'),
(7,5,2,'PHP request response cơ bản','video','PHP xử lý request, validate input và trả dữ liệu qua JSON response.','/demo/videos/php-01-request-response.mp4','local',NULL,1200,1,'published',1,'2026-01-12 10:00:00','2026-06-15 10:00:00'),
(8,6,2,'MySQL transaction trong thanh toán','text','MySQL transaction giúp đảm bảo order, enrollment và revenue không bị lệch dữ liệu.',NULL,'local',NULL,0,0,'published',1,'2026-01-12 10:10:00','2026-06-15 10:00:00'),
(9,7,3,'Component hóa giao diện khóa học','video','React component nên tách theo UI state, dữ liệu API và hành vi người dùng.','/demo/videos/react-01-components.mp4','local',NULL,1500,1,'published',1,'2026-01-20 10:00:00','2026-06-16 10:00:00'),
(10,8,3,'Learning dashboard với API thật','text','Learning dashboard cần trạng thái loading, empty, error và dữ liệu progress rõ ràng.',NULL,'local',NULL,0,0,'published',1,'2026-01-20 10:10:00','2026-06-16 10:00:00'),
(11,9,6,'AI tóm tắt bài học an toàn','text','AI summary chỉ dùng nội dung bài học, không gửi token hay dữ liệu nhạy cảm lên provider.',NULL,'local',NULL,0,1,'published',1,'2026-02-15 10:00:00','2026-06-18 10:00:00'),
(12,9,6,'AI phân tích điểm yếu sau quiz','video','AI phân tích điểm yếu dựa trên quiz_attempt_answers.option_id, score_earned và explanation của câu hỏi.','/demo/videos/ai-02-quiz-weakness.mp4','local',NULL,1800,0,'published',2,'2026-02-15 10:10:00','2026-06-18 10:00:00'),
(13,10,7,'Git branch và pull request','video','Git branch giúp team đồ án làm song song, pull request giúp review trước khi merge.','/demo/videos/git-01-branch-pr.mp4','local',NULL,900,1,'published',1,'2026-01-15 10:00:00','2026-06-01 10:00:00'),
(14,11,4,'NodeJS draft lesson','text','Nội dung draft NodeJS chưa public.',NULL,'local',NULL,0,0,'draft',1,'2026-02-01 10:00:00','2026-02-01 10:00:00'),
(15,12,8,'Module boundary trong Laravel','text','Advanced Laravel dùng module boundary để giảm phụ thuộc chéo giữa Auth, Payment và Learning.',NULL,'local',NULL,0,0,'published',1,'2026-06-01 10:00:00','2026-06-18 08:00:00'),
(16,13,9,'AI trong học tập là gì?','video','Nội dung video: AI trong học tập là gì?. File seed theo course_folder ai-learning.','/videos/ai-learning/ai-learning-01-ai-trong-hoc-tap-la-gi.mp4','local',NULL,600,1,'published',1,'2026-06-27 08:00:00','2026-06-27 08:00:00'),
(17,13,9,'AI có thể hỗ trợ sinh viên IT như thế nào?','video','Nội dung video: AI có thể hỗ trợ sinh viên IT như thế nào?. File seed theo course_folder ai-learning.','/videos/ai-learning/ai-learning-02-ai-co-the-ho-tro-sinh-vien-it-nhu-the-nao.mp4','local',NULL,600,0,'published',2,'2026-06-27 08:00:00','2026-06-27 08:00:00'),
(18,13,9,'Prompt là gì và vì sao quan trọng?','video','Nội dung video: Prompt là gì và vì sao quan trọng?. File seed theo course_folder ai-learning.','/videos/ai-learning/ai-learning-03-prompt-la-gi-va-vi-sao-quan-trong.mp4','local',NULL,600,0,'published',3,'2026-06-27 08:00:00','2026-06-27 08:00:00'),
(19,13,9,'Cách đặt câu hỏi để AI trả lời đúng hơn','video','Nội dung video: Cách đặt câu hỏi để AI trả lời đúng hơn. File seed theo course_folder ai-learning.','/videos/ai-learning/ai-learning-04-cach-dat-cau-hoi-de-ai-tra-loi-dung-hon.mp4','local',NULL,600,0,'published',4,'2026-06-27 08:00:00','2026-06-27 08:00:00'),
(20,13,9,'Những giới hạn của AI mà người học phải biết','video','Nội dung video: Những giới hạn của AI mà người học phải biết. File seed theo course_folder ai-learning.','/videos/ai-learning/ai-learning-05-nhung-gioi-han-cua-ai-ma-nguoi-hoc-phai-biet.mp4','local',NULL,600,0,'published',5,'2026-06-27 08:00:00','2026-06-27 08:00:00'),
(21,14,9,'Dùng AI giải thích code và lỗi lập trình','video','Nội dung video: Dùng AI giải thích code và lỗi lập trình. File seed theo course_folder ai-learning.','/videos/ai-learning/ai-learning-06-dung-ai-giai-thich-code-va-loi-lap-trinh.mp4','local',NULL,600,0,'published',1,'2026-06-27 08:00:00','2026-06-27 08:00:00'),
(22,14,9,'Dùng AI tạo lộ trình học cá nhân','video','Nội dung video: Dùng AI tạo lộ trình học cá nhân. File seed theo course_folder ai-learning.','/videos/ai-learning/ai-learning-07-dung-ai-tao-lo-trinh-hoc-ca-nhan.mp4','local',NULL,600,0,'published',2,'2026-06-27 08:00:00','2026-06-27 08:00:00'),
(23,14,9,'Dùng AI học HTML CSS JavaScript hiệu quả','video','Nội dung video: Dùng AI học HTML CSS JavaScript hiệu quả. File seed theo course_folder ai-learning.','/videos/ai-learning/ai-learning-08-dung-ai-hoc-html-css-javascript-hieu-qua.mp4','local',NULL,600,0,'published',3,'2026-06-27 08:00:00','2026-06-27 08:00:00'),
(24,14,9,'Dùng AI học Laravel Backend hiệu quả','video','Nội dung video: Dùng AI học Laravel Backend hiệu quả. File seed theo course_folder ai-learning.','/videos/ai-learning/ai-learning-09-dung-ai-hoc-laravel-backend-hieu-qua.mp4','local',NULL,600,0,'published',4,'2026-06-27 08:00:00','2026-06-27 08:00:00'),
(25,14,9,'Dùng AI học React Frontend hiệu quả','video','Nội dung video: Dùng AI học React Frontend hiệu quả. File seed theo course_folder ai-learning.','/videos/ai-learning/ai-learning-10-dung-ai-hoc-react-frontend-hieu-qua.mp4','local',NULL,600,0,'published',5,'2026-06-27 08:00:00','2026-06-27 08:00:00'),
(26,15,9,'Ứng dụng AI để tóm tắt bài học','video','Nội dung video: Ứng dụng AI để tóm tắt bài học. File seed theo course_folder ai-learning.','/videos/ai-learning/ai-learning-11-ung-dung-ai-de-tom-tat-bai-hoc.mp4','local',NULL,600,0,'published',1,'2026-06-27 08:00:00','2026-06-27 08:00:00'),
(27,15,9,'Ứng dụng AI để tạo quiz từ lesson','video','Nội dung video: Ứng dụng AI để tạo quiz từ lesson. File seed theo course_folder ai-learning.','/videos/ai-learning/ai-learning-12-ung-dung-ai-de-tao-quiz-tu-lesson.mp4','local',NULL,600,0,'published',2,'2026-06-27 08:00:00','2026-06-27 08:00:00'),
(28,15,9,'AI gợi ý khóa học cá nhân hóa','video','Nội dung video: AI gợi ý khóa học cá nhân hóa. File seed theo course_folder ai-learning.','/videos/ai-learning/ai-learning-13-ai-goi-y-khoa-hoc-ca-nhan-hoa.mp4','local',NULL,600,0,'published',3,'2026-06-27 08:00:00','2026-06-27 08:00:00'),
(29,15,9,'AI phân tích điểm yếu sau quiz','video','Nội dung video: AI phân tích điểm yếu sau quiz. File seed theo course_folder ai-learning.','/videos/ai-learning/ai-learning-14-ai-phan-tich-diem-yeu-sau-quiz.mp4','local',NULL,600,0,'published',4,'2026-06-27 08:00:00','2026-06-27 08:00:00'),
(30,15,9,'AI cảnh báo nguy cơ bỏ học','video','Nội dung video: AI cảnh báo nguy cơ bỏ học. File seed theo course_folder ai-learning.','/videos/ai-learning/ai-learning-15-ai-canh-bao-nguy-co-bo-hoc.mp4','local',NULL,600,0,'published',5,'2026-06-27 08:00:00','2026-06-27 08:00:00'),
(31,16,9,'Prompt tạo nội dung video bài học','video','Nội dung video: Prompt tạo nội dung video bài học. File seed theo course_folder ai-learning.','/videos/ai-learning/ai-learning-16-prompt-tao-noi-dung-video-bai-hoc.mp4','local',NULL,600,0,'published',1,'2026-06-27 08:00:00','2026-06-27 08:00:00'),
(32,16,9,'Prompt viết README và tài liệu đồ án','video','Nội dung video: Prompt viết README và tài liệu đồ án. File seed theo course_folder ai-learning.','/videos/ai-learning/ai-learning-17-prompt-viet-readme-va-tai-lieu-do-an.mp4','local',NULL,600,0,'published',2,'2026-06-27 08:00:00','2026-06-27 08:00:00'),
(33,16,9,'Prompt review code Laravel và React','video','Nội dung video: Prompt review code Laravel và React. File seed theo course_folder ai-learning.','/videos/ai-learning/ai-learning-18-prompt-review-code-laravel-va-react.mp4','local',NULL,600,0,'published',3,'2026-06-27 08:00:00','2026-06-27 08:00:00'),
(34,16,9,'Prompt tạo test case cho API','video','Nội dung video: Prompt tạo test case cho API. File seed theo course_folder ai-learning.','/videos/ai-learning/ai-learning-19-prompt-tao-test-case-cho-api.mp4','local',NULL,600,0,'published',4,'2026-06-27 08:00:00','2026-06-27 08:00:00'),
(35,16,9,'Prompt chuẩn bị câu trả lời phỏng vấn IT','video','Nội dung video: Prompt chuẩn bị câu trả lời phỏng vấn IT. File seed theo course_folder ai-learning.','/videos/ai-learning/ai-learning-20-prompt-chuan-bi-cau-tra-loi-phong-van-it.mp4','local',NULL,600,0,'published',5,'2026-06-27 08:00:00','2026-06-27 08:00:00'),
(36,17,9,'Vì sao không nên copy nguyên câu trả lời của AI?','video','Nội dung video: Vì sao không nên copy nguyên câu trả lời của AI?. File seed theo course_folder ai-learning.','/videos/ai-learning/ai-learning-21-vi-sao-khong-nen-copy-nguyen-cau-tra-loi-cua-ai.mp4','local',NULL,600,0,'published',1,'2026-06-27 08:00:00','2026-06-27 08:00:00'),
(37,17,9,'Cách kiểm chứng thông tin AI tạo ra','video','Nội dung video: Cách kiểm chứng thông tin AI tạo ra. File seed theo course_folder ai-learning.','/videos/ai-learning/ai-learning-22-cach-kiem-chung-thong-tin-ai-tao-ra.mp4','local',NULL,600,0,'published',2,'2026-06-27 08:00:00','2026-06-27 08:00:00'),
(38,17,9,'Bảo mật dữ liệu khi dùng AI trong đồ án','video','Nội dung video: Bảo mật dữ liệu khi dùng AI trong đồ án. File seed theo course_folder ai-learning.','/videos/ai-learning/ai-learning-23-bao-mat-du-lieu-khi-dung-ai-trong-do-an.mp4','local',NULL,600,0,'published',3,'2026-06-27 08:00:00','2026-06-27 08:00:00'),
(39,17,9,'Tránh phụ thuộc AI khi học lập trình','video','Nội dung video: Tránh phụ thuộc AI khi học lập trình. File seed theo course_folder ai-learning.','/videos/ai-learning/ai-learning-24-tranh-phu-thuoc-ai-khi-hoc-lap-trinh.mp4','local',NULL,600,0,'published',4,'2026-06-27 08:00:00','2026-06-27 08:00:00'),
(40,17,9,'Cách dùng AI trung thực trong CV và phỏng vấn','video','Nội dung video: Cách dùng AI trung thực trong CV và phỏng vấn. File seed theo course_folder ai-learning.','/videos/ai-learning/ai-learning-25-cach-dung-ai-trung-thuc-trong-cv-va-phong-van.mp4','local',NULL,600,0,'published',5,'2026-06-27 08:00:00','2026-06-27 08:00:00'),
(41,18,9,'Quy trình học một chủ đề mới bằng AI','video','Nội dung video: Quy trình học một chủ đề mới bằng AI. File seed theo course_folder ai-learning.','/videos/ai-learning/ai-learning-26-quy-trinh-hoc-mot-chu-de-moi-bang-ai.mp4','local',NULL,600,0,'published',1,'2026-06-27 08:00:00','2026-06-27 08:00:00'),
(42,18,9,'Tạo ghi chú học tập và checklist bằng AI','video','Nội dung video: Tạo ghi chú học tập và checklist bằng AI. File seed theo course_folder ai-learning.','/videos/ai-learning/ai-learning-27-tao-ghi-chu-hoc-tap-va-checklist-bang-ai.mp4','local',NULL,600,0,'published',2,'2026-06-27 08:00:00','2026-06-27 08:00:00'),
(43,18,9,'Dùng AI luyện phỏng vấn hằng ngày','video','Nội dung video: Dùng AI luyện phỏng vấn hằng ngày. File seed theo course_folder ai-learning.','/videos/ai-learning/ai-learning-28-dung-ai-luyen-phong-van-hang-ngay.mp4','local',NULL,600,0,'published',3,'2026-06-27 08:00:00','2026-06-27 08:00:00'),
(44,18,9,'Xây trợ lý học tập cá nhân cho sinh viên IT','video','Nội dung video: Xây trợ lý học tập cá nhân cho sinh viên IT. File seed theo course_folder ai-learning.','/videos/ai-learning/ai-learning-29-xay-tro-ly-hoc-tap-ca-nhan-cho-sinh-vien-it.mp4','local',NULL,600,0,'published',4,'2026-06-27 08:00:00','2026-06-27 08:00:00'),
(45,18,9,'Tổng kết khóa học AI Learning','video','Nội dung video: Tổng kết khóa học AI Learning. File seed theo course_folder ai-learning.','/videos/ai-learning/ai-learning-30-tong-ket-khoa-hoc-ai-learning.mp4','local',NULL,600,0,'published',5,'2026-06-27 08:00:00','2026-06-27 08:00:00'),
(46,19,10,'Postman là gì và vì sao Backend Fresher nên biết?','video','Nội dung video: Postman là gì và vì sao Backend Fresher nên biết?. File seed theo course_folder postman-api-testing.','/videos/postman-api-testing/postman-api-testing-01-postman-la-gi-va-vi-sao-backend-fresher-nen-biet.mp4','local',NULL,600,1,'published',1,'2026-06-27 08:00:00','2026-06-27 08:00:00'),
(47,19,10,'Cài đặt Postman và tạo workspace đầu tiên','video','Nội dung video: Cài đặt Postman và tạo workspace đầu tiên. File seed theo course_folder postman-api-testing.','/videos/postman-api-testing/postman-api-testing-02-cai-dat-postman-va-tao-workspace-dau-tien.mp4','local',NULL,600,0,'published',2,'2026-06-27 08:00:00','2026-06-27 08:00:00'),
(48,19,10,'Hiểu method, URL, header, body và response trong Postman','video','Nội dung video: Hiểu method, URL, header, body và response trong Postman. File seed theo course_folder postman-api-testing.','/videos/postman-api-testing/postman-api-testing-03-hieu-method-url-header-body-va-response-trong-postman.mp4','local',NULL,600,0,'published',3,'2026-06-27 08:00:00','2026-06-27 08:00:00'),
(49,19,10,'Tạo request GET POST PUT PATCH DELETE đầu tiên','video','Nội dung video: Tạo request GET POST PUT PATCH DELETE đầu tiên. File seed theo course_folder postman-api-testing.','/videos/postman-api-testing/postman-api-testing-04-tao-request-get-post-put-patch-delete-dau-tien.mp4','local',NULL,600,0,'published',4,'2026-06-27 08:00:00','2026-06-27 08:00:00'),
(50,19,10,'Đọc status code và JSON response khi test API','video','Nội dung video: Đọc status code và JSON response khi test API. File seed theo course_folder postman-api-testing.','/videos/postman-api-testing/postman-api-testing-05-doc-status-code-va-json-response-khi-test-api.mp4','local',NULL,600,0,'published',5,'2026-06-27 08:00:00','2026-06-27 08:00:00'),
(51,20,10,'Test API register bằng Postman','video','Nội dung video: Test API register bằng Postman. File seed theo course_folder postman-api-testing.','/videos/postman-api-testing/postman-api-testing-06-test-api-register-bang-postman.mp4','local',NULL,600,0,'published',1,'2026-06-27 08:00:00','2026-06-27 08:00:00'),
(52,20,10,'Test API login và lưu access token','video','Nội dung video: Test API login và lưu access token. File seed theo course_folder postman-api-testing.','/videos/postman-api-testing/postman-api-testing-07-test-api-login-va-luu-access-token.mp4','local',NULL,600,0,'published',2,'2026-06-27 08:00:00','2026-06-27 08:00:00'),
(53,20,10,'Gửi Authorization header cho API protected','video','Nội dung video: Gửi Authorization header cho API protected. File seed theo course_folder postman-api-testing.','/videos/postman-api-testing/postman-api-testing-08-gui-authorization-header-cho-api-protected.mp4','local',NULL,600,0,'published',3,'2026-06-27 08:00:00','2026-06-27 08:00:00'),
(54,20,10,'Test logout và refresh token','video','Nội dung video: Test logout và refresh token. File seed theo course_folder postman-api-testing.','/videos/postman-api-testing/postman-api-testing-09-test-logout-va-refresh-token.mp4','local',NULL,600,0,'published',4,'2026-06-27 08:00:00','2026-06-27 08:00:00'),
(55,20,10,'Xử lý lỗi 401 403 khi test API','video','Nội dung video: Xử lý lỗi 401 403 khi test API. File seed theo course_folder postman-api-testing.','/videos/postman-api-testing/postman-api-testing-10-xu-ly-loi-401-403-khi-test-api.mp4','local',NULL,600,0,'published',5,'2026-06-27 08:00:00','2026-06-27 08:00:00'),
(56,21,10,'Tạo Postman Collection cho dự án Laravel','video','Nội dung video: Tạo Postman Collection cho dự án Laravel. File seed theo course_folder postman-api-testing.','/videos/postman-api-testing/postman-api-testing-11-tao-postman-collection-cho-du-an-laravel.mp4','local',NULL,600,0,'published',1,'2026-06-27 08:00:00','2026-06-27 08:00:00'),
(57,21,10,'Dùng environment cho base_url và token','video','Nội dung video: Dùng environment cho base_url và token. File seed theo course_folder postman-api-testing.','/videos/postman-api-testing/postman-api-testing-12-dung-environment-cho-base-url-va-token.mp4','local',NULL,600,0,'published',2,'2026-06-27 08:00:00','2026-06-27 08:00:00'),
(58,21,10,'Dùng variable để lưu user_id course_id order_id','video','Nội dung video: Dùng variable để lưu user_id course_id order_id. File seed theo course_folder postman-api-testing.','/videos/postman-api-testing/postman-api-testing-13-dung-variable-de-luu-user-id-course-id-order-id.mp4','local',NULL,600,0,'published',3,'2026-06-27 08:00:00','2026-06-27 08:00:00'),
(59,21,10,'Viết pre-request script cơ bản','video','Nội dung video: Viết pre-request script cơ bản. File seed theo course_folder postman-api-testing.','/videos/postman-api-testing/postman-api-testing-14-viet-pre-request-script-co-ban.mp4','local',NULL,600,0,'published',4,'2026-06-27 08:00:00','2026-06-27 08:00:00'),
(60,21,10,'Viết test script kiểm tra status và response','video','Nội dung video: Viết test script kiểm tra status và response. File seed theo course_folder postman-api-testing.','/videos/postman-api-testing/postman-api-testing-15-viet-test-script-kiem-tra-status-va-response.mp4','local',NULL,600,0,'published',5,'2026-06-27 08:00:00','2026-06-27 08:00:00'),
(61,22,10,'Test flow xem danh sách khóa học','video','Nội dung video: Test flow xem danh sách khóa học. File seed theo course_folder postman-api-testing.','/videos/postman-api-testing/postman-api-testing-16-test-flow-xem-danh-sach-khoa-hoc.mp4','local',NULL,600,0,'published',1,'2026-06-27 08:00:00','2026-06-27 08:00:00'),
(62,22,10,'Test flow chi tiết khóa học và lesson overview','video','Nội dung video: Test flow chi tiết khóa học và lesson overview. File seed theo course_folder postman-api-testing.','/videos/postman-api-testing/postman-api-testing-17-test-flow-chi-tiet-khoa-hoc-va-lesson-overview.mp4','local',NULL,600,0,'published',2,'2026-06-27 08:00:00','2026-06-27 08:00:00'),
(63,22,10,'Test flow tạo order và áp coupon','video','Nội dung video: Test flow tạo order và áp coupon. File seed theo course_folder postman-api-testing.','/videos/postman-api-testing/postman-api-testing-18-test-flow-tao-order-va-ap-coupon.mp4','local',NULL,600,0,'published',3,'2026-06-27 08:00:00','2026-06-27 08:00:00'),
(64,22,10,'Test flow thanh toán và tạo enrollment','video','Nội dung video: Test flow thanh toán và tạo enrollment. File seed theo course_folder postman-api-testing.','/videos/postman-api-testing/postman-api-testing-19-test-flow-thanh-toan-va-tao-enrollment.mp4','local',NULL,600,0,'published',4,'2026-06-27 08:00:00','2026-06-27 08:00:00'),
(65,22,10,'Test flow học bài và lưu tiến độ','video','Nội dung video: Test flow học bài và lưu tiến độ. File seed theo course_folder postman-api-testing.','/videos/postman-api-testing/postman-api-testing-20-test-flow-hoc-bai-va-luu-tien-do.mp4','local',NULL,600,0,'published',5,'2026-06-27 08:00:00','2026-06-27 08:00:00'),
(66,23,10,'Test validation lỗi 422 trong API','video','Nội dung video: Test validation lỗi 422 trong API. File seed theo course_folder postman-api-testing.','/videos/postman-api-testing/postman-api-testing-21-test-validation-loi-422-trong-api.mp4','local',NULL,600,0,'published',1,'2026-06-27 08:00:00','2026-06-27 08:00:00'),
(67,23,10,'Test not found 404 và conflict 409','video','Nội dung video: Test not found 404 và conflict 409. File seed theo course_folder postman-api-testing.','/videos/postman-api-testing/postman-api-testing-22-test-not-found-404-va-conflict-409.mp4','local',NULL,600,0,'published',2,'2026-06-27 08:00:00','2026-06-27 08:00:00'),
(68,23,10,'Test permission và ownership trong API','video','Nội dung video: Test permission và ownership trong API. File seed theo course_folder postman-api-testing.','/videos/postman-api-testing/postman-api-testing-23-test-permission-va-ownership-trong-api.mp4','local',NULL,600,0,'published',3,'2026-06-27 08:00:00','2026-06-27 08:00:00'),
(69,23,10,'Debug lỗi 500 bằng response và Laravel log','video','Nội dung video: Debug lỗi 500 bằng response và Laravel log. File seed theo course_folder postman-api-testing.','/videos/postman-api-testing/postman-api-testing-24-debug-loi-500-bang-response-va-laravel-log.mp4','local',NULL,600,0,'published',4,'2026-06-27 08:00:00','2026-06-27 08:00:00'),
(70,23,10,'Ghi báo cáo test API rõ ràng','video','Nội dung video: Ghi báo cáo test API rõ ràng. File seed theo course_folder postman-api-testing.','/videos/postman-api-testing/postman-api-testing-25-ghi-bao-cao-test-api-ro-rang.mp4','local',NULL,600,0,'published',5,'2026-06-27 08:00:00','2026-06-27 08:00:00'),
(71,24,10,'Sắp xếp collection theo module Auth Catalog Payment Learning','video','Nội dung video: Sắp xếp collection theo module Auth Catalog Payment Learning. File seed theo course_folder postman-api-testing.','/videos/postman-api-testing/postman-api-testing-26-sap-xep-collection-theo-module-auth-catalog-payment-learning.mp4','local',NULL,600,0,'published',1,'2026-06-27 08:00:00','2026-06-27 08:00:00'),
(72,24,10,'Chuẩn bị dữ liệu seed để test ổn định','video','Nội dung video: Chuẩn bị dữ liệu seed để test ổn định. File seed theo course_folder postman-api-testing.','/videos/postman-api-testing/postman-api-testing-27-chuan-bi-du-lieu-seed-de-test-on-dinh.mp4','local',NULL,600,0,'published',2,'2026-06-27 08:00:00','2026-06-27 08:00:00'),
(73,24,10,'Export collection và environment cho team','video','Nội dung video: Export collection và environment cho team. File seed theo course_folder postman-api-testing.','/videos/postman-api-testing/postman-api-testing-28-export-collection-va-environment-cho-team.mp4','local',NULL,600,0,'published',3,'2026-06-27 08:00:00','2026-06-27 08:00:00'),
(74,24,10,'Checklist test API trước ngày demo','video','Nội dung video: Checklist test API trước ngày demo. File seed theo course_folder postman-api-testing.','/videos/postman-api-testing/postman-api-testing-29-checklist-test-api-truoc-ngay-demo.mp4','local',NULL,600,0,'published',4,'2026-06-27 08:00:00','2026-06-27 08:00:00'),
(75,24,10,'Tổng kết khóa học Postman API Testing','video','Nội dung video: Tổng kết khóa học Postman API Testing. File seed theo course_folder postman-api-testing.','/videos/postman-api-testing/postman-api-testing-30-tong-ket-khoa-hoc-postman-api-testing.mp4','local',NULL,600,0,'published',5,'2026-06-27 08:00:00','2026-06-27 08:00:00'),
(76,25,11,'Vì sao làm đồ án nhóm phải dùng Git?','video','Nội dung video: Vì sao làm đồ án nhóm phải dùng Git?. File seed theo course_folder git-github.','/videos/git-github/git-github-01-vi-sao-lam-do-an-nhom-phai-dung-git.mp4','local',NULL,600,1,'published',1,'2026-06-27 08:00:00','2026-06-27 08:00:00'),
(77,25,11,'Git, GitHub và repository khác nhau thế nào?','video','Nội dung video: Git, GitHub và repository khác nhau thế nào?. File seed theo course_folder git-github.','/videos/git-github/git-github-02-git-github-va-repository-khac-nhau-the-nao.mp4','local',NULL,600,0,'published',2,'2026-06-27 08:00:00','2026-06-27 08:00:00'),
(78,25,11,'Cài Git và cấu hình tài khoản GitHub','video','Nội dung video: Cài Git và cấu hình tài khoản GitHub. File seed theo course_folder git-github.','/videos/git-github/git-github-03-cai-git-va-cau-hinh-tai-khoan-github.mp4','local',NULL,600,0,'published',3,'2026-06-27 08:00:00','2026-06-27 08:00:00'),
(79,25,11,'Tạo repository đầu tiên cho đồ án nhóm','video','Nội dung video: Tạo repository đầu tiên cho đồ án nhóm. File seed theo course_folder git-github.','/videos/git-github/git-github-04-tao-repository-dau-tien-cho-do-an-nhom.mp4','local',NULL,600,0,'published',4,'2026-06-27 08:00:00','2026-06-27 08:00:00'),
(80,25,11,'Clone project và hiểu cấu trúc repository','video','Nội dung video: Clone project và hiểu cấu trúc repository. File seed theo course_folder git-github.','/videos/git-github/git-github-05-clone-project-va-hieu-cau-truc-repository.mp4','local',NULL,600,0,'published',5,'2026-06-27 08:00:00','2026-06-27 08:00:00'),
(81,26,11,'Git status, add và commit hoạt động ra sao?','video','Nội dung video: Git status, add và commit hoạt động ra sao?. File seed theo course_folder git-github.','/videos/git-github/git-github-06-git-status-add-va-commit-hoat-dong-ra-sao.mp4','local',NULL,600,0,'published',1,'2026-06-27 08:00:00','2026-06-27 08:00:00'),
(82,26,11,'Viết commit message dễ hiểu','video','Nội dung video: Viết commit message dễ hiểu. File seed theo course_folder git-github.','/videos/git-github/git-github-07-viet-commit-message-de-hieu.mp4','local',NULL,600,0,'published',2,'2026-06-27 08:00:00','2026-06-27 08:00:00'),
(83,26,11,'Push code lên GitHub đúng cách','video','Nội dung video: Push code lên GitHub đúng cách. File seed theo course_folder git-github.','/videos/git-github/git-github-08-push-code-len-github-dung-cach.mp4','local',NULL,600,0,'published',3,'2026-06-27 08:00:00','2026-06-27 08:00:00'),
(84,26,11,'Pull code mới về máy trước khi làm việc','video','Nội dung video: Pull code mới về máy trước khi làm việc. File seed theo course_folder git-github.','/videos/git-github/git-github-09-pull-code-moi-ve-may-truoc-khi-lam-viec.mp4','local',NULL,600,0,'published',4,'2026-06-27 08:00:00','2026-06-27 08:00:00'),
(85,26,11,'Branch là gì và cách chia nhánh','video','Nội dung video: Branch là gì và cách chia nhánh. File seed theo course_folder git-github.','/videos/git-github/git-github-10-branch-la-gi-va-cach-chia-nhanh.mp4','local',NULL,600,0,'published',5,'2026-06-27 08:00:00','2026-06-27 08:00:00'),
(86,27,11,'Pull request và review code cơ bản','video','Nội dung video: Pull request và review code cơ bản. File seed theo course_folder git-github.','/videos/git-github/git-github-11-pull-request-va-review-code-co-ban.mp4','local',NULL,600,0,'published',1,'2026-06-27 08:00:00','2026-06-27 08:00:00'),
(87,27,11,'Workflow Git cho team Backend và Frontend','video','Nội dung video: Workflow Git cho team Backend và Frontend. File seed theo course_folder git-github.','/videos/git-github/git-github-12-workflow-git-cho-team-backend-va-frontend.mp4','local',NULL,600,0,'published',2,'2026-06-27 08:00:00','2026-06-27 08:00:00'),
(88,27,11,'Dùng GitHub Issues để chia task','video','Nội dung video: Dùng GitHub Issues để chia task. File seed theo course_folder git-github.','/videos/git-github/git-github-13-dung-github-issues-de-chia-task.mp4','local',NULL,600,0,'published',3,'2026-06-27 08:00:00','2026-06-27 08:00:00'),
(89,27,11,'Gắn branch, commit và pull request với task đồ án','video','Nội dung video: Gắn branch, commit và pull request với task đồ án. File seed theo course_folder git-github.','/videos/git-github/git-github-14-gan-branch-commit-va-pull-request-voi-task-do-an.mp4','local',NULL,600,0,'published',4,'2026-06-27 08:00:00','2026-06-27 08:00:00'),
(90,27,11,'Quy tắc làm việc nhóm để tránh đè code','video','Nội dung video: Quy tắc làm việc nhóm để tránh đè code. File seed theo course_folder git-github.','/videos/git-github/git-github-15-quy-tac-lam-viec-nhom-de-tranh-de-code.mp4','local',NULL,600,0,'published',5,'2026-06-27 08:00:00','2026-06-27 08:00:00'),
(91,28,11,'Xử lý conflict khi nhiều người sửa cùng file','video','Nội dung video: Xử lý conflict khi nhiều người sửa cùng file. File seed theo course_folder git-github.','/videos/git-github/git-github-16-xu-ly-conflict-khi-nhieu-nguoi-sua-cung-file.mp4','local',NULL,600,0,'published',1,'2026-06-27 08:00:00','2026-06-27 08:00:00'),
(92,28,11,'Push bị từ chối thì xử lý thế nào?','video','Nội dung video: Push bị từ chối thì xử lý thế nào?. File seed theo course_folder git-github.','/videos/git-github/git-github-17-push-bi-tu-choi-thi-xu-ly-the-nao.mp4','local',NULL,600,0,'published',2,'2026-06-27 08:00:00','2026-06-27 08:00:00'),
(93,28,11,'Commit nhầm file và cách phòng tránh','video','Nội dung video: Commit nhầm file và cách phòng tránh. File seed theo course_folder git-github.','/videos/git-github/git-github-18-commit-nham-file-va-cach-phong-tranh.mp4','local',NULL,600,0,'published',3,'2026-06-27 08:00:00','2026-06-27 08:00:00'),
(94,28,11,'Lỡ code trực tiếp trên main thì xử lý ra sao?','video','Nội dung video: Lỡ code trực tiếp trên main thì xử lý ra sao?. File seed theo course_folder git-github.','/videos/git-github/git-github-19-lo-code-truc-tiep-tren-main-thi-xu-ly-ra-sao.mp4','local',NULL,600,0,'published',4,'2026-06-27 08:00:00','2026-06-27 08:00:00'),
(95,28,11,'Những lỗi Git thường gặp và cách xử lý','video','Nội dung video: Những lỗi Git thường gặp và cách xử lý. File seed theo course_folder git-github.','/videos/git-github/git-github-20-nhung-loi-git-thuong-gap-va-cach-xu-ly.mp4','local',NULL,600,0,'published',5,'2026-06-27 08:00:00','2026-06-27 08:00:00'),
(96,29,11,'Viết README setup project cho team','video','Nội dung video: Viết README setup project cho team. File seed theo course_folder git-github.','/videos/git-github/git-github-21-viet-readme-setup-project-cho-team.mp4','local',NULL,600,0,'published',1,'2026-06-27 08:00:00','2026-06-27 08:00:00'),
(97,29,11,'Dùng .gitignore và .env.example trong đồ án','video','Nội dung video: Dùng .gitignore và .env.example trong đồ án. File seed theo course_folder git-github.','/videos/git-github/git-github-22-dung-gitignore-va-env-example-trong-do-an.mp4','local',NULL,600,0,'published',2,'2026-06-27 08:00:00','2026-06-27 08:00:00'),
(98,29,11,'Không commit file nhạy cảm lên GitHub','video','Nội dung video: Không commit file nhạy cảm lên GitHub. File seed theo course_folder git-github.','/videos/git-github/git-github-23-khong-commit-file-nhay-cam-len-github.mp4','local',NULL,600,0,'published',3,'2026-06-27 08:00:00','2026-06-27 08:00:00'),
(99,29,11,'Cách trình bày repository đồ án cho giảng viên và nhà tuyển dụng','video','Nội dung video: Cách trình bày repository đồ án cho giảng viên và nhà tuyển dụng. File seed theo course_folder git-github.','/videos/git-github/git-github-24-cach-trinh-bay-repository-do-an-cho-giang-vien-va-nha-tuyen-dung.mp4','local',NULL,600,0,'published',4,'2026-06-27 08:00:00','2026-06-27 08:00:00'),
(100,29,11,'GitHub profile cho sinh viên IT','video','Nội dung video: GitHub profile cho sinh viên IT. File seed theo course_folder git-github.','/videos/git-github/git-github-25-github-profile-cho-sinh-vien-it.mp4','local',NULL,600,0,'published',5,'2026-06-27 08:00:00','2026-06-27 08:00:00'),
(101,30,11,'Quy trình Git cho dự án Laravel Backend','video','Nội dung video: Quy trình Git cho dự án Laravel Backend. File seed theo course_folder git-github.','/videos/git-github/git-github-26-quy-trinh-git-cho-du-an-laravel-backend.mp4','local',NULL,600,0,'published',1,'2026-06-27 08:00:00','2026-06-27 08:00:00'),
(102,30,11,'Quy trình Git cho dự án React Frontend','video','Nội dung video: Quy trình Git cho dự án React Frontend. File seed theo course_folder git-github.','/videos/git-github/git-github-27-quy-trinh-git-cho-du-an-react-frontend.mp4','local',NULL,600,0,'published',2,'2026-06-27 08:00:00','2026-06-27 08:00:00'),
(103,30,11,'Quy trình merge code trước ngày demo','video','Nội dung video: Quy trình merge code trước ngày demo. File seed theo course_folder git-github.','/videos/git-github/git-github-28-quy-trinh-merge-code-truoc-ngay-demo.mp4','local',NULL,600,0,'published',3,'2026-06-27 08:00:00','2026-06-27 08:00:00'),
(104,30,11,'Checklist GitHub trước khi nộp đồ án','video','Nội dung video: Checklist GitHub trước khi nộp đồ án. File seed theo course_folder git-github.','/videos/git-github/git-github-29-checklist-github-truoc-khi-nop-do-an.mp4','local',NULL,600,0,'published',4,'2026-06-27 08:00:00','2026-06-27 08:00:00'),
(105,30,11,'Tổng kết khóa học Git & GitHub làm việc nhóm đồ án','video','Nội dung video: Tổng kết khóa học Git & GitHub làm việc nhóm đồ án. File seed theo course_folder git-github.','/videos/git-github/git-github-30-tong-ket-khoa-hoc-git-va-github-lam-viec-nhom-do-an.mp4','local',NULL,600,0,'published',5,'2026-06-27 08:00:00','2026-06-27 08:00:00'),
(106,31,12,'Database trong hệ thống E-learning dùng để làm gì?','video','Nội dung video: Database trong hệ thống E-learning dùng để làm gì?. File seed theo course_folder mysql-database-design.','/videos/mysql-database-design/mysql-database-design-01-database-trong-he-thong-e-learning-dung-de-lam-gi.mp4','local',NULL,600,1,'published',1,'2026-06-27 08:00:00','2026-06-27 08:00:00'),
(107,31,12,'Table column record và data type trong MySQL','video','Nội dung video: Table column record và data type trong MySQL. File seed theo course_folder mysql-database-design.','/videos/mysql-database-design/mysql-database-design-02-table-column-record-va-data-type-trong-mysql.mp4','local',NULL,600,0,'published',2,'2026-06-27 08:00:00','2026-06-27 08:00:00'),
(108,31,12,'Primary key foreign key và unique constraint','video','Nội dung video: Primary key foreign key và unique constraint. File seed theo course_folder mysql-database-design.','/videos/mysql-database-design/mysql-database-design-03-primary-key-foreign-key-va-unique-constraint.mp4','local',NULL,600,0,'published',3,'2026-06-27 08:00:00','2026-06-27 08:00:00'),
(109,31,12,'One-to-many many-to-many và one-to-one','video','Nội dung video: One-to-many many-to-many và one-to-one. File seed theo course_folder mysql-database-design.','/videos/mysql-database-design/mysql-database-design-04-one-to-many-many-to-many-va-one-to-one.mp4','local',NULL,600,0,'published',4,'2026-06-27 08:00:00','2026-06-27 08:00:00'),
(110,31,12,'Cách đọc ERD cho sinh viên IT','video','Nội dung video: Cách đọc ERD cho sinh viên IT. File seed theo course_folder mysql-database-design.','/videos/mysql-database-design/mysql-database-design-05-cach-doc-erd-cho-sinh-vien-it.mp4','local',NULL,600,0,'published',5,'2026-06-27 08:00:00','2026-06-27 08:00:00'),
(111,32,12,'Thiết kế bảng users và roles','video','Nội dung video: Thiết kế bảng users và roles. File seed theo course_folder mysql-database-design.','/videos/mysql-database-design/mysql-database-design-06-thiet-ke-bang-users-va-roles.mp4','local',NULL,600,0,'published',1,'2026-06-27 08:00:00','2026-06-27 08:00:00'),
(112,32,12,'Thiết kế categories và course_categories','video','Nội dung video: Thiết kế categories và course_categories. File seed theo course_folder mysql-database-design.','/videos/mysql-database-design/mysql-database-design-07-thiet-ke-categories-va-course-categories.mp4','local',NULL,600,0,'published',2,'2026-06-27 08:00:00','2026-06-27 08:00:00'),
(113,32,12,'Thiết kế courses cho nền tảng E-learning','video','Nội dung video: Thiết kế courses cho nền tảng E-learning. File seed theo course_folder mysql-database-design.','/videos/mysql-database-design/mysql-database-design-08-thiet-ke-courses-cho-nen-tang-e-learning.mp4','local',NULL,600,0,'published',3,'2026-06-27 08:00:00','2026-06-27 08:00:00'),
(114,32,12,'Thiết kế course_sections và lessons','video','Nội dung video: Thiết kế course_sections và lessons. File seed theo course_folder mysql-database-design.','/videos/mysql-database-design/mysql-database-design-09-thiet-ke-course-sections-va-lessons.mp4','local',NULL,600,0,'published',4,'2026-06-27 08:00:00','2026-06-27 08:00:00'),
(115,32,12,'Lưu video_url và asset_url sao cho đúng','video','Nội dung video: Lưu video_url và asset_url sao cho đúng. File seed theo course_folder mysql-database-design.','/videos/mysql-database-design/mysql-database-design-10-luu-video-url-va-asset-url-sao-cho-dung.mp4','local',NULL,600,0,'published',5,'2026-06-27 08:00:00','2026-06-27 08:00:00'),
(116,33,12,'Thiết kế enrollments sau khi mua khóa học','video','Nội dung video: Thiết kế enrollments sau khi mua khóa học. File seed theo course_folder mysql-database-design.','/videos/mysql-database-design/mysql-database-design-11-thiet-ke-enrollments-sau-khi-mua-khoa-hoc.mp4','local',NULL,600,0,'published',1,'2026-06-27 08:00:00','2026-06-27 08:00:00'),
(117,33,12,'Thiết kế lesson_progress và video_progress','video','Nội dung video: Thiết kế lesson_progress và video_progress. File seed theo course_folder mysql-database-design.','/videos/mysql-database-design/mysql-database-design-12-thiet-ke-lesson-progress-va-video-progress.mp4','local',NULL,600,0,'published',2,'2026-06-27 08:00:00','2026-06-27 08:00:00'),
(118,33,12,'Thiết kế lesson_notes cho ghi chú cá nhân','video','Nội dung video: Thiết kế lesson_notes cho ghi chú cá nhân. File seed theo course_folder mysql-database-design.','/videos/mysql-database-design/mysql-database-design-13-thiet-ke-lesson-notes-cho-ghi-chu-ca-nhan.mp4','local',NULL,600,0,'published',3,'2026-06-27 08:00:00','2026-06-27 08:00:00'),
(119,33,12,'Thiết kế quiz questions options và attempts','video','Nội dung video: Thiết kế quiz questions options và attempts. File seed theo course_folder mysql-database-design.','/videos/mysql-database-design/mysql-database-design-14-thiet-ke-quiz-questions-options-va-attempts.mp4','local',NULL,600,0,'published',4,'2026-06-27 08:00:00','2026-06-27 08:00:00'),
(120,33,12,'Tính progress_percent như thế nào cho hợp lý','video','Nội dung video: Tính progress_percent như thế nào cho hợp lý. File seed theo course_folder mysql-database-design.','/videos/mysql-database-design/mysql-database-design-15-tinh-progress-percent-nhu-the-nao-cho-hop-ly.mp4','local',NULL,600,0,'published',5,'2026-06-27 08:00:00','2026-06-27 08:00:00'),
(121,34,12,'Thiết kế orders cho đơn hàng khóa học','video','Nội dung video: Thiết kế orders cho đơn hàng khóa học. File seed theo course_folder mysql-database-design.','/videos/mysql-database-design/mysql-database-design-16-thiet-ke-orders-cho-don-hang-khoa-hoc.mp4','local',NULL,600,0,'published',1,'2026-06-27 08:00:00','2026-06-27 08:00:00'),
(122,34,12,'Thiết kế coupons và kiểm tra điều kiện sử dụng','video','Nội dung video: Thiết kế coupons và kiểm tra điều kiện sử dụng. File seed theo course_folder mysql-database-design.','/videos/mysql-database-design/mysql-database-design-17-thiet-ke-coupons-va-kiem-tra-dieu-kien-su-dung.mp4','local',NULL,600,0,'published',2,'2026-06-27 08:00:00','2026-06-27 08:00:00'),
(123,34,12,'Thiết kế payment_status và order status','video','Nội dung video: Thiết kế payment_status và order status. File seed theo course_folder mysql-database-design.','/videos/mysql-database-design/mysql-database-design-18-thiet-ke-payment-status-va-order-status.mp4','local',NULL,600,0,'published',3,'2026-06-27 08:00:00','2026-06-27 08:00:00'),
(124,34,12,'Thiết kế revenues cho instructor và platform','video','Nội dung video: Thiết kế revenues cho instructor và platform. File seed theo course_folder mysql-database-design.','/videos/mysql-database-design/mysql-database-design-19-thiet-ke-revenues-cho-instructor-va-platform.mp4','local',NULL,600,0,'published',4,'2026-06-27 08:00:00','2026-06-27 08:00:00'),
(125,34,12,'Vì sao nghiệp vụ thanh toán cần transaction','video','Nội dung video: Vì sao nghiệp vụ thanh toán cần transaction. File seed theo course_folder mysql-database-design.','/videos/mysql-database-design/mysql-database-design-20-vi-sao-nghiep-vu-thanh-toan-can-transaction.mp4','local',NULL,600,0,'published',5,'2026-06-27 08:00:00','2026-06-27 08:00:00'),
(126,35,12,'JOIN dữ liệu course instructor và category','video','Nội dung video: JOIN dữ liệu course instructor và category. File seed theo course_folder mysql-database-design.','/videos/mysql-database-design/mysql-database-design-21-join-du-lieu-course-instructor-va-category.mp4','local',NULL,600,0,'published',1,'2026-06-27 08:00:00','2026-06-27 08:00:00'),
(127,35,12,'Index là gì và khi nào nên dùng?','video','Nội dung video: Index là gì và khi nào nên dùng?. File seed theo course_folder mysql-database-design.','/videos/mysql-database-design/mysql-database-design-22-index-la-gi-va-khi-nao-nen-dung.mp4','local',NULL,600,0,'published',2,'2026-06-27 08:00:00','2026-06-27 08:00:00'),
(128,35,12,'Tránh N+1 query từ góc nhìn database','video','Nội dung video: Tránh N+1 query từ góc nhìn database. File seed theo course_folder mysql-database-design.','/videos/mysql-database-design/mysql-database-design-23-tranh-n-plus-1-query-tu-goc-nhin-database.mp4','local',NULL,600,0,'published',3,'2026-06-27 08:00:00','2026-06-27 08:00:00'),
(129,35,12,'Soft delete và trạng thái dữ liệu','video','Nội dung video: Soft delete và trạng thái dữ liệu. File seed theo course_folder mysql-database-design.','/videos/mysql-database-design/mysql-database-design-24-soft-delete-va-trang-thai-du-lieu.mp4','local',NULL,600,0,'published',4,'2026-06-27 08:00:00','2026-06-27 08:00:00'),
(130,35,12,'Dữ liệu demo seed cho trình chiếu sản phẩm','video','Nội dung video: Dữ liệu demo seed cho trình chiếu sản phẩm. File seed theo course_folder mysql-database-design.','/videos/mysql-database-design/mysql-database-design-25-du-lieu-demo-seed-cho-trinh-chieu-san-pham.mp4','local',NULL,600,0,'published',5,'2026-06-27 08:00:00','2026-06-27 08:00:00'),
(131,36,12,'Kiểm tra naming convention của bảng và cột','video','Nội dung video: Kiểm tra naming convention của bảng và cột. File seed theo course_folder mysql-database-design.','/videos/mysql-database-design/mysql-database-design-26-kiem-tra-naming-convention-cua-bang-va-cot.mp4','local',NULL,600,0,'published',1,'2026-06-27 08:00:00','2026-06-27 08:00:00'),
(132,36,12,'Kiểm tra constraint và relationship trước khi code','video','Nội dung video: Kiểm tra constraint và relationship trước khi code. File seed theo course_folder mysql-database-design.','/videos/mysql-database-design/mysql-database-design-27-kiem-tra-constraint-va-relationship-truoc-khi-code.mp4','local',NULL,600,0,'published',2,'2026-06-27 08:00:00','2026-06-27 08:00:00'),
(133,36,12,'Kiểm tra dữ liệu mẫu có đủ flow demo','video','Nội dung video: Kiểm tra dữ liệu mẫu có đủ flow demo. File seed theo course_folder mysql-database-design.','/videos/mysql-database-design/mysql-database-design-28-kiem-tra-du-lieu-mau-co-du-flow-demo.mp4','local',NULL,600,0,'published',3,'2026-06-27 08:00:00','2026-06-27 08:00:00'),
(134,36,12,'Checklist lỗi database thường gặp trong đồ án','video','Nội dung video: Checklist lỗi database thường gặp trong đồ án. File seed theo course_folder mysql-database-design.','/videos/mysql-database-design/mysql-database-design-29-checklist-loi-database-thuong-gap-trong-do-an.mp4','local',NULL,600,0,'published',4,'2026-06-27 08:00:00','2026-06-27 08:00:00'),
(135,36,12,'Tổng kết khóa học MySQL Database Design','video','Nội dung video: Tổng kết khóa học MySQL Database Design. File seed theo course_folder mysql-database-design.','/videos/mysql-database-design/mysql-database-design-30-tong-ket-khoa-hoc-mysql-database-design.mp4','local',NULL,600,0,'published',5,'2026-06-27 08:00:00','2026-06-27 08:00:00'),
(136,37,13,'Deploy fullstack là gì?','video','Nội dung video: Deploy fullstack là gì?. File seed theo course_folder deploy-vps-aapanel.','/videos/deploy-vps-aapanel/deploy-vps-aapanel-01-deploy-fullstack-la-gi.mp4','local',NULL,600,1,'published',1,'2026-06-27 08:00:00','2026-06-27 08:00:00'),
(137,37,13,'Frontend API và media domain khác nhau thế nào?','video','Nội dung video: Frontend API và media domain khác nhau thế nào?. File seed theo course_folder deploy-vps-aapanel.','/videos/deploy-vps-aapanel/deploy-vps-aapanel-02-frontend-api-va-media-domain-khac-nhau-the-nao.mp4','local',NULL,600,0,'published',2,'2026-06-27 08:00:00','2026-06-27 08:00:00'),
(138,37,13,'Vì sao nên tách domain frontend api và media?','video','Nội dung video: Vì sao nên tách domain frontend api và media?. File seed theo course_folder deploy-vps-aapanel.','/videos/deploy-vps-aapanel/deploy-vps-aapanel-03-vi-sao-nen-tach-domain-frontend-api-va-media.mp4','local',NULL,600,0,'published',3,'2026-06-27 08:00:00','2026-06-27 08:00:00'),
(139,37,13,'DNS domain và server IP hoạt động ra sao?','video','Nội dung video: DNS domain và server IP hoạt động ra sao?. File seed theo course_folder deploy-vps-aapanel.','/videos/deploy-vps-aapanel/deploy-vps-aapanel-04-dns-domain-va-server-ip-hoat-dong-ra-sao.mp4','local',NULL,600,0,'published',4,'2026-06-27 08:00:00','2026-06-27 08:00:00'),
(140,37,13,'Cấu trúc thư mục chuẩn trên aaPanel','video','Nội dung video: Cấu trúc thư mục chuẩn trên aaPanel. File seed theo course_folder deploy-vps-aapanel.','/videos/deploy-vps-aapanel/deploy-vps-aapanel-05-cau-truc-thu-muc-chuan-tren-aapanel.mp4','local',NULL,600,0,'published',5,'2026-06-27 08:00:00','2026-06-27 08:00:00'),
(141,38,13,'Checklist trước khi deploy lên VPS','video','Nội dung video: Checklist trước khi deploy lên VPS. File seed theo course_folder deploy-vps-aapanel.','/videos/deploy-vps-aapanel/deploy-vps-aapanel-06-checklist-truoc-khi-deploy-len-vps.mp4','local',NULL,600,0,'published',1,'2026-06-27 08:00:00','2026-06-27 08:00:00'),
(142,38,13,'Tạo website frontend trên aaPanel','video','Nội dung video: Tạo website frontend trên aaPanel. File seed theo course_folder deploy-vps-aapanel.','/videos/deploy-vps-aapanel/deploy-vps-aapanel-07-tao-website-frontend-tren-aapanel.mp4','local',NULL,600,0,'published',2,'2026-06-27 08:00:00','2026-06-27 08:00:00'),
(143,38,13,'Tạo website api cho Laravel public folder','video','Nội dung video: Tạo website api cho Laravel public folder. File seed theo course_folder deploy-vps-aapanel.','/videos/deploy-vps-aapanel/deploy-vps-aapanel-08-tao-website-api-cho-laravel-public-folder.mp4','local',NULL,600,0,'published',3,'2026-06-27 08:00:00','2026-06-27 08:00:00'),
(144,38,13,'Tạo website media để lưu video và ảnh','video','Nội dung video: Tạo website media để lưu video và ảnh. File seed theo course_folder deploy-vps-aapanel.','/videos/deploy-vps-aapanel/deploy-vps-aapanel-09-tao-website-media-de-luu-video-va-anh.mp4','local',NULL,600,0,'published',4,'2026-06-27 08:00:00','2026-06-27 08:00:00'),
(145,38,13,'Bật SSL cho domain và subdomain','video','Nội dung video: Bật SSL cho domain và subdomain. File seed theo course_folder deploy-vps-aapanel.','/videos/deploy-vps-aapanel/deploy-vps-aapanel-10-bat-ssl-cho-domain-va-subdomain.mp4','local',NULL,600,0,'published',5,'2026-06-27 08:00:00','2026-06-27 08:00:00'),
(146,39,13,'Upload code Laravel lên server','video','Nội dung video: Upload code Laravel lên server. File seed theo course_folder deploy-vps-aapanel.','/videos/deploy-vps-aapanel/deploy-vps-aapanel-11-upload-code-laravel-len-server.mp4','local',NULL,600,0,'published',1,'2026-06-27 08:00:00','2026-06-27 08:00:00'),
(147,39,13,'Cấu hình .env production cho Laravel','video','Nội dung video: Cấu hình .env production cho Laravel. File seed theo course_folder deploy-vps-aapanel.','/videos/deploy-vps-aapanel/deploy-vps-aapanel-12-cau-hinh-env-production-cho-laravel.mp4','local',NULL,600,0,'published',2,'2026-06-27 08:00:00','2026-06-27 08:00:00'),
(148,39,13,'Composer install và quyền thư mục storage bootstrap cache','video','Nội dung video: Composer install và quyền thư mục storage bootstrap cache. File seed theo course_folder deploy-vps-aapanel.','/videos/deploy-vps-aapanel/deploy-vps-aapanel-13-composer-install-va-quyen-thu-muc-storage-bootstrap-cache.mp4','local',NULL,600,0,'published',3,'2026-06-27 08:00:00','2026-06-27 08:00:00'),
(149,39,13,'Chạy migrate seed và cache config','video','Nội dung video: Chạy migrate seed và cache config. File seed theo course_folder deploy-vps-aapanel.','/videos/deploy-vps-aapanel/deploy-vps-aapanel-14-chay-migrate-seed-va-cache-config.mp4','local',NULL,600,0,'published',4,'2026-06-27 08:00:00','2026-06-27 08:00:00'),
(150,39,13,'Kiểm tra API health và lỗi Laravel log','video','Nội dung video: Kiểm tra API health và lỗi Laravel log. File seed theo course_folder deploy-vps-aapanel.','/videos/deploy-vps-aapanel/deploy-vps-aapanel-15-kiem-tra-api-health-va-loi-laravel-log.mp4','local',NULL,600,0,'published',5,'2026-06-27 08:00:00','2026-06-27 08:00:00'),
(151,40,13,'Cấu hình VITE_API_BASE_URL và VITE_MEDIA_BASE_URL','video','Nội dung video: Cấu hình VITE_API_BASE_URL và VITE_MEDIA_BASE_URL. File seed theo course_folder deploy-vps-aapanel.','/videos/deploy-vps-aapanel/deploy-vps-aapanel-16-cau-hinh-vite-api-base-url-va-vite-media-base-url.mp4','local',NULL,600,0,'published',1,'2026-06-27 08:00:00','2026-06-27 08:00:00'),
(152,40,13,'Build React Vite cho production','video','Nội dung video: Build React Vite cho production. File seed theo course_folder deploy-vps-aapanel.','/videos/deploy-vps-aapanel/deploy-vps-aapanel-17-build-react-vite-cho-production.mp4','local',NULL,600,0,'published',2,'2026-06-27 08:00:00','2026-06-27 08:00:00'),
(153,40,13,'Upload dist lên website frontend','video','Nội dung video: Upload dist lên website frontend. File seed theo course_folder deploy-vps-aapanel.','/videos/deploy-vps-aapanel/deploy-vps-aapanel-18-upload-dist-len-website-frontend.mp4','local',NULL,600,0,'published',3,'2026-06-27 08:00:00','2026-06-27 08:00:00'),
(154,40,13,'Cấu hình fallback cho React Router','video','Nội dung video: Cấu hình fallback cho React Router. File seed theo course_folder deploy-vps-aapanel.','/videos/deploy-vps-aapanel/deploy-vps-aapanel-19-cau-hinh-fallback-cho-react-router.mp4','local',NULL,600,0,'published',4,'2026-06-27 08:00:00','2026-06-27 08:00:00'),
(155,40,13,'Kiểm tra frontend gọi API qua HTTPS','video','Nội dung video: Kiểm tra frontend gọi API qua HTTPS. File seed theo course_folder deploy-vps-aapanel.','/videos/deploy-vps-aapanel/deploy-vps-aapanel-20-kiem-tra-frontend-goi-api-qua-https.mp4','local',NULL,600,0,'published',5,'2026-06-27 08:00:00','2026-06-27 08:00:00'),
(156,41,13,'Tạo cây thư mục video thumbnails assets certificates','video','Nội dung video: Tạo cây thư mục video thumbnails assets certificates. File seed theo course_folder deploy-vps-aapanel.','/videos/deploy-vps-aapanel/deploy-vps-aapanel-21-tao-cay-thu-muc-video-thumbnails-assets-certificates.mp4','local',NULL,600,0,'published',1,'2026-06-27 08:00:00','2026-06-27 08:00:00'),
(157,41,13,'Upload video khóa học theo course_folder','video','Nội dung video: Upload video khóa học theo course_folder. File seed theo course_folder deploy-vps-aapanel.','/videos/deploy-vps-aapanel/deploy-vps-aapanel-22-upload-video-khoa-hoc-theo-course-folder.mp4','local',NULL,600,0,'published',2,'2026-06-27 08:00:00','2026-06-27 08:00:00'),
(158,41,13,'DB nên lưu relative video_url như thế nào?','video','Nội dung video: DB nên lưu relative video_url như thế nào?. File seed theo course_folder deploy-vps-aapanel.','/videos/deploy-vps-aapanel/deploy-vps-aapanel-23-db-nen-luu-relative-video-url-nhu-the-nao.mp4','local',NULL,600,0,'published',3,'2026-06-27 08:00:00','2026-06-27 08:00:00'),
(159,41,13,'Kiểm tra public URL của file media','video','Nội dung video: Kiểm tra public URL của file media. File seed theo course_folder deploy-vps-aapanel.','/videos/deploy-vps-aapanel/deploy-vps-aapanel-24-kiem-tra-public-url-cua-file-media.mp4','local',NULL,600,0,'published',4,'2026-06-27 08:00:00','2026-06-27 08:00:00'),
(160,41,13,'Backup media và database trước ngày demo','video','Nội dung video: Backup media và database trước ngày demo. File seed theo course_folder deploy-vps-aapanel.','/videos/deploy-vps-aapanel/deploy-vps-aapanel-25-backup-media-va-database-truoc-ngay-demo.mp4','local',NULL,600,0,'published',5,'2026-06-27 08:00:00','2026-06-27 08:00:00'),
(161,42,13,'Fix lỗi 500 Laravel sau khi deploy','video','Nội dung video: Fix lỗi 500 Laravel sau khi deploy. File seed theo course_folder deploy-vps-aapanel.','/videos/deploy-vps-aapanel/deploy-vps-aapanel-26-fix-loi-500-laravel-sau-khi-deploy.mp4','local',NULL,600,0,'published',1,'2026-06-27 08:00:00','2026-06-27 08:00:00'),
(162,42,13,'Fix lỗi CORS khi frontend gọi API','video','Nội dung video: Fix lỗi CORS khi frontend gọi API. File seed theo course_folder deploy-vps-aapanel.','/videos/deploy-vps-aapanel/deploy-vps-aapanel-27-fix-loi-cors-khi-frontend-goi-api.mp4','local',NULL,600,0,'published',2,'2026-06-27 08:00:00','2026-06-27 08:00:00'),
(163,42,13,'Fix lỗi route refresh 404 trong React','video','Nội dung video: Fix lỗi route refresh 404 trong React. File seed theo course_folder deploy-vps-aapanel.','/videos/deploy-vps-aapanel/deploy-vps-aapanel-28-fix-loi-route-refresh-404-trong-react.mp4','local',NULL,600,0,'published',3,'2026-06-27 08:00:00','2026-06-27 08:00:00'),
(164,42,13,'Checklist bảo mật cơ bản sau deploy','video','Nội dung video: Checklist bảo mật cơ bản sau deploy. File seed theo course_folder deploy-vps-aapanel.','/videos/deploy-vps-aapanel/deploy-vps-aapanel-29-checklist-bao-mat-co-ban-sau-deploy.mp4','local',NULL,600,0,'published',4,'2026-06-27 08:00:00','2026-06-27 08:00:00'),
(165,42,13,'Tổng kết khóa học Deploy VPS aaPanel','video','Nội dung video: Tổng kết khóa học Deploy VPS aaPanel. File seed theo course_folder deploy-vps-aapanel.','/videos/deploy-vps-aapanel/deploy-vps-aapanel-30-tong-ket-khoa-hoc-deploy-vps-aapanel.mp4','local',NULL,600,0,'published',5,'2026-06-27 08:00:00','2026-06-27 08:00:00'),
(166,43,14,'Tailwind CSS là gì và phù hợp với đồ án như thế nào?','video','Nội dung video: Tailwind CSS là gì và phù hợp với đồ án như thế nào?. File seed theo course_folder tailwind-ui-elearning.','/videos/tailwind-ui-elearning/tailwind-ui-elearning-01-tailwind-css-la-gi-va-phu-hop-voi-do-an-nhu-the-nao.mp4','local',NULL,600,1,'published',1,'2026-06-27 08:00:00','2026-06-27 08:00:00'),
(167,43,14,'Utility class và tư duy thiết kế bằng Tailwind','video','Nội dung video: Utility class và tư duy thiết kế bằng Tailwind. File seed theo course_folder tailwind-ui-elearning.','/videos/tailwind-ui-elearning/tailwind-ui-elearning-02-utility-class-va-tu-duy-thiet-ke-bang-tailwind.mp4','local',NULL,600,0,'published',2,'2026-06-27 08:00:00','2026-06-27 08:00:00'),
(168,43,14,'Thiết lập màu sắc typography spacing cho MindHub','video','Nội dung video: Thiết lập màu sắc typography spacing cho MindHub. File seed theo course_folder tailwind-ui-elearning.','/videos/tailwind-ui-elearning/tailwind-ui-elearning-03-thiet-lap-mau-sac-typography-spacing-cho-mindhub.mp4','local',NULL,600,0,'published',3,'2026-06-27 08:00:00','2026-06-27 08:00:00'),
(169,43,14,'Responsive design trong Tailwind','video','Nội dung video: Responsive design trong Tailwind. File seed theo course_folder tailwind-ui-elearning.','/videos/tailwind-ui-elearning/tailwind-ui-elearning-04-responsive-design-trong-tailwind.mp4','local',NULL,600,0,'published',4,'2026-06-27 08:00:00','2026-06-27 08:00:00'),
(170,43,14,'Dark mode và trạng thái hover focus active','video','Nội dung video: Dark mode và trạng thái hover focus active. File seed theo course_folder tailwind-ui-elearning.','/videos/tailwind-ui-elearning/tailwind-ui-elearning-05-dark-mode-va-trang-thai-hover-focus-active.mp4','local',NULL,600,0,'published',5,'2026-06-27 08:00:00','2026-06-27 08:00:00'),
(171,44,14,'Thiết kế hero section cho E-learning','video','Nội dung video: Thiết kế hero section cho E-learning. File seed theo course_folder tailwind-ui-elearning.','/videos/tailwind-ui-elearning/tailwind-ui-elearning-06-thiet-ke-hero-section-cho-e-learning.mp4','local',NULL,600,0,'published',1,'2026-06-27 08:00:00','2026-06-27 08:00:00'),
(172,44,14,'Thiết kế category section và search bar','video','Nội dung video: Thiết kế category section và search bar. File seed theo course_folder tailwind-ui-elearning.','/videos/tailwind-ui-elearning/tailwind-ui-elearning-07-thiet-ke-category-section-va-search-bar.mp4','local',NULL,600,0,'published',2,'2026-06-27 08:00:00','2026-06-27 08:00:00'),
(173,44,14,'Thiết kế course card đẹp và dễ đọc','video','Nội dung video: Thiết kế course card đẹp và dễ đọc. File seed theo course_folder tailwind-ui-elearning.','/videos/tailwind-ui-elearning/tailwind-ui-elearning-08-thiet-ke-course-card-dep-va-de-doc.mp4','local',NULL,600,0,'published',3,'2026-06-27 08:00:00','2026-06-27 08:00:00'),
(174,44,14,'Thiết kế course grid responsive','video','Nội dung video: Thiết kế course grid responsive. File seed theo course_folder tailwind-ui-elearning.','/videos/tailwind-ui-elearning/tailwind-ui-elearning-09-thiet-ke-course-grid-responsive.mp4','local',NULL,600,0,'published',4,'2026-06-27 08:00:00','2026-06-27 08:00:00'),
(175,44,14,'Thiết kế empty state khi không có khóa học','video','Nội dung video: Thiết kế empty state khi không có khóa học. File seed theo course_folder tailwind-ui-elearning.','/videos/tailwind-ui-elearning/tailwind-ui-elearning-10-thiet-ke-empty-state-khi-khong-co-khoa-hoc.mp4','local',NULL,600,0,'published',5,'2026-06-27 08:00:00','2026-06-27 08:00:00'),
(176,45,14,'Thiết kế trang chi tiết khóa học','video','Nội dung video: Thiết kế trang chi tiết khóa học. File seed theo course_folder tailwind-ui-elearning.','/videos/tailwind-ui-elearning/tailwind-ui-elearning-11-thiet-ke-trang-chi-tiet-khoa-hoc.mp4','local',NULL,600,0,'published',1,'2026-06-27 08:00:00','2026-06-27 08:00:00'),
(177,45,14,'Thiết kế outline chương và bài học','video','Nội dung video: Thiết kế outline chương và bài học. File seed theo course_folder tailwind-ui-elearning.','/videos/tailwind-ui-elearning/tailwind-ui-elearning-12-thiet-ke-outline-chuong-va-bai-hoc.mp4','local',NULL,600,0,'published',2,'2026-06-27 08:00:00','2026-06-27 08:00:00'),
(178,45,14,'Thiết kế pricing box và CTA mua khóa học','video','Nội dung video: Thiết kế pricing box và CTA mua khóa học. File seed theo course_folder tailwind-ui-elearning.','/videos/tailwind-ui-elearning/tailwind-ui-elearning-13-thiet-ke-pricing-box-va-cta-mua-khoa-hoc.mp4','local',NULL,600,0,'published',3,'2026-06-27 08:00:00','2026-06-27 08:00:00'),
(179,45,14,'Thiết kế form coupon và order summary','video','Nội dung video: Thiết kế form coupon và order summary. File seed theo course_folder tailwind-ui-elearning.','/videos/tailwind-ui-elearning/tailwind-ui-elearning-14-thiet-ke-form-coupon-va-order-summary.mp4','local',NULL,600,0,'published',4,'2026-06-27 08:00:00','2026-06-27 08:00:00'),
(180,45,14,'Thiết kế trạng thái paid pending failed cho đơn hàng','video','Nội dung video: Thiết kế trạng thái paid pending failed cho đơn hàng. File seed theo course_folder tailwind-ui-elearning.','/videos/tailwind-ui-elearning/tailwind-ui-elearning-15-thiet-ke-trang-thai-paid-pending-failed-cho-don-hang.mp4','local',NULL,600,0,'published',5,'2026-06-27 08:00:00','2026-06-27 08:00:00'),
(181,46,14,'Thiết kế learning dashboard cho learner','video','Nội dung video: Thiết kế learning dashboard cho learner. File seed theo course_folder tailwind-ui-elearning.','/videos/tailwind-ui-elearning/tailwind-ui-elearning-16-thiet-ke-learning-dashboard-cho-learner.mp4','local',NULL,600,0,'published',1,'2026-06-27 08:00:00','2026-06-27 08:00:00'),
(182,46,14,'Thiết kế progress card và course progress','video','Nội dung video: Thiết kế progress card và course progress. File seed theo course_folder tailwind-ui-elearning.','/videos/tailwind-ui-elearning/tailwind-ui-elearning-17-thiet-ke-progress-card-va-course-progress.mp4','local',NULL,600,0,'published',2,'2026-06-27 08:00:00','2026-06-27 08:00:00'),
(183,46,14,'Thiết kế lesson video layout','video','Nội dung video: Thiết kế lesson video layout. File seed theo course_folder tailwind-ui-elearning.','/videos/tailwind-ui-elearning/tailwind-ui-elearning-18-thiet-ke-lesson-video-layout.mp4','local',NULL,600,0,'published',3,'2026-06-27 08:00:00','2026-06-27 08:00:00'),
(184,46,14,'Thiết kế sidebar chương bài học','video','Nội dung video: Thiết kế sidebar chương bài học. File seed theo course_folder tailwind-ui-elearning.','/videos/tailwind-ui-elearning/tailwind-ui-elearning-19-thiet-ke-sidebar-chuong-bai-hoc.mp4','local',NULL,600,0,'published',4,'2026-06-27 08:00:00','2026-06-27 08:00:00'),
(185,46,14,'Thiết kế notes panel trong màn học','video','Nội dung video: Thiết kế notes panel trong màn học. File seed theo course_folder tailwind-ui-elearning.','/videos/tailwind-ui-elearning/tailwind-ui-elearning-20-thiet-ke-notes-panel-trong-man-hoc.mp4','local',NULL,600,0,'published',5,'2026-06-27 08:00:00','2026-06-27 08:00:00'),
(186,47,14,'Thiết kế dashboard cards và chart placeholder','video','Nội dung video: Thiết kế dashboard cards và chart placeholder. File seed theo course_folder tailwind-ui-elearning.','/videos/tailwind-ui-elearning/tailwind-ui-elearning-21-thiet-ke-dashboard-cards-va-chart-placeholder.mp4','local',NULL,600,0,'published',1,'2026-06-27 08:00:00','2026-06-27 08:00:00'),
(187,47,14,'Thiết kế table quản lý khóa học','video','Nội dung video: Thiết kế table quản lý khóa học. File seed theo course_folder tailwind-ui-elearning.','/videos/tailwind-ui-elearning/tailwind-ui-elearning-22-thiet-ke-table-quan-ly-khoa-hoc.mp4','local',NULL,600,0,'published',2,'2026-06-27 08:00:00','2026-06-27 08:00:00'),
(188,47,14,'Thiết kế form tạo khóa học','video','Nội dung video: Thiết kế form tạo khóa học. File seed theo course_folder tailwind-ui-elearning.','/videos/tailwind-ui-elearning/tailwind-ui-elearning-23-thiet-ke-form-tao-khoa-hoc.mp4','local',NULL,600,0,'published',3,'2026-06-27 08:00:00','2026-06-27 08:00:00'),
(189,47,14,'Thiết kế modal upload video và thumbnail','video','Nội dung video: Thiết kế modal upload video và thumbnail. File seed theo course_folder tailwind-ui-elearning.','/videos/tailwind-ui-elearning/tailwind-ui-elearning-24-thiet-ke-modal-upload-video-va-thumbnail.mp4','local',NULL,600,0,'published',4,'2026-06-27 08:00:00','2026-06-27 08:00:00'),
(190,47,14,'Thiết kế checklist publish course','video','Nội dung video: Thiết kế checklist publish course. File seed theo course_folder tailwind-ui-elearning.','/videos/tailwind-ui-elearning/tailwind-ui-elearning-25-thiet-ke-checklist-publish-course.mp4','local',NULL,600,0,'published',5,'2026-06-27 08:00:00','2026-06-27 08:00:00'),
(191,48,14,'Chuẩn hóa component button input badge','video','Nội dung video: Chuẩn hóa component button input badge. File seed theo course_folder tailwind-ui-elearning.','/videos/tailwind-ui-elearning/tailwind-ui-elearning-26-chuan-hoa-component-button-input-badge.mp4','local',NULL,600,0,'published',1,'2026-06-27 08:00:00','2026-06-27 08:00:00'),
(192,48,14,'Xử lý loading skeleton và error state','video','Nội dung video: Xử lý loading skeleton và error state. File seed theo course_folder tailwind-ui-elearning.','/videos/tailwind-ui-elearning/tailwind-ui-elearning-27-xu-ly-loading-skeleton-va-error-state.mp4','local',NULL,600,0,'published',2,'2026-06-27 08:00:00','2026-06-27 08:00:00'),
(193,48,14,'Kiểm tra responsive mobile tablet desktop','video','Nội dung video: Kiểm tra responsive mobile tablet desktop. File seed theo course_folder tailwind-ui-elearning.','/videos/tailwind-ui-elearning/tailwind-ui-elearning-28-kiem-tra-responsive-mobile-tablet-desktop.mp4','local',NULL,600,0,'published',3,'2026-06-27 08:00:00','2026-06-27 08:00:00'),
(194,48,14,'Checklist UI trước khi trình chiếu sản phẩm','video','Nội dung video: Checklist UI trước khi trình chiếu sản phẩm. File seed theo course_folder tailwind-ui-elearning.','/videos/tailwind-ui-elearning/tailwind-ui-elearning-29-checklist-ui-truoc-khi-trinh-chieu-san-pham.mp4','local',NULL,600,0,'published',4,'2026-06-27 08:00:00','2026-06-27 08:00:00'),
(195,48,14,'Tổng kết khóa học Tailwind UI E-learning','video','Nội dung video: Tổng kết khóa học Tailwind UI E-learning. File seed theo course_folder tailwind-ui-elearning.','/videos/tailwind-ui-elearning/tailwind-ui-elearning-30-tong-ket-khoa-hoc-tailwind-ui-e-learning.mp4','local',NULL,600,0,'published',5,'2026-06-27 08:00:00','2026-06-27 08:00:00'),
(196,49,15,'Vì sao phải chốt scope trước khi báo giá?','video','Nội dung video: Vì sao phải chốt scope trước khi báo giá?. File seed theo course_folder web-project-management.','/videos/web-project-management/web-project-management-01-vi-sao-phai-chot-scope-truoc-khi-bao-gia.mp4','local',NULL,600,1,'published',1,'2026-06-27 08:00:00','2026-06-27 08:00:00'),
(197,49,15,'Cách phân loại landing page website giới thiệu và web app','video','Nội dung video: Cách phân loại landing page website giới thiệu và web app. File seed theo course_folder web-project-management.','/videos/web-project-management/web-project-management-02-cach-phan-loai-landing-page-website-gioi-thieu-va-web-app.mp4','local',NULL,600,0,'published',2,'2026-06-27 08:00:00','2026-06-27 08:00:00'),
(198,49,15,'Cách bóc tách tính năng thành hạng mục','video','Nội dung video: Cách bóc tách tính năng thành hạng mục. File seed theo course_folder web-project-management.','/videos/web-project-management/web-project-management-03-cach-boc-tach-tinh-nang-thanh-hang-muc.mp4','local',NULL,600,0,'published',3,'2026-06-27 08:00:00','2026-06-27 08:00:00'),
(199,49,15,'Cách xác định phần khách hàng phải cung cấp','video','Nội dung video: Cách xác định phần khách hàng phải cung cấp. File seed theo course_folder web-project-management.','/videos/web-project-management/web-project-management-04-cach-xac-dinh-phan-khach-hang-phai-cung-cap.mp4','local',NULL,600,0,'published',4,'2026-06-27 08:00:00','2026-06-27 08:00:00'),
(200,49,15,'Cách tránh nhận dự án vượt khả năng','video','Nội dung video: Cách tránh nhận dự án vượt khả năng. File seed theo course_folder web-project-management.','/videos/web-project-management/web-project-management-05-cach-tranh-nhan-du-an-vuot-kha-nang.mp4','local',NULL,600,0,'published',5,'2026-06-27 08:00:00','2026-06-27 08:00:00'),
(201,50,15,'Các yếu tố ảnh hưởng đến giá một website','video','Nội dung video: Các yếu tố ảnh hưởng đến giá một website. File seed theo course_folder web-project-management.','/videos/web-project-management/web-project-management-06-cac-yeu-to-anh-huong-den-gia-mot-website.mp4','local',NULL,600,0,'published',1,'2026-06-27 08:00:00','2026-06-27 08:00:00'),
(202,50,15,'Cách báo giá theo gói đơn giản cho người mới','video','Nội dung video: Cách báo giá theo gói đơn giản cho người mới. File seed theo course_folder web-project-management.','/videos/web-project-management/web-project-management-07-cach-bao-gia-theo-goi-don-gian-cho-nguoi-moi.mp4','local',NULL,600,0,'published',2,'2026-06-27 08:00:00','2026-06-27 08:00:00'),
(203,50,15,'Cách ghi rõ phạm vi chỉnh sửa','video','Nội dung video: Cách ghi rõ phạm vi chỉnh sửa. File seed theo course_folder web-project-management.','/videos/web-project-management/web-project-management-08-cach-ghi-ro-pham-vi-chinh-sua.mp4','local',NULL,600,0,'published',3,'2026-06-27 08:00:00','2026-06-27 08:00:00'),
(204,50,15,'Những điều khoản cơ bản nên có trong hợp đồng','video','Nội dung video: Những điều khoản cơ bản nên có trong hợp đồng. File seed theo course_folder web-project-management.','/videos/web-project-management/web-project-management-09-nhung-dieu-khoan-co-ban-nen-co-trong-hop-dong.mp4','local',NULL,600,0,'published',4,'2026-06-27 08:00:00','2026-06-27 08:00:00'),
(205,50,15,'Cách xử lý đặt cọc thanh toán và bàn giao','video','Nội dung video: Cách xử lý đặt cọc thanh toán và bàn giao. File seed theo course_folder web-project-management.','/videos/web-project-management/web-project-management-10-cach-xu-ly-dat-coc-thanh-toan-va-ban-giao.mp4','local',NULL,600,0,'published',5,'2026-06-27 08:00:00','2026-06-27 08:00:00'),
(206,51,15,'Cách lập timeline dự án web 2 đến 4 tuần','video','Nội dung video: Cách lập timeline dự án web 2 đến 4 tuần. File seed theo course_folder web-project-management.','/videos/web-project-management/web-project-management-11-cach-lap-timeline-du-an-web-2-den-4-tuan.mp4','local',NULL,600,0,'published',1,'2026-06-27 08:00:00','2026-06-27 08:00:00'),
(207,51,15,'Cách gửi update tiến độ cho khách hàng','video','Nội dung video: Cách gửi update tiến độ cho khách hàng. File seed theo course_folder web-project-management.','/videos/web-project-management/web-project-management-12-cach-gui-update-tien-do-cho-khach-hang.mp4','local',NULL,600,0,'published',2,'2026-06-27 08:00:00','2026-06-27 08:00:00'),
(208,51,15,'Cách nhận feedback mà không bị loạn yêu cầu','video','Nội dung video: Cách nhận feedback mà không bị loạn yêu cầu. File seed theo course_folder web-project-management.','/videos/web-project-management/web-project-management-13-cach-nhan-feedback-ma-khong-bi-loan-yeu-cau.mp4','local',NULL,600,0,'published',3,'2026-06-27 08:00:00','2026-06-27 08:00:00'),
(209,51,15,'Cách quản lý file hình ảnh nội dung và tài khoản','video','Nội dung video: Cách quản lý file hình ảnh nội dung và tài khoản. File seed theo course_folder web-project-management.','/videos/web-project-management/web-project-management-14-cach-quan-ly-file-hinh-anh-noi-dung-va-tai-khoan.mp4','local',NULL,600,0,'published',4,'2026-06-27 08:00:00','2026-06-27 08:00:00'),
(210,51,15,'Cách xử lý khi dự án bị trễ','video','Nội dung video: Cách xử lý khi dự án bị trễ. File seed theo course_folder web-project-management.','/videos/web-project-management/web-project-management-15-cach-xu-ly-khi-du-an-bi-tre.mp4','local',NULL,600,0,'published',5,'2026-06-27 08:00:00','2026-06-27 08:00:00'),
(211,52,15,'Checklist trước khi bàn giao website','video','Nội dung video: Checklist trước khi bàn giao website. File seed theo course_folder web-project-management.','/videos/web-project-management/web-project-management-16-checklist-truoc-khi-ban-giao-website.mp4','local',NULL,600,0,'published',1,'2026-06-27 08:00:00','2026-06-27 08:00:00'),
(212,52,15,'Cách viết tài liệu hướng dẫn sử dụng ngắn','video','Nội dung video: Cách viết tài liệu hướng dẫn sử dụng ngắn. File seed theo course_folder web-project-management.','/videos/web-project-management/web-project-management-17-cach-viet-tai-lieu-huong-dan-su-dung-ngan.mp4','local',NULL,600,0,'published',2,'2026-06-27 08:00:00','2026-06-27 08:00:00'),
(213,52,15,'Cách báo giá bảo trì hosting domain và chỉnh sửa','video','Nội dung video: Cách báo giá bảo trì hosting domain và chỉnh sửa. File seed theo course_folder web-project-management.','/videos/web-project-management/web-project-management-18-cach-bao-gia-bao-tri-hosting-domain-va-chinh-sua.mp4','local',NULL,600,0,'published',3,'2026-06-27 08:00:00','2026-06-27 08:00:00'),
(214,52,15,'Cách lưu source code và backup sau dự án','video','Nội dung video: Cách lưu source code và backup sau dự án. File seed theo course_folder web-project-management.','/videos/web-project-management/web-project-management-19-cach-luu-source-code-va-backup-sau-du-an.mp4','local',NULL,600,0,'published',4,'2026-06-27 08:00:00','2026-06-27 08:00:00'),
(215,52,15,'Tổng kết khóa học quản lý dự án web nhỏ','video','Nội dung video: Tổng kết khóa học quản lý dự án web nhỏ. File seed theo course_folder web-project-management.','/videos/web-project-management/web-project-management-20-tong-ket-khoa-hoc-quan-ly-du-an-web-nho.mp4','local',NULL,600,0,'published',5,'2026-06-27 08:00:00','2026-06-27 08:00:00'),
(216,53,16,'Freelance Web Developer là gì?','video','Nội dung video: Freelance Web Developer là gì?. File seed theo course_folder freelance-web-developer.','/videos/freelance-web-developer/freelance-web-developer-01-freelance-web-developer-la-gi.mp4','local',NULL,600,1,'published',1,'2026-06-27 08:00:00','2026-06-27 08:00:00'),
(217,53,16,'Người mới nên nhận loại dự án web nào?','video','Nội dung video: Người mới nên nhận loại dự án web nào?. File seed theo course_folder freelance-web-developer.','/videos/freelance-web-developer/freelance-web-developer-02-nguoi-moi-nen-nhan-loai-du-an-web-nao.mp4','local',NULL,600,0,'published',2,'2026-06-27 08:00:00','2026-06-27 08:00:00'),
(218,53,16,'Portfolio cần có gì trước khi tìm khách hàng?','video','Nội dung video: Portfolio cần có gì trước khi tìm khách hàng?. File seed theo course_folder freelance-web-developer.','/videos/freelance-web-developer/freelance-web-developer-03-portfolio-can-co-gi-truoc-khi-tim-khach-hang.mp4','local',NULL,600,0,'published',3,'2026-06-27 08:00:00','2026-06-27 08:00:00'),
(219,53,16,'Cách chọn niche dịch vụ web phù hợp','video','Nội dung video: Cách chọn niche dịch vụ web phù hợp. File seed theo course_folder freelance-web-developer.','/videos/freelance-web-developer/freelance-web-developer-04-cach-chon-niche-dich-vu-web-phu-hop.mp4','local',NULL,600,0,'published',4,'2026-06-27 08:00:00','2026-06-27 08:00:00'),
(220,53,16,'Những sai lầm khiến freelancer mới dễ mất tiền','video','Nội dung video: Những sai lầm khiến freelancer mới dễ mất tiền. File seed theo course_folder freelance-web-developer.','/videos/freelance-web-developer/freelance-web-developer-05-nhung-sai-lam-khien-freelancer-moi-de-mat-tien.mp4','local',NULL,600,0,'published',5,'2026-06-27 08:00:00','2026-06-27 08:00:00'),
(221,54,16,'Cách trình bày 3 dự án web nổi bật','video','Nội dung video: Cách trình bày 3 dự án web nổi bật. File seed theo course_folder freelance-web-developer.','/videos/freelance-web-developer/freelance-web-developer-06-cach-trinh-bay-3-du-an-web-noi-bat.mp4','local',NULL,600,0,'published',1,'2026-06-27 08:00:00','2026-06-27 08:00:00'),
(222,54,16,'Cách viết mô tả dịch vụ làm landing page','video','Nội dung video: Cách viết mô tả dịch vụ làm landing page. File seed theo course_folder freelance-web-developer.','/videos/freelance-web-developer/freelance-web-developer-07-cach-viet-mo-ta-dich-vu-lam-landing-page.mp4','local',NULL,600,0,'published',2,'2026-06-27 08:00:00','2026-06-27 08:00:00'),
(223,54,16,'Cách dùng GitHub và demo để tăng độ tin cậy','video','Nội dung video: Cách dùng GitHub và demo để tăng độ tin cậy. File seed theo course_folder freelance-web-developer.','/videos/freelance-web-developer/freelance-web-developer-08-cach-dung-github-va-demo-de-tang-do-tin-cay.mp4','local',NULL,600,0,'published',3,'2026-06-27 08:00:00','2026-06-27 08:00:00'),
(224,54,16,'Cách viết case study ngắn cho project cá nhân','video','Nội dung video: Cách viết case study ngắn cho project cá nhân. File seed theo course_folder freelance-web-developer.','/videos/freelance-web-developer/freelance-web-developer-09-cach-viet-case-study-ngan-cho-project-ca-nhan.mp4','local',NULL,600,0,'published',4,'2026-06-27 08:00:00','2026-06-27 08:00:00'),
(225,54,16,'Cách chuẩn bị mẫu tin nhắn giới thiệu dịch vụ','video','Nội dung video: Cách chuẩn bị mẫu tin nhắn giới thiệu dịch vụ. File seed theo course_folder freelance-web-developer.','/videos/freelance-web-developer/freelance-web-developer-10-cach-chuan-bi-mau-tin-nhan-gioi-thieu-dich-vu.mp4','local',NULL,600,0,'published',5,'2026-06-27 08:00:00','2026-06-27 08:00:00'),
(226,55,16,'Tìm khách hàng đầu tiên ở đâu?','video','Nội dung video: Tìm khách hàng đầu tiên ở đâu?. File seed theo course_folder freelance-web-developer.','/videos/freelance-web-developer/freelance-web-developer-11-tim-khach-hang-dau-tien-o-dau.mp4','local',NULL,600,0,'published',1,'2026-06-27 08:00:00','2026-06-27 08:00:00'),
(227,55,16,'Cách hỏi nhu cầu khách hàng trước khi báo giá','video','Nội dung video: Cách hỏi nhu cầu khách hàng trước khi báo giá. File seed theo course_folder freelance-web-developer.','/videos/freelance-web-developer/freelance-web-developer-12-cach-hoi-nhu-cau-khach-hang-truoc-khi-bao-gia.mp4','local',NULL,600,0,'published',2,'2026-06-27 08:00:00','2026-06-27 08:00:00'),
(228,55,16,'Cách giải thích phạm vi website bằng ngôn ngữ dễ hiểu','video','Nội dung video: Cách giải thích phạm vi website bằng ngôn ngữ dễ hiểu. File seed theo course_folder freelance-web-developer.','/videos/freelance-web-developer/freelance-web-developer-13-cach-giai-thich-pham-vi-website-bang-ngon-ngu-de-hieu.mp4','local',NULL,600,0,'published',3,'2026-06-27 08:00:00','2026-06-27 08:00:00'),
(229,55,16,'Cách xử lý khách hàng muốn thêm chức năng liên tục','video','Nội dung video: Cách xử lý khách hàng muốn thêm chức năng liên tục. File seed theo course_folder freelance-web-developer.','/videos/freelance-web-developer/freelance-web-developer-14-cach-xu-ly-khach-hang-muon-them-chuc-nang-lien-tuc.mp4','local',NULL,600,0,'published',4,'2026-06-27 08:00:00','2026-06-27 08:00:00'),
(230,55,16,'Cách giữ giao tiếp chuyên nghiệp trong dự án nhỏ','video','Nội dung video: Cách giữ giao tiếp chuyên nghiệp trong dự án nhỏ. File seed theo course_folder freelance-web-developer.','/videos/freelance-web-developer/freelance-web-developer-15-cach-giu-giao-tiep-chuyen-nghiep-trong-du-an-nho.mp4','local',NULL,600,0,'published',5,'2026-06-27 08:00:00','2026-06-27 08:00:00'),
(231,56,16,'Checklist bàn giao website cho khách hàng','video','Nội dung video: Checklist bàn giao website cho khách hàng. File seed theo course_folder freelance-web-developer.','/videos/freelance-web-developer/freelance-web-developer-16-checklist-ban-giao-website-cho-khach-hang.mp4','local',NULL,600,0,'published',1,'2026-06-27 08:00:00','2026-06-27 08:00:00'),
(232,56,16,'Cách hướng dẫn khách hàng sử dụng website','video','Nội dung video: Cách hướng dẫn khách hàng sử dụng website. File seed theo course_folder freelance-web-developer.','/videos/freelance-web-developer/freelance-web-developer-17-cach-huong-dan-khach-hang-su-dung-website.mp4','local',NULL,600,0,'published',2,'2026-06-27 08:00:00','2026-06-27 08:00:00'),
(233,56,16,'Cách xin feedback và testimonial sau dự án','video','Nội dung video: Cách xin feedback và testimonial sau dự án. File seed theo course_folder freelance-web-developer.','/videos/freelance-web-developer/freelance-web-developer-18-cach-xin-feedback-va-testimonial-sau-du-an.mp4','local',NULL,600,0,'published',3,'2026-06-27 08:00:00','2026-06-27 08:00:00'),
(234,56,16,'Cách biến một dự án nhỏ thành portfolio tốt hơn','video','Nội dung video: Cách biến một dự án nhỏ thành portfolio tốt hơn. File seed theo course_folder freelance-web-developer.','/videos/freelance-web-developer/freelance-web-developer-19-cach-bien-mot-du-an-nho-thanh-portfolio-tot-hon.mp4','local',NULL,600,0,'published',4,'2026-06-27 08:00:00','2026-06-27 08:00:00'),
(235,56,16,'Tổng kết khóa học Freelance Web Developer','video','Nội dung video: Tổng kết khóa học Freelance Web Developer. File seed theo course_folder freelance-web-developer.','/videos/freelance-web-developer/freelance-web-developer-20-tong-ket-khoa-hoc-freelance-web-developer.mp4','local',NULL,600,0,'published',5,'2026-06-27 08:00:00','2026-06-27 08:00:00'),
(236,57,17,'SaaS là gì và khác website thông thường thế nào?','video','Nội dung video: SaaS là gì và khác website thông thường thế nào?. File seed theo course_folder saas-product-thinking.','/videos/saas-product-thinking/saas-product-thinking-01-saas-la-gi-va-khac-website-thong-thuong-the-nao.mp4','local',NULL,600,1,'published',1,'2026-06-27 08:00:00','2026-06-27 08:00:00'),
(237,57,17,'Vì sao lập trình viên web nên hiểu tư duy SaaS?','video','Nội dung video: Vì sao lập trình viên web nên hiểu tư duy SaaS?. File seed theo course_folder saas-product-thinking.','/videos/saas-product-thinking/saas-product-thinking-02-vi-sao-lap-trinh-vien-web-nen-hieu-tu-duy-saas.mp4','local',NULL,600,0,'published',2,'2026-06-27 08:00:00','2026-06-27 08:00:00'),
(238,57,17,'User subscription plan và billing là gì?','video','Nội dung video: User subscription plan và billing là gì?. File seed theo course_folder saas-product-thinking.','/videos/saas-product-thinking/saas-product-thinking-03-user-subscription-plan-va-billing-la-gi.mp4','local',NULL,600,0,'published',3,'2026-06-27 08:00:00','2026-06-27 08:00:00'),
(239,57,17,'Multi-tenant là gì ở mức dễ hiểu?','video','Nội dung video: Multi-tenant là gì ở mức dễ hiểu?. File seed theo course_folder saas-product-thinking.','/videos/saas-product-thinking/saas-product-thinking-04-multi-tenant-la-gi-o-muc-de-hieu.mp4','local',NULL,600,0,'published',4,'2026-06-27 08:00:00','2026-06-27 08:00:00'),
(240,57,17,'Những ví dụ SaaS gần với sinh viên IT','video','Nội dung video: Những ví dụ SaaS gần với sinh viên IT. File seed theo course_folder saas-product-thinking.','/videos/saas-product-thinking/saas-product-thinking-05-nhung-vi-du-saas-gan-voi-sinh-vien-it.mp4','local',NULL,600,0,'published',5,'2026-06-27 08:00:00','2026-06-27 08:00:00'),
(241,58,17,'Cách xác định user role trong SaaS','video','Nội dung video: Cách xác định user role trong SaaS. File seed theo course_folder saas-product-thinking.','/videos/saas-product-thinking/saas-product-thinking-06-cach-xac-dinh-user-role-trong-saas.mp4','local',NULL,600,0,'published',1,'2026-06-27 08:00:00','2026-06-27 08:00:00'),
(242,58,17,'Cách thiết kế onboarding cho người dùng mới','video','Nội dung video: Cách thiết kế onboarding cho người dùng mới. File seed theo course_folder saas-product-thinking.','/videos/saas-product-thinking/saas-product-thinking-07-cach-thiet-ke-onboarding-cho-nguoi-dung-moi.mp4','local',NULL,600,0,'published',2,'2026-06-27 08:00:00','2026-06-27 08:00:00'),
(243,58,17,'Cách tổ chức dashboard cho sản phẩm SaaS','video','Nội dung video: Cách tổ chức dashboard cho sản phẩm SaaS. File seed theo course_folder saas-product-thinking.','/videos/saas-product-thinking/saas-product-thinking-08-cach-to-chuc-dashboard-cho-san-pham-saas.mp4','local',NULL,600,0,'published',3,'2026-06-27 08:00:00','2026-06-27 08:00:00'),
(244,58,17,'Cách nghĩ về permission và giới hạn gói dịch vụ','video','Nội dung video: Cách nghĩ về permission và giới hạn gói dịch vụ. File seed theo course_folder saas-product-thinking.','/videos/saas-product-thinking/saas-product-thinking-09-cach-nghi-ve-permission-va-gioi-han-goi-dich-vu.mp4','local',NULL,600,0,'published',4,'2026-06-27 08:00:00','2026-06-27 08:00:00'),
(245,58,17,'Cách viết roadmap tính năng nhỏ','video','Nội dung video: Cách viết roadmap tính năng nhỏ. File seed theo course_folder saas-product-thinking.','/videos/saas-product-thinking/saas-product-thinking-10-cach-viet-roadmap-tinh-nang-nho.mp4','local',NULL,600,0,'published',5,'2026-06-27 08:00:00','2026-06-27 08:00:00'),
(246,59,17,'Metrics cơ bản của SaaS cần biết','video','Nội dung video: Metrics cơ bản của SaaS cần biết. File seed theo course_folder saas-product-thinking.','/videos/saas-product-thinking/saas-product-thinking-11-metrics-co-ban-cua-saas-can-biet.mp4','local',NULL,600,0,'published',1,'2026-06-27 08:00:00','2026-06-27 08:00:00'),
(247,59,17,'Activation retention churn là gì?','video','Nội dung video: Activation retention churn là gì?. File seed theo course_folder saas-product-thinking.','/videos/saas-product-thinking/saas-product-thinking-12-activation-retention-churn-la-gi.mp4','local',NULL,600,0,'published',2,'2026-06-27 08:00:00','2026-06-27 08:00:00'),
(248,59,17,'Cách thu feedback và ưu tiên cải tiến','video','Nội dung video: Cách thu feedback và ưu tiên cải tiến. File seed theo course_folder saas-product-thinking.','/videos/saas-product-thinking/saas-product-thinking-13-cach-thu-feedback-va-uu-tien-cai-tien.mp4','local',NULL,600,0,'published',3,'2026-06-27 08:00:00','2026-06-27 08:00:00'),
(249,59,17,'Cách viết changelog và thông báo cập nhật','video','Nội dung video: Cách viết changelog và thông báo cập nhật. File seed theo course_folder saas-product-thinking.','/videos/saas-product-thinking/saas-product-thinking-14-cach-viet-changelog-va-thong-bao-cap-nhat.mp4','local',NULL,600,0,'published',4,'2026-06-27 08:00:00','2026-06-27 08:00:00'),
(250,59,17,'Cách hỗ trợ người dùng khi có lỗi','video','Nội dung video: Cách hỗ trợ người dùng khi có lỗi. File seed theo course_folder saas-product-thinking.','/videos/saas-product-thinking/saas-product-thinking-15-cach-ho-tro-nguoi-dung-khi-co-loi.mp4','local',NULL,600,0,'published',5,'2026-06-27 08:00:00','2026-06-27 08:00:00'),
(251,60,17,'Cách biến đồ án E-learning thành ý tưởng SaaS','video','Nội dung video: Cách biến đồ án E-learning thành ý tưởng SaaS. File seed theo course_folder saas-product-thinking.','/videos/saas-product-thinking/saas-product-thinking-16-cach-bien-do-an-e-learning-thanh-y-tuong-saas.mp4','local',NULL,600,0,'published',1,'2026-06-27 08:00:00','2026-06-27 08:00:00'),
(252,60,17,'Cách trình bày SaaS case study trong CV','video','Nội dung video: Cách trình bày SaaS case study trong CV. File seed theo course_folder saas-product-thinking.','/videos/saas-product-thinking/saas-product-thinking-17-cach-trinh-bay-saas-case-study-trong-cv.mp4','local',NULL,600,0,'published',2,'2026-06-27 08:00:00','2026-06-27 08:00:00'),
(253,60,17,'Cách demo dashboard subscription và analytics','video','Nội dung video: Cách demo dashboard subscription và analytics. File seed theo course_folder saas-product-thinking.','/videos/saas-product-thinking/saas-product-thinking-18-cach-demo-dashboard-subscription-va-analytics.mp4','local',NULL,600,0,'published',3,'2026-06-27 08:00:00','2026-06-27 08:00:00'),
(254,60,17,'Checklist sản phẩm SaaS mini cho Web Developer','video','Nội dung video: Checklist sản phẩm SaaS mini cho Web Developer. File seed theo course_folder saas-product-thinking.','/videos/saas-product-thinking/saas-product-thinking-19-checklist-san-pham-saas-mini-cho-web-developer.mp4','local',NULL,600,0,'published',4,'2026-06-27 08:00:00','2026-06-27 08:00:00'),
(255,60,17,'Tổng kết khóa học tư duy SaaS cho Web Developer','video','Nội dung video: Tổng kết khóa học tư duy SaaS cho Web Developer. File seed theo course_folder saas-product-thinking.','/videos/saas-product-thinking/saas-product-thinking-20-tong-ket-khoa-hoc-tu-duy-saas-cho-web-developer.mp4','local',NULL,600,0,'published',5,'2026-06-27 08:00:00','2026-06-27 08:00:00'),
(256,61,18,'MVP là gì và vì sao người làm web nên biết?','video','Nội dung video: MVP là gì và vì sao người làm web nên biết?. File seed theo course_folder mvp-web-product.','/videos/mvp-web-product/mvp-web-product-01-mvp-la-gi-va-vi-sao-nguoi-lam-web-nen-biet.mp4','local',NULL,600,1,'published',1,'2026-06-27 08:00:00','2026-06-27 08:00:00'),
(257,61,18,'Sản phẩm web khác project học tập như thế nào?','video','Nội dung video: Sản phẩm web khác project học tập như thế nào?. File seed theo course_folder mvp-web-product.','/videos/mvp-web-product/mvp-web-product-02-san-pham-web-khac-project-hoc-tap-nhu-the-nao.mp4','local',NULL,600,0,'published',2,'2026-06-27 08:00:00','2026-06-27 08:00:00'),
(258,61,18,'Cách tìm vấn đề nhỏ đáng giải quyết','video','Nội dung video: Cách tìm vấn đề nhỏ đáng giải quyết. File seed theo course_folder mvp-web-product.','/videos/mvp-web-product/mvp-web-product-03-cach-tim-van-de-nho-dang-giai-quyet.mp4','local',NULL,600,0,'published',3,'2026-06-27 08:00:00','2026-06-27 08:00:00'),
(259,61,18,'Cách xác định người dùng mục tiêu','video','Nội dung video: Cách xác định người dùng mục tiêu. File seed theo course_folder mvp-web-product.','/videos/mvp-web-product/mvp-web-product-04-cach-xac-dinh-nguoi-dung-muc-tieu.mp4','local',NULL,600,0,'published',4,'2026-06-27 08:00:00','2026-06-27 08:00:00'),
(260,61,18,'Những lỗi khi làm MVP quá lớn ngay từ đầu','video','Nội dung video: Những lỗi khi làm MVP quá lớn ngay từ đầu. File seed theo course_folder mvp-web-product.','/videos/mvp-web-product/mvp-web-product-05-nhung-loi-khi-lam-mvp-qua-lon-ngay-tu-dau.mp4','local',NULL,600,0,'published',5,'2026-06-27 08:00:00','2026-06-27 08:00:00'),
(261,62,18,'Cách viết problem solution cho ý tưởng web','video','Nội dung video: Cách viết problem solution cho ý tưởng web. File seed theo course_folder mvp-web-product.','/videos/mvp-web-product/mvp-web-product-06-cach-viet-problem-solution-cho-y-tuong-web.mp4','local',NULL,600,0,'published',1,'2026-06-27 08:00:00','2026-06-27 08:00:00'),
(262,62,18,'Cách chọn 3 tính năng cốt lõi đầu tiên','video','Nội dung video: Cách chọn 3 tính năng cốt lõi đầu tiên. File seed theo course_folder mvp-web-product.','/videos/mvp-web-product/mvp-web-product-07-cach-chon-3-tinh-nang-cot-loi-dau-tien.mp4','local',NULL,600,0,'published',2,'2026-06-27 08:00:00','2026-06-27 08:00:00'),
(263,62,18,'Cách vẽ user flow đơn giản','video','Nội dung video: Cách vẽ user flow đơn giản. File seed theo course_folder mvp-web-product.','/videos/mvp-web-product/mvp-web-product-08-cach-ve-user-flow-don-gian.mp4','local',NULL,600,0,'published',3,'2026-06-27 08:00:00','2026-06-27 08:00:00'),
(264,62,18,'Cách thiết kế database tối thiểu cho MVP','video','Nội dung video: Cách thiết kế database tối thiểu cho MVP. File seed theo course_folder mvp-web-product.','/videos/mvp-web-product/mvp-web-product-09-cach-thiet-ke-database-toi-thieu-cho-mvp.mp4','local',NULL,600,0,'published',4,'2026-06-27 08:00:00','2026-06-27 08:00:00'),
(265,62,18,'Cách ưu tiên chức năng bằng impact effort','video','Nội dung video: Cách ưu tiên chức năng bằng impact effort. File seed theo course_folder mvp-web-product.','/videos/mvp-web-product/mvp-web-product-10-cach-uu-tien-chuc-nang-bang-impact-effort.mp4','local',NULL,600,0,'published',5,'2026-06-27 08:00:00','2026-06-27 08:00:00'),
(266,63,18,'Cách tạo landing page kiểm tra nhu cầu','video','Nội dung video: Cách tạo landing page kiểm tra nhu cầu. File seed theo course_folder mvp-web-product.','/videos/mvp-web-product/mvp-web-product-11-cach-tao-landing-page-kiem-tra-nhu-cau.mp4','local',NULL,600,0,'published',1,'2026-06-27 08:00:00','2026-06-27 08:00:00'),
(267,63,18,'Cách làm prototype trước khi code toàn bộ','video','Nội dung video: Cách làm prototype trước khi code toàn bộ. File seed theo course_folder mvp-web-product.','/videos/mvp-web-product/mvp-web-product-12-cach-lam-prototype-truoc-khi-code-toan-bo.mp4','local',NULL,600,0,'published',2,'2026-06-27 08:00:00','2026-06-27 08:00:00'),
(268,63,18,'Cách dùng analytics để đo hành vi người dùng','video','Nội dung video: Cách dùng analytics để đo hành vi người dùng. File seed theo course_folder mvp-web-product.','/videos/mvp-web-product/mvp-web-product-13-cach-dung-analytics-de-do-hanh-vi-nguoi-dung.mp4','local',NULL,600,0,'published',3,'2026-06-27 08:00:00','2026-06-27 08:00:00'),
(269,63,18,'Cách nhận feedback từ người dùng đầu tiên','video','Nội dung video: Cách nhận feedback từ người dùng đầu tiên. File seed theo course_folder mvp-web-product.','/videos/mvp-web-product/mvp-web-product-14-cach-nhan-feedback-tu-nguoi-dung-dau-tien.mp4','local',NULL,600,0,'published',4,'2026-06-27 08:00:00','2026-06-27 08:00:00'),
(270,63,18,'Cách quyết định giữ sửa hay bỏ tính năng','video','Nội dung video: Cách quyết định giữ sửa hay bỏ tính năng. File seed theo course_folder mvp-web-product.','/videos/mvp-web-product/mvp-web-product-15-cach-quyet-dinh-giu-sua-hay-bo-tinh-nang.mp4','local',NULL,600,0,'published',5,'2026-06-27 08:00:00','2026-06-27 08:00:00'),
(271,64,18,'Cách trình bày MVP trong portfolio','video','Nội dung video: Cách trình bày MVP trong portfolio. File seed theo course_folder mvp-web-product.','/videos/mvp-web-product/mvp-web-product-16-cach-trinh-bay-mvp-trong-portfolio.mp4','local',NULL,600,0,'published',1,'2026-06-27 08:00:00','2026-06-27 08:00:00'),
(272,64,18,'Cách viết case study sản phẩm cá nhân','video','Nội dung video: Cách viết case study sản phẩm cá nhân. File seed theo course_folder mvp-web-product.','/videos/mvp-web-product/mvp-web-product-17-cach-viet-case-study-san-pham-ca-nhan.mp4','local',NULL,600,0,'published',2,'2026-06-27 08:00:00','2026-06-27 08:00:00'),
(273,64,18,'Cách chuẩn bị demo MVP cho nhà tuyển dụng','video','Nội dung video: Cách chuẩn bị demo MVP cho nhà tuyển dụng. File seed theo course_folder mvp-web-product.','/videos/mvp-web-product/mvp-web-product-18-cach-chuan-bi-demo-mvp-cho-nha-tuyen-dung.mp4','local',NULL,600,0,'published',3,'2026-06-27 08:00:00','2026-06-27 08:00:00'),
(274,64,18,'Cách phát triển MVP thành SaaS nhỏ','video','Nội dung video: Cách phát triển MVP thành SaaS nhỏ. File seed theo course_folder mvp-web-product.','/videos/mvp-web-product/mvp-web-product-19-cach-phat-trien-mvp-thanh-saas-nho.mp4','local',NULL,600,0,'published',4,'2026-06-27 08:00:00','2026-06-27 08:00:00'),
(275,64,18,'Tổng kết khóa học xây MVP sản phẩm Web','video','Nội dung video: Tổng kết khóa học xây MVP sản phẩm Web. File seed theo course_folder mvp-web-product.','/videos/mvp-web-product/mvp-web-product-20-tong-ket-khoa-hoc-xay-mvp-san-pham-web.mp4','local',NULL,600,0,'published',5,'2026-06-27 08:00:00','2026-06-27 08:00:00'),
(276,65,19,'Vì sao lập trình viên web cần giao tiếp tốt?','video','Nội dung video: Vì sao lập trình viên web cần giao tiếp tốt?. File seed theo course_folder soft-communication-it.','/videos/soft-communication-it/soft-communication-it-01-vi-sao-lap-trinh-vien-web-can-giao-tiep-tot.mp4','local',NULL,600,1,'published',1,'2026-06-27 08:00:00','2026-06-27 08:00:00'),
(277,65,19,'Cách trình bày vấn đề kỹ thuật cho người không chuyên','video','Nội dung video: Cách trình bày vấn đề kỹ thuật cho người không chuyên. File seed theo course_folder soft-communication-it.','/videos/soft-communication-it/soft-communication-it-02-cach-trinh-bay-van-de-ky-thuat-cho-nguoi-khong-chuyen.mp4','local',NULL,600,0,'published',2,'2026-06-27 08:00:00','2026-06-27 08:00:00'),
(278,65,19,'Cách hỏi khi gặp lỗi mà không làm mất thời gian của team','video','Nội dung video: Cách hỏi khi gặp lỗi mà không làm mất thời gian của team. File seed theo course_folder soft-communication-it.','/videos/soft-communication-it/soft-communication-it-03-cach-hoi-khi-gap-loi-ma-khong-lam-mat-thoi-gian-cua-team.mp4','local',NULL,600,0,'published',3,'2026-06-27 08:00:00','2026-06-27 08:00:00'),
(279,65,19,'Cách báo cáo tiến độ task rõ ràng','video','Nội dung video: Cách báo cáo tiến độ task rõ ràng. File seed theo course_folder soft-communication-it.','/videos/soft-communication-it/soft-communication-it-04-cach-bao-cao-tien-do-task-ro-rang.mp4','local',NULL,600,0,'published',4,'2026-06-27 08:00:00','2026-06-27 08:00:00'),
(280,65,19,'Cách ghi chú quyết định kỹ thuật sau cuộc họp','video','Nội dung video: Cách ghi chú quyết định kỹ thuật sau cuộc họp. File seed theo course_folder soft-communication-it.','/videos/soft-communication-it/soft-communication-it-05-cach-ghi-chu-quyet-dinh-ky-thuat-sau-cuoc-hop.mp4','local',NULL,600,0,'published',5,'2026-06-27 08:00:00','2026-06-27 08:00:00'),
(281,66,19,'Cách thống nhất API contract giữa Backend và Frontend','video','Nội dung video: Cách thống nhất API contract giữa Backend và Frontend. File seed theo course_folder soft-communication-it.','/videos/soft-communication-it/soft-communication-it-06-cach-thong-nhat-api-contract-giua-backend-va-frontend.mp4','local',NULL,600,0,'published',1,'2026-06-27 08:00:00','2026-06-27 08:00:00'),
(282,66,19,'Cách trao đổi khi response API thay đổi','video','Nội dung video: Cách trao đổi khi response API thay đổi. File seed theo course_folder soft-communication-it.','/videos/soft-communication-it/soft-communication-it-07-cach-trao-doi-khi-response-api-thay-doi.mp4','local',NULL,600,0,'published',2,'2026-06-27 08:00:00','2026-06-27 08:00:00'),
(283,66,19,'Cách mô tả bug để teammate tái hiện được','video','Nội dung video: Cách mô tả bug để teammate tái hiện được. File seed theo course_folder soft-communication-it.','/videos/soft-communication-it/soft-communication-it-08-cach-mo-ta-bug-de-teammate-tai-hien-duoc.mp4','local',NULL,600,0,'published',3,'2026-06-27 08:00:00','2026-06-27 08:00:00'),
(284,66,19,'Cách feedback UI UX mà không gây căng thẳng','video','Nội dung video: Cách feedback UI UX mà không gây căng thẳng. File seed theo course_folder soft-communication-it.','/videos/soft-communication-it/soft-communication-it-09-cach-feedback-ui-ux-ma-khong-gay-cang-thang.mp4','local',NULL,600,0,'published',4,'2026-06-27 08:00:00','2026-06-27 08:00:00'),
(285,66,19,'Cách xử lý hiểu lầm khi chia module đồ án','video','Nội dung video: Cách xử lý hiểu lầm khi chia module đồ án. File seed theo course_folder soft-communication-it.','/videos/soft-communication-it/soft-communication-it-10-cach-xu-ly-hieu-lam-khi-chia-module-do-an.mp4','local',NULL,600,0,'published',5,'2026-06-27 08:00:00','2026-06-27 08:00:00'),
(286,67,19,'Cách trình bày demo sản phẩm trong 5 phút','video','Nội dung video: Cách trình bày demo sản phẩm trong 5 phút. File seed theo course_folder soft-communication-it.','/videos/soft-communication-it/soft-communication-it-11-cach-trinh-bay-demo-san-pham-trong-5-phut.mp4','local',NULL,600,0,'published',1,'2026-06-27 08:00:00','2026-06-27 08:00:00'),
(287,67,19,'Cách giải thích tính năng web bằng ngôn ngữ nghiệp vụ','video','Nội dung video: Cách giải thích tính năng web bằng ngôn ngữ nghiệp vụ. File seed theo course_folder soft-communication-it.','/videos/soft-communication-it/soft-communication-it-12-cach-giai-thich-tinh-nang-web-bang-ngon-ngu-nghiep-vu.mp4','local',NULL,600,0,'published',2,'2026-06-27 08:00:00','2026-06-27 08:00:00'),
(288,67,19,'Cách trả lời khi không biết câu hỏi kỹ thuật','video','Nội dung video: Cách trả lời khi không biết câu hỏi kỹ thuật. File seed theo course_folder soft-communication-it.','/videos/soft-communication-it/soft-communication-it-13-cach-tra-loi-khi-khong-biet-cau-hoi-ky-thuat.mp4','local',NULL,600,0,'published',3,'2026-06-27 08:00:00','2026-06-27 08:00:00'),
(289,67,19,'Cách hỏi lại yêu cầu để tránh làm sai chức năng','video','Nội dung video: Cách hỏi lại yêu cầu để tránh làm sai chức năng. File seed theo course_folder soft-communication-it.','/videos/soft-communication-it/soft-communication-it-14-cach-hoi-lai-yeu-cau-de-tranh-lam-sai-chuc-nang.mp4','local',NULL,600,0,'published',4,'2026-06-27 08:00:00','2026-06-27 08:00:00'),
(290,67,19,'Cách viết email hoặc tin nhắn chuyên nghiệp trong dự án','video','Nội dung video: Cách viết email hoặc tin nhắn chuyên nghiệp trong dự án. File seed theo course_folder soft-communication-it.','/videos/soft-communication-it/soft-communication-it-15-cach-viet-email-hoac-tin-nhan-chuyen-nghiep-trong-du-an.mp4','local',NULL,600,0,'published',5,'2026-06-27 08:00:00','2026-06-27 08:00:00'),
(291,68,19,'Mẫu daily update cho sinh viên làm đồ án','video','Nội dung video: Mẫu daily update cho sinh viên làm đồ án. File seed theo course_folder soft-communication-it.','/videos/soft-communication-it/soft-communication-it-16-mau-daily-update-cho-sinh-vien-lam-do-an.mp4','local',NULL,600,0,'published',1,'2026-06-27 08:00:00','2026-06-27 08:00:00'),
(292,68,19,'Mẫu báo lỗi API cho team Backend','video','Nội dung video: Mẫu báo lỗi API cho team Backend. File seed theo course_folder soft-communication-it.','/videos/soft-communication-it/soft-communication-it-17-mau-bao-loi-api-cho-team-backend.mp4','local',NULL,600,0,'published',2,'2026-06-27 08:00:00','2026-06-27 08:00:00'),
(293,68,19,'Mẫu mô tả task GitHub Issue rõ ràng','video','Nội dung video: Mẫu mô tả task GitHub Issue rõ ràng. File seed theo course_folder soft-communication-it.','/videos/soft-communication-it/soft-communication-it-18-mau-mo-ta-task-github-issue-ro-rang.mp4','local',NULL,600,0,'published',3,'2026-06-27 08:00:00','2026-06-27 08:00:00'),
(294,68,19,'Checklist giao tiếp trước ngày demo','video','Nội dung video: Checklist giao tiếp trước ngày demo. File seed theo course_folder soft-communication-it.','/videos/soft-communication-it/soft-communication-it-19-checklist-giao-tiep-truoc-ngay-demo.mp4','local',NULL,600,0,'published',4,'2026-06-27 08:00:00','2026-06-27 08:00:00'),
(295,68,19,'Tổng kết khóa học giao tiếp trong team IT','video','Nội dung video: Tổng kết khóa học giao tiếp trong team IT. File seed theo course_folder soft-communication-it.','/videos/soft-communication-it/soft-communication-it-20-tong-ket-khoa-hoc-giao-tiep-trong-team-it.mp4','local',NULL,600,0,'published',5,'2026-06-27 08:00:00','2026-06-27 08:00:00'),
(296,69,20,'Teamwork trong dự án web khác làm bài cá nhân thế nào?','video','Nội dung video: Teamwork trong dự án web khác làm bài cá nhân thế nào?. File seed theo course_folder teamwork-agile-web.','/videos/teamwork-agile-web/teamwork-agile-web-01-teamwork-trong-du-an-web-khac-lam-bai-ca-nhan-the-nao.mp4','local',NULL,600,1,'published',1,'2026-06-27 08:00:00','2026-06-27 08:00:00'),
(297,69,20,'Vai trò Backend Frontend Tester UI UX trong nhóm','video','Nội dung video: Vai trò Backend Frontend Tester UI UX trong nhóm. File seed theo course_folder teamwork-agile-web.','/videos/teamwork-agile-web/teamwork-agile-web-02-vai-tro-backend-frontend-tester-ui-ux-trong-nhom.mp4','local',NULL,600,0,'published',2,'2026-06-27 08:00:00','2026-06-27 08:00:00'),
(298,69,20,'Cách chia module để tránh chồng chéo','video','Nội dung video: Cách chia module để tránh chồng chéo. File seed theo course_folder teamwork-agile-web.','/videos/teamwork-agile-web/teamwork-agile-web-03-cach-chia-module-de-tranh-chong-cheo.mp4','local',NULL,600,0,'published',3,'2026-06-27 08:00:00','2026-06-27 08:00:00'),
(299,69,20,'Cách thống nhất deadline và phạm vi đồ án','video','Nội dung video: Cách thống nhất deadline và phạm vi đồ án. File seed theo course_folder teamwork-agile-web.','/videos/teamwork-agile-web/teamwork-agile-web-04-cach-thong-nhat-deadline-va-pham-vi-do-an.mp4','local',NULL,600,0,'published',4,'2026-06-27 08:00:00','2026-06-27 08:00:00'),
(300,69,20,'Những lỗi teamwork khiến đồ án bị trễ','video','Nội dung video: Những lỗi teamwork khiến đồ án bị trễ. File seed theo course_folder teamwork-agile-web.','/videos/teamwork-agile-web/teamwork-agile-web-05-nhung-loi-teamwork-khien-do-an-bi-tre.mp4','local',NULL,600,0,'published',5,'2026-06-27 08:00:00','2026-06-27 08:00:00'),
(301,70,20,'Agile là gì theo cách dễ hiểu?','video','Nội dung video: Agile là gì theo cách dễ hiểu?. File seed theo course_folder teamwork-agile-web.','/videos/teamwork-agile-web/teamwork-agile-web-06-agile-la-gi-theo-cach-de-hieu.mp4','local',NULL,600,0,'published',1,'2026-06-27 08:00:00','2026-06-27 08:00:00'),
(302,70,20,'Sprint là gì và áp dụng vào đồ án ra sao?','video','Nội dung video: Sprint là gì và áp dụng vào đồ án ra sao?. File seed theo course_folder teamwork-agile-web.','/videos/teamwork-agile-web/teamwork-agile-web-07-sprint-la-gi-va-ap-dung-vao-do-an-ra-sao.mp4','local',NULL,600,0,'published',2,'2026-06-27 08:00:00','2026-06-27 08:00:00'),
(303,70,20,'Daily meeting nên nói gì cho đúng trọng tâm?','video','Nội dung video: Daily meeting nên nói gì cho đúng trọng tâm?. File seed theo course_folder teamwork-agile-web.','/videos/teamwork-agile-web/teamwork-agile-web-08-daily-meeting-nen-noi-gi-cho-dung-trong-tam.mp4','local',NULL,600,0,'published',3,'2026-06-27 08:00:00','2026-06-27 08:00:00'),
(304,70,20,'Sprint planning cho nhóm làm MindHub','video','Nội dung video: Sprint planning cho nhóm làm MindHub. File seed theo course_folder teamwork-agile-web.','/videos/teamwork-agile-web/teamwork-agile-web-09-sprint-planning-cho-nhom-lam-mindhub.mp4','local',NULL,600,0,'published',4,'2026-06-27 08:00:00','2026-06-27 08:00:00'),
(305,70,20,'Retrospective sau mỗi sprint để cải thiện team','video','Nội dung video: Retrospective sau mỗi sprint để cải thiện team. File seed theo course_folder teamwork-agile-web.','/videos/teamwork-agile-web/teamwork-agile-web-10-retrospective-sau-moi-sprint-de-cai-thien-team.mp4','local',NULL,600,0,'published',5,'2026-06-27 08:00:00','2026-06-27 08:00:00'),
(306,71,20,'Dùng GitHub Issues để chia task nhóm','video','Nội dung video: Dùng GitHub Issues để chia task nhóm. File seed theo course_folder teamwork-agile-web.','/videos/teamwork-agile-web/teamwork-agile-web-11-dung-github-issues-de-chia-task-nhom.mp4','local',NULL,600,0,'published',1,'2026-06-27 08:00:00','2026-06-27 08:00:00'),
(307,71,20,'Cách viết task có acceptance criteria','video','Nội dung video: Cách viết task có acceptance criteria. File seed theo course_folder teamwork-agile-web.','/videos/teamwork-agile-web/teamwork-agile-web-12-cach-viet-task-co-acceptance-criteria.mp4','local',NULL,600,0,'published',2,'2026-06-27 08:00:00','2026-06-27 08:00:00'),
(308,71,20,'Cách theo dõi tiến độ không gây áp lực độc hại','video','Nội dung video: Cách theo dõi tiến độ không gây áp lực độc hại. File seed theo course_folder teamwork-agile-web.','/videos/teamwork-agile-web/teamwork-agile-web-13-cach-theo-doi-tien-do-khong-gay-ap-luc-doc-hai.mp4','local',NULL,600,0,'published',3,'2026-06-27 08:00:00','2026-06-27 08:00:00'),
(309,71,20,'Cách xử lý thành viên chậm task','video','Nội dung video: Cách xử lý thành viên chậm task. File seed theo course_folder teamwork-agile-web.','/videos/teamwork-agile-web/teamwork-agile-web-14-cach-xu-ly-thanh-vien-cham-task.mp4','local',NULL,600,0,'published',4,'2026-06-27 08:00:00','2026-06-27 08:00:00'),
(310,71,20,'Cách chốt scope khi gần deadline','video','Nội dung video: Cách chốt scope khi gần deadline. File seed theo course_folder teamwork-agile-web.','/videos/teamwork-agile-web/teamwork-agile-web-15-cach-chot-scope-khi-gan-deadline.mp4','local',NULL,600,0,'published',5,'2026-06-27 08:00:00','2026-06-27 08:00:00'),
(311,72,20,'Checklist tổng hợp code trước ngày demo','video','Nội dung video: Checklist tổng hợp code trước ngày demo. File seed theo course_folder teamwork-agile-web.','/videos/teamwork-agile-web/teamwork-agile-web-16-checklist-tong-hop-code-truoc-ngay-demo.mp4','local',NULL,600,0,'published',1,'2026-06-27 08:00:00','2026-06-27 08:00:00'),
(312,72,20,'Cách phân vai khi thuyết trình sản phẩm','video','Nội dung video: Cách phân vai khi thuyết trình sản phẩm. File seed theo course_folder teamwork-agile-web.','/videos/teamwork-agile-web/teamwork-agile-web-17-cach-phan-vai-khi-thuyet-trinh-san-pham.mp4','local',NULL,600,0,'published',2,'2026-06-27 08:00:00','2026-06-27 08:00:00'),
(313,72,20,'Cách chuẩn bị câu hỏi phản biện theo module','video','Nội dung video: Cách chuẩn bị câu hỏi phản biện theo module. File seed theo course_folder teamwork-agile-web.','/videos/teamwork-agile-web/teamwork-agile-web-18-cach-chuan-bi-cau-hoi-phan-bien-theo-module.mp4','local',NULL,600,0,'published',3,'2026-06-27 08:00:00','2026-06-27 08:00:00'),
(314,72,20,'Cách xử lý sự cố khi demo bị lỗi','video','Nội dung video: Cách xử lý sự cố khi demo bị lỗi. File seed theo course_folder teamwork-agile-web.','/videos/teamwork-agile-web/teamwork-agile-web-19-cach-xu-ly-su-co-khi-demo-bi-loi.mp4','local',NULL,600,0,'published',4,'2026-06-27 08:00:00','2026-06-27 08:00:00'),
(315,72,20,'Tổng kết khóa học Teamwork Agile cho dự án Web','video','Nội dung video: Tổng kết khóa học Teamwork Agile cho dự án Web. File seed theo course_folder teamwork-agile-web.','/videos/teamwork-agile-web/teamwork-agile-web-20-tong-ket-khoa-hoc-teamwork-agile-cho-du-an-web.mp4','local',NULL,600,0,'published',5,'2026-06-27 08:00:00','2026-06-27 08:00:00'),
(316,73,21,'Vì sao cần biết trình bày project web?','video','Nội dung video: Vì sao cần biết trình bày project web?. File seed theo course_folder present-web-project.','/videos/present-web-project/present-web-project-01-vi-sao-can-biet-trinh-bay-project-web.mp4','local',NULL,600,1,'published',1,'2026-06-27 08:00:00','2026-06-27 08:00:00'),
(317,73,21,'Cách giới thiệu project trong 60 giây','video','Nội dung video: Cách giới thiệu project trong 60 giây. File seed theo course_folder present-web-project.','/videos/present-web-project/present-web-project-02-cach-gioi-thieu-project-trong-60-giay.mp4','local',NULL,600,0,'published',2,'2026-06-27 08:00:00','2026-06-27 08:00:00'),
(318,73,21,'Cách nói rõ vấn đề sản phẩm giải quyết','video','Nội dung video: Cách nói rõ vấn đề sản phẩm giải quyết. File seed theo course_folder present-web-project.','/videos/present-web-project/present-web-project-03-cach-noi-ro-van-de-san-pham-giai-quyet.mp4','local',NULL,600,0,'published',3,'2026-06-27 08:00:00','2026-06-27 08:00:00'),
(319,73,21,'Cách trình bày vai trò cá nhân trong team','video','Nội dung video: Cách trình bày vai trò cá nhân trong team. File seed theo course_folder present-web-project.','/videos/present-web-project/present-web-project-04-cach-trinh-bay-vai-tro-ca-nhan-trong-team.mp4','local',NULL,600,0,'published',4,'2026-06-27 08:00:00','2026-06-27 08:00:00'),
(320,73,21,'Cách tránh phóng đại phần mình không làm','video','Nội dung video: Cách tránh phóng đại phần mình không làm. File seed theo course_folder present-web-project.','/videos/present-web-project/present-web-project-05-cach-tranh-phong-dai-phan-minh-khong-lam.mp4','local',NULL,600,0,'published',5,'2026-06-27 08:00:00','2026-06-27 08:00:00'),
(321,74,21,'Cách trình bày kiến trúc React Laravel MySQL','video','Nội dung video: Cách trình bày kiến trúc React Laravel MySQL. File seed theo course_folder present-web-project.','/videos/present-web-project/present-web-project-06-cach-trinh-bay-kien-truc-react-laravel-mysql.mp4','local',NULL,600,0,'published',1,'2026-06-27 08:00:00','2026-06-27 08:00:00'),
(322,74,21,'Cách giải thích API flow cho người phỏng vấn','video','Nội dung video: Cách giải thích API flow cho người phỏng vấn. File seed theo course_folder present-web-project.','/videos/present-web-project/present-web-project-07-cach-giai-thich-api-flow-cho-nguoi-phong-van.mp4','local',NULL,600,0,'published',2,'2026-06-27 08:00:00','2026-06-27 08:00:00'),
(323,74,21,'Cách nói về database và quan hệ bảng','video','Nội dung video: Cách nói về database và quan hệ bảng. File seed theo course_folder present-web-project.','/videos/present-web-project/present-web-project-08-cach-noi-ve-database-va-quan-he-bang.mp4','local',NULL,600,0,'published',3,'2026-06-27 08:00:00','2026-06-27 08:00:00'),
(324,74,21,'Cách trình bày module payment learning auth','video','Nội dung video: Cách trình bày module payment learning auth. File seed theo course_folder present-web-project.','/videos/present-web-project/present-web-project-09-cach-trinh-bay-module-payment-learning-auth.mp4','local',NULL,600,0,'published',4,'2026-06-27 08:00:00','2026-06-27 08:00:00'),
(325,74,21,'Cách nói về bảo mật và phân quyền trong project','video','Nội dung video: Cách nói về bảo mật và phân quyền trong project. File seed theo course_folder present-web-project.','/videos/present-web-project/present-web-project-10-cach-noi-ve-bao-mat-va-phan-quyen-trong-project.mp4','local',NULL,600,0,'published',5,'2026-06-27 08:00:00','2026-06-27 08:00:00'),
(326,75,21,'Cách chuẩn bị demo flow chính của web app','video','Nội dung video: Cách chuẩn bị demo flow chính của web app. File seed theo course_folder present-web-project.','/videos/present-web-project/present-web-project-11-cach-chuan-bi-demo-flow-chinh-cua-web-app.mp4','local',NULL,600,0,'published',1,'2026-06-27 08:00:00','2026-06-27 08:00:00'),
(327,75,21,'Cách xử lý khi demo bị lỗi nhẹ','video','Nội dung video: Cách xử lý khi demo bị lỗi nhẹ. File seed theo course_folder present-web-project.','/videos/present-web-project/present-web-project-12-cach-xu-ly-khi-demo-bi-loi-nhe.mp4','local',NULL,600,0,'published',2,'2026-06-27 08:00:00','2026-06-27 08:00:00'),
(328,75,21,'Cách dùng dữ liệu demo để kể câu chuyện sản phẩm','video','Nội dung video: Cách dùng dữ liệu demo để kể câu chuyện sản phẩm. File seed theo course_folder present-web-project.','/videos/present-web-project/present-web-project-13-cach-dung-du-lieu-demo-de-ke-cau-chuyen-san-pham.mp4','local',NULL,600,0,'published',3,'2026-06-27 08:00:00','2026-06-27 08:00:00'),
(329,75,21,'Cách trình bày GitHub README và tài liệu','video','Nội dung video: Cách trình bày GitHub README và tài liệu. File seed theo course_folder present-web-project.','/videos/present-web-project/present-web-project-14-cach-trinh-bay-github-readme-va-tai-lieu.mp4','local',NULL,600,0,'published',4,'2026-06-27 08:00:00','2026-06-27 08:00:00'),
(330,75,21,'Cách kết nối project với vị trí ứng tuyển','video','Nội dung video: Cách kết nối project với vị trí ứng tuyển. File seed theo course_folder present-web-project.','/videos/present-web-project/present-web-project-15-cach-ket-noi-project-voi-vi-tri-ung-tuyen.mp4','local',NULL,600,0,'published',5,'2026-06-27 08:00:00','2026-06-27 08:00:00'),
(331,76,21,'Câu hỏi thường gặp về đồ án web','video','Nội dung video: Câu hỏi thường gặp về đồ án web. File seed theo course_folder present-web-project.','/videos/present-web-project/present-web-project-16-cau-hoi-thuong-gap-ve-do-an-web.mp4','local',NULL,600,0,'published',1,'2026-06-27 08:00:00','2026-06-27 08:00:00'),
(332,76,21,'Cách trả lời câu hỏi vì sao chọn công nghệ này','video','Nội dung video: Cách trả lời câu hỏi vì sao chọn công nghệ này. File seed theo course_folder present-web-project.','/videos/present-web-project/present-web-project-17-cach-tra-loi-cau-hoi-vi-sao-chon-cong-nghe-nay.mp4','local',NULL,600,0,'published',2,'2026-06-27 08:00:00','2026-06-27 08:00:00'),
(333,76,21,'Cách trả lời khi bị hỏi sâu phần chưa rõ','video','Nội dung video: Cách trả lời khi bị hỏi sâu phần chưa rõ. File seed theo course_folder present-web-project.','/videos/present-web-project/present-web-project-18-cach-tra-loi-khi-bi-hoi-sau-phan-chua-ro.mp4','local',NULL,600,0,'published',3,'2026-06-27 08:00:00','2026-06-27 08:00:00'),
(334,76,21,'Checklist chuẩn bị trước buổi phỏng vấn','video','Nội dung video: Checklist chuẩn bị trước buổi phỏng vấn. File seed theo course_folder present-web-project.','/videos/present-web-project/present-web-project-19-checklist-chuan-bi-truoc-buoi-phong-van.mp4','local',NULL,600,0,'published',4,'2026-06-27 08:00:00','2026-06-27 08:00:00'),
(335,76,21,'Tổng kết khóa học trình bày project Web','video','Nội dung video: Tổng kết khóa học trình bày project Web. File seed theo course_folder present-web-project.','/videos/present-web-project/present-web-project-20-tong-ket-khoa-hoc-trinh-bay-project-web.mp4','local',NULL,600,0,'published',5,'2026-06-27 08:00:00','2026-06-27 08:00:00'),
(336,77,22,'Vì sao không nên lao vào code khi chưa hiểu yêu cầu?','video','Nội dung video: Vì sao không nên lao vào code khi chưa hiểu yêu cầu?. File seed theo course_folder problem-solving-webdev.','/videos/problem-solving-webdev/problem-solving-webdev-01-vi-sao-khong-nen-lao-vao-code-khi-chua-hieu-yeu-cau.mp4','local',NULL,600,1,'published',1,'2026-06-27 08:00:00','2026-06-27 08:00:00'),
(337,77,22,'Cách bóc tách yêu cầu thành user flow','video','Nội dung video: Cách bóc tách yêu cầu thành user flow. File seed theo course_folder problem-solving-webdev.','/videos/problem-solving-webdev/problem-solving-webdev-02-cach-boc-tach-yeu-cau-thanh-user-flow.mp4','local',NULL,600,0,'published',2,'2026-06-27 08:00:00','2026-06-27 08:00:00'),
(338,77,22,'Cách đặt câu hỏi để làm rõ nghiệp vụ','video','Nội dung video: Cách đặt câu hỏi để làm rõ nghiệp vụ. File seed theo course_folder problem-solving-webdev.','/videos/problem-solving-webdev/problem-solving-webdev-03-cach-dat-cau-hoi-de-lam-ro-nghiep-vu.mp4','local',NULL,600,0,'published',3,'2026-06-27 08:00:00','2026-06-27 08:00:00'),
(339,77,22,'Cách xác định input output của một chức năng','video','Nội dung video: Cách xác định input output của một chức năng. File seed theo course_folder problem-solving-webdev.','/videos/problem-solving-webdev/problem-solving-webdev-04-cach-xac-dinh-input-output-cua-mot-chuc-nang.mp4','local',NULL,600,0,'published',4,'2026-06-27 08:00:00','2026-06-27 08:00:00'),
(340,77,22,'Cách viết checklist trước khi bắt đầu task','video','Nội dung video: Cách viết checklist trước khi bắt đầu task. File seed theo course_folder problem-solving-webdev.','/videos/problem-solving-webdev/problem-solving-webdev-05-cach-viet-checklist-truoc-khi-bat-dau-task.mp4','local',NULL,600,0,'published',5,'2026-06-27 08:00:00','2026-06-27 08:00:00'),
(341,78,22,'Debug là gì và vì sao người mới hay sửa mò?','video','Nội dung video: Debug là gì và vì sao người mới hay sửa mò?. File seed theo course_folder problem-solving-webdev.','/videos/problem-solving-webdev/problem-solving-webdev-06-debug-la-gi-va-vi-sao-nguoi-moi-hay-sua-mo.mp4','local',NULL,600,0,'published',1,'2026-06-27 08:00:00','2026-06-27 08:00:00'),
(342,78,22,'Cách tái hiện lỗi trước khi sửa','video','Nội dung video: Cách tái hiện lỗi trước khi sửa. File seed theo course_folder problem-solving-webdev.','/videos/problem-solving-webdev/problem-solving-webdev-07-cach-tai-hien-loi-truoc-khi-sua.mp4','local',NULL,600,0,'published',2,'2026-06-27 08:00:00','2026-06-27 08:00:00'),
(343,78,22,'Cách đọc log và khoanh vùng nguyên nhân','video','Nội dung video: Cách đọc log và khoanh vùng nguyên nhân. File seed theo course_folder problem-solving-webdev.','/videos/problem-solving-webdev/problem-solving-webdev-08-cach-doc-log-va-khoanh-vung-nguyen-nhan.mp4','local',NULL,600,0,'published',3,'2026-06-27 08:00:00','2026-06-27 08:00:00'),
(344,78,22,'Cách phân biệt lỗi frontend backend database','video','Nội dung video: Cách phân biệt lỗi frontend backend database. File seed theo course_folder problem-solving-webdev.','/videos/problem-solving-webdev/problem-solving-webdev-09-cach-phan-biet-loi-frontend-backend-database.mp4','local',NULL,600,0,'published',4,'2026-06-27 08:00:00','2026-06-27 08:00:00'),
(345,78,22,'Cách ghi lại lỗi đã sửa để học nhanh hơn','video','Nội dung video: Cách ghi lại lỗi đã sửa để học nhanh hơn. File seed theo course_folder problem-solving-webdev.','/videos/problem-solving-webdev/problem-solving-webdev-10-cach-ghi-lai-loi-da-sua-de-hoc-nhanh-hon.mp4','local',NULL,600,0,'published',5,'2026-06-27 08:00:00','2026-06-27 08:00:00'),
(346,79,22,'Cách chọn giải pháp đơn giản trước khi tối ưu','video','Nội dung video: Cách chọn giải pháp đơn giản trước khi tối ưu. File seed theo course_folder problem-solving-webdev.','/videos/problem-solving-webdev/problem-solving-webdev-11-cach-chon-giai-phap-don-gian-truoc-khi-toi-uu.mp4','local',NULL,600,0,'published',1,'2026-06-27 08:00:00','2026-06-27 08:00:00'),
(347,79,22,'Cách so sánh nhiều hướng xử lý một API','video','Nội dung video: Cách so sánh nhiều hướng xử lý một API. File seed theo course_folder problem-solving-webdev.','/videos/problem-solving-webdev/problem-solving-webdev-12-cach-so-sanh-nhieu-huong-xu-ly-mot-api.mp4','local',NULL,600,0,'published',2,'2026-06-27 08:00:00','2026-06-27 08:00:00'),
(348,79,22,'Cách nghĩ về edge case trong web app','video','Nội dung video: Cách nghĩ về edge case trong web app. File seed theo course_folder problem-solving-webdev.','/videos/problem-solving-webdev/problem-solving-webdev-13-cach-nghi-ve-edge-case-trong-web-app.mp4','local',NULL,600,0,'published',3,'2026-06-27 08:00:00','2026-06-27 08:00:00'),
(349,79,22,'Cách dùng flowchart để mô tả nghiệp vụ','video','Nội dung video: Cách dùng flowchart để mô tả nghiệp vụ. File seed theo course_folder problem-solving-webdev.','/videos/problem-solving-webdev/problem-solving-webdev-14-cach-dung-flowchart-de-mo-ta-nghiep-vu.mp4','local',NULL,600,0,'published',4,'2026-06-27 08:00:00','2026-06-27 08:00:00'),
(350,79,22,'Cách tránh over-engineering trong đồ án','video','Nội dung video: Cách tránh over-engineering trong đồ án. File seed theo course_folder problem-solving-webdev.','/videos/problem-solving-webdev/problem-solving-webdev-15-cach-tranh-over-engineering-trong-do-an.mp4','local',NULL,600,0,'published',5,'2026-06-27 08:00:00','2026-06-27 08:00:00'),
(351,80,22,'Cách trình bày suy nghĩ khi gặp câu hỏi khó','video','Nội dung video: Cách trình bày suy nghĩ khi gặp câu hỏi khó. File seed theo course_folder problem-solving-webdev.','/videos/problem-solving-webdev/problem-solving-webdev-16-cach-trinh-bay-suy-nghi-khi-gap-cau-hoi-kho.mp4','local',NULL,600,0,'published',1,'2026-06-27 08:00:00','2026-06-27 08:00:00'),
(352,80,22,'Cách nói về bug đã từng xử lý trong project','video','Nội dung video: Cách nói về bug đã từng xử lý trong project. File seed theo course_folder problem-solving-webdev.','/videos/problem-solving-webdev/problem-solving-webdev-17-cach-noi-ve-bug-da-tung-xu-ly-trong-project.mp4','local',NULL,600,0,'published',2,'2026-06-27 08:00:00','2026-06-27 08:00:00'),
(353,80,22,'Cách giải thích quyết định kỹ thuật trong đồ án','video','Nội dung video: Cách giải thích quyết định kỹ thuật trong đồ án. File seed theo course_folder problem-solving-webdev.','/videos/problem-solving-webdev/problem-solving-webdev-18-cach-giai-thich-quyet-dinh-ky-thuat-trong-do-an.mp4','local',NULL,600,0,'published',3,'2026-06-27 08:00:00','2026-06-27 08:00:00'),
(354,80,22,'Checklist problem solving trước phỏng vấn','video','Nội dung video: Checklist problem solving trước phỏng vấn. File seed theo course_folder problem-solving-webdev.','/videos/problem-solving-webdev/problem-solving-webdev-19-checklist-problem-solving-truoc-phong-van.mp4','local',NULL,600,0,'published',4,'2026-06-27 08:00:00','2026-06-27 08:00:00'),
(355,80,22,'Tổng kết khóa học tư duy giải quyết vấn đề','video','Nội dung video: Tổng kết khóa học tư duy giải quyết vấn đề. File seed theo course_folder problem-solving-webdev.','/videos/problem-solving-webdev/problem-solving-webdev-20-tong-ket-khoa-hoc-tu-duy-giai-quyet-van-de.mp4','local',NULL,600,0,'published',5,'2026-06-27 08:00:00','2026-06-27 08:00:00'),
(356,81,23,'React là gì và vì sao dùng cho E-learning?','video','Nội dung video: React là gì và vì sao dùng cho E-learning?. File seed theo course_folder react-elearning.','/videos/react-elearning/react-elearning-01-react-la-gi-va-vi-sao-dung-cho-e-learning.mp4','local',NULL,600,1,'published',1,'2026-06-27 08:00:00','2026-06-27 08:00:00'),
(357,81,23,'Cài đặt React Vite và cấu trúc project frontend','video','Nội dung video: Cài đặt React Vite và cấu trúc project frontend. File seed theo course_folder react-elearning.','/videos/react-elearning/react-elearning-02-cai-dat-react-vite-va-cau-truc-project-frontend.mp4','local',NULL,600,0,'published',2,'2026-06-27 08:00:00','2026-06-27 08:00:00'),
(358,81,23,'Component, props và state trong React','video','Nội dung video: Component, props và state trong React. File seed theo course_folder react-elearning.','/videos/react-elearning/react-elearning-03-component-props-va-state-trong-react.mp4','local',NULL,600,0,'published',3,'2026-06-27 08:00:00','2026-06-27 08:00:00'),
(359,81,23,'Tổ chức layout Header, Footer và App Shell','video','Nội dung video: Tổ chức layout Header, Footer và App Shell. File seed theo course_folder react-elearning.','/videos/react-elearning/react-elearning-04-to-chuc-layout-header-footer-va-app-shell.mp4','local',NULL,600,0,'published',4,'2026-06-27 08:00:00','2026-06-27 08:00:00'),
(360,81,23,'Kết nối React với Laravel API bằng environment','video','Nội dung video: Kết nối React với Laravel API bằng environment. File seed theo course_folder react-elearning.','/videos/react-elearning/react-elearning-05-ket-noi-react-voi-laravel-api-bang-environment.mp4','local',NULL,600,0,'published',5,'2026-06-27 08:00:00','2026-06-27 08:00:00'),
(361,82,23,'React Router cho trang Home, Course List và Course Detail','video','Nội dung video: React Router cho trang Home, Course List và Course Detail. File seed theo course_folder react-elearning.','/videos/react-elearning/react-elearning-06-react-router-cho-trang-home-course-list-va-course-detail.mp4','local',NULL,600,0,'published',1,'2026-06-27 08:00:00','2026-06-27 08:00:00'),
(362,82,23,'Thiết kế Course Card và Course Grid','video','Nội dung video: Thiết kế Course Card và Course Grid. File seed theo course_folder react-elearning.','/videos/react-elearning/react-elearning-07-thiet-ke-course-card-va-course-grid.mp4','local',NULL,600,0,'published',2,'2026-06-27 08:00:00','2026-06-27 08:00:00'),
(363,82,23,'Gọi API danh sách khóa học và xử lý loading','video','Nội dung video: Gọi API danh sách khóa học và xử lý loading. File seed theo course_folder react-elearning.','/videos/react-elearning/react-elearning-08-goi-api-danh-sach-khoa-hoc-va-xu-ly-loading.mp4','local',NULL,600,0,'published',3,'2026-06-27 08:00:00','2026-06-27 08:00:00'),
(364,82,23,'Xử lý search, filter và sort khóa học trên frontend','video','Nội dung video: Xử lý search, filter và sort khóa học trên frontend. File seed theo course_folder react-elearning.','/videos/react-elearning/react-elearning-09-xu-ly-search-filter-va-sort-khoa-hoc-tren-frontend.mp4','local',NULL,600,0,'published',4,'2026-06-27 08:00:00','2026-06-27 08:00:00'),
(365,82,23,'Hiển thị Course Detail với chương và bài học','video','Nội dung video: Hiển thị Course Detail với chương và bài học. File seed theo course_folder react-elearning.','/videos/react-elearning/react-elearning-10-hien-thi-course-detail-voi-chuong-va-bai-hoc.mp4','local',NULL,600,0,'published',5,'2026-06-27 08:00:00','2026-06-27 08:00:00'),
(366,83,23,'Form đăng nhập và đăng ký trong React','video','Nội dung video: Form đăng nhập và đăng ký trong React. File seed theo course_folder react-elearning.','/videos/react-elearning/react-elearning-11-form-dang-nhap-va-dang-ky-trong-react.mp4','local',NULL,600,0,'published',1,'2026-06-27 08:00:00','2026-06-27 08:00:00'),
(367,83,23,'Lưu token và gửi Authorization header','video','Nội dung video: Lưu token và gửi Authorization header. File seed theo course_folder react-elearning.','/videos/react-elearning/react-elearning-12-luu-token-va-gui-authorization-header.mp4','local',NULL,600,0,'published',2,'2026-06-27 08:00:00','2026-06-27 08:00:00'),
(368,83,23,'Protected Route cho learner và instructor','video','Nội dung video: Protected Route cho learner và instructor. File seed theo course_folder react-elearning.','/videos/react-elearning/react-elearning-13-protected-route-cho-learner-va-instructor.mp4','local',NULL,600,0,'published',3,'2026-06-27 08:00:00','2026-06-27 08:00:00'),
(369,83,23,'Xử lý trạng thái user sau khi refresh trang','video','Nội dung video: Xử lý trạng thái user sau khi refresh trang. File seed theo course_folder react-elearning.','/videos/react-elearning/react-elearning-14-xu-ly-trang-thai-user-sau-khi-refresh-trang.mp4','local',NULL,600,0,'published',4,'2026-06-27 08:00:00','2026-06-27 08:00:00'),
(370,83,23,'Logout và xử lý lỗi 401 403 trên frontend','video','Nội dung video: Logout và xử lý lỗi 401 403 trên frontend. File seed theo course_folder react-elearning.','/videos/react-elearning/react-elearning-15-logout-va-xu-ly-loi-401-403-tren-frontend.mp4','local',NULL,600,0,'published',5,'2026-06-27 08:00:00','2026-06-27 08:00:00'),
(371,84,23,'Xây trang học video cho learner','video','Nội dung video: Xây trang học video cho learner. File seed theo course_folder react-elearning.','/videos/react-elearning/react-elearning-16-xay-trang-hoc-video-cho-learner.mp4','local',NULL,600,0,'published',1,'2026-06-27 08:00:00','2026-06-27 08:00:00'),
(372,84,23,'Hiển thị sidebar chương và danh sách bài học','video','Nội dung video: Hiển thị sidebar chương và danh sách bài học. File seed theo course_folder react-elearning.','/videos/react-elearning/react-elearning-17-hien-thi-sidebar-chuong-va-danh-sach-bai-hoc.mp4','local',NULL,600,0,'published',2,'2026-06-27 08:00:00','2026-06-27 08:00:00'),
(373,84,23,'Phát video từ media domain trong React','video','Nội dung video: Phát video từ media domain trong React. File seed theo course_folder react-elearning.','/videos/react-elearning/react-elearning-18-phat-video-tu-media-domain-trong-react.mp4','local',NULL,600,0,'published',3,'2026-06-27 08:00:00','2026-06-27 08:00:00'),
(374,84,23,'Lưu tiến độ học video và lesson progress','video','Nội dung video: Lưu tiến độ học video và lesson progress. File seed theo course_folder react-elearning.','/videos/react-elearning/react-elearning-19-luu-tien-do-hoc-video-va-lesson-progress.mp4','local',NULL,600,0,'published',4,'2026-06-27 08:00:00','2026-06-27 08:00:00'),
(375,84,23,'Giao diện ghi chú bài học trong màn học','video','Nội dung video: Giao diện ghi chú bài học trong màn học. File seed theo course_folder react-elearning.','/videos/react-elearning/react-elearning-20-giao-dien-ghi-chu-bai-hoc-trong-man-hoc.mp4','local',NULL,600,0,'published',5,'2026-06-27 08:00:00','2026-06-27 08:00:00'),
(376,85,23,'Quản lý state loading, error và empty state','video','Nội dung video: Quản lý state loading, error và empty state. File seed theo course_folder react-elearning.','/videos/react-elearning/react-elearning-21-quan-ly-state-loading-error-va-empty-state.mp4','local',NULL,600,0,'published',1,'2026-06-27 08:00:00','2026-06-27 08:00:00'),
(377,85,23,'Validate form frontend nhưng không thay thế backend','video','Nội dung video: Validate form frontend nhưng không thay thế backend. File seed theo course_folder react-elearning.','/videos/react-elearning/react-elearning-22-validate-form-frontend-nhung-khong-thay-the-backend.mp4','local',NULL,600,0,'published',2,'2026-06-27 08:00:00','2026-06-27 08:00:00'),
(378,85,23,'Tạo reusable component cho button, input và modal','video','Nội dung video: Tạo reusable component cho button, input và modal. File seed theo course_folder react-elearning.','/videos/react-elearning/react-elearning-23-tao-reusable-component-cho-button-input-va-modal.mp4','local',NULL,600,0,'published',3,'2026-06-27 08:00:00','2026-06-27 08:00:00'),
(379,85,23,'Tối ưu responsive cho dashboard và lesson page','video','Nội dung video: Tối ưu responsive cho dashboard và lesson page. File seed theo course_folder react-elearning.','/videos/react-elearning/react-elearning-24-toi-uu-responsive-cho-dashboard-va-lesson-page.mp4','local',NULL,600,0,'published',4,'2026-06-27 08:00:00','2026-06-27 08:00:00'),
(380,85,23,'Xử lý thông báo toast và confirm dialog','video','Nội dung video: Xử lý thông báo toast và confirm dialog. File seed theo course_folder react-elearning.','/videos/react-elearning/react-elearning-25-xu-ly-thong-bao-toast-va-confirm-dialog.mp4','local',NULL,600,0,'published',5,'2026-06-27 08:00:00','2026-06-27 08:00:00'),
(381,86,23,'Kết nối flow xem khóa học, mua khóa và học bài','video','Nội dung video: Kết nối flow xem khóa học, mua khóa và học bài. File seed theo course_folder react-elearning.','/videos/react-elearning/react-elearning-26-ket-noi-flow-xem-khoa-hoc-mua-khoa-va-hoc-bai.mp4','local',NULL,600,0,'published',1,'2026-06-27 08:00:00','2026-06-27 08:00:00'),
(382,86,23,'Tích hợp learning dashboard cho learner','video','Nội dung video: Tích hợp learning dashboard cho learner. File seed theo course_folder react-elearning.','/videos/react-elearning/react-elearning-27-tich-hop-learning-dashboard-cho-learner.mp4','local',NULL,600,0,'published',2,'2026-06-27 08:00:00','2026-06-27 08:00:00'),
(383,86,23,'Tối ưu build React trước khi deploy','video','Nội dung video: Tối ưu build React trước khi deploy. File seed theo course_folder react-elearning.','/videos/react-elearning/react-elearning-28-toi-uu-build-react-truoc-khi-deploy.mp4','local',NULL,600,0,'published',3,'2026-06-27 08:00:00','2026-06-27 08:00:00'),
(384,86,23,'Deploy React frontend lên aaPanel','video','Nội dung video: Deploy React frontend lên aaPanel. File seed theo course_folder react-elearning.','/videos/react-elearning/react-elearning-29-deploy-react-frontend-len-aapanel.mp4','local',NULL,600,0,'published',4,'2026-06-27 08:00:00','2026-06-27 08:00:00'),
(385,86,23,'Tổng kết khóa học React E-learning','video','Nội dung video: Tổng kết khóa học React E-learning. File seed theo course_folder react-elearning.','/videos/react-elearning/react-elearning-30-tong-ket-khoa-hoc-react-e-learning.mp4','local',NULL,600,0,'published',5,'2026-06-27 08:00:00','2026-06-27 08:00:00'),
(386,87,24,'Landing page là gì và khác homepage thế nào?','video','Nội dung video: Landing page là gì và khác homepage thế nào?. File seed theo course_folder landing-page-conversion.','/videos/landing-page-conversion/landing-page-conversion-01-landing-page-la-gi-va-khac-homepage-the-nao.mp4','local',NULL,600,1,'published',1,'2026-06-27 08:00:00','2026-06-27 08:00:00'),
(387,87,24,'Conversion là gì trong website sản phẩm?','video','Nội dung video: Conversion là gì trong website sản phẩm?. File seed theo course_folder landing-page-conversion.','/videos/landing-page-conversion/landing-page-conversion-02-conversion-la-gi-trong-website-san-pham.mp4','local',NULL,600,0,'published',2,'2026-06-27 08:00:00','2026-06-27 08:00:00'),
(388,87,24,'Cách xác định mục tiêu landing page','video','Nội dung video: Cách xác định mục tiêu landing page. File seed theo course_folder landing-page-conversion.','/videos/landing-page-conversion/landing-page-conversion-03-cach-xac-dinh-muc-tieu-landing-page.mp4','local',NULL,600,0,'published',3,'2026-06-27 08:00:00','2026-06-27 08:00:00'),
(389,87,24,'Cách hiểu khách hàng trước khi thiết kế','video','Nội dung video: Cách hiểu khách hàng trước khi thiết kế. File seed theo course_folder landing-page-conversion.','/videos/landing-page-conversion/landing-page-conversion-04-cach-hieu-khach-hang-truoc-khi-thiet-ke.mp4','local',NULL,600,0,'published',4,'2026-06-27 08:00:00','2026-06-27 08:00:00'),
(390,87,24,'Những lỗi landing page khiến người dùng thoát nhanh','video','Nội dung video: Những lỗi landing page khiến người dùng thoát nhanh. File seed theo course_folder landing-page-conversion.','/videos/landing-page-conversion/landing-page-conversion-05-nhung-loi-landing-page-khien-nguoi-dung-thoat-nhanh.mp4','local',NULL,600,0,'published',5,'2026-06-27 08:00:00','2026-06-27 08:00:00'),
(391,88,24,'Hero section cần có những gì?','video','Nội dung video: Hero section cần có những gì?. File seed theo course_folder landing-page-conversion.','/videos/landing-page-conversion/landing-page-conversion-06-hero-section-can-co-nhung-gi.mp4','local',NULL,600,0,'published',1,'2026-06-27 08:00:00','2026-06-27 08:00:00'),
(392,88,24,'Cách trình bày problem solution rõ ràng','video','Nội dung video: Cách trình bày problem solution rõ ràng. File seed theo course_folder landing-page-conversion.','/videos/landing-page-conversion/landing-page-conversion-07-cach-trinh-bay-problem-solution-ro-rang.mp4','local',NULL,600,0,'published',2,'2026-06-27 08:00:00','2026-06-27 08:00:00'),
(393,88,24,'Cách viết benefits thay vì chỉ liệt kê features','video','Nội dung video: Cách viết benefits thay vì chỉ liệt kê features. File seed theo course_folder landing-page-conversion.','/videos/landing-page-conversion/landing-page-conversion-08-cach-viet-benefits-thay-vi-chi-liet-ke-features.mp4','local',NULL,600,0,'published',3,'2026-06-27 08:00:00','2026-06-27 08:00:00'),
(394,88,24,'Cách dùng social proof testimonial case study','video','Nội dung video: Cách dùng social proof testimonial case study. File seed theo course_folder landing-page-conversion.','/videos/landing-page-conversion/landing-page-conversion-09-cach-dung-social-proof-testimonial-case-study.mp4','local',NULL,600,0,'published',4,'2026-06-27 08:00:00','2026-06-27 08:00:00'),
(395,88,24,'Cách đặt CTA theo từng giai đoạn đọc trang','video','Nội dung video: Cách đặt CTA theo từng giai đoạn đọc trang. File seed theo course_folder landing-page-conversion.','/videos/landing-page-conversion/landing-page-conversion-10-cach-dat-cta-theo-tung-giai-doan-doc-trang.mp4','local',NULL,600,0,'published',5,'2026-06-27 08:00:00','2026-06-27 08:00:00'),
(396,89,24,'Cách dùng visual hierarchy để dẫn mắt người dùng','video','Nội dung video: Cách dùng visual hierarchy để dẫn mắt người dùng. File seed theo course_folder landing-page-conversion.','/videos/landing-page-conversion/landing-page-conversion-11-cach-dung-visual-hierarchy-de-dan-mat-nguoi-dung.mp4','local',NULL,600,0,'published',1,'2026-06-27 08:00:00','2026-06-27 08:00:00'),
(397,89,24,'Cách phối màu typography và khoảng trắng','video','Nội dung video: Cách phối màu typography và khoảng trắng. File seed theo course_folder landing-page-conversion.','/videos/landing-page-conversion/landing-page-conversion-12-cach-phoi-mau-typography-va-khoang-trang.mp4','local',NULL,600,0,'published',2,'2026-06-27 08:00:00','2026-06-27 08:00:00'),
(398,89,24,'Cách viết headline subheadline dễ hiểu','video','Nội dung video: Cách viết headline subheadline dễ hiểu. File seed theo course_folder landing-page-conversion.','/videos/landing-page-conversion/landing-page-conversion-13-cach-viet-headline-subheadline-de-hieu.mp4','local',NULL,600,0,'published',3,'2026-06-27 08:00:00','2026-06-27 08:00:00'),
(399,89,24,'Cách thiết kế form liên hệ không gây ngại','video','Nội dung video: Cách thiết kế form liên hệ không gây ngại. File seed theo course_folder landing-page-conversion.','/videos/landing-page-conversion/landing-page-conversion-14-cach-thiet-ke-form-lien-he-khong-gay-ngai.mp4','local',NULL,600,0,'published',4,'2026-06-27 08:00:00','2026-06-27 08:00:00'),
(400,89,24,'Cách tối ưu landing page trên mobile','video','Nội dung video: Cách tối ưu landing page trên mobile. File seed theo course_folder landing-page-conversion.','/videos/landing-page-conversion/landing-page-conversion-15-cach-toi-uu-landing-page-tren-mobile.mp4','local',NULL,600,0,'published',5,'2026-06-27 08:00:00','2026-06-27 08:00:00'),
(401,90,24,'Các chỉ số cần theo dõi trên landing page','video','Nội dung video: Các chỉ số cần theo dõi trên landing page. File seed theo course_folder landing-page-conversion.','/videos/landing-page-conversion/landing-page-conversion-16-cac-chi-so-can-theo-doi-tren-landing-page.mp4','local',NULL,600,0,'published',1,'2026-06-27 08:00:00','2026-06-27 08:00:00'),
(402,90,24,'A/B testing cơ bản cho headline và CTA','video','Nội dung video: A/B testing cơ bản cho headline và CTA. File seed theo course_folder landing-page-conversion.','/videos/landing-page-conversion/landing-page-conversion-17-a-b-testing-co-ban-cho-headline-va-cta.mp4','local',NULL,600,0,'published',2,'2026-06-27 08:00:00','2026-06-27 08:00:00'),
(403,90,24,'Cách dùng heatmap và analytics ở mức nhập môn','video','Nội dung video: Cách dùng heatmap và analytics ở mức nhập môn. File seed theo course_folder landing-page-conversion.','/videos/landing-page-conversion/landing-page-conversion-18-cach-dung-heatmap-va-analytics-o-muc-nhap-mon.mp4','local',NULL,600,0,'published',3,'2026-06-27 08:00:00','2026-06-27 08:00:00'),
(404,90,24,'Checklist landing page trước khi chạy quảng cáo','video','Nội dung video: Checklist landing page trước khi chạy quảng cáo. File seed theo course_folder landing-page-conversion.','/videos/landing-page-conversion/landing-page-conversion-19-checklist-landing-page-truoc-khi-chay-quang-cao.mp4','local',NULL,600,0,'published',4,'2026-06-27 08:00:00','2026-06-27 08:00:00'),
(405,90,24,'Tổng kết khóa học Landing Page chuyển đổi cao','video','Nội dung video: Tổng kết khóa học Landing Page chuyển đổi cao. File seed theo course_folder landing-page-conversion.','/videos/landing-page-conversion/landing-page-conversion-20-tong-ket-khoa-hoc-landing-page-chuyen-doi-cao.mp4','local',NULL,600,0,'published',5,'2026-06-27 08:00:00','2026-06-27 08:00:00'),
(406,91,25,'Web Analytics là gì và vì sao Web Developer nên biết?','video','Nội dung video: Web Analytics là gì và vì sao Web Developer nên biết?. File seed theo course_folder web-analytics-ab-testing.','/videos/web-analytics-ab-testing/web-analytics-ab-testing-01-web-analytics-la-gi-va-vi-sao-web-developer-nen-biet.mp4','local',NULL,600,1,'published',1,'2026-06-27 08:00:00','2026-06-27 08:00:00'),
(407,91,25,'Page view session user conversion là gì?','video','Nội dung video: Page view session user conversion là gì?. File seed theo course_folder web-analytics-ab-testing.','/videos/web-analytics-ab-testing/web-analytics-ab-testing-02-page-view-session-user-conversion-la-gi.mp4','local',NULL,600,0,'published',2,'2026-06-27 08:00:00','2026-06-27 08:00:00'),
(408,91,25,'Event tracking dùng để theo dõi hành động nào?','video','Nội dung video: Event tracking dùng để theo dõi hành động nào?. File seed theo course_folder web-analytics-ab-testing.','/videos/web-analytics-ab-testing/web-analytics-ab-testing-03-event-tracking-dung-de-theo-doi-hanh-dong-nao.mp4','local',NULL,600,0,'published',3,'2026-06-27 08:00:00','2026-06-27 08:00:00'),
(409,91,25,'Funnel cơ bản trong website E-learning','video','Nội dung video: Funnel cơ bản trong website E-learning. File seed theo course_folder web-analytics-ab-testing.','/videos/web-analytics-ab-testing/web-analytics-ab-testing-04-funnel-co-ban-trong-website-e-learning.mp4','local',NULL,600,0,'published',4,'2026-06-27 08:00:00','2026-06-27 08:00:00'),
(410,91,25,'Những chỉ số không nên hiểu sai khi mới bắt đầu','video','Nội dung video: Những chỉ số không nên hiểu sai khi mới bắt đầu. File seed theo course_folder web-analytics-ab-testing.','/videos/web-analytics-ab-testing/web-analytics-ab-testing-05-nhung-chi-so-khong-nen-hieu-sai-khi-moi-bat-dau.mp4','local',NULL,600,0,'published',5,'2026-06-27 08:00:00','2026-06-27 08:00:00'),
(411,92,25,'Theo dõi click CTA trên landing page','video','Nội dung video: Theo dõi click CTA trên landing page. File seed theo course_folder web-analytics-ab-testing.','/videos/web-analytics-ab-testing/web-analytics-ab-testing-06-theo-doi-click-cta-tren-landing-page.mp4','local',NULL,600,0,'published',1,'2026-06-27 08:00:00','2026-06-27 08:00:00'),
(412,92,25,'Theo dõi form submit và lỗi form','video','Nội dung video: Theo dõi form submit và lỗi form. File seed theo course_folder web-analytics-ab-testing.','/videos/web-analytics-ab-testing/web-analytics-ab-testing-07-theo-doi-form-submit-va-loi-form.mp4','local',NULL,600,0,'published',2,'2026-06-27 08:00:00','2026-06-27 08:00:00'),
(413,92,25,'Theo dõi người dùng xem course detail','video','Nội dung video: Theo dõi người dùng xem course detail. File seed theo course_folder web-analytics-ab-testing.','/videos/web-analytics-ab-testing/web-analytics-ab-testing-08-theo-doi-nguoi-dung-xem-course-detail.mp4','local',NULL,600,0,'published',3,'2026-06-27 08:00:00','2026-06-27 08:00:00'),
(414,92,25,'Theo dõi video lesson progress ở mức sản phẩm','video','Nội dung video: Theo dõi video lesson progress ở mức sản phẩm. File seed theo course_folder web-analytics-ab-testing.','/videos/web-analytics-ab-testing/web-analytics-ab-testing-09-theo-doi-video-lesson-progress-o-muc-san-pham.mp4','local',NULL,600,0,'published',4,'2026-06-27 08:00:00','2026-06-27 08:00:00'),
(415,92,25,'Theo dõi drop-off trong flow checkout','video','Nội dung video: Theo dõi drop-off trong flow checkout. File seed theo course_folder web-analytics-ab-testing.','/videos/web-analytics-ab-testing/web-analytics-ab-testing-10-theo-doi-drop-off-trong-flow-checkout.mp4','local',NULL,600,0,'published',5,'2026-06-27 08:00:00','2026-06-27 08:00:00'),
(416,93,25,'A/B Testing là gì và dùng khi nào?','video','Nội dung video: A/B Testing là gì và dùng khi nào?. File seed theo course_folder web-analytics-ab-testing.','/videos/web-analytics-ab-testing/web-analytics-ab-testing-11-a-b-testing-la-gi-va-dung-khi-nao.mp4','local',NULL,600,0,'published',1,'2026-06-27 08:00:00','2026-06-27 08:00:00'),
(417,93,25,'Chọn một giả thuyết test rõ ràng','video','Nội dung video: Chọn một giả thuyết test rõ ràng. File seed theo course_folder web-analytics-ab-testing.','/videos/web-analytics-ab-testing/web-analytics-ab-testing-12-chon-mot-gia-thuyet-test-ro-rang.mp4','local',NULL,600,0,'published',2,'2026-06-27 08:00:00','2026-06-27 08:00:00'),
(418,93,25,'Test headline CTA layout và form','video','Nội dung video: Test headline CTA layout và form. File seed theo course_folder web-analytics-ab-testing.','/videos/web-analytics-ab-testing/web-analytics-ab-testing-13-test-headline-cta-layout-va-form.mp4','local',NULL,600,0,'published',3,'2026-06-27 08:00:00','2026-06-27 08:00:00'),
(419,93,25,'Những lỗi A/B Testing người mới hay mắc','video','Nội dung video: Những lỗi A/B Testing người mới hay mắc. File seed theo course_folder web-analytics-ab-testing.','/videos/web-analytics-ab-testing/web-analytics-ab-testing-14-nhung-loi-a-b-testing-nguoi-moi-hay-mac.mp4','local',NULL,600,0,'published',4,'2026-06-27 08:00:00','2026-06-27 08:00:00'),
(420,93,25,'Cách đọc kết quả test một cách thận trọng','video','Nội dung video: Cách đọc kết quả test một cách thận trọng. File seed theo course_folder web-analytics-ab-testing.','/videos/web-analytics-ab-testing/web-analytics-ab-testing-15-cach-doc-ket-qua-test-mot-cach-than-trong.mp4','local',NULL,600,0,'published',5,'2026-06-27 08:00:00','2026-06-27 08:00:00'),
(421,94,25,'Thiết kế event naming dễ hiểu cho dev team','video','Nội dung video: Thiết kế event naming dễ hiểu cho dev team. File seed theo course_folder web-analytics-ab-testing.','/videos/web-analytics-ab-testing/web-analytics-ab-testing-16-thiet-ke-event-naming-de-hieu-cho-dev-team.mp4','local',NULL,600,0,'published',1,'2026-06-27 08:00:00','2026-06-27 08:00:00'),
(422,94,25,'Kết hợp analytics với UX improvement','video','Nội dung video: Kết hợp analytics với UX improvement. File seed theo course_folder web-analytics-ab-testing.','/videos/web-analytics-ab-testing/web-analytics-ab-testing-17-ket-hop-analytics-voi-ux-improvement.mp4','local',NULL,600,0,'published',2,'2026-06-27 08:00:00','2026-06-27 08:00:00'),
(423,94,25,'Checklist tracking trước khi deploy landing page','video','Nội dung video: Checklist tracking trước khi deploy landing page. File seed theo course_folder web-analytics-ab-testing.','/videos/web-analytics-ab-testing/web-analytics-ab-testing-18-checklist-tracking-truoc-khi-deploy-landing-page.mp4','local',NULL,600,0,'published',3,'2026-06-27 08:00:00','2026-06-27 08:00:00'),
(424,94,25,'Cách trình bày insight analytics trong portfolio','video','Nội dung video: Cách trình bày insight analytics trong portfolio. File seed theo course_folder web-analytics-ab-testing.','/videos/web-analytics-ab-testing/web-analytics-ab-testing-19-cach-trinh-bay-insight-analytics-trong-portfolio.mp4','local',NULL,600,0,'published',4,'2026-06-27 08:00:00','2026-06-27 08:00:00'),
(425,94,25,'Tổng kết khóa học Web Analytics và A/B Testing','video','Nội dung video: Tổng kết khóa học Web Analytics và A/B Testing. File seed theo course_folder web-analytics-ab-testing.','/videos/web-analytics-ab-testing/web-analytics-ab-testing-20-tong-ket-khoa-hoc-web-analytics-va-a-b-testing.mp4','local',NULL,600,0,'published',5,'2026-06-27 08:00:00','2026-06-27 08:00:00'),
(426,95,26,'Content marketing là gì trong website công nghệ?','video','Nội dung video: Content marketing là gì trong website công nghệ?. File seed theo course_folder content-marketing-web.','/videos/content-marketing-web/content-marketing-web-01-content-marketing-la-gi-trong-website-cong-nghe.mp4','local',NULL,600,1,'published',1,'2026-06-27 08:00:00','2026-06-27 08:00:00'),
(427,95,26,'Vì sao lập trình viên web nên hiểu content?','video','Nội dung video: Vì sao lập trình viên web nên hiểu content?. File seed theo course_folder content-marketing-web.','/videos/content-marketing-web/content-marketing-web-02-vi-sao-lap-trinh-vien-web-nen-hieu-content.mp4','local',NULL,600,0,'published',2,'2026-06-27 08:00:00','2026-06-27 08:00:00'),
(428,95,26,'Phân biệt landing page blog post và product page','video','Nội dung video: Phân biệt landing page blog post và product page. File seed theo course_folder content-marketing-web.','/videos/content-marketing-web/content-marketing-web-03-phan-biet-landing-page-blog-post-va-product-page.mp4','local',NULL,600,0,'published',3,'2026-06-27 08:00:00','2026-06-27 08:00:00'),
(429,95,26,'Cách xác định người đọc trước khi viết nội dung','video','Nội dung video: Cách xác định người đọc trước khi viết nội dung. File seed theo course_folder content-marketing-web.','/videos/content-marketing-web/content-marketing-web-04-cach-xac-dinh-nguoi-doc-truoc-khi-viet-noi-dung.mp4','local',NULL,600,0,'published',4,'2026-06-27 08:00:00','2026-06-27 08:00:00'),
(430,95,26,'Cách biến tính năng kỹ thuật thành lợi ích người dùng','video','Nội dung video: Cách biến tính năng kỹ thuật thành lợi ích người dùng. File seed theo course_folder content-marketing-web.','/videos/content-marketing-web/content-marketing-web-05-cach-bien-tinh-nang-ky-thuat-thanh-loi-ich-nguoi-dung.mp4','local',NULL,600,0,'published',5,'2026-06-27 08:00:00','2026-06-27 08:00:00'),
(431,96,26,'Cách viết hero section rõ giá trị','video','Nội dung video: Cách viết hero section rõ giá trị. File seed theo course_folder content-marketing-web.','/videos/content-marketing-web/content-marketing-web-06-cach-viet-hero-section-ro-gia-tri.mp4','local',NULL,600,0,'published',1,'2026-06-27 08:00:00','2026-06-27 08:00:00'),
(432,96,26,'Cách viết section tính năng không sáo rỗng','video','Nội dung video: Cách viết section tính năng không sáo rỗng. File seed theo course_folder content-marketing-web.','/videos/content-marketing-web/content-marketing-web-07-cach-viet-section-tinh-nang-khong-sao-rong.mp4','local',NULL,600,0,'published',2,'2026-06-27 08:00:00','2026-06-27 08:00:00'),
(433,96,26,'Cách viết CTA tự nhiên và thuyết phục','video','Nội dung video: Cách viết CTA tự nhiên và thuyết phục. File seed theo course_folder content-marketing-web.','/videos/content-marketing-web/content-marketing-web-08-cach-viet-cta-tu-nhien-va-thuyet-phuc.mp4','local',NULL,600,0,'published',3,'2026-06-27 08:00:00','2026-06-27 08:00:00'),
(434,96,26,'Cách viết FAQ thật hơn cho landing page','video','Nội dung video: Cách viết FAQ thật hơn cho landing page. File seed theo course_folder content-marketing-web.','/videos/content-marketing-web/content-marketing-web-09-cach-viet-faq-that-hon-cho-landing-page.mp4','local',NULL,600,0,'published',4,'2026-06-27 08:00:00','2026-06-27 08:00:00'),
(435,96,26,'Cách viết footer và thông tin doanh nghiệp tĩnh','video','Nội dung video: Cách viết footer và thông tin doanh nghiệp tĩnh. File seed theo course_folder content-marketing-web.','/videos/content-marketing-web/content-marketing-web-10-cach-viet-footer-va-thong-tin-doanh-nghiep-tinh.mp4','local',NULL,600,0,'published',5,'2026-06-27 08:00:00','2026-06-27 08:00:00'),
(436,97,26,'Cách chọn chủ đề blog cho website E-learning','video','Nội dung video: Cách chọn chủ đề blog cho website E-learning. File seed theo course_folder content-marketing-web.','/videos/content-marketing-web/content-marketing-web-11-cach-chon-chu-de-blog-cho-website-e-learning.mp4','local',NULL,600,0,'published',1,'2026-06-27 08:00:00','2026-06-27 08:00:00'),
(437,97,26,'Cách viết outline bài blog công nghệ','video','Nội dung video: Cách viết outline bài blog công nghệ. File seed theo course_folder content-marketing-web.','/videos/content-marketing-web/content-marketing-web-12-cach-viet-outline-bai-blog-cong-nghe.mp4','local',NULL,600,0,'published',2,'2026-06-27 08:00:00','2026-06-27 08:00:00'),
(438,97,26,'Cách dùng ví dụ code mà người mới hiểu được','video','Nội dung video: Cách dùng ví dụ code mà người mới hiểu được. File seed theo course_folder content-marketing-web.','/videos/content-marketing-web/content-marketing-web-13-cach-dung-vi-du-code-ma-nguoi-moi-hieu-duoc.mp4','local',NULL,600,0,'published',3,'2026-06-27 08:00:00','2026-06-27 08:00:00'),
(439,97,26,'Cách viết tutorial có bước rõ ràng','video','Nội dung video: Cách viết tutorial có bước rõ ràng. File seed theo course_folder content-marketing-web.','/videos/content-marketing-web/content-marketing-web-14-cach-viet-tutorial-co-buoc-ro-rang.mp4','local',NULL,600,0,'published',4,'2026-06-27 08:00:00','2026-06-27 08:00:00'),
(440,97,26,'Cách tái sử dụng content cho README và portfolio','video','Nội dung video: Cách tái sử dụng content cho README và portfolio. File seed theo course_folder content-marketing-web.','/videos/content-marketing-web/content-marketing-web-15-cach-tai-su-dung-content-cho-readme-va-portfolio.mp4','local',NULL,600,0,'published',5,'2026-06-27 08:00:00','2026-06-27 08:00:00'),
(441,98,26,'Cách kiểm tra nội dung có khớp giao diện không','video','Nội dung video: Cách kiểm tra nội dung có khớp giao diện không. File seed theo course_folder content-marketing-web.','/videos/content-marketing-web/content-marketing-web-16-cach-kiem-tra-noi-dung-co-khop-giao-dien-khong.mp4','local',NULL,600,0,'published',1,'2026-06-27 08:00:00','2026-06-27 08:00:00'),
(442,98,26,'Cách phối hợp content với UI UX','video','Nội dung video: Cách phối hợp content với UI UX. File seed theo course_folder content-marketing-web.','/videos/content-marketing-web/content-marketing-web-17-cach-phoi-hop-content-voi-ui-ux.mp4','local',NULL,600,0,'published',2,'2026-06-27 08:00:00','2026-06-27 08:00:00'),
(443,98,26,'Cách dùng AI hỗ trợ viết content nhưng không copy mù','video','Nội dung video: Cách dùng AI hỗ trợ viết content nhưng không copy mù. File seed theo course_folder content-marketing-web.','/videos/content-marketing-web/content-marketing-web-18-cach-dung-ai-ho-tro-viet-content-nhung-khong-copy-mu.mp4','local',NULL,600,0,'published',3,'2026-06-27 08:00:00','2026-06-27 08:00:00'),
(444,98,26,'Checklist content trước khi đưa website lên production','video','Nội dung video: Checklist content trước khi đưa website lên production. File seed theo course_folder content-marketing-web.','/videos/content-marketing-web/content-marketing-web-19-checklist-content-truoc-khi-dua-website-len-production.mp4','local',NULL,600,0,'published',4,'2026-06-27 08:00:00','2026-06-27 08:00:00'),
(445,98,26,'Tổng kết khóa học Content Marketing cho Web','video','Nội dung video: Tổng kết khóa học Content Marketing cho Web. File seed theo course_folder content-marketing-web.','/videos/content-marketing-web/content-marketing-web-20-tong-ket-khoa-hoc-content-marketing-cho-web.mp4','local',NULL,600,0,'published',5,'2026-06-27 08:00:00','2026-06-27 08:00:00'),
(446,99,27,'SEO là gì và vì sao Web Developer nên biết?','video','Nội dung video: SEO là gì và vì sao Web Developer nên biết?. File seed theo course_folder seo-for-webdev.','/videos/seo-for-webdev/seo-for-webdev-01-seo-la-gi-va-vi-sao-web-developer-nen-biet.mp4','local',NULL,600,1,'published',1,'2026-06-27 08:00:00','2026-06-27 08:00:00'),
(447,99,27,'Google nhìn một trang web như thế nào?','video','Nội dung video: Google nhìn một trang web như thế nào?. File seed theo course_folder seo-for-webdev.','/videos/seo-for-webdev/seo-for-webdev-02-google-nhin-mot-trang-web-nhu-the-nao.mp4','local',NULL,600,0,'published',2,'2026-06-27 08:00:00','2026-06-27 08:00:00'),
(448,99,27,'Từ khóa search intent và nội dung hữu ích','video','Nội dung video: Từ khóa search intent và nội dung hữu ích. File seed theo course_folder seo-for-webdev.','/videos/seo-for-webdev/seo-for-webdev-03-tu-khoa-search-intent-va-noi-dung-huu-ich.mp4','local',NULL,600,0,'published',3,'2026-06-27 08:00:00','2026-06-27 08:00:00'),
(449,99,27,'URL slug title meta description là gì?','video','Nội dung video: URL slug title meta description là gì?. File seed theo course_folder seo-for-webdev.','/videos/seo-for-webdev/seo-for-webdev-04-url-slug-title-meta-description-la-gi.mp4','local',NULL,600,0,'published',4,'2026-06-27 08:00:00','2026-06-27 08:00:00'),
(450,99,27,'Những hiểu lầm phổ biến về SEO kỹ thuật','video','Nội dung video: Những hiểu lầm phổ biến về SEO kỹ thuật. File seed theo course_folder seo-for-webdev.','/videos/seo-for-webdev/seo-for-webdev-05-nhung-hieu-lam-pho-bien-ve-seo-ky-thuat.mp4','local',NULL,600,0,'published',5,'2026-06-27 08:00:00','2026-06-27 08:00:00'),
(451,100,27,'Cấu trúc heading H1 H2 H3 cho trang khóa học','video','Nội dung video: Cấu trúc heading H1 H2 H3 cho trang khóa học. File seed theo course_folder seo-for-webdev.','/videos/seo-for-webdev/seo-for-webdev-06-cau-truc-heading-h1-h2-h3-cho-trang-khoa-hoc.mp4','local',NULL,600,0,'published',1,'2026-06-27 08:00:00','2026-06-27 08:00:00'),
(452,100,27,'Internal link và breadcrumb trong website E-learning','video','Nội dung video: Internal link và breadcrumb trong website E-learning. File seed theo course_folder seo-for-webdev.','/videos/seo-for-webdev/seo-for-webdev-07-internal-link-va-breadcrumb-trong-website-e-learning.mp4','local',NULL,600,0,'published',2,'2026-06-27 08:00:00','2026-06-27 08:00:00'),
(453,100,27,'Tối ưu ảnh thumbnail alt text và file name','video','Nội dung video: Tối ưu ảnh thumbnail alt text và file name. File seed theo course_folder seo-for-webdev.','/videos/seo-for-webdev/seo-for-webdev-08-toi-uu-anh-thumbnail-alt-text-va-file-name.mp4','local',NULL,600,0,'published',3,'2026-06-27 08:00:00','2026-06-27 08:00:00'),
(454,100,27,'Sitemap robots txt và canonical cơ bản','video','Nội dung video: Sitemap robots txt và canonical cơ bản. File seed theo course_folder seo-for-webdev.','/videos/seo-for-webdev/seo-for-webdev-09-sitemap-robots-txt-va-canonical-co-ban.mp4','local',NULL,600,0,'published',4,'2026-06-27 08:00:00','2026-06-27 08:00:00'),
(455,100,27,'Tốc độ tải trang ảnh hưởng SEO ra sao?','video','Nội dung video: Tốc độ tải trang ảnh hưởng SEO ra sao?. File seed theo course_folder seo-for-webdev.','/videos/seo-for-webdev/seo-for-webdev-10-toc-do-tai-trang-anh-huong-seo-ra-sao.mp4','local',NULL,600,0,'published',5,'2026-06-27 08:00:00','2026-06-27 08:00:00'),
(456,101,27,'Cách viết title SEO cho trang khóa học','video','Nội dung video: Cách viết title SEO cho trang khóa học. File seed theo course_folder seo-for-webdev.','/videos/seo-for-webdev/seo-for-webdev-11-cach-viet-title-seo-cho-trang-khoa-hoc.mp4','local',NULL,600,0,'published',1,'2026-06-27 08:00:00','2026-06-27 08:00:00'),
(457,101,27,'Cách viết mô tả khóa học có ích cho người học','video','Nội dung video: Cách viết mô tả khóa học có ích cho người học. File seed theo course_folder seo-for-webdev.','/videos/seo-for-webdev/seo-for-webdev-12-cach-viet-mo-ta-khoa-hoc-co-ich-cho-nguoi-hoc.mp4','local',NULL,600,0,'published',2,'2026-06-27 08:00:00','2026-06-27 08:00:00'),
(458,101,27,'Cách tổ chức category course lesson thân thiện SEO','video','Nội dung video: Cách tổ chức category course lesson thân thiện SEO. File seed theo course_folder seo-for-webdev.','/videos/seo-for-webdev/seo-for-webdev-13-cach-to-chuc-category-course-lesson-than-thien-seo.mp4','local',NULL,600,0,'published',3,'2026-06-27 08:00:00','2026-06-27 08:00:00'),
(459,101,27,'Schema cơ bản cho Course và FAQ','video','Nội dung video: Schema cơ bản cho Course và FAQ. File seed theo course_folder seo-for-webdev.','/videos/seo-for-webdev/seo-for-webdev-14-schema-co-ban-cho-course-va-faq.mp4','local',NULL,600,0,'published',4,'2026-06-27 08:00:00','2026-06-27 08:00:00'),
(460,101,27,'Cách tránh duplicate content trong web khóa học','video','Nội dung video: Cách tránh duplicate content trong web khóa học. File seed theo course_folder seo-for-webdev.','/videos/seo-for-webdev/seo-for-webdev-15-cach-tranh-duplicate-content-trong-web-khoa-hoc.mp4','local',NULL,600,0,'published',5,'2026-06-27 08:00:00','2026-06-27 08:00:00'),
(461,102,27,'Google Search Console dùng để làm gì?','video','Nội dung video: Google Search Console dùng để làm gì?. File seed theo course_folder seo-for-webdev.','/videos/seo-for-webdev/seo-for-webdev-16-google-search-console-dung-de-lam-gi.mp4','local',NULL,600,0,'published',1,'2026-06-27 08:00:00','2026-06-27 08:00:00'),
(462,102,27,'Chỉ số impression click CTR position là gì?','video','Nội dung video: Chỉ số impression click CTR position là gì?. File seed theo course_folder seo-for-webdev.','/videos/seo-for-webdev/seo-for-webdev-17-chi-so-impression-click-ctr-position-la-gi.mp4','local',NULL,600,0,'published',2,'2026-06-27 08:00:00','2026-06-27 08:00:00'),
(463,102,27,'Cách kiểm tra index cơ bản cho website mới','video','Nội dung video: Cách kiểm tra index cơ bản cho website mới. File seed theo course_folder seo-for-webdev.','/videos/seo-for-webdev/seo-for-webdev-18-cach-kiem-tra-index-co-ban-cho-website-moi.mp4','local',NULL,600,0,'published',3,'2026-06-27 08:00:00','2026-06-27 08:00:00'),
(464,102,27,'Checklist SEO trước khi public landing page','video','Nội dung video: Checklist SEO trước khi public landing page. File seed theo course_folder seo-for-webdev.','/videos/seo-for-webdev/seo-for-webdev-19-checklist-seo-truoc-khi-public-landing-page.mp4','local',NULL,600,0,'published',4,'2026-06-27 08:00:00','2026-06-27 08:00:00'),
(465,102,27,'Tổng kết khóa học SEO cơ bản cho Web Developer','video','Nội dung video: Tổng kết khóa học SEO cơ bản cho Web Developer. File seed theo course_folder seo-for-webdev.','/videos/seo-for-webdev/seo-for-webdev-20-tong-ket-khoa-hoc-seo-co-ban-cho-web-developer.mp4','local',NULL,600,0,'published',5,'2026-06-27 08:00:00','2026-06-27 08:00:00'),
(466,103,28,'Payment flow trong E-learning hoạt động thế nào?','video','Nội dung video: Payment flow trong E-learning hoạt động thế nào?. File seed theo course_folder vnpay-laravel-payment.','/videos/vnpay-laravel-payment/vnpay-laravel-payment-01-payment-flow-trong-e-learning-hoat-dong-the-nao.mp4','local',NULL,600,1,'published',1,'2026-06-27 08:00:00','2026-06-27 08:00:00'),
(467,103,28,'Order payment_status và enrollment khác nhau ra sao?','video','Nội dung video: Order payment_status và enrollment khác nhau ra sao?. File seed theo course_folder vnpay-laravel-payment.','/videos/vnpay-laravel-payment/vnpay-laravel-payment-02-order-payment-status-va-enrollment-khac-nhau-ra-sao.mp4','local',NULL,600,0,'published',2,'2026-06-27 08:00:00','2026-06-27 08:00:00'),
(468,103,28,'VNPay sandbox là gì?','video','Nội dung video: VNPay sandbox là gì?. File seed theo course_folder vnpay-laravel-payment.','/videos/vnpay-laravel-payment/vnpay-laravel-payment-03-vnpay-sandbox-la-gi.mp4','local',NULL,600,0,'published',3,'2026-06-27 08:00:00','2026-06-27 08:00:00'),
(469,103,28,'Return URL và IPN URL khác nhau thế nào?','video','Nội dung video: Return URL và IPN URL khác nhau thế nào?. File seed theo course_folder vnpay-laravel-payment.','/videos/vnpay-laravel-payment/vnpay-laravel-payment-04-return-url-va-ipn-url-khac-nhau-the-nao.mp4','local',NULL,600,0,'published',4,'2026-06-27 08:00:00','2026-06-27 08:00:00'),
(470,103,28,'Những rủi ro khi xử lý thanh toán online','video','Nội dung video: Những rủi ro khi xử lý thanh toán online. File seed theo course_folder vnpay-laravel-payment.','/videos/vnpay-laravel-payment/vnpay-laravel-payment-05-nhung-rui-ro-khi-xu-ly-thanh-toan-online.mp4','local',NULL,600,0,'published',5,'2026-06-27 08:00:00','2026-06-27 08:00:00'),
(471,104,28,'Các biến cấu hình VNPay cần có','video','Nội dung video: Các biến cấu hình VNPay cần có. File seed theo course_folder vnpay-laravel-payment.','/videos/vnpay-laravel-payment/vnpay-laravel-payment-06-cac-bien-cau-hinh-vnpay-can-co.mp4','local',NULL,600,0,'published',1,'2026-06-27 08:00:00','2026-06-27 08:00:00'),
(472,104,28,'Lưu TMN Code và Hash Secret an toàn','video','Nội dung video: Lưu TMN Code và Hash Secret an toàn. File seed theo course_folder vnpay-laravel-payment.','/videos/vnpay-laravel-payment/vnpay-laravel-payment-07-luu-tmn-code-va-hash-secret-an-toan.mp4','local',NULL,600,0,'published',2,'2026-06-27 08:00:00','2026-06-27 08:00:00'),
(473,104,28,'Tạo config vnpay trong Laravel','video','Nội dung video: Tạo config vnpay trong Laravel. File seed theo course_folder vnpay-laravel-payment.','/videos/vnpay-laravel-payment/vnpay-laravel-payment-08-tao-config-vnpay-trong-laravel.mp4','local',NULL,600,0,'published',3,'2026-06-27 08:00:00','2026-06-27 08:00:00'),
(474,104,28,'Thiết kế bảng orders cho thanh toán khóa học','video','Nội dung video: Thiết kế bảng orders cho thanh toán khóa học. File seed theo course_folder vnpay-laravel-payment.','/videos/vnpay-laravel-payment/vnpay-laravel-payment-09-thiet-ke-bang-orders-cho-thanh-toan-khoa-hoc.mp4','local',NULL,600,0,'published',4,'2026-06-27 08:00:00','2026-06-27 08:00:00'),
(475,104,28,'Tạo order pending trước khi sang VNPay','video','Nội dung video: Tạo order pending trước khi sang VNPay. File seed theo course_folder vnpay-laravel-payment.','/videos/vnpay-laravel-payment/vnpay-laravel-payment-10-tao-order-pending-truoc-khi-sang-vnpay.mp4','local',NULL,600,0,'published',5,'2026-06-27 08:00:00','2026-06-27 08:00:00'),
(476,105,28,'Tạo payment URL VNPay từ order','video','Nội dung video: Tạo payment URL VNPay từ order. File seed theo course_folder vnpay-laravel-payment.','/videos/vnpay-laravel-payment/vnpay-laravel-payment-11-tao-payment-url-vnpay-tu-order.mp4','local',NULL,600,0,'published',1,'2026-06-27 08:00:00','2026-06-27 08:00:00'),
(477,105,28,'Build query params cho VNPay đúng cách','video','Nội dung video: Build query params cho VNPay đúng cách. File seed theo course_folder vnpay-laravel-payment.','/videos/vnpay-laravel-payment/vnpay-laravel-payment-12-build-query-params-cho-vnpay-dung-cach.mp4','local',NULL,600,0,'published',2,'2026-06-27 08:00:00','2026-06-27 08:00:00'),
(478,105,28,'Tạo secure hash và kiểm tra chữ ký','video','Nội dung video: Tạo secure hash và kiểm tra chữ ký. File seed theo course_folder vnpay-laravel-payment.','/videos/vnpay-laravel-payment/vnpay-laravel-payment-13-tao-secure-hash-va-kiem-tra-chu-ky.mp4','local',NULL,600,0,'published',3,'2026-06-27 08:00:00','2026-06-27 08:00:00'),
(479,105,28,'Frontend nhận payment_url và redirect người dùng','video','Nội dung video: Frontend nhận payment_url và redirect người dùng. File seed theo course_folder vnpay-laravel-payment.','/videos/vnpay-laravel-payment/vnpay-laravel-payment-14-frontend-nhan-payment-url-va-redirect-nguoi-dung.mp4','local',NULL,600,0,'published',4,'2026-06-27 08:00:00','2026-06-27 08:00:00'),
(480,105,28,'Test tạo thanh toán bằng Postman','video','Nội dung video: Test tạo thanh toán bằng Postman. File seed theo course_folder vnpay-laravel-payment.','/videos/vnpay-laravel-payment/vnpay-laravel-payment-15-test-tao-thanh-toan-bang-postman.mp4','local',NULL,600,0,'published',5,'2026-06-27 08:00:00','2026-06-27 08:00:00'),
(481,106,28,'Xử lý VNPay return URL sau khi người dùng thanh toán','video','Nội dung video: Xử lý VNPay return URL sau khi người dùng thanh toán. File seed theo course_folder vnpay-laravel-payment.','/videos/vnpay-laravel-payment/vnpay-laravel-payment-16-xu-ly-vnpay-return-url-sau-khi-nguoi-dung-thanh-toan.mp4','local',NULL,600,0,'published',1,'2026-06-27 08:00:00','2026-06-27 08:00:00'),
(482,106,28,'Xử lý IPN để cập nhật đơn hàng đáng tin cậy hơn','video','Nội dung video: Xử lý IPN để cập nhật đơn hàng đáng tin cậy hơn. File seed theo course_folder vnpay-laravel-payment.','/videos/vnpay-laravel-payment/vnpay-laravel-payment-17-xu-ly-ipn-de-cap-nhat-don-hang-dang-tin-cay-hon.mp4','local',NULL,600,0,'published',2,'2026-06-27 08:00:00','2026-06-27 08:00:00'),
(483,106,28,'Kiểm tra amount transaction no và response code','video','Nội dung video: Kiểm tra amount transaction no và response code. File seed theo course_folder vnpay-laravel-payment.','/videos/vnpay-laravel-payment/vnpay-laravel-payment-18-kiem-tra-amount-transaction-no-va-response-code.mp4','local',NULL,600,0,'published',3,'2026-06-27 08:00:00','2026-06-27 08:00:00'),
(484,106,28,'Tránh cập nhật paid nhiều lần cho cùng order','video','Nội dung video: Tránh cập nhật paid nhiều lần cho cùng order. File seed theo course_folder vnpay-laravel-payment.','/videos/vnpay-laravel-payment/vnpay-laravel-payment-19-tranh-cap-nhat-paid-nhieu-lan-cho-cung-order.mp4','local',NULL,600,0,'published',4,'2026-06-27 08:00:00','2026-06-27 08:00:00'),
(485,106,28,'Ghi log thanh toán để debug khi lỗi','video','Nội dung video: Ghi log thanh toán để debug khi lỗi. File seed theo course_folder vnpay-laravel-payment.','/videos/vnpay-laravel-payment/vnpay-laravel-payment-20-ghi-log-thanh-toan-de-debug-khi-loi.mp4','local',NULL,600,0,'published',5,'2026-06-27 08:00:00','2026-06-27 08:00:00'),
(486,107,28,'Tạo enrollment sau khi order paid','video','Nội dung video: Tạo enrollment sau khi order paid. File seed theo course_folder vnpay-laravel-payment.','/videos/vnpay-laravel-payment/vnpay-laravel-payment-21-tao-enrollment-sau-khi-order-paid.mp4','local',NULL,600,0,'published',1,'2026-06-27 08:00:00','2026-06-27 08:00:00'),
(487,107,28,'Tính platform fee và instructor revenue','video','Nội dung video: Tính platform fee và instructor revenue. File seed theo course_folder vnpay-laravel-payment.','/videos/vnpay-laravel-payment/vnpay-laravel-payment-22-tinh-platform-fee-va-instructor-revenue.mp4','local',NULL,600,0,'published',2,'2026-06-27 08:00:00','2026-06-27 08:00:00'),
(488,107,28,'Dùng DB transaction khi cập nhật payment','video','Nội dung video: Dùng DB transaction khi cập nhật payment. File seed theo course_folder vnpay-laravel-payment.','/videos/vnpay-laravel-payment/vnpay-laravel-payment-23-dung-db-transaction-khi-cap-nhat-payment.mp4','local',NULL,600,0,'published',3,'2026-06-27 08:00:00','2026-06-27 08:00:00'),
(489,107,28,'Xử lý đơn hàng failed cancelled expired','video','Nội dung video: Xử lý đơn hàng failed cancelled expired. File seed theo course_folder vnpay-laravel-payment.','/videos/vnpay-laravel-payment/vnpay-laravel-payment-24-xu-ly-don-hang-failed-cancelled-expired.mp4','local',NULL,600,0,'published',4,'2026-06-27 08:00:00','2026-06-27 08:00:00'),
(490,107,28,'Test case lỗi khi payment callback sai dữ liệu','video','Nội dung video: Test case lỗi khi payment callback sai dữ liệu. File seed theo course_folder vnpay-laravel-payment.','/videos/vnpay-laravel-payment/vnpay-laravel-payment-25-test-case-loi-khi-payment-callback-sai-du-lieu.mp4','local',NULL,600,0,'published',5,'2026-06-27 08:00:00','2026-06-27 08:00:00'),
(491,108,28,'Test flow mua khóa học từ frontend đến backend','video','Nội dung video: Test flow mua khóa học từ frontend đến backend. File seed theo course_folder vnpay-laravel-payment.','/videos/vnpay-laravel-payment/vnpay-laravel-payment-26-test-flow-mua-khoa-hoc-tu-frontend-den-backend.mp4','local',NULL,600,0,'published',1,'2026-06-27 08:00:00','2026-06-27 08:00:00'),
(492,108,28,'Test retry payment cho order chưa thanh toán','video','Nội dung video: Test retry payment cho order chưa thanh toán. File seed theo course_folder vnpay-laravel-payment.','/videos/vnpay-laravel-payment/vnpay-laravel-payment-27-test-retry-payment-cho-order-chua-thanh-toan.mp4','local',NULL,600,0,'published',2,'2026-06-27 08:00:00','2026-06-27 08:00:00'),
(493,108,28,'Test cancel order chưa thanh toán','video','Nội dung video: Test cancel order chưa thanh toán. File seed theo course_folder vnpay-laravel-payment.','/videos/vnpay-laravel-payment/vnpay-laravel-payment-28-test-cancel-order-chua-thanh-toan.mp4','local',NULL,600,0,'published',3,'2026-06-27 08:00:00','2026-06-27 08:00:00'),
(494,108,28,'Checklist bảo mật payment trước khi demo','video','Nội dung video: Checklist bảo mật payment trước khi demo. File seed theo course_folder vnpay-laravel-payment.','/videos/vnpay-laravel-payment/vnpay-laravel-payment-29-checklist-bao-mat-payment-truoc-khi-demo.mp4','local',NULL,600,0,'published',4,'2026-06-27 08:00:00','2026-06-27 08:00:00'),
(495,108,28,'Tổng kết khóa học VNPay Laravel Payment','video','Nội dung video: Tổng kết khóa học VNPay Laravel Payment. File seed theo course_folder vnpay-laravel-payment.','/videos/vnpay-laravel-payment/vnpay-laravel-payment-30-tong-ket-khoa-hoc-vnpay-laravel-payment.mp4','local',NULL,600,0,'published',5,'2026-06-27 08:00:00','2026-06-27 08:00:00'),
(496,109,29,'Web Developer cần học gì để đi thực tập?','video','Nội dung video: Web Developer cần học gì để đi thực tập?. File seed theo course_folder career-webdev.','/videos/career-webdev/career-webdev-01-web-developer-can-hoc-gi-de-di-thuc-tap.mp4','local',NULL,600,1,'published',1,'2026-06-27 08:00:00','2026-06-27 08:00:00'),
(497,109,29,'Frontend, Backend, Full-stack khác nhau thế nào?','video','Nội dung video: Frontend, Backend, Full-stack khác nhau thế nào?. File seed theo course_folder career-webdev.','/videos/career-webdev/career-webdev-02-frontend-backend-full-stack-khac-nhau-the-nao.mp4','local',NULL,600,0,'published',2,'2026-06-27 08:00:00','2026-06-27 08:00:00'),
(498,109,29,'Sinh viên IT nên chọn hướng nào trong năm cuối?','video','Nội dung video: Sinh viên IT nên chọn hướng nào trong năm cuối?. File seed theo course_folder career-webdev.','/videos/career-webdev/career-webdev-03-sinh-vien-it-nen-chon-huong-nao-trong-nam-cuoi.mp4','local',NULL,600,0,'published',3,'2026-06-27 08:00:00','2026-06-27 08:00:00'),
(499,109,29,'Fresher, Intern, Junior khác nhau ra sao?','video','Nội dung video: Fresher, Intern, Junior khác nhau ra sao?. File seed theo course_folder career-webdev.','/videos/career-webdev/career-webdev-04-fresher-intern-junior-khac-nhau-ra-sao.mp4','local',NULL,600,0,'published',4,'2026-06-27 08:00:00','2026-06-27 08:00:00'),
(500,109,29,'Nhà tuyển dụng cần gì ở sinh viên IT?','video','Nội dung video: Nhà tuyển dụng cần gì ở sinh viên IT?. File seed theo course_folder career-webdev.','/videos/career-webdev/career-webdev-05-nha-tuyen-dung-can-gi-o-sinh-vien-it.mp4','local',NULL,600,0,'published',5,'2026-06-27 08:00:00','2026-06-27 08:00:00'),
(501,110,29,'Khi nào nên học Frontend trước?','video','Nội dung video: Khi nào nên học Frontend trước?. File seed theo course_folder career-webdev.','/videos/career-webdev/career-webdev-06-khi-nao-nen-hoc-frontend-truoc.mp4','local',NULL,600,0,'published',1,'2026-06-27 08:00:00','2026-06-27 08:00:00'),
(502,110,29,'Khi nào nên học Backend trước?','video','Nội dung video: Khi nào nên học Backend trước?. File seed theo course_folder career-webdev.','/videos/career-webdev/career-webdev-07-khi-nao-nen-hoc-backend-truoc.mp4','local',NULL,600,0,'published',2,'2026-06-27 08:00:00','2026-06-27 08:00:00'),
(503,110,29,'Lộ trình học HTML CSS JavaScript','video','Nội dung video: Lộ trình học HTML CSS JavaScript. File seed theo course_folder career-webdev.','/videos/career-webdev/career-webdev-08-lo-trinh-hoc-html-css-javascript.mp4','local',NULL,600,0,'published',3,'2026-06-27 08:00:00','2026-06-27 08:00:00'),
(504,110,29,'Lộ trình học PHP Laravel Backend','video','Nội dung video: Lộ trình học PHP Laravel Backend. File seed theo course_folder career-webdev.','/videos/career-webdev/career-webdev-09-lo-trinh-hoc-php-laravel-backend.mp4','local',NULL,600,0,'published',4,'2026-06-27 08:00:00','2026-06-27 08:00:00'),
(505,110,29,'Lộ trình học React và API để làm dự án','video','Nội dung video: Lộ trình học React và API để làm dự án. File seed theo course_folder career-webdev.','/videos/career-webdev/career-webdev-10-lo-trinh-hoc-react-va-api-de-lam-du-an.mp4','local',NULL,600,0,'published',5,'2026-06-27 08:00:00','2026-06-27 08:00:00'),
(506,111,29,'Cách xây CV IT khi chưa có kinh nghiệm','video','Nội dung video: Cách xây CV IT khi chưa có kinh nghiệm. File seed theo course_folder career-webdev.','/videos/career-webdev/career-webdev-11-cach-xay-cv-it-khi-chua-co-kinh-nghiem.mp4','local',NULL,600,0,'published',1,'2026-06-27 08:00:00','2026-06-27 08:00:00'),
(507,111,29,'Cách viết mô tả đồ án trong CV','video','Nội dung video: Cách viết mô tả đồ án trong CV. File seed theo course_folder career-webdev.','/videos/career-webdev/career-webdev-12-cach-viet-mo-ta-do-an-trong-cv.mp4','local',NULL,600,0,'published',2,'2026-06-27 08:00:00','2026-06-27 08:00:00'),
(508,111,29,'Cách viết phần kỹ năng trong CV IT','video','Nội dung video: Cách viết phần kỹ năng trong CV IT. File seed theo course_folder career-webdev.','/videos/career-webdev/career-webdev-13-cach-viet-phan-ky-nang-trong-cv-it.mp4','local',NULL,600,0,'published',3,'2026-06-27 08:00:00','2026-06-27 08:00:00'),
(509,111,29,'Cách viết phần dự án cá nhân trong CV','video','Nội dung video: Cách viết phần dự án cá nhân trong CV. File seed theo course_folder career-webdev.','/videos/career-webdev/career-webdev-14-cach-viet-phan-du-an-ca-nhan-trong-cv.mp4','local',NULL,600,0,'published',4,'2026-06-27 08:00:00','2026-06-27 08:00:00'),
(510,111,29,'Những lỗi CV khiến sinh viên mất điểm','video','Nội dung video: Những lỗi CV khiến sinh viên mất điểm. File seed theo course_folder career-webdev.','/videos/career-webdev/career-webdev-15-nhung-loi-cv-khien-sinh-vien-mat-diem.mp4','local',NULL,600,0,'published',5,'2026-06-27 08:00:00','2026-06-27 08:00:00'),
(511,112,29,'GitHub profile cần có gì để gây ấn tượng?','video','Nội dung video: GitHub profile cần có gì để gây ấn tượng?. File seed theo course_folder career-webdev.','/videos/career-webdev/career-webdev-16-github-profile-can-co-gi-de-gay-an-tuong.mp4','local',NULL,600,0,'published',1,'2026-06-27 08:00:00','2026-06-27 08:00:00'),
(512,112,29,'Portfolio cá nhân nên có những dự án nào?','video','Nội dung video: Portfolio cá nhân nên có những dự án nào?. File seed theo course_folder career-webdev.','/videos/career-webdev/career-webdev-17-portfolio-ca-nhan-nen-co-nhung-du-an-nao.mp4','local',NULL,600,0,'published',2,'2026-06-27 08:00:00','2026-06-27 08:00:00'),
(513,112,29,'Cách viết README cho project trên GitHub','video','Nội dung video: Cách viết README cho project trên GitHub. File seed theo course_folder career-webdev.','/videos/career-webdev/career-webdev-18-cach-viet-readme-cho-project-tren-github.mp4','local',NULL,600,0,'published',3,'2026-06-27 08:00:00','2026-06-27 08:00:00'),
(514,112,29,'Cách trình bày đồ án E-learning trong portfolio','video','Nội dung video: Cách trình bày đồ án E-learning trong portfolio. File seed theo course_folder career-webdev.','/videos/career-webdev/career-webdev-19-cach-trinh-bay-do-an-e-learning-trong-portfolio.mp4','local',NULL,600,0,'published',4,'2026-06-27 08:00:00','2026-06-27 08:00:00'),
(515,112,29,'Checklist portfolio trước khi gửi nhà tuyển dụng','video','Nội dung video: Checklist portfolio trước khi gửi nhà tuyển dụng. File seed theo course_folder career-webdev.','/videos/career-webdev/career-webdev-20-checklist-portfolio-truoc-khi-gui-nha-tuyen-dung.mp4','local',NULL,600,0,'published',5,'2026-06-27 08:00:00','2026-06-27 08:00:00'),
(516,113,29,'Cách chuẩn bị trước khi đi phỏng vấn fresher','video','Nội dung video: Cách chuẩn bị trước khi đi phỏng vấn fresher. File seed theo course_folder career-webdev.','/videos/career-webdev/career-webdev-21-cach-chuan-bi-truoc-khi-di-phong-van-fresher.mp4','local',NULL,600,0,'published',1,'2026-06-27 08:00:00','2026-06-27 08:00:00'),
(517,113,29,'Những lỗi khiến sinh viên rớt phỏng vấn IT','video','Nội dung video: Những lỗi khiến sinh viên rớt phỏng vấn IT. File seed theo course_folder career-webdev.','/videos/career-webdev/career-webdev-22-nhung-loi-khien-sinh-vien-rot-phong-van-it.mp4','local',NULL,600,0,'published',2,'2026-06-27 08:00:00','2026-06-27 08:00:00'),
(518,113,29,'Cách trả lời khi chưa có kinh nghiệm đi làm','video','Nội dung video: Cách trả lời khi chưa có kinh nghiệm đi làm. File seed theo course_folder career-webdev.','/videos/career-webdev/career-webdev-23-cach-tra-loi-khi-chua-co-kinh-nghiem-di-lam.mp4','local',NULL,600,0,'published',3,'2026-06-27 08:00:00','2026-06-27 08:00:00'),
(519,113,29,'Cách nói về điểm mạnh và điểm yếu','video','Nội dung video: Cách nói về điểm mạnh và điểm yếu. File seed theo course_folder career-webdev.','/videos/career-webdev/career-webdev-24-cach-noi-ve-diem-manh-va-diem-yeu.mp4','local',NULL,600,0,'published',4,'2026-06-27 08:00:00','2026-06-27 08:00:00'),
(520,113,29,'Cách hỏi ngược lại nhà tuyển dụng','video','Nội dung video: Cách hỏi ngược lại nhà tuyển dụng. File seed theo course_folder career-webdev.','/videos/career-webdev/career-webdev-25-cach-hoi-nguoc-lai-nha-tuyen-dung.mp4','local',NULL,600,0,'published',5,'2026-06-27 08:00:00','2026-06-27 08:00:00'),
(521,114,29,'Lộ trình 90 ngày để sẵn sàng xin internship','video','Nội dung video: Lộ trình 90 ngày để sẵn sàng xin internship. File seed theo course_folder career-webdev.','/videos/career-webdev/career-webdev-26-lo-trinh-90-ngay-de-san-sang-xin-internship.mp4','local',NULL,600,0,'published',1,'2026-06-27 08:00:00','2026-06-27 08:00:00'),
(522,114,29,'30 ngày đầu: củng cố nền tảng web','video','Nội dung video: 30 ngày đầu: củng cố nền tảng web. File seed theo course_folder career-webdev.','/videos/career-webdev/career-webdev-27-30-ngay-dau-cung-co-nen-tang-web.mp4','local',NULL,600,0,'published',2,'2026-06-27 08:00:00','2026-06-27 08:00:00'),
(523,114,29,'30 ngày tiếp theo: hoàn thiện project và GitHub','video','Nội dung video: 30 ngày tiếp theo: hoàn thiện project và GitHub. File seed theo course_folder career-webdev.','/videos/career-webdev/career-webdev-28-30-ngay-tiep-theo-hoan-thien-project-va-github.mp4','local',NULL,600,0,'published',3,'2026-06-27 08:00:00','2026-06-27 08:00:00'),
(524,114,29,'30 ngày cuối: luyện phỏng vấn và apply','video','Nội dung video: 30 ngày cuối: luyện phỏng vấn và apply. File seed theo course_folder career-webdev.','/videos/career-webdev/career-webdev-29-30-ngay-cuoi-luyen-phong-van-va-apply.mp4','local',NULL,600,0,'published',4,'2026-06-27 08:00:00','2026-06-27 08:00:00'),
(525,114,29,'Tổng kết khóa học: từ sinh viên đến Web Developer Fresher','video','Nội dung video: Tổng kết khóa học: từ sinh viên đến Web Developer Fresher. File seed theo course_folder career-webdev.','/videos/career-webdev/career-webdev-30-tong-ket-khoa-hoc-tu-sinh-vien-den-web-developer-fresher.mp4','local',NULL,600,0,'published',5,'2026-06-27 08:00:00','2026-06-27 08:00:00'),
(526,115,30,'Backend Developer Fresher cần biết gì?','video','Nội dung video: Backend Developer Fresher cần biết gì?. File seed theo course_folder backend-interview.','/videos/backend-interview/backend-interview-01-backend-developer-fresher-can-biet-gi.mp4','bunny','4eead3b2-8490-4205-84c5-f38619be9a49',600,1,'published',1,'2026-06-27 08:00:00','2026-08-18 19:28:52'),
(527,115,30,'Nhà tuyển dụng hỏi gì ở Backend Fresher?','video','Nội dung video: Nhà tuyển dụng hỏi gì ở Backend Fresher?. File seed theo course_folder backend-interview.','/videos/backend-interview/backend-interview-02-nha-tuyen-dung-hoi-gi-o-backend-fresher.mp4','bunny','8a72af94-d3e8-4f8b-aabe-ca211542baaa',600,0,'published',2,'2026-06-27 08:00:00','2026-08-18 19:28:52'),
(528,115,30,'Cách giới thiệu bản thân khi phỏng vấn Backend','video','Nội dung video: Cách giới thiệu bản thân khi phỏng vấn Backend. File seed theo course_folder backend-interview.','/videos/backend-interview/backend-interview-03-cach-gioi-thieu-ban-than-khi-phong-van-backend.mp4','bunny','5fcbe4f3-1e7b-444b-8c4b-c10ea52a5f6f',600,0,'published',3,'2026-06-27 08:00:00','2026-08-20 05:28:08'),
(529,115,30,'Cách trình bày đồ án Backend E-learning','video','Nội dung video: Cách trình bày đồ án Backend E-learning. File seed theo course_folder backend-interview.','/videos/backend-interview/backend-interview-04-cach-trinh-bay-do-an-backend-e-learning.mp4','bunny','07c4e011-6575-457e-8293-9f53a1057095',600,0,'published',4,'2026-06-27 08:00:00','2026-08-20 05:29:47'),
(530,115,30,'Những lỗi khiến Backend Fresher mất điểm','video','Nội dung video: Những lỗi khiến Backend Fresher mất điểm. File seed theo course_folder backend-interview.','/videos/backend-interview/backend-interview-05-nhung-loi-khien-backend-fresher-mat-diem.mp4','local',NULL,600,0,'published',5,'2026-06-27 08:00:00','2026-06-27 08:00:00'),
(531,116,30,'REST API là gì và trả lời sao cho dễ hiểu?','video','Nội dung video: REST API là gì và trả lời sao cho dễ hiểu?. File seed theo course_folder backend-interview.','/videos/backend-interview/backend-interview-06-rest-api-la-gi-va-tra-loi-sao-cho-de-hieu.mp4','local',NULL,600,0,'published',1,'2026-06-27 08:00:00','2026-06-27 08:00:00'),
(532,116,30,'HTTP method GET POST PUT PATCH DELETE khác nhau thế nào?','video','Nội dung video: HTTP method GET POST PUT PATCH DELETE khác nhau thế nào?. File seed theo course_folder backend-interview.','/videos/backend-interview/backend-interview-07-http-method-get-post-put-patch-delete-khac-nhau-the-nao.mp4','local',NULL,600,0,'published',2,'2026-06-27 08:00:00','2026-06-27 08:00:00'),
(533,116,30,'Status code thường gặp khi làm REST API','video','Nội dung video: Status code thường gặp khi làm REST API. File seed theo course_folder backend-interview.','/videos/backend-interview/backend-interview-08-status-code-thuong-gap-khi-lam-rest-api.mp4','local',NULL,600,0,'published',3,'2026-06-27 08:00:00','2026-06-27 08:00:00'),
(534,116,30,'Request, response, header, body, query params khác nhau thế nào?','video','Nội dung video: Request, response, header, body, query params khác nhau thế nào?. File seed theo course_folder backend-interview.','/videos/backend-interview/backend-interview-09-request-response-header-body-query-params-khac-nhau-the-nao.mp4','local',NULL,600,0,'published',4,'2026-06-27 08:00:00','2026-06-27 08:00:00'),
(535,116,30,'JSON API, filter, sort và pagination nên giải thích ra sao?','video','Nội dung video: JSON API, filter, sort và pagination nên giải thích ra sao?. File seed theo course_folder backend-interview.','/videos/backend-interview/backend-interview-10-json-api-filter-sort-va-pagination-nen-giai-thich-ra-sao.mp4','local',NULL,600,0,'published',5,'2026-06-27 08:00:00','2026-06-27 08:00:00'),
(536,117,30,'Authentication và Authorization khác nhau ra sao?','video','Nội dung video: Authentication và Authorization khác nhau ra sao?. File seed theo course_folder backend-interview.','/videos/backend-interview/backend-interview-11-authentication-va-authorization-khac-nhau-ra-sao.mp4','local',NULL,600,0,'published',1,'2026-06-27 08:00:00','2026-06-27 08:00:00'),
(537,117,30,'Session, token, JWT và refresh token nên hiểu thế nào?','video','Nội dung video: Session, token, JWT và refresh token nên hiểu thế nào?. File seed theo course_folder backend-interview.','/videos/backend-interview/backend-interview-12-session-token-jwt-va-refresh-token-nen-hieu-the-nao.mp4','local',NULL,600,0,'published',2,'2026-06-27 08:00:00','2026-06-27 08:00:00'),
(538,117,30,'Middleware và role permission trong backend','video','Nội dung video: Middleware và role permission trong backend. File seed theo course_folder backend-interview.','/videos/backend-interview/backend-interview-13-middleware-va-role-permission-trong-backend.mp4','local',NULL,600,0,'published',3,'2026-06-27 08:00:00','2026-06-27 08:00:00'),
(539,117,30,'Validation dữ liệu trong backend','video','Nội dung video: Validation dữ liệu trong backend. File seed theo course_folder backend-interview.','/videos/backend-interview/backend-interview-14-validation-du-lieu-trong-backend.mp4','local',NULL,600,0,'published',4,'2026-06-27 08:00:00','2026-06-27 08:00:00'),
(540,117,30,'Bảo mật cơ bản khi làm REST API','video','Nội dung video: Bảo mật cơ bản khi làm REST API. File seed theo course_folder backend-interview.','/videos/backend-interview/backend-interview-15-bao-mat-co-ban-khi-lam-rest-api.mp4','local',NULL,600,0,'published',5,'2026-06-27 08:00:00','2026-06-27 08:00:00'),
(541,118,30,'Primary key, foreign key và relationship trong database','video','Nội dung video: Primary key, foreign key và relationship trong database. File seed theo course_folder backend-interview.','/videos/backend-interview/backend-interview-16-primary-key-foreign-key-va-relationship-trong-database.mp4','local',NULL,600,0,'published',1,'2026-06-27 08:00:00','2026-06-27 08:00:00'),
(542,118,30,'JOIN, N+1 query và index cơ bản trong phỏng vấn','video','Nội dung video: JOIN, N+1 query và index cơ bản trong phỏng vấn. File seed theo course_folder backend-interview.','/videos/backend-interview/backend-interview-17-join-n-1-query-va-index-co-ban-trong-phong-van.mp4','local',NULL,600,0,'published',2,'2026-06-27 08:00:00','2026-06-27 08:00:00'),
(543,118,30,'Transaction là gì và khi nào cần dùng?','video','Nội dung video: Transaction là gì và khi nào cần dùng?. File seed theo course_folder backend-interview.','/videos/backend-interview/backend-interview-18-transaction-la-gi-va-khi-nao-can-dung.mp4','local',NULL,600,0,'published',3,'2026-06-27 08:00:00','2026-06-27 08:00:00'),
(544,118,30,'Thiết kế database cho hệ thống E-learning','video','Nội dung video: Thiết kế database cho hệ thống E-learning. File seed theo course_folder backend-interview.','/videos/backend-interview/backend-interview-19-thiet-ke-database-cho-he-thong-e-learning.mp4','local',NULL,600,0,'published',4,'2026-06-27 08:00:00','2026-06-27 08:00:00'),
(545,118,30,'Soft delete, audit log và trạng thái dữ liệu','video','Nội dung video: Soft delete, audit log và trạng thái dữ liệu. File seed theo course_folder backend-interview.','/videos/backend-interview/backend-interview-20-soft-delete-audit-log-va-trang-thai-du-lieu.mp4','local',NULL,600,0,'published',5,'2026-06-27 08:00:00','2026-06-27 08:00:00'),
(546,119,30,'Laravel route, controller, request và resource','video','Nội dung video: Laravel route, controller, request và resource. File seed theo course_folder backend-interview.','/videos/backend-interview/backend-interview-21-laravel-route-controller-request-va-resource.mp4','local',NULL,600,0,'published',1,'2026-06-27 08:00:00','2026-06-27 08:00:00'),
(547,119,30,'Eloquent model, relationship và query scope','video','Nội dung video: Eloquent model, relationship và query scope. File seed theo course_folder backend-interview.','/videos/backend-interview/backend-interview-22-eloquent-model-relationship-va-query-scope.mp4','local',NULL,600,0,'published',2,'2026-06-27 08:00:00','2026-06-27 08:00:00'),
(548,119,30,'Repository Service Pattern giải thích trong phỏng vấn','video','Nội dung video: Repository Service Pattern giải thích trong phỏng vấn. File seed theo course_folder backend-interview.','/videos/backend-interview/backend-interview-23-repository-service-pattern-giai-thich-trong-phong-van.mp4','local',NULL,600,0,'published',3,'2026-06-27 08:00:00','2026-06-27 08:00:00'),
(549,119,30,'Exception handling và response format trong API','video','Nội dung video: Exception handling và response format trong API. File seed theo course_folder backend-interview.','/videos/backend-interview/backend-interview-24-exception-handling-va-response-format-trong-api.mp4','local',NULL,600,0,'published',4,'2026-06-27 08:00:00','2026-06-27 08:00:00'),
(550,119,30,'Queue, scheduler và command trong Laravel dùng khi nào?','video','Nội dung video: Queue, scheduler và command trong Laravel dùng khi nào?. File seed theo course_folder backend-interview.','/videos/backend-interview/backend-interview-25-queue-scheduler-va-command-trong-laravel-dung-khi-nao.mp4','local',NULL,600,0,'published',5,'2026-06-27 08:00:00','2026-06-27 08:00:00'),
(551,120,30,'Cách test API bằng Postman trong phỏng vấn','video','Nội dung video: Cách test API bằng Postman trong phỏng vấn. File seed theo course_folder backend-interview.','/videos/backend-interview/backend-interview-26-cach-test-api-bang-postman-trong-phong-van.mp4','local',NULL,600,0,'published',1,'2026-06-27 08:00:00','2026-06-27 08:00:00'),
(552,120,30,'Cách debug lỗi backend và đọc log','video','Nội dung video: Cách debug lỗi backend và đọc log. File seed theo course_folder backend-interview.','/videos/backend-interview/backend-interview-27-cach-debug-loi-backend-va-doc-log.mp4','local',NULL,600,0,'published',2,'2026-06-27 08:00:00','2026-06-27 08:00:00'),
(553,120,30,'Cách trả lời câu hỏi về payment và enrollment trong đồ án','video','Nội dung video: Cách trả lời câu hỏi về payment và enrollment trong đồ án. File seed theo course_folder backend-interview.','/videos/backend-interview/backend-interview-28-cach-tra-loi-cau-hoi-ve-payment-va-enrollment-trong-do-an.mp4','local',NULL,600,0,'published',3,'2026-06-27 08:00:00','2026-06-27 08:00:00'),
(554,120,30,'Mock interview Backend Fresher: 10 câu hỏi thường gặp','video','Nội dung video: Mock interview Backend Fresher: 10 câu hỏi thường gặp. File seed theo course_folder backend-interview.','/videos/backend-interview/backend-interview-29-mock-interview-backend-fresher-10-cau-hoi-thuong-gap.mp4','local',NULL,600,0,'published',4,'2026-06-27 08:00:00','2026-06-27 08:00:00'),
(555,120,30,'Tổng kết checklist trước phỏng vấn Backend Developer','video','Nội dung video: Tổng kết checklist trước phỏng vấn Backend Developer. File seed theo course_folder backend-interview.','/videos/backend-interview/backend-interview-30-tong-ket-checklist-truoc-phong-van-backend-developer.mp4','local',NULL,600,0,'published',5,'2026-06-27 08:00:00','2026-06-27 08:00:00'),
(710,275,454,'Lesson 6a797a982c4a4','video',NULL,NULL,'local',NULL,0,0,'published',0,'2026-08-10 07:15:36','2026-08-10 07:15:36'),
(711,276,455,'Lesson 6a797a98357f6','video',NULL,NULL,'local',NULL,0,0,'published',0,'2026-08-10 07:15:36','2026-08-10 07:15:36'),
(719,284,461,'Lesson B','text',NULL,NULL,'local',NULL,0,0,'published',2,'2026-08-10 07:15:36','2026-08-10 07:15:36'),
(720,284,461,'Lesson A (First by sort order)','video',NULL,NULL,'local',NULL,400,0,'published',1,'2026-08-10 07:15:36','2026-08-10 07:15:36');
/*!40000 ALTER TABLE `lessons` ENABLE KEYS */;
UNLOCK TABLES;
COMMIT;
SET AUTOCOMMIT=@OLD_AUTOCOMMIT;

--
-- Table structure for table `migrations`
--

DROP TABLE IF EXISTS `migrations`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `migrations` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `migration` varchar(255) NOT NULL,
  `batch` int(11) NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=43 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `migrations`
--

SET @OLD_AUTOCOMMIT=@@AUTOCOMMIT, @@AUTOCOMMIT=0;
LOCK TABLES `migrations` WRITE;
/*!40000 ALTER TABLE `migrations` DISABLE KEYS */;
INSERT INTO `migrations` VALUES
(1,'0001_01_01_000000_create_users_table',1),
(2,'0001_01_01_000001_create_cache_table',1),
(3,'0001_01_01_000002_create_jobs_table',1),
(4,'2026_06_06_160042_create_personal_access_tokens_table',1),
(5,'2026_06_08_000000_import_base_schema',1),
(6,'2026_06_09_053637_create_cat_users_table',1),
(7,'2026_06_09_053638_create_cat_categories_table',1),
(8,'2026_06_09_053638_create_cat_courses_table',1),
(9,'2026_06_09_053639_create_cat_coupons_table',1),
(10,'2026_06_09_053639_create_cat_orders_table',1),
(11,'2026_06_09_053640_create_cat_course_reviews_table',1),
(12,'2026_06_09_053640_create_cat_enrollments_table',1),
(13,'2026_06_09_053641_create_cat_banners_table',1),
(14,'2026_06_09_053641_create_cat_instructor_profiles_table',1),
(15,'2026_06_09_053642_create_cat_course_categories_table',1),
(16,'2026_06_12_142114_create_auth_sessions_table',1),
(17,'2026_07_11_000001_create_course_content_tables',1),
(18,'2026_07_11_000002_create_revenues_table_if_missing',1),
(19,'2026_07_15_000000_add_admin_columns',1),
(20,'2026_07_15_000001_create_notifications_table',1),
(21,'2026_07_20_000000_add_instructor_api_columns',1),
(22,'2026_07_20_000000_create_commission_rules_table',1),
(23,'2026_07_20_000001_add_revenue_share_source_columns',1),
(24,'2026_07_21_000000_add_name_column_to_users_table',1),
(25,'2026_07_22_000000_create_course_views_table',1),
(26,'2026_07_23_000000_create_instructor_question_stars_table',1),
(27,'2026_07_24_000000_create_user_otps_table',1),
(28,'2026_07_25_000000_create_credit_system_tables',1),
(29,'2026_07_25_000001_make_course_id_nullable_in_orders_table',1),
(30,'2026_07_26_000000_add_udemy_payout_and_revenue_columns',1),
(31,'2026_07_26_000001_create_withdrawal_revenues_table',1),
(32,'2026_07_30_000000_create_credit_tables',1),
(33,'2026_08_01_000000_add_discount_percent_to_courses_table',1),
(34,'2026_08_01_135230_add_indexes_to_finance_and_banners_tables',1),
(35,'2026_08_01_140532_change_sort_order_to_string_in_categories_table',1),
(36,'2026_08_01_141210_convert_sort_order_to_alphabetical_in_categories_table',1),
(37,'2026_08_14_203421_add_payout_provider_to_withdraw_requests_table',2),
(38,'2026_08_14_203821_ensure_provider_payout_id_on_withdraw_requests_table',2),
(39,'2026_08_14_210153_add_snapshot_balances_to_withdraw_requests_table',2),
(40,'2026_08_18_140619_add_avatar_public_id_to_users_table',2),
(41,'2026_08_18_190431_add_video_provider_to_lessons_table',3),
(42,'2026_08_20_175618_add_missing_video_provider_to_lessons_table',4);
/*!40000 ALTER TABLE `migrations` ENABLE KEYS */;
UNLOCK TABLES;
COMMIT;
SET AUTOCOMMIT=@OLD_AUTOCOMMIT;

--
-- Table structure for table `notifications`
--

DROP TABLE IF EXISTS `notifications`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `notifications` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `user_id` bigint(20) unsigned NOT NULL,
  `type` varchar(100) NOT NULL,
  `title` varchar(255) NOT NULL,
  `message` text NOT NULL,
  `data` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL CHECK (json_valid(`data`)),
  `action_url` varchar(2048) DEFAULT NULL,
  `channel` enum('web','email','both') NOT NULL DEFAULT 'web',
  `read_at` datetime DEFAULT NULL,
  `email_status` enum('pending','sent','failed','skipped') DEFAULT NULL,
  `email_sent_at` datetime DEFAULT NULL,
  `email_error` text DEFAULT NULL,
  `created_at` datetime NOT NULL DEFAULT current_timestamp(),
  `updated_at` datetime NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  PRIMARY KEY (`id`),
  KEY `idx_notifications_user_read` (`user_id`,`read_at`,`created_at`),
  KEY `idx_notifications_email_status` (`email_status`),
  CONSTRAINT `fk_notifications_user` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=28 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `notifications`
--

SET @OLD_AUTOCOMMIT=@@AUTOCOMMIT, @@AUTOCOMMIT=0;
LOCK TABLES `notifications` WRITE;
/*!40000 ALTER TABLE `notifications` DISABLE KEYS */;
INSERT INTO `notifications` VALUES
(1,4,'payment_paid','Thanh toán thành công','Bạn đã được ghi danh vào khóa Laravel REST API.','{\"order_id\":1,\"course_id\":1}','/me/courses/1','web','2026-06-20 08:10:00','sent','2026-06-20 07:51:00',NULL,'2026-06-20 07:51:00','2026-06-20 08:10:00'),
(2,4,'learning_resume','Bạn đang học dở bài Custom session','Tiếp tục từ giây 980 trong bài Custom session và refresh token.','{\"course_id\":1,\"lesson_id\":3,\"current_second\":980}','/learn/lessons/3','web',NULL,NULL,NULL,NULL,'2026-06-22 20:40:00','2026-06-22 20:40:00'),
(3,5,'payment_pending','Đơn hàng đang chờ thanh toán','Đơn ORD-2026-0002 của bạn đang chờ thanh toán.','{\"order_id\":2,\"course_id\":1}','/orders/2','web',NULL,'pending',NULL,NULL,'2026-06-22 20:31:00','2026-06-22 20:31:00'),
(4,6,'certificate_issued','Chứng chỉ đã được cấp','Chúc mừng bạn đã hoàn thành khóa Laravel REST API.','{\"certificate_id\":1,\"course_id\":1}','/certificates/MH-CERT-2026-0001','web','2026-05-03 10:00:00','sent','2026-05-03 09:05:00',NULL,'2026-05-03 09:05:00','2026-05-03 10:00:00'),
(5,2,'revenue_available','Doanh thu có thể rút','Bạn có doanh thu khả dụng từ khóa Laravel REST API.','{\"revenue_id\":1,\"order_id\":1}','/instructor/revenues','web',NULL,'sent','2026-06-20 08:00:00',NULL,'2026-06-20 08:00:00','2026-06-20 08:00:00'),
(6,17,'info','Lượt xem khoá học mới','Khoá học \"COURSE_PUBLISHED Laravel API Featured\" của bạn vừa có lượt xem mới.',NULL,'/instructor/courses/31','web',NULL,NULL,NULL,NULL,'2026-08-08 04:57:41','2026-08-08 04:57:41'),
(7,2,'info','Lượt xem khoá học mới','Khoá học \"PHP & MySQL nền tảng cho Backend\" của bạn vừa có lượt xem mới.',NULL,'/instructor/courses/2','web',NULL,NULL,NULL,NULL,'2026-08-08 04:58:08','2026-08-08 04:58:08'),
(8,2,'info','Lượt xem khoá học mới','Khoá học \"Phỏng vấn Backend Developer Fresher\" của bạn vừa có lượt xem mới.',NULL,'/instructor/courses/30','web',NULL,NULL,NULL,NULL,'2026-08-08 05:04:33','2026-08-08 05:04:33'),
(9,17,'info','Lượt xem khoá học mới','Khoá học \"COURSE_PUBLISHED Laravel API Featured\" của bạn vừa có lượt xem mới.',NULL,'/instructor/courses/31','web',NULL,NULL,NULL,NULL,'2026-08-08 05:47:29','2026-08-08 05:47:29'),
(10,2,'info','Lượt xem khoá học mới','Khoá học \"Phỏng vấn Backend Developer Fresher\" của bạn vừa có lượt xem mới.',NULL,'/instructor/courses/30','web',NULL,NULL,NULL,NULL,'2026-08-08 05:50:32','2026-08-08 05:50:32'),
(11,17,'info','Lượt xem khoá học mới','Khoá học \"COURSE_PUBLISHED Laravel API Featured\" của bạn vừa có lượt xem mới.',NULL,'/instructor/courses/31','web',NULL,NULL,NULL,NULL,'2026-08-08 06:27:52','2026-08-08 06:27:52'),
(12,2,'info','Lượt xem khoá học mới','Khoá học \"Phỏng vấn Backend Developer Fresher\" của bạn vừa có lượt xem mới.',NULL,'/instructor/courses/30','web',NULL,NULL,NULL,NULL,'2026-08-08 06:28:15','2026-08-08 06:28:15'),
(13,18,'info','Lượt xem khoá học mới','Khoá học \"COURSE_PUBLISHED React Latest\" của bạn vừa có lượt xem mới.',NULL,'/instructor/courses/33','web',NULL,NULL,NULL,NULL,'2026-08-08 06:30:19','2026-08-08 06:30:19'),
(14,2,'info','Lượt xem khoá học mới','Khoá học \"PHP & MySQL nền tảng cho Backend\" của bạn vừa có lượt xem mới.',NULL,'/instructor/courses/2','web',NULL,NULL,NULL,NULL,'2026-08-10 05:37:52','2026-08-10 05:37:52'),
(15,17,'info','Lượt xem khoá học mới','Khoá học \"COURSE_PUBLISHED Laravel API Featured\" của bạn vừa có lượt xem mới.',NULL,'/instructor/courses/31','web',NULL,NULL,NULL,NULL,'2026-08-10 07:08:47','2026-08-10 07:08:47'),
(25,24,'payment','🎉 Thanh toán thành công','Chào mừng bạn đến với khóa học \"Phỏng vấn Backend Developer Fresher\". Chúng tôi đã mở khóa toàn bộ bài học và gửi email hướng dẫn học tập cho bạn.',NULL,'/courses','web',NULL,NULL,NULL,NULL,'2026-08-18 19:26:43','2026-08-18 19:26:43'),
(26,24,'payment','🎉 Thanh toán thành công','Chào mừng bạn đến với khóa học \"Lộ trình xin việc Web Developer cho sinh viên IT\". Chúng tôi đã mở khóa toàn bộ bài học và gửi email hướng dẫn học tập cho bạn.',NULL,'/courses','web',NULL,NULL,NULL,NULL,'2026-08-20 05:35:49','2026-08-20 05:35:49'),
(27,24,'payment','🎉 Thanh toán thành công','Chào mừng bạn đến với khóa học \"COURSE_PUBLISHED Laravel API Featured\". Chúng tôi đã mở khóa toàn bộ bài học và gửi email hướng dẫn học tập cho bạn.',NULL,'/courses','web',NULL,NULL,NULL,NULL,'2026-08-20 16:33:20','2026-08-20 16:33:20');
/*!40000 ALTER TABLE `notifications` ENABLE KEYS */;
UNLOCK TABLES;
COMMIT;
SET AUTOCOMMIT=@OLD_AUTOCOMMIT;

--
-- Table structure for table `orders`
--

DROP TABLE IF EXISTS `orders`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `orders` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `order_code` varchar(80) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `user_id` bigint(20) unsigned NOT NULL,
  `course_id` bigint(20) unsigned NOT NULL,
  `coupon_id` bigint(20) unsigned DEFAULT NULL,
  `commission_rule_id` bigint(20) unsigned NOT NULL,
  `status` enum('pending_payment','paid','cancelled','failed','expired') CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'pending_payment',
  `payment_status` enum('pending','paid','failed','expired') CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'pending',
  `price_snapshot` decimal(15,2) NOT NULL,
  `discount_amount` decimal(15,2) NOT NULL DEFAULT 0.00,
  `amount` decimal(15,2) NOT NULL,
  `payment_method` varchar(50) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `provider_transaction_id` varchar(191) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `paid_at` datetime DEFAULT NULL,
  `expires_at` datetime DEFAULT NULL,
  `cancelled_reason` varchar(1000) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `failed_reason` varchar(1000) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `created_at` datetime NOT NULL DEFAULT current_timestamp(),
  `updated_at` datetime NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  PRIMARY KEY (`id`),
  UNIQUE KEY `uq_orders_order_code` (`order_code`),
  UNIQUE KEY `uq_orders_provider_transaction` (`provider_transaction_id`),
  KEY `idx_orders_user_status` (`user_id`,`status`),
  KEY `idx_orders_course` (`course_id`),
  KEY `idx_orders_coupon` (`coupon_id`),
  KEY `idx_orders_commission_rule` (`commission_rule_id`),
  KEY `idx_orders_payment_status` (`payment_status`,`created_at`),
  CONSTRAINT `fk_orders_commission_rule` FOREIGN KEY (`commission_rule_id`) REFERENCES `commission_rules` (`id`) ON UPDATE CASCADE,
  CONSTRAINT `fk_orders_coupon` FOREIGN KEY (`coupon_id`) REFERENCES `coupons` (`id`) ON UPDATE CASCADE,
  CONSTRAINT `fk_orders_course` FOREIGN KEY (`course_id`) REFERENCES `courses` (`id`) ON UPDATE CASCADE,
  CONSTRAINT `fk_orders_user` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON UPDATE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=275 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_uca1400_ai_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `orders`
--

SET @OLD_AUTOCOMMIT=@@AUTOCOMMIT, @@AUTOCOMMIT=0;
LOCK TABLES `orders` WRITE;
/*!40000 ALTER TABLE `orders` DISABLE KEYS */;
INSERT INTO `orders` VALUES
(1,'ORD-2026-0001',4,1,NULL,1,'paid','paid',299000.00,0.00,269100.00,'vnpay','VNPAY-DEMO-0001','2026-06-20 07:50:00',NULL,NULL,NULL,'2026-06-20 07:45:00','2026-06-20 07:50:00'),
(2,'ORD-2026-0002',5,1,NULL,1,'pending_payment','pending',299000.00,0.00,299000.00,NULL,NULL,NULL,NULL,NULL,NULL,'2026-06-22 20:30:00','2026-06-22 20:30:00'),
(3,'ORD-2026-0003',5,2,NULL,1,'failed','failed',399000.00,0.00,399000.00,'vnpay','VNPAY-DEMO-0003',NULL,NULL,NULL,NULL,'2026-06-18 12:00:00','2026-06-18 12:15:00'),
(4,'ORD-2026-0004',5,3,NULL,1,'cancelled','pending',449000.00,0.00,449000.00,NULL,NULL,NULL,NULL,NULL,NULL,'2026-06-17 09:00:00','2026-06-17 09:20:00'),
(5,'ORD-2026-0005',5,6,NULL,1,'expired','pending',499000.00,0.00,499000.00,NULL,NULL,NULL,NULL,NULL,NULL,'2026-05-01 09:00:00','2026-05-02 09:00:00'),
(6,'ORD-2026-0006',6,1,NULL,1,'paid','paid',299000.00,0.00,299000.00,'momo','MOMO-DEMO-0006','2026-05-01 07:40:00',NULL,NULL,NULL,'2026-05-01 07:30:00','2026-05-01 07:40:00'),
(7,'ORD-2026-0007',4,2,NULL,1,'paid','paid',399000.00,0.00,399000.00,'bank_transfer','BANK-DEMO-0007','2026-06-21 08:30:00',NULL,NULL,NULL,'2026-06-21 08:00:00','2026-06-21 08:30:00'),
(8,'ORD-2026-0008',5,7,NULL,1,'pending_payment','pending',0.00,0.00,0.00,'free',NULL,NULL,NULL,NULL,NULL,'2026-06-23 08:00:00','2026-06-23 08:00:00'),
(9,'ORD-2026-0009',4,3,NULL,1,'pending_payment','pending',449000.00,0.00,449000.00,NULL,NULL,NULL,NULL,NULL,NULL,'2026-06-23 08:05:00','2026-06-23 08:05:00'),
(10,'ORD-2026-0010',4,6,NULL,1,'failed','failed',499000.00,0.00,499000.00,'vnpay','VNPAY-DEMO-0010',NULL,NULL,NULL,NULL,'2026-06-19 14:00:00','2026-06-19 14:10:00'),
(11,'CAT-ORDER-LARAVEL-001',21,31,NULL,1,'paid','paid',799000.00,0.00,719100.00,'vnpay','CAT-TXN-LARAVEL-001','2026-07-29 17:47:13',NULL,NULL,NULL,'2026-08-06 17:47:13','2026-08-06 17:47:13'),
(12,'CAT-ORDER-LARAVEL-002',22,31,NULL,1,'paid','paid',799000.00,0.00,799000.00,'momo','CAT-TXN-LARAVEL-002','2026-07-30 17:47:13',NULL,NULL,NULL,'2026-08-06 17:47:13','2026-08-06 17:47:13'),
(13,'CAT-ORDER-PHP-001',21,32,NULL,1,'paid','paid',499000.00,0.00,499000.00,'vnpay','CAT-TXN-PHP-001','2026-07-22 17:47:13',NULL,NULL,NULL,'2026-08-06 17:47:13','2026-08-06 17:47:13'),
(14,'CAT-ORDER-PHP-002',22,32,NULL,1,'paid','paid',499000.00,0.00,499000.00,'momo','CAT-TXN-PHP-002','2026-07-23 17:47:13',NULL,NULL,NULL,'2026-08-06 17:47:13','2026-08-06 17:47:13'),
(15,'CAT-ORDER-PHP-003',23,32,NULL,1,'paid','paid',499000.00,0.00,499000.00,'bank_transfer','CAT-TXN-PHP-003','2026-07-24 17:47:13',NULL,NULL,NULL,'2026-08-06 17:47:13','2026-08-06 17:47:13'),
(16,'CAT-ORDER-REACT-001',21,33,NULL,1,'paid','paid',1199000.00,0.00,1199000.00,'vnpay','CAT-TXN-REACT-001','2026-08-05 17:47:13',NULL,NULL,NULL,'2026-08-06 17:47:13','2026-08-06 17:47:13'),
(17,'CAT-ORDER-FREE-UI-001',22,34,NULL,1,'paid','paid',0.00,0.00,0.00,'free',NULL,'2026-08-04 17:47:13',NULL,NULL,NULL,'2026-08-06 17:47:13','2026-08-06 17:47:13'),
(18,'ORD-20260807014202-NJLUR4',24,31,NULL,3,'paid','paid',799000.00,0.00,7990.00,'sepay','SEPAY-CONFIRM-1787243600','2026-08-20 16:33:20',NULL,NULL,NULL,'2026-08-07 01:42:02','2026-08-20 16:33:20'),
(19,'ORD-20260807021014-D2AAD1',24,1,NULL,1,'pending_payment','pending',299000.00,0.00,2990.00,'sepay','ORD-20260807021014-D2AAD1',NULL,NULL,NULL,NULL,'2026-08-07 02:10:14','2026-08-07 02:10:14'),
(20,'ORD-20260808055137-ZRPAM8',24,30,NULL,3,'paid','paid',299000.00,0.00,2990.00,'sepay','SEPAY-CONFIRM-1787081203','2026-08-18 19:26:43',NULL,NULL,NULL,'2026-08-08 05:51:37','2026-08-18 19:26:43'),
(21,'ORD-20260810053807-7XWOGW',24,2,NULL,1,'pending_payment','pending',399000.00,0.00,399000.00,'sepay','ORD-20260810053807-7XWOGW',NULL,NULL,NULL,NULL,'2026-08-10 05:38:07','2026-08-10 05:38:07'),
(243,'TEST-ORDER-DRAFT-6a797a98147c8',4,453,NULL,1,'paid','paid',100000.00,0.00,100000.00,'bank_transfer',NULL,NULL,NULL,NULL,NULL,'2026-08-10 07:15:36','2026-08-10 07:15:36'),
(244,'TEST-ORDER-DRAFT-6a797a98357f6',4,455,NULL,1,'paid','paid',100000.00,0.00,100000.00,'bank_transfer',NULL,NULL,NULL,NULL,NULL,'2026-08-10 07:15:36','2026-08-10 07:15:36'),
(245,'TEST-ORDER-NEXT-6a797a9896d4a',4,457,NULL,1,'paid','paid',100000.00,0.00,100000.00,'bank_transfer',NULL,NULL,NULL,NULL,NULL,'2026-08-10 07:15:36','2026-08-10 07:15:36'),
(246,'TEST-ORDER-NEXT-SUC1-6a797a98a113c',4,458,NULL,1,'paid','paid',100000.00,0.00,100000.00,'bank_transfer',NULL,NULL,NULL,NULL,NULL,'2026-08-10 07:15:36','2026-08-10 07:15:36'),
(247,'TEST-ORDER-NEXT-SUC2-6a797a98a8bf8',4,459,NULL,1,'paid','paid',100000.00,0.00,100000.00,'bank_transfer',NULL,NULL,NULL,NULL,NULL,'2026-08-10 07:15:36','2026-08-10 07:15:36'),
(248,'TEST-ORDER-NEXT-SUC3-6a797a98ae6a4',4,460,NULL,1,'paid','paid',100000.00,0.00,100000.00,'bank_transfer',NULL,NULL,NULL,NULL,NULL,'2026-08-10 07:15:36','2026-08-10 07:15:36'),
(249,'TEST-ORDER-6a797a98f05ea',4,461,NULL,1,'paid','paid',200000.00,0.00,200000.00,'bank_transfer',NULL,NULL,NULL,NULL,NULL,'2026-08-10 07:15:36','2026-08-10 07:15:36'),
(250,'TEST-ORDER-EMPTY-6a797a99010a1',4,462,NULL,1,'paid','paid',150000.00,0.00,150000.00,'bank_transfer',NULL,NULL,NULL,NULL,NULL,'2026-08-10 07:15:37','2026-08-10 07:15:37'),
(251,'TEST-ORDER-DRAFT-6a797a99926e4',4,463,NULL,1,'paid','paid',100000.00,0.00,100000.00,'bank_transfer',NULL,NULL,NULL,NULL,NULL,'2026-08-10 07:15:37','2026-08-10 07:15:37'),
(274,'ORD-20260820053547-5E7HGY',24,29,NULL,1,'paid','paid',299000.00,0.00,299000.00,'sepay','SEPAY-CONFIRM-1787204149','2026-08-20 05:35:49',NULL,NULL,NULL,'2026-08-20 05:35:47','2026-08-20 05:35:49');
/*!40000 ALTER TABLE `orders` ENABLE KEYS */;
UNLOCK TABLES;
COMMIT;
SET AUTOCOMMIT=@OLD_AUTOCOMMIT;

--
-- Table structure for table `payout_accounts`
--

DROP TABLE IF EXISTS `payout_accounts`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `payout_accounts` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `user_id` bigint(20) unsigned NOT NULL,
  `provider` varchar(100) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `account_number` varchar(100) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `account_name` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `status` enum('pending_verification','verified','disabled') CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'pending_verification',
  `is_default` tinyint(1) NOT NULL DEFAULT 0,
  `verified_at` datetime DEFAULT NULL,
  `disabled_at` datetime DEFAULT NULL,
  `created_at` datetime NOT NULL DEFAULT current_timestamp(),
  `updated_at` datetime NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  PRIMARY KEY (`id`),
  UNIQUE KEY `uq_payout_accounts_user_provider_account` (`user_id`,`provider`,`account_number`),
  KEY `idx_payout_accounts_user_status` (`user_id`,`status`),
  CONSTRAINT `fk_payout_accounts_user` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON UPDATE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=4 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_uca1400_ai_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `payout_accounts`
--

SET @OLD_AUTOCOMMIT=@@AUTOCOMMIT, @@AUTOCOMMIT=0;
LOCK TABLES `payout_accounts` WRITE;
/*!40000 ALTER TABLE `payout_accounts` DISABLE KEYS */;
INSERT INTO `payout_accounts` VALUES
(1,2,'bank','970400000001','NGUYEN MINH KHOA','verified',0,NULL,NULL,'2026-02-01 08:00:00','2026-02-01 08:00:00'),
(2,3,'bank','970400000002','TRAN HA LINH','pending_verification',0,NULL,NULL,'2026-02-02 08:00:00','2026-02-02 08:00:00'),
(3,2,'Techcombank – Ngân hàng TMCP Kỹ thương Việt Nam','1903123456789','GIẢNG VIÊN MINDHUB 01','verified',1,NULL,NULL,'2026-08-06 17:47:12','2026-08-06 17:47:12');
/*!40000 ALTER TABLE `payout_accounts` ENABLE KEYS */;
UNLOCK TABLES;
COMMIT;
SET AUTOCOMMIT=@OLD_AUTOCOMMIT;
/*!50003 SET @saved_cs_client      = @@character_set_client */ ;
/*!50003 SET @saved_cs_results     = @@character_set_results */ ;
/*!50003 SET @saved_col_connection = @@collation_connection */ ;
/*!50003 SET character_set_client  = utf8mb4 */ ;
/*!50003 SET character_set_results = utf8mb4 */ ;
/*!50003 SET collation_connection  = utf8mb4_uca1400_ai_ci */ ;
/*!50003 SET @saved_sql_mode       = @@sql_mode */ ;
/*!50003 SET sql_mode              = 'NO_AUTO_VALUE_ON_ZERO' */ ;
DELIMITER ;;
/*!50003 CREATE*/ /*!50017 DEFINER=``@`localhost`*/ /*!50003 TRIGGER `trg_payout_accounts_default_bi` BEFORE INSERT ON `payout_accounts` FOR EACH ROW BEGIN
    IF NEW.is_default = 1 AND NEW.status <> 'verified' THEN
        SIGNAL SQLSTATE '45000'
            SET MESSAGE_TEXT = 'Only a verified payout account can be default';
    END IF;

    IF NEW.is_default = 1
       AND EXISTS (
           SELECT 1 FROM `payout_accounts`
           WHERE `user_id` = NEW.`user_id`
             AND `is_default` = 1
       ) THEN
        SIGNAL SQLSTATE '45000'
            SET MESSAGE_TEXT = 'A user can have only one default payout account';
    END IF;
END 
*/;;
DELIMITER ;
/*!50003 SET sql_mode              = @saved_sql_mode */ ;
/*!50003 SET character_set_client  = @saved_cs_client */ ;
/*!50003 SET character_set_results = @saved_cs_results */ ;
/*!50003 SET collation_connection  = @saved_col_connection */ ;
/*!50003 SET @saved_cs_client      = @@character_set_client */ ;
/*!50003 SET @saved_cs_results     = @@character_set_results */ ;
/*!50003 SET @saved_col_connection = @@collation_connection */ ;
/*!50003 SET character_set_client  = utf8mb4 */ ;
/*!50003 SET character_set_results = utf8mb4 */ ;
/*!50003 SET collation_connection  = utf8mb4_uca1400_ai_ci */ ;
/*!50003 SET @saved_sql_mode       = @@sql_mode */ ;
/*!50003 SET sql_mode              = 'NO_AUTO_VALUE_ON_ZERO' */ ;
DELIMITER ;;
/*!50003 CREATE*/ /*!50017 DEFINER=``@`localhost`*/ /*!50003 TRIGGER `trg_payout_accounts_default_bu` BEFORE UPDATE ON `payout_accounts` FOR EACH ROW BEGIN
    IF NEW.is_default = 1 AND NEW.status <> 'verified' THEN
        SIGNAL SQLSTATE '45000'
            SET MESSAGE_TEXT = 'Only a verified payout account can be default';
    END IF;

    IF NEW.is_default = 1
       AND EXISTS (
           SELECT 1 FROM `payout_accounts`
           WHERE `user_id` = NEW.`user_id`
             AND `is_default` = 1
             AND `id` <> OLD.`id`
       ) THEN
        SIGNAL SQLSTATE '45000'
            SET MESSAGE_TEXT = 'A user can have only one default payout account';
    END IF;
END 
*/;;
DELIMITER ;
/*!50003 SET sql_mode              = @saved_sql_mode */ ;
/*!50003 SET character_set_client  = @saved_cs_client */ ;
/*!50003 SET character_set_results = @saved_cs_results */ ;
/*!50003 SET collation_connection  = @saved_col_connection */ ;

--
-- Table structure for table `revenues`
--

DROP TABLE IF EXISTS `revenues`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `revenues` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `instructor_id` bigint(20) unsigned NOT NULL,
  `course_id` bigint(20) unsigned NOT NULL,
  `order_id` bigint(20) unsigned NOT NULL,
  `gross_amount` decimal(15,2) NOT NULL,
  `instructor_amount` decimal(15,2) NOT NULL,
  `platform_fee_amount` decimal(15,2) NOT NULL,
  `commission_rule_id` bigint(20) unsigned NOT NULL,
  `earned_at` datetime NOT NULL DEFAULT current_timestamp(),
  `created_at` datetime NOT NULL DEFAULT current_timestamp(),
  `updated_at` datetime NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  PRIMARY KEY (`id`),
  UNIQUE KEY `uq_revenues_order` (`order_id`),
  KEY `idx_revenues_instructor_status` (`instructor_id`),
  KEY `idx_revenues_course` (`course_id`),
  KEY `idx_revenues_commission_rule` (`commission_rule_id`),
  CONSTRAINT `fk_revenues_commission_rule` FOREIGN KEY (`commission_rule_id`) REFERENCES `commission_rules` (`id`) ON UPDATE CASCADE,
  CONSTRAINT `fk_revenues_course` FOREIGN KEY (`course_id`) REFERENCES `courses` (`id`) ON UPDATE CASCADE,
  CONSTRAINT `fk_revenues_instructor` FOREIGN KEY (`instructor_id`) REFERENCES `users` (`id`) ON UPDATE CASCADE,
  CONSTRAINT `fk_revenues_order` FOREIGN KEY (`order_id`) REFERENCES `orders` (`id`) ON UPDATE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=226 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_uca1400_ai_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `revenues`
--

SET @OLD_AUTOCOMMIT=@@AUTOCOMMIT, @@AUTOCOMMIT=0;
LOCK TABLES `revenues` WRITE;
/*!40000 ALTER TABLE `revenues` DISABLE KEYS */;
INSERT INTO `revenues` VALUES
(1,2,1,1,269100.00,188370.00,80730.00,1,'2026-06-20 07:50:00','2026-06-20 07:50:00','2026-06-20 07:50:00'),
(2,2,1,6,299000.00,209300.00,89700.00,1,'2026-05-01 07:40:00','2026-05-01 07:40:00','2026-05-01 07:40:00'),
(3,2,2,7,399000.00,279300.00,119700.00,1,'2026-06-21 08:30:00','2026-06-21 08:30:00','2026-06-21 08:30:00'),
(223,2,30,20,2990.00,1106.30,1883.70,3,'2026-08-18 19:26:43','2026-08-18 19:26:43','2026-08-18 19:26:43'),
(224,2,29,274,299000.00,209300.00,89700.00,1,'2026-08-20 05:35:49','2026-08-20 05:35:49','2026-08-20 05:35:49'),
(225,17,31,18,7990.00,2956.30,5033.70,3,'2026-08-20 16:33:20','2026-08-20 16:33:20','2026-08-20 16:33:20');
/*!40000 ALTER TABLE `revenues` ENABLE KEYS */;
UNLOCK TABLES;
COMMIT;
SET AUTOCOMMIT=@OLD_AUTOCOMMIT;

--
-- Table structure for table `sessions`
--

DROP TABLE IF EXISTS `sessions`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `sessions` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `user_id` bigint(20) unsigned NOT NULL,
  `refresh_token_hash` varchar(255) NOT NULL,
  `device_name` varchar(255) DEFAULT NULL,
  `ip_address` varchar(45) DEFAULT NULL,
  `user_agent` text DEFAULT NULL,
  `expires_at` datetime NOT NULL,
  `revoked_at` datetime DEFAULT NULL,
  `created_at` datetime NOT NULL DEFAULT current_timestamp(),
  `updated_at` datetime NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  PRIMARY KEY (`id`),
  UNIQUE KEY `uq_sessions_refresh_token_hash` (`refresh_token_hash`),
  KEY `idx_sessions_user_active` (`user_id`,`revoked_at`,`expires_at`),
  CONSTRAINT `fk_sessions_user` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=137 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `sessions`
--

SET @OLD_AUTOCOMMIT=@@AUTOCOMMIT, @@AUTOCOMMIT=0;
LOCK TABLES `sessions` WRITE;
/*!40000 ALTER TABLE `sessions` DISABLE KEYS */;
INSERT INTO `sessions` VALUES
(1,4,'demo_hash_learner1_active_chrome','Chrome on Windows','127.0.0.1','Mozilla/5.0 Chrome MindHub Demo','2026-07-23 23:59:59',NULL,'2026-06-23 07:40:00','2026-08-21 01:31:06'),
(2,4,'demo_hash_learner1_expired_mobile','Safari on iPhone','10.0.0.12','Mobile Safari MindHub Demo','2026-05-01 23:59:59',NULL,'2026-04-01 08:00:00','2026-08-21 01:31:06'),
(3,4,'demo_hash_learner1_revoked_edge','Edge on Windows','10.0.0.13','Microsoft Edge MindHub Demo','2026-07-01 23:59:59','2026-06-01 09:00:00','2026-05-20 08:00:00','2026-08-21 01:31:06'),
(4,5,'demo_hash_learner2_active_chrome','Chrome on MacBook','10.0.0.14','Mozilla/5.0 Chrome macOS MindHub Demo','2026-07-22 23:59:59',NULL,'2026-06-22 21:00:00','2026-08-21 01:31:06'),
(5,7,'demo_hash_locked_user_active','Firefox on Windows','10.0.0.15','Firefox MindHub Demo','2026-07-01 23:59:59',NULL,'2026-04-01 10:00:00','2026-08-21 01:31:06'),
(6,10,'demo_hash_limit_device_1','Chrome on Windows','10.0.0.21','Chrome MindHub Demo','2026-07-23 23:59:59',NULL,'2026-06-23 06:00:00','2026-08-21 01:31:06'),
(7,10,'demo_hash_limit_device_2','Safari on iPad','10.0.0.22','Safari iPad MindHub Demo','2026-07-23 23:59:59',NULL,'2026-06-23 06:10:00','2026-08-21 01:31:06'),
(8,10,'demo_hash_limit_expired','Old Android','10.0.0.23','Android WebView MindHub Demo','2026-03-01 23:59:59',NULL,'2026-02-01 12:00:00','2026-08-21 01:31:06'),
(9,24,'f414aec6c1ba04a2b80f296028b47ee3ee7084ba0976d971df848ab3dbf4d647','api_client','127.0.0.1','Mozilla/5.0 (X11; Linux x86_64; rv:153.0) Gecko/20100101 Firefox/153.0','2027-08-07 01:25:04','2026-08-07 01:53:33','2026-08-07 01:25:04','2026-08-21 01:31:06'),
(10,24,'915937e9701b554fda64ce562380c3b62838682f4341a58cab1e202e401e27bf','api_client','127.0.0.1','Mozilla/5.0 (X11; Linux x86_64; rv:153.0) Gecko/20100101 Firefox/153.0','2027-08-07 01:53:49','2026-08-08 05:54:29','2026-08-07 01:53:49','2026-08-21 01:31:06'),
(11,24,'29321d464dd353218317deb900c0e16ed090bda333415c261347e111207746eb','api_client','127.0.0.1','Mozilla/5.0 (X11; Linux x86_64; rv:153.0) Gecko/20100101 Firefox/153.0','2027-08-08 05:55:14',NULL,'2026-08-08 05:55:14','2026-08-21 01:31:06'),
(12,24,'298e833dfdcc3424f77e367e763aa3628681b679b87f0093148d93297cf37e4e','api_client','127.0.0.1','Mozilla/5.0 (X11; Linux x86_64; rv:153.0) Gecko/20100101 Firefox/153.0','2027-08-08 05:56:14',NULL,'2026-08-08 05:56:14','2026-08-21 01:31:06'),
(13,24,'da19f7e8cddd55242f958929944ef2d174bb61cb21701836e1fd7cd4054f69f6','api_client','127.0.0.1','Mozilla/5.0 (X11; Linux x86_64; rv:153.0) Gecko/20100101 Firefox/153.0','2027-08-08 05:59:38',NULL,'2026-08-08 05:59:38','2026-08-21 01:31:06'),
(14,25,'2f6111e83d986381352684bc9f6afb375483e23bf7a28e5079478aea1bf80725','api_client','127.0.0.1','Mozilla/5.0 (X11; Linux x86_64; rv:153.0) Gecko/20100101 Firefox/153.0','2027-08-08 06:06:41',NULL,'2026-08-08 06:06:41','2026-08-21 01:31:06'),
(15,24,'98ffc9a410b2a1a1ecce461c4d2b78d147e4916b2e18d5e427279c1719fbe333','api_client','127.0.0.1','Mozilla/5.0 (X11; Linux x86_64; rv:153.0) Gecko/20100101 Firefox/153.0','2027-08-08 06:27:48',NULL,'2026-08-08 06:27:48','2026-08-21 01:31:06'),
(16,1,'e3bb4c321b3ff20f486eba8e19aae81398d9453a43b1e11f9f82c9eeea8630f5','testing','127.0.0.1','Symfony','2027-08-10 07:15:22',NULL,'2026-08-10 07:15:22','2026-08-21 01:31:06'),
(17,1,'d2cc923f55cc6129eeb60f6fc26281ce2be3c9c147b2d2f57e09730a10685403','testing','127.0.0.1','Symfony','2027-08-10 07:15:22',NULL,'2026-08-10 07:15:22','2026-08-21 01:31:06'),
(18,1,'4c50f6395088ff22ed5fe7914f5210758a514ea1b68c782a28039a036d790e04','testing','127.0.0.1','Symfony','2027-08-10 07:15:22',NULL,'2026-08-10 07:15:22','2026-08-21 01:31:06'),
(19,1,'5ee6aa184f09187267f875216cb7542cec4e10abb63adf9f9829ae5d98f54fae','testing','127.0.0.1','Symfony','2027-08-10 07:15:22',NULL,'2026-08-10 07:15:22','2026-08-21 01:31:06'),
(20,1,'9c73f1c92e725b2b36530089eb514bedd3edf7587c5b98e38bf3bb12abffd114','testing','127.0.0.1','Symfony','2027-08-10 07:15:22',NULL,'2026-08-10 07:15:22','2026-08-21 01:31:06'),
(21,2,'ddd7f47fa50f7b23f89de0ffd8d0f3dcede16b20c66254b9c669170e201fd874','testing','127.0.0.1','Symfony','2027-08-10 07:15:22',NULL,'2026-08-10 07:15:22','2026-08-21 01:31:06'),
(22,1,'7eb73d49930fadd46fae038debb65821383e4a487352d0bd31db07c0d3da93b9','testing','127.0.0.1','Symfony','2027-08-10 07:15:22',NULL,'2026-08-10 07:15:22','2026-08-21 01:31:06'),
(23,1,'1d85ebe804fd384d87fa2e00d19c634214f5a38dfa07d0748c8b8410cc66e8f7','testing','127.0.0.1','Symfony','2027-08-10 07:15:23',NULL,'2026-08-10 07:15:23','2026-08-21 01:31:06'),
(24,1,'6cd946d7207c836dc78bdfc8c96268f008170cf675daf4a1f0c53dca8010c684','testing','127.0.0.1','Symfony','2027-08-10 07:15:23',NULL,'2026-08-10 07:15:23','2026-08-21 01:31:06'),
(25,1,'8d3cbd2b79091957d49ab4871138803294faf5dc49b0f93f3b6771cb05ef222d','testing','127.0.0.1','Symfony','2027-08-10 07:15:23',NULL,'2026-08-10 07:15:23','2026-08-21 01:31:06'),
(26,1,'65a2420baac1f8725f376ed476ed634cfbcffabc461e11cb70585529c7c65e49','testing','127.0.0.1','Symfony','2027-08-10 07:15:23',NULL,'2026-08-10 07:15:23','2026-08-21 01:31:06'),
(27,1,'a97f43fb08331519b182122e28c5300fe7acc196cc1135d95378f55088667b5e','testing','127.0.0.1','Symfony','2027-08-10 07:15:23',NULL,'2026-08-10 07:15:23','2026-08-21 01:31:06'),
(28,1,'49af995e2e7b6cfb8b830c1cd6d6d077dfc5976653d3b6e21fe4b7ed3a634202','testing','127.0.0.1','Symfony','2027-08-10 07:15:23',NULL,'2026-08-10 07:15:23','2026-08-21 01:31:06'),
(29,1,'ca02e755883d3018a0cd4af168d3d73c0aee78b24a3560a620070b228827cbca','testing','127.0.0.1','Symfony','2027-08-10 07:15:23',NULL,'2026-08-10 07:15:23','2026-08-21 01:31:06'),
(30,4,'81b07dfcb51a7e3cd045518f76c20a37363dd4d2503ca92073e95fa759810d02','testing','127.0.0.1','Symfony','2027-08-10 07:15:23',NULL,'2026-08-10 07:15:23','2026-08-21 01:31:06'),
(31,2,'62d85db94b05751b647e2c4178595794bbf0d86bfe3323af1e5f121132e93bd4','testing','127.0.0.1','Symfony','2027-08-10 07:15:23',NULL,'2026-08-10 07:15:23','2026-08-21 01:31:06'),
(32,1,'1743327d0cb81b1c11743d127b4124ffa5e2fdd0db91563ba6a65f75ef9b0767','testing','127.0.0.1','Symfony','2027-08-10 07:15:23',NULL,'2026-08-10 07:15:23','2026-08-21 01:31:06'),
(33,1,'90ccd2988b99992b8fd781137be6bc450a6d66e0c8ddfea81864d7fa12f43ec2','testing','127.0.0.1','Symfony','2027-08-10 07:15:23',NULL,'2026-08-10 07:15:23','2026-08-21 01:31:06'),
(34,1,'600e98e8d258bea7958ab58d259c49c95bf1460425de5c0e1002a20a3efbbe79','testing','127.0.0.1','Symfony','2027-08-10 07:15:23',NULL,'2026-08-10 07:15:23','2026-08-21 01:31:06'),
(35,1,'c3a2586a60d322274b74f72d6cd301b8ee43ad7cda2cd52dc3c052190fc95bc6','testing','127.0.0.1','Symfony','2027-08-10 07:15:23',NULL,'2026-08-10 07:15:23','2026-08-21 01:31:06'),
(49,1,'8f9ca026992e4ba7896e009d92e29ad2d18ea0a53b3a2fa7507e7810268c3ac7','testing','127.0.0.1','Symfony','2027-08-10 07:15:24',NULL,'2026-08-10 07:15:24','2026-08-21 01:31:06'),
(50,1,'89059dd475e86c4c5566e94ae7ce0c3c10256c473cc5c81320634bd6aaa01b11','testing','127.0.0.1','Symfony','2027-08-10 07:15:24',NULL,'2026-08-10 07:15:24','2026-08-21 01:31:06'),
(51,1,'df547bf8fc85401478e3062534c427469d4876558c0749c6ac7262b7ee85f008','testing','127.0.0.1','Symfony','2027-08-10 07:15:24',NULL,'2026-08-10 07:15:24','2026-08-21 01:31:06'),
(52,1,'a83ac66beb2504b44ecab00657a0c211f66fd978e0cca2beb2ac92133bb13b12','testing','127.0.0.1','Symfony','2027-08-10 07:15:24',NULL,'2026-08-10 07:15:24','2026-08-21 01:31:06'),
(53,1,'94cfcad107e839c362801067663b32e27ce3041e9ca6313bd5d196ec19451db2','testing','127.0.0.1','Symfony','2027-08-10 07:15:24',NULL,'2026-08-10 07:15:24','2026-08-21 01:31:06'),
(54,2,'5a756b9bc62a2ca71d5d67055af7046c63083722e81d406a98fa788c151b0f39','testing','127.0.0.1','Symfony','2027-08-10 07:15:24',NULL,'2026-08-10 07:15:24','2026-08-21 01:31:06'),
(55,1,'fdf7f9587ef286d98718a16b111fd16d3edf559eba60cf04f3f0f7aa32821e16','testing','127.0.0.1','Symfony','2027-08-10 07:15:24',NULL,'2026-08-10 07:15:24','2026-08-21 01:31:06'),
(56,1,'97e11fea2705fc351ca65976e48c8564e3aa02df91fe3f90fc536ca693327242','testing','127.0.0.1','Symfony','2027-08-10 07:15:24',NULL,'2026-08-10 07:15:24','2026-08-21 01:31:06'),
(57,1,'15382c3d3e2428daa388dd0db5f4811c9b4045f3e48c02c6796051964f23102d','testing','127.0.0.1','Symfony','2027-08-10 07:15:24',NULL,'2026-08-10 07:15:24','2026-08-21 01:31:06'),
(58,1,'344278b7b9aa09cd5ed36ca26af4979263ecc9407398c6dc8d2a291b30a10072','testing','127.0.0.1','Symfony','2027-08-10 07:15:24',NULL,'2026-08-10 07:15:24','2026-08-21 01:31:06'),
(127,5,'62b20456048b269dc2354fa1ffe9f8a8f59ae22e523757fe1b4600eb778388ff','testing','127.0.0.1','Symfony','2027-08-10 07:15:35',NULL,'2026-08-10 07:15:35','2026-08-21 01:31:06'),
(128,5,'cefbfee682790b86608078d4ab36f47b73b1fe63c7982de773e62497fa0417a3','testing','127.0.0.1','Symfony','2027-08-10 07:15:36',NULL,'2026-08-10 07:15:36','2026-08-21 01:31:06'),
(129,5,'6e56569d69288e9e00976592d8ec831521dc495f9ab74200349d5e7b68c50992','testing','127.0.0.1','Symfony','2027-08-10 07:15:36',NULL,'2026-08-10 07:15:36','2026-08-21 01:31:06'),
(130,5,'8ad191f036713389b2378c69cfb35ebcd57cf04b04790953e464767718bd0f42','testing','127.0.0.1','Symfony','2027-08-10 07:15:36',NULL,'2026-08-10 07:15:36','2026-08-21 01:31:06'),
(131,5,'1534e23951f68debb026a5e166e3e14848367f1fb18df273e6590a1339e13e4f','testing','127.0.0.1','Symfony','2027-08-10 07:15:37',NULL,'2026-08-10 07:15:37','2026-08-21 01:31:06'),
(132,5,'2ed5abfde8e3f8b362886bb32b180dc17758ecf1a6975153b34e0763afa8a1ad','testing','127.0.0.1','Symfony','2027-08-10 07:15:37',NULL,'2026-08-10 07:15:37','2026-08-21 01:31:06'),
(133,5,'733e317ae09cfd66be1e6b169d9121218c6e45b7546733eb405c030ac505deb6','testing','127.0.0.1','Symfony','2027-08-10 07:15:37',NULL,'2026-08-10 07:15:37','2026-08-21 01:31:06'),
(134,5,'edf2635eaba382ec517699c9606e50c41d4e95a7402943d9ba1614c5e364ddb8','testing','127.0.0.1','Symfony','2027-08-10 07:15:38',NULL,'2026-08-10 07:15:38','2026-08-21 01:31:06'),
(135,5,'d4b2bf10d9f70b7ae15c94c4642f33b2c7c234b661ce2dfe69bfe73bd5d3922b','testing','127.0.0.1','Symfony','2027-08-10 07:15:38',NULL,'2026-08-10 07:15:38','2026-08-21 01:31:06'),
(136,6,'40968deb31348dd2e6e94455b97c94973753799327f65ffafd2126eb1e32d873','testing','127.0.0.1','Symfony','2027-08-10 07:15:38',NULL,'2026-08-10 07:15:38','2026-08-21 01:31:06');
/*!40000 ALTER TABLE `sessions` ENABLE KEYS */;
UNLOCK TABLES;
COMMIT;
SET AUTOCOMMIT=@OLD_AUTOCOMMIT;

--
-- Table structure for table `user_otps`
--

DROP TABLE IF EXISTS `user_otps`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `user_otps` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `user_id` bigint(20) unsigned NOT NULL,
  `purpose` varchar(100) NOT NULL,
  `code_hash` varchar(255) NOT NULL,
  `expires_at` datetime NOT NULL,
  `used_at` datetime DEFAULT NULL,
  `attempts` tinyint(3) unsigned NOT NULL DEFAULT 0,
  `created_at` datetime NOT NULL DEFAULT current_timestamp(),
  `updated_at` datetime NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  PRIMARY KEY (`id`),
  KEY `idx_user_otps_lookup` (`user_id`,`purpose`,`expires_at`,`used_at`),
  CONSTRAINT `fk_user_otps_user` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=2 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `user_otps`
--

SET @OLD_AUTOCOMMIT=@@AUTOCOMMIT, @@AUTOCOMMIT=0;
LOCK TABLES `user_otps` WRITE;
/*!40000 ALTER TABLE `user_otps` DISABLE KEYS */;
INSERT INTO `user_otps` VALUES
(1,25,'payout_account_change','$2y$12$XXKVVhmX30SFyij0k8LH9uv0NM2ZCMS3tVWVI4mWr6Kzr5iFgN.fe','2026-08-08 06:13:32',NULL,0,'2026-08-08 06:08:32','2026-08-08 06:08:32');
/*!40000 ALTER TABLE `user_otps` ENABLE KEYS */;
UNLOCK TABLES;
COMMIT;
SET AUTOCOMMIT=@OLD_AUTOCOMMIT;

--
-- Table structure for table `users`
--

DROP TABLE IF EXISTS `users`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `users` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `full_name` varchar(150) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `email` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `phone` varchar(30) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `password_hash` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `avatar_url` varchar(2048) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `avatar_public_id` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `role` enum('learner','instructor','admin') CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'learner',
  `status` enum('active','inactive','suspended') CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'active',
  `locked` tinyint(1) NOT NULL DEFAULT 0,
  `locked_reason` varchar(500) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `email_verified_at` datetime DEFAULT NULL,
  `last_login_at` datetime DEFAULT NULL,
  `created_at` datetime NOT NULL DEFAULT current_timestamp(),
  `updated_at` datetime NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  PRIMARY KEY (`id`),
  UNIQUE KEY `uq_users_email` (`email`),
  UNIQUE KEY `uq_users_phone` (`phone`),
  UNIQUE KEY `uq_users_avatar_public_id` (`avatar_public_id`),
  KEY `idx_users_role_status` (`role`,`status`)
) ENGINE=InnoDB AUTO_INCREMENT=127 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_uca1400_ai_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `users`
--

SET @OLD_AUTOCOMMIT=@@AUTOCOMMIT, @@AUTOCOMMIT=0;
LOCK TABLES `users` WRITE;
/*!40000 ALTER TABLE `users` DISABLE KEYS */;
INSERT INTO `users` VALUES
(1,'Test User','admin@mindhub.test','0900000000-1','$2y$04$cBExZDm5njzKAiyYALfU/O5N70jaAusLQVx1m1pkINqrKGDIi3e2.',NULL,NULL,'admin','active',0,NULL,'2026-08-10 07:15:24','2026-08-10 07:15:24','2026-08-10 07:15:24','2026-08-10 07:15:24'),
(2,'Instructor Test 1','instructor1@mindhub.test','0900000000-2','$2y$04$JP5ajZn16/5.UeV/2JHAieIOAMmh8gH4jE3Bx7tAx/Pz6CEyysn1C',NULL,NULL,'instructor','active',0,NULL,'2026-08-10 07:15:38','2026-08-10 07:15:24','2026-08-10 07:15:38','2026-08-10 07:15:38'),
(3,'Instructor Test 2','instructor2@mindhub.test','0900000000-3','$2y$04$XO6idvS7k0.NGieW26Jh6ebnxr2r94Lv63YfcLe7dJxMnKPZD.Ste',NULL,NULL,'instructor','active',0,NULL,'2026-08-10 07:15:38','2026-06-21 16:45:00','2026-08-10 07:15:38','2026-08-10 07:15:38'),
(4,'Learner Test 1','learner1@mindhub.test','0900000000-4','$2y$04$g.N7PZF2s9cxJycT6Jx3rOINZ84OgkJlxaoV59c0f.LMxPHxgg0TO',NULL,NULL,'learner','active',0,NULL,'2026-08-10 07:15:38','2026-08-10 07:15:23','2026-08-10 07:15:38','2026-08-10 07:15:38'),
(5,'Phạm Anh Thư','learner2@mindhub.test','0900000005-5','$2y$12$j6h5YFXMJndp1aI7e1GCgeFc.pUUPNyT/tW8bzfHpxz03sUG.OXXC',NULL,NULL,'learner','active',0,NULL,'2026-01-05 09:05:00','2026-08-10 07:15:38','2026-01-02 09:05:00','2026-08-10 07:15:38'),
(6,'Đỗ Hoàng Nam','learner.completed@mindhub.test','0900000006-6','$2y$12$j6h5YFXMJndp1aI7e1GCgeFc.pUUPNyT/tW8bzfHpxz03sUG.OXXC',NULL,NULL,'learner','active',0,NULL,'2026-01-05 09:10:00','2026-08-10 07:15:38','2026-01-02 09:10:00','2026-08-10 07:15:38'),
(7,'Tài khoản bị khóa','locked@mindhub.test','0900000007-7','$2y$12$j6h5YFXMJndp1aI7e1GCgeFc.pUUPNyT/tW8bzfHpxz03sUG.OXXC',NULL,NULL,'learner','suspended',1,'Demo tài khoản bị khóa để test active.user middleware.','2026-01-05 09:15:00','2026-04-01 10:00:00','2026-01-02 09:15:00','2026-04-01 10:00:00'),
(8,'Tài khoản inactive','inactive@mindhub.test','0900000008-8','$2y$12$j6h5YFXMJndp1aI7e1GCgeFc.pUUPNyT/tW8bzfHpxz03sUG.OXXC',NULL,NULL,'learner','inactive',0,NULL,NULL,NULL,'2026-01-02 09:20:00','2026-01-02 09:20:00'),
(9,'OAuth Only User','oauth.only@mindhub.test','0900000004-9','',NULL,NULL,'learner','active',0,NULL,'2026-08-06 17:47:11',NULL,'2026-01-02 09:25:00','2026-08-06 17:47:11'),
(10,'Learner Device Limit','learner.limit@mindhub.test','0900000010-10','$2y$12$j6h5YFXMJndp1aI7e1GCgeFc.pUUPNyT/tW8bzfHpxz03sUG.OXXC',NULL,NULL,'learner','active',0,NULL,'2026-01-05 09:30:00','2026-06-23 06:30:00','2026-01-02 09:30:00','2026-06-23 06:30:00'),
(11,'Learner Empty State','learner.empty@mindhub.test','0900000011-11','$2y$12$j6h5YFXMJndp1aI7e1GCgeFc.pUUPNyT/tW8bzfHpxz03sUG.OXXC',NULL,NULL,'learner','active',0,NULL,'2026-01-05 09:35:00',NULL,'2026-01-02 09:35:00','2026-01-02 09:35:00'),
(12,'Learner Active','learner.active@mindhub.test','0900000001-12','$2y$12$gOSC8RV.fwKNqd/vCRi5MOWThZ.Xom5wSkJ1Ba7j5lRArD0d3T3J2',NULL,NULL,'learner','active',0,NULL,'2026-08-06 17:47:11',NULL,'2026-08-06 17:47:11','2026-08-06 17:47:11'),
(13,'Learner Locked','learner.locked@mindhub.test','0900000002-13','$2y$12$0/z.gTdB7mmdSD7I20tdaObYVbuuORmCIm0XjT9lMGceflTW24Q6K',NULL,NULL,'learner','suspended',1,'Seed locked user for testing','2026-08-06 17:47:11',NULL,'2026-08-06 17:47:11','2026-08-06 17:47:11'),
(14,'Learner Inactive','learner.inactive@mindhub.test','0900000003-14','$2y$12$EZEemJpyuFIbxIHHyW5onOc9V21so6GBLZNLz8PAAj1ClKxIhDZQu',NULL,NULL,'learner','inactive',0,NULL,NULL,NULL,'2026-08-06 17:47:11','2026-08-06 17:47:11'),
(15,'Email Exists User','email.exists@mindhub.test','0900000005-15','$2y$12$k9OV/zVFyiqeFK/FaVg3JuOVlN8bRUA85Ki.b47EBodDoyQj5D936',NULL,NULL,'learner','active',0,NULL,'2026-08-06 17:47:11',NULL,'2026-08-06 17:47:11','2026-08-06 17:47:11'),
(16,'CAT Admin','cat.admin@example.com','0900000000-16','$2y$12$fNWUs3l7Ru0HFgE6zULSyOoT0/JwQHYi0AaXh7MjQuhl3.mWWXHBO',NULL,NULL,'admin','active',0,NULL,'2026-08-06 17:47:12','2026-08-06 17:47:12','2026-08-06 17:47:12','2026-08-06 17:47:12'),
(17,'CAT Instructor Active 01','cat.instructor01@example.com','0900000001-17','$2y$12$S4r7cFSBNQCXO1oV.zO6XuKai06wybdkjH87.XOmizz6MBNypDK1u',NULL,NULL,'instructor','active',0,NULL,'2026-08-06 17:47:12','2026-08-06 17:47:12','2026-08-06 17:47:12','2026-08-06 17:47:12'),
(18,'CAT Instructor Active 02','cat.instructor02@example.com','0900000002-18','$2y$12$wqIX7v4kqakf/huZnPitoeC6a276kJjHoSsk1WmuLyAnLNIhTLdvO',NULL,NULL,'instructor','active',0,NULL,'2026-08-06 17:47:12','2026-08-06 17:47:12','2026-08-06 17:47:12','2026-08-06 17:47:12'),
(19,'CAT Instructor Inactive','cat.instructor.inactive@example.com','0900000003-19','$2y$12$HNXKdaW9ucmykyk9vFWKbu6nGOEDXKod9L0.uKCjcH/FOuyLjyTn.',NULL,NULL,'instructor','inactive',0,NULL,'2026-08-06 17:47:12','2026-08-06 17:47:12','2026-08-06 17:47:12','2026-08-06 17:47:12'),
(20,'CAT Instructor Locked','cat.instructor.locked@example.com','0900000004-20','$2y$12$kYMjWjuUjUKRdInzSJXUcu60bcmUB2i5bbTWpSK.PkLLLkCJ/bTLm',NULL,NULL,'instructor','suspended',1,'Seeder test locked instructor','2026-08-06 17:47:12','2026-08-06 17:47:12','2026-08-06 17:47:12','2026-08-06 17:47:12'),
(21,'CAT Learner 01','cat.learner01@example.com','0910000001-21','$2y$12$AM4P2etMgVGprFkJR/f.ve4cFTNrPI.4.ldA/dEBr8g3myGuCsXGe',NULL,NULL,'learner','active',0,NULL,'2026-08-06 17:47:12','2026-08-06 17:47:12','2026-08-06 17:47:12','2026-08-06 17:47:12'),
(22,'CAT Learner 02','cat.learner02@example.com','0910000002-22','$2y$12$RbmTB3OR8MAHAhvCwHJ/Uew0sVLDU8ek6qF91o2BFbJSGXgqebUgW',NULL,NULL,'learner','active',0,NULL,'2026-08-06 17:47:12','2026-08-06 17:47:12','2026-08-06 17:47:12','2026-08-06 17:47:12'),
(23,'CAT Learner 03','cat.learner03@example.com','0910000003-23','$2y$12$19iJA7KCGR2pQaLMB3e1huUIOBnM7oT0xNMWIzQcxkmGag5IQO5Gy',NULL,NULL,'learner','active',0,NULL,'2026-08-06 17:47:12','2026-08-06 17:47:12','2026-08-06 17:47:12','2026-08-06 17:47:12'),
(24,'Kiran','leduy123@gmail.com','012345678-24','$2y$12$yo9FaWW0gP7jqzW6ZxSKY.lIp6bMZUAZyHN.KHuUNIzXY3w.jDr0e','https://res.cloudinary.com/hcoy6dgr/image/upload/v1787075874/mindhub/avatars/slmfc9vjczwfojdoxrgh.jpg','mindhub/avatars/slmfc9vjczwfojdoxrgh','learner','active',0,NULL,'2026-08-07 01:22:44','2026-08-08 06:27:48','2026-08-07 01:21:10','2026-08-18 17:57:55'),
(25,'nguyen van a','duyle123@gmail.com',NULL,'$2y$12$oF/d2piTOuqKX2yhYUNJWuN4u3voPOvtVqA6qFBPtWXr/tMNP5dxi',NULL,NULL,'instructor','active',0,NULL,'2026-08-08 06:06:35','2026-08-08 06:06:41','2026-08-08 06:05:34','2026-08-08 06:06:41'),
(126,'Joany Ratke','xherman@example.org',NULL,'$2y$04$GEhSRNz9sMsTTXubZvMsae9iW6Hzsed1FXv9vevkkmpHh1WTL5t0y',NULL,NULL,'instructor','active',0,NULL,'2026-08-10 07:15:23',NULL,'2026-08-10 07:15:23','2026-08-10 07:15:23');
/*!40000 ALTER TABLE `users` ENABLE KEYS */;
UNLOCK TABLES;
COMMIT;
SET AUTOCOMMIT=@OLD_AUTOCOMMIT;

--
-- Table structure for table `video_progress`
--

DROP TABLE IF EXISTS `video_progress`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `video_progress` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `enrollment_id` bigint(20) unsigned NOT NULL,
  `lesson_id` bigint(20) unsigned NOT NULL,
  `current_second` int(10) unsigned NOT NULL DEFAULT 0,
  `created_at` datetime NOT NULL DEFAULT current_timestamp(),
  `updated_at` datetime NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  PRIMARY KEY (`id`),
  UNIQUE KEY `uq_video_progress_enrollment_lesson` (`enrollment_id`,`lesson_id`),
  KEY `idx_video_progress_lesson` (`lesson_id`),
  CONSTRAINT `fk_video_progress_enrollment` FOREIGN KEY (`enrollment_id`) REFERENCES `enrollments` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT `fk_video_progress_lesson` FOREIGN KEY (`lesson_id`) REFERENCES `lessons` (`id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=10 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `video_progress`
--

SET @OLD_AUTOCOMMIT=@@AUTOCOMMIT, @@AUTOCOMMIT=0;
LOCK TABLES `video_progress` WRITE;
/*!40000 ALTER TABLE `video_progress` DISABLE KEYS */;
INSERT INTO `video_progress` VALUES
(4,2,1,900,'2026-05-01 08:00:00','2026-05-01 08:20:00'),
(5,2,3,1800,'2026-05-02 08:00:00','2026-05-02 08:45:00'),
(6,2,6,2100,'2026-05-03 08:00:00','2026-05-03 08:50:00'),
(9,1,1,320,'2026-08-10 07:15:37','2026-08-10 07:15:37');
/*!40000 ALTER TABLE `video_progress` ENABLE KEYS */;
UNLOCK TABLES;
COMMIT;
SET AUTOCOMMIT=@OLD_AUTOCOMMIT;

--
-- Table structure for table `wishlist`
--

DROP TABLE IF EXISTS `wishlist`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `wishlist` (
  `user_id` bigint(20) unsigned NOT NULL,
  `course_id` bigint(20) unsigned NOT NULL,
  `created_at` datetime NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`user_id`,`course_id`),
  KEY `idx_wishlist_course` (`course_id`),
  CONSTRAINT `fk_wishlist_course` FOREIGN KEY (`course_id`) REFERENCES `courses` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT `fk_wishlist_user` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `wishlist`
--

SET @OLD_AUTOCOMMIT=@@AUTOCOMMIT, @@AUTOCOMMIT=0;
LOCK TABLES `wishlist` WRITE;
/*!40000 ALTER TABLE `wishlist` DISABLE KEYS */;
INSERT INTO `wishlist` VALUES
(4,3,'2026-06-19 11:00:00'),
(4,6,'2026-06-19 11:05:00'),
(5,1,'2026-06-18 10:00:00'),
(5,3,'2026-06-18 10:05:00'),
(5,6,'2026-06-18 10:10:00');
/*!40000 ALTER TABLE `wishlist` ENABLE KEYS */;
UNLOCK TABLES;
COMMIT;
SET AUTOCOMMIT=@OLD_AUTOCOMMIT;

--
-- Table structure for table `withdraw_requests`
--

DROP TABLE IF EXISTS `withdraw_requests`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `withdraw_requests` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `user_id` bigint(20) unsigned NOT NULL,
  `payout_account_id` bigint(20) unsigned NOT NULL,
  `amount` decimal(15,2) NOT NULL,
  `status` enum('pending','approved','processing','manual_required','paid','rejected') CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'pending',
  `requested_at` datetime NOT NULL DEFAULT current_timestamp(),
  `approved_at` datetime DEFAULT NULL,
  `paid_at` datetime DEFAULT NULL,
  `processed_at` datetime DEFAULT NULL,
  `provider_payout_id` varchar(191) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `failure_reason` varchar(1000) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `rejected_reason` varchar(1000) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `admin_note` varchar(1000) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `account_number_snapshot` varchar(100) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `account_name_snapshot` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `available_balance_before` decimal(15,2) NOT NULL,
  `available_balance_after` decimal(15,2) NOT NULL,
  `bank_name_snapshot` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `payout_provider` varchar(100) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `created_at` datetime NOT NULL DEFAULT current_timestamp(),
  `updated_at` datetime NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  PRIMARY KEY (`id`),
  UNIQUE KEY `uq_withdraw_provider_payout_id` (`provider_payout_id`),
  KEY `idx_withdraw_user_status` (`user_id`,`status`,`requested_at`),
  KEY `idx_withdraw_payout_account` (`payout_account_id`),
  CONSTRAINT `fk_withdraw_payout_account` FOREIGN KEY (`payout_account_id`) REFERENCES `payout_accounts` (`id`) ON UPDATE CASCADE,
  CONSTRAINT `fk_withdraw_user` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON UPDATE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=3 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_uca1400_ai_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `withdraw_requests`
--

SET @OLD_AUTOCOMMIT=@@AUTOCOMMIT, @@AUTOCOMMIT=0;
LOCK TABLES `withdraw_requests` WRITE;
/*!40000 ALTER TABLE `withdraw_requests` DISABLE KEYS */;
INSERT INTO `withdraw_requests` VALUES
(1,2,1,209300.00,'paid','2026-05-05 08:00:00','2026-05-05 10:00:00','2026-05-06 09:00:00',NULL,'PAYOUT-DEMO-0001',NULL,NULL,NULL,'970400000001','NGUYEN MINH KHOA',0.00,0.00,'Unknown Bank',NULL,'2026-05-05 08:00:00','2026-05-06 09:00:00'),
(2,2,1,188370.00,'pending','2026-06-22 08:00:00',NULL,NULL,NULL,NULL,NULL,NULL,NULL,'970400000001','NGUYEN MINH KHOA',0.00,0.00,'Unknown Bank',NULL,'2026-06-22 08:00:00','2026-06-22 08:00:00');
/*!40000 ALTER TABLE `withdraw_requests` ENABLE KEYS */;
UNLOCK TABLES;
COMMIT;
SET AUTOCOMMIT=@OLD_AUTOCOMMIT;

--
-- Table structure for table `withdrawal_revenues`
--

DROP TABLE IF EXISTS `withdrawal_revenues`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `withdrawal_revenues` (
  `withdrawal_id` bigint(20) unsigned NOT NULL,
  `revenue_id` bigint(20) unsigned NOT NULL,
  `allocated_amount` decimal(15,2) NOT NULL,
  `created_at` datetime NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`withdrawal_id`,`revenue_id`),
  KEY `idx_withdrawal_revenues_revenue` (`revenue_id`),
  CONSTRAINT `fk_withdrawal_revenues_revenue` FOREIGN KEY (`revenue_id`) REFERENCES `revenues` (`id`) ON UPDATE CASCADE,
  CONSTRAINT `fk_withdrawal_revenues_withdrawal` FOREIGN KEY (`withdrawal_id`) REFERENCES `withdraw_requests` (`id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_uca1400_ai_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `withdrawal_revenues`
--

SET @OLD_AUTOCOMMIT=@@AUTOCOMMIT, @@AUTOCOMMIT=0;
LOCK TABLES `withdrawal_revenues` WRITE;
/*!40000 ALTER TABLE `withdrawal_revenues` DISABLE KEYS */;
/*!40000 ALTER TABLE `withdrawal_revenues` ENABLE KEYS */;
UNLOCK TABLES;
COMMIT;
SET AUTOCOMMIT=@OLD_AUTOCOMMIT;
/*!40103 SET TIME_ZONE=@OLD_TIME_ZONE */;

/*!40101 SET SQL_MODE=@OLD_SQL_MODE */;
/*!40014 SET FOREIGN_KEY_CHECKS=@OLD_FOREIGN_KEY_CHECKS */;
/*!40014 SET UNIQUE_CHECKS=@OLD_UNIQUE_CHECKS */;
/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
/*M!100616 SET NOTE_VERBOSITY=@OLD_NOTE_VERBOSITY */;

-- Dump completed on 2026-08-21  1:53:51
