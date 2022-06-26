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
import threading
import queue
from mysql.connector import errorcode
from urllib.parse import urlparse
from config import Config

RETRY_MAX = 35

queue = queue.Queue()

def launcher():
    while True:
        bot_id = queue.get();
        subprocess.Popen(["python3", "robot_start.py", str(bot_id)])
        queue.task_done();

def main():
    retry_count = 0
    config = Config(0)
    config.read_ini()

    threading.Thread(target=launcher, daemon=True).start();

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

    jobs = dict();
    jobs['ids'] = []
    jobs['current_time'] = "";

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

        # Keep track of current hour and minute.
        # Reset our jobs array if the time has incremented.
        now = datetime.datetime.now().strftime("%H:%M")
        if jobs['current_time'] != now:
            jobs['ids'] = []
        jobs['current_time'] = now

        for row in rows:
            bot_id, start_time = row[0], row[1]

            # Check whether the timestamp from our row matches the time stamp
            # of our jobs. If it matches and the id hasn't been dealt with
            # then pass the bot_id to our thread and add it to the list of ids.
            timestamp = (datetime.datetime.min + start_time).time().strftime("%H:%M")
            if timestamp == jobs['current_time'] and bot_id not in jobs['ids']:
                jobs['ids'].append(bot_id)
                queue.put(bot_id)

        cnx.commit()
        cursor.close()
        time.sleep(1)

    queue.join()
    cnx.close()

    return 0

if __name__ == '__main__':
    sys.exit(main())
