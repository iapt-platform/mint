#!/bin/bash

export CODE="mint-frontend"
export NAME="$CODE-$USER"

if podman container exists $NAME; then
    podman start -i -a $NAME
else
    podman run --name $NAME -it --events-backend=file --hostname=mint --network host -v $PWD:/workspace:z $CODE
fi
