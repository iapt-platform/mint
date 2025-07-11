FROM ubuntu:latest
LABEL maintainer="Kassapa"

ENV DEBIAN_FRONTEND noninteractive
# https://launchpad.net/~deadsnakes/+archive/ubuntu/ppa
ARG PYTHON_VERSION=3.13

RUN apt update
RUN apt -y install lsb-release apt-utils \
    debian-keyring debian-archive-keyring apt-transport-https software-properties-common curl wget gnupg
RUN add-apt-repository -y ppa:deadsnakes/ppa
RUN apt -y upgrade
RUN apt -y install git vim sudo locales locales-all tzdata build-essential \
    python${PYTHON_VERSION}-dev python${PYTHON_VERSION}-venv
RUN apt -y autoremove
RUN apt -y clean

RUN echo "en_US.UTF-8 UTF-8" > /etc/locale.gen
RUN locale-gen
RUN update-locale LANG=en_US.UTF-8
RUN update-alternatives --set editor /usr/bin/vim.basic

# RUN useradd -s /bin/bash -m deploy
# RUN passwd -l deploy
# RUN echo 'deploy ALL=(ALL:ALL) NOPASSWD: ALL' > /etc/sudoers.d/101-deploy
# USER deploy
# ADD --chown=deploy https://bootstrap.pypa.io/get-pip.py /opt/

RUN python${PYTHON_VERSION} -m venv $HOME/python3
RUN echo 'source $HOME/python3/bin/activate' >> $HOME/.bashrc

# https://pip.pypa.io/en/stable/installation/#get-pip-py
ADD https://bootstrap.pypa.io/get-pip.py /opt/
RUN bash -c ". $HOME/python3/bin/activate && python3 /opt/get-pip.py"
RUN bash -c ". $HOME/python3/bin/activate && pip install pika requests redis[hiredis] openai"

# COPY --chown=deploy README.md pyproject.toml /opt/ai-translate/
# COPY --chown=deploy ai_translate /opt/ai-translate/ai_translate
# RUN bash -c "source $HOME/python3/bin/activate && python3 -m pip install -e /opt/ai-translate"

RUN echo "$(date -u +%4Y%m%d%H%M%S)" | sudo tee /VERSION

VOLUME /srv
WORKDIR /srv

CMD ["/bin/bash", "-l"]
