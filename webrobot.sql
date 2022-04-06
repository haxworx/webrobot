DROP TABLE IF EXISTS `tbl_crawl_data`;
CREATE TABLE `tbl_crawl_data` (
  `id` int NOT NULL AUTO_INCREMENT,
  `bot_id` int DEFAULT NULL,
  `srv_date` date DEFAULT(CURRENT_DATE),
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
  `metadata` text DEFAULT NULL,
  `checksum` varchar(32) DEFAULT NULL,
  `encoding` varchar(32) DEFAULT NULL,
  `length` int DEFAULT NULL,
  `data` mediumblob,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

DROP TABLE IF EXISTS `tbl_crawl_errors`;
CREATE TABLE `tbl_crawl_errors` (
  `id` int NOT NULL AUTO_INCREMENT,
  `bot_id` int DEFAULT NULL,
  `srv_date` date DEFAULT(CURRENT_DATE),
  `srv_time_stamp` datetime DEFAULT CURRENT_TIMESTAMP,
  `scan_date` date DEFAULT NULL,
  `scan_time_stamp` datetime DEFAULT NULL,
  `scan_time_zone` varchar(64) DEFAULT NULL,
  `status_code` int DEFAULT NULL,
  `url` varchar(4096) DEFAULT NULL,
  `link_source` varchar(4096) DEFAULT NULL,
  `description` text DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

DROP TABLE IF EXISTS `tbl_crawl_log`;
CREATE TABLE `tbl_crawl_log` (
  `id` int NOT NULL AUTO_INCREMENT,
  `bot_id` int DEFAULT NULL,
  `srv_date` date DEFAULT(CURRENT_DATE),
  `srv_time_stamp` datetime DEFAULT CURRENT_TIMESTAMP,
  `scan_date` date DEFAULT NULL,
  `scan_time_stamp` datetime DEFAULT NULL,
  `crawler_name` varchar(32) DEFAULT NULL,
  `hostname` varchar(128) DEFAULT NULL,
  `ip_address` varchar(128) DEFAULT NULL,
  `level_number` INT DEFAULT NULL,
  `level_name` varchar(32) DEFAULT NULL,
  `message` text,
  PRIMARY KEY(`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

DROP TABLE IF EXISTS `tbl_crawl_settings`;
CREATE TABLE `tbl_crawl_settings` (
  `bot_id` int NOT NULL AUTO_INCREMENT,
  `user_id` int DEFAULT NULL,
  `scheme` varchar(32) DEFAULT NULL,
  `address` varchar(260) DEFAULT NULL,
  `domain` varchar(253) DEFAULT NULL,
  `agent` varchar(255) DEFAULT NULL,
  `delay` float DEFAULT NULL,
  `ignore_query` boolean DEFAULT NULL,
  `import_sitemaps` boolean DEFAULT NULL,
  `retry_max` int DEFAULT NULL,
  `start_time` time DEFAULT NULL,
  `end_time` datetime DEFAULT NULL,
  `daily` boolean DEFAULT NULL,
  `weekly` boolean DEFAULT NULL,
  `weekday` varchar(32) DEFAULT NULL,
  PRIMARY KEY (`bot_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

DROP TABLE IF EXISTS `tbl_content_types`;
CREATE TABLE `tbl_content_types` (
  `content_id` int NOT NULL AUTO_INCREMENT,
  `content_type` varchar(128) DEFAULT NULL,
  `description` text DEFAULT NULL,
  PRIMARY KEY(`content_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

DROP TABLE IF EXISTS `tbl_crawl_allowed_content`;
CREATE TABLE `tbl_crawl_allowed_content` (
  `id` int NOT NULL AUTO_INCREMENT,
  `bot_id` int DEFAULT NULL,
  `content_id` int DEFAULT NULL,
  PRIMARY KEY(`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

INSERT INTO `tbl_content_types` (`content_type`, `description`)
VALUES
    ('text/plain', 'Text'),
    ('text/html', 'HTML'),
    ('text/css', 'CSS'),
    ('application/xml', 'Application XML'),
    ('text/xml', 'Text XML'),
    ('application/json', 'Application JSON');

DROP TABLE IF EXISTS `tbl_global_settings`;
CREATE TABLE `tbl_global_settings` (
  `id` int NOT NULL AUTO_INCREMENT,
  `time_stamp` timestamp DEFAULT NULL,
  `in_use` boolean DEFAULT NULL,
  `max_crawlers` int DEFAULT NULL,
  `debug` boolean DEFAULT NULL,
  `docker_image` varchar(128) DEFAULT NULL,
  `mqtt_host` varchar(128) DEFAULT NULL,
  `mqtt_port` int DEFAULT NULL,
  `mqtt_topic` varchar(8192) DEFAULT NULL,
  PRIMARY KEY(`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

DROP TABLE IF EXISTS `tbl_users`;
CREATE TABLE `tbl_users` (
   `user_id` int NOT NULL AUTO_INCREMENT,
   `username` varchar(128) NOT NULL UNIQUE,
   `password` varchar(255) DEFAULT NULL,
   PRIMARY KEY(`user_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

INSERT INTO `tbl_global_settings`
    (`time_stamp`, `in_use`, `max_crawlers`, `debug`, `docker_image`, `mqtt_host`, `mqtt_port`, `mqtt_topic`)
    VALUES
    (NOW(), true, 5, true, 'spiderz', 'datacentre', 1883, 'testing');
