#!/bin/bash

set -e

if [ "$#" -ne 2 ]
then
  echo "USAGE: $0 DOMAIN REPO"
  exit 1
fi

export WORKSPACE=/var/www/$1

if [ ! -d $WORKSPACE/repo.git ]
then
  git clone $2 $WORKSPACE/repo.git
fi

cd $WORKSPACE/repo.git
git pull
git submodule update --init --recursive
hugo -d $WORKSPACE/htdocs

echo "done."
exit 0

