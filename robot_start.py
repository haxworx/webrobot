#!/usr/bin/env python3

import os
import sys
import mysql.connector
from mysql.connector import errorcode
from urllib.parse import urlparse

import database
from config import Config

def main(botid):
    ret = 1
    config = Config(botid)
    config.read_ini()

    dbh = database.Connect(config.db_user, config.db_pass,
                           config.db_host, config.db_name)

    SQL = """
    SELECT COUNT(*) FROM tbl_crawl_data WHERE botid = %s
    AND scan_date = DATE(NOW())
    """
    cursor = dbh.cnx.cursor()
    try:
        cursor.execute(SQL, (botid,))
        rows = cursor.fetchone()
    except mysql.connector.Error as e:
        print("Error: ({}) STATE: ({}) Message: ({})" . format(e.errno, e.sqlstate, e.msg), file=sys.stderr)
        sys.exit(2)

    scan_count = rows[0]
    if scan_count == 0:
        os.environ['ROBOT_START'] = "1"
        ret = os.system("python3 main.py {}" . format(botid))

    cursor.close()
    dbh.close()

    return ret

if __name__ == '__main__':
    if len(sys.argv) != 2:
        print("ERR: argument count", file=sys.stderr)
        sys.exit(4)

    sys.exit(main(sys.argv[1]))
