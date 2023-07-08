#!/bin/bash

set -e

# rclone copy --drive-shared-with-me $1:assets assets

export WORKSPACE=$PWD

function build_book(){
    local target=$WORKSPACE/tmp/$1/$2
    local dist=$WORKSPACE/roles/mint-assets/files/public/$1/$2
    if [ ! -d $target ]
    then
        git clone -b $2 "https://github.com/iapt-platform/$1.git" $target
    fi
    cd $target
    git pull
    if [ -d $dist ]
    then
        rm -r $dist
    fi
    mkdir -p $dist
    $HOME/.local/bin/mdbook build --dest-dir $dist
}

declare -a languages=(
    "zh-Hans"
)

declare -a books=(
    "pali-handbook"
    "help"
)

for b in "${books[@]}"
do
    for l in "${languages[@]}"
    do
        build_book $b $l
    done
done

echo 'done.'
exit 0
