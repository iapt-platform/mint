# LILY

## Setup

```bash
$ sudo apt install python3-full imagemagick ffmpeg fonts-dejavu-extra texlive-full
$ python3 -m venv $HOME/local/python3
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
