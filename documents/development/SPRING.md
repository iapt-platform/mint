# SPRING 开发环境

## ssh 登录

```bash
ssh -i YOUR_KEY YOUR_ID@YOUR_ID.spring.wikipali.org
```

## PHP 开发（以用户 xxx 为例）

- `~/www/htdocs/info.php` => `https://YOUR_ID.spring.wikipali.org/info.php`
- 日志文件在 `~/www/logs/`
- 其余参见[常见 PHP 设置](php/)

## 服务设置

- PostgreSql 连接 `psql -h 127.0.0.1 -U xxx xxx_mint`
- RabbitMQ 的 `virtual-host` 是`xxx-mint`

## 常用工具

- 文件传输[FileZilla](https://filezilla-project.org/download.php?type=client)
- 终端[Putty](https://www.chiark.greenend.org.uk/~sgtatham/putty/latest.html)

## VsCode 设置

- [Remote Development using SSH](https://code.visualstudio.com/docs/remote/ssh)
