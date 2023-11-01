#!/bin/bash

set -e

export WORKSPACE=$PWD

build_backend() {
    echo "build $1@$2..."
    mkdir -pv $WORKSPACE/build/$1-$2
    cd $WORKSPACE/build/$1-$2
    conan install --build=missing --profile:build=default \
        --profile:host=/opt/conan/profiles/$1 /opt/conan
    cmake $WORKSPACE -DCMAKE_BUILD_TYPE=$2 \
        -DCMAKE_TOOLCHAIN_FILE=/opt/conan/toolchains/$1.cmake
    make -j
}

build_backend amd64 Debug
build_backend amd64 Release
build_backend arm64 Release
build_backend armhf Release

echo 'done.'

exit 0
