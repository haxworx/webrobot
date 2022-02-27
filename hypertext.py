#!/usr/bin/env python

import email.utils as eut
from datetime import datetime

class Http:
    def date(date_string):
        dt = None
        if date_string is None:
            return dt
        try:
            dt = datetime(*eut.parsedate(date_string)[:6])
#            dt = datetime.strptime(modified, "%a, %d %b %Y %H:%M:%S %Z")
        except Exception as e:
            pass
        return dt

    def int(int_string):
        val = None

        if int_string is None:
            return val
        try:
            val = int(int_string)
        except:
            pass

        return val

    def string(string):
        return string

