#!/usr/bin/env python

import sys
import os
import socket
import atexit
import time
import signal
import logging
import re
import hashlib
import mysql.connector
import fcntl
from datetime import datetime
from mysql.connector import errorcode
from urllib import error
from urllib.parse import urljoin, urlparse

import core
import logs
from config import Config
from pages import PageList, Page
from robots_text import RobotsText
from download import Download


class Robot:
    LOCK_FILE = 'crawl.lock'
    cnx = None

    def __init__(self, url, name):
        self.acquire_lock()
        atexit.register(self.cleanup)
        self.config = Config()
        self.starting_url = url

        self.name = name
        self.domain = self.domain_parse(url)
        self.page_list = PageList()
        self.robots_text = RobotsText(self)
        self.hostname = socket.gethostname()
        self.ip_address = socket.gethostbyname(self.hostname)
        self.database_connect()

        # Compile regular expressions.
        self.wanted_content = "^({})" . format(self.config.wanted_content)
        self.compile_regexes()

        # Create and set up database log handler.
        self.log = logging.getLogger(self.name)
        handler = logs.DatabaseHandler(self)
        self.log.addHandler(handler)

        # Restrict crawling based on starting url path (if exists).
        self.path_limit = urlparse(url).path
        if len(self.path_limit) and \
                self.path_limit[len(self.path_limit)-1] == '/':
            self.path_limit = self.path_limit[:len(self.path_limit)-1]

        self.save_count = 0
        self.attempted = 0
        self.retry_count = 0
        self.retry_max = self.config.retry_max

    def acquire_lock(self):
        try:
            self.lock = lock = open(self.LOCK_FILE, 'w+')
            fcntl.flock(lock, fcntl.LOCK_EX | fcntl.LOCK_NB)
        except BlockingIOError:
            print("Instance already running.", file=sys.stderr)
            sys.exit(0)
        except OSError as e:
            print("Unable to open '{}': {}". format(self.LOCK_FILE, e), file=sys.stderr)
            sys.exit(3)

    def release_lock(self):
        fcntl.flock(self.lock, fcntl.LOCK_UN)

    def compile_regexes(self):
        try:
            self.wanted = re.compile(self.wanted_content,
                                     re.IGNORECASE)
            self.charset = re.compile('charset=([a-zA-Z0-9-_]+)',
                                      re.IGNORECASE)
            self.hrefs = re.compile("href=[\"\'](.*?)[\"\']",
                                    re.IGNORECASE)
        except re.error as e:
            print("Regex compilation failed: {}" . format(e), file=sys.stderr)
            sys.exit(1)

    def cleanup(self):
        if self.cnx is not None:
            self.cnx.close()
        self.release_lock()

    def database_connect(self):
        try:
            self.cnx = mysql.connector.connect(user=self.config.db_user,
                                               password=self.config.db_pass,
                                               host=self.config.db_host,
                                               database=self.config.db_name)
        except mysql.connector.Error as e:
            print("Unable to connect ({}): {}" . format(e.errno, e.msg), file=sys.stderr)
            sys.exit(1)

    def domain_parse(self, url):
        domain = urlparse(url).netloc
        return domain

    def valid_link(self, link):
        """
        Check link is valid and against robot.txt rules.
        """

        if len(link) == 0:
            return False

        if link[0] != '/':
            return False

        if len(self.path_limit) and not link.startswith(self.path_limit):
            self.log.warning("Ignoring path outside crawl parameters {} -> {}."
                             . format(link, self.path_limit))
            return False

        for rule in self.robots_text.allowed():
            matches = re.search(rule, link)
            if matches:
                return True

        for rule in self.robots_text.disallowed():
            matches = re.search(rule, link)
            if matches:
                self.log.warning("robots: Ignoring %s as rule: '%s'",
                                 link, rule)
                return False
        return True

    def save_results(self, res):
        """
        Save crawl data into our database table.
        """
        everything_is_fine = True

        now = datetime.now()

        SQL = """
        INSERT INTO tbl_crawl_data (scan_date, scan_time_stamp,
        scan_time_zone, domain, scheme, link_source, modified,
        status_code, url, path, query, content_type, metadata,
        checksum, encoding, length, data) VALUES(%s, %s,
        'Europe/London', %s, %s, %s, %s, %s, %s, %s, %s, %s, %s,
        %s, %s, %s, COMPRESS(%s))
        """
        val = (now, now, res['domain'], res['scheme'],
               res['link_source'], res['modified'], res['status_code'],
               res['url'], res['path'], res['query'], res['content_type'],
               res['metadata'], res['checksum'], res['encoding'],
               res['length'], res['data'])
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

        now = datetime.now()

        SQL = """
        INSERT INTO tbl_crawl_errors (scan_date,
        scan_time_stamp, scan_time_zone, status_code,
        url, link_source, description) VALUES(%s, %s,
        'Europe/London', %s, %s, %s, %s)
        """
        val = (now, now, res['status_code'], res['url'],
               res['link_source'], res['description'])
        cursor = self.cnx.cursor()
        try:
            cursor.execute(SQL, val)
            self.cnx.commit()
        except mysql.connector.Error as e:
            self.log.fatal("Database: (%i) -> %s", e.errno, e.msg)
            everything_is_fine = False

        cursor.close()
        return everything_is_fine

    def metadata_extract(self, headers):
        metadata = ""
        for name, value in headers.items():
            metadata = metadata + name + ': ' + value + '\n'
        return metadata

    def import_sitemaps(self):
        for url in self.robots_text.sitemap_indexes():
            if core.shutdown_gracefully():
                break
            if self.page_list.append(url, sitemap_url=True):
                self.log.info("Appending sitemap index: %s", url)

        for url in self.robots_text.sitemap():
            if core.shutdown_gracefully():
                break
            if self.page_list.append(url, sitemap_url=True):
                self.log.info("Appending sitemap url: %s", url)

    def crawl(self):
        """
        Crawling logic.

        It's important to keep track of so many events.

        """
        self.log.info("Crawling %s", self.starting_url)
        self.robots_text.parse(self.starting_url)
        self.page_list.append(self.robots_text.url())

        if self.config.import_sitemaps:
            self.import_sitemaps()

        self.page_list.append(self.starting_url)

        for page in self.page_list:
            if core.shutdown_gracefully():
                break

            self.url = page.url()

            parsed_url = urlparse(self.url)
            (scheme, path, query) = (parsed_url.scheme, parsed_url.path,
                                     parsed_url.query)

            if self.config.ignore_query and len(query):
                self.log.warning("Ignoring URL '%s' with query string",
                                 self.url)
                continue
            self.attempted += 1
            try:
                downloader = Download(self.url, self.config.user_agent)
                (response, code) = downloader.get()
            except error.HTTPError as e:
                self.log.info("Recording %s -> %i", self.url, e.code)
                res = {'status_code': e.code,
                       'url': self.url,
                       'link_source': page.link_source(),
                       'description': e.reason}
                if not self.save_errors(res):
                    self.log.fatal("Terminating crawl. Unable to save errors.")
                    break

                page.visited = True
            except error.URLError as e:
                self.log.error("Unable to connect: %s -> %s",
                               e.reason, self.url)
                self.retry_count += 1

                if self.retry_count > self.retry_max:
                    self.log.fatal("Terminating crawl. "
                                   "Retry limit reached: %i",
                                   self.config.retry_max)
                    break
                else:
                    self.page_list.again()
                    self.log.warning("Retrying: %s", self.url)
                    continue
            except Exception as e:
                self.log.warning("Skipping due to exception: %s -> %s",
                                 self.url, e)
                continue
            else:
                self.retry_count = 0
                content_type = response.headers['content-type']
                modified = response.headers['last-modified']
                length = response.headers['content-length']
                if modified is not None:
                    modified = datetime.strptime(modified,
                                                 "%a, %d %b %Y %H:%M:%S %Z")
                if length is not None:
                    length = int(length)

                matches = self.wanted.search(content_type)
                if not matches:
                    self.log.warning("Ignoring %s as %s",
                                     self.url,
                                     content_type)
                else:
                    # Have we redirected?
                    if self.domain.upper() != \
                            self.domain_parse(response.url).upper():
                        self.log.warning("Ignoring redirected URL: {}"
                                         . format(response.url))
                        continue

                    self.url = response.url
                    metadata = self.metadata_extract(response.headers)
                    content_type = matches.group(1)
                    encoding = 'iso-8859-1'
                    matches = self.charset.search(
                        response.headers['content-type'])
                    if matches:
                        encoding = matches.group(1)

                    data = response.read()
                    checksum = hashlib.md5(data)

                    res = {'domain': self.domain_parse(self.url),
                           'scheme': scheme,
                           'link_source': page.link_source(),
                           'modified': modified,
                           'status_code': code,
                           'content_type': content_type,
                           'metadata': metadata,
                           'url': self.url,
                           'path': path,
                           'query': query,
                           'checksum': checksum.hexdigest(),
                           'encoding': encoding,
                           'length': length,
                           'data': data}

                    self.log.info("Saving %s", self.url)

                    if not self.save_results(res):
                        self.log.fatal("Terminating crawl. "
                                       "Unable to save results.")
                        break

                    # Don't scrape links from sitemap listed URLs.
                    if not self.config.include_sitemaps or \
                            (self.config.include_sitemaps and not page.is_sitemap_source()):
                        try:
                            content = data.decode(encoding)
                        except UnicodeDecodeError as e:
                            content = data.decode('iso-8859-1')
                        links = self.hrefs.findall(content)
                        for link in links:
                            if self.valid_link(link):
                                url = urljoin(self.url, link)
                                domain = self.domain_parse(url)
                                if domain.upper() == self.domain.upper():
                                    if self.page_list.append(url,
                                                             link_source=page.url()):
                                        self.log.info("Appending new url: %s",
                                                      url)

                page.visited = True
                time.sleep(self.config.crawl_interval)
                response.close()

        if core.shutdown_gracefully():
            self.log.critical("Shutting down.")

        self.log.info("Done! Saved %s, attempted %s, total %s",
                      crawler.save_count,
                      crawler.attempted,
                      len(crawler.page_list))


def signal_handler(signum, frame):
    if signum == signal.SIGINT:
        core.shutdown()


if __name__ == '__main__':
    if len(sys.argv) != 2:
        print("Usage: {} <url>" . format(sys.argv[0]))
        sys.exit(0)

    logging.basicConfig(level=logging.INFO)
    core.init()

    try:
        signal.signal(signal.SIGINT, signal_handler)
    except OSError as e:
        print("signal: {}" . format(e), file=sys.stderr)
        sys.exit(1)

    crawler = Robot(sys.argv[1], 'crawler')
    crawler.crawl()
