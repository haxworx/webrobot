#!/usr/bin/env python

import sys
import configparser


class Config:
    CONFIG_FILE = 'config.txt'

    def __init__(self):
        self.include_sitemaps = False
        self.ignore_query = False

        self._read()

    def _read(self):
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

                keys = ('host', 'port', 'topic')
                if not all(key in parser['mqtt'] for key in keys):
                    raise Exception("Missing MQTT config field.")

                self.mqtt_host = parser['mqtt']['host']
                self.mqtt_topic = parser['mqtt']['topic']
                self.mqtt_port = int(parser['mqtt']['port'])

                keys = ('interval', 'user-agent', 'wanted-content',
                        'ignore-query', 'retry-max', 'import-sitemaps')
                if not all(key in parser['crawling'] for key in keys):
                    raise Exception("Missing crawling config field.")

                self.crawl_interval = float(parser['crawling']['interval'])
                self.user_agent = parser['crawling']['user-agent']
                self.wanted_content = parser['crawling']['wanted-content']
                if parser['crawling']['ignore-query'].upper() == 'TRUE':
                    self.ignore_query = True
                self.retry_max = int(parser['crawling']['retry-max'])
                if parser['crawling']['import-sitemaps'].upper() == 'TRUE':
                    self.import_sitemaps = True
        except OSError as e:
            print("Unable to open '{}' -> {}" . format(self.CONFIG_FILE, e),
                  file=sys.stderr)
            sys.exit(1)
        except Exception as e:
            print("Error parsing '{}' -> {}" . format(self.CONFIG_FILE, e),
                  file=sys.stderr)
            sys.exit(1)
