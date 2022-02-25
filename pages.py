import sys
import urllib

class PageList:
    """
    Simple class to keep track of pages.
    Provides an iterator and append method.
    """
    def __init__(self):
        self._page_list = []
        self._page_index = 0

    def __len__(self):
        return len(self._page_list)

    def __iter__(self):
        return self

    def __next__(self):
        page = None
        if len(self._page_list) == 0:
            raise StopIteration
        if self._page_index == 0 or self._page_index < len(self._page_list):
            page = self._page_list[self._page_index]
            self._page_index += 1
            return page
        self._page_index = 0
        raise StopIteration

    def again(self):
        if self._page_index > 0:
            self._page_index -=1

    def append(self, url, link_source=None, sitemap_url=False):
        """
        Append a URL to the page list.
        Only appends when url is unseen/new.
        """
        page_new = Page(url, link_source=link_source, sitemap_url=sitemap_url)
        exists = False
        for page in self._page_list:
            if page_new.url() == page.url():
                exists = True
                break
        if exists:
             return False
        else:
            self._page_list.append(page_new)
            return True

class Page:
    def __init__(self, url, visited=False, link_source=None, sitemap_url=False):
        self._url = self.asciify_url(url)
        self._link_source = link_source
        self._sitemap_url = sitemap_url
        self._visited = visited

    def asciify_url(self, url):
        if not url.isascii():
            (scheme, netloc, path, query, fragment) = urllib.parse.urlsplit(url)
            if not scheme.isascii():
                scheme = urllib.parse.quote(scheme)
            if not netloc.isascii():
                netloc = netloc.encode('idna').decode('utf-8')
            if not path.isascii():
                path = urllib.parse.quote(path)
            if not query.isascii():
                query = urllib.parse.quote(query)
            if not fragment.isascii():
                fragment = urllib.parse.quote(fragment)
            url = urllib.parse.urlunsplit((scheme, netloc, path, query, fragment))
        return url

    @property
    def visited(self):
        return self._visited

    @visited.setter
    def visited(self, visited):
        self._visited = visited

    def url(self):
        return self._url

    def link_source(self):
        return self._link_source

    def is_sitemap_source(self):
        return self._sitemap_url
