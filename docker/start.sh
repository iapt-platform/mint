#!/bin/sh

podman run --rm -it --userns=keep-id \
    --user=$(id -ur):$(id -gr) --network host \
    --events-backend=file -v $PWD:/workspace:z mint
