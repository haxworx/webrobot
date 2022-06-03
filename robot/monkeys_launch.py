#!/usr/bin/env python3

# Launch our monkeys.

import os
import sys
import mysql.connector
import datetime
import database
import time
import subprocess
import signal
from mysql.connector import errorcode
from urllib.parse import urlparse
from config import Config

RETRY_MAX = 35

def main():
    retry_count = 0
    config = Config(0)
    config.read_ini()

    # Ensure we don't end up with a million zombies.
    signal.signal(signal.SIGCHLD, signal.SIG_IGN)

    # Wait for our container to come up. This should be enough time
    # to accomodate for the first-run database setup process.
    for _ in range(0, 36):
        try:
            cnx = mysql.connector.connect(user=config.db_user,
                                          host=config.db_host,
                                          password=config.db_pass,
                                          database=config.db_name)
        except mysql.connector.Error as e:
            retry_count += 1
            if retry_count >= RETRY_MAX:
                print("Error: ({}) STATE: ({}) Message: ({}) Block: Retry" . format(e.errno, e.sqlstate, e.msg), file=sys.stderr)
                sys.exit(1)
            else:
                time.sleep(1)
                print("Retry...{}" . format(retry_count), file=sys.stderr)
                continue

    seen = dict();
    seen['ids'] = []
    seen['time'] = "";

    while True:

        SQL = """
        SELECT bot_id, start_time FROM crawl_settings
        """
        cursor = cnx.cursor()
        try:
            cursor.execute(SQL, ())
            rows = cursor.fetchall()
        except mysql.connector.Error as e:
            print("Error: ({}) STATE: ({}) Message: ({})" . format(e.errno, e.sqlstate, e.msg), file=sys.stderr)
            sys.exit(2)

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
                subprocess.Popen(["python3", "robot_start.py", str(bot_id)])

        cnx.commit()
        cursor.close()
        time.sleep(1)

    cnx.close()
    return 0

if __name__ == '__main__':
    sys.exit(main())
