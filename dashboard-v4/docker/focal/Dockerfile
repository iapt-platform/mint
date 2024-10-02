FROM ubuntu:focal
LABEL maintainer="Jeremy Zheng"

ENV DEBIAN_FRONTEND noninteractive

RUN apt update
RUN apt install -y lsb-release
RUN echo "deb [arch=amd64] http://archive.ubuntu.com/ubuntu/ $(lsb_release -cs) main restricted universe multiverse" > /etc/apt/sources.list
RUN echo "deb [arch=amd64] http://archive.ubuntu.com/ubuntu/ $(lsb_release -cs)-updates main restricted universe multiverse" >> /etc/apt/sources.list
RUN echo "deb [arch=amd64] http://archive.ubuntu.com/ubuntu/ $(lsb_release -cs)-security main restricted universe multiverse" >> /etc/apt/sources.list
RUN dpkg --add-architecture arm64
RUN echo "deb [arch=arm64] http://ports.ubuntu.com/ $(lsb_release -cs) main restricted universe multiverse" >> /etc/apt/sources.list
RUN echo "deb [arch=arm64] http://ports.ubuntu.com/ $(lsb_release -cs)-security main restricted universe multiverse" >> /etc/apt/sources.list
RUN echo "deb [arch=arm64] http://ports.ubuntu.com/ $(lsb_release -cs)-updates main restricted universe multiverse" >> /etc/apt/sources.list
RUN apt update && apt -y upgrade

ENV GCC_VERSION 10
ENV CLANG_VERSION 12
RUN apt -y install zsh git locales locales-all \
    vim tzdata zip unzip tree tmux \
    build-essential crossbuild-essential-arm64 \
    g++-${GCC_VERSION} g++-${GCC_VERSION}-aarch64-linux-gnu \
    libstdc++-${GCC_VERSION}-dev:amd64 libstdc++-${GCC_VERSION}-dev:arm64 \
    clang-${CLANG_VERSION} lldb-${CLANG_VERSION} lld-${CLANG_VERSION} \
    cmake pkg-config libtool automake autoconf autoconf-archive binutils cpio \
    debhelper bison flex ninja-build \
    python3 python3-distutils python3-dev python3-pip virtualenv
RUN update-alternatives --install /usr/bin/clang++ clang++ /usr/bin/clang++-${CLANG_VERSION} 100 \
    && update-alternatives --install /usr/bin/clang clang /usr/bin/clang-${CLANG_VERSION} 100 \
    && update-alternatives --install /usr/bin/lldb lldb /usr/bin/lldb-${CLANG_VERSION} 100 \
    && update-alternatives --install /usr/bin/lld lld /usr/bin/lld-${CLANG_VERSION} 100 \
    && update-alternatives --install /usr/bin/gcc gcc /usr/bin/gcc-${GCC_VERSION} 100 \
    && update-alternatives --install /usr/bin/g++ g++ /usr/bin/g++-${GCC_VERSION} 100 \
    && update-alternatives --install /usr/bin/aarch64-linux-gnu-gcc aarch64-linux-gnu-gcc /usr/bin/aarch64-linux-gnu-gcc-${GCC_VERSION} 100 \
    && update-alternatives --install /usr/bin/aarch64-linux-gnu-g++ aarch64-linux-gnu-g++ /usr/bin/aarch64-linux-gnu-g++-${GCC_VERSION} 100

RUN apt -y autoremove && apt -y clean

RUN cd /usr/bin && ln -s make gmake
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
ENV NVM_VERSION "v0.39.3"
RUN git clone -b ${NVM_VERSION} https://github.com/nvm-sh/nvm.git $HOME/.nvm
RUN echo 'export NVM_DIR="$HOME/.nvm"' >> $HOME/.zshrc
RUN echo '[ -s "$NVM_DIR/nvm.sh" ] && \. "$NVM_DIR/nvm.sh"' >> $HOME/.zshrc
RUN echo '[ -s "$NVM_DIR/bash_completion" ] && \. "$NVM_DIR/bash_completion"' >> $HOME/.zshrc
RUN zsh -c "source $HOME/.zshrc \
    && nvm install node \
    && nvm use node \
    && npm i yarn -g"
RUN echo 'export PATH=$HOME/.yarn/bin:$PATH' >> $HOME/.zshrc

# https://www.rust-lang.org/tools/install
RUN curl --proto '=https' --tlsv1.2 -sSf https://sh.rustup.rs | sh -s -- -y
RUN zsh -c "source $HOME/.cargo/env && rustup target add aarch64-unknown-linux-gnu"

# https://github.com/rui314/mold
RUN git clone -b v1.9.0 https://github.com/rui314/mold.git $HOME/downloads/mold
RUN apt install -y g++-10 \
    libssl-dev libxxhash-dev zlib1g-dev \
    file bsdmainutils
RUN zsh -c "source $HOME/.zshrc \
    && mkdir $HOME/build/mold \
    && cd $HOME/build/mold \
    && cmake -DCMAKE_BUILD_TYPE=Release -DCMAKE_CXX_COMPILER=g++-10 \
        -DCMAKE_INSTALL_PREFIX=$HOME/.local $HOME/downloads/mold \
    && make -j \
    && make install"
RUN cp $HOME/.local/bin/mold /usr/bin/

# https://github.com/grpc/grpc
ENV GRPC_VERSION "v1.51.1"
RUN git clone --recurse-submodules -b $GRPC_VERSION https://github.com/grpc/grpc.git $HOME/downloads/grpc
RUN zsh -c "source $HOME/.zshrc \
    && mkdir -pv $HOME/build/grpc \
    && cd $HOME/build/grpc \
    && cmake -DCMAKE_BUILD_TYPE=Release \
        -DgRPC_INSTALL=ON \
        -DgRPC_SSL_PROVIDER=package \
        -DgRPC_BUILD_TESTS=OFF \
        -DCMAKE_INSTALL_PREFIX=$HOME/.local $HOME/downloads/grpc \
    && make -j \
    && make install"

RUN echo "$(date -u +%4Y%m%d%H%M%S)" | tee /VERSION

VOLUME /workspace
WORKDIR /workspace

CMD ["/bin/zsh", "-l"]
