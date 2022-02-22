#!/usr/bin/env python3

from logging import StreamHandler

class DatabaseHandler(StreamHandler):
    def __init__(self, crawler):
        StreamHandler.__init__(self);
        self.crawler = crawler
        self.cnx = crawler.cnx

    def emit(self, record):
        msg = self.format(record)
        level_name = record.levelname
        level_number = record.levelno

        SQL = "INSERT INTO tbl_app_log (time_stamp, `crawler_name`, `hostname`, `ip_address`, level_number, level_name, message) VALUES (NOW(), %s, %s, %s, %s, %s, %s)"
        cursor = self.cnx.cursor()
        data = (self.crawler.name, self.crawler.hostname, self.crawler.ip_address, level_number, level_name, msg)
        cursor.execute(SQL, data)
        self.cnx.commit()
