#!/usr/bin/env python3

from logging import StreamHandler

class DatabaseHandler(StreamHandler):
    def __init__(self, cnx):
        StreamHandler.__init__(self);
        self.cnx = cnx

    def emit(self, record):
        msg = self.format(record)
        SQL = "INSERT INTO tbl_app_log (time_stamp, message) VALUES (NOW(), %s)"
        cursor = self.cnx.cursor()
        data = (msg, )
        cursor.execute(SQL, data)
        self.cnx.commit()
