#!/bin/bash

set -e

if [ "$#" -ne 1 ]
then
    echo "USAGE: $0 DOMAIN"
    exit 1
fi

export GIT_VERSION=$(git describe --tags --always --dirty --first-parent)


if [ ! -d node_modules ]
then
    yarn install
fi

PUBLIC_URL=/pcd yarn build


echo "$GIT_VERSION" > build/VERSION
echo "$(date -R)" >> build/VERSION


rsync -rzv build/ deploy@$1:/var/www/$1/dashboard

echo "done($GIT_VERSION)."
