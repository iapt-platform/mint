#!/bin/bash

set -e


if [ "$#" -ne 1 ]
then
    echo "USAGE: $0 NODE_ID"
    exit 1
fi

echo "check /etc/redis/node-$1.conf"
if [ ! -d /etc/redis ]
then
    mkdir -p /etc/redis
fi

if [ ! -f /etc/redis/node-$1.conf ]
then
    cat > /etc/redis/node-$1.conf <<EOF
bind 0.0.0.0
port 637${1}
daemonize no
dir /var/lib/redis-node-$1

cluster-enabled yes
cluster-config-file /tmp/redis-node-$1.conf
cluster-node-timeout 5000

appendonly yes
appendfsync everysec
EOF
    chown redis:redis /etc/redis/node-$1.conf
    chmod 400 /etc/redis/node-$1.conf
fi

echo "create /var/lib/redis-node-$1"
if [ ! -d /var/lib/redis-node-$1 ]
then
    mkdir -p /var/lib/redis-node-$1
    chown redis:redis /var/lib/redis-node-$1
    chmod 700 /var/lib/redis-node-$1
fi

echo "create /lib/systemd/system/redis-node-$1.service"
if [ ! -f /lib/systemd/system/redis-node-$1.service ]
then

    cat > /lib/systemd/system/redis-node-$1.service <<EOF
[Unit]
Description=Redis cluster node-$1
After=network.target

[Service]
Type=simple
User=redis
Group=redis
WorkingDirectory=/var/lib/redis-node-$1
ExecStart=/usr/bin/redis-server /etc/redis/node-$1.conf
# or always, on-abort, on-failure, etc
Restart=always 
RestartSec=10s

[Install]
WantedBy=multi-user.target
EOF
    chmod 444 /lib/systemd/system/redis-node-$1.service
    systemctl daemon-reload
    systemctl enable redis-node-$1
fi

echo "done."

exit 0
