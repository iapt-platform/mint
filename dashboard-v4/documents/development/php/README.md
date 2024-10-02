# PHP 开发环境

## Nginx 调整

- 修改 `/etc/nginx/sites-enabled/default`, root 改成`/workspace/YOUR_PROJECT`

## 常见 PHP 设置

- PHP-WEB 文件写入权限不足

```bash
XXX/mint/scrips/spring/laravel.sh
```

## Php-Nginx for local container

- 打补丁 `palm-jammy-20220929140150`

  ```bash
  apt install php-sqlite3
  ```

- 权限设置

  ```bash
  # 设置日志目录
  cd /workspace/iapt-platform
  mkdir tmp
  chmod 777 tmp
  cd /workspace/iapt-platform/mint-laravel
  # 修复文件权限
  ../mint/scripts/spring/laravel.sh
  ```

- 用 [laravel.conf](laravel.conf) 替换 `/etc/nginx/sites-enabled/default`
