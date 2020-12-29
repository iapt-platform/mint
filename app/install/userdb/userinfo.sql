--
-- 由SQLiteStudio v3.1.1 产生的文件 周五 7月 31 13:55:14 2020
--
-- 文本编码：UTF-8
--
PRAGMA foreign_keys = off;
BEGIN TRANSACTION;

-- 表：user

CREATE TABLE user (
    id           INTEGER    PRIMARY KEY AUTOINCREMENT,
    userid       TEXT       UNIQUE,
    path         CHAR (36),
    username     TEXT (64)  NOT NULL,
    password     TEXT       NOT NULL,
    nickname     TEXT (64)  NOT NULL,
    email        TEXT (256),
    create_time  INTEGER,
    modify_time  INTEGER,
    receive_time INTEGER
);


COMMIT TRANSACTION;
PRAGMA foreign_keys = on;
