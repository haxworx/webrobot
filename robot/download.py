#!/usr/bin/env python3

import urllib.request


class Download:

    """
    A simple HTTP download class.
    """

    def __init__(self, url, user_agent):
        self.url = url
        self.user_agent = user_agent

    @property
    def url(self):
        return self._url

    @url.setter
    def url(self, url):
        self._url = url

    @property
    def user_agent(self):
        return self._user_agent

    @user_agent.setter
    def user_agent(self, user_agent):
        self._user_agent = user_agent


    def get(self):
        """
        Make a HTTP request.

        A wrapper to avoid code duplication. Exceptions must be caught.
        """

        request = urllib.request.Request(self.url)
        request.add_header('User-Agent', self.user_agent)
        response = urllib.request.urlopen(request)
        code = response.getcode()
        return (response, code)

    def contents(self):
        """
        Download content without a load of exception handling. Akin to
        file_get_contents in PHP.
        """

        contents = None
        try:
            request = urllib.request.Request(self.url)
            request.add_header('User-Agent', self.user_agent)
            response = urllib.request.urlopen(request)
        except urllib.error.HTTPError as e:
            pass
        except urllib.error.URLError as e:
            pass
        except Exception as e:
            pass
        else:
            data = response.read()
            contents = data.decode('utf-8')
            response.close()
        return contents
