#!/usr/bin/env python

import urllib.request
from urllib.parse import urljoin, urlparse
import re
import sys
import atexit
import time
import logging
import hashlib
import mysql.connector
from mysql.connector import errorcode

from config import Config
from pages import PageList, Page
from robots_text import RobotsText
from download import Download
import logs

class Robot:
    def __init__(self, url):
        self.base_url = url
        self.hostname = self.get_hostname(url)
        self.page_list = PageList()
        self.config = Config()
        self.robots_text = RobotsText(self.config.user_agent)
        self.database_connect()
        self.wanted_content = "^({})" . format(self.config.wanted_content)
        atexit.register(self.cleanup)

        self.log = logging.getLogger('crawl')
        handler = logs.DatabaseHandler(self.cnx)
        self.log.addHandler(handler)

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
        if len(link) == 0 or link[0] != '/':
            return False
        for rule in self.robots_text.disallowed:
            matches = re.search(rule, link)
            if matches:
                self.log.warning("robots: Ignoring %s as rule: '%s'", link, rule)
                return False
        return True

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

        self.log.info("Crawling %s", self.base_url)
        self.robots_text.parse(self.base_url)
        self.page_list.append(self.robots_text.get_url())
        self.page_list.append(self.base_url)

        for page in self.page_list:
            self.attempted += 1
            self.url = page.get_url()
            parsed_url = urlparse(self.url)

            if not self.valid_link(parsed_url.path):
                continue
            try:
                downloader = Download(self.url, self.config.user_agent)
                (response, code) = downloader.get()
            except urllib.error.HTTPError as e:
                self.log.warning("Ignoring %s -> %i", self.url, e.code)
                page.set_visited(True)
                response.close()
            except urllib.error.URLError as e:
                print("Unable to connect: {}" . format(e.reason))
                break
            else:
                content_type = response.headers['content-type']
                matches = re.search(self.wanted_content, content_type, re.IGNORECASE)
                if not matches:
                    self.log.warning("Ignoring %s as %s", self.url, content_type)
                else:
                    # Have we redirected?
                    self.url = response.url
                    content_type = matches.group(1)
                    encoding = 'utf-8'
                    matches = re.search('charset=([a-zA-Z0-9-_]*)', content_type, re.IGNORECASE)
                    if matches:
                        encoding = matches.group(1)

                    data = response.read()
                    text = data.decode(encoding)
                    checksum = hashlib.md5(data)

                    self.log.info("Saving %s", self.url)

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
                                if self.page_list.append(url):
                                    self.log.info("Appending new url: %s", url)
                page.set_visited(True)
                time.sleep(self.config.crawl_interval)
                response.close()
        self.log.info("Done! Saved %s, attempted %s, total %s", crawler.save_count, crawler.attempted, len(crawler.page_list))

if __name__ == '__main__':
    if len(sys.argv) != 2:
        print("Usage: {} <url>" . format(sys.argv[0]))
        sys.exit(0)

    logging.basicConfig(level=logging.INFO)

    crawler = Robot(sys.argv[1])
    crawler.crawl()

