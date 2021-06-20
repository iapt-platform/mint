FROM ubuntu:latest
LABEL maintainer="Jeremy Zheng"

ENV DEBIAN_FRONTEND noninteractive

RUN apt update
RUN apt -y upgrade
RUN apt -y install apt-transport-https software-properties-common curl wget gnupg

RUN apt -y install zsh git locales rsync openssh-client sshpass \
    vim sudo tzdata pwgen curl zip unzip tree screen tmux \
    build-essential \
    php-cli php-fpm \
    php-xml php-json php-imap php-intl \
    php-mbstring php-bz2 php-zip php-curl \
    php-gd php-imagick \
    php-mysql php-pgsql php-sqlite3 php-redis
RUN apt -y autoremove
RUN apt -y clean

RUN echo "en_US.UTF-8 UTF-8" > /etc/locale.gen
RUN locale-gen
RUN update-locale LANG=en_US.UTF-8
RUN update-alternatives --set editor /usr/bin/vim.basic

# deploy
RUN useradd -m deploy -s /bin/zsh
RUN passwd -l deploy
RUN echo 'deploy ALL=(ALL) NOPASSWD:ALL' > /etc/sudoers.d/101-deploy

USER deploy

RUN mkdir -p $HOME/downloads $HOME/local

# https://github.com/ohmyzsh/ohmyzsh
RUN git clone https://github.com/ohmyzsh/ohmyzsh.git $HOME/.oh-my-zsh
RUN cp $HOME/.oh-my-zsh/templates/zshrc.zsh-template $HOME/.zshrc
RUN echo 'source $HOME/.profile' >> $HOME/.zshrc

RUN echo 'term screen-256color' >> $HOME/.screenrc
RUN echo 'startup_message off' >> $HOME/.screenrc
RUN echo "defscrollback 10240" >> $HOME/.screenrc

# https://github.com/nvm-sh/nvm
RUN curl -o- https://raw.githubusercontent.com/nvm-sh/nvm/v0.38.0/install.sh | sh
RUN sh -c ". $HOME/.profile \
    && nvm install node"
RUN sh -c ". $HOME/.profile \
    && npm i yarn -g"
RUN echo 'export PATH=$HOME/.yarn/bin:$PATH' >> $HOME/.profile

RUN mkdir -p $HOME/.local/bin
RUN wget https://getcomposer.org/download/latest-stable/composer.phar -O $HOME/.local/bin/composer
RUN chmod +x $HOME/.local/bin/composer

VOLUME /workspace
WORKDIR /workspace

CMD ["/bin/zsh", "-l"]
