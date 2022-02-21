#!/usr/bin/env python3
import urllib
from urllib.parse import urlparse
import re

from download import Download

class RobotsText:
    """
    Handle robots.txt.
    """
    def __init__(self):
        self.version = "pythonbond/1.0"
        self.agents = dict()

    def parse(self, url):
        parsed_url = urlparse(url)
        url = parsed_url.scheme + '://' + parsed_url.netloc + '/robots.txt'
        try:
            downloader = Download(url, self.version)
            (response, code) = downloader.get()
        except urllib.error.HTTPError as e:
            logging.warning("Ignoring %s -> %i", url, e.code)
        except urllib.error.URLError as e:
            print("Unable to connect: {}" . format(e.reason))
        else:
            data = response.read()
            text = data.decode('utf-8')
            lines = text.split('\n')
            for line in lines:
                unwanted = '\r\n'
                line = line.translate({ord(i): None for i in unwanted})
                matches = re.search('^User-Agent:\s*(.*?)$', line, re.IGNORECASE)
                if matches:
                    agent = matches.group(1)
                    if agent not in self.agents:
                        self.agents[agent] = { 'allowed': [], 'disallowed': [] }
                matches = re.search('Allow:\s+(.*?)$', line, re.IGNORECASE)
                if matches:
                    self.agents[agent]['allowed'].append(matches.group(1))
                matches = re.search('Disallow:\s+(.*?)$', line, re.IGNORECASE)
                if matches:
                    self.agents[agent]['disallowed'].append(matches.group(1))

