#!/bin/bash

set -e

if [ "$#" -ne 2 ]
then
    echo "Usage: $0 USER PASSWORD"
    exit 1
fi

psql -h 127.0.0.1 -U postgres << EOF
CREATE DATABASE $1_mint WITH ENCODING = 'UTF8';
CREATE USER $1 WITH PASSWORD '$2';
GRANT ALL PRIVILEGES ON DATABASE $1_mint TO $1;
EOF

echo "done($1)."

exit 0
