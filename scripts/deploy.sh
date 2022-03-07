#!/bin/bash

SCRIPT=$(basename $0)

if [ $# != 1 ]; then
	echo "Usage: $SCRIPT <image>"
	exit 1
fi

IMG=$1

docker inspect --type=image $IMG 2>&1 > /dev/null
if [ "$?" -ne "0" ]; then
	docker build docker/ -t $IMG
else
	echo "Image $IMG already built."
fi

