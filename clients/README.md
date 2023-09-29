# USAGE

## Setup

- for PHP

  ```bash
  sudo apt install php-grpc-all-dev
  ```

## Server

- Node server demo

  ![node-server](documents/node-server.png)

- PHP Server demo

  ![php-server](documents/php-server.png)

## Client

- PHP

  ```bash
  php -d extension=grpc.so -d max_execution_time=300 morus-demo.php
  php -d extension=grpc.so -d max_execution_time=300 lily-demo.php
  ```

  ![client](documents/client.png)
