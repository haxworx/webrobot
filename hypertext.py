#!/usr/bin/env python

import email.utils as eut
from datetime import datetime

class Http:
    def date(date_string):
        if date_string is None:
            return None
        dt = None
        try:
            dt = datetime(*eut.parsedate(date_string)[:6])
#            dt = datetime.strptime(modified, "%a, %d %b %Y %H:%M:%S %Z")
        except Exception as e:
            pass
        return dt

