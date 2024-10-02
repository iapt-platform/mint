--
-- 由SQLiteStudio v3.1.1 产生的文件 周二 7月 28 18:49:04 2020
--
-- 文本编码：UTF-8
--
PRAGMA foreign_keys = off;
BEGIN TRANSACTION;

-- 表：dict
DROP TABLE IF EXISTS dict;

CREATE TABLE dict (
    id         INTEGER,
    pali       TEXT     NOT NULL,
    type       TEXT,
    gramma     TEXT,
    parent     TEXT,
    mean       TEXT,
    note       TEXT,
    parts      TEXT,
    partmean   TEXT,
    status     INTEGER  DEFAULT (1),
    confidence INTEGER  DEFAULT (100),
    len        INTEGER,
    dict_name  TEXT,
    lang       CHAR (8) DEFAULT en
);


-- 索引：pali
DROP INDEX IF EXISTS pali;

CREATE INDEX pali ON dict (
    "pali" ASC
);


COMMIT TRANSACTION;
PRAGMA foreign_keys = on;
