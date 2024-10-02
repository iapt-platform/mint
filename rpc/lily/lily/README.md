# LILY

## Setup

- Install [Python3.11+](https://launchpad.net/~deadsnakes/+archive/ubuntu/ppa)

  ```bash
  sudo add-apt-repository ppa:deadsnakes/ppa
  sudo apt update
  sudo apt install python3.12-full python3.12-dev
  ```

- Install libraries

  ```bash
  $ sudo apt install imagemagick ffmpeg fonts-dejavu-extra texlive-full
  $ python3.12 -m venv $HOME/local/python3
  $ source $HOME/local/python3/bin/activate
  $ pip install psycopg minio redis[hiredis] \
    pika msgpack matplotlib ebooklib \
    grpcio protobuf grpcio-health-checking \
    pandas openpyxl xlrd pyxlsb
  ```

## Start

- run `python lily -d -c config.toml`

## Documents

- [https://matplotlib.org/stable/gallery/index.html](Matplotlib)
- [https://graphviz.org/](Graphviz)
- [EbookLib](https://github.com/aerkalov/ebooklib)
- [Excel files](https://pandas.pydata.org/docs/user_guide/io.html#excel-files)
- [Data types used by Excel](https://learn.microsoft.com/en-us/office/client-developer/excel/data-types-used-by-excel)
