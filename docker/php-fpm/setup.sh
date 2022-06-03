#!/bin/bash

# Ensure config.ini is populated and correct before running.

groupadd -g 2222 spider
if [ $? -ne 0 ]; then
	echo "$0: Failed to create 'spider' group";
	exit 1
fi

useradd -u 2222 -g 2222 -m -d /home/spider spider
if [ $? -ne 0 ]; then
	echo "$0: Failed to create 'spider' user";
	exit 1
fi

usermod -a -G www-data spider
if [ $? -ne 0 ]; then
	echo "$0: Failed to add 'www-data' to user 'spider' group.";
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

