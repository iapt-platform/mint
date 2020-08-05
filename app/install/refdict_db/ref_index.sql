--
-- 由SQLiteStudio v3.1.1 产生的文件 周三 8月 5 15:11:23 2020
--
-- 文本编码：UTF-8
--
PRAGMA foreign_keys = off;
BEGIN TRANSACTION;

-- 表：dict
DROP TABLE IF EXISTS dict;

CREATE TABLE dict (
    id     INTEGER,
    eword  TEXT,
    word   TEXT,
    length INTEGER,
    count  INTEGER
);


-- 索引：search
DROP INDEX IF EXISTS search;

CREATE INDEX search ON dict (
    eword,
    word
);


COMMIT TRANSACTION;
VACUUM;
PRAGMA foreign_keys = on;
