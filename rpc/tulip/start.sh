#!/bin/bash

set -e

export CODE="mint-tulip"

if [ "$#" -ne 1 ]
then
    echo "USAGE: $0 PORT"
    exit 1
fi

podman run -d --rm --events-backend=file --hostname=palm --network host $CODE /usr/bin/php server.php --port $1

exit 0
