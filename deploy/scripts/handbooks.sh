#!/bin/bash

set -e


if [ $# -ne 1 ]
then
	echo "Usage: $0 FOLDER"
	exit 1
fi


function build_book(){
    local target=$HOME/tmp/$2/$3
    local dist=$1/public/$2/$3
    if [ ! -d $target ]
    then
        git clone -b $3 "https://github.com/iapt-platform/$2.git" $target
    fi
    cd $target
    git pull
    if [ -d $dist ]
    then
        rm -r $dist
    fi
    mkdir -p $dist
    $HOME/.local/bin/mdbook build --dest-dir $dist
    echo "done($dist)."
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
        build_book $1 $b $l
    done
done

exit 0
