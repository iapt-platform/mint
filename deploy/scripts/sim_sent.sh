#!/bin/sh

set -e

export SIM=$(pgrep -f "php sim_sent")

echo "find pid $SIM"
renice +19 $SIM
ionice -c 2 -n 7 -p $SIM

echo "done."

exit 0
