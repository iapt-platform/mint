FROM ubuntu:latest
LABEL maintainer="Jeremy Zheng"

ENV DEBIAN_FRONTEND noninteractive

RUN apt update
RUN apt -y upgrade
RUN apt -y install vim locales locales-all tzdata
RUN apt -y autoremove
RUN apt -y clean

RUN echo "en_US.UTF-8 UTF-8" > /etc/locale.gen
RUN locale-gen
RUN update-locale LANG=en_US.UTF-8
RUN update-alternatives --set editor /usr/bin/vim.basic

# https://opensearch.org/downloads.html#opensearch
ENV OPENSEARCH_VERSION "2.17.0"
ENV OPENSEARCH_JAVA_HOME "/opt/opensearch-${OPENSEARCH_VERSION}/jdk"
RUN wget -q -P $HOME/downloads \
    https://artifacts.opensearch.org/releases/bundle/opensearch/${OPENSEARCH_VERSION}/opensearch-${OPENSEARCH_VERSION}-linux-arm64.tar.gz
RUN cd /opt && tar xf $HOME/downloads/opensearch-${OPENSEARCH_VERSION}-linux-x64.tar.gz 
RUN echo "network.host: 0.0.0.0" >> /opt/opensearch-${OPENSEARCH_VERSION}/config/opensearch.yml
RUN echo "discovery.type: single-node" >> /opt/opensearch-${OPENSEARCH_VERSION}/config/opensearch.yml 
RUN echo "plugins.security.disabled: true" >> /opt/opensearch-${OPENSEARCH_VERSION}/config/opensearch.yml

RUN echo "$(date -u +%4Y%m%d%H%M%S)" | tee /VERSION

WORKDIR /opt/opensearch-${OPENSEARCH_VERSION}

CMD ["/opt/opensearch-${OPENSEARCH_VERSION}/bin/opensearch"]
