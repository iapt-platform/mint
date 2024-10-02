--
-- 由SQLiteStudio v3.1.1 产生的文件 周三 8月 5 15:10:22 2020
--
-- 文本编码：UTF-8
--
PRAGMA foreign_keys = off;
BEGIN TRANSACTION;

-- 表：dict
DROP TABLE IF EXISTS dict;

CREATE TABLE dict (
    id       INTEGER PRIMARY KEY AUTOINCREMENT
                     UNIQUE,
    language TEXT,
    dict_id  INTEGER,
    eword    TEXT,
    word     TEXT,
    paliword TEXT,
    mean     TEXT,
    length   INTEGER,
    status   INTEGER DEFAULT (1) 
);


-- 表：info
DROP TABLE IF EXISTS info;

CREATE TABLE info (
    language  TEXT,
    id        INTEGER PRIMARY KEY
                      NOT NULL,
    shortname TEXT,
    name      TEXT
);


-- 索引：eword
DROP INDEX IF EXISTS eword;

CREATE INDEX eword ON dict (
    "eword" ASC
);


-- 索引：len
DROP INDEX IF EXISTS len;

CREATE INDEX len ON dict (
    "length" ASC
);


-- 索引：word
DROP INDEX IF EXISTS word;

CREATE INDEX word ON dict (
    "word" ASC
);


-- 视图：語法彙編
DROP VIEW IF EXISTS 語法彙編;
CREATE VIEW 語法彙編 AS
    SELECT *
      FROM dict
     WHERE dict_id = 32;


COMMIT TRANSACTION;
VACUUM;
PRAGMA foreign_keys = on;
