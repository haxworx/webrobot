import sys
import urllib

class PageList:
    """
    Simple class to keep track of pages.
    Provides an iterator and append method.
    """
    def __init__(self):
        self.page_list = []
        self.page_index = 0

    def __len__(self):
        return len(self.page_list)

    def __iter__(self):
        return self

    def __next__(self):
        page = None
        if len(self.page_list) == 0:
            raise StopIteration
        if self.page_index == 0 or self.page_index < len(self.page_list):
            page = self.page_list[self.page_index]
            self.page_index += 1
            return page
        raise StopIteration

    def again(self):
        if self.page_index > 0:
            self.page_index -=1

    def append(self, url, link_source=None, sitemap_url=False):
        """
        Append a URL to the page list.
        Only appends when url is unseen/new.
        """
        page_new = Page(url, link_source=link_source, sitemap_url=sitemap_url)
        exists = False
        for page in self.page_list:
            if page_new.get_url() == page.get_url():
                exists = True
                break
        if exists:
             return False
        else:
            self.page_list.append(page_new)
            return True

class Page:
    def __init__(self, url, visited=False, link_source=None, sitemap_url=False):
        self.url = self.asciify_url(url)
        self.link_source = link_source
        self.sitemap_url = sitemap_url
        self.visited = visited

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

    def set_visited(self, visited):
        self.visited = visited

    def get_visited(self):
        return self.visited

    def get_url(self):
        return self.url

    def get_source(self):
        return self.link_source

    def sitemap_source(self):
        return self.sitemap_url
