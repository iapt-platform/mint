--
-- 由SQLiteStudio v3.1.1 产生的文件 周一 8月 3 08:29:56 2020
--
-- 文本编码：UTF-8
--
PRAGMA foreign_keys = off;
BEGIN TRANSACTION;

-- 表：bookword
DROP TABLE IF EXISTS bookword;

CREATE TABLE bookword (
    book      INT,
    wordindex INT,
    count     INTEGER
);


-- 索引：
DROP INDEX IF EXISTS "";

CREATE INDEX "" ON bookword (
    wordindex ASC
);


COMMIT TRANSACTION;
VACUUM;
PRAGMA foreign_keys = on;
