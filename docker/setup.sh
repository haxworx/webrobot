#!/bin/bash

export DEBIAN_FRONTEND=noninteractive

apt-get update
apt-get -y upgrade
apt-get -y install python3 pip git
apt-get clean
rm -rf /var/lib/apt/lists/*
python3 -m pip install mysql-connector-python paho.mqtt boto3
mkdir -p /opt/crawler
cd /opt/crawler
git clone https://github.com/haxworx/webrobot --depth 1
cp /config.ini /opt/crawler/webrobot
