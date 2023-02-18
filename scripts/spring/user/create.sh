#!/bin/bash

set -e

if [ "$#" -ne 1 ]
then
    echo "Usage: $0 USER"
    exit 1
fi

pacman -S --needed zsh git zip unzip bzip2 curl wget vim pwgen

if id "$1" &>/dev/null
then
    echo "user $1 found"
else
    echo "create user $1"
    useradd -m -d /home/$1 -s /bin/zsh $1
fi

echo 'reset password'
passwd -l $1
echo "$1:$(pwgen 32 1)" | chpasswd

echo 'setup nginx'
export WORKSPACE=/srv/http/$1

if [ ! -d $WORKSPACE/htdocs ]
then
    mkdir -p $WORKSPACE/htdocs
    chown $1:$1 $WORKSPACE/htdocs
fi

if [ ! -d $WORKSPACE/logs ]
then
    mkdir -p $WORKSPACE/logs
    chown http:http $WORKSPACE/logs
fi

if [ ! -d $WORKSPACE/tmp ]
then
    mkdir -p /workspace/tmp/$1
    chown $1:$1 /workspace/tmp/$1
fi

if [ ! -d $WORKSPACE/dashboard ]
then
    mkdir -p /workspace/dashboard/$1
    chown $1:$1 /workspace/dashboard/$1
fi

if [ ! -f $WORKSPACE/nginx.conf ]
then
    # https://en.wikipedia.org/wiki/Hostname#Restrictions_on_valid_host_names
    cat > $WORKSPACE/nginx.conf <<EOF
# https://laravel.com/docs/9.x/deployment#nginx

server {
  listen 60080;
  server_name ${1//_/-}.spring.wikipali.org;

  access_log $WORKSPACE/logs/access.org;
  error_log $WORKSPACE/logs/error.log;

  add_header X-Frame-Options "SAMEORIGIN";
  add_header X-Content-Type-Options "nosniff";

  root $WORKSPACE/htdocs/public;
  index index.html index.php;

  charset utf-8;
  gzip on;
  client_max_body_size 16M;

  location / {
    try_files \$uri \$uri/ /index.php?\$query_string;
  }

  location /my/ {
    alias $WORKSPACE/dashboard/;
    try_files \$uri \$uri/ /my/index.html;
    
    location ~* \.(css|js|png|jpg|jpeg|gif|gz|svg|mp4|ogg|ogv|webm|htc|xml|woff)$ {
      access_log off;
      expires max;
    }
  }

  location = /favicon.ico { access_log off; log_not_found off; }
  location = /robots.txt  { access_log off; log_not_found off; }
  error_page 404 /index.php;
  
  location ~ \.php\$ {
    fastcgi_pass unix:/run/php-fpm/php-fpm.sock;
    fastcgi_param SCRIPT_FILENAME \$realpath_root\$fastcgi_script_name;
    include fastcgi_params;
  }
  location ~ /\.(?!well-known).* {
    deny all;
  }
}
EOF

ln -sf $WORKSPACE/nginx.conf /etc/nginx/sites-enabled/$1-spring.conf

fi

echo "done($1)."

exit 0
