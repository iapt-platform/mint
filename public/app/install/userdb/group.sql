--
-- 由SQLiteStudio v3.1.1 产生的文件 周五 7月 31 13:48:23 2020
--
-- 文本编码：UTF-8
--
PRAGMA foreign_keys = off;
BEGIN TRANSACTION;

-- 表：group_file


CREATE TABLE group_file (
    id          INTEGER   PRIMARY KEY AUTOINCREMENT,
    group_id    INTEGER   NOT NULL
                          DEFAULT (0),
    file_name   TEXT      NOT NULL,
    file_size   INTEGER   NOT NULL,
    file_title  TEXT      NOT NULL,
    project     CHAR (32),
    create_time INTEGER   NOT NULL,
    modify_time INTEGER   NOT NULL,
    access_time INTEGER   NOT NULL,
    stage       INTEGER   DEFAULT (0) 
);


-- 表：group_file_contribution


CREATE TABLE group_file_contribution (
    id                INTEGER PRIMARY KEY AUTOINCREMENT,
    file_id           INTEGER NOT NULL,
    user_id           INTEGER NOT NULL,
    group_id          INTEGER DEFAULT (0) 
                              NOT NULL,
    wbw_create_mean   INTEGER NOT NULL
                              DEFAULT (0),
    wbw_create_factor INTEGER DEFAULT (0),
    wbw_submit_mean   INTEGER DEFAULT (0),
    wbw_submit_factor INTEGER DEFAULT (0),
    wbw_ref_mean      INTEGER DEFAULT (0),
    wbw_ref_factor    INTEGER DEFAULT (0),
    tran_create       INTEGER DEFAULT (0) 
);


-- 表：group_file_power


CREATE TABLE group_file_power (
    id       INTEGER PRIMARY KEY AUTOINCREMENT,
    file_id  INTEGER NOT NULL,
    user_id  INTEGER NOT NULL,
    stage    INTEGER NOT NULL
                     DEFAULT (0),
    power    INTEGER NOT NULL
                     DEFAULT (0),
    group_id INTEGER DEFAULT (0) 
                     NOT NULL
);


-- 表：group_info


CREATE TABLE group_info (
    id            INTEGER   PRIMARY KEY AUTOINCREMENT,
    name          CHAR (32) NOT NULL,
    create_time   INTEGER   NOT NULL,
    folder        CHAR (40) NOT NULL,
    file_number   INTEGER   DEFAULT (0) 
                            NOT NULL,
    member_number INTEGER   DEFAULT (1) 
                            NOT NULL
);


-- 表：group_member


CREATE TABLE group_member (
    id         INTEGER   PRIMARY KEY AUTOINCREMENT,
    user_id    INTEGER   NOT NULL,
    group_id   INTEGER   NOT NULL,
    power      INTEGER   NOT NULL
                         DEFAULT (0),
    group_name CHAR (32) 
);


-- 表：group_power


CREATE TABLE group_power (
    id       INTEGER   PRIMARY KEY AUTOINCREMENT,
    group_id INTEGER   NOT NULL,
    position CHAR (32) NOT NULL,
    power    TEXT      NOT NULL,
    power_id INTEGER   DEFAULT (0) 
);


-- 表：group_process


CREATE TABLE group_process (
    id       INTEGER   PRIMARY KEY AUTOINCREMENT,
    group_id INTEGER   NOT NULL,
    stage    INTEGER   NOT NULL,
    name     CHAR (32) NOT NULL
);


COMMIT TRANSACTION;
PRAGMA foreign_keys = on;
