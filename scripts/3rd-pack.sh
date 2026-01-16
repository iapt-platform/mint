#!/bin/bash

set -e

export WORKSPACE=$PWD
export PHP_VERSION="$(php -r 'echo PHP_VERSION;')"
export VERSION="$(uname -m)-$(date "+%4Y%m%d%H%M%S")"
export XZ_OPT=-9
export TAR="tar -cJf"

git config --global --add safe.directory $PWD

if [[ "$PHP_VERSION" == "8.1.34" ]]; then    
    cd $WORKSPACE/
    $TAR api-v8-$VERSION.tar.xz -C api-v8 node_modules package-lock.json vendor composer.lock public/node_modules public/package-lock.json public/vendor public/composer.lock
    $TAR dashboard-v4-$VERSION.tar.xz -C dashboard-v4/dashboard node_modules yarn.lock
elif [[ "$PHP_VERSION" == "8.4.16" ]]; then
    cd $WORKSPACE/api-v12/
    composer install --optimize-autoloader --no-dev
    npm install
    cd $WORKSPACE/dashboard-v6/
    npm install
    cd $WORKSPACE/open-ai-server/
    npm install

    cd $WORKSPACE/
    $TAR api-v12-$VERSION.tar.xz -C api-v12 node_modules package-lock.json vendor composer.lock    
    $TAR dashboard-v6-$VERSION.tar.xz -C dashboard-v6 node_modules package-lock.json
    $TAR open-ai-server-$VERSION.tar.xz -C open-ai-server node_modules package-lock.json
else
    echo "unsupported php version $PHP_VERSION"
    exit 1
fi

md5sum *-$VERSION.tar.xz > $VERSION.md5
echo "done($VERSION)."
exit 0

