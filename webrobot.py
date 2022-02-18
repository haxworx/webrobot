#!/usr/bin/env python

import urllib.request
from urllib.parse import urljoin, urlparse
import re
import sys
import atexit
import logging
import configparser
import mysql.connector
import hashlib
from mysql.connector import errorcode

class Robot:
    def __init__(self, url):
        self.base_url = url
        result = urlparse(url)
        self.netloc = result.netloc;
        self.scheme = result.scheme;

        self.robot_text = self.RobotText()
        self.page_list = self.PageList()
        self.config_read()
        self.database_connect()
        atexit.register(self.cleanup)

    def cleanup(self):
        self.cnx.close()

    def database_connect(self):
        try:
            self.cnx = mysql.connector.connect(user=self.db_user,
                                               password=self.db_pass,
                                               host='127.0.0.1',
                                               database=self.db_name)
        except mysql.connector.Error as err:
            raise e

    def config_read(self):
        try:
            with open("config.txt", "r") as f:
                content = f.read()
                parser = configparser.ConfigParser()
                parser.read_string(content)
                self.db_name = parser['database']['name']
                self.db_user = parser['database']['user']
                self.db_pass = parser['database']['pass']
        except OSError as e:
            raise e

    class RobotText:
        """
        Handle robots.txt.
        """
        def __init__(self):
            self.version = "pythonbond/1.0"
            self.allowed = []
            self.restricted = []

    class PageList:
        """
        Simple class to keep track of pages.
        Provides an iterator and append method.
        """
        def __init__(self):
            self.page_list = []
            self.page_index = 0

        def __iter__(self):
            return self

        def __next__(self):
            while True:
                if self.page_index + 1 < len(self.page_list):
                    self.page_index += 1
                if self.page_list[self.page_index]['visited'] != True:
                    return self.page_list[self.page_index]
                else:
                    raise StopIteration

        def append(self, item):
            """
            Append a URL to the page list.
            Only appends when url is unseen/new.
            """
            exists = False
            for page in self.page_list:
                if item['url'] == page['url']:
                    exists = True
                    break
            if exists:
                return None
            else:
                self.page_list.append(item)
                return item

    def page_save(self, res):
        SQL = """
        INSERT INTO tbl_crawl_data (time_stamp, time_zone, http_status_code, http_content_type, scheme, url, path, query_string, checksum, encoding, data)
            VALUES(NOW(), 'Europe/London', %s, %s, %s, %s, %s, %s, %s, %s, %s)
        """
        val = (res['http_status_code'], res['http_content_type'], res['scheme'], res['url'], res['path'], res['query_string'], res['checksum'], res['encoding'], res['data'])
        cursor = self.cnx.cursor()
        cursor.execute(SQL, val)
        self.cnx.commit()

    def crawl(self):
        self.page_list.append( { "url": self.base_url, "visited": False })

        for page in self.page_list:
            self.url = page['url']
            logging.info("Parsing %s", self.url)
            try:
                response = urllib.request.urlopen(self.url)
            except urllib.error.HTTPError as e:
                logging.warning("Ignoring %s -> %i", self.url, e.code)
                page['visited'] = True
            except urllib.error.URLError as e:
                print("We failed to reach a server.")
                print("reason: {}" . format(e.reason))
                break
            else:
                code = response.getcode()
                content_type = response.info()["content-type"]
                matches = re.search('^(text/html|text/plain);\s*charset=([a-zA-Z0-9-_]*)', content_type, re.IGNORECASE)
                if matches:
                    content_type = matches.group(1)
                    encoding = matches.group(2)
                    data = response.read()
                    text = data.decode(encoding)
                    checksum = hashlib.md5(data)

                    parsed_url = urlparse(self.url)
                    res = { 'http_status_code': code, 'http_content_type': content_type,
                            'scheme': parsed_url.scheme, 'url': self.url, 'path': parsed_url.path,
                            'query_string': parsed_url.query, 'checksum': checksum.hexdigest(),
                            'data': text, 'encoding': encoding,
                    }
                    self.page_save(res)

                    links = re.findall("href=[\"\'](.*?)[\"\']", text)
                    for link in links:
                        if len(link) and link[0] == '/':
                            url = urljoin(self.url, link)
                            parsed_url = urlparse(url)
                            if parsed_url.scheme == self.scheme and parsed_url.netloc == self.netloc:
                                if self.page_list.append({ "url": url, "visited": False }) is not None:
                                    logging.info("Appending new url: %s", url)
                    response.close()
                else:
                    logging.warning("Ignoring %s as %s", self.url, content_type)
                page['visited'] = True

if __name__ == '__main__':
    if len(sys.argv) != 2:
        print("Usage: {} <url>" . format(sys.argv[0]))
        sys.exit(0)

    logging.basicConfig(level=logging.INFO)
    logging.info("Init.")

    crawler = Robot(sys.argv[1])
    crawler.crawl()

