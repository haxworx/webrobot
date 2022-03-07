#!/bin/bash

if [[ "$@" == "" ]]; then
	echo "Missing parameter. Aborting."
	exit 1
fi

cd /opt/crawler/webrobot

python3 make_config.py datacentre crawl test password
python3 robot_start.py $1
