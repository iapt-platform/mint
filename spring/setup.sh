#!/bin/bash

set -e

apt -y upgrade
apt -y install debian-keyring debian-archive-keyring apt-transport-https software-properties-common curl wget gnupg
apt -y install zsh git locales locales-all rsync openssh-client sshpass \
    vim tzdata pwgen zip unzip tree tmux dialog asciidoc doxygen \
    net-tools dnsutils net-tools iputils-arping iputils-ping telnet \
    imagemagick ffmpeg fonts-dejavu-extra texlive-full \
    build-essential cmake pkg-config libtool automake autoconf \
    python3 python3-distutils python3-dev python3-pip virtualenv \
    php-fpm php-mbstring php-json php-xml php-pear php-bcmath php-curl php-zip \
    php-mysql php-pgsql php-sqlite3 php-redis php-mongodb php-amqp php-zmq \
    php-imagick php-gd \
    nginx rabbitmq-server redis postgresql
apt -y autoremove
apt -y clean

echo "en_US.UTF-8 UTF-8" > /etc/locale.gen
locale-gen
update-locale LANG=en_US.UTF-8
update-alternatives --set editor /usr/bin/vim.basic

mkdir -p $HOME/downloads $HOME/build $HOME/local $HOME/tmp

# https://github.com/ohmyzsh/ohmyzsh
if [ ! -d $HOME/.oh-my-zsh ]
then
    git clone https://github.com/ohmyzsh/ohmyzsh.git $HOME/.oh-my-zsh
    cp $HOME/.oh-my-zsh/templates/zshrc.zsh-template $HOME/.zshrc
    echo 'export LANG=en_US.UTF-8' >> $HOME/.zshrc
    echo 'export LC_ALL=en_US.UTF-8' >> $HOME/.zshrc
    echo 'export PATH=$HOME/.local/bin:$PATH' >> $HOME/.zshrc
fi
chsh -s /bin/zsh

git config --global core.quotepath false 
git config --global http.version HTTP/1.1 
git config --global pull.rebase false 
git config --global url."https://".insteadOf git://

echo 'set-option -g history-limit 102400' > $HOME/.tmux.conf
echo 'set-option -g default-shell "/bin/zsh"' >> $HOME/.tmux.conf

# https://getcomposer.org/doc/00-intro.md#installation-linux-unix-macos
if [ ! -f $HOME/.local/bin/composer ]
then
    wget -q -O $HOME/downloads/composer https://getcomposer.org/installer
    cd $HOME/downloads
    php composer
    mv composer.phar $HOME/.local/bin/composer
fi

# https://github.com/nvm-sh/nvm

if [ ! -d $HOME/.nvm ]
then
    export NVM_VERSION="v0.39.3"
    git clone -b ${NVM_VERSION} https://github.com/nvm-sh/nvm.git $HOME/.nvm
    echo 'export NVM_DIR="$HOME/.nvm"' >> $HOME/.zshrc
    echo '[ -s "$NVM_DIR/nvm.sh" ] && \. "$NVM_DIR/nvm.sh"' >> $HOME/.zshrc
    echo '[ -s "$NVM_DIR/bash_completion" ] && \. "$NVM_DIR/bash_completion"' >> $HOME/.zshrc
    zsh -c "source $HOME/.zshrc \
        && nvm install node \
        && nvm use node \
        && npm i yarn -g"
    echo 'export PATH=$HOME/.yarn/bin:$PATH' >> $HOME/.zshrc
fi




# https://opensearch.org/downloads.html#opensearch
export OPENSEARCH_VERSION="2.4.1"
if [ ! -d /opt/opensearch-${OPENSEARCH_VERSION} ]
then
    wget -q -P $HOME/downloads \
        https://artifacts.opensearch.org/releases/bundle/opensearch/${OPENSEARCH_VERSION}/opensearch-${OPENSEARCH_VERSION}-linux-x64.tar.gz
    tar xf $HOME/downloads/opensearch-${OPENSEARCH_VERSION}-linux-x64.tar.gz -C /opt
    # https://opensearch.org/docs/latest/opensearch/install/tar/
    echo "network.host: 0.0.0.0" >> /opt/opensearch-${OPENSEARCH_VERSION}/config/opensearch.yml
    echo "discovery.type: single-node" >> /opt/opensearch-${OPENSEARCH_VERSION}/config/opensearch.yml
    echo "plugins.security.disabled: true" >> /opt/opensearch-${OPENSEARCH_VERSION}/config/opensearch.yml
    chown -R nobody /opt/opensearch-${OPENSEARCH_VERSION}
fi

# https://min.io/download#/linux
if [ ! -f /usr/bin/minio ]
then
    wget -q -O /usr/bin/minio \
        https://dl.min.io/server/minio/release/linux-amd64/minio
    chmod +x /usr/bin/minio
    mkdir -p /var/lib/minio/data
    chown -R nobody /var/lib/minio
fi

echo 'init postgresql data folder'
if [ ! -d /var/lib/postgresql/data ]
then
    su - postgres -c "/usr/lib/postgresql/14/bin/initdb -D /var/lib/postgresql/data"
    echo "listen_addresses = '0.0.0.0'" >> /var/lib/postgresql/data/postgresql.conf
    echo "host  all  all 0.0.0.0/0 md5" >> /var/lib/postgresql/data/pg_hba.conf
fi

ADD etc/redis/* /etc/redis/
cd /var/lib \
    && mkdir redis-s redis-1 redis-2 redis-3 redis-4 redis-5 redis-6 \
    && chown redis:redis redis-s redis-1 redis-2 redis-3 redis-4 redis-5 redis-6 \
    && chmod 750 redis-s redis-1 redis-2 redis-3 redis-4 redis-5 redis-6

mkdir /run/php \
    && echo "<?php phpinfo(); ?>" > /var/www/html/info.php \
    && echo "daemon off;" >> /etc/nginx/nginx.conf
ADD etc/nginx.conf /etc/nginx/sites-enabled/default

cat > /etc/nginx/sites-enabled/default <<EOF
server {
    listen 80;
    server_name _;
    index index.html index.php;
    root /var/www/html;
    
    access_log /var/log/nginx/localhost.access.log custom;
    error_log /var/log/nginx/localhost.error.log warn;

    location / {
        try_files \$uri \$uri/ =404;
    }

    location ~ \.php\$ {
        include snippets/fastcgi-php.conf;
        fastcgi_pass unix:/run/php/php8.1-fpm.sock;
    }
}
EOF

echo '20221226' > /VERSION

echo 'done.'
