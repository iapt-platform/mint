#!/bin/bash

set -e

if [ "$#" -ne 1 ]
then
    echo "Usage: $0 USER"
    exit 1
fi

apt -y install zsh git zip unzip bzip2 curl wget vim pwgen

if id "$1" &>/dev/null
then
    echo "user $1 found"
else
    echo "create user $1"
    useradd -m -d /workspace/home/$1 -s /bin/zsh $1
fi

echo 'reset password'
passwd -l $1
echo "$1:$(pwgen 32 1)" | chpasswd

echo 'setup nginx'

if [ ! -d /workspace/www/$1/htdocs ]
then
    mkdir -p /workspace/www/$1/htdocs
    chown $1:$1 /workspace/www/$1/htdocs
fi

if [ ! -d /workspace/www/$1/logs ]
then
    mkdir -p /workspace/www/$1/logs
    chown www-data:www-data /workspace/www/$1/logs
fi

if [ ! -f /workspace/www/$1/nginx.conf ]
then
    # https://en.wikipedia.org/wiki/Hostname#Restrictions_on_valid_host_names
    cat > /workspace/www/$1/nginx.conf <<EOF
# https://laravel.com/docs/9.x/deployment#nginx

server {
  listen 80;
  server_name ${1//_/-}.spring.wikipali.org;

  access_log /workspace/www/$1/logs/access.org;
  error_log /workspace/www/$1/logs/error.log;

  add_header X-Frame-Options "SAMEORIGIN";
  add_header X-Content-Type-Options "nosniff";

  root /workspace/www/$1/htdocs/public;
  index index.html index.php;

  charset utf-8;
  gzip on;
  client_max_body_size 16M;

  location / {
    try_files \$uri \$uri/ /index.php?\$query_string;
  }

  location = /favicon.ico { access_log off; log_not_found off; }
  location = /robots.txt  { access_log off; log_not_found off; }
  error_page 404 /index.php;
  
  location ~ \.php\$ {
    fastcgi_pass unix:/run/php/php-fpm.sock;
    fastcgi_param SCRIPT_FILENAME \$realpath_root\$fastcgi_script_name;
    include fastcgi_params;
  }
  location ~ /\.(?!well-known).* {
    deny all;
  }
}
EOF

ln -sf /workspace/www/$1/nginx.conf /etc/nginx/sites-enabled/$1.spring.wikipali.org.conf

fi

echo "done($1)."

exit 0
