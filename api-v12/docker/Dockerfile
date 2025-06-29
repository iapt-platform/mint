FROM ubuntu:latest
LABEL maintainer="Kassapa"

ENV DEBIAN_FRONTEND noninteractive
# https://launchpad.net/~deadsnakes/+archive/ubuntu/ppa
ARG _VERSION=3.13

RUN apt update
RUN apt -y install lsb-release apt-utils \
    debian-keyring debian-archive-keyring apt-transport-https software-properties-common curl wget gnupg
RUN add-apt-repository -y ppa:ondrej/php
RUN apt -y upgrade
RUN apt -y install git vim locales locales-all tzdata build-essential \
    php${PHP_VERSION}-cli php${PHP_VERSION}-fpm \
    php${PHP_VERSION}-xml php${PHP_VERSION}-imap php${PHP_VERSION}-intl php${PHP_VERSION}-mbstring php${PHP_VERSION}-bcmath \
    php${PHP_VERSION}-bz2 php${PHP_VERSION}-zip php${PHP_VERSION}-curl php${PHP_VERSION}-gd php${PHP_VERSION}-imagick \
    php${PHP_VERSION}-pgsql php${PHP_VERSION}-mysql php${PHP_VERSION}-sqlite3 php${PHP_VERSION}-redis php${PHP_VERSION}-amqp
RUN apt -y autoremove
RUN apt -y clean

RUN echo "en_US.UTF-8 UTF-8" > /etc/locale.gen
RUN locale-gen
RUN update-locale LANG=en_US.UTF-8
RUN update-alternatives --set editor /usr/bin/vim.basic

RUN echo "$(date -u +%4Y%m%d%H%M%S)" | tee /VERSION

VOLUME /srv
WORKDIR /srv

CMD ["/bin/bash", "-l"]
