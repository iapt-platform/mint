#!/bin/bash

set -e

. /etc/os-release
export WORKSPACE=$PWD

if [ "$#" -ne 1 ]
then
    echo "USAGE: $0 PROFILE"
    exit 1
fi

export TARGET=$HOME/build/conan-$1

if [ -d $TARGET ]
then
    rm -r $TARGET
fi
mkdir -p $TARGET
cd $TARGET
conan install --build=missing --profile:build=default --profile:host=$WORKSPACE/profiles/$1 $WORKSPACE

echo "done($1)."

exit 0
