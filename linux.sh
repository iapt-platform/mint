#!/bin/sh

set -e

declare -a folders=(
    "appdata"
    "user"
    "palihtml"
    "font"
    "dicttext"
    "palicsv"
    "temp"
    "log"
)

for i in "${folders[@]}"
do
    mkdir -pv $i
    sudo chown -R www-data:www-data $i    
done

exit 0