#!/bin/bash

set -e

# -----------------------------------------------------------------------------
if [[ "$EUID" -ne 0 ]]; then
    echo "please run this script as root."
    exit 1
fi

. /etc/os-release
if [[ "$ID" != "ubuntu" ]]; then
    echo "unsupported distribution $ID"
    exit 1
fi
# -----------------------------------------------------------------------------

# TODO 

# php artisan mq:wbw.analyses

if [[ "$#" -eq 1 && "$1" == "diagnose" ]]; then
    php --version
    echo "NodeJs: $(node -v)"
    echo "npm: $(npm -v)"
    composer diagnose
elif [[ "$#" -eq 2 && "$1" == "fcgi" ]]; then
    echo "start FastCGI server listening on 0.0.0.0:$2"
    php-fpm -F -R
else
    echo "unsupported args, USAGE: $0 diagnose|fcgi PORT"
    exit 1
fi

exit 0
