FROM ubuntu:latest
LABEL maintainer="Jeremy Zheng"

ENV DEBIAN_FRONTEND="noninteractive"
# https://launchpad.net/~ondrej/+archive/ubuntu/php
ARG PHP_VERSION=8.5

RUN apt update
RUN apt -y install lsb-release apt-utils \
    debian-keyring debian-archive-keyring apt-transport-https software-properties-common curl wget gnupg
RUN add-apt-repository -y ppa:ondrej/php
RUN apt -y upgrade
RUN apt -y install git vim locales locales-all inetutils-telnet iproute2 tzdata build-essential \
    php${PHP_VERSION}-cli php${PHP_VERSION}-fpm \
    php${PHP_VERSION}-xml php${PHP_VERSION}-imap php${PHP_VERSION}-intl php${PHP_VERSION}-mbstring php${PHP_VERSION}-bcmath \
    php${PHP_VERSION}-bz2 php${PHP_VERSION}-zip php${PHP_VERSION}-curl php${PHP_VERSION}-gd php${PHP_VERSION}-imagick \
    php${PHP_VERSION}-pgsql php${PHP_VERSION}-mysql php${PHP_VERSION}-sqlite3 php${PHP_VERSION}-redis php${PHP_VERSION}-amqp \
    fonts-dejavu-extra fonts-opensymbol fonts-lxgw-wenkai fonts-smiley-sans \
    fonts-noto-extra fonts-noto-cjk-extra fonts-noto-color-emoji \
    fonts-arphic-ukai fonts-arphic-uming \
    fonts-wqy-microhei fonts-wqy-zenhei \
    fonts-cns11643-kai fonts-cns11643-sung \
    fonts-moe-standard-kai fonts-moe-standard-song \
    fonts-ipaexfont fonts-ipafont fonts-konatu fonts-ipafont-nonfree-jisx0208 fonts-ipafont-nonfree-uigothic \
    fonts-mikachan \
    fonts-tibetan-machine fonts-ddc-uchen fonts-monlam fonts-sambhota-tsugring fonts-sambhota-yigchung \
    imagemagick ffmpeg graphviz pandoc texlive-full p7zip-full \
    python3-full python3-dev \
    libssl-dev libpq-dev libmysqlclient-dev \
    && apt -y autoremove \
    && apt -y clean

RUN echo "en_US.UTF-8 UTF-8" > /etc/locale.gen
RUN locale-gen
RUN update-locale LANG=en_US.UTF-8
RUN update-alternatives --set editor /usr/bin/vim.basic

RUN mkdir -p $HOME/downloads $HOME/build $HOME/local $HOME/tmp

# https://getcomposer.org/doc/00-intro.md#installation-linux-unix-macos
RUN wget -q -O $HOME/downloads/composer https://getcomposer.org/installer
RUN cd $HOME/downloads && php${PHP_VERSION} composer && cp composer.phar /usr/local/bin/composer

# https://github.com/nvm-sh/nvm
ENV NVM_VERSION="v0.40.3"
RUN git clone -b ${NVM_VERSION} https://github.com/nvm-sh/nvm.git $HOME/.nvm
RUN echo 'export NVM_DIR="$HOME/.nvm"' >> $HOME/.bashrc
RUN echo '[ -s "$NVM_DIR/nvm.sh" ] && \. "$NVM_DIR/nvm.sh"' >> $HOME/.bashrc
RUN echo '[ -s "$NVM_DIR/bash_completion" ] && \. "$NVM_DIR/bash_completion"' >> $HOME/.bashrc
RUN bash -i -c "nvm install --lts"
RUN bash -i -c "npm install -g yarn"

RUN echo "$(date -u +%4Y%m%d%H%M%S)" | tee /VERSION

EXPOSE 9000/tcp 8080/tcp

VOLUME /srv
WORKDIR /srv

CMD ["/bin/bash", "-l"]
