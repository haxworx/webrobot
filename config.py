#!/usr/bin/env python

import sys
import configparser


class Config:
    CONFIG_FILE = 'config.txt'

    def __init__(self):
        self.include_sitemaps = False
        self.ignore_query = False

        self.read()

    def read(self):
        try:
            with open(self.CONFIG_FILE, "r") as f:
                content = f.read()
                parser = configparser.ConfigParser()
                parser.read_string(content)

                keys = ('host', 'name', 'user', 'pass')
                if not all(key in parser['database'] for key in keys):
                    raise Exception("Missing database config field.")

                self.db_host = parser['database']['host']
                self.db_name = parser['database']['name']
                self.db_user = parser['database']['user']
                self.db_pass = parser['database']['pass']

                keys = ('interval', 'user-agent', 'wanted-content',
                        'ignore-query', 'retry-max', 'include-sitemaps')
                if not all(key in parser['crawling'] for key in keys):
                    raise Exception("Missing crawling config field.")

                self.crawl_interval = float(parser['crawling']['interval'])
                self.user_agent = parser['crawling']['user-agent']
                self.wanted_content = parser['crawling']['wanted-content']
                if parser['crawling']['ignore-query'].upper() == 'TRUE':
                    self.ignore_query = True
                self.retry_max = int(parser['crawling']['retry-max'])
                if parser['crawling']['include-sitemaps'].upper() == 'TRUE':
                    self.include_sitemaps = True
        except OSError as e:
            print("Unable to open '{}' -> {}" . format(CONFIG_FILE, e),
                  file=sys.stderr)
            sys.exit(1)
        except Exception as e:
            print("Error parsing '{}' -> {}" . format(CONFIG_FILE, e),
                  file=sys.stderr)
            sys.exit(1)
