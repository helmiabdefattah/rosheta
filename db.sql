-- --------------------------------------------------------
-- Host:                         127.0.0.1
-- Server version:               8.0.30 - MySQL Community Server - GPL
-- Server OS:                    Win64
-- HeidiSQL Version:             12.1.0.6537
-- --------------------------------------------------------

/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET NAMES utf8 */;
/*!50503 SET NAMES utf8mb4 */;
/*!40103 SET @OLD_TIME_ZONE=@@TIME_ZONE */;
/*!40103 SET TIME_ZONE='+00:00' */;
/*!40014 SET @OLD_FOREIGN_KEY_CHECKS=@@FOREIGN_KEY_CHECKS, FOREIGN_KEY_CHECKS=0 */;
/*!40101 SET @OLD_SQL_MODE=@@SQL_MODE, SQL_MODE='NO_AUTO_VALUE_ON_ZERO' */;
/*!40111 SET @OLD_SQL_NOTES=@@SQL_NOTES, SQL_NOTES=0 */;

-- Dumping structure for table rosheta.areas
CREATE TABLE IF NOT EXISTS `areas` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `city_id` bigint unsigned NOT NULL,
  `name` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `name_ar` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `is_active` tinyint(1) NOT NULL DEFAULT '1',
  `sort_order` int NOT NULL DEFAULT '0',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `areas_city_id_index` (`city_id`),
  KEY `areas_is_active_index` (`is_active`),
  CONSTRAINT `areas_city_id_foreign` FOREIGN KEY (`city_id`) REFERENCES `cities` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=639 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Data exporting was unselected.

-- Dumping structure for table rosheta.cache
CREATE TABLE IF NOT EXISTS `cache` (
  `key` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `value` mediumtext COLLATE utf8mb4_unicode_ci NOT NULL,
  `expiration` int NOT NULL,
  PRIMARY KEY (`key`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Data exporting was unselected.

-- Dumping structure for table rosheta.cache_locks
CREATE TABLE IF NOT EXISTS `cache_locks` (
  `key` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `owner` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `expiration` int NOT NULL,
  PRIMARY KEY (`key`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Data exporting was unselected.

-- Dumping structure for table rosheta.cities
CREATE TABLE IF NOT EXISTS `cities` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `governorate_id` bigint unsigned NOT NULL,
  `name` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `name_ar` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `is_active` tinyint(1) NOT NULL DEFAULT '1',
  `sort_order` int NOT NULL DEFAULT '0',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `cities_governorate_id_index` (`governorate_id`),
  KEY `cities_is_active_index` (`is_active`),
  CONSTRAINT `cities_governorate_id_foreign` FOREIGN KEY (`governorate_id`) REFERENCES `governorates` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=638 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Data exporting was unselected.

-- Dumping structure for table rosheta.clients
CREATE TABLE IF NOT EXISTS `clients` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `name` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `email` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `avatar` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `phone_number` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `password` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `clients_email_unique` (`email`)
) ENGINE=InnoDB AUTO_INCREMENT=107 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Data exporting was unselected.

-- Dumping structure for table rosheta.client_addresses
CREATE TABLE IF NOT EXISTS `client_addresses` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `client_id` bigint unsigned NOT NULL,
  `address` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `location` json DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `city_id` bigint unsigned DEFAULT NULL,
  `area_id` bigint unsigned DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `client_addresses_client_id_foreign` (`client_id`),
  KEY `client_addresses_city_id_foreign` (`city_id`),
  KEY `client_addresses_area_id_foreign` (`area_id`),
  CONSTRAINT `client_addresses_area_id_foreign` FOREIGN KEY (`area_id`) REFERENCES `areas` (`id`) ON DELETE SET NULL,
  CONSTRAINT `client_addresses_city_id_foreign` FOREIGN KEY (`city_id`) REFERENCES `cities` (`id`) ON DELETE SET NULL,
  CONSTRAINT `client_addresses_client_id_foreign` FOREIGN KEY (`client_id`) REFERENCES `clients` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=307 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Data exporting was unselected.

-- Dumping structure for table rosheta.client_requests
CREATE TABLE IF NOT EXISTS `client_requests` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `client_id` bigint unsigned NOT NULL,
  `client_address_id` bigint unsigned DEFAULT NULL,
  `pregnant` tinyint(1) NOT NULL DEFAULT '0',
  `diabetic` tinyint(1) NOT NULL DEFAULT '0',
  `heart_patient` tinyint(1) NOT NULL DEFAULT '0',
  `high_blood_pressure` tinyint(1) NOT NULL DEFAULT '0',
  `note` text COLLATE utf8mb4_unicode_ci,
  `status` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'pending',
  `type` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `images` json DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `client_requests_client_id_foreign` (`client_id`),
  KEY `client_requests_client_address_id_foreign` (`client_address_id`),
  KEY `client_requests_type_index` (`type`),
  CONSTRAINT `client_requests_client_address_id_foreign` FOREIGN KEY (`client_address_id`) REFERENCES `client_addresses` (`id`) ON DELETE RESTRICT,
  CONSTRAINT `client_requests_client_id_foreign` FOREIGN KEY (`client_id`) REFERENCES `clients` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=108 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Data exporting was unselected.

-- Dumping structure for table rosheta.client_request_lines
CREATE TABLE IF NOT EXISTS `client_request_lines` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `item_type` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'medicine',
  `client_request_id` bigint unsigned NOT NULL,
  `medicine_id` bigint unsigned DEFAULT NULL,
  `medical_test_id` bigint unsigned DEFAULT NULL,
  `quantity` int unsigned DEFAULT NULL,
  `unit` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `client_request_lines_client_request_id_foreign` (`client_request_id`),
  KEY `client_request_lines_medicine_id_foreign` (`medicine_id`),
  KEY `client_request_lines_item_type_index` (`item_type`),
  KEY `client_request_lines_medical_test_id_index` (`medical_test_id`),
  CONSTRAINT `client_request_lines_client_request_id_foreign` FOREIGN KEY (`client_request_id`) REFERENCES `client_requests` (`id`) ON DELETE CASCADE,
  CONSTRAINT `client_request_lines_medical_test_id_foreign` FOREIGN KEY (`medical_test_id`) REFERENCES `medical_tests` (`id`) ON DELETE RESTRICT,
  CONSTRAINT `client_request_lines_medicine_id_foreign` FOREIGN KEY (`medicine_id`) REFERENCES `medicines` (`id`) ON DELETE RESTRICT
) ENGINE=InnoDB AUTO_INCREMENT=269 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Data exporting was unselected.

-- Dumping structure for table rosheta.client_request_test_lines
CREATE TABLE IF NOT EXISTS `client_request_test_lines` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `client_request_id` bigint unsigned NOT NULL,
  `medical_test_id` bigint unsigned NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `client_request_test_lines_client_request_id_foreign` (`client_request_id`),
  KEY `client_request_test_lines_medical_test_id_foreign` (`medical_test_id`),
  CONSTRAINT `client_request_test_lines_client_request_id_foreign` FOREIGN KEY (`client_request_id`) REFERENCES `client_requests` (`id`) ON DELETE CASCADE,
  CONSTRAINT `client_request_test_lines_medical_test_id_foreign` FOREIGN KEY (`medical_test_id`) REFERENCES `medical_tests` (`id`) ON DELETE RESTRICT
) ENGINE=InnoDB AUTO_INCREMENT=6 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Data exporting was unselected.

-- Dumping structure for table rosheta.diagnostic_radiology
CREATE TABLE IF NOT EXISTS `diagnostic_radiology` (
  `id` int NOT NULL AUTO_INCREMENT,
  `name_ar` varchar(255) NOT NULL,
  `name_en` varchar(255) NOT NULL,
  `description` text,
  `conditions` text,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=95 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

-- Data exporting was unselected.

-- Dumping structure for table rosheta.failed_jobs
CREATE TABLE IF NOT EXISTS `failed_jobs` (
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

-- Data exporting was unselected.

-- Dumping structure for table rosheta.governorates
CREATE TABLE IF NOT EXISTS `governorates` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `name` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `name_ar` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `is_active` tinyint(1) NOT NULL DEFAULT '1',
  `sort_order` int NOT NULL DEFAULT '0',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `governorates_is_active_index` (`is_active`)
) ENGINE=InnoDB AUTO_INCREMENT=163 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Data exporting was unselected.

-- Dumping structure for table rosheta.jobs
CREATE TABLE IF NOT EXISTS `jobs` (
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

-- Data exporting was unselected.

-- Dumping structure for table rosheta.job_batches
CREATE TABLE IF NOT EXISTS `job_batches` (
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

-- Data exporting was unselected.

-- Dumping structure for table rosheta.laboratories
CREATE TABLE IF NOT EXISTS `laboratories` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `user_id` bigint unsigned DEFAULT NULL,
  `area_id` bigint unsigned DEFAULT NULL,
  `name` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `phone` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `email` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `logo` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `address` text COLLATE utf8mb4_unicode_ci,
  `location` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `lat` decimal(11,8) DEFAULT NULL,
  `lng` decimal(11,8) DEFAULT NULL,
  `license_number` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `manager_name` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `manager_license` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `opening_time` time DEFAULT NULL,
  `closing_time` time DEFAULT NULL,
  `is_active` tinyint(1) NOT NULL DEFAULT '1',
  `notes` text COLLATE utf8mb4_unicode_ci,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `laboratories_user_id_foreign` (`user_id`),
  KEY `laboratories_area_id_foreign` (`area_id`),
  CONSTRAINT `laboratories_area_id_foreign` FOREIGN KEY (`area_id`) REFERENCES `areas` (`id`) ON DELETE SET NULL,
  CONSTRAINT `laboratories_user_id_foreign` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB AUTO_INCREMENT=8 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Data exporting was unselected.

-- Dumping structure for table rosheta.medical_tests
CREATE TABLE IF NOT EXISTS `medical_tests` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `test_name_en` text NOT NULL,
  `test_name_ar` text NOT NULL,
  `test_description` text,
  `created_at` date DEFAULT NULL,
  `updated_at` date DEFAULT NULL,
  `conditions` varchar(255) DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=489 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

-- Data exporting was unselected.

-- Dumping structure for table rosheta.medical_test_offers
CREATE TABLE IF NOT EXISTS `medical_test_offers` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `medical_test_id` bigint unsigned NOT NULL,
  `laboratory_id` bigint unsigned NOT NULL,
  `client_request_id` bigint unsigned DEFAULT NULL,
  `price` decimal(10,2) NOT NULL,
  `offer_price` decimal(10,2) DEFAULT NULL,
  `discount` decimal(5,2) NOT NULL DEFAULT '0.00',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `uniq_test_lab_offer` (`medical_test_id`,`laboratory_id`),
  KEY `medical_test_offers_laboratory_id_foreign` (`laboratory_id`),
  KEY `medical_test_offers_client_request_id_foreign` (`client_request_id`),
  CONSTRAINT `medical_test_offers_client_request_id_foreign` FOREIGN KEY (`client_request_id`) REFERENCES `client_requests` (`id`) ON DELETE SET NULL,
  CONSTRAINT `medical_test_offers_laboratory_id_foreign` FOREIGN KEY (`laboratory_id`) REFERENCES `laboratories` (`id`) ON DELETE CASCADE,
  CONSTRAINT `medical_test_offers_medical_test_id_foreign` FOREIGN KEY (`medical_test_id`) REFERENCES `medical_tests` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=301 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Data exporting was unselected.

-- Dumping structure for table rosheta.medicines
CREATE TABLE IF NOT EXISTS `medicines` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `api_id` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `name` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `arabic` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `shortage` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `date_updated` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `imported` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `percentage` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `pharmacology` text CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci,
  `route` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `in_eye` tinyint(1) NOT NULL DEFAULT '0',
  `units` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `small_unit` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `sold_times` int NOT NULL DEFAULT '0',
  `dosage_form` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `barcode` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `barcode2` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `qrcode` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `anyupdatedate` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `new_added_date` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `updtime` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `fame` tinyint(1) NOT NULL DEFAULT '0',
  `cosmo` tinyint(1) NOT NULL DEFAULT '0',
  `dose` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `repeated` tinyint(1) NOT NULL DEFAULT '0',
  `price` decimal(10,2) DEFAULT NULL,
  `oldprice` decimal(10,2) DEFAULT NULL,
  `newprice` decimal(10,2) DEFAULT NULL,
  `active_ingredient` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `similars_id` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `img` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `description` text CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci,
  `uses` text CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci,
  `company` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `reported` tinyint(1) NOT NULL DEFAULT '0',
  `reporter_name` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `visits` int NOT NULL DEFAULT '0',
  `sim_visits` int NOT NULL DEFAULT '0',
  `ind_visits` int NOT NULL DEFAULT '0',
  `composition_visits` int NOT NULL DEFAULT '0',
  `order_visits` int NOT NULL DEFAULT '0',
  `app_visits` int NOT NULL DEFAULT '0',
  `shares` int NOT NULL DEFAULT '0',
  `last_visited` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `lastupdatingadmin` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `awhat_visits` int NOT NULL DEFAULT '0',
  `report_msg` text CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci,
  `found_pharmacies_ids` text CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci,
  `availability` int NOT NULL DEFAULT '0',
  `noimgid` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `raw_data` json DEFAULT NULL,
  `search_term` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `batch_number` int DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `medicines_api_id_index` (`api_id`),
  KEY `name_index` (`name`) USING BTREE
) ENGINE=InnoDB AUTO_INCREMENT=25549 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Data exporting was unselected.

-- Dumping structure for table rosheta.migrations
CREATE TABLE IF NOT EXISTS `migrations` (
  `id` int unsigned NOT NULL AUTO_INCREMENT,
  `migration` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `batch` int NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=42 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Data exporting was unselected.

-- Dumping structure for table rosheta.offers
CREATE TABLE IF NOT EXISTS `offers` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `client_request_id` bigint unsigned NOT NULL,
  `request_type` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'medicine',
  `pharmacy_id` bigint unsigned DEFAULT NULL,
  `laboratory_id` bigint unsigned DEFAULT NULL,
  `user_id` bigint unsigned NOT NULL,
  `status` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'draft',
  `total_price` decimal(10,2) DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `offers_client_request_id_foreign` (`client_request_id`),
  KEY `offers_pharmacy_id_foreign` (`pharmacy_id`),
  KEY `offers_user_id_foreign` (`user_id`),
  KEY `offers_request_type_index` (`request_type`),
  KEY `offers_laboratory_id_index` (`laboratory_id`),
  CONSTRAINT `offers_client_request_id_foreign` FOREIGN KEY (`client_request_id`) REFERENCES `client_requests` (`id`),
  CONSTRAINT `offers_laboratory_id_foreign` FOREIGN KEY (`laboratory_id`) REFERENCES `laboratories` (`id`) ON DELETE SET NULL,
  CONSTRAINT `offers_pharmacy_id_foreign` FOREIGN KEY (`pharmacy_id`) REFERENCES `pharmacies` (`id`),
  CONSTRAINT `offers_user_id_foreign` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=105 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Data exporting was unselected.

-- Dumping structure for table rosheta.offer_lines
CREATE TABLE IF NOT EXISTS `offer_lines` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `item_type` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'medicine',
  `offer_id` bigint unsigned NOT NULL,
  `medicine_id` bigint unsigned DEFAULT NULL,
  `medical_test_id` bigint unsigned DEFAULT NULL,
  `quantity` int unsigned NOT NULL,
  `unit` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `price` decimal(10,2) DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `offer_lines_offer_id_foreign` (`offer_id`),
  KEY `offer_lines_medicine_id_foreign` (`medicine_id`),
  KEY `offer_lines_item_type_index` (`item_type`),
  KEY `offer_lines_medical_test_id_index` (`medical_test_id`),
  CONSTRAINT `offer_lines_medical_test_id_foreign` FOREIGN KEY (`medical_test_id`) REFERENCES `medical_tests` (`id`) ON DELETE RESTRICT,
  CONSTRAINT `offer_lines_medicine_id_foreign` FOREIGN KEY (`medicine_id`) REFERENCES `medicines` (`id`) ON DELETE RESTRICT,
  CONSTRAINT `offer_lines_offer_id_foreign` FOREIGN KEY (`offer_id`) REFERENCES `offers` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=285 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Data exporting was unselected.

-- Dumping structure for table rosheta.orders
CREATE TABLE IF NOT EXISTS `orders` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `client_request_id` bigint unsigned NOT NULL,
  `pharmacy_id` bigint unsigned NOT NULL,
  `user_id` bigint unsigned NOT NULL,
  `offer_id` bigint unsigned DEFAULT NULL,
  `status` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'preparing',
  `payment_method` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `payed` tinyint(1) NOT NULL DEFAULT '0',
  `total_price` decimal(10,2) DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `orders_client_request_id_foreign` (`client_request_id`),
  KEY `orders_pharmacy_id_foreign` (`pharmacy_id`),
  KEY `orders_user_id_foreign` (`user_id`),
  KEY `orders_offer_id_foreign` (`offer_id`),
  CONSTRAINT `orders_client_request_id_foreign` FOREIGN KEY (`client_request_id`) REFERENCES `client_requests` (`id`),
  CONSTRAINT `orders_offer_id_foreign` FOREIGN KEY (`offer_id`) REFERENCES `offers` (`id`) ON DELETE SET NULL,
  CONSTRAINT `orders_pharmacy_id_foreign` FOREIGN KEY (`pharmacy_id`) REFERENCES `pharmacies` (`id`),
  CONSTRAINT `orders_user_id_foreign` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=13 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Data exporting was unselected.

-- Dumping structure for table rosheta.order_lines
CREATE TABLE IF NOT EXISTS `order_lines` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `order_id` bigint unsigned NOT NULL,
  `medicine_id` bigint unsigned NOT NULL,
  `quantity` int unsigned NOT NULL,
  `unit` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `price` decimal(10,2) DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `order_lines_order_id_foreign` (`order_id`),
  KEY `order_lines_medicine_id_foreign` (`medicine_id`),
  CONSTRAINT `order_lines_medicine_id_foreign` FOREIGN KEY (`medicine_id`) REFERENCES `medicines` (`id`),
  CONSTRAINT `order_lines_order_id_foreign` FOREIGN KEY (`order_id`) REFERENCES `orders` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=8 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Data exporting was unselected.

-- Dumping structure for table rosheta.password_reset_codes
CREATE TABLE IF NOT EXISTS `password_reset_codes` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `phone` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `code` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `expires_at` timestamp NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=14 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Data exporting was unselected.

-- Dumping structure for table rosheta.password_reset_tokens
CREATE TABLE IF NOT EXISTS `password_reset_tokens` (
  `email` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `token` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`email`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Data exporting was unselected.

-- Dumping structure for table rosheta.personal_access_tokens
CREATE TABLE IF NOT EXISTS `personal_access_tokens` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `tokenable_type` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `tokenable_id` bigint unsigned NOT NULL,
  `name` text COLLATE utf8mb4_unicode_ci NOT NULL,
  `token` varchar(64) COLLATE utf8mb4_unicode_ci NOT NULL,
  `abilities` text COLLATE utf8mb4_unicode_ci,
  `last_used_at` timestamp NULL DEFAULT NULL,
  `expires_at` timestamp NULL DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `personal_access_tokens_token_unique` (`token`),
  KEY `personal_access_tokens_tokenable_type_tokenable_id_index` (`tokenable_type`,`tokenable_id`),
  KEY `personal_access_tokens_expires_at_index` (`expires_at`)
) ENGINE=InnoDB AUTO_INCREMENT=57 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Data exporting was unselected.

-- Dumping structure for table rosheta.pharmacies
CREATE TABLE IF NOT EXISTS `pharmacies` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `user_id` bigint unsigned DEFAULT NULL,
  `area_id` bigint unsigned DEFAULT NULL,
  `name` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `phone` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `email` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `address` text CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci,
  `location` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `lat` decimal(10,8) DEFAULT NULL,
  `lng` decimal(11,8) DEFAULT NULL,
  `license_number` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `pharmacist_name` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `pharmacist_license` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `opening_time` time DEFAULT NULL,
  `closing_time` time DEFAULT NULL,
  `is_active` tinyint(1) NOT NULL DEFAULT '1',
  `notes` text CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `pharmacies_area_id_foreign` (`area_id`),
  KEY `pharmacies_user_id_index` (`user_id`),
  KEY `pharmacies_name_index` (`name`),
  KEY `pharmacies_lat_lng_index` (`lat`,`lng`),
  CONSTRAINT `pharmacies_area_id_foreign` FOREIGN KEY (`area_id`) REFERENCES `areas` (`id`) ON DELETE SET NULL,
  CONSTRAINT `pharmacies_user_id_foreign` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB AUTO_INCREMENT=122 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Data exporting was unselected.

-- Dumping structure for table rosheta.pharmacy_agents
CREATE TABLE IF NOT EXISTS `pharmacy_agents` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `pharmacy_id` bigint unsigned NOT NULL,
  `name` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `phone` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `email` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `active` tinyint(1) NOT NULL DEFAULT '1',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `pharmacy_agents_pharmacy_id_foreign` (`pharmacy_id`),
  CONSTRAINT `pharmacy_agents_pharmacy_id_foreign` FOREIGN KEY (`pharmacy_id`) REFERENCES `pharmacies` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=2 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Data exporting was unselected.

-- Dumping structure for table rosheta.sessions
CREATE TABLE IF NOT EXISTS `sessions` (
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

-- Data exporting was unselected.

-- Dumping structure for table rosheta.users
CREATE TABLE IF NOT EXISTS `users` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `name` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `email` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `email_verified_at` timestamp NULL DEFAULT NULL,
  `password` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `remember_token` varchar(100) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `pharmacy_id` bigint unsigned DEFAULT NULL,
  `laboratory_id` bigint unsigned DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `users_email_unique` (`email`),
  KEY `users_pharmacy_id_foreign` (`pharmacy_id`),
  KEY `users_laboratory_id_foreign` (`laboratory_id`),
  CONSTRAINT `users_laboratory_id_foreign` FOREIGN KEY (`laboratory_id`) REFERENCES `laboratories` (`id`) ON DELETE SET NULL,
  CONSTRAINT `users_pharmacy_id_foreign` FOREIGN KEY (`pharmacy_id`) REFERENCES `pharmacies` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB AUTO_INCREMENT=124 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Data exporting was unselected.

/*!40103 SET TIME_ZONE=IFNULL(@OLD_TIME_ZONE, 'system') */;
/*!40101 SET SQL_MODE=IFNULL(@OLD_SQL_MODE, '') */;
/*!40014 SET FOREIGN_KEY_CHECKS=IFNULL(@OLD_FOREIGN_KEY_CHECKS, 1) */;
/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40111 SET SQL_NOTES=IFNULL(@OLD_SQL_NOTES, 1) */;
