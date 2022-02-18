DROP TABLE IF EXISTS `tbl_crawl_data`;
CREATE TABLE `tbl_crawl_data` (
  `id` int NOT NULL AUTO_INCREMENT,
  `time_stamp` datetime DEFAULT NULL,
  `time_zone` varchar(64) DEFAULT NULL,
  `http_status_code` int DEFAULT NULL,
  `http_content_type` varchar(255) DEFAULT NULL,
  `scheme` varchar(32) DEFAULT NULL,
  `url` varchar(8192) DEFAULT NULL,
  `path` text,
  `query_string` text,
  `checksum` varchar(32) DEFAULT NULL,
  `encoding` varchar(32) DEFAULT NULL,
  `data` mediumtext,
  PRIMARY KEY (`id`)
);
