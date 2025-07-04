FROM ubuntu:latest
LABEL maintainer="Kassapa"

ENV DEBIAN_FRONTEND noninteractive

# https://nodejs.org/en/about/previous-releases
ARG NODEJS_VERSION="jod"

RUN apt update
RUN apt -y upgrade
RUN apt -y install lsb-release curl wget git vim locales locales-all tzdata build-essential
RUN apt -y autoremove
RUN apt -y clean

RUN echo "en_US.UTF-8 UTF-8" > /etc/locale.gen
RUN locale-gen
RUN update-locale LANG=en_US.UTF-8
RUN update-alternatives --set editor /usr/bin/vim.basic

# https://github.com/nvm-sh/nvm
ENV NVM_VERSION "v0.40.3"
RUN git clone -b ${NVM_VERSION} https://github.com/nvm-sh/nvm.git $HOME/.nvm
RUN echo 'export NVM_DIR="$HOME/.nvm"' >> $HOME/.bashrc
RUN echo '[ -s "$NVM_DIR/nvm.sh" ] && \. "$NVM_DIR/nvm.sh"' >> $HOME/.bashrc
RUN echo '[ -s "$NVM_DIR/bash_completion" ] && \. "$NVM_DIR/bash_completion"' >> $HOME/.bashrc
RUN bash -c ". $HOME/.nvm/nvm.sh && nvm install --lts=${NODEJS_VERSION}"

RUN echo "$(date -u +%4Y%m%d%H%M%S)" | tee /VERSION

VOLUME /srv
WORKDIR /srv

CMD ["/bin/bash", "-l"]
