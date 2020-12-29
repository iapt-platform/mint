--
-- 由SQLiteStudio v3.1.1 产生的文件 周五 7月 31 13:44:59 2020
--
-- 文本编码：UTF-8
--
PRAGMA foreign_keys = off;
BEGIN TRANSACTION;

-- 表：comments

CREATE TABLE comments (
    id        INTEGER PRIMARY KEY AUTOINCREMENT,
    album     INTEGER NOT NULL,
    book      INTEGER NOT NULL,
    paragraph INTEGER NOT NULL,
    text      TEXT,
    user      INTEGER NOT NULL,
    reputable INTEGER DEFAULT (0),
    time      INTEGER
);


COMMIT TRANSACTION;
PRAGMA foreign_keys = on;
