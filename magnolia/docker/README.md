# Usage

## Setup

```bash
# archlinux
sudo pacman -S buildkit docker-buildx
# ubuntu
sudo apt install docker-ce docker-ce-cli containerd.io docker-buildx-plugin docker-compose-plugin

sudo gpasswd -a xxx docker

docker image load -i xxx.tar
docker image ls --tree

```

## Documents

### Laravel-PHP Compatibility

| Laravel                                                     | PHP |
| ----------------------------------------------------------- | --- |
| [8](https://laravel.com/docs/10.x/releases#support-policy)  | 8.1 |
| [12](https://laravel.com/docs/12.x/releases#support-policy) | 8.4 |
