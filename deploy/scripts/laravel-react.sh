#!/bin/bash

set -e


if [ "$#" -ne 1 ]
then
    echo "USAGE: $0 DOMAIN"
    exit 1
fi

echo "check $1.conf"
if [ ! -d /var/www/$1/logs ]
then
  mkdir -p /var/www/$1/logs
  chown -R www-data:www-data /var/www/$1/logs
fi

if [ ! -f /etc/nginx/sites-enabled/$1.conf ]
then
    # https://laravel.com/docs/10.x/deployment
    cat > /etc/nginx/sites-enabled/$1.conf <<EOF
server {

  server_name $1;
  root /var/www/$1/htdocs/public;
  access_log /var/www/$1/logs/access.log;
  error_log  /var/www/$1/logs/error.log;
  
  add_header X-Frame-Options "SAMEORIGIN";
  add_header X-Content-Type-Options "nosniff";

  index index.php;
  charset utf-8;

  gzip on;
  gzip_comp_level 9;
  gzip_min_length 1k;
  gzip_types text/plain text/css application/xml application/javascript;
  gzip_vary on;
  client_max_body_size 128M;
  

  location /pcd/ {
    alias /var/www/$1/dashboard/;
    try_files \$uri \$uri/ /pcd/index.html;
    
    location ~* \\.(css|js|png|jpg|jpeg|gif|gz|svg|mp4|ogg|ogv|webm|htc|xml|woff)\$ {
      access_log off;
      expires max;
    }
  }
  
  location / {
    try_files \$uri \$uri/ /index.php?\$query_string;
  }

  location = /favicon.ico { access_log off; log_not_found off; }
  location = /robots.txt  { access_log off; log_not_found off; }
  error_page 404 /index.php;

  location ~ \.php\$ {
    fastcgi_pass unix:/var/run/php/php8.1-fpm.sock;
    fastcgi_param SCRIPT_FILENAME \$realpath_root\$fastcgi_script_name;
    include fastcgi_params;
  }
 
  location ~ /\.(?!well-known).* {
      deny all;
  }

}
EOF

    chmod 644 /etc/nginx/sites-enabled/$1.conf
fi

echo "done($1)."
exit 0
