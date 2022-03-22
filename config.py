#!/usr/bin/env python

import sys
import configparser
import string

import database
from aws.password_vault import Vault

class Config:
    CONFIG_FILE = 'config.ini';

    def __init__(self, botid):
        self.include_sitemaps = False
        self.ignore_query = False
        self.aws_password_value = False
        self.botid = botid

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

                keys = ('password_vault', 'profile', 'secret', 'region')
                if not all(key in parser['aws'] for key in keys):
                    raise Exception("Missing AWS config field.");

                if parser['aws']['password_vault'].upper() == 'TRUE':
                    vault = Vault(parser['aws']['profile'],
                                  parser['aws']['region'],
                                  parser['aws']['secret'])
                    self.db_host = vault.contents['host']
                    self.db_name = vault.contents['dbname']
                    self.db_user = vault.contents['username']
                    self.db_pass = vault.contents['password']
                    vault.contents = None
                else:
                    self.db_host = parser['database']['host']
                    self.db_name = parser['database']['name']
                    self.db_user = parser['database']['user']
                    self.db_pass = parser['database']['pass']

                dbh = database.Connect(self.db_user, self.db_pass,
                                       self.db_host, self.db_name)

                SQL = """
                SELECT scheme, address, domain, agent, delay,
                ignore_query, import_sitemaps, retry_max
                FROM tbl_crawl_settings WHERE botid = %s
                """

                cursor = dbh.cnx.cursor()
                cursor.execute(SQL, [self.botid,])
                row = cursor.fetchall()
                row = row[0]
                cursor.close()

                self.scheme = row[0]
                self.address = row[1]
                self.domain = row[2]
                self.user_agent = row[3]
                self.crawl_interval = row[4]
                self.ignore_query = row[5]
                self.import_sitemaps = row[6]
                self.retry_max = row[7]

                SQL = """
                SELECT contentid FROM tbl_crawl_allowed_content
                WHERE botid = %s
                """

                content_ids = []
                cursor = dbh.cnx.cursor()
                cursor.execute(SQL, [self.botid,])
                rows = cursor.fetchall()
                for row in rows:
                    content_ids.append(row[0])
                cursor.close()

                s = ','.join(str(s) for s in content_ids)
                SQL = """
                SELECT content_type FROM tbl_content_types
                WHERE contentid IN ({}) """ . format(s)

                cursor = dbh.cnx.cursor()
                cursor.execute(SQL)
                rows = cursor.fetchall()
                cursor.close()
                self.wanted_content = '|' . join(str(s[0]) for s in rows)

                SQL = """
                SELECT mqtt_host, mqtt_port, mqtt_topic
                FROM tbl_global_settings ORDER BY id DESC LIMIT 1
                """
                cursor = dbh.cnx.cursor()
                cursor.execute(SQL, [])
                row = cursor.fetchall()
                row = row[0]
                cursor.close()
                self.mqtt_host = row[0]
                self.mqtt_port = row[1]
                self.mqtt_topic = row[2]

        except OSError as e:
            print("Unable to open '{}' -> {}" . format(self.CONFIG_FILE, e),
                  file=sys.stderr)
            sys.exit(1)
        except Exception as e:
            print("Error parsing '{}' -> {}" . format(self.CONFIG_FILE, e),
                  file=sys.stderr)
            sys.exit(1)
