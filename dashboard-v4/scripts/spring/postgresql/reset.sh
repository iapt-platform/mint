#!/bin/bash

set -e

if [ "$#" -ne 1 ]
then
    echo "Usage: $0 USER"
    exit 1
fi

psql << EOF
DROP DATABASE $1_mint;
CREATE DATABASE $1_mint WITH ENCODING = 'UTF8' OWNER $1;
EOF

echo "done($1)."

exit 0
