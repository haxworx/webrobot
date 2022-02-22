#!/usr/bin/env python3

from logging import StreamHandler

class DatabaseHandler(StreamHandler):
    def __init__(self, cnx):
        StreamHandler.__init__(self);
        self.cnx = cnx

    def emit(self, record):
        msg = self.format(record)
        level_name = record.levelname
        level_number = record.levelno

        SQL = "INSERT INTO tbl_app_log (time_stamp, level_number, level_name, message) VALUES (NOW(), %s, %s, %s)"
        cursor = self.cnx.cursor()
        data = (level_number, level_name, msg)
        cursor.execute(SQL, data)
        self.cnx.commit()
