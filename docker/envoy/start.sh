#!/bin/bash

export CODE="palm-envoy"
export NAME="$CODE-$USER"

podman run -rm --events-backend=file --hostname=palm --network host $CODE
