#!/usr/bin/env python

import urllib.request
from urllib.parse import urljoin, urlparse
import re
import sys
import atexit
import time
import string
import logging
import hashlib
import mysql.connector
from mysql.connector import errorcode

from config import Config
from pages import PageList, Page
from robots_text import RobotsText

class Robot:
    def __init__(self, url):
        self.base_url = url
        self.hostname = self.get_hostname(url)
        self.robots_text = RobotsText()
        self.page_list = PageList()
        self.config = Config()
        self.database_connect()
        atexit.register(self.cleanup)

        self.save_count = 0
        self.attempted = 0

    def cleanup(self):
        self.cnx.close()

    def database_connect(self):
        try:
            self.cnx = mysql.connector.connect(user=self.config.db_user,
                                               password=self.config.db_pass,
                                               host=self.config.db_host,
                                               database=self.config.db_name)
        except mysql.connector.Error as err:
            raise e

    def get_hostname(self, url):
        netloc = urlparse(url).netloc
        hostname = netloc.split('.')
        result = '.'
        for i, subhostname in enumerate(hostname):
            if subhostname == "www":
                hostname.pop(i)
        return result.join(hostname)

    def valid_link(self, link):
        if len(link) and link[0] == '/':
            return True
        return False

    def save_results(self, res):
        SQL = """
        INSERT INTO tbl_crawl_data (time_stamp, time_zone, domain, http_status_code, http_content_type, scheme, url, path, query_string, checksum, encoding, data)
            VALUES(NOW(), 'Europe/London', %s, %s, %s, %s, %s, %s, %s, %s, %s, %s)
        """
        val = (res['domain'], res['http_status_code'], res['http_content_type'], res['scheme'], res['url'], res['path'], res['query_string'], res['checksum'], res['encoding'], res['data'])
        cursor = self.cnx.cursor()
        cursor.execute(SQL, val)
        self.cnx.commit()

        self.save_count += 1

    def crawl(self):
        page = Page(self.base_url)
        self.page_list.append(page)

        for page in self.page_list:
            self.attempted += 1
            self.url = page.get_url()
            try:
                request = urllib.request.Request(self.url)
                request.add_header('User-Agent', self.robots_text.version)
                response = urllib.request.urlopen(request)
                code = response.getcode()
            except urllib.error.HTTPError as e:
                logging.warning("Ignoring %s -> %i", self.url, e.code)
                page.set_visited(True)
            except urllib.error.URLError as e:
                print("Unable to connect: {}" . format(e.reason))
                break
            else:
                content_type = response.headers["content-type"]
                matches = re.search('^(text/html|text/plain);\s*charset=([a-zA-Z0-9-_]*)', content_type, re.IGNORECASE)
                if not matches:
                    logging.warning("Ignoring %s as %s", self.url, content_type)
                else:
                    # Have we redirected?
                    self.url = response.url
                    content_type = matches.group(1)
                    encoding = matches.group(2)
                    data = response.read()
                    text = data.decode(encoding)
                    checksum = hashlib.md5(data)

                    logging.info("Saving %s", self.url)

                    parsed_url = urlparse(self.url)
                    res = { 'domain': self.get_hostname(self.url), 'http_status_code': code, 'http_content_type': content_type,
                            'scheme': parsed_url.scheme, 'url': self.url, 'path': parsed_url.path,
                            'query_string': parsed_url.query, 'checksum': checksum.hexdigest(),
                            'data': text, 'encoding': encoding,
                    }
                    self.save_results(res)

                    links = re.findall("href=[\"\'](.*?)[\"\']", text)
                    for link in links:
                        if self.valid_link(link):
                            url = urljoin(self.url, link)
                            hostname = self.get_hostname(url)
                            if hostname == self.hostname:
                                page = Page(url)
                                if self.page_list.append(page) is not None:
                                    logging.info("Appending new url: %s", url)
                    response.close()
                page.set_visited(True)
                time.sleep(self.config.crawl_interval)

if __name__ == '__main__':
    if len(sys.argv) != 2:
        print("Usage: {} <url>" . format(sys.argv[0]))
        sys.exit(0)

    logging.basicConfig(level=logging.INFO)
    logging.info("Init.")

    crawler = Robot(sys.argv[1])
    logging.info("Slurping %s", crawler.hostname)
    crawler.crawl()

    print("saved {} attempted {} total {}" . format(crawler.save_count, crawler.attempted, len(crawler.page_list)))
    logging.info("Done.")

