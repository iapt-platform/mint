--
-- 由SQLiteStudio v3.1.1 产生的文件 周五 7月 31 13:47:31 2020
--
-- 文本编码：UTF-8
--
PRAGMA foreign_keys = off;
BEGIN TRANSACTION;

-- 表：complaint


CREATE TABLE complaint (
    id       INTEGER PRIMARY KEY AUTOINCREMENT,
    file_id  INTEGER NOT NULL,
    cpl_user INTEGER NOT NULL,
    message  TEXT    NOT NULL,
    time     INTEGER NOT NULL,
    status   INTEGER DEFAULT (0) 
                     NOT NULL,
    feedback TEXT,
    fb_time  INTEGER,
    admin_id INTEGER
);


-- 表：fileindex


CREATE TABLE fileindex (
    id           CHAR (36),
    parent_id    CHAR (36),
    user_id      INTEGER,
    book         INTEGER   DEFAULT (0),
    paragraph    INTEGER   DEFAULT (0),
    file_name    TEXT      NOT NULL,
    title        TEXT,
    tag          TEXT,
    status       INTEGER   DEFAULT (1),
    create_time  INTEGER,
    modify_time  INTEGER,
    accese_time  INTEGER,
    file_size    INTEGER,
    share        INTEGER   DEFAULT (0),
    doc_info     TEXT,
    doc_block    TEXT,
    receive_time INTEGER
);


-- 表：power


CREATE TABLE power (
    id           CHAR (36) PRIMARY KEY,
    doc_id       CHAR (36),
    user         CHAR (36),
    power        INTEGER,
    status       INTEGER,
    create_time  INTEGER,
    modify_time  INTEGER,
    receive_time INTEGER
);


COMMIT TRANSACTION;
PRAGMA foreign_keys = on;
