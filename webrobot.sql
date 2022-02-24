DROP TABLE IF EXISTS `tbl_crawl_data`;
CREATE TABLE `tbl_crawl_data` (
  `id` int NOT NULL AUTO_INCREMENT,
  `time_stamp` datetime DEFAULT NULL,
  `time_zone` varchar(64) DEFAULT NULL,
  `domain` varchar(253) DEFAULT NULL,
  `scheme` varchar(32) DEFAULT NULL,
  `url` varchar(8192) DEFAULT NULL,
  `status_code` int DEFAULT NULL,
  `path` text,
  `query` text,
  `content_type` varchar(255) DEFAULT NULL,
  `checksum` varchar(32) DEFAULT NULL,
  `encoding` varchar(32) DEFAULT NULL,
  `content` mediumblob,
  PRIMARY KEY (`id`)
);

DROP TABLE IF EXISTS `tbl_app_log`;
CREATE TABLE `tbl_app_log` (
  `id` int NOT NULL AUTO_INCREMENT,
  `time_stamp` datetime DEFAULT NULL,
  `crawler_name` varchar(32) DEFAULT NULL,
  `hostname` varchar(128) DEFAULT NULL,
  `ip_address` varchar(128) DEFAULT NULL,
  `level_number` INT DEFAULT NULL,
  `level_name` varchar(32) DEFAULT NULL,
  `message` text,
  PRIMARY KEY(`id`)
)
