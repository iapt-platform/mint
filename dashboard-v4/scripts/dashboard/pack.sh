#!/bin/sh

set -e

export VERSION=$(date "+%4Y%m%d%H%M%S")

XZ_OPT=-9 tar -cJf dashboard-$VERSION.tar.xz node_modules yarn.lock

echo "Done($VERSION)."

exit 0
