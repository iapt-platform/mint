#!/bin/bash

set -e

export PYTHON_HOME=$PWD/python

if [ ! -d $PYTHON_HOME ]
then
    python -m venv $PYTHON_HOME
fi

source $PWD/python/bin/activate

if [ ! -f $PYTHON_HOME/bin/ttx ]
then
    pip install psycopg minio redis[hiredis] \
        pika msgpack matplotlib ebooklib \
        grpcio protobuf grpcio-health-checking \
        pandas openpyxl xlrd pyxlsb
fi

python lily $*
