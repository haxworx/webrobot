DROP TABLE IF EXISTS `tbl_crawl_data`;
CREATE TABLE `tbl_crawl_data` (
  `id` int NOT NULL AUTO_INCREMENT,
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
);

DROP TABLE IF EXISTS `tbl_crawl_errors`;
CREATE TABLE `tbl_crawl_errors` (
  `id` int NOT NULL AUTO_INCREMENT,
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
);

DROP TABLE IF EXISTS `tbl_crawl_log`;
CREATE TABLE `tbl_crawl_log` (
  `id` int NOT NULL AUTO_INCREMENT,
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
);

DROP TABLE IF EXISTS `tbl_crawl_launch`;
CREATE TABLE `tbl_crawl_launch` (
  `id` int NOT NULL AUTO_INCREMENT,
  `extid` varchar(32) DEFAULT NULL,
  `scheme` varchar(32) DEFAULT NULL,
  `address` varchar(260) DEFAULT NULL,
  `domain` varchar(253) DEFAULT NULL,
  `agent` varchar(255) DEFAULT NULL,
  `start_time` time DEFAULT NULL,
  `weekly` boolean DEFAULT NULL,
  `daily` boolean DEFAULT NULL,
  `weekday` varchar(32) DEFAULT NULL,
  `last_ran` datetime DEFAULT NULL,
  `failed` boolean DEFAULT NULL,
  `errors` mediumtext DEFAULT NULL,
  PRIMARY KEY (`id`)
);

