#!/usr/bin/env python3

import urllib
from urllib.parse import urlparse
import re
import sys
import xml.dom.minidom

import core
from download import Download


class RobotsText:
    """
    Handle robots.txt.

    As of 2022-02-24 this handles as much of the robots.txt
    spec as Google itself honours with their web crawlers.

    """

    def __init__(self, crawler):
        self._crawler = crawler
        self._user_agent = crawler.user_agent
        self._agents = dict()
        self._allowed = []
        self._disallowed = []
        self._sitemap_indexes = []
        self._sitemap_urls = []
        self._url = None

    def regexify(self, string):
        string = string.replace('.', '\.')
        string = string.replace('*', '.*')
        string = string.replace('?', '\?')
        return string

    def parse(self, url):
        parsed_url = urlparse(url)
        self._url = parsed_url.scheme + '://' + parsed_url.netloc + '/robots.txt'
        try:
            downloader = Download(self._url, self._user_agent)
            (response, code) = downloader.get()
        except urllib.error.HTTPError as e:
            self._crawler.log.warning("warning/robots/%i/%s",
                                      e.code, self._url)
        except urllib.error.URLError as e:
            self._crawler.log.warning("warning/robots/connect/%s", e.reason)
        except Exception as e:
            self._crawler.log.warning("warning/robots/exception/%s", e)
        else:
            data = response.read()
            response.close()
            text = data.decode('utf-8')
            lines = text.split('\n')
            agent = None

            for line in lines:
                if len(line) and line[0] == '#':
                    continue
                unwanted = '\r\n'
                line = line.translate({ord(i): None for i in unwanted})
                matches = re.search('^User-Agent:\s*(.*?)$',
                                    line,
                                    re.IGNORECASE)
                if matches:
                    agent = matches.group(1)
                    if agent not in self._agents:
                        self._agents[agent] = {'allowed': [],
                                               'disallowed': [],
                                               'sitemaps': []}
                if agent is None:
                    continue
                matches = re.search('^Allow:\s+(.*?)$', line, re.IGNORECASE)
                if matches:
                    self._agents[agent]['allowed'].append(matches.group(1))
                matches = re.search('^Disallow:\s+(.*?)$', line, re.IGNORECASE)
                if matches:
                    self._agents[agent]['disallowed'].append(matches.group(1))
                matches = re.search('^Sitemap:\s+(.*?)$', line, re.IGNORECASE)
                if matches:
                    self._agents[agent]['sitemaps'].append(matches.group(1))

            for agent, rules in self._agents.items():
                if agent == '*' or agent == self._user_agent:
                    for path in rules['allowed']:
                        path = self.regexify(path)
                        self._allowed.append(path)
                    for path in rules['disallowed']:
                        path = self.regexify(path)
                        self._disallowed.append(path)
                    for url in rules['sitemaps']:
                        self._sitemap_indexes.append(url)

            sitemaps = SiteMaps(self._sitemap_indexes, self._user_agent)
            sitemaps.parse()
            self._sitemap_indexes.extend(sitemaps.sitemap_indexes())
            self._sitemap_urls = sitemaps.urls()

    @property
    def url(self):
        return self._url

    @property
    def allowed(self):
        return self._allowed

    @property
    def disallowed(self):
        return self._disallowed

    @property
    def sitemap(self):
        return self._sitemap_urls

    @property
    def sitemap_indexes(self):
        return self._sitemap_indexes


class SiteMaps:
    """
    Download, parse and collect sitemap URLs from
    sitemap indexes and sitemaps.
    """

    def __init__(self, sitemap_indexes, user_agent):
        self._user_agent = user_agent
        self._sitemap_indexes = sitemap_indexes
        self._sitemap_urls = []

    def parse(self):
        for sitemap in self._sitemap_indexes:
            downloader = Download(sitemap, self._user_agent)
            contents = downloader.contents()
            if contents is None:
                continue

            # Check DOM to determine whether this is a sitemap
            # index or a list of URLs.
            dom = xml.dom.minidom.parseString(contents)
            sitemap_index = dom.getElementsByTagName('sitemap')
            if not sitemap_index:
                self.read_sitemap(contents)
            else:
                for url in sitemap_index:
                    nodes = url.getElementsByTagName('loc')
                    for node in nodes:
                        if core.shutdown_gracefully():
                            return
                        self._sitemap_indexes.append(node.firstChild.nodeValue)
                        downloader = Download(node.firstChild.nodeValue,
                                              self._user_agent)
                        contents = downloader.contents()
                        if contents is not None:
                            self.read_sitemap(contents)

    def read_sitemap(self, contents):
        if contents is None:
            return

        dom = xml.dom.minidom.parseString(contents)
        urls = dom.getElementsByTagName('url')
        for url in urls:
            nodes = url.getElementsByTagName('loc')
            if core.shutdown_gracefully():
                return
            for node in nodes:
                self._sitemap_urls.append(node.firstChild.nodeValue)

    def urls(self):
        return self._sitemap_urls

    def sitemap_indexes(self):
        return self._sitemap_indexes
