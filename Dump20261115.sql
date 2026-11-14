-- MySQL dump 10.13  Distrib 8.0.46, for Win64 (x86_64)
--
-- Host: localhost    Database: base_laravel_web_db_vs1
-- ------------------------------------------------------
-- Server version	8.0.46

/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!50503 SET NAMES utf8 */;
/*!40103 SET @OLD_TIME_ZONE=@@TIME_ZONE */;
/*!40103 SET TIME_ZONE='+00:00' */;
/*!40014 SET @OLD_UNIQUE_CHECKS=@@UNIQUE_CHECKS, UNIQUE_CHECKS=0 */;
/*!40014 SET @OLD_FOREIGN_KEY_CHECKS=@@FOREIGN_KEY_CHECKS, FOREIGN_KEY_CHECKS=0 */;
/*!40101 SET @OLD_SQL_MODE=@@SQL_MODE, SQL_MODE='NO_AUTO_VALUE_ON_ZERO' */;
/*!40111 SET @OLD_SQL_NOTES=@@SQL_NOTES, SQL_NOTES=0 */;

--
-- Table structure for table `article_categories`
--

DROP TABLE IF EXISTS `article_categories`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `article_categories` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `parent_id` bigint unsigned DEFAULT NULL,
  `name` varchar(150) COLLATE utf8mb4_unicode_ci NOT NULL,
  `slug` varchar(160) COLLATE utf8mb4_unicode_ci NOT NULL,
  `description` text COLLATE utf8mb4_unicode_ci,
  `image_id` bigint unsigned DEFAULT NULL,
  `meta_title` varchar(160) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `meta_description` varchar(320) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `sort_order` smallint unsigned NOT NULL DEFAULT '0',
  `is_active` tinyint(1) NOT NULL DEFAULT '1',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `meta_key` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `no_index` tinyint(1) NOT NULL DEFAULT '0',
  `no_follow` tinyint(1) NOT NULL DEFAULT '0',
  `public_by` bigint unsigned DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `article_categories_name_unique` (`name`),
  UNIQUE KEY `article_categories_slug_unique` (`slug`),
  KEY `article_categories_image_id_foreign` (`image_id`),
  KEY `article_categories_parent_id_foreign` (`parent_id`),
  KEY `article_categories_public_by_foreign` (`public_by`),
  CONSTRAINT `article_categories_image_id_foreign` FOREIGN KEY (`image_id`) REFERENCES `medias` (`id`) ON DELETE SET NULL,
  CONSTRAINT `article_categories_parent_id_foreign` FOREIGN KEY (`parent_id`) REFERENCES `article_categories` (`id`) ON DELETE RESTRICT,
  CONSTRAINT `article_categories_public_by_foreign` FOREIGN KEY (`public_by`) REFERENCES `users` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB AUTO_INCREMENT=4 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `article_categories`
--

LOCK TABLES `article_categories` WRITE;
/*!40000 ALTER TABLE `article_categories` DISABLE KEYS */;
INSERT INTO `article_categories` VALUES (1,NULL,'Tin tức báo chí','tin-tuc-bao-chi',NULL,NULL,NULL,NULL,0,1,'2026-11-13 13:31:34','2026-11-13 19:15:50',NULL,0,0,NULL),(2,NULL,'Tin tức tuyển dụng','tin-tuc-tuyen-dung',NULL,NULL,NULL,NULL,0,1,'2026-11-13 19:43:35','2026-11-13 19:43:35',NULL,0,0,NULL),(3,2,'Tin tuyển dụng IT1','tin-tuyen-dung-it1',NULL,NULL,NULL,NULL,0,1,'2026-11-13 19:49:27','2026-11-13 19:49:27',NULL,0,0,NULL);
/*!40000 ALTER TABLE `article_categories` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `article_category_article`
--

DROP TABLE IF EXISTS `article_category_article`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `article_category_article` (
  `article_id` bigint unsigned NOT NULL,
  `category_id` bigint unsigned NOT NULL,
  PRIMARY KEY (`article_id`,`category_id`),
  KEY `article_category_article_category_id_index` (`category_id`),
  CONSTRAINT `article_category_article_article_id_foreign` FOREIGN KEY (`article_id`) REFERENCES `articles` (`id`) ON DELETE CASCADE,
  CONSTRAINT `article_category_article_category_id_foreign` FOREIGN KEY (`category_id`) REFERENCES `article_categories` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `article_category_article`
--

LOCK TABLES `article_category_article` WRITE;
/*!40000 ALTER TABLE `article_category_article` DISABLE KEYS */;
INSERT INTO `article_category_article` VALUES (1,2),(1,3);
/*!40000 ALTER TABLE `article_category_article` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `article_tag`
--

DROP TABLE IF EXISTS `article_tag`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `article_tag` (
  `article_id` bigint unsigned NOT NULL,
  `tag_id` bigint unsigned NOT NULL,
  PRIMARY KEY (`article_id`,`tag_id`),
  KEY `article_tag_tag_id_index` (`tag_id`),
  CONSTRAINT `article_tag_article_id_foreign` FOREIGN KEY (`article_id`) REFERENCES `articles` (`id`) ON DELETE CASCADE,
  CONSTRAINT `article_tag_tag_id_foreign` FOREIGN KEY (`tag_id`) REFERENCES `tags` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `article_tag`
--

LOCK TABLES `article_tag` WRITE;
/*!40000 ALTER TABLE `article_tag` DISABLE KEYS */;
INSERT INTO `article_tag` VALUES (1,1);
/*!40000 ALTER TABLE `article_tag` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `articles`
--

DROP TABLE IF EXISTS `articles`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `articles` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `category_id` bigint unsigned NOT NULL,
  `title` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `slug` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `excerpt` text COLLATE utf8mb4_unicode_ci,
  `content` longtext COLLATE utf8mb4_unicode_ci,
  `image_id` bigint unsigned DEFAULT NULL,
  `meta_title` varchar(160) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `meta_description` varchar(320) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `is_active` tinyint(1) NOT NULL DEFAULT '1',
  `published_at` timestamp NULL DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `meta_key` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `no_index` tinyint(1) NOT NULL DEFAULT '0',
  `no_follow` tinyint(1) NOT NULL DEFAULT '0',
  `public_by` bigint unsigned DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `articles_slug_unique` (`slug`),
  KEY `articles_category_id_foreign` (`category_id`),
  KEY `articles_image_id_foreign` (`image_id`),
  KEY `articles_is_active_published_at_index` (`is_active`,`published_at`),
  KEY `articles_public_by_foreign` (`public_by`),
  CONSTRAINT `articles_category_id_foreign` FOREIGN KEY (`category_id`) REFERENCES `article_categories` (`id`) ON DELETE RESTRICT,
  CONSTRAINT `articles_image_id_foreign` FOREIGN KEY (`image_id`) REFERENCES `medias` (`id`) ON DELETE SET NULL,
  CONSTRAINT `articles_public_by_foreign` FOREIGN KEY (`public_by`) REFERENCES `users` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB AUTO_INCREMENT=2 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `articles`
--

LOCK TABLES `articles` WRITE;
/*!40000 ALTER TABLE `articles` DISABLE KEYS */;
INSERT INTO `articles` VALUES (1,3,'Xe đạp và khoảng trống di chuyển 3-5 km trong đô thị','xe-dap-va-khoang-trong-di-chuyen-3-5-km-trong-do-thi',NULL,NULL,NULL,NULL,NULL,1,'2026-11-13 13:32:00','2026-11-13 13:32:11','2026-11-13 19:49:42',NULL,0,0,1);
/*!40000 ALTER TABLE `articles` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `brands`
--

DROP TABLE IF EXISTS `brands`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `brands` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `name` varchar(150) COLLATE utf8mb4_unicode_ci NOT NULL,
  `slug` varchar(160) COLLATE utf8mb4_unicode_ci NOT NULL,
  `description` text COLLATE utf8mb4_unicode_ci,
  `image_id` bigint unsigned DEFAULT NULL,
  `sort_order` smallint unsigned NOT NULL DEFAULT '0',
  `is_active` tinyint(1) NOT NULL DEFAULT '1',
  `meta_title` varchar(160) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `meta_description` varchar(320) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `meta_key` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `no_index` tinyint(1) NOT NULL DEFAULT '0',
  `no_follow` tinyint(1) NOT NULL DEFAULT '0',
  `public_by` bigint unsigned DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `brands_name_unique` (`name`),
  UNIQUE KEY `brands_slug_unique` (`slug`),
  KEY `brands_image_id_foreign` (`image_id`),
  KEY `brands_public_by_foreign` (`public_by`),
  CONSTRAINT `brands_image_id_foreign` FOREIGN KEY (`image_id`) REFERENCES `medias` (`id`) ON DELETE SET NULL,
  CONSTRAINT `brands_public_by_foreign` FOREIGN KEY (`public_by`) REFERENCES `users` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB AUTO_INCREMENT=14 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `brands`
--

LOCK TABLES `brands` WRITE;
/*!40000 ALTER TABLE `brands` DISABLE KEYS */;
INSERT INTO `brands` VALUES (1,'Giant','giant',NULL,4,0,1,NULL,NULL,'2026-11-12 18:12:22','2026-11-13 11:28:00',NULL,0,0,NULL),(2,'Trek','trek',NULL,NULL,1,1,NULL,NULL,'2026-11-12 18:12:22','2026-11-12 18:12:22',NULL,0,0,NULL),(3,'Specialized','specialized',NULL,NULL,2,1,NULL,NULL,'2026-11-12 18:12:22','2026-11-12 18:12:22',NULL,0,0,NULL),(4,'Cannondale','cannondale',NULL,NULL,3,1,NULL,NULL,'2026-11-12 18:12:22','2026-11-12 18:12:22',NULL,0,0,NULL),(5,'Merida','merida',NULL,NULL,4,1,NULL,NULL,'2026-11-12 18:12:22','2026-11-12 18:12:22',NULL,0,0,NULL),(6,'Scott','scott',NULL,NULL,5,1,NULL,NULL,'2026-11-12 18:12:22','2026-11-12 18:12:22',NULL,0,0,NULL),(7,'Shimano','shimano',NULL,NULL,6,1,NULL,NULL,'2026-11-12 18:12:22','2026-11-12 18:12:22',NULL,0,0,NULL),(8,'SRAM','sram',NULL,NULL,7,1,NULL,NULL,'2026-11-12 18:12:22','2026-11-12 18:12:22',NULL,0,0,NULL),(9,'Campagnolo','campagnolo',NULL,NULL,8,1,NULL,NULL,'2026-11-12 18:12:22','2026-11-12 18:12:22',NULL,0,0,NULL),(10,'Fox','fox',NULL,NULL,9,1,NULL,NULL,'2026-11-12 18:12:22','2026-11-12 18:12:22',NULL,0,0,NULL),(11,'RockShox','rockshox',NULL,NULL,10,1,NULL,NULL,'2026-11-12 18:12:22','2026-11-12 18:12:22',NULL,0,0,NULL),(12,'Continental','continental',NULL,NULL,11,1,NULL,NULL,'2026-11-12 18:12:22','2026-11-12 18:12:22',NULL,0,0,NULL),(13,'Maxxis','maxxis',NULL,NULL,12,1,NULL,NULL,'2026-11-12 18:12:22','2026-11-12 18:12:22',NULL,0,0,NULL);
/*!40000 ALTER TABLE `brands` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `cache`
--

DROP TABLE IF EXISTS `cache`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `cache` (
  `key` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `value` mediumtext COLLATE utf8mb4_unicode_ci NOT NULL,
  `expiration` int NOT NULL,
  PRIMARY KEY (`key`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `cache`
--

LOCK TABLES `cache` WRITE;
/*!40000 ALTER TABLE `cache` DISABLE KEYS */;
INSERT INTO `cache` VALUES ('base_laravel_web_cache_article_categories.parent_map','a:3:{i:1;N;i:2;N;i:3;i:2;}',2110017298),('base_laravel_web_cache_languages.all','O:39:\"Illuminate\\Database\\Eloquent\\Collection\":2:{s:8:\"\0*\0items\";a:2:{i:0;O:19:\"App\\Models\\Language\":33:{s:13:\"\0*\0connection\";s:5:\"mysql\";s:8:\"\0*\0table\";s:9:\"languages\";s:13:\"\0*\0primaryKey\";s:2:\"id\";s:10:\"\0*\0keyType\";s:3:\"int\";s:12:\"incrementing\";b:1;s:7:\"\0*\0with\";a:0:{}s:12:\"\0*\0withCount\";a:0:{}s:19:\"preventsLazyLoading\";b:0;s:10:\"\0*\0perPage\";i:15;s:6:\"exists\";b:1;s:18:\"wasRecentlyCreated\";b:0;s:28:\"\0*\0escapeWhenCastingToString\";b:0;s:13:\"\0*\0attributes\";a:10:{s:2:\"id\";i:1;s:4:\"code\";s:2:\"vi\";s:6:\"prefix\";s:2:\"vn\";s:4:\"name\";s:14:\"Tiếng Việt\";s:4:\"flag\";s:2:\"vn\";s:10:\"sort_order\";i:1;s:10:\"is_default\";i:1;s:9:\"is_active\";i:1;s:10:\"created_at\";s:19:\"2026-11-14 23:19:01\";s:10:\"updated_at\";s:19:\"2026-11-14 23:19:01\";}s:11:\"\0*\0original\";a:10:{s:2:\"id\";i:1;s:4:\"code\";s:2:\"vi\";s:6:\"prefix\";s:2:\"vn\";s:4:\"name\";s:14:\"Tiếng Việt\";s:4:\"flag\";s:2:\"vn\";s:10:\"sort_order\";i:1;s:10:\"is_default\";i:1;s:9:\"is_active\";i:1;s:10:\"created_at\";s:19:\"2026-11-14 23:19:01\";s:10:\"updated_at\";s:19:\"2026-11-14 23:19:01\";}s:10:\"\0*\0changes\";a:0:{}s:11:\"\0*\0previous\";a:0:{}s:8:\"\0*\0casts\";a:2:{s:10:\"is_default\";s:7:\"boolean\";s:9:\"is_active\";s:7:\"boolean\";}s:17:\"\0*\0classCastCache\";a:0:{}s:21:\"\0*\0attributeCastCache\";a:0:{}s:13:\"\0*\0dateFormat\";N;s:10:\"\0*\0appends\";a:0:{}s:19:\"\0*\0dispatchesEvents\";a:0:{}s:14:\"\0*\0observables\";a:0:{}s:12:\"\0*\0relations\";a:0:{}s:10:\"\0*\0touches\";a:0:{}s:27:\"\0*\0relationAutoloadCallback\";N;s:26:\"\0*\0relationAutoloadContext\";N;s:10:\"timestamps\";b:1;s:13:\"usesUniqueIds\";b:0;s:9:\"\0*\0hidden\";a:0:{}s:10:\"\0*\0visible\";a:0:{}s:11:\"\0*\0fillable\";a:7:{i:0;s:4:\"code\";i:1;s:6:\"prefix\";i:2;s:4:\"name\";i:3;s:4:\"flag\";i:4;s:10:\"sort_order\";i:5;s:10:\"is_default\";i:6;s:9:\"is_active\";}s:10:\"\0*\0guarded\";a:1:{i:0;s:1:\"*\";}}i:1;O:19:\"App\\Models\\Language\":33:{s:13:\"\0*\0connection\";s:5:\"mysql\";s:8:\"\0*\0table\";s:9:\"languages\";s:13:\"\0*\0primaryKey\";s:2:\"id\";s:10:\"\0*\0keyType\";s:3:\"int\";s:12:\"incrementing\";b:1;s:7:\"\0*\0with\";a:0:{}s:12:\"\0*\0withCount\";a:0:{}s:19:\"preventsLazyLoading\";b:0;s:10:\"\0*\0perPage\";i:15;s:6:\"exists\";b:1;s:18:\"wasRecentlyCreated\";b:0;s:28:\"\0*\0escapeWhenCastingToString\";b:0;s:13:\"\0*\0attributes\";a:10:{s:2:\"id\";i:2;s:4:\"code\";s:2:\"en\";s:6:\"prefix\";s:2:\"en\";s:4:\"name\";s:7:\"English\";s:4:\"flag\";s:2:\"gb\";s:10:\"sort_order\";i:2;s:10:\"is_default\";i:0;s:9:\"is_active\";i:1;s:10:\"created_at\";s:19:\"2026-11-14 23:19:01\";s:10:\"updated_at\";s:19:\"2026-11-14 23:19:01\";}s:11:\"\0*\0original\";a:10:{s:2:\"id\";i:2;s:4:\"code\";s:2:\"en\";s:6:\"prefix\";s:2:\"en\";s:4:\"name\";s:7:\"English\";s:4:\"flag\";s:2:\"gb\";s:10:\"sort_order\";i:2;s:10:\"is_default\";i:0;s:9:\"is_active\";i:1;s:10:\"created_at\";s:19:\"2026-11-14 23:19:01\";s:10:\"updated_at\";s:19:\"2026-11-14 23:19:01\";}s:10:\"\0*\0changes\";a:0:{}s:11:\"\0*\0previous\";a:0:{}s:8:\"\0*\0casts\";a:2:{s:10:\"is_default\";s:7:\"boolean\";s:9:\"is_active\";s:7:\"boolean\";}s:17:\"\0*\0classCastCache\";a:0:{}s:21:\"\0*\0attributeCastCache\";a:0:{}s:13:\"\0*\0dateFormat\";N;s:10:\"\0*\0appends\";a:0:{}s:19:\"\0*\0dispatchesEvents\";a:0:{}s:14:\"\0*\0observables\";a:0:{}s:12:\"\0*\0relations\";a:0:{}s:10:\"\0*\0touches\";a:0:{}s:27:\"\0*\0relationAutoloadCallback\";N;s:26:\"\0*\0relationAutoloadContext\";N;s:10:\"timestamps\";b:1;s:13:\"usesUniqueIds\";b:0;s:9:\"\0*\0hidden\";a:0:{}s:10:\"\0*\0visible\";a:0:{}s:11:\"\0*\0fillable\";a:7:{i:0;s:4:\"code\";i:1;s:6:\"prefix\";i:2;s:4:\"name\";i:3;s:4:\"flag\";i:4;s:10:\"sort_order\";i:5;s:10:\"is_default\";i:6;s:9:\"is_active\";}s:10:\"\0*\0guarded\";a:1:{i:0;s:1:\"*\";}}}s:28:\"\0*\0escapeWhenCastingToString\";b:0;}',2110033611),('base_laravel_web_cache_web.page_cache_version','i:17;',2110017298);
/*!40000 ALTER TABLE `cache` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `cache_locks`
--

DROP TABLE IF EXISTS `cache_locks`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `cache_locks` (
  `key` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `owner` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `expiration` int NOT NULL,
  PRIMARY KEY (`key`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `cache_locks`
--

LOCK TABLES `cache_locks` WRITE;
/*!40000 ALTER TABLE `cache_locks` DISABLE KEYS */;
/*!40000 ALTER TABLE `cache_locks` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `failed_jobs`
--

DROP TABLE IF EXISTS `failed_jobs`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `failed_jobs` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `uuid` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `connection` text COLLATE utf8mb4_unicode_ci NOT NULL,
  `queue` text COLLATE utf8mb4_unicode_ci NOT NULL,
  `payload` longtext COLLATE utf8mb4_unicode_ci NOT NULL,
  `exception` longtext COLLATE utf8mb4_unicode_ci NOT NULL,
  `failed_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `failed_jobs_uuid_unique` (`uuid`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `failed_jobs`
--

LOCK TABLES `failed_jobs` WRITE;
/*!40000 ALTER TABLE `failed_jobs` DISABLE KEYS */;
/*!40000 ALTER TABLE `failed_jobs` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `job_batches`
--

DROP TABLE IF EXISTS `job_batches`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `job_batches` (
  `id` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `name` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `total_jobs` int NOT NULL,
  `pending_jobs` int NOT NULL,
  `failed_jobs` int NOT NULL,
  `failed_job_ids` longtext COLLATE utf8mb4_unicode_ci NOT NULL,
  `options` mediumtext COLLATE utf8mb4_unicode_ci,
  `cancelled_at` int DEFAULT NULL,
  `created_at` int NOT NULL,
  `finished_at` int DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `job_batches`
--

LOCK TABLES `job_batches` WRITE;
/*!40000 ALTER TABLE `job_batches` DISABLE KEYS */;
/*!40000 ALTER TABLE `job_batches` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `jobs`
--

DROP TABLE IF EXISTS `jobs`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `jobs` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `queue` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `payload` longtext COLLATE utf8mb4_unicode_ci NOT NULL,
  `attempts` tinyint unsigned NOT NULL,
  `reserved_at` int unsigned DEFAULT NULL,
  `available_at` int unsigned NOT NULL,
  `created_at` int unsigned NOT NULL,
  PRIMARY KEY (`id`),
  KEY `jobs_queue_index` (`queue`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `jobs`
--

LOCK TABLES `jobs` WRITE;
/*!40000 ALTER TABLE `jobs` DISABLE KEYS */;
/*!40000 ALTER TABLE `jobs` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `languages`
--

DROP TABLE IF EXISTS `languages`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `languages` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `code` varchar(10) COLLATE utf8mb4_unicode_ci NOT NULL,
  `prefix` varchar(10) COLLATE utf8mb4_unicode_ci NOT NULL,
  `name` varchar(60) COLLATE utf8mb4_unicode_ci NOT NULL,
  `flag` varchar(5) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `sort_order` smallint unsigned NOT NULL DEFAULT '0',
  `is_default` tinyint(1) NOT NULL DEFAULT '0',
  `is_active` tinyint(1) NOT NULL DEFAULT '1',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `languages_code_unique` (`code`),
  UNIQUE KEY `languages_prefix_unique` (`prefix`)
) ENGINE=InnoDB AUTO_INCREMENT=3 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `languages`
--

LOCK TABLES `languages` WRITE;
/*!40000 ALTER TABLE `languages` DISABLE KEYS */;
INSERT INTO `languages` VALUES (1,'vi','vn','Tiếng Việt','vn',1,1,1,'2026-11-14 16:19:01','2026-11-14 16:19:01'),(2,'en','en','English','gb',2,0,1,'2026-11-14 16:19:01','2026-11-14 16:19:01');
/*!40000 ALTER TABLE `languages` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `medias`
--

DROP TABLE IF EXISTS `medias`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `medias` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `name` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `type` varchar(20) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'image',
  `extension` varchar(10) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `title` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `alt` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `path` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `webp_path` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `thumb_400` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `thumb_800` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `mime` varchar(100) COLLATE utf8mb4_unicode_ci NOT NULL,
  `size` bigint unsigned NOT NULL,
  `width` int unsigned DEFAULT NULL,
  `height` int unsigned DEFAULT NULL,
  `uploaded_by` bigint unsigned DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `deleted_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `medias_uploaded_by_foreign` (`uploaded_by`),
  KEY `medias_type_index` (`type`),
  CONSTRAINT `medias_uploaded_by_foreign` FOREIGN KEY (`uploaded_by`) REFERENCES `users` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB AUTO_INCREMENT=5 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `medias`
--

LOCK TABLES `medias` WRITE;
/*!40000 ALTER TABLE `medias` DISABLE KEYS */;
INSERT INTO `medias` VALUES (3,'Mau-Phieu-Xuat-Kho-Vs1.png','image','png',NULL,NULL,'medias/2026/11/jLp2w3PQs0jUdCToDHFzZAr87imEjdBLQQ9Wbf67.png','medias/2026/11/jLp2w3PQs0jUdCToDHFzZAr87imEjdBLQQ9Wbf67.webp','medias/2026/11/jLp2w3PQs0jUdCToDHFzZAr87imEjdBLQQ9Wbf67-400.png',NULL,'image/png',89028,713,797,1,'2026-11-13 11:26:55','2026-11-13 11:26:55',NULL),(4,'anh-mau-phieu-nhap-kho-vs1.jpg','image','jpg',NULL,NULL,'medias/2026/11/kELY5UzE7UwYGmr6TKl8t8cGb6WcqiHrAvMyXmEP.jpg','medias/2026/11/kELY5UzE7UwYGmr6TKl8t8cGb6WcqiHrAvMyXmEP.webp','medias/2026/11/kELY5UzE7UwYGmr6TKl8t8cGb6WcqiHrAvMyXmEP-400.jpg',NULL,'image/jpeg',46683,546,689,1,'2026-11-13 11:27:28','2026-11-13 11:27:28',NULL);
/*!40000 ALTER TABLE `medias` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `migrations`
--

DROP TABLE IF EXISTS `migrations`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `migrations` (
  `id` int unsigned NOT NULL AUTO_INCREMENT,
  `migration` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `batch` int NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=27 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `migrations`
--

LOCK TABLES `migrations` WRITE;
/*!40000 ALTER TABLE `migrations` DISABLE KEYS */;
INSERT INTO `migrations` VALUES (1,'0001_01_01_000000_create_users_table',1),(2,'0001_01_01_000001_create_cache_table',1),(3,'0001_01_01_000002_create_jobs_table',1),(4,'2026_11_12_172747_create_brands_table',2),(5,'2026_11_13_000001_create_roles_table',3),(6,'2026_11_13_000002_add_account_fields_to_users_table',3),(7,'2026_11_13_000003_create_modules_table',4),(8,'2026_11_13_000004_create_permissions_table',4),(9,'2026_11_13_000005_create_permission_role_table',4),(10,'2026_11_13_000006_add_is_system_to_roles_table',4),(11,'2026_11_14_000001_create_medias_table',5),(12,'2026_11_14_000002_add_file_fields_and_soft_deletes_to_medias_table',6),(13,'2026_11_14_000003_add_image_id_to_brands_table',7),(14,'2026_11_15_000001_create_article_categories_table',8),(15,'2026_11_15_000002_create_articles_table',8),(16,'2026_11_15_000003_add_seo_to_brands_table',9),(17,'2026_11_15_000003_add_parent_id_to_article_categories_table',10),(18,'2026_11_15_000004_create_article_category_article_table',11),(19,'2026_11_15_000005_create_tags_table',12),(20,'2026_11_15_000006_create_article_tag_table',12),(21,'2026_11_16_000001_create_product_categories_table',13),(22,'2026_11_16_000002_create_product_tags_table',13),(23,'2026_11_16_000003_create_products_table',13),(24,'2026_11_16_000004_create_product_pivot_tables',13),(25,'2026_11_17_000001_add_seo_and_publisher_fields',14),(26,'2026_11_18_000001_create_languages_table',15);
/*!40000 ALTER TABLE `migrations` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `modules`
--

DROP TABLE IF EXISTS `modules`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `modules` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `code` varchar(50) COLLATE utf8mb4_unicode_ci NOT NULL,
  `name` varchar(100) COLLATE utf8mb4_unicode_ci NOT NULL,
  `sort_order` smallint unsigned NOT NULL DEFAULT '0',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `modules_code_unique` (`code`)
) ENGINE=InnoDB AUTO_INCREMENT=13 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `modules`
--

LOCK TABLES `modules` WRITE;
/*!40000 ALTER TABLE `modules` DISABLE KEYS */;
INSERT INTO `modules` VALUES (1,'brands','Thương hiệu',0,'2026-11-13 04:56:41','2026-11-13 04:56:41'),(2,'accounts','Tài khoản',9,'2026-11-13 04:56:41','2026-11-14 16:25:05'),(3,'roles','Vai trò',10,'2026-11-13 04:56:41','2026-11-14 16:25:05'),(4,'medias','Thư viện ảnh',11,'2026-11-13 09:13:45','2026-11-14 16:25:05'),(5,'article-categories','Danh mục bài viết',1,'2026-11-13 13:25:54','2026-11-13 13:25:54'),(6,'articles','Bài viết',3,'2026-11-13 13:25:54','2026-11-13 20:43:48'),(7,'cache','Bộ nhớ đệm',7,'2026-11-13 20:36:04','2026-11-14 09:05:22'),(8,'tags','Thẻ bài viết',2,'2026-11-13 20:43:48','2026-11-13 20:43:48'),(9,'product-categories','Danh mục sản phẩm',4,'2026-11-14 09:05:22','2026-11-14 09:05:22'),(10,'products','Sản phẩm',5,'2026-11-14 09:05:22','2026-11-14 09:05:22'),(11,'product-tags','Thẻ sản phẩm',6,'2026-11-14 09:05:22','2026-11-14 09:05:22'),(12,'languages','Ngôn ngữ',8,'2026-11-14 16:25:05','2026-11-14 16:25:05');
/*!40000 ALTER TABLE `modules` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `password_reset_tokens`
--

DROP TABLE IF EXISTS `password_reset_tokens`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `password_reset_tokens` (
  `email` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `token` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`email`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `password_reset_tokens`
--

LOCK TABLES `password_reset_tokens` WRITE;
/*!40000 ALTER TABLE `password_reset_tokens` DISABLE KEYS */;
/*!40000 ALTER TABLE `password_reset_tokens` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `permission_role`
--

DROP TABLE IF EXISTS `permission_role`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `permission_role` (
  `role_id` bigint unsigned NOT NULL,
  `permission_id` bigint unsigned NOT NULL,
  PRIMARY KEY (`role_id`,`permission_id`),
  KEY `permission_role_permission_id_foreign` (`permission_id`),
  CONSTRAINT `permission_role_permission_id_foreign` FOREIGN KEY (`permission_id`) REFERENCES `permissions` (`id`) ON DELETE CASCADE,
  CONSTRAINT `permission_role_role_id_foreign` FOREIGN KEY (`role_id`) REFERENCES `roles` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `permission_role`
--

LOCK TABLES `permission_role` WRITE;
/*!40000 ALTER TABLE `permission_role` DISABLE KEYS */;
INSERT INTO `permission_role` VALUES (2,1),(2,2),(2,3),(2,4),(2,5),(2,6),(2,7),(2,8),(2,9),(2,10),(2,11),(2,12),(2,13),(2,14),(2,15),(2,16),(2,17),(2,18),(2,19),(2,20),(2,21);
/*!40000 ALTER TABLE `permission_role` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `permissions`
--

DROP TABLE IF EXISTS `permissions`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `permissions` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `module_id` bigint unsigned NOT NULL,
  `action` varchar(100) COLLATE utf8mb4_unicode_ci NOT NULL,
  `code` varchar(150) COLLATE utf8mb4_unicode_ci NOT NULL,
  `name` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `permissions_module_id_action_unique` (`module_id`,`action`),
  UNIQUE KEY `permissions_code_unique` (`code`),
  CONSTRAINT `permissions_module_id_foreign` FOREIGN KEY (`module_id`) REFERENCES `modules` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=53 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `permissions`
--

LOCK TABLES `permissions` WRITE;
/*!40000 ALTER TABLE `permissions` DISABLE KEYS */;
INSERT INTO `permissions` VALUES (1,1,'view','brands.view','Xem','2026-11-13 04:56:41','2026-11-13 04:56:41'),(2,1,'create','brands.create','Thêm','2026-11-13 04:56:41','2026-11-13 04:56:41'),(3,1,'update','brands.update','Sửa','2026-11-13 04:56:41','2026-11-13 04:56:41'),(4,1,'delete','brands.delete','Xóa','2026-11-13 04:56:41','2026-11-13 04:56:41'),(5,2,'view','accounts.view','Xem','2026-11-13 04:56:41','2026-11-13 04:56:41'),(6,2,'create','accounts.create','Thêm','2026-11-13 04:56:41','2026-11-13 04:56:41'),(7,2,'update','accounts.update','Sửa','2026-11-13 04:56:41','2026-11-13 04:56:41'),(8,2,'delete','accounts.delete','Xóa','2026-11-13 04:56:41','2026-11-13 04:56:41'),(9,3,'view','roles.view','Xem','2026-11-13 04:56:41','2026-11-13 04:56:41'),(10,3,'create','roles.create','Thêm','2026-11-13 04:56:41','2026-11-13 04:56:41'),(11,3,'update','roles.update','Sửa','2026-11-13 04:56:41','2026-11-13 04:56:41'),(12,3,'delete','roles.delete','Xóa','2026-11-13 04:56:41','2026-11-13 04:56:41'),(13,3,'permissions.update','roles.permissions.update','Gán quyền cho vai trò','2026-11-13 04:56:41','2026-11-13 04:56:41'),(14,4,'view','medias.view','Xem','2026-11-13 09:13:45','2026-11-13 09:13:45'),(15,4,'create','medias.create','Thêm','2026-11-13 09:13:45','2026-11-13 09:13:45'),(16,4,'update','medias.update','Sửa','2026-11-13 09:13:45','2026-11-13 09:13:45'),(17,4,'delete','medias.delete','Xóa','2026-11-13 09:13:45','2026-11-13 09:13:45'),(18,4,'trash.view','medias.trash.view','Xem thùng rác','2026-11-13 09:58:50','2026-11-13 09:58:50'),(19,4,'trash.restore','medias.trash.restore','Khôi phục tệp từ thùng rác','2026-11-13 09:58:50','2026-11-13 09:58:50'),(20,4,'trash.delete','medias.trash.delete','Xóa vĩnh viễn tệp trong thùng rác','2026-11-13 09:58:50','2026-11-13 09:58:50'),(21,4,'trash.empty','medias.trash.empty','Dọn sạch thùng rác','2026-11-13 09:58:50','2026-11-13 09:58:50'),(22,5,'view','article-categories.view','Xem','2026-11-13 13:25:54','2026-11-13 13:25:54'),(23,5,'create','article-categories.create','Thêm','2026-11-13 13:25:54','2026-11-13 13:25:54'),(24,5,'update','article-categories.update','Sửa','2026-11-13 13:25:54','2026-11-13 13:25:54'),(25,5,'delete','article-categories.delete','Xóa','2026-11-13 13:25:54','2026-11-13 13:25:54'),(26,6,'view','articles.view','Xem','2026-11-13 13:25:54','2026-11-13 13:25:54'),(27,6,'create','articles.create','Thêm','2026-11-13 13:25:54','2026-11-13 13:25:54'),(28,6,'update','articles.update','Sửa','2026-11-13 13:25:54','2026-11-13 13:25:54'),(29,6,'delete','articles.delete','Xóa','2026-11-13 13:25:54','2026-11-13 13:25:54'),(30,7,'view','cache.view','Xem','2026-11-13 20:36:04','2026-11-13 20:36:04'),(31,7,'clear-web','cache.clear-web','Xóa cache trang web','2026-11-13 20:36:04','2026-11-13 20:36:04'),(32,7,'clear-admin','cache.clear-admin','Xóa cache quản trị','2026-11-13 20:36:04','2026-11-13 20:36:04'),(33,8,'view','tags.view','Xem','2026-11-13 20:43:48','2026-11-13 20:43:48'),(34,8,'create','tags.create','Thêm','2026-11-13 20:43:48','2026-11-13 20:43:48'),(35,8,'update','tags.update','Sửa','2026-11-13 20:43:48','2026-11-13 20:43:48'),(36,8,'delete','tags.delete','Xóa','2026-11-13 20:43:48','2026-11-13 20:43:48'),(37,9,'view','product-categories.view','Xem','2026-11-14 09:05:22','2026-11-14 09:05:22'),(38,9,'create','product-categories.create','Thêm','2026-11-14 09:05:22','2026-11-14 09:05:22'),(39,9,'update','product-categories.update','Sửa','2026-11-14 09:05:22','2026-11-14 09:05:22'),(40,9,'delete','product-categories.delete','Xóa','2026-11-14 09:05:22','2026-11-14 09:05:22'),(41,10,'view','products.view','Xem','2026-11-14 09:05:22','2026-11-14 09:05:22'),(42,10,'create','products.create','Thêm','2026-11-14 09:05:22','2026-11-14 09:05:22'),(43,10,'update','products.update','Sửa','2026-11-14 09:05:22','2026-11-14 09:05:22'),(44,10,'delete','products.delete','Xóa','2026-11-14 09:05:22','2026-11-14 09:05:22'),(45,11,'view','product-tags.view','Xem','2026-11-14 09:05:22','2026-11-14 09:05:22'),(46,11,'create','product-tags.create','Thêm','2026-11-14 09:05:22','2026-11-14 09:05:22'),(47,11,'update','product-tags.update','Sửa','2026-11-14 09:05:22','2026-11-14 09:05:22'),(48,11,'delete','product-tags.delete','Xóa','2026-11-14 09:05:22','2026-11-14 09:05:22'),(49,12,'view','languages.view','Xem','2026-11-14 16:25:05','2026-11-14 16:25:05'),(50,12,'create','languages.create','Thêm','2026-11-14 16:25:05','2026-11-14 16:25:05'),(51,12,'update','languages.update','Sửa','2026-11-14 16:25:05','2026-11-14 16:25:05'),(52,12,'default','languages.default','Đặt ngôn ngữ chính','2026-11-14 16:25:05','2026-11-14 16:25:05');
/*!40000 ALTER TABLE `permissions` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `product_categories`
--

DROP TABLE IF EXISTS `product_categories`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `product_categories` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `parent_id` bigint unsigned DEFAULT NULL,
  `name` varchar(150) COLLATE utf8mb4_unicode_ci NOT NULL,
  `slug` varchar(160) COLLATE utf8mb4_unicode_ci NOT NULL,
  `description` text COLLATE utf8mb4_unicode_ci,
  `image_id` bigint unsigned DEFAULT NULL,
  `meta_title` varchar(160) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `meta_description` varchar(320) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `sort_order` smallint unsigned NOT NULL DEFAULT '0',
  `is_active` tinyint(1) NOT NULL DEFAULT '1',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `meta_key` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `no_index` tinyint(1) NOT NULL DEFAULT '0',
  `no_follow` tinyint(1) NOT NULL DEFAULT '0',
  `public_by` bigint unsigned DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `product_categories_name_unique` (`name`),
  UNIQUE KEY `product_categories_slug_unique` (`slug`),
  KEY `product_categories_parent_id_foreign` (`parent_id`),
  KEY `product_categories_image_id_foreign` (`image_id`),
  KEY `product_categories_public_by_foreign` (`public_by`),
  CONSTRAINT `product_categories_image_id_foreign` FOREIGN KEY (`image_id`) REFERENCES `medias` (`id`) ON DELETE SET NULL,
  CONSTRAINT `product_categories_parent_id_foreign` FOREIGN KEY (`parent_id`) REFERENCES `product_categories` (`id`) ON DELETE RESTRICT,
  CONSTRAINT `product_categories_public_by_foreign` FOREIGN KEY (`public_by`) REFERENCES `users` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB AUTO_INCREMENT=4 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `product_categories`
--

LOCK TABLES `product_categories` WRITE;
/*!40000 ALTER TABLE `product_categories` DISABLE KEYS */;
INSERT INTO `product_categories` VALUES (1,NULL,'Xe đạp','xe-dap',NULL,NULL,NULL,NULL,0,1,'2026-11-14 09:06:44','2026-11-14 09:06:44',NULL,0,0,NULL),(2,NULL,'Linh kiện & Phụ tùng','linh-kien-phu-tung',NULL,NULL,NULL,NULL,0,1,'2026-11-14 09:07:12','2026-11-14 09:07:12',NULL,0,0,NULL),(3,1,'Xe đạp thể thao Grani','xe-dap-the-thao-grani',NULL,NULL,NULL,NULL,0,1,'2026-11-14 09:07:27','2026-11-14 09:07:39',NULL,0,0,NULL);
/*!40000 ALTER TABLE `product_categories` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `product_category_product`
--

DROP TABLE IF EXISTS `product_category_product`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `product_category_product` (
  `product_id` bigint unsigned NOT NULL,
  `category_id` bigint unsigned NOT NULL,
  PRIMARY KEY (`product_id`,`category_id`),
  KEY `product_category_product_category_id_index` (`category_id`),
  CONSTRAINT `product_category_product_category_id_foreign` FOREIGN KEY (`category_id`) REFERENCES `product_categories` (`id`) ON DELETE CASCADE,
  CONSTRAINT `product_category_product_product_id_foreign` FOREIGN KEY (`product_id`) REFERENCES `products` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `product_category_product`
--

LOCK TABLES `product_category_product` WRITE;
/*!40000 ALTER TABLE `product_category_product` DISABLE KEYS */;
/*!40000 ALTER TABLE `product_category_product` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `product_media`
--

DROP TABLE IF EXISTS `product_media`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `product_media` (
  `product_id` bigint unsigned NOT NULL,
  `media_id` bigint unsigned NOT NULL,
  `sort_order` smallint unsigned NOT NULL DEFAULT '0',
  PRIMARY KEY (`product_id`,`media_id`),
  KEY `product_media_media_id_foreign` (`media_id`),
  CONSTRAINT `product_media_media_id_foreign` FOREIGN KEY (`media_id`) REFERENCES `medias` (`id`) ON DELETE CASCADE,
  CONSTRAINT `product_media_product_id_foreign` FOREIGN KEY (`product_id`) REFERENCES `products` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `product_media`
--

LOCK TABLES `product_media` WRITE;
/*!40000 ALTER TABLE `product_media` DISABLE KEYS */;
/*!40000 ALTER TABLE `product_media` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `product_product_tag`
--

DROP TABLE IF EXISTS `product_product_tag`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `product_product_tag` (
  `product_id` bigint unsigned NOT NULL,
  `tag_id` bigint unsigned NOT NULL,
  PRIMARY KEY (`product_id`,`tag_id`),
  KEY `product_product_tag_tag_id_index` (`tag_id`),
  CONSTRAINT `product_product_tag_product_id_foreign` FOREIGN KEY (`product_id`) REFERENCES `products` (`id`) ON DELETE CASCADE,
  CONSTRAINT `product_product_tag_tag_id_foreign` FOREIGN KEY (`tag_id`) REFERENCES `product_tags` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `product_product_tag`
--

LOCK TABLES `product_product_tag` WRITE;
/*!40000 ALTER TABLE `product_product_tag` DISABLE KEYS */;
/*!40000 ALTER TABLE `product_product_tag` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `product_tags`
--

DROP TABLE IF EXISTS `product_tags`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `product_tags` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `name` varchar(100) COLLATE utf8mb4_unicode_ci NOT NULL,
  `slug` varchar(120) COLLATE utf8mb4_unicode_ci NOT NULL,
  `description` text COLLATE utf8mb4_unicode_ci,
  `meta_title` varchar(160) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `meta_description` varchar(320) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `is_active` tinyint(1) NOT NULL DEFAULT '1',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `meta_key` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `no_index` tinyint(1) NOT NULL DEFAULT '0',
  `no_follow` tinyint(1) NOT NULL DEFAULT '0',
  `public_by` bigint unsigned DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `product_tags_name_unique` (`name`),
  UNIQUE KEY `product_tags_slug_unique` (`slug`),
  KEY `product_tags_public_by_foreign` (`public_by`),
  CONSTRAINT `product_tags_public_by_foreign` FOREIGN KEY (`public_by`) REFERENCES `users` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `product_tags`
--

LOCK TABLES `product_tags` WRITE;
/*!40000 ALTER TABLE `product_tags` DISABLE KEYS */;
/*!40000 ALTER TABLE `product_tags` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `products`
--

DROP TABLE IF EXISTS `products`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `products` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `category_id` bigint unsigned NOT NULL,
  `brand_id` bigint unsigned DEFAULT NULL,
  `name` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `slug` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `sku` varchar(60) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `price` bigint unsigned NOT NULL DEFAULT '0',
  `sale_price` bigint unsigned DEFAULT NULL,
  `stock` int unsigned NOT NULL DEFAULT '0',
  `short_description` text COLLATE utf8mb4_unicode_ci,
  `content` longtext COLLATE utf8mb4_unicode_ci,
  `specs` json DEFAULT NULL,
  `image_id` bigint unsigned DEFAULT NULL,
  `meta_title` varchar(160) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `meta_description` varchar(320) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `is_active` tinyint(1) NOT NULL DEFAULT '1',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `meta_key` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `no_index` tinyint(1) NOT NULL DEFAULT '0',
  `no_follow` tinyint(1) NOT NULL DEFAULT '0',
  `public_by` bigint unsigned DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `products_slug_unique` (`slug`),
  UNIQUE KEY `products_sku_unique` (`sku`),
  KEY `products_category_id_foreign` (`category_id`),
  KEY `products_brand_id_foreign` (`brand_id`),
  KEY `products_image_id_foreign` (`image_id`),
  KEY `products_is_active_id_index` (`is_active`,`id`),
  KEY `products_public_by_foreign` (`public_by`),
  CONSTRAINT `products_brand_id_foreign` FOREIGN KEY (`brand_id`) REFERENCES `brands` (`id`) ON DELETE SET NULL,
  CONSTRAINT `products_category_id_foreign` FOREIGN KEY (`category_id`) REFERENCES `product_categories` (`id`) ON DELETE RESTRICT,
  CONSTRAINT `products_image_id_foreign` FOREIGN KEY (`image_id`) REFERENCES `medias` (`id`) ON DELETE SET NULL,
  CONSTRAINT `products_public_by_foreign` FOREIGN KEY (`public_by`) REFERENCES `users` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `products`
--

LOCK TABLES `products` WRITE;
/*!40000 ALTER TABLE `products` DISABLE KEYS */;
/*!40000 ALTER TABLE `products` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `roles`
--

DROP TABLE IF EXISTS `roles`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `roles` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `name` varchar(100) COLLATE utf8mb4_unicode_ci NOT NULL,
  `description` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `is_system` tinyint(1) NOT NULL DEFAULT '0',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `roles_name_unique` (`name`)
) ENGINE=InnoDB AUTO_INCREMENT=4 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `roles`
--

LOCK TABLES `roles` WRITE;
/*!40000 ALTER TABLE `roles` DISABLE KEYS */;
INSERT INTO `roles` VALUES (1,'Quản trị viên','Toàn quyền quản trị hệ thống',1,'2026-11-12 20:29:55','2026-11-12 20:29:55'),(2,'Quản lý','Quản lý vận hành hệ thống',0,'2026-11-12 20:29:55','2026-11-12 20:29:55'),(3,'Nhân viên','Nhân viên vận hành',0,'2026-11-12 20:29:55','2026-11-12 20:29:55');
/*!40000 ALTER TABLE `roles` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `sessions`
--

DROP TABLE IF EXISTS `sessions`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `sessions` (
  `id` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `user_id` bigint unsigned DEFAULT NULL,
  `ip_address` varchar(45) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `user_agent` text COLLATE utf8mb4_unicode_ci,
  `payload` longtext COLLATE utf8mb4_unicode_ci NOT NULL,
  `last_activity` int NOT NULL,
  PRIMARY KEY (`id`),
  KEY `sessions_user_id_index` (`user_id`),
  KEY `sessions_last_activity_index` (`last_activity`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `sessions`
--

LOCK TABLES `sessions` WRITE;
/*!40000 ALTER TABLE `sessions` DISABLE KEYS */;
INSERT INTO `sessions` VALUES ('112S8sMGjeVRHkeIliYhDTWNKD9iEVcFZULUG7F7',1,'127.0.0.1','Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/157.0.0.0 Safari/537.36','YTo2OntzOjY6Il90b2tlbiI7czo0MDoiZFBFTDdhdUNWeTZhZHFPQXBvYTBkekQ2SmNkQWpDT295Yk91dlpWSCI7czo5OiJfcHJldmlvdXMiO2E6Mjp7czozOiJ1cmwiO3M6Mzg6Imh0dHA6Ly9teS1sYXJhdmVsLWJhc2Utd2ViLmxvY2FsL2FkbWluIjtzOjU6InJvdXRlIjtzOjE1OiJhZG1pbi5kYXNoYm9hcmQiO31zOjY6Il9mbGFzaCI7YToyOntzOjM6Im9sZCI7YTowOnt9czozOiJuZXciO2E6MDp7fX1zOjM6InVybCI7YTowOnt9czo1MDoibG9naW5fd2ViXzU5YmEzNmFkZGMyYjJmOTQwMTU4MGYwMTRjN2Y1OGVhNGUzMDk4OWQiO2k6MTtzOjE3OiJwYXNzd29yZF9oYXNoX3dlYiI7czo2NDoiMGY4ZDU5ZjY0NGI4NDlhYWQ1NDE3YmViMWFhOTA0YWQwNGVmMDkxZThiOTE5MDg4MWYwNjViZDJmY2M3YjFiYSI7fQ==',1794662484),('13ZmBxttVfCcUoT9Fs3ZJmvNYJFJKZTKLen5x1ii',NULL,'127.0.0.1','Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/157.0.0.0 Safari/537.36','YTo0OntzOjY6Il90b2tlbiI7czo0MDoibHVnMjhUYlZKelRqNkV4b2NyZzN2bW5ETWZGVHJOMkZ6TkVZQlFOZSI7czozOiJ1cmwiO2E6MTp7czo4OiJpbnRlbmRlZCI7czozODoiaHR0cDovL215LWxhcmF2ZWwtYmFzZS13ZWIubG9jYWwvYWRtaW4iO31zOjk6Il9wcmV2aW91cyI7YToyOntzOjM6InVybCI7czo0NDoiaHR0cDovL215LWxhcmF2ZWwtYmFzZS13ZWIubG9jYWwvYWRtaW4vbG9naW4iO3M6NToicm91dGUiO3M6MTE6ImFkbWluLmxvZ2luIjt9czo2OiJfZmxhc2giO2E6Mjp7czozOiJvbGQiO2E6MDp7fXM6MzoibmV3IjthOjA6e319fQ==',1794673576),('5FYwvMay0L0p4G7E0VM0cJHfOhlpt3FLKsyUDecB',1,'127.0.0.1','Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/157.0.0.0 Safari/537.36','YTo2OntzOjY6Il90b2tlbiI7czo0MDoiZ1NCOFhSWnlYTHZ4TEFxZEZoVnZuZVc3MmN4RDFGS3EyTUk2VzEyUSI7czozOiJ1cmwiO2E6MDp7fXM6OToiX3ByZXZpb3VzIjthOjI6e3M6MzoidXJsIjtzOjQ0OiJodHRwOi8vbXktbGFyYXZlbC1iYXNlLXdlYi5sb2NhbC9hZG1pbi9sb2dpbiI7czo1OiJyb3V0ZSI7czoxMToiYWRtaW4ubG9naW4iO31zOjY6Il9mbGFzaCI7YToyOntzOjM6Im9sZCI7YTowOnt9czozOiJuZXciO2E6MDp7fX1zOjUwOiJsb2dpbl93ZWJfNTliYTM2YWRkYzJiMmY5NDAxNTgwZjAxNGM3ZjU4ZWE0ZTMwOTg5ZCI7aToxO3M6MTc6InBhc3N3b3JkX2hhc2hfd2ViIjtzOjY0OiIwZjhkNTlmNjQ0Yjg0OWFhZDU0MTdiZWIxYWE5MDRhZDA0ZWYwOTFlOGI5MTkwODgxZjA2NWJkMmZjYzdiMWJhIjt9',1794673643),('dm5LG4Em5wfroWfARdNMxkws2E5EJL5WupAaaVEC',1,'127.0.0.1','Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/157.0.0.0 Safari/537.36','YTo2OntzOjY6Il90b2tlbiI7czo0MDoiS0NGMjcxalAyYmZYRUxjWmtjdnpRV2UwWmxSb0tUbnJWRlRxZ3pQaSI7czo5OiJfcHJldmlvdXMiO2E6Mjp7czozOiJ1cmwiO3M6NDc6Imh0dHA6Ly9teS1sYXJhdmVsLWJhc2Utd2ViLmxvY2FsL2FkbWluL2FydGljbGVzIjtzOjU6InJvdXRlIjtzOjIwOiJhZG1pbi5hcnRpY2xlcy5pbmRleCI7fXM6NjoiX2ZsYXNoIjthOjI6e3M6Mzoib2xkIjthOjA6e31zOjM6Im5ldyI7YTowOnt9fXM6MzoidXJsIjthOjA6e31zOjUwOiJsb2dpbl93ZWJfNTliYTM2YWRkYzJiMmY5NDAxNTgwZjAxNGM3ZjU4ZWE0ZTMwOTg5ZCI7aToxO3M6MTc6InBhc3N3b3JkX2hhc2hfd2ViIjtzOjY0OiIwZjhkNTlmNjQ0Yjg0OWFhZDU0MTdiZWIxYWE5MDRhZDA0ZWYwOTFlOGI5MTkwODgxZjA2NWJkMmZjYzdiMWJhIjt9',1794647038),('FSkWkzKwYsjmBStwWebo7OgkQN8AGfXupltACLMe',NULL,'127.0.0.1','Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/157.0.0.0 Safari/537.36','YToyOntzOjY6Il90b2tlbiI7czo0MDoielFJVGVnOVU3UTF6a0hLek5ib3lyM09sYjVlREpVbVZrMVdVMTRNZSI7czo2OiJfZmxhc2giO2E6Mjp7czozOiJvbGQiO2E6MDp7fXM6MzoibmV3IjthOjA6e319fQ==',1794655631);
/*!40000 ALTER TABLE `sessions` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `tags`
--

DROP TABLE IF EXISTS `tags`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `tags` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `name` varchar(100) COLLATE utf8mb4_unicode_ci NOT NULL,
  `slug` varchar(120) COLLATE utf8mb4_unicode_ci NOT NULL,
  `description` text COLLATE utf8mb4_unicode_ci,
  `meta_title` varchar(160) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `meta_description` varchar(320) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `is_active` tinyint(1) NOT NULL DEFAULT '1',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `meta_key` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `no_index` tinyint(1) NOT NULL DEFAULT '0',
  `no_follow` tinyint(1) NOT NULL DEFAULT '0',
  `public_by` bigint unsigned DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `tags_name_unique` (`name`),
  UNIQUE KEY `tags_slug_unique` (`slug`),
  KEY `tags_public_by_foreign` (`public_by`),
  CONSTRAINT `tags_public_by_foreign` FOREIGN KEY (`public_by`) REFERENCES `users` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB AUTO_INCREMENT=2 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `tags`
--

LOCK TABLES `tags` WRITE;
/*!40000 ALTER TABLE `tags` DISABLE KEYS */;
INSERT INTO `tags` VALUES (1,'Nghề IT hot','nghe-it-hot',NULL,NULL,NULL,1,'2026-11-13 20:45:34','2026-11-13 20:45:34',NULL,0,0,NULL);
/*!40000 ALTER TABLE `tags` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `users`
--

DROP TABLE IF EXISTS `users`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `users` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `name` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `username` varchar(50) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `email` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `email_verified_at` timestamp NULL DEFAULT NULL,
  `password` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `id_role` bigint unsigned DEFAULT NULL,
  `is_active` tinyint(1) NOT NULL DEFAULT '1',
  `image` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `note` text COLLATE utf8mb4_unicode_ci,
  `remember_token` varchar(100) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `users_email_unique` (`email`),
  UNIQUE KEY `users_username_unique` (`username`),
  KEY `users_id_role_foreign` (`id_role`),
  CONSTRAINT `users_id_role_foreign` FOREIGN KEY (`id_role`) REFERENCES `roles` (`id`) ON DELETE RESTRICT
) ENGINE=InnoDB AUTO_INCREMENT=3 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `users`
--

LOCK TABLES `users` WRITE;
/*!40000 ALTER TABLE `users` DISABLE KEYS */;
INSERT INTO `users` VALUES (1,'Quản trị viên','admin','tech@gmail.com',NULL,'$2y$12$GeeCdYuFhyfxidDzwQH8Wu7LkgBfPW5j1sZJZxWLt.nTGHhafwxIS',1,1,'users/k2fzZmGV6ACLJ0ZtaI1te6pYtNtWvXeZNLhbYfNq.jpg',NULL,NULL,'2026-11-12 18:09:38','2026-11-13 11:16:40'),(2,'Bùi Văn Thiện','thienbui','thienbui@gmail.com',NULL,'$2y$12$EwS2ipfZ0iit/rEJx/VgI.D3cM3i3bFz8NXDY68a3LhjJnyykNiVO',2,1,NULL,'Tài khoản quản lý 1',NULL,'2026-11-13 05:02:10','2026-11-13 18:04:27');
/*!40000 ALTER TABLE `users` ENABLE KEYS */;
UNLOCK TABLES;
/*!40103 SET TIME_ZONE=@OLD_TIME_ZONE */;

/*!40101 SET SQL_MODE=@OLD_SQL_MODE */;
/*!40014 SET FOREIGN_KEY_CHECKS=@OLD_FOREIGN_KEY_CHECKS */;
/*!40014 SET UNIQUE_CHECKS=@OLD_UNIQUE_CHECKS */;
/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
/*!40111 SET SQL_NOTES=@OLD_SQL_NOTES */;

-- Dump completed on 2026-11-15  0:20:03
