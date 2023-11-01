FROM ubuntu:latest
LABEL maintainer="Jeremy Zheng"

ENV DEBIAN_FRONTEND noninteractive

RUN apt update
RUN apt -y upgrade
RUN apt -y install build-essential \
    imagemagick ffmpeg fonts-dejavu-extra texlive-full pandoc \
    fonts-arphic-ukai fonts-arphic-uming \
    fonts-noto-cjk fonts-noto-cjk-extra \
    fonts-arphic-bkai00mp fonts-arphic-bsmi00lp fonts-arphic-gbsn00lp fonts-arphic-gkai00mp \
    fonts-wqy-microhei fonts-wqy-zenhei \
    fonts-cns11643-kai fonts-cns11643-sung \
    fonts-moe-standard-kai fonts-moe-standard-song \
    fonts-ipafont-nonfree-jisx0208 \
    python3-full python3-dev 

# https://getcomposer.org/download/
RUN wget https://raw.githubusercontent.com/composer/getcomposer.org/76a7060ccb93902cd7576b67264ad91c8a2700e2/web/installer -O - -q | php -- --quiet --install-dir=/usr/local/bin --filename=composer

RUN useradd -s /bin/bash -m deploy
RUN passwd -l deploy
RUN echo 'deploy ALL=(ALL:ALL) NOPASSWD: ALL' > /etc/sudoers.d/101-deploy
RUN mkdir /opt/lily
RUN chown deploy:deploy /opt/lily
USER deploy

# https://pip.pypa.io/en/stable/installation/
RUN bash -c "python3 -m venv $HOME/python3 \
    && . $HOME/python3/bin/activate \
    && pip install --upgrade pip \
    && pip install cmake \
        psycopg minio redis[hiredis] \
        pika msgpack matplotlib ebooklib \
        grpcio protobuf grpcio-health-checking \
        pandas openpyxl xlrd pyxlsb"
RUN echo 'source $HOME/python3/bin/activate' >> $HOME/.bashrc

COPY lily /opt/lily/

RUN echo "$(date -u +%4Y%m%d%H%M%S)" | sudo tee /VERSION

WORKDIR /opt/morus

CMD ["/bin/bash"]
