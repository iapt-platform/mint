# SPRING 开发环境

## ssh 登录

```bash
ssh -i YOUR_KEY YOUR_ID@YOUR_ID.spring.wikipali.org
```

## PHP 开发（以用户 xxx 为例）

- `~/www/htdocs/info.php` => `https://YOUR_ID.spring.wikipali.org/info.php` (**如果您的 id 里面有'\_'，需要替换成'-'**)
- 日志文件在 `~/www/logs/`
- 其余参见[常见 PHP 设置](php/)

## 服务设置

- PostgreSql 连接 `psql -h 127.0.0.1 -U xxx xxx_mint`
- RabbitMQ 的 `virtual-host` 是`xxx-mint`
- redis 的 namespace`xxx://YOUR_KEY`

## VsCode 设置

- [Remote Development using SSH](https://code.visualstudio.com/docs/remote/ssh)
