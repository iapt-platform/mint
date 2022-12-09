#!/bin/bash

set -e

if [ "$#" -ne 2 ]
then
    echo "Usage: $0 USER PASSWORD"
    exit 1
fi

psql << EOF
CREATE USER $1 WITH PASSWORD '$2';
CREATE DATABASE $1_mint WITH ENCODING = 'UTF8' OWNER $1;
EOF

echo "done($1)."

exit 0
