#!/bin/bash

set -e

export PROTOBUF_ROOT=$HOME/.local
export WORKSPACE=$PWD

# -----------------------------------------------------------------------------

function generate_grpc_by_lang() {
    local target=$WORKSPACE/$1
    echo "generate code for $1"
    if [ -d $target ]
    then
        rm -r $target
    fi
    mkdir -p $target
    $PROTOBUF_ROOT/bin/protoc -I $WORKSPACE/../protocols \
        -I $PROTOBUF_ROOT/include/google/protobuf \
        --${1}_out=$target --grpc_out=$target \
        --plugin=protoc-gen-grpc=$PROTOBUF_ROOT/bin/grpc_${1}_plugin \
        $WORKSPACE/../protocols/*.proto
}

function generate_grpc_for_php() {
    local target=$WORKSPACE/php
    echo "generate code for php"

    local -a folders=(
        "GPBMetadata"
        "Mint"
    )

    for f in "${folders[@]}"
    do
        if [ -d $target/$f ]
        then
            rm -r $target/$f
        fi
    done
    mkdir -p $target
    $PROTOBUF_ROOT/bin/protoc -I $WORKSPACE/../protocols \
        -I $PROTOBUF_ROOT/include/google/protobuf \
        --php_out=$target --grpc_out=generate_server:$target \
        --plugin=protoc-gen-grpc=$PROTOBUF_ROOT/bin/grpc_php_plugin \
        $WORKSPACE/../protocols/*.proto
}

# -----------------------------------------------------------------------------

declare -a languages=(
    "cpp"
    "python"
    "ruby"
    "csharp"
    "java"
)

for l in "${languages[@]}"
do
    generate_grpc_by_lang $l
done

generate_grpc_for_php
# -----------------------------------------------------------------------------

echo 'done.'
exit 0
