#!/usr/bin/env python3

import sys
from logging import StreamHandler
import mysql.connector
from mysql.connector import errorcode
from datetime import datetime


class DatabaseHandler(StreamHandler):
    def __init__(self, crawler):
        StreamHandler.__init__(self)
        self.crawler = crawler
        self.cnx = crawler.dbh.cnx

    def emit(self, record):
        msg = self.format(record)

        now = datetime.now()

        SQL = """
        INSERT INTO tbl_crawl_log (scan_date, scan_time_stamp,
        crawler_name, hostname, ip_address, level_number,
        level_name, message) VALUES (%s, %s, %s, %s, %s, %s, %s, %s)
        """
        cursor = self.cnx.cursor()
        data = (now, now, self.crawler.name, self.crawler.hostname,
                self.crawler.ip_address, record.levelno,
                record.levelname, msg)
        try:
            cursor.execute(SQL, data)
            self.cnx.commit()
        except mysql.connector.Error as e:
            print("Logging failed: see 'something_really_bad_happened.txt'",
                  file=sys.stderr)
            with open("something_really_bad_happened.txt", "w") as f:
                f.write("Logging failed:\n"
                        "\tError code: {}\n"
                        "\tSQLSTATE:   {}\n"
                        "\tMessage:    {}\n"
                        . format(e.errno, e.sqlstate, e.msg))
            sys.exit(2)

        cursor.close()
