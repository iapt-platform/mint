#!/bin/bash

if [ "$#" -ne 1 ]; then
    echo "USAGE: $0 PYTHON_VERSION"
    exit 1
fi

podman run --rm -it --events-backend=file --hostname=palm --network host -v $PWD:/srv:z "mint-python-$1"
