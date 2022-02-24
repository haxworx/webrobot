#!/usr/bin/env python3
import urllib
from urllib.parse import urlparse
import re

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
        self.url = None;

    def regexify(self, string):
        string = string.replace('*', '.*')
        string = string.replace('?', '\?')
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

            for agent, values in self.agents.items():
                if agent == '*' or agent == self.user_agent:
                    for path in values['allowed']:
                        path = self.regexify(path)
                        self.allowed.append(path)
                    for path in values['disallowed']:
                        path = self.regexify(path)
                        self.disallowed.append(path)
    def get_url(self):
        return self.url

