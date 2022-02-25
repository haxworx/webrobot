DROP TABLE IF EXISTS `tbl_crawl_data`;
CREATE TABLE `tbl_crawl_data` (
  `id` int NOT NULL AUTO_INCREMENT,
  `date` date DEFAULT NULL,
  `time_stamp` datetime DEFAULT NULL,
  `time_zone` varchar(64) DEFAULT NULL,
  `domain` varchar(253) DEFAULT NULL,
  `scheme` varchar(32) DEFAULT NULL,
  `link_source` varchar(4096) DEFAULT NULL,
  `modified` datetime DEFAULT NULL,
  `url` varchar(4096) DEFAULT NULL,
  `status_code` int DEFAULT NULL,
  `path` text,
  `query` text,
  `content_type` varchar(255) DEFAULT NULL,
  `checksum` varchar(32) DEFAULT NULL,
  `encoding` varchar(32) DEFAULT NULL,
  `data` mediumblob,
  PRIMARY KEY (`id`)
);

DROP TABLE IF EXISTS `tbl_crawl_errors`;
CREATE TABLE `tbl_crawl_errors` (
  `id` int NOT NULL AUTO_INCREMENT,
  `date` date DEFAULT NULL,
  `time_stamp` datetime DEFAULT NULL,
  `time_zone` varchar(64) DEFAULT NULL,
  `status_code` int DEFAULT NULL,
  `url` varchar(4096) DEFAULT NULL,
  `link_source` varchar(4096) DEFAULT NULL,
  `description` text DEFAULT NULL,
  PRIMARY KEY (`id`)
);

DROP TABLE IF EXISTS `tbl_app_log`;
CREATE TABLE `tbl_app_log` (
  `id` int NOT NULL AUTO_INCREMENT,
  `date` date DEFAULT NULL,
  `time_stamp` datetime DEFAULT NULL,
  `crawler_name` varchar(32) DEFAULT NULL,
  `hostname` varchar(128) DEFAULT NULL,
  `ip_address` varchar(128) DEFAULT NULL,
  `level_number` INT DEFAULT NULL,
  `level_name` varchar(32) DEFAULT NULL,
  `message` text,
  PRIMARY KEY(`id`)
)
