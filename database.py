#!/usr/bin/env python

import sys
import mysql.connector
from mysql.connector import errorcode

class Connect:
    cnx = None

    def __init__(self, db_user="", db_pass="", db_host="", db_name=""):
        try:    
            self.cnx = mysql.connector.connect(user=db_user,
                                               password=db_pass,
                                               host=db_host,
                                               database=db_name)
        except mysql.connector.Error as e:
            print("Unable to connect ({}): {}" . format(e.errno, e.msg), file=sys.stderr)
            sys.exit(1)
    
    def close(self):
        if self.cnx is not None:
            self.cnx.close()

