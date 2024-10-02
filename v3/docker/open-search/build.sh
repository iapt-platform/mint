#!/bin/bash

set -e

export VERSION=$(date "+%4Y%m%d%H%M%S")
export CODE="mint-open-search"

if [ "$#" -ne 1 ]; then
    echo "USAGE: $0 OPENSEARCH_ARCH"
    exit 1
fi

podman pull ubuntu:latest
podman build --build-arg OPENSEARCH_ARCH=$1 -t $CODE .
podman save --format=oci-archive -o $CODE-$VERSION.tar $CODE
md5sum $CODE-$VERSION.tar >$CODE-$VERSION.md5

echo "done($CODE-$VERSION.tar)."

exit 0
