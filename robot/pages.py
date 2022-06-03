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
        # XXX This uses more memory but we cannot keep order with a dict
        # when iterating. It's fine.
        self._seen = dict()

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
        self._seen = dict()
        raise StopIteration

    def __str__(self):
        text = ''
        for page in self._page_list:
            text += page.url + "\n"
        return text

    def again(self):
        if self._page_index > 0:
            self._page_index -= 1

    def append(self, url, link_source=None, sitemap_url=False):
        """
        Append a URL to the page list.
        Only appends when url is unseen/new.
        """
        if url in self._seen:
            return False
        else:
            self._seen[url] = 1
            page_new = Page(url, link_source=link_source, sitemap_url=sitemap_url)
            self._page_list.append(page_new)
            return True


class Page:
    def __init__(self, url, link_source=None, sitemap_url=False):
        self._url = self.asciify_url(url)
        self._link_source = link_source
        self._sitemap_url = sitemap_url

    def asciify_url(self, url):
        if not url.isascii():
            (scheme, netloc, path, query, fragment) = \
                    urllib.parse.urlsplit(url)
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
    def url(self):
        return self._url

    @url.setter
    def url(self, url):
        self._url = url

    @property
    def link_source(self):
        return self._link_source

    @property
    def is_sitemap_source(self):
        return self._sitemap_url
