#!/bin/bash

export CODE="magnolia"

docker run --rm -it --hostname=mint --network host -v $PWD:/srv/:z $CODE
