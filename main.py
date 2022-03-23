#!/usr/bin/env python3

import sys
import os
import socket
import atexit
import time
import signal
import logging
import re
import hashlib
import fcntl
import mysql.connector
from datetime import datetime
from urllib import error
from urllib.parse import urljoin, urlparse
from mysql.connector import errorcode

import core
import logs
import database
from hypertext import Http
from config import Config
from pages import PageList, Page
from robots_text import RobotsText
from download import Download

class Robot:
    LOCK_FILE = 'data/crawl.lock'
    PIDFILE = 'data/crawl.pid'
    _botid = None
    _cnx = None
    _name = None
    _domain = None
    _ip_address = None
    _url = None
    _starting_url = None
    dbh = None

    def __init__(self, botid):
        self.botid = botid;
        self.acquire_lock()
        self.pidfile_create();
        atexit.register(self.cleanup)

        self.config = Config(self.botid)
        self.dbh = database.Connect(self.config.db_user, self.config.db_pass,
                                    self.config.db_host, self.config.db_name)
        self.starting_url = self.url = self.config.address
        self.user_agent = self.config.user_agent;
        self.scheme = self.config.scheme
        self.name = self.config.domain
        self.domain = self.config.domain
        self.hostname = socket.gethostname()
        self.ip_address = socket.gethostbyname(self.hostname)

        if self.name is None or self.scheme is None:
            print("Invalid URL: {}" . format(url), file=sys.stderr)
            sys.exit(1)

        self.page_list = PageList()
        self.robots_text = RobotsText(self)

        # Compile regular expressions.
        self.wanted_content = "^({})" . format(self.config.wanted_content)
        self.compile_regexes()

        # Create and set up database and MQTT log handler.
        self.log = logging.getLogger(self.name)
        self.log.addHandler(logs.DatabaseHandler(self))
        self.log.addHandler(logs.MQTTHandler(self))

        self.save_count = 0
        self.attempted = 0
        self.retry_count = 0

    @property
    def url(self):
        return self._url

    @url.setter
    def url(self, value):
        self._url = value

    @property
    def botid(self):
        return self._botid

    @botid.setter
    def botid(self, value):
        self._botid = value

    @property
    def name(self):
        return self._name

    @name.setter
    def name(self, name):
        self._name = name

    @property
    def hostname(self):
        return self._hostname

    @hostname.setter
    def hostname(self, hostname):
        self._hostname = hostname

    @property
    def ip_address(self):
        return self._ip_address

    @ip_address.setter
    def ip_address(self, ip_address):
        self._ip_address = ip_address

    @property
    def starting_url(self):
        return self._starting_url

    @starting_url.setter
    def starting_url(self, url):
        self._starting_url = url

    def pidfile_create(self):
        pid = str(os.getpid())
        with open(self.PIDFILE, 'w') as f:
            f.write(pid)

    def pidfile_delete(self):
        if os.path.isfile(self.PIDFILE):
            os.unlink(self.PIDFILE)

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
        os.unlink(self.LOCK_FILE)

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
        if self.dbh is not None:
            self.dbh.close()
        self.pidfile_delete()
        self.release_lock()

    def domain_parse(self, url):
        domain = urlparse(url).netloc
        if len(domain) == 0:
            return None
        return domain

    def scheme_parse(self, url):
        scheme = urlparse(url).scheme
        if len(scheme) == 0:
            return None
        return scheme

    def valid_link(self, link):
        """
        Check link is valid and against robot.txt rules.
        """

        if len(link) == 0:
            return False

        if link[0] != '/':
            return False

        for rule in self.robots_text.allowed:
            matches = re.search(rule, link)
            if matches:
                return True

        for rule in self.robots_text.disallowed:
            matches = re.search(rule, link)
            if matches:
                self.log.warning("/%s/%s/warning/robots/rule/ignore/%s",
                                 self.hostname, self.domain, link)
                return False
        return True

    def save_results(self, res):
        """
        Save crawl data into our database table.
        """
        everything_is_fine = True

        now = datetime.now()

        SQL = """
        INSERT INTO tbl_crawl_data (botid, scan_date, scan_time_stamp,
        scan_time_zone, domain, scheme, link_source, modified,
        status_code, url, path, query, content_type, metadata,
        checksum, encoding, length, data) VALUES (%s, %s, %s,
        'Europe/London', %s, %s, %s, %s, %s, %s, %s, %s, %s, %s,
        %s, %s, %s, COMPRESS(%s))
        """
        val = (res['botid'], now, now, res['domain'], res['scheme'],
               res['link_source'], res['modified'], res['status_code'],
               res['url'], res['path'], res['query'], res['content_type'],
               res['metadata'], res['checksum'], res['encoding'],
               res['length'], res['data'])
        cursor = self.dbh.cnx.cursor()
        try:
            cursor.execute(SQL, val)
            self.dbh.cnx.commit()
        except mysql.connector.Error as e:
            self.log.critical("/%s/%s/critical/database/save/%i/%s", self.hostname, self.domain, e.errno, e.msg)
            everything_is_fine = False

        cursor.close()
        self.save_count += 1

        return everything_is_fine

    def save_errors(self, res):
        everything_is_fine = True

        now = datetime.now()

        SQL = """
        INSERT INTO tbl_crawl_errors (botid, scan_date,
        scan_time_stamp, scan_time_zone, status_code,
        url, link_source, description) VALUES (%s, %s, %s,
        'Europe/London', %s, %s, %s, %s)
        """
        val = (res['botid'], now, now, res['status_code'], res['url'],
               res['link_source'], res['description'])
        cursor = self.dbh.cnx.cursor()
        try:
            cursor.execute(SQL, val)
            self.dbh.cnx.commit()
        except mysql.connector.Error as e:
            self.log.critical("/%s/%s/critical/database/save/errors/%i/%s", self.hostname, self.domain, e.errno, e.msg)
            everything_is_fine = False

        cursor.close()
        return everything_is_fine

    def metadata_extract(self, headers):
        metadata = ""
        for name, value in headers.items():
            metadata = metadata + name + ': ' + value + '\n'
        return metadata

    def import_sitemaps(self):
        for url in self.robots_text.sitemap_indexes:
            if core.shutdown_gracefully():
                break
            if self.page_list.append(url, sitemap_url=True):
                self.log.info("/%s/%s/info/sitemap/index/%s",
                              self.hostname, self.domain, url)

        for url in self.robots_text.sitemap:
            if core.shutdown_gracefully():
                break
            if self.page_list.append(url, sitemap_url=True):
                self.log.info("/%s/%s/info/sitemap/url/%s", self.hostname, self.domain, url)

    def crawl(self):
        """
        Crawling logic.

        It's important to keep track of so many events.

        """
        self.log.info("/%s/%s/info/start", self.hostname, self.domain)
        self.robots_text.parse(self.url)
        self.page_list.append(self.robots_text.url)

        if self.config.import_sitemaps:
            self.import_sitemaps()

        self.page_list.append(self.url)

        for page in self.page_list:
            if core.shutdown_gracefully():
                break

            self.url = page.url

            parsed_url = urlparse(self.url)
            (scheme, path, query) = (parsed_url.scheme, parsed_url.path,
                                     parsed_url.query)

            if self.config.ignore_query and len(query):
                continue
            self.attempted += 1
            try:
                downloader = Download(self.url, self.user_agent)
                (response, code) = downloader.get()
            except error.HTTPError as e:
                self.log.info("Recording %s -> %i", self.url, e.code)
                res = {'botid': self.botid,
                       'status_code': e.code,
                       'url': self.url,
                       'link_source': page.link_source,
                       'description': e.reason}
                if not self.save_errors(res):
                    self.log.critical("/%s/%s/critical/database/errors/save", self.hostname, self.domain)
                    break

            except error.URLError as e:
                self.log.error("/%s/%s/error/connect/%s/%s",
                               self.hostname, self.domain, e.reason, self.url)
                self.retry_count += 1

                if self.retry_count > self.config.retry_max:
                    self.log.critical("/%s/%s/critical/connect/retry_max/%i",
                                      self.hostname, self.domain, self.config.retry_max)
                    break
                else:
                    self.page_list.again()
                    self.log.warning("/%s/%s/warning/retry/%s", self.hostname, self.domain, self.url)
                    continue
            except Exception as e:
                self.log.warning("/%s/%s/warning/exception/%s/%s",
                                 self.hostname, self.domain, self.url, e)
                continue
            else:
                self.retry_count = 0
                content_type = Http.string(response.headers['content-type'])
                length = Http.int(response.headers['content-length'])
                modified = Http.date(response.headers['last-modified'])

                matches = self.wanted.search(content_type)
                if matches:
                    # Ignore redirects outside domain.
                    if self.domain.upper() != \
                            self.domain_parse(response.url).upper():
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

                    res = {'botid': self.botid,
                           'domain': self.domain_parse(self.url),
                           'scheme': scheme,
                           'link_source': page.link_source,
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

                    self.log.info("/%s/%s/info/save/%s", self.hostname, self.domain, self.url)

                    if not self.save_results(res):
                        self.log.critical("/%s/%s/critical/database/data/save",
                                          self.hostname, self.domain)
                        break

                    count = 0
                    # Don't scrape links from sitemap listed URLs.
                    if not self.config.import_sitemaps or \
                            (self.config.import_sitemaps and not page.is_sitemap_source):
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
                                                             link_source=page.url):
                                        count += 1
                        if count:
                            self.log.info("/%s/%s/info/found/%i/%s", self.hostname, self.domain, count, self.url)

                time.sleep(self.config.crawl_interval)
                response.close()

        if core.shutdown_gracefully():
            self.log.critical("/%s/%s/critical/interrupted", self.hostname, self.domain)

        self.log.info("/%s/%s/info/finished/saved/%i", self.hostname, self.domain, crawler.save_count)


def signal_handler(signum, frame):
    if signum == signal.SIGINT:
        core.shutdown()


if __name__ == '__main__':
    ROBOT_START = os.getenv('ROBOT_START')
    if ROBOT_START is None:
        print("This tool should not be launched directly.", file=sys.stderr)
        sys.exit(1)

    if len(sys.argv) != 2:
        print("Usage: {} <botid>" . format(sys.argv[0]))
        sys.exit(1)

    fmt = '/%(asctime)s%(message)s'
    datefmt = "%Y-%m-%d"

    logging.basicConfig(level=logging.INFO, format=fmt, datefmt=datefmt)
    core.init()

    try:
        signal.signal(signal.SIGINT, signal_handler)
    except OSError as e:
        print("signal: {}" . format(e), file=sys.stderr)
        sys.exit(1)

    crawler = Robot(int(sys.argv[1]))
    crawler.crawl()
