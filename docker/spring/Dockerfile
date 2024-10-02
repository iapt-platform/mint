FROM ubuntu:latest
LABEL maintainer="Jeremy Zheng"

ENV DEBIAN_FRONTEND noninteractive

RUN apt update
RUN apt -y upgrade

RUN apt -y install wget vim locales locales-all tzdata zip unzip tree tmux \
    build-essential

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
ARG OPENSEARCH_ARCH
ENV OPENSEARCH_VERSION "2.17.0"
ENV OPENSEARCH_JAVA_HOME "/opt/opensearch-${OPENSEARCH_VERSION}/jdk"
RUN wget -q -P $HOME/downloads \
    https://artifacts.opensearch.org/releases/bundle/opensearch/${OPENSEARCH_VERSION}/opensearch-${OPENSEARCH_VERSION}-linux-${OPENSEARCH_ARCH}.tar.gz
RUN cd /opt && tar xf $HOME/downloads/opensearch-${OPENSEARCH_VERSION}-linux-${OPENSEARCH_ARCH}.tar.gz 
RUN echo "network.host: 0.0.0.0" >> /opt/opensearch-${OPENSEARCH_VERSION}/config/opensearch.yml
RUN echo "discovery.type: single-node" >> /opt/opensearch-${OPENSEARCH_VERSION}/config/opensearch.yml 
RUN echo "plugins.security.disabled: true" >> /opt/opensearch-${OPENSEARCH_VERSION}/config/opensearch.yml


RUN echo "$(date -u +%4Y%m%d%H%M%S)" | tee /VERSION

VOLUME /workspace
WORKDIR /workspace

CMD ["/bin/zsh", "-l"]
