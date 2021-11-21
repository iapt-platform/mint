#!/bin/bash

set -e

export PHP_VERSION="8.0"

declare -a plugins=(
    "cli"
    "fpm"
    "xml"
    # https://php.watch/versions/8.0/ext-json
    # "json"
    "imap"
    "intl"
    "mbstring"
    "bz2"
    "zip"
    "curl"
    "gd"
    "imagick"
    "mysql"
    "pgsql"
    "sqlite3"
    "redis"
    "bcmath"
)

for i in "${plugins[@]}"
do
    sudo apt install -y php${PHP_VERSION}-$i
done

echo 'done.'
