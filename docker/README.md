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
  > netstat -ant | grep 'LISTEN'
  # connect to redis
  > redis-cli
  # connect to postgresql
  > psql -U postgres -h 127.0.0.1 -p 5432
  ```

  ![start](documents/start.png)

- For VSCode **Run in your local host**

```bash
sudo apt install yarnpkg golang-go
```
