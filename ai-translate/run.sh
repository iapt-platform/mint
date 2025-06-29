#!/bin/bash

if [ "$#" -lt 2 ]; then
    echo "USAGE: $0 PYTHON_VERSION ARGS"
    exit 1
fi

podman run --rm -it --events-backend=file --hostname=palm --network host -v $PWD:/srv:z "mint-python$1" bash -c "source ~/python3/bin/activate && mint-ai-translate-consumer ${@:2}"
