# PostgreSQL 常用命令

用户名：www
密码：change-me
主机: 127.0.0.1
端口：5432
数据库：demo

- 导出数据库

  ```bash
  pg_dump -O -s -h 127.0.0.1 -p 5432 -U www demo > demo-schema-20221009.sql
  pg_dump -Fc -O -a -h 127.0.0.1 -p 5432 -U www demo | gzip -9 > demo-data-20221009.dump.gz
  ```

- 导入数据库

  ```bash
  psql -h 127.0.0.1 -p 5432 -U www demo < demo-schema-20221009.sql
  gunzip demo-20221009.dump.gz
  pg_restore -Fc -h 127.0.0.1 -p 5432 -U www -d demo < demo-20221009.dump
  ```
