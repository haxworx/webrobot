#!/usr/bin/env python3
import urllib
from urllib.parse import urlparse
import re
import sys
import xml.dom.minidom

from download import Download

class RobotsText:
    """
    Handle robots.txt.
    """
    def __init__(self, crawler):
        self.crawler = crawler
        self.user_agent = crawler.config.user_agent
        self.agents = dict()
        self.allowed = []
        self.disallowed = []
        self.sitemaps = []
        self.urls = []
        self.url = None;

    def regexify(self, string):
        string = string.replace('*', '.*')
        string = string.replace('?', '\?')
        string = string.replace('.', '\.')
        return string

    def parse(self, url):
        parsed_url = urlparse(url)
        self.url = url = parsed_url.scheme + '://' + parsed_url.netloc + '/robots.txt'
        try:
            downloader = Download(url, self.user_agent)
            (response, code) = downloader.get()
        except urllib.error.HTTPError as e:
            self.crawler.log.warning("RobotsText: Ignoring %s -> %i", url, e.code)
        except urllib.error.URLError as e:
            self.crawler.log.warning("RobotsText: Unable to connect -> %s", e.reason)
        else:
            data = response.read()
            response.close()
            text = data.decode('utf-8')
            lines = text.split('\n')
            agent = None
            for line in lines:
                unwanted = '\r\n'
                line = line.translate({ord(i): None for i in unwanted})
                matches = re.search('^User-Agent:\s*(.*?)$', line, re.IGNORECASE)
                if matches:
                    agent = matches.group(1)
                    if agent not in self.agents:
                        self.agents[agent] = { 'allowed': [], 'disallowed': [] }
                if agent is None:
                    continue
                matches = re.search('^Allow:\s+(.*?)$', line, re.IGNORECASE)
                if matches:
                    self.agents[agent]['allowed'].append(matches.group(1))
                matches = re.search('^Disallow:\s+(.*?)$', line, re.IGNORECASE)
                if matches:
                    self.agents[agent]['disallowed'].append(matches.group(1))
                matches = re.search('^Sitemap:\s+(.*?)$', line, re.IGNORECASE)
                if matches:
                    self.sitemaps.append(matches.group(1))

            for agent, values in self.agents.items():
                if agent == '*' or agent == self.user_agent:
                    for path in values['allowed']:
                        path = self.regexify(path)
                        self.allowed.append(path)
                    for path in values['disallowed']:
                        path = self.regexify(path)
                        self.disallowed.append(path)

            sitemap = SiteMap(self.sitemaps, self.user_agent)
            self.urls = sitemap.parse()

    def get_url(self):
        return self.url

    def get_sitemap(self):
        return self.urls

class SiteMap:
    def __init__(self, sitemaps, user_agent):
        self.user_agent = user_agent
        self.sitemaps = sitemaps
        self.urls = []

    def parse(self):
        for sitemap in self.sitemaps:
            downloader = Download(sitemap, self.user_agent)
            contents = downloader.get_contents()
            if contents is None:
                continue
            dom = xml.dom.minidom.parseString(contents)
            urls = dom.getElementsByTagName('url')
            for url in urls:
                nodes = url.getElementsByTagName('loc')
                for node in nodes:
                    self.urls.append(node.firstChild.nodeValue)

        return self.urls
