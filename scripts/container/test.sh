#!/bin/bash

set -e

if [ "$#" -ne 1 ]; then
    echo "USAGE: $0 PHP_VERSION"
    exit 1
fi

docker run --rm -it --hostname=mint --network host -v $(dirname $PWD):/srv:z mint-php${1}

exit 0
