#!/bin/bash

SCRIPT=$(basename $0)

if [ $# != 2 ]; then
	echo "Usage: $SCRIPT <image> <host>"
	exit 1
fi

IMG=$1
HOST=$2

docker run -it $IMG /run.sh $HOST

