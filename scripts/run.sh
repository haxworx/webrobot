#!/bin/bash

IMG="test"
HOST=$@

docker run -it $IMG /run.sh $HOST

