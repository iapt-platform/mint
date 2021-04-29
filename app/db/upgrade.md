2021-04-28 20:13:22

在 sentence 添加 channel 字段

```
-- Queries executed on database sentence (C:/mysoft/Ampps/www/mint/tmp/user/sentence.db3)
-- Date and time of execution: 2021-04-28 20:13:22
PRAGMA foreign_keys = 0;
CREATE TABLE sqlitestudio_temp_table AS SELECT \* FROM sent_block;
DROP TABLE sent_block;
CREATE TABLE sent_block (id VARCHAR (36) PRIMARY KEY, parent_id VARCHAR (36), book INTEGER, paragraph INTEGER, channel VARCHAR (36), owner VARCHAR (36), lang VARCHAR (8), author VARCHAR (64), editor VARCHAR (64), tag VARCHAR (64), status INTEGER, modify_time INTEGER, receive_time INTEGER);
INSERT INTO sent_block (id, parent_id, book, paragraph, owner, lang, author, editor, tag, status, modify_time, receive_time) SELECT id, parent_id, book, paragraph, owner, lang, author, editor, tag, status, modify_time, receive_time FROM sqlitestudio_temp_table;
DROP TABLE sqlitestudio_temp_table;
PRAGMA foreign_keys = 1;
```
