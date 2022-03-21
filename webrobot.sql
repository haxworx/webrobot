DROP TABLE IF EXISTS `tbl_crawl_data`;
CREATE TABLE `tbl_crawl_data` (
  `id` int NOT NULL AUTO_INCREMENT,
  `botid` int DEFAULT NULL,
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
) ENGINE=InnoDB;

DROP TABLE IF EXISTS `tbl_crawl_errors`;
CREATE TABLE `tbl_crawl_errors` (
  `id` int NOT NULL AUTO_INCREMENT,
  `botid` int DEFAULT NULL,
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
) ENGINE=InnoDB;

DROP TABLE IF EXISTS `tbl_crawl_log`;
CREATE TABLE `tbl_crawl_log` (
  `id` int NOT NULL AUTO_INCREMENT,
  `botid` int DEFAULT NULL,
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
) ENGINE=InnoDB;

DROP TABLE IF EXISTS `tbl_crawl_settings`;
CREATE TABLE `tbl_crawl_settings` (
  `botid` int NOT NULL AUTO_INCREMENT,
  `scheme` varchar(32) DEFAULT NULL,
  `address` varchar(260) DEFAULT NULL,
  `domain` varchar(253) DEFAULT NULL,
  `agent` varchar(255) DEFAULT NULL,
  `delay` float DEFAULT NULL,
  `ignore_query` boolean DEFAULT NULL,
  `import_sitemaps` boolean DEFAULT NULL,
  `retry_max` int DEFAULT NULL,
  `start_time` time DEFAULT NULL,
  `daily` boolean DEFAULT NULL,
  `weekly` boolean DEFAULT NULL,
  `weekday` varchar(32) DEFAULT NULL,
  PRIMARY KEY (`botid`)
) ENGINE=InnoDB;

DROP TABLE IF EXISTS `tbl_content_types`;
CREATE TABLE `tbl_content_types` (
  `contentid` int NOT NULL AUTO_INCREMENT,
  `content_type` varchar(128) DEFAULT NULL,
  `description` text DEFAULT NULL,
  PRIMARY KEY(`contentid`)
) ENGINE=InnoDB;

DROP TABLE IF EXISTS `tbl_crawl_allowed_content`;
CREATE TABLE `tbl_crawl_allowed_content` (
  `id` int NOT NULL AUTO_INCREMENT,
  `botid` int DEFAULT NULL,
  `contentid` int DEFAULT NULL,
  PRIMARY KEY(`id`)
) ENGINE=InnoDB;

INSERT INTO `tbl_content_types` (`content_type`, `description`)
VALUES
    ('text/plain', 'Text'),
    ('text/html', 'HTML'),
    ('text/css', 'CSS'),
    ('application/xml', 'Application XML'),
    ('text/xml', 'Text XML');
