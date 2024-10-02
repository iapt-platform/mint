#!/bin/bash

set -e

export PROTOBUF_ROOT=$HOME/.local
export WORKSPACE=$PWD

# -----------------------------------------------------------------------------

function generate_grpc_by_lang() {
    local target=$WORKSPACE/sdk/$1
    echo "generate code for grpc-$1"
    
    if [ -d $target ]
    then
        rm -r $target        
    fi
    mkdir -p $target
    $PROTOBUF_ROOT/bin/protoc -I $WORKSPACE/protocols \
        -I $PROTOBUF_ROOT/include/google/protobuf \
        --${1}_out=$target --grpc_out=$target \
        --plugin=protoc-gen-grpc=$PROTOBUF_ROOT/bin/grpc_${1}_plugin \
        $WORKSPACE/protocols/*.proto
}

function generate_flatbuffers(){
    echo "generate flatbuffers"
    flatc --rust -o $WORKSPACE/src/$2.rs $WORKSPACE/protocols/$1.fbs
}

# https://github.com/grpc/grpc-web#code-generator-plugin
function generate_grpc_web() {
    echo "generate code for grpc-web"
    local target=$1/src/protocols
    if [ -d $target ]
    then
        rm -r $target
    fi
    mkdir -p $target
    $PROTOBUF_ROOT/bin/protoc -I $WORKSPACE/protocols \
        -I $PROTOBUF_ROOT/include/google/protobuf \
        --js_out=import_style=commonjs,binary:$target \
        --grpc-web_out=import_style=typescript,mode=grpcweb:$target \
        $WORKSPACE/protocols/*.proto
}

function generate_for_morus() {
    echo "generate code for morus project"
    local target=$WORKSPACE/morus/morus
    local -a folders=(
        "GPBMetadata"
        "Mint"
    )
    for f in "${folders[@]}"
    do
        local t=$target/$f
        if [ -d $t ]
        then
            rm -r $t
        fi
    done
    $PROTOBUF_ROOT/bin/protoc -I $WORKSPACE/protocols \
        -I $PROTOBUF_ROOT/include/google/protobuf \
        --php_out=$target --grpc_out=generate_server:$target \
        --plugin=protoc-gen-grpc=$PROTOBUF_ROOT/bin/grpc_php_plugin \
        $WORKSPACE/protocols/morus.proto    
}

function generate_for_lily() {
    echo "generate code for lily project"
    local target=$WORKSPACE/lily/lily/palm
    local -a files=(        
        "lily_pb2.py"
        "lily_pb2_grpc.py"
    )
    for f in "${files[@]}"
    do
        local t=$target/$f
        if [ -f $t ]
        then
            rm $t
        fi
    done
    $PROTOBUF_ROOT/bin/protoc -I $WORKSPACE/protocols \
        -I $PROTOBUF_ROOT/include/google/protobuf \
        --python_out=$target --grpc_out=$target \
        --plugin=protoc-gen-grpc=$PROTOBUF_ROOT/bin/grpc_python_plugin \
        $WORKSPACE/protocols/lily.proto
    sed -i 's/import lily_/from . import lily_/g' $target/lily_pb2_grpc.py
}


function generate_for_tulip() {
    echo "generate code for tulip project"
    local target=$WORKSPACE/tulip/tulip
    local -a folders=(
        "GPBMetadata"
        "Mint"        
    )
    for f in "${folders[@]}"
    do
        local t=$target/$f
        if [ -d $t ]
        then
            rm -r $t
        fi
    done
    $PROTOBUF_ROOT/bin/protoc -I $WORKSPACE/protocols \
        -I $PROTOBUF_ROOT/include/google/protobuf \
        --php_out=$target --grpc_out=generate_server:$target \
        --plugin=protoc-gen-grpc=$PROTOBUF_ROOT/bin/grpc_php_plugin \
        $WORKSPACE/protocols/tulip.proto    
}

function generate_grpc_for_php() {
    if [ -d $1 ]
    then
        rm -r $1
    fi
    mkdir -p $1
    $PROTOBUF_ROOT/bin/protoc -I $WORKSPACE/protocols \
        -I $PROTOBUF_ROOT/include/google/protobuf \
        --php_out=$1 --grpc_out=generate_server:$1 \
        --plugin=protoc-gen-grpc=$PROTOBUF_ROOT/bin/grpc_php_plugin \
        $WORKSPACE/protocols/*.proto
}

# -----------------------------------------------------------------------------

declare -a languages=(    
    "python"
    "ruby"
    "cpp"
    "csharp"    
    "java"
)

for l in "${languages[@]}"
do
    generate_grpc_by_lang $l
done
generate_grpc_for_php $WORKSPACE/sdk/php

generate_for_morus
generate_for_lily
generate_for_tulip

generate_grpc_web $WORKSPACE/../dashboard

# ----------------------------------------------------------

echo 'done.'
exit 0
