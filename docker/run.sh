#!/bin/bash

if [ $# != 2 ]; then
	echo "Usage: $0 <host> <user-agent>"
	exit 1
fi

HOST=$1
AGENT=$2

cd /opt/crawler/webrobot
python3 make_config.py datacentre crawl test password
python3 robot_start.py $HOST $AGENT
