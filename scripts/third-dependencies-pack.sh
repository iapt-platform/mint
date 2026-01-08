#!/bin/bash

set -e

export VERSION=$(date "+%4Y%m%d%H%M%S")

XZ_OPT=-9 tar -cJf mint-$VERSION

export TAR="XZ_OPT=-9 tar -cJf"

$TAR dashboard-v6-$VERSION.tar.xz -C dashboard-v6 node_modules package-lock.json
$TAR dashboard-v4-$VERSION.tar.xz -C dashboard-v4/dashboard node_modules yarn.lock
$TAR api-v8-frontend-$VERSION.tar.xz -C api-v8 node_modules package-lock.json
$TAR api-v8-public-$VERSION.tar.xz -C dashboard-v4/dashboard node_modules yarn.lock

exit 0

