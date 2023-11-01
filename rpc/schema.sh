#!/bin/bash

set -e

export PROTOBUF_ROOT=$HOME/.local
export WORKSPACE=$PWD

# -----------------------------------------------------------------------------

# if [[ "$1" == "php" ]]
#         then
#             declare -a folders=(
#                 "GPBMetadata"
#                 "Mint"
#                 "Palm"
#             )
#             for f in "${folders[@]}"
#             do
#                 local t=$target/$1/$f
#                 if [ -d $t ]
#                 then
#                     rm -f $t
#                 fi
#             done            
#         else
            
#         fi

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


# -----------------------------------------------------------------------------

declare -a languages=(    
    "python"
    "ruby"
    "cpp"
    "csharp"    
    "java"
    "php"
)

for l in "${languages[@]}"
do
    generate_grpc_by_lang $l
done

generate_grpc_web $WORKSPACE/../dashboard

# ----------------------------------------------------------

echo 'done.'
exit 0
