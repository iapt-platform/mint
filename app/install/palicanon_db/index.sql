--
-- 由SQLiteStudio v3.1.1 产生的文件 周一 8月 3 08:37:43 2020
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


-- 表：wordindex
DROP TABLE IF EXISTS wordindex;

CREATE TABLE wordindex (
    id      INTEGER PRIMARY KEY AUTOINCREMENT,
    word    TEXT    UNIQUE,
    word_en TEXT,
    count   INTEGER DEFAULT (0),
    normal  INTEGER DEFAULT (0),
    bold    INTEGER DEFAULT (0),
    is_base INTEGER DEFAULT (0),
    len     INTEGER DEFAULT (0),
    final   INTEGER DEFAULT (0) 
);


-- 索引：info
DROP INDEX IF EXISTS info;

CREATE INDEX info ON wordindex (
    word_en,
    len,
    final
);


-- 索引：pali
DROP INDEX IF EXISTS pali;

CREATE INDEX pali ON word (
    book,
    paragraph,
    wordindex,
    bold
);


-- 索引：wordid
DROP INDEX IF EXISTS wordid;

CREATE UNIQUE INDEX wordid ON wordindex (
    word
);


COMMIT TRANSACTION;
PRAGMA foreign_keys = on;

VACUUM;
