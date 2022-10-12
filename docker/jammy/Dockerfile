FROM ubuntu:jammy
LABEL maintainer="Jeremy Zheng"

ENV DEBIAN_FRONTEND noninteractive

RUN apt update
RUN apt -y upgrade
RUN apt -y install debian-keyring debian-archive-keyring apt-transport-https software-properties-common curl wget gnupg

# https://wiki.ubuntu.com/ToolChain
RUN echo "deb [arch=amd64] http://archive.ubuntu.com/ubuntu/ $(lsb_release -cs) main restricted universe multiverse" > /etc/apt/sources.list
RUN echo "deb [arch=amd64] http://archive.ubuntu.com/ubuntu/ $(lsb_release -cs)-updates main restricted universe multiverse" >> /etc/apt/sources.list
RUN echo "deb [arch=amd64] http://archive.ubuntu.com/ubuntu/ $(lsb_release -cs)-security main restricted universe multiverse" >> /etc/apt/sources.list
RUN dpkg --add-architecture armhf
RUN dpkg --add-architecture arm64
RUN echo "deb [arch=armhf,arm64] http://ports.ubuntu.com/ $(lsb_release -cs) main restricted universe multiverse" >> /etc/apt/sources.list
RUN echo "deb [arch=armhf,arm64] http://ports.ubuntu.com/ $(lsb_release -cs)-security main restricted universe multiverse" >> /etc/apt/sources.list
RUN echo "deb [arch=armhf,arm64] http://ports.ubuntu.com/ $(lsb_release -cs)-updates main restricted universe multiverse" >> /etc/apt/sources.list
RUN apt update
RUN apt -y upgrade

RUN apt -y install zsh git locales locales-all rsync openssh-client sshpass \
    vim tzdata pwgen zip unzip tree tmux dialog \
    net-tools dnsutils net-tools iputils-arping iputils-ping telnet \
    imagemagick ffmpeg fonts-dejavu-extra \
    clang clang-format lldb lld \
    build-essential cmake pkg-config libtool automake autoconf binutils cpio mold \
    debhelper bison flex ninja-build \
    crossbuild-essential-armhf crossbuild-essential-arm64 \
    python3 python3-distutils python3-dev python3-pip virtualenv \
    php-fpm php-mbstring php-json php-xml php-pear php-bcmath php-curl php-zip \
    php-mysql php-pgsql php-sqlite3 php-redis php-mongodb php-amqp php-zmq \
    php-imagick php-gd \
    nginx rabbitmq-server redis postgresql mariadb-server

# https://launchpad.net/~ubuntu-toolchain-r/+archive/ubuntu/test
RUN add-apt-repository -y ppa:ubuntu-toolchain-r/test
RUN apt update
ENV GCC_VERSION 12
RUN apt install -y g++-${GCC_VERSION} g++-${GCC_VERSION}-aarch64-linux-gnu g++-${GCC_VERSION}-arm-linux-gnueabihf

# https://apt.llvm.org/
ENV CLANG_VERSION=14
RUN echo "deb [arch=amd64] http://apt.llvm.org/$(lsb_release -cs)/ llvm-toolchain-$(lsb_release -cs)-${CLANG_VERSION} main" > /etc/apt/sources.list.d/llvm.list
RUN wget -O - https://apt.llvm.org/llvm-snapshot.gpg.key | apt-key add -
RUN apt update
RUN apt install -y clang-${CLANG_VERSION} \
    clangd-${CLANG_VERSION} clang-tools-${CLANG_VERSION} clang-format-${CLANG_VERSION} \
    lldb-${CLANG_VERSION} lld-${CLANG_VERSION}
RUN update-alternatives --install /usr/bin/clang++ clang++ /usr/bin/clang++-${CLANG_VERSION} 100
RUN update-alternatives --install /usr/bin/clang clang /usr/bin/clang-${CLANG_VERSION} 100
RUN update-alternatives --install /usr/bin/clang-format clang-format /usr/bin/clang-format-${CLANG_VERSION} 100
RUN update-alternatives --install /usr/bin/lldb lldb /usr/bin/lldb-${CLANG_VERSION} 100
RUN update-alternatives --install /usr/bin/lld lld /usr/bin/lld-${CLANG_VERSION} 100

# https://www.envoyproxy.io/docs/envoy/latest/start/install#install-envoy-on-ubuntu-linux
RUN curl -sL 'https://deb.dl.getenvoy.io/public/gpg.8115BA8E629CC074.key' | gpg --dearmor -o /usr/share/keyrings/getenvoy-keyring.gpg
RUN echo a077cb587a1b622e03aa4bf2f3689de14658a9497a9af2c427bba5f4cc3c4723 /usr/share/keyrings/getenvoy-keyring.gpg | sha256sum --check
RUN echo "deb [arch=amd64 signed-by=/usr/share/keyrings/getenvoy-keyring.gpg] https://deb.dl.getenvoy.io/public/deb/ubuntu $(lsb_release -cs) main" | tee /etc/apt/sources.list.d/getenvoy.list
RUN apt update
RUN apt install -y getenvoy-envoy

# https://www.mongodb.com/docs/manual/tutorial/install-mongodb-on-ubuntu/
# RUN wget -qO - https://www.mongodb.org/static/pgp/server-6.0.asc | apt-key add -
# RUN echo "deb [arch=amd64] https://repo.mongodb.org/apt/ubuntu $(lsb_release -cs)/mongodb-org/6.0 multiverse" | tee /etc/apt/sources.list.d/mongodb-org-6.0.list
# RUN apt update
# RUN apt install -y mongodb

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
RUN echo 'source $HOME/.profile' >> $HOME/.zshrc
RUN echo 'export LANG=en_US.UTF-8' >> $HOME/.profile
RUN echo 'export LC_ALL=en_US.UTF-8' >> $HOME/.profile

RUN git config --global core.quotepath false
RUN git config --global http.version HTTP/1.1
RUN git config --global pull.rebase false
RUN echo 'set-option -g history-limit 102400' > $HOME/.tmux.conf

RUN sh -c ". $HOME/.profile && pip3 install --user cmake"

RUN sh -c ". $HOME/.profile \
    && pip3 install --user ansible paramiko"
RUN echo 'export ANSIBLE_HOST_KEY_CHECKING=False' >> $HOME/.profile
RUN echo 'export PATH=$HOME/.local/bin:$PATH' >> $HOME/.profile
RUN echo 'alias peony="ANSIBLE_LOG_PATH=$HOME/tmp/$(date +%Y%m%d%H%M%S).log ansible-playbook"' >> $HOME/.profile

RUN sh -c ". $HOME/.profile \
    && pip3 install --user conan \
    && conan profile new default --detect \
    && conan profile update settings.compiler.libcxx=libstdc++11 default"

RUN sh -c ". $HOME/.profile && pip3 install --user supervisor"

# https://getcomposer.org/doc/00-intro.md#installation-linux-unix-macos
RUN wget -q -O $HOME/downloads/composer https://getcomposer.org/installer
RUN cd $HOME/downloads \
    && php composer \
    && mv composer.phar $HOME/.local/bin/composer

# https://github.com/richfelker/musl-cross-make
RUN git clone https://github.com/richfelker/musl-cross-make.git $HOME/build/musl-cross-make
RUN cd $HOME/build/musl-cross-make \
    && echo "TARGET=x86_64-linux-musl" > config.mak \
    && echo "OUTPUT=/opt/crosstools" >> config.mak \
    && make \
    && make install 
RUN cd $HOME/build/musl-cross-make \
    && echo "TARGET=aarch64-linux-musl" > config.mak \
    && echo "OUTPUT=/opt/crosstools" >> config.mak \
    && make \
    && make install
RUN cd $HOME/build/musl-cross-make \
    && echo "TARGET=arm-linux-musleabihf" > config.mak \
    && echo "OUTPUT=/opt/crosstools" >> config.mak \
    && make \
    && make install

ENV BAZEL_VERSION "v1.14.0"
RUN wget -q -O $HOME/.local/bin/bazel \
    https://github.com/bazelbuild/bazelisk/releases/download/${BAZEL_VERSION}/bazelisk-linux-amd64
RUN chmod +x $HOME/.local/bin/bazel

# https://github.com/rbenv/rbenv
RUN git clone https://github.com/rbenv/rbenv.git $HOME/.rbenv
RUN cd $HOME/.rbenv && src/configure && make -C src
RUN echo 'export PATH="$HOME/.rbenv/bin:$PATH"' >> $HOME/.zshrc
RUN echo 'eval "$(rbenv init -)"' >> $HOME/.zshrc
RUN git clone https://github.com/rbenv/ruby-build.git $HOME/.rbenv/plugins/ruby-build
RUN git clone https://github.com/rbenv/rbenv-vars.git $HOME/.rbenv/plugins/rbenv-vars
ENV RUBY_VERSION "3.1.2"
RUN apt install -y libssl-dev
RUN zsh -c "source $HOME/.zshrc \
    && rbenv install ${RUBY_VERSION} \
    && rbenv global ${RUBY_VERSION} \
    && gem install bundler"

ENV GO_VERSION "1.19.2"
RUN wget -q -P $HOME/downloads https://go.dev/dl/go${GO_VERSION}.linux-amd64.tar.gz
RUN tar xf $HOME/downloads/go${GO_VERSION}.linux-amd64.tar.gz -C $HOME/local
RUN echo 'export GOROOT=$HOME/local/go' >> $HOME/.zshrc
RUN echo 'export PATH=$GOROOT/bin:$PATH' >> $HOME/.zshrc
RUN echo 'export GOPATH=$HOME/go' >> $HOME/.zshrc

ENV JDK_VERSION "19-open"
RUN curl -s "https://get.sdkman.io" | zsh
RUN sed -i -e 's/sdkman_auto_answer=false/sdkman_auto_answer=true/g' $HOME/.sdkman/etc/config
RUN zsh -c "source $HOME/.zshrc \
    && sdk install java ${JDK_VERSION} \
    && sdk install maven \
    && sdk install gradle"

# https://github.com/nvm-sh/nvm
RUN curl -o- https://raw.githubusercontent.com/nvm-sh/nvm/v0.39.1/install.sh | sh
RUN sh -c ". $HOME/.profile \
    && nvm install node \
    && nvm use node \
    && npm i yarn -g"
RUN sh -c ". $HOME/.profile \
    && nvm install --lts \
    && nvm use --lts \
    && npm i yarn -g"
# https://stackoverflow.com/questions/37324519/node-sass-does-not-yet-support-your-current-environment-linux-64-bit-with-false
RUN sh -c ". $HOME/.profile \
    && nvm install lts/fermium \
    && nvm use lts/fermium \
    && npm i yarn -g"
RUN echo 'export PATH=$HOME/.yarn/bin:$PATH' >> $HOME/.profile

# https://www.rust-lang.org/tools/install
RUN curl --proto '=https' --tlsv1.2 -sSf https://sh.rustup.rs | sh -s -- -y
# https://doc.rust-lang.org/nightly/rustc/platform-support.html
RUN zsh -c "source $HOME/.cargo/env \
    && rustup component add rust-analyzer \
    && rustup target add armv7-unknown-linux-gnueabihf \
    && rustup target add aarch64-unknown-linux-gnu"


RUN apt install -y libpq-dev libmysqlclient-dev libsqlite3-dev
RUN zsh -c "source $HOME/.zshrc \
    && cargo install diesel_cli \
    && cargo install --locked cargo-outdated \
    && cargo install mdbook"

ADD conan /opt/conan
RUN zsh -c "source $HOME/.zshrc && cd /opt/conan && ./install.sh amd64"
RUN zsh -c "source $HOME/.zshrc && cd /opt/conan && ./install.sh arm64"
RUN zsh -c "source $HOME/.zshrc && cd /opt/conan && ./install.sh armhf"

ENV GRPC_VERSION "v1.49.1"
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

# https://github.com/protocolbuffers/protobuf-javascript
ENV GRPC_JS_PLUGIN_VERSION "3.21.2"
RUN wget -P $HOME/downloads \
    https://github.com/protocolbuffers/protobuf-javascript/releases/download/v${GRPC_JS_PLUGIN_VERSION}/protobuf-javascript-${GRPC_JS_PLUGIN_VERSION}-linux-x86_64.tar.gz
RUN mkdir -p $HOME/build/protobuf-javascript \
    cd $HOME/build/protobuf-javascript \
    && tar xf $HOME/downloads/protobuf-javascript-${GRPC_JS_PLUGIN_VERSION}-linux-x86_64.tar.gz \
    && cp bin/protoc-gen-js $HOME/.local/bin/

# https://repo1.maven.org/maven2/io/grpc/protoc-gen-grpc-java/
ENV GRPC_JAVA_PLUGIN_VERSION "1.50.0"
RUN wget -q -O $HOME/.local/bin/grpc_java_plugin \
    https://repo1.maven.org/maven2/io/grpc/protoc-gen-grpc-java/${GRPC_JAVA_PLUGIN_VERSION}/protoc-gen-grpc-java-${GRPC_JAVA_PLUGIN_VERSION}-linux-x86_64.exe
RUN chmod +x $HOME/.local/bin/grpc_java_plugin

# https://github.com/grpc/grpc-web#code-generator-plugin
ENV GRPC_WEB_PLUGIN_VERSION "1.4.1"
RUN wget -q -O $HOME/.local/bin/protoc-gen-grpc-web \
    https://github.com/grpc/grpc-web/releases/download/${GRPC_WEB_PLUGIN_VERSION}/protoc-gen-grpc-web-${GRPC_WEB_PLUGIN_VERSION}-linux-x86_64
RUN chmod +x $HOME/.local/bin/protoc-gen-grpc-web

# https://opensearch.org/downloads.html#opensearch
ENV OPENSEARCH_VERSION "2.3.0"
RUN wget -q -P $HOME/downloads \
    https://artifacts.opensearch.org/releases/bundle/opensearch/${OPENSEARCH_VERSION}/opensearch-${OPENSEARCH_VERSION}-linux-x64.tar.gz
RUN tar xf $HOME/downloads/opensearch-${OPENSEARCH_VERSION}-linux-x64.tar.gz -C /opt

# https://min.io/download#/linux
RUN wget -q -O /usr/bin/minio \
    https://dl.min.io/server/minio/release/linux-amd64/minio
RUN chmod +x /usr/bin/minio

# https://opensearch.org/docs/latest/opensearch/install/tar/
RUN echo "network.host: 0.0.0.0" >> /opt/opensearch-${OPENSEARCH_VERSION}/config/opensearch.yml
RUN echo "discovery.type: single-node" >> /opt/opensearch-${OPENSEARCH_VERSION}/config/opensearch.yml
RUN echo "plugins.security.disabled: true" >> /opt/opensearch-${OPENSEARCH_VERSION}/config/opensearch.yml
RUN chown -R nobody /opt/opensearch-${OPENSEARCH_VERSION}

RUN mkdir -p /var/lib/minio/data
RUN chown -R nobody /var/lib/minio

RUN sed -i 's/127.0.0.1/0.0.0.0/g' /etc/mysql/mariadb.conf.d/50-server.cnf

RUN su - postgres -c "/usr/lib/postgresql/14/bin/initdb -D /var/lib/postgresql/data"
RUN echo "listen_addresses = '0.0.0.0'" >> /var/lib/postgresql/data/postgresql.conf

RUN cd /var/lib \
    && mkdir redis-s redis-1 redis-2 redis-3 redis-4 redis-5 redis-6 \
    && chown redis:redis redis-s redis-1 redis-2 redis-3 redis-4 redis-5 redis-6 \
    && chmod 750 redis-s redis-1 redis-2 redis-3 redis-4 redis-5 redis-6
ADD etc/redis/* /etc/redis/

RUN mkdir /run/php
RUN echo "<?php phpinfo(); ?>" > /var/www/html/info.php
RUN echo "daemon off;" >> /etc/nginx/nginx.conf
ADD etc/nginx.conf /etc/nginx/sites-enabled/default

ADD etc/envoy.yaml /etc/

ADD etc/supervisor /etc/supervisor


RUN echo "$(date -u +%4Y%m%d%H%M%S)" | tee /VERSION

VOLUME /workspace
WORKDIR /workspace

CMD ["/bin/zsh", "-l"]
