FROM ubuntu:latest
LABEL maintainer="Jeremy Zheng"

ENV DEBIAN_FRONTEND noninteractive

RUN apt update
RUN apt -y upgrade

RUN apt install -y software-properties-common
ENV PYTHON_VERSION "3.12"
# https://launchpad.net/~deadsnakes/+archive/ubuntu/ppa
RUN add-apt-repository -y ppa:deadsnakes/ppa
RUN apt update
RUN apt -y install build-essential \
    imagemagick ffmpeg fonts-dejavu-extra texlive-full pandoc \
    fonts-arphic-ukai fonts-arphic-uming \
    fonts-noto-cjk fonts-noto-cjk-extra \
    fonts-arphic-bkai00mp fonts-arphic-bsmi00lp fonts-arphic-gbsn00lp fonts-arphic-gkai00mp \
    fonts-wqy-microhei fonts-wqy-zenhei \
    fonts-cns11643-kai fonts-cns11643-sung \
    fonts-moe-standard-kai fonts-moe-standard-song \
    fonts-ipafont-nonfree-jisx0208 \
    python${PYTHON_VERSION}-full python${PYTHON_VERSION}-dev \
    libpq5
RUN apt -y clean

RUN mkdir /opt/lily
# https://pip.pypa.io/en/stable/installation/
RUN bash -c "python${PYTHON_VERSION} -m venv $HOME/python3 \
    && . $HOME/python3/bin/activate \
    && pip install --upgrade pip \
    && pip install cmake \
        psycopg minio redis[hiredis] \
        pika msgpack matplotlib ebooklib \
        grpcio protobuf grpcio-health-checking \
        pandas openpyxl xlrd pyxlsb"
RUN echo 'source $HOME/python3/bin/activate' >> $HOME/.bashrc

COPY lily /opt/lily/
COPY config.toml /etc/lily.toml

RUN echo "$(date -u +%4Y%m%d%H%M%S)" | tee /VERSION

WORKDIR /opt/lily

CMD ["/bin/bash", "-l"]
