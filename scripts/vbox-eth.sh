#!/bin/sh

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

if [ ! -f /etc/resolv.conf.orig ]
then
    cp -v /etc/resolv.conf /etc/resolv.conf.orig
fi

cat > /etc/resolv.conf <EOF
nameserver 8.8.8.8
nameserver 8.8.4.4
EOF

echo 'done.'
