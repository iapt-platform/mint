#!/bin/bash

set -e

# https://laravel.com/docs/master/releases
if [ "$#" -ne 1 ]; then
    echo "USAGE: $0 PHP_VERSION"
    exit 1
fi

export VERSION=$(date "+%4Y%m%d%H%M%S")
export CODE="mint-php$1-$(uname -m)"
export TAR="$CODE-$VERSION"

# podman pull ubuntu:latest
# podman build --build-arg PHP_VERSION=$1 -t $CODE .
# podman save --format=oci-archive -o $TAR.tar $CODE

docker pull ubuntu:latest
docker build --build-arg PHP_VERSION=$1 -t $CODE .
docker save -o $TAR.tar $CODE:latest
md5sum $TAR.tar >$TAR.md5

echo "done($TAR.tar)."

exit 0
