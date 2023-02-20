#!/bin/bash

set -e


if [ "$#" -ne 2 ]
then
    echo "USAGE: $0 DOMAIN PORT"
    exit 1
fi

echo "check /etc/nginx/sites-enabled/$1.conf"
if [ ! -f /etc/nginx/sites-enabled/$1.conf ]
then
    cat > aaa.confg <<EOF
server {

  server_name $1;
  access_log /var/log/nginx/$1.access.log;
  error_log  /var/log/nginx/$1.error.log;

  gzip on;
  gzip_comp_level 9;
  gzip_min_length 1k;
  gzip_types text/plain text/css application/xml application/javascript;
  gzip_vary on;
  client_max_body_size 128M;
  

  location /my/ {
    alias /var/www/$1/current/dashboard/;
    try_files \$uri \$uri/ /my/index.html;
    
    location ~* \\.(css|js|png|jpg|jpeg|gif|gz|svg|mp4|ogg|ogv|webm|htc|xml|woff)\$ {
      access_log off;
      expires max;
    }
  }
  
  location / {
    proxy_set_header X-Forwarded-Proto http;
    proxy_set_header X-Real-IP \$remote_addr;
    proxy_set_header X-Forwarded-For \$proxy_add_x_forwarded_for;    
    proxy_set_header Host \$http_host;
    proxy_redirect off;
    proxy_pass http://127.0.0.1:$2;
    proxy_set_header Upgrade \$http_upgrade;
    proxy_set_header Connection "upgrade";
  }

}
EOF

    chmod 644 /etc/nginx/sites-enabled/$1.conf
fi

echo "done($1)."
exit 0
