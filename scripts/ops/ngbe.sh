#!/bin/bash

set -e

. /etc/os-release

export WORKSPACE=$PWD/ngbe-1.2.3

if [ ! -d $WORKSPACE ]
then
    echo "coun't find the source folder $WORKSPACE"
    exit 1
fi

apt install -y build-essential
cd $WORKSPACE/src
make
make install

echo 'ngbe' > /etc/modules-load.d/ngbe.conf

echo 'done.'
exit 0