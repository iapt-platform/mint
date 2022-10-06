# USAGE

- add to `/etc/sysctl.d/60-my.conf` and then `reboot` or `sysctl -p`

  ```text
  vm.overcommit_memory = 1
  vm.max_map_count = 262144
  ```

- start container [dashboard](http://localhost:10001)

  ```bash
  $ cd ~/workspace
  $ ./saturn-xiv/palm/docker/jammy/start.sh
  > supervisord -c /etc/supervisor/supervisord.conf
  # init redis cluster
  > /etc/redis/clusters-init.sh
  ```

- PostgreSql

  ```bash
  psql -h 127.0.0.1 -p 5432 -U postgres
  ```

- MySql

  ```bash
  # reset root's password
  mysql_secure_installation
  ```

- Redis

  ```bash
  # connect to redis node-1
  redis-cli -c -h 127.0.0.1 -p 16371
  ```

- Minio [dashboard](http://localhost:9001) (`admin:12345678`)

- RabbitMQ [dashboard](http://localhost:15672) (`guest:guest`)

  ```bash
  # enable rabbitmq management plugin
  rabbitmq-plugins enable rabbitmq_management
  ```

- Php [info.php](http://localhost:8080/info.php)

- OpenSearch

  ```bash
  # show info
  curl -X GET http://localhost:9200
  curl -X GET http://localhost:9200/_cat/plugins?v
  ```
