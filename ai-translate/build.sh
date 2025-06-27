#!/bin/bash

set -e

if [ "$#" -ne 1 ]; then
    echo "USAGE: $0 PYTHON_VERSION"
    exit 1
fi

export VERSION=$(date "+%4Y%m%d%H%M%S")
export CODE="mint-python$1"
export TAR="$CODE-$VERSION"

podman pull ubuntu:latest
podman build --build-arg PYTHON_VERSION=$1 -t $CODE .
# podman save --format=oci-archive -o $TAR-$(uname -m).tar $CODE
# md5sum $TAR.tar >$TAR.md5

echo "done($TAR.tar)."

exit 0
