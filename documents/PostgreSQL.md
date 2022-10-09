# PostgreSQL 常用命令

用户名：www
密码：change-me
主机: 127.0.0.1
端口：5432
数据库：demo

- 导出数据库

  ```bash
  pg_dump -Z 9 -h 127.0.0.1 -p 5432 -U www demo > $HOME/tmp/demo-20221009.sql.gz
  ```

- 导入数据库

  ```bash
  gunzip demo-20221009.sql.gz
  psql -h 127.0.0.1 -p 5432 -U www demo < demo-20221009.sql
  ```
