#!/bin/bash

set -e

export VERSION=$(date "+%4Y%m%d%H%M%S")
export CODE="magnolia"
export TAR="$CODE-$(uname -m)-$(date +"%Y%m%d%H%M%S")"

docker pull ubuntu:latest
DOCKER_BUILDKIT=1 docker build -t $CODE .
docker save -o $TAR.tar $CODE
md5sum $TAR.tar >$TAR.md5

echo "done($TAR.tar)."

exit 0
