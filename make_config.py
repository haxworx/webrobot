#!/usr/bin/env python3

import sys

if len(sys.argv) != 5:
    print("{} <db_host> <db_name> <db_user> <db_pass>" . format(sys.argv[0]))
    sys.exit(1)

db_host = sys.argv[1]
db_name = sys.argv[2]
db_user = sys.argv[3]
db_pass = sys.argv[4]

with open('config.template', 'r') as f:
    text = f.read()
    text = text . format(db_host, db_name, db_user, db_pass)
    with open('config.ini', 'w') as of:
        of.write(text)
