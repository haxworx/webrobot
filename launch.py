#!/usr/bin/env python3

import database
from config import Config

def main():
    config = Config()

    dbh = database.Connect(config.db_user, config.db_pass,
                           config.db_host, config.db_name)

    dbh.close()

if __name__ == '__main__':
    main()
