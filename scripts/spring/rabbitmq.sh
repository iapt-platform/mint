#!/bin/bash

set -e

if [ "$#" -ne 2 ]
then
    echo "Usage: $0 USER PASSWORD"
    exit 1
fi

rabbitmqctl add_user $1 $2
rabbitmqctl add_vhost $1-mint
rabbitmqctl set_permissions -p $1-mint $1 ".*" ".*" ".*"

echo "done($1)."

exit 0
