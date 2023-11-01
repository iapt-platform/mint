#!/bin/sh

export CODE="mint-lily"

podman run -it --rm --events-backend=file --hostname=palm --network host $CODE
