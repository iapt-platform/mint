#!/bin/bash

set -e

export VERSION=$(date "+%4Y%m%d%H%M%S")
export CODE="palm-spring"

buildah pull ubuntu:latest
buildah bud --layers -t $CODE .
podman save -o $CODE-$VERSION.tar $CODE
XZ_OPT=-9 tar -cJf - $CODE-$VERSION.tar | split -d -b 3G - $CODE-$VERSION.tar.xz.
md5sum $CODE-$VERSION.tar* > md5.txt

echo "done($CODE-$VERSION.tar.xz)."

exit 0
