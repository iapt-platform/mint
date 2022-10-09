#!/bin/bash

export CODE="palm-alpine"
export NAME="$CODE-$USER"

if podman container exists $NAME
then
    podman start -i -a $NAME
else
    if [ "$(uname)" == "Darwin" ]
    then
        podman run --name $NAME -it --hostname=palm --network host -v $PWD:/workspace:z $CODE
    else
        podman run --name $NAME -it --events-backend=file --hostname=palm --network host -v $PWD:/workspace:z $CODE
    fi
fi
