FROM ubuntu:latest
LABEL maintainer="Kassapa"

ENV DEBIAN_FRONTEND="noninteractive"

RUN apt update
RUN apt -y upgrade
RUN apt install -y lsb-release apt-utils \
    debian-keyring debian-archive-keyring apt-transport-https software-properties-common curl wget gnupg
RUN add-apt-repository -y ppa:ondrej/php
# https://launchpad.net/~deadsnakes/+archive/ubuntu/ppa
RUN add-apt-repository -y ppa:deadsnakes/ppa
# https://launchpad.net/~ondrej/+archive/ubuntu/php
RUN apt install -y zip unzip vim locales locales-all rsync openssh-client telnet sshpass pwgen screen \
    tzdata tree asciidoc doxygen imagemagick ffmpeg fonts-dejavu-extra texlive-full \
    git cmake build-essential ninja-build \
    redis-tools postgresql-client mariadb-client \
    php8.1-fpm php8.1-mbstring php8.1-xml php8.1-bcmath php8.1-curl php8.1-zip \
    php8.1-mysql php8.1-pgsql php8.1-sqlite3 php8.1-redis php8.1-mongodb php8.1-amqp php8.1-zmq \
    php8.1-imagick php8.1-gd php8.1-intl php8.1-soap \
    php8.4-fpm php8.4-mbstring php8.4-xml php8.4-bcmath php8.4-curl php8.4-zip \
    php8.4-mysql php8.4-pgsql php8.4-sqlite3 php8.4-redis php8.4-mongodb php8.4-amqp php8.4-zmq \
    php8.4-imagick php8.4-gd php8.4-intl php8.4-soap \
    python3.13-venv python3.13-dev
RUN apt clean
RUN apt autoremove

RUN echo "en_US.UTF-8 UTF-8" > /etc/locale.gen
RUN locale-gen
RUN update-alternatives --set editor /usr/bin/vim.basic
RUN python3.13 -m ensurepip --upgrade

# RUN useradd -s /bin/bash -m deploy
# RUN passwd -l deploy
# RUN echo 'deploy ALL=(ALL:ALL) NOPASSWD: ALL' > /etc/sudoers.d/101-deploy
# USER deploy

RUN mkdir $HOME/local $HOME/tmp $HOME/downloads

# https://github.com/sdkman/sdkman-cli
# https://docs.gradle.org/current/userguide/compatibility.html
ENV JDK_VERSION="21.0.2-open"
RUN curl -s "https://get.sdkman.io" | bash
RUN sed -i -e 's/sdkman_auto_answer=false/sdkman_auto_answer=true/g' $HOME/.sdkman/etc/config
RUN bash -c ". $HOME/.sdkman/bin/sdkman-init.sh \
    && sdk install java ${JDK_VERSION} \
    && sdk install maven \
    && sdk install gradle \
    && sdk install kotlin"

# https://github.com/nvm-sh/nvm
ENV NVM_VERSION="v0.40.3"
RUN git clone -b ${NVM_VERSION} https://github.com/nvm-sh/nvm.git $HOME/.nvm
RUN echo 'export NVM_DIR="$HOME/.nvm"' >> $HOME/.bashrc 
RUN echo '[ -s "$NVM_DIR/nvm.sh" ] && \. "$NVM_DIR/nvm.sh"' >> $HOME/.bashrc 
RUN echo '[ -s "$NVM_DIR/bash_completion" ] && \. "$NVM_DIR/bash_completion"' >> $HOME/.bashrc
RUN bash -c ". $HOME/.nvm/nvm.sh \
    && nvm install --lts \
    && npm install -g yarn"

# https://getcomposer.org/doc/00-intro.md#installation-linux-unix-macos
RUN wget -q -O $HOME/downloads/composer https://getcomposer.org/installer
RUN cd $HOME/downloads && php composer && mv composer.phar /usr/local/bin/composer

RUN echo "$(date -u +%4Y%m%d%H%M%S)" | tee /VERSION

VOLUME /srv
WORKDIR /srv

CMD ["/bin/bash", "-l"]

