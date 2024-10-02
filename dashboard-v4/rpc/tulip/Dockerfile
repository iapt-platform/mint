FROM ubuntu:latest
LABEL maintainer="Jeremy Zheng"

ENV DEBIAN_FRONTEND noninteractive

RUN apt update
RUN apt install -y apt-utils debian-keyring debian-archive-keyring apt-transport-https software-properties-common gnupg
RUN LC_ALL=C.UTF-8 add-apt-repository -y ppa:ondrej/php
RUN apt -y upgrade
ENV PHP_VERSION="8.2"
RUN apt install -y sudo wget unzip \
    php${PHP_VERSION}-zip php${PHP_VERSION}-grpc php${PHP_VERSION}-protobuf
RUN apt clean

# https://getcomposer.org/download/
RUN wget https://raw.githubusercontent.com/composer/getcomposer.org/76a7060ccb93902cd7576b67264ad91c8a2700e2/web/installer -O - -q | php -- --quiet --install-dir=/usr/local/bin --filename=composer

RUN useradd -s /bin/bash -m deploy
RUN passwd -l deploy
RUN echo 'deploy ALL=(ALL:ALL) NOPASSWD: ALL' > /etc/sudoers.d/101-deploy
RUN mkdir /opt/tulip
RUN chown deploy:deploy /opt/tulip
USER deploy

COPY tulip /opt/tulip/
RUN cd /opt/tulip && composer install
RUN echo "$(date -u +%4Y%m%d%H%M%S)" | sudo tee /VERSION

WORKDIR /opt/tulip

CMD ["/bin/bash", "-l"]
