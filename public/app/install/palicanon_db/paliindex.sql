--
-- 由SQLiteStudio v3.1.1 产生的文件 周一 8月 3 08:42:26 2020
--
-- 文本编码：UTF-8
--
PRAGMA foreign_keys = off;
BEGIN TRANSACTION;

-- 表：word
DROP TABLE IF EXISTS word;

CREATE TABLE word (
    id        INTEGER PRIMARY KEY AUTOINCREMENT,
    book      INTEGER DEFAULT (0),
    paragraph INTEGER,
    wordindex INTEGER,
    bold      INTEGER
);


-- 索引：pali
DROP INDEX IF EXISTS pali;

CREATE INDEX pali ON word (
    book ASC,
    paragraph ASC,
    wordindex ASC,
    bold ASC
);


COMMIT TRANSACTION;
VACUUM;
PRAGMA foreign_keys = on;
