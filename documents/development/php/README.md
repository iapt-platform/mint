# PHP 开发环境

## Nginx 调整

- 修改 `/etc/nginx/sites-enabled/default`, root 改成`/workspace/YOUR_PROJECT`

## 常见 PHP 设置

- PHP-WEB 文件写入权限不足

```bash
# for folder
chmod 777 FOLDER_NAME
# for file
chmod 666 FILE_NAME
```
