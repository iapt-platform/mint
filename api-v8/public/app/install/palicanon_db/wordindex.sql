--
-- 由SQLiteStudio v3.1.1 产生的文件 周一 8月 3 08:34:55 2020
--
-- 文本编码：UTF-8
--
PRAGMA foreign_keys = off;
BEGIN TRANSACTION;

-- 表：wordindex
DROP TABLE IF EXISTS wordindex;

CREATE TABLE wordindex (
    id      INT,
    word    TEXT PRIMARY KEY ASC
                 UNIQUE,
    word_en TEXT,
    count   INT,
    normal  INT,
    bold    INT,
    is_base INT,
    len     INT,
    final   INT
);


-- 索引：
DROP INDEX IF EXISTS "";

CREATE INDEX "" ON wordindex (
    word_en ASC
);


COMMIT TRANSACTION;
VACUUM;
PRAGMA foreign_keys = on;
