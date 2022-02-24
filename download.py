#!/usr/bin/env python3

import urllib

class Download:
    def __init__(self, url, user_agent):
        self.url = url
        self.user_agent = user_agent

    def get(self):
        request = urllib.request.Request(self.url)
        request.add_header('User-Agent', self.user_agent)
        response = urllib.request.urlopen(request)
        code = response.getcode()
        return (response, code)

    def get_contents(self):
        contents = None
        try:
            request = urllib.request.Request(self.url)
            request.add_header('User-Agent', self.user_agent)
            response = urllib.request.urlopen(request)
            code = response.getcode()
        except urllib.error.HTTPError as e:
            pass
        except urllib.error.URLError as e:
            pass
        else:
            data = response.read()
            contents = data.decode('utf-8')
            response.close()
        return contents

