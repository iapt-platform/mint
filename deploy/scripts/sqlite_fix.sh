#!/bin/sh

set -e
if [ $# -ne 1 ]
then
	echo "Usage: $0 DB"
	exit 1
fi

if [ ! -f $1 ]
then
	echo "$1 not exists"
	exit 1
fi

echo '.dump'|sqlite3 $1|sqlite3 $1_repaired
mv -v $1 $1_corrupt
mv -v $1_repaired $1

exit 0
