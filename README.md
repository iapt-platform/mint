# International Academy Of Pali Tipitaka(国际巴利三藏学院)

这是一个开放的基于语料库的巴利语学习和翻译平台。

## Usage

## Development

```bash
$ sudo apt install -y git crun podman buildah fuse-overlayfs
$ git clone https://github.com/iapt-platform/mint.git ~/workspace/iapt-platform/mint
$ cd ~/workspace/iapt-platform/mint/
# Load the mint-spring image
$ podman load -i tmp/mint-spring-TIMESTAMP.tar.xz
# Start postgresql/redis/rabbitmq... services
$ ./docker/spring/start.sh services
# Start a backend server
$ ./docker/spring/start.sh backend 8080 # http://localhost:8080
# Start a frontend server
$ ./docker/spring/start.sh frontend 3000 # http://localhost:3000
```

## Documents

- [Podman Installation Instructions](https://podman.io/docs/installation)
- [Download Visual Studio Code](https://code.visualstudio.com/download)
- [Remote Development using SSH](https://code.visualstudio.com/docs/remote/ssh)
