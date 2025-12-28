FROM ubuntu:latest
LABEL maintainer="Jeremy Zheng"

ENV DEBIAN_FRONTEND="noninteractive"
# https://launchpad.net/%7Edeadsnakes/+archive/ubuntu/ppa
ARG PYTHON_VERSION=3.12

RUN apt update
RUN apt -y install lsb-release apt-utils \
    debian-keyring debian-archive-keyring apt-transport-https software-properties-common curl wget gnupg
RUN add-apt-repository -y ppa:deadsnakes/ppa
RUN apt -y upgrade
RUN apt -y install git vim locales locales-all tzdata build-essential \
    fonts-dejavu-extra fonts-opensymbol fonts-lxgw-wenkai fonts-smiley-sans \
    fonts-noto-extra fonts-noto-cjk-extra fonts-noto-color-emoji \
    fonts-arphic-ukai fonts-arphic-uming \
    fonts-wqy-microhei fonts-wqy-zenhei \
    fonts-cns11643-kai fonts-cns11643-sung \
    fonts-moe-standard-kai fonts-moe-standard-song \
    fonts-ipaexfont fonts-ipafont fonts-konatu fonts-ipafont-nonfree-jisx0208 fonts-ipafont-nonfree-uigothic \
    fonts-mikachan \
    fonts-tibetan-machine fonts-ddc-uchen fonts-monlam fonts-sambhota-tsugring fonts-sambhota-yigchung \
    imagemagick ffmpeg graphviz pandoc texlive-full \
    python${PYTHON_VERSION}-full python${PYTHON_VERSION}-dev \
    libssl-dev libpq-dev libmysqlclient-dev \
    && apt -y autoremove \
    && apt -y clean

RUN echo "en_US.UTF-8 UTF-8" > /etc/locale.gen
RUN locale-gen
RUN update-locale LANG=en_US.UTF-8
RUN update-alternatives --set editor /usr/bin/vim.basic

RUN mkdir -p $HOME/downloads $HOME/build $HOME/local $HOME/tmp

# https://pip.pypa.io/en/stable/installation/
RUN sh -c ". $HOME/.profile \
    && python3 -m venv $HOME/local/python3 \
    && . $HOME/local/python3/bin/activate \
    && pip install --upgrade pip"
RUN echo '. $HOME/local/python3/bin/activate' >> $HOME/.bashrc

# https://github.com/nvm-sh/nvm
ENV NVM_VERSION="v0.40.3"
RUN git clone -b ${NVM_VERSION} https://github.com/nvm-sh/nvm.git $HOME/.nvm
RUN echo 'export NVM_DIR="$HOME/.nvm"' >> $HOME/.bashrc
RUN echo '[ -s "$NVM_DIR/nvm.sh" ] && \. "$NVM_DIR/nvm.sh"' >> $HOME/.bashrc
RUN echo '[ -s "$NVM_DIR/bash_completion" ] && \. "$NVM_DIR/bash_completion"' >> $HOME/.bashrc
RUN bash -i -c "nvm install --lts"

RUN echo "$(date -u +%4Y%m%d%H%M%S)" | tee /VERSION

EXPOSE 9000/tcp 8080/tcp

VOLUME /srv
WORKDIR /srv

CMD ["/bin/bash", "-l"]
