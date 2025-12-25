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

if [[ "$#" -eq 1 && "$1" == "diagnose" ]]; then
    php --version
    echo "NodeJs: $(node -v)"
    echo "npm: $(npm -v)"
    composer diagnose
else
    echo "unsupported args, USAGE: $0 diagnose|api-v8 PORT|api-v12 PORT"
    exit 1
fi

exit 0
