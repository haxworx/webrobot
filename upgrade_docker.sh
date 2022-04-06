#!/bin/bash

# Upgrade our docker file image for user spider.
# Ensure config.ini is populated and correct before running.

sudo -u spider docker rmi -f spiderz
if [ $? -ne 0 ]; then
	echo "$0:Failed to remove docker image.";
	exit 1;
fi

# Generate our docker/config.ini
python3 make_config.py
if [ $? -ne 0 ]; then
	echo "$0:Failed running make_config.py";
	exit 1
fi

# Build our docker image.
sudo -u spider docker build --no-cache docker/ -t spiderz
if [ $? -ne 0 ]; then
	echo "$0:Failed building our docker image.";
	exit 1;
fi

# Remove our docker/config.ini
rm docker/config.ini

