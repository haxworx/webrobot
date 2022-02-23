#!/usr/bin/env python

import sys
import configparser

CONFIG_FILE = 'config.txt'

class Config:
    def __init__(self):
        self.read()

    def read(self):
        try:
            with open(CONFIG_FILE, "r") as f:
                content = f.read()
                parser = configparser.ConfigParser()
                parser.read_string(content)

                if not all(key in parser['database'] for key in ('host', 'name', 'user', 'pass')):
                    raise Exception("Missing database config field.")
                self.db_host = parser['database']['host']
                self.db_name = parser['database']['name']
                self.db_user = parser['database']['user']
                self.db_pass = parser['database']['pass']
                if not all (key in parser['crawling'] for key in ('interval', 'user-agent', 'wanted-content', 'ignore-query', 'retry-max')):
                    raise Exception("Missing crawling config field.")
                self.crawl_interval = float(parser['crawling']['interval'])
                self.user_agent = parser['crawling']['user-agent']
                self.wanted_content = parser['crawling']['wanted-content']
                self.ignore_query = bool(parser['crawling']['ignore-query'])
                self.retry_max = int(parser['crawling']['retry-max'])
        except OSError as e:
            print("Unable to open '{}' -> {}" . format(CONFIG_FILE, e), file=sys.stderr)
            sys.exit(1)
        except Exception as e:
            print("Error parsing '{}' -> {}" . format(CONFIG_FILE, e), file=sys.stderr)
            sys.exit(1)


