#!/bin/bash

cd /opt/crawler/webrobot

python3 make_config.py datacentre crawl test password
python3 robot_start.py $@
