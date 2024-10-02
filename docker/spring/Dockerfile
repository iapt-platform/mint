FROM ubuntu:latest
LABEL maintainer="Jeremy Zheng"

ENV DEBIAN_FRONTEND noninteractive

RUN apt update
RUN apt -y upgrade
RUN apt -y install locales locales-all curl wget vim tzdata pwgen zip unzip tree tmux dialog asciidoc doxygen \
    build-essential mold \
    php-cli php-fpm php-xml php-imap php-intl php-mbstring php-bcmath php-bz2 php-zip php-curl php-gd php-imagick php-pgsql php-mysql php-sqlite3 php-redis php-amqp \
    postgresql postgresql-contrib rabbitmq-server redis \
    fonts-dejavu-extra fonts-opensymbol fonts-lxgw-wenkai fonts-smiley-sans \
    fonts-noto-extra fonts-noto-cjk-extra fonts-noto-color-emoji \
    fonts-arphic-ukai fonts-arphic-uming  \
    fonts-wqy-microhei fonts-wqy-zenhei \
    fonts-cns11643-kai fonts-cns11643-sung \
    fonts-moe-standard-kai fonts-moe-standard-song \
    fonts-ipaexfont fonts-ipafont fonts-konatu fonts-ipafont-nonfree-jisx0208 fonts-ipafont-nonfree-uigothic \
    fonts-mikachan \
    fonts-tibetan-machine fonts-ddc-uchen fonts-monlam fonts-sambhota-tsugring fonts-sambhota-yigchung \
    imagemagick ffmpeg graphviz pandoc texlive-full
RUN apt -y autoremove
RUN apt -y clean

RUN echo "en_US.UTF-8 UTF-8" > /etc/locale.gen
RUN locale-gen
RUN update-locale LANG=en_US.UTF-8
RUN update-alternatives --set editor /usr/bin/vim.basic

RUN mkdir -p $HOME/downloads $HOME/build $HOME/local $HOME/tmp

# https://github.com/ohmyzsh/ohmyzsh
RUN git clone https://github.com/ohmyzsh/ohmyzsh.git $HOME/.oh-my-zsh
RUN cp $HOME/.oh-my-zsh/templates/zshrc.zsh-template $HOME/.zshrc
RUN echo 'export LANG=en_US.UTF-8' >> $HOME/.zshrc
RUN echo 'export LC_ALL=en_US.UTF-8' >> $HOME/.zshrc
RUN echo 'export PATH=$HOME/.local/bin:$PATH' >> $HOME/.zshrc

RUN git config --global core.quotepath false \
    && git config --global http.version HTTP/1.1 \
    && git config --global pull.rebase false \
    && git config --global url."https://".insteadOf git://
RUN echo 'set-option -g history-limit 102400' > $HOME/.tmux.conf \
    && echo 'set-option -g default-shell "/bin/zsh"' >> $HOME/.tmux.conf

# https://github.com/nvm-sh/nvm
ENV NVM_VERSION "v0.40.1"
RUN git clone -b ${NVM_VERSION} https://github.com/nvm-sh/nvm.git $HOME/.nvm
RUN echo 'export NVM_DIR="$HOME/.nvm"' >> $HOME/.zshrc
RUN echo '[ -s "$NVM_DIR/nvm.sh" ] && \. "$NVM_DIR/nvm.sh"' >> $HOME/.zshrc
RUN echo '[ -s "$NVM_DIR/bash_completion" ] && \. "$NVM_DIR/bash_completion"' >> $HOME/.zshrc
RUN zsh -c "source $HOME/.zshrc \
    && nvm install --lts"

RUN echo "en_US.UTF-8 UTF-8" > /etc/locale.gen
RUN locale-gen
RUN update-locale LANG=en_US.UTF-8
RUN update-alternatives --set editor /usr/bin/vim.basic
RUN mkdir -p $HOME/downloads

# https://opensearch.org/downloads.html#opensearch
ENV OPENSEARCH_VERSION "2.17.0"
ENV OPENSEARCH_JAVA_HOME "/opt/opensearch-${OPENSEARCH_VERSION}/jdk"
RUN wget -q -P $HOME/downloads \
    https://artifacts.opensearch.org/releases/bundle/opensearch/${OPENSEARCH_VERSION}/opensearch-${OPENSEARCH_VERSION}-linux-${OPENSEARCH_ARCH}.tar.gz
RUN cd /opt && tar xf $HOME/downloads/opensearch-${OPENSEARCH_VERSION}-linux-${OPENSEARCH_ARCH}.tar.gz \
    && mv /opt/opensearch-${OPENSEARCH_VERSION} /opt/opensearch \
    && echo "network.host: 0.0.0.0" >> /opt/opensearch-${OPENSEARCH_VERSION}/config/opensearch.yml \
    && echo "discovery.type: single-node" >> /opt/opensearch-${OPENSEARCH_VERSION}/config/opensearch.yml \
    && echo "plugins.security.disabled: true" >> /opt/opensearch-${OPENSEARCH_VERSION}/config/opensearch.yml

RUN wget -q -O $HOME/downloads/minio https://dl.min.io/server/minio/release/linux-amd64/minio \
    && sudo mv $HOME/downloads/minio /usr/local/bin/minio \
    && sudo chown root:root /usr/local/bin/minio \
    && sudo chmod +x /usr/local/bin/minio
RUN mkdir -p /var/lib/minio/data \
    && chown -R nobody /var/lib/minio

# https://www.rabbitmq.com/access-control.html#loopback-users
RUN echo 'loopback_users = none' >> /etc/rabbitmq/rabbitmq.conf \
    && sed -i "s/NODENAME.*/NODENAME=palm/g" /etc/rabbitmq/rabbitmq-env.conf \
    && rabbitmq-plugins enable rabbitmq_mqtt \
    && rabbitmq-plugins enable rabbitmq_management

RUN su - postgres -c "/usr/bin/initdb -D /var/lib/postgres/data"
RUN echo "listen_addresses = '0.0.0.0'" >> /var/lib/postgres/data/postgresql.conf \
    && echo "host  all  all 0.0.0.0/0 md5" >> /var/lib/postgres/data/pg_hba.conf \
    && mkdir /run/postgresql \
    && chown -R postgres:postgres /run/postgresql

ADD etc/redis/* /etc/redis/
RUN mkdir -p /var/lib/redis \
    && cd /var/lib/redis \
    && mkdir single node-1 node-2 node-3 node-4 node-5 node-6 \
    && chown -R redis:redis /var/lib/redis \
    && chmod 750 /var/lib/redis

RUN mkdir -p /var/www/html \
    && echo "<?php phpinfo(); ?>" > /var/www/html/info.php \
    && echo "daemon off;" >> /etc/nginx/nginx.conf \
    && sed -i '35,79d' /etc/nginx/nginx.conf \
    && sed -i '35i include sites-enabled/*.conf;' /etc/nginx/nginx.conf \
    && sed -i "s/error_log =.*/error_log = log\/php-fpm.log/g" /etc/php/php-fpm.conf
ADD etc/nginx/sites-enabled/localhost.conf /etc/nginx/sites-enabled/localhost.conf

ADD etc/supervisor /etc/supervisor

RUN echo "$(date -u +%4Y%m%d%H%M%S)" | tee /VERSION

VOLUME /workspace
WORKDIR /workspace

CMD ["/bin/zsh", "-l"]
