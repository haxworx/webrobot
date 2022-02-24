#!/usr/bin/env python

import urllib.request
from urllib.parse import urljoin, urlparse
import re
import sys
import os
import socket
import atexit
import time, datetime
import signal
import logging
import hashlib
import mysql.connector
from mysql.connector import errorcode

from config import Config
from pages import PageList, Page
from robots_text import RobotsText
from download import Download
import logs

shutdown_gracefully = False

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
            self.log.warning("Ignoring path outside crawl parameters {} -> {}." . format(link, self.path_limit))
            return False

        for rule in self.robots_text.allowed:
            matches = re.search(rule, link)
            if matches:
                return True

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
        INSERT INTO tbl_crawl_data (date, time_stamp, time_zone, domain, scheme, link_source, modified, status_code, url, path, query, content_type, checksum, encoding, content)
            VALUES(NOW(), NOW(), 'Europe/London', %s, %s, %s, %s, %s, %s, %s, %s, %s, %s, %s, COMPRESS(%s))
        """
        val = (res['domain'], res['scheme'], res['link_source'], res['modified'], res['status_code'], res['url'], res['path'], res['query'], res['content_type'], res['checksum'], res['encoding'], res['content'])
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

    def save_errors(self, res):
        everything_is_fine = True

        SQL = """
        INSERT INTO tbl_crawl_errors (date, time_stamp, time_zone, status_code, url, link_source, description)
            VALUES(NOW(), NOW(), 'Europe/London', %s, %s, %s, %s)
        """
        val = (res['status_code'], res['url'], res['link_source'], res['description'])
        cursor = self.cnx.cursor()
        try:
            cursor.execute(SQL, val)
            self.cnx.commit()
        except mysql.connector.Error as e:
            self.log.fatal("Database: (%i) -> %s", e.errno, e.msg)
            everything_is_fine = False

        cursor.close()
        return everything_is_fine

    def crawl(self):
        """
        Crawling logic.
        """

        sitemap_urls = []

        self.log.info("Crawling %s", self.starting_url)
        self.robots_text.parse(self.starting_url)

        if self.config.include_sitemaps:
            sitemap_urls = self.robots_text.get_sitemap()

        self.page_list.append(self.robots_text.get_url())
        self.page_list.append(self.starting_url)

        if len(sitemap_urls):
            self.log.info("Total number of sitemap listed urls: {}" . format(len(sitemap_urls)))
        for url in sitemap_urls:
            if self.page_list.append(url, sitemap_url=True):
                self.log.info("Appending sitemap url: %s", url)

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
                self.log.info("Recording %s -> %i", self.url, e.code)
                res = { 'status_code': e.code, 'url': self.url,
                        'link_source': page.get_source(), 'description': e.reason
                }
                if not self.save_errors(res):
                    self.log.fatal("Terminating crawl. Unable to save errors.")
                    break

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
                modified = response.headers['last-modified']
                if modified is not None:
                    modified = datetime.datetime.strptime(modified, "%a, %d %b %Y %H:%M:%S %Z")

                matches = re.search(self.wanted_content, content_type, re.IGNORECASE)
                if not matches:
                    self.log.warning("Ignoring %s as %s", self.url, content_type)
                else:
                    # Have we redirected?
                    self.url = response.url
                    content_type = matches.group(1)
                    encoding = 'iso-8859-1'
                    matches = re.search('charset=([a-zA-Z0-9-_]+)', response.headers['content-type'], re.IGNORECASE)
                    if matches:
                        encoding = matches.group(1)

                    data = response.read()
                    content = data.decode(encoding)
                    checksum = hashlib.md5(data)

                    res = { 'domain': self.get_domain(self.url), 'scheme': scheme,
                            'link_source': page.get_source(), 'modified': modified,
                            'status_code': code, 'content_type': content_type,
                            'url': self.url, 'path': path,
                            'query': query, 'checksum': checksum.hexdigest(),
                            'content': content, 'encoding': encoding,
                    }

                    self.log.info("Saving %s", self.url)

                    if not self.save_results(res):
                        self.log.fatal("Terminating crawl. Unable to save results.")
                        break

                    # Don't scape links from sitemap listed URLs.
                    if not self.config.include_sitemaps or (self.config.include_sitemaps and not page.is_sitemap_source()):
                        links = re.findall("href=[\"\'](.*?)[\"\']", content)
                        for link in links:
                            if self.valid_link(link):
                                url = urljoin(self.url, link)
                                domain = self.get_domain(url)
                                if domain == self.domain:
                                    if self.page_list.append(url, link_source=page.url):
                                        self.log.info("Appending new url: %s", url)

                page.set_visited(True)
                time.sleep(self.config.crawl_interval)
                response.close()

                global shutdown_gracefully
                if shutdown_gracefully:
                    self.log.critical("Shutting down.")
                    break

        self.log.info("Done! Saved %s, attempted %s, total %s", crawler.save_count, crawler.attempted, len(crawler.page_list))

def signal_handler(signum, frame):
    global shutdown_gracefully
    if signum == signal.SIGINT:
        shutdown_gracefully = True

if __name__ == '__main__':
    if len(sys.argv) != 2:
        print("Usage: {} <url>" . format(sys.argv[0]))
        sys.exit(0)

    logging.basicConfig(level=logging.INFO)

    try:
        signal.signal(signal.SIGINT, signal_handler)
    except OSError as e:
        print("signal: {}" . format(e), file=sys.stderr)
        sys.exit(1)

    crawler = Robot(sys.argv[1], 'crawler')
    crawler.crawl()
