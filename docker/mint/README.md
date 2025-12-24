# USAGE

## Usage

- Building

```bash
cd ~/workspace/mint/docker
./build.sh PHP_VERSION
```

- Load image

  ```bash
  docker load -i mint-php8.4-x86_64.tar
  ```

- Laravel & PHP compatibility

| Laravel                                      | PHP       | Security Fixes Until |
| -------------------------------------------- | --------- | -------------------- |
| [8](https://laravel.com/docs/8.x/releases)   | 7.3 - 8.1 | Jan, 2023            |
| [12](https://laravel.com/docs/12.x/releases) | 8.2 - 8.5 | Feb, 2027            |

## Documents

- [Laravel Release Notes](https://laravel.com/docs/releases)
