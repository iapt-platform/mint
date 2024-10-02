--
-- 由SQLiteStudio v3.1.1 产生的文件 周五 7月 31 13:49:22 2020
--
-- 文本编码：UTF-8
--
PRAGMA foreign_keys = off;
BEGIN TRANSACTION;

-- 表：media


CREATE TABLE media (
    id           CHAR (36) PRIMARY KEY,
    type         INTEGER,
    link         TEXT,
    title        TEXT,
    summary      TEXT,
    anchor       TEXT,
    creator      CHAR (36),
    create_time  INTEGER,
    modify_time  INTEGER,
    receive_time INTEGER
);


COMMIT TRANSACTION;
PRAGMA foreign_keys = on;
