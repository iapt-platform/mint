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
    cat > /workspace/www/$1/nginx.conf <<EOF
server {
  listen 80;
  root /workspace/www/$1/htdocs;
  index index.html index.php;
  server_name $1.spring.wikipali.org;

  access_log /workspace/www/$1/logs/access.org;
  error_log /workspace/www/$1/logs/error.log;

  location / {
    try_files \$uri \$uri/ =404;
  }
  
  location ~ \.php$ {
    include snippets/fastcgi-php.conf;
    fastcgi_pass unix:/run/php/php-fpm.sock;
  }
}
EOF

ln -sf /workspace/www/$1/nginx.conf /etc/nginx/sites-enabled/$1.spring.wikipali.org.conf

fi

echo "done($1)."

exit 0
