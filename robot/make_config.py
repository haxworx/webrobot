#!/usr/bin/env python3

# Config is parsed and the plaintext database credentials copied
# to docker/ to be included in our docker images.
# This will read both plaintext (INI) and from our AWS password
# secrets storage (if configured).
#
# The docker image always uses INI values to initialise a
# database connection.

import sys

from config import Config

try:
    config = Config(0)
    config.read_ini()
except Exception as e:
    print("Caught exception: {} " . format(e), file=sys.stderr)
    sys.exit(1)

with open('config.template', 'r') as f:
    text = f.read()
    text = text . format(config.db_host, config.db_name, config.db_user, config.db_pass)
    with open('docker/config.ini', 'w') as of:
        of.write(text)
sys.exit(0)
