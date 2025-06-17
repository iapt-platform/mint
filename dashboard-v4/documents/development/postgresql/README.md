# PostgreSQL 常用命令

用户名：www
密码：change-me
主机: 127.0.0.1
端口：5432
数据库：demo

- 导出数据库

  ```bash
  pg_dump -Fc -O -a -Z 9 --dbname postgresql://USER:PASSWORD@HOST:PORT/DB_NAME -T migrations -f DB_NAME-data-$(date +"%Y%m%d%H%M%S").dump.gz
  ```

- 导入数据库

  ```bash
  pg_restore -h HOST -p PORT -U USER -d DB_NAME -1 FILE
  ```
