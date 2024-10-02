--
-- 由SQLiteStudio v3.1.1 产生的文件 周五 7月 31 13:46:57 2020
--
-- 文本编码：UTF-8
--
PRAGMA foreign_keys = off;
BEGIN TRANSACTION;

-- 表：term


CREATE TABLE term (
    id            INTEGER   PRIMARY KEY AUTOINCREMENT,
    guid          TEXT (36),
    word          TEXT,
    word_en       TEXT,
    meaning       TEXT,
    other_meaning TEXT,
    note          TEXT,
    tag           TEXT,
    create_time   INTEGER,
    owner         TEXT,
    hit           INTEGER   DEFAULT (0),
    language      CHAR (8),
    receive_time  INTEGER,
    modify_time   INTEGER
);


COMMIT TRANSACTION;
PRAGMA foreign_keys = on;
