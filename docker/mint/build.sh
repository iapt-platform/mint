#!/bin/bash

set -e

# https://laravel.com/docs/master/releases
if [ "$#" -ne 2 ]; then
    echo "USAGE: $0 PHP_VERSION ARCH"
    exit 1
fi

export VERSION=$(date "+%4Y%m%d%H%M%S")
export CODE="mint-php$1"
export TAR="$CODE-$(uname -m)-$VERSION"

docker pull ubuntu:latest
docker build --platform=linux/$2 --build-arg PHP_VERSION=$1 -t $CODE .
docker save -o $TAR.tar $CODE:latest
md5sum $TAR.tar >$TAR.md5

echo "done($TAR.tar)."

exit 0
