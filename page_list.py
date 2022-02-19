class PageList:
    """
    Simple class to keep track of pages.
    Provides an iterator and append method.
    """
    def __init__(self):
        self.page_list = []
        self.page_index = 0

    def __iter__(self):
        return self

    def __next__(self):
        if len(self.page_list) == 0:
            raise StopIteration

        while True:
            if self.page_index + 1 < len(self.page_list):
                self.page_index += 1
            if self.page_list[self.page_index]['visited'] != True:
                return self.page_list[self.page_index]
            else:
                raise StopIteration

    def append(self, item):
        """
        Append a URL to the page list.
        Only appends when url is unseen/new.
        """
        exists = False
        for page in self.page_list:
            if item['url'] == page['url']:
                exists = True
                break
        if exists:
             return None
        else:
            self.page_list.append(item)
            return item

