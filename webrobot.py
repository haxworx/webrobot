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
import socket

from config import Config
from pages import PageList, Page
from robots_text import RobotsText
from download import Download
import logs

class Robot:
    def __init__(self, url, name):
        self.config = Config()
        self.starting_url = url

        self.domain = self.get_domain(url)
        self.page_list = PageList()
        self.robots_text = RobotsText(self)
        self.hostname = socket.gethostname()
        self.ip_address = socket.gethostbyname(self.hostname)
        self.database_connect()
        self.wanted_content = "^({})" . format(self.config.wanted_content)
        self.name = name

        self.log = logging.getLogger(self.name)
        handler = logs.DatabaseHandler(self)
        self.log.addHandler(handler)

        self.path_limit = urlparse(url).path
        if len(self.path_limit) and self.path_limit[len(self.path_limit)-1] == '/':
            self.path_limit = self.path_limit[:len(self.path_limit)-1]

        self.save_count = 0
        self.attempted = 0
        self.retry_count = 0
        self.retry_max = self.config.retry_max

        atexit.register(self.cleanup)

    def cleanup(self):
        self.cnx.close()

    def database_connect(self):
        try:
            self.cnx = mysql.connector.connect(user=self.config.db_user,
                                               password=self.config.db_pass,
                                               host=self.config.db_host,
                                               database=self.config.db_name)
        except mysql.connector.Error as e:
            print("Unable to connect ({}): {}" . format(e.errno, e.msg))
            sys.exit(1)

    def get_domain(self, url):
        """
        Crudely obtain domain name of URL.
        """

        netloc = urlparse(url).netloc
        domain = netloc.split('.')
        result = '.'
        for i, subdomain in enumerate(domain):
            if subdomain == "www":
                domain.pop(i)
        return result.join(domain)

    def valid_link(self, link):
        """
        Is link a valid link to be attempted?
        """

        if len(link) and link[0] != '/':
            return False

        if len(self.path_limit) and not link.startswith(self.path_limit):
            self.log.warning("robots: Ignoring path outside crawl parameters {} -> {}." . format(link, self.path_limit))
            return False

        for rule in self.robots_text.disallowed:
            matches = re.search(rule, link)
            if matches:
                self.log.warning("robots: Ignoring %s as rule: '%s'", link, rule)
                return False
        return True

    def save_results(self, res):
        """
        Save crawl data into our database table.
        """
        everything_is_fine = True

        SQL = """
        INSERT INTO tbl_crawl_data (time_stamp, time_zone, domain, scheme, status_code, url, path, query, content_type, checksum, encoding, content)
            VALUES(NOW(), 'Europe/London', %s, %s, %s, %s, %s, %s, %s, %s, %s, %s)
        """
        val = (res['domain'], res['scheme'], res['status_code'], res['url'], res['path'], res['query'], res['content_type'], res['checksum'], res['encoding'], res['content'])
        cursor = self.cnx.cursor()
        try:
           cursor.execute(SQL, val)
           self.cnx.commit()
        except mysql.connector.Error as e:
            self.log.fatal("Database: (%i) -> %s", e.errno, e.msg)
            everything_is_fine = False

        cursor.close()
        self.save_count += 1

        return everything_is_fine

    def crawl(self):
        """
        Crawling logic.
        """

        self.log.info("Crawling %s", self.starting_url)
        self.robots_text.parse(self.starting_url)
        self.page_list.append(self.robots_text.get_url())
        self.page_list.append(self.starting_url)

        for page in self.page_list:
            self.attempted += 1
            self.url = page.get_url()

            parsed_url = urlparse(self.url)
            (scheme, path, query) = (parsed_url.scheme, parsed_url.path, parsed_url.query)

            if self.config.ignore_query and len(query):
                self.log.warning("Ignoring URL '%s' with query string", self.url)
                continue

            try:
                downloader = Download(self.url, self.config.user_agent)
                (response, code) = downloader.get()
            except urllib.error.HTTPError as e:
                self.log.warning("Ignoring %s -> %i", self.url, e.code)
                page.set_visited(True)
            except urllib.error.URLError as e:
                self.log.error("Unable to connect: %s -> %s", e.reason, self.url)
                self.retry_count += 1
                if self.retry_count < self.retry_max:
                    self.page_list.again()
                    self.log.warning("Retrying: %s", self.url)
                    continue
                else:
                    self.log.fatal("Terminating crawl. Retry limit reached: %i", self.config.retry_max)
                    break
            else:
                self.retry_count = 0
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
                    content = data.decode(encoding)
                    checksum = hashlib.md5(data)

                    res = { 'domain': self.get_domain(self.url), 'scheme': scheme,
                            'status_code': code, 'content_type': content_type,
                            'url': self.url, 'path': path,
                            'query': query, 'checksum': checksum.hexdigest(),
                            'content': content, 'encoding': encoding,
                    }

                    self.log.info("Saving %s", self.url)

                    if not self.save_results(res):
                        self.log.fatal("Terminating crawl. Unable to save results.")
                        break

                    links = re.findall("href=[\"\'](.*?)[\"\']", content)
                    for link in links:
                        if self.valid_link(link):
                            url = urljoin(self.url, link)
                            domain = self.get_domain(url)
                            if domain == self.domain:
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

    crawler = Robot(sys.argv[1], 'crawler')
    crawler.crawl()

