#!/bin/bash

set -e

if [ "$#" -ne 1 ]
then
    echo "Usage: $0 USER"
    exit 1
fi

psql << EOF
DROP DATABASE $1_mint;
CREATE DATABASE $1_mint WITH ENCODING = 'UTF8';
GRANT ALL PRIVILEGES ON DATABASE $1_mint TO $1;
EOF

echo "done($1)."

exit 0
