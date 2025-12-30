#!/bin/bash

set -e

export VERSION=$(date "+%4Y%m%d%H%M%S")

if [[ "$#" -eq 1 && "$1" == "dashboard" ]]; then
    XZ_OPT=-9 tar -cJf dashboard-v6-$VERSION.tar.xz -C dashboard-v6/dashboard node_modules package-lock.json
    XZ_OPT=-9 tar -cJf dashboard-v4-$VERSION.tar.xz -C dashboard-v4/dashboard node_modules yarn.lock
elif [[ "$#" -eq 1 && "$1" == "laravel" ]]; then
    XZ_OPT=-9 tar -cJf dashboard-$VERSION.tar.xz -C xxx node_modules package-lock.json
else
    echo "USAGE: $0 dashboard|laravel"
    exit 1
fi

exit 0

