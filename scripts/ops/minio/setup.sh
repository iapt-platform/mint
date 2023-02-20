#!/bin/bash

set -e

if [ "$#" -ne 1 ]
then
    echo "USAGE: $0 DOMAIN"
    exit 1
fi

echo "check /var/lib/minio"
if [ ! -d /var/lib/minio ]
then
    mkdir /var/lib/minio
    chown www-data:www-data /var/lib/minio
    chmod 700 /var/lib/minio
fi

echo "check /lib/systemd/system/minio.service"
if [ ! -f /lib/systemd/system/minio.service ]
then
    cat > /lib/systemd/system/minio.service <<EOF
[Unit]
Description=MinIO offers high-performance, S3 compatible object storage.
After=network.target

[Service]
Type=simple
User=www-data
Group=www-data
WorkingDirectory=/var/lib/minio
ExecStart=/usr/bin/minio server data
Restart=always
RestartSec=10s

Environment="MINIO_ADDRESS=127.0.0.1:9000"
Environment="MINIO_CONSOLE_ADDRESS=127.0.0.1:9001"
Environment="MINIO_ROOT_USER=www"
Environment="MINIO_ROOT_PASSWORD=$(pwgen 32 1)"

[Install]
WantedBy=multi-user.target
EOF
    chmod 444 /lib/systemd/system/minio.service
    systemctl daemon-reload
    systemctl enable minio
fi


echo "check /etc/nginx/sites-enabled/s3.$1.conf"
if [ ! -f /etc/nginx/sites-enabled/s3.$1.conf ]
then

    cat > /etc/nginx/sites-enabled/s3.$1.conf <<EOF
server {
  server_name assets.$1;
  access_log /var/log/nginx/assets.$1.access.log;
  error_log  /var/log/nginx/assets.$1.error.log;

  location / {
    proxy_set_header X-Forwarded-Proto http;
    proxy_set_header X-Real-IP \$remote_addr;
    proxy_set_header X-Forwarded-For \$proxy_add_x_forwarded_for;    
    proxy_set_header Host \$http_host;
    proxy_redirect off;
    proxy_pass http://127.0.0.1:9000;
    proxy_set_header Upgrade \$http_upgrade;
    proxy_set_header Connection "upgrade";
  }
}

server {

  server_name s3.$1;
  access_log /var/log/nginx/s3.$1.access.log;
  error_log  /var/log/nginx/s3.$1.error.log;

  location / {
    proxy_set_header X-Forwarded-Proto http;
    proxy_set_header X-Real-IP \$remote_addr;
    proxy_set_header X-Forwarded-For \$proxy_add_x_forwarded_for;    
    proxy_set_header Host \$http_host;
    proxy_redirect off;
    proxy_pass http://127.0.0.1:9001;
    proxy_set_header Upgrade \$http_upgrade;
    proxy_set_header Connection "upgrade";
  }
}

EOF
    chmod 644 /etc/nginx/sites-enabled/s3.$1.conf
fi

echo "done($1)."
exit 0



