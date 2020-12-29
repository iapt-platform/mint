--
-- 由SQLiteStudio v3.1.1 产生的文件 周五 7月 31 13:55:56 2020
--
-- 文本编码：UTF-8
--
PRAGMA foreign_keys = off;
BEGIN TRANSACTION;

-- 表：dict


CREATE TABLE dict (
    id          INTEGER PRIMARY KEY AUTOINCREMENT
                        NOT NULL,
    guid        TEXT,
    pali        TEXT    NOT NULL,
    type        TEXT,
    gramma      TEXT,
    parent      TEXT,
    parent_id   TEXT,
    mean        TEXT,
    note        TEXT,
    factors     TEXT,
    factormean  TEXT,
    part_id     TEXT,
    status      INTEGER,
    dict_name   TEXT,
    language    TEXT,
    time        INTEGER,
    confidence  INTEGER DEFAULT (100),
    creator     INTEGER,
    ref_counter INTEGER DEFAULT (1) 
);


-- 表：user_index


CREATE TABLE user_index (
    id          INTEGER PRIMARY KEY AUTOINCREMENT
                        UNIQUE,
    word_index  INTEGER NOT NULL,
    user_id     INTEGER NOT NULL,
    create_time INTEGER DEFAULT (1) 
);


COMMIT TRANSACTION;
PRAGMA foreign_keys = on;
