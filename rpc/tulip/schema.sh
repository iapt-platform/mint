#!/bin/bash

set -e

export PROTOBUF_ROOT=$HOME/.local
export WORKSPACE=$PWD
export TARGET_DIR=$WORKSPACE/tulip

# -----------------------------------------------------------------------------

echo "generate code for tulip project"

declare -a folders=(
    "GPBMetadata"
    "Mint"        
)
for f in "${folders[@]}"
do
    t=$TARGET_DIR/$f
    if [ -d $t ]
    then
        rm -r $t
    fi
done

$PROTOBUF_ROOT/bin/protoc -I $WORKSPACE/../protocols \
    -I $PROTOBUF_ROOT/include/google/protobuf \
    --php_out=$TARGET_DIR --grpc_out=generate_server:$TARGET_DIR \
    --plugin=protoc-gen-grpc=$PROTOBUF_ROOT/bin/grpc_php_plugin \
    $WORKSPACE/../protocols/tulip.proto   

echo 'done.'

exit -0
