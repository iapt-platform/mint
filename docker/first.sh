#!/bin/sh
podman run --name mint -it --userns=keep-id --user=$(id -ur):$(id -gr) --network host --events-backend=file -v $PWD:/workspace:z palm
