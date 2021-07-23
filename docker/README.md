# Usage for Ubuntu 20.10 and newer

- Install podman: `sudo apt -y install podman runc buildah skopeo`.
- Setup `/etc/containers/registries.conf`.

  ```text
  [registries.search]
  registries = ['quay.io', 'docker.io']
  ```

- Work with podman image

  ```bash
  # clear outdated images
  podman rmi -a -f
  # uncompress image files
  cat palm.tar.xz.a* | tar xj
  # import new podman image
  podman load -q -i mint-TIMESTAMP.tar  
  ```

- Enjoy it!
  
  ```bash
  # for the first time start
  ./docker/ubuntu/first.sh
  # fot the next time start
  ./docker/ubuntu/next.sh
  
  # start servers
  > sudo supervisord -c /etc/supervisor/supervisord.conf

  # enable rabbitmq Management Plugin 
  > sudo rabbitmq-plugins enable rabbitmq_management

  # enable redis clusters
  > ./docker/redis.sh
  ```

  ![start](documents/start.png)

  - RabbitMQ: `http://localhost:15672`, user `guest`, password `guest`
  - Redis cluster ports `6371~6376`
  - Minio server: `http://localhost:9000` user `admin`, password `12345678`
  - PostgreSql: `psql -U postgres -h 127.0.0.1 -p 5432`
  - ElasticSearch: `curl 127.0.0.1:9200/`

- For VSCode **Run in your local host**

```bash
sudo apt install yarnpkg golang-go
```
