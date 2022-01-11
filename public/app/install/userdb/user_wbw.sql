--
-- 由SQLiteStudio v3.1.1 产生的文件 周五 7月 31 13:54:15 2020
--
-- 文本编码：UTF-8
--
PRAGMA foreign_keys = off;
BEGIN TRANSACTION;

-- 表：wbw


CREATE TABLE wbw (
    id           CHAR (36) PRIMARY KEY,
    block_id     CHAR (36),
    book         INTEGER,
    paragraph    INTEGER,
    wid          INTEGER,
    word         TEXT,
    data         TEXT,
    modify_time  INTEGER,
    receive_time INTEGER,
    status       INTEGER,
    owner        CHAR (36) 
);


-- 表：wbw_block


CREATE TABLE wbw_block (
    id           CHAR (36) PRIMARY KEY,
    parent_id    CHAR (36),
    owner        CHAR (36),
    book         INTEGER,
    paragraph    INTEGER,
    style        CHAR (16),
    lang         CHAR (8),
    status       INTEGER,
    modify_time  INTEGER,
    receive_time INTEGER
);


COMMIT TRANSACTION;
PRAGMA foreign_keys = on;
