# LILY

## Setup

- Install [Python3.11+](https://launchpad.net/~deadsnakes/+archive/ubuntu/ppa)

  ```bash
  $ sudo add-apt-repository ppa:deadsnakes/ppa
  $ sudo apt update
  $ sudo apt install python3.12-full python3.12-dev
  ```

- Install libraries

  ```bash
  $ sudo apt install imagemagick ffmpeg fonts-dejavu-extra texlive-full
  $ python3.12 -m venv $HOME/local/python3
  $ source $HOME/local/python3/bin/activate
  $ pip install psycopg pika matplotlib ebooklib \
    grpcio protobuf grpcio-health-checking \
    pandas openpyxl xlrd pyxlsb
  ```

## Start

- create config.toml

  ```toml
  [rpc]
  port = 9999
  workers = 8
  ```

- run `python lily -d`

## Documents

- [https://matplotlib.org/stable/gallery/index.html](Matplotlib)
- [https://graphviz.org/](Graphviz)
- [EbookLib](https://github.com/aerkalov/ebooklib)
- [Excel files](https://pandas.pydata.org/docs/user_guide/io.html#excel-files)
