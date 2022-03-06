#!/bin/bash

IMG=$@

docker inspect --type=image $IMG 2>&1 > /dev/null
if [ "$?" -ne "0" ]; then
	docker build docker/ -t $IMG
else
	echo "Image $IMG already built."
fi

