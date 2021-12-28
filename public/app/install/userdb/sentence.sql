--
-- 由SQLiteStudio v3.1.1 产生的文件 周五 7月 31 13:52:16 2020
--
-- 文本编码：UTF-8
--
PRAGMA foreign_keys = off;
BEGIN TRANSACTION;

-- 表：sent_block


CREATE TABLE sent_block (
    id           CHAR (36),
    parent_id    CHAR (36),
    book         INTEGER,
    paragraph    INTEGER,
    owner        CHAR (36),
    lang         CHAR (8),
    author       CHAR (50),
    editor       TEXT,
    tag          TEXT,
    status       INTEGER,
    modify_time  INTEGER,
    receive_time INTEGER
);


-- 表：sentence


CREATE TABLE sentence (
    id           CHAR (36) PRIMARY KEY,
    block_id     CHAR (36) DEFAULT (0),
    book         INTEGER   NOT NULL,
    paragraph    INTEGER   NOT NULL,
    [begin]      INTEGER   NOT NULL,
    [end]        INTEGER   NOT NULL,
    tag          CHAR (40),
    author       CHAR (40),
    editor       INTEGER,
    text         TEXT,
    language     CHAR (8),
    ver          INTEGER,
    status       INTEGER,
    modify_time  INTEGER   NOT NULL,
    receive_time INTEGER
);


COMMIT TRANSACTION;
PRAGMA foreign_keys = on;
