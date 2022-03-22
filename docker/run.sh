#!/bin/bash

if [ $# != 1 ]; then
	echo "Usage: $0 <botid>"
	exit 1
fi

BOTID=$1
cd /opt/crawler/webrobot
python3 robot_start.py $BOTID
