#!/usr/bin/env python3

# Launch our monkeys.

import os
import sys
import mysql.connector
import datetime
import database
import time
import subprocess
from mysql.connector import errorcode
from urllib.parse import urlparse
from config import Config

RETRY_MAX = 10

def main():
    retry_count = 0
    config = Config(0)
    config.read_ini()
    dbh = database.Connect(config.db_user, config.db_pass,
                           config.db_host, config.db_name)

    seen = dict();
    seen['ids'] = []
    seen['time'] = "";

    while True:

        SQL = """
        SELECT bot_id, start_time FROM crawl_settings
        """
        cursor = dbh.cnx.cursor()
        try:
            cursor.execute(SQL, ())
            rows = cursor.fetchall()
        except mysql.connector.Error as e:
            retry_count += 1
            if retry_count >= RETRY_MAX:
                print("Error: ({}) STATE: ({}) Message: ({})" . format(e.errno, e.sqlstate, e.msg), file=sys.stderr)
                sys.exit(2)
            else:
                print("retry...{}" . format(retry_count))
                time.sleep(1)
                continue

        now = datetime.datetime.now().strftime("%H:%M")

        if seen['time'] != now:
            seen['ids'] = []
        seen['time'] = now

        for row in rows:
            bot_id, start_time = row[0], row[1]
            start_time = (datetime.datetime.min + start_time).time()
            timestamp = start_time.strftime("%H:%M")
            if timestamp == seen['time'] and bot_id not in seen['ids']:
                seen['ids'].append(bot_id)
                subprocess.run(["python3", "robot_start.py", str(bot_id)])

        dbh.cnx.commit()
        cursor.close()
        time.sleep(1)

    dbh.close()
    return 0

if __name__ == '__main__':
    sys.exit(main())
