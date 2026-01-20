#!/bin/bash

set -e

sed -i 's/^listen = .*/listen = 9000/g' /etc/php/$1/fpm/pool.d/www.conf

/usr/sbin/php-fpm${1} --nodaemonize --fpm-config /etc/php/$1/fpm/php-fpm.conf

exit 0
