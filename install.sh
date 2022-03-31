#!/bin/bash

# Ensure config.ini is populated and correct before running.

groupadd -g 2222 spider
if [ $? -ne 0 ]; then
	echo "$0: Failed to create 'spider' group";
	exit 1
fi

useradd -g 2222 -m -d /home/spider spider
if [ $? -ne 0 ]; then
	echo "$0: Failed to create 'spider' user";
	exit 1
fi

groupmod -a -U www-data spider
if [ $? -ne 0 ]; then
	echo "$0: Failed to add 'www-data' to user 'spider' group.";
	exit 1
fi

groupmod -a -U spider docker
if [ $? -ne 0 ]; then
	echo "$0: Failed to add spider to docker group.";
	exit 1
fi

echo 'www-data ALL=(spider:ALL) NOPASSWD:ALL' | sudo EDITOR='tee -a' visudo
if [ $? -ne 0 ]; then
	echo "$0: Failed to add sudoers rule for user www-data as spider.";
	exit 1
fi

sudo -u spider mkdir -p /home/spider/.config/systemd/user
if [ $? -ne 0 ]; then
	echo "$0: Failed to create systemd directory for spider user.";
	exit 1
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
if [ $? -ne 0 ]; then
	echo "$0:Failed to remove docker/config.ini";
	exit 1;
fi
