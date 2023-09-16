#!/bin/bash

set -e

if [ "$#" -ne 3 ]
then
    echo "USAGE: $0 NETWORK IP DEVICE"
    exit 1
fi

if [ "$EUID" -ne 0 ]
  then echo "Must run as root."
  exit 1
fi

ip address flush dev $3
ip address add $1.$2/24 broadcast $1.255 dev $3
ip route add default via $1.1 dev $3

cat <<EOF > /etc/resolv.conf.google
nameserver 8.8.8.8
nameserver 8.8.4.4
EOF


if [ -L /etc/resolv.conf ]
then
    ln -svf /etc/resolv.conf.google /etc/resolv.conf
fi

echo 'done.'
