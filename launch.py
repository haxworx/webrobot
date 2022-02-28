#!/usr/bin/env python3

import os
import sys
import mysql.connector
from mysql.connector import errorcode
from urllib.parse import urlparse

import database
from config import Config

def main(url):
    ret = 1
    config = Config()

    parsed_url = urlparse(url)
    (scheme, domain) = (parsed_url.scheme, parsed_url.netloc)
    if len(scheme) == 0:
        sys.exit(3)

    dbh = database.Connect(config.db_user, config.db_pass,
                           config.db_host, config.db_name)

    SQL = """
    SELECT COUNT(*) FROM tbl_crawl_data WHERE domain = %s
    AND scan_date = DATE(NOW())
    """
    cursor = dbh.cnx.cursor()
    try:
        cursor.execute(SQL, (domain,))
        rows = cursor.fetchone()
    except mysql.connector.Error as e:
        print("Error: ({}) STATE: ({}) Message: ({})" . format(e.errno, e.sqlstate, e.msg))
        sys.exit(2)

    scan_count = rows[0]
    if scan_count == 0:
        ret = os.system("python3 webrobot.py {}" . format(url))

    cursor.close()
    dbh.close()

    sys.exit(ret)

if __name__ == '__main__':
    if len(sys.argv) != 2:
        sys.exit(4)

    main(sys.argv[1])
