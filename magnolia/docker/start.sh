#!/bin/bash

export CODE="magnolia"

docker run --rm -it --hostname=palm --network host -v $PWD:/srv/:z $CODE
