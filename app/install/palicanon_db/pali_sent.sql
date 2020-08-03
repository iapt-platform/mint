--
-- 由SQLiteStudio v3.1.1 产生的文件 周一 8月 3 14:23:47 2020
--
-- 文本编码：UTF-8
--
PRAGMA foreign_keys = off;
BEGIN TRANSACTION;

-- 表：pali_sent
DROP TABLE IF EXISTS pali_sent;

CREATE TABLE pali_sent (
    id        INTEGER PRIMARY KEY AUTOINCREMENT,
    book      INTEGER,
    paragraph INTEGER,
    [begin]   INTEGER,
    [end]     INTEGER,
    length    INTEGER,
    count     INTEGER,
    text      TEXT,
    real      TEXT,
    real_en   TEXT
);


COMMIT TRANSACTION;
PRAGMA foreign_keys = on;
