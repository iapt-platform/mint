--
-- 由SQLiteStudio v3.1.1 产生的文件 周五 7月 31 13:45:55 2020
--
-- 文本编码：UTF-8
--
PRAGMA foreign_keys = off;
BEGIN TRANSACTION;

-- 表：course

CREATE TABLE course (
    id           CHAR (36) PRIMARY KEY,
    cover        CHAR (36),
    title        TEXT,
    subtitle     TEXT,
    summary      TEXT,
    teacher      TEXT,
    tag          TEXT,
    lang         CHAR (8),
    speech_lang  TEXT,
    status       INTEGER,
    lesson_num   INTEGER,
    creator      CHAR (36),
    create_time  INTEGER,
    modify_time  INTEGER,
    receive_time INTEGER
);


-- 表：course_lang


CREATE TABLE course_lang (
    id           CHAR (36) PRIMARY KEY,
    course_id    CHAR (36),
    title        TEXT,
    subtitle     TEXT,
    summary      TEXT,
    tag          TEXT,
    lang         CHAR (8),
    creator      CHAR (36),
    create_time  INTEGER,
    modify_time  INTEGER,
    receive_time INTEGER
);


-- 表：lesson


CREATE TABLE lesson (
    id           CHAR (36) PRIMARY KEY,
    course_id    CHAR (36),
    video        CHAR (36),
    link         TEXT,
    title        TEXT,
    subtitle     TEXT,
    creator      CHAR (36),
    tag          TEXT,
    summary      TEXT,
    status       INTEGER,
    cover        CHAR (36),
    teacher      TEXT,
    attachment   TEXT,
    lang         TEXT,
    speech_lang  TEXT,
    create_time  INTEGER,
    modify_time  INTEGER,
    receive_time INTEGER
);


-- 表：lesson_lang


CREATE TABLE lesson_lang (
    id           CHAR (36) PRIMARY KEY,
    lesson_id    CHAR (36),
    title        TEXT,
    subtitle     TEXT,
    tag          TEXT,
    summary      TEXT,
    lang         CHAR (8),
    modify_time  INTEGER,
    receive_time INTEGER,
    creator      CHAR (36) 
);


COMMIT TRANSACTION;
PRAGMA foreign_keys = on;
