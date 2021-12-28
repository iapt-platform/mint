--
-- 由SQLiteStudio v3.1.1 产生的文件 周五 7月 31 13:53:26 2020
--
-- 文本编码：UTF-8
--
PRAGMA foreign_keys = off;
BEGIN TRANSACTION;

-- 表：hit


CREATE TABLE hit (
    id  CHAR (36) PRIMARY KEY,
    hit INTEGER
);


COMMIT TRANSACTION;
PRAGMA foreign_keys = on;
