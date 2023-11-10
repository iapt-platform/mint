#!/bin/bash

export CODE="palm-lily"

podman run --rm --events-backend=file --hostname=palm --network host $CODE /bin/bash -i -c "python . $*"
