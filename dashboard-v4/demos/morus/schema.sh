#!/bin/bash

set -e

export PROTOBUF_ROOT=$HOME/.local
export WORKSPACE=$PWD

# -----------------------------------------------------------------------------

function generate_node() {
    echo "generate code for node"
    local target=$WORKSPACE/src/protocols
    if [ -d $target ]
    then
        rm -r $target
    fi
    mkdir -p $target
    grpc_tools_node_protoc -I $WORKSPACE/../protocols \
        -I $PROTOBUF_ROOT/include/google/protobuf \
        --js_out=import_style=commonjs,binary:$target \
        --grpc_out=grpc_js:$target $WORKSPACE/../protocols/morus.proto
}

# -----------------------------------------------------------------------------
generate_node
# -----------------------------------------------------------------------------

echo 'done.'
exit 0
