--
-- 由SQLiteStudio v3.1.1 产生的文件 周六 8月 1 08:06:28 2020
--
-- 文本编码：UTF-8
--
PRAGMA foreign_keys = off;
BEGIN TRANSACTION;

-- 表：part

CREATE TABLE part (
    word   TEXT,
    weight INTEGER
);


-- 索引：word
DROP INDEX IF EXISTS word;

CREATE INDEX word ON part (
    word
);


COMMIT TRANSACTION;
PRAGMA foreign_keys = on;
