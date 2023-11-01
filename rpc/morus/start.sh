#!/bin/sh

export CODE="mint-morus"

podman run -it --rm --events-backend=file --hostname=palm --network host -v $PWD:/workspace:z $CODE
