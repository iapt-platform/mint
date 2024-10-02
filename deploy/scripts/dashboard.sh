#!/bin/bash

set -e

export WORKSPACE=$PWD

cd $WORKSPACE/dashboard
if [ ! -d node_modules ]
then
    yarn install
fi

# GENERATE_SOURCEMAP=false 
BUILD_PATH=$WORKSPACE/deploy/roles/mint/files/dashboard PUBLIC_URL=/pcd yarn build

echo 'done.'

exit 0
