#!/bin/bash

SCRIPT=$(basename $0)

if [ $# != 3 ]; then
	echo "Usage: $SCRIPT <image> <host> <user-agent>"
	exit 1
fi

IMG=$1
HOST=$2
AGENT=$3

docker run -it $IMG /run.sh $HOST $AGENT

