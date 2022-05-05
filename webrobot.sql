-- MySQL dump 10.13  Distrib 8.0.23, for Linux (x86_64)
--
-- Host: localhost    Database: crawler
-- ------------------------------------------------------
-- Server version	8.0.23-3+b1

/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!50503 SET NAMES utf8mb4 */;
/*!40103 SET @OLD_TIME_ZONE=@@TIME_ZONE */;
/*!40103 SET TIME_ZONE='+00:00' */;
/*!40014 SET @OLD_UNIQUE_CHECKS=@@UNIQUE_CHECKS, UNIQUE_CHECKS=0 */;
/*!40014 SET @OLD_FOREIGN_KEY_CHECKS=@@FOREIGN_KEY_CHECKS, FOREIGN_KEY_CHECKS=0 */;
/*!40101 SET @OLD_SQL_MODE=@@SQL_MODE, SQL_MODE='NO_AUTO_VALUE_ON_ZERO' */;
/*!40111 SET @OLD_SQL_NOTES=@@SQL_NOTES, SQL_NOTES=0 */;

--
-- Table structure for table `content_types`
--

DROP TABLE IF EXISTS `content_types`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `content_types` (
  `content_id` int NOT NULL AUTO_INCREMENT,
  `content_type` varchar(128) DEFAULT NULL,
  `description` text,
  PRIMARY KEY (`content_id`)
) ENGINE=InnoDB AUTO_INCREMENT=7 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `crawl_allowed_content`
--

DROP TABLE IF EXISTS `crawl_allowed_content`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `crawl_allowed_content` (
  `id` int NOT NULL AUTO_INCREMENT,
  `bot_id` int DEFAULT NULL,
  `content_id` int DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `crawl_data`
--

DROP TABLE IF EXISTS `crawl_data`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `crawl_data` (
  `id` int NOT NULL AUTO_INCREMENT,
  `bot_id` int DEFAULT NULL,
  `srv_date` date DEFAULT (curdate()),
  `srv_time_stamp` datetime DEFAULT CURRENT_TIMESTAMP,
  `scan_date` date DEFAULT NULL,
  `scan_time_stamp` datetime DEFAULT NULL,
  `scan_time_zone` varchar(64) DEFAULT NULL,
  `domain` varchar(253) DEFAULT NULL,
  `scheme` varchar(32) DEFAULT NULL,
  `link_source` varchar(4096) DEFAULT NULL,
  `modified` datetime DEFAULT NULL,
  `url` varchar(4096) DEFAULT NULL,
  `status_code` int DEFAULT NULL,
  `path` text,
  `query` text,
  `content_type` varchar(255) DEFAULT NULL,
  `metadata` text,
  `checksum` varchar(32) DEFAULT NULL,
  `encoding` varchar(32) DEFAULT NULL,
  `length` int DEFAULT NULL,
  `data` mediumblob,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `crawl_errors`
--

DROP TABLE IF EXISTS `crawl_errors`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `crawl_errors` (
  `id` int NOT NULL AUTO_INCREMENT,
  `bot_id` int DEFAULT NULL,
  `srv_date` date DEFAULT (curdate()),
  `srv_time_stamp` datetime DEFAULT CURRENT_TIMESTAMP,
  `scan_date` date DEFAULT NULL,
  `scan_time_stamp` datetime DEFAULT NULL,
  `scan_time_zone` varchar(64) DEFAULT NULL,
  `status_code` int DEFAULT NULL,
  `url` varchar(4096) DEFAULT NULL,
  `link_source` varchar(4096) DEFAULT NULL,
  `description` text,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `crawl_log`
--

DROP TABLE IF EXISTS `crawl_log`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `crawl_log` (
  `id` int NOT NULL AUTO_INCREMENT,
  `bot_id` int DEFAULT NULL,
  `srv_date` date DEFAULT (curdate()),
  `srv_time_stamp` datetime DEFAULT CURRENT_TIMESTAMP,
  `scan_date` date DEFAULT NULL,
  `scan_time_stamp` datetime DEFAULT NULL,
  `crawler_name` varchar(32) DEFAULT NULL,
  `hostname` varchar(128) DEFAULT NULL,
  `ip_address` varchar(128) DEFAULT NULL,
  `level_number` int DEFAULT NULL,
  `level_name` varchar(32) DEFAULT NULL,
  `message` text,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=10 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `crawl_settings`
--

DROP TABLE IF EXISTS `crawl_settings`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `crawl_settings` (
  `bot_id` int NOT NULL AUTO_INCREMENT,
  `user_id` int DEFAULT NULL,
  `scheme` varchar(32) DEFAULT NULL,
  `address` varchar(260) DEFAULT NULL,
  `domain` varchar(253) DEFAULT NULL,
  `agent` varchar(255) DEFAULT NULL,
  `delay` float DEFAULT NULL,
  `ignore_query` tinyint(1) DEFAULT NULL,
  `import_sitemaps` tinyint(1) DEFAULT NULL,
  `retry_max` int DEFAULT NULL,
  `start_time` time DEFAULT NULL,
  `end_time` datetime DEFAULT NULL,
  PRIMARY KEY (`bot_id`)
) ENGINE=InnoDB AUTO_INCREMENT=25 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `doctrine_migration_versions`
--

DROP TABLE IF EXISTS `doctrine_migration_versions`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `doctrine_migration_versions` (
  `version` varchar(191) COLLATE utf8_unicode_ci NOT NULL,
  `executed_at` datetime DEFAULT NULL,
  `execution_time` int DEFAULT NULL,
  PRIMARY KEY (`version`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `global_settings`
--

DROP TABLE IF EXISTS `global_settings`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `global_settings` (
  `id` int NOT NULL AUTO_INCREMENT,
  `time_stamp` timestamp NULL DEFAULT NULL,
  `in_use` tinyint(1) DEFAULT NULL,
  `max_crawlers` int DEFAULT NULL,
  `debug` tinyint(1) DEFAULT NULL,
  `docker_image` varchar(128) DEFAULT NULL,
  `mqtt_host` varchar(128) DEFAULT NULL,
  `mqtt_port` int DEFAULT NULL,
  `mqtt_topic` varchar(8192) DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=2 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `messenger_messages`
--

DROP TABLE IF EXISTS `messenger_messages`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `messenger_messages` (
  `id` bigint NOT NULL AUTO_INCREMENT,
  `body` longtext COLLATE utf8mb4_unicode_ci NOT NULL,
  `headers` longtext COLLATE utf8mb4_unicode_ci NOT NULL,
  `queue_name` varchar(190) COLLATE utf8mb4_unicode_ci NOT NULL,
  `created_at` datetime NOT NULL,
  `available_at` datetime NOT NULL,
  `delivered_at` datetime DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `IDX_75EA56E0FB7336F0` (`queue_name`),
  KEY `IDX_75EA56E0E3BD61CE` (`available_at`),
  KEY `IDX_75EA56E016BA31DB` (`delivered_at`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `user`
--

DROP TABLE IF EXISTS `user`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `user` (
  `id` int NOT NULL AUTO_INCREMENT,
  `email` varchar(180) COLLATE utf8mb4_unicode_ci NOT NULL,
  `roles` json NOT NULL,
  `password` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `UNIQ_8D93D649E7927C74` (`email`)
) ENGINE=InnoDB AUTO_INCREMENT=2 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
/*!40103 SET TIME_ZONE=@OLD_TIME_ZONE */;

/*!40101 SET SQL_MODE=@OLD_SQL_MODE */;
/*!40014 SET FOREIGN_KEY_CHECKS=@OLD_FOREIGN_KEY_CHECKS */;
/*!40014 SET UNIQUE_CHECKS=@OLD_UNIQUE_CHECKS */;
/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
/*!40111 SET SQL_NOTES=@OLD_SQL_NOTES */;

-- Dump completed on 2022-05-04 17:43:14


INSERT INTO `content_types` (`content_type`, `description`)
VALUES
    ('text/plain', 'Text'),
    ('text/html', 'HTML'),
    ('text/css', 'CSS'),
    ('application/xml', 'Application XML'),
    ('text/xml', 'Text XML'),
    ('application/json', 'Application JSON');

INSERT INTO `global_settings`
    (`time_stamp`, `in_use`, `max_crawlers`, `debug`, `docker_image`, `mqtt_host`, `mqtt_port`, `mqtt_topic`)
    VALUES
    (NOW(), true, 5, true, 'spiderz', 'datacentre', 1883, 'testing');
