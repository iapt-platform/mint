--
-- 由SQLiteStudio v3.1.1 产生的文件 周六 8月 8 07:41:35 2020
--
-- 文本编码：UTF-8
--
PRAGMA foreign_keys = off;
BEGIN TRANSACTION;

-- 表：album
DROP TABLE IF EXISTS album;

CREATE TABLE album (
    id          INTEGER   PRIMARY KEY ASC AUTOINCREMENT,
    book        INTEGER,
    guid        CHAR (40),
    type        INTEGER,
    title       TEXT,
    file        TEXT,
    cover       TEXT,
    language    INTEGER,
    author      TEXT,
    tag         TEXT,
    summary     TEXT,
    create_time INTEGER,
    update_time INTEGER,
    version     INTEGER,
    edition     TEXT (20),
    owner       INTEGER   DEFAULT (0) 
);


-- 表：album_ebook
DROP TABLE IF EXISTS album_ebook;

CREATE TABLE album_ebook (
    id          INTEGER PRIMARY KEY AUTOINCREMENT
                        UNIQUE
                        NOT NULL,
    album       INTEGER NOT NULL,
    file_format INTEGER NOT NULL,
    file_size   INTEGER NOT NULL,
    file_name   TEXT    NOT NULL,
    time        INTEGER
);


-- 表：album_power
DROP TABLE IF EXISTS album_power;

CREATE TABLE album_power (
    id       INTEGER   PRIMARY KEY AUTOINCREMENT
                       NOT NULL
                       UNIQUE,
    album_id INTEGER   NOT NULL,
    user_id  INTEGER   NOT NULL,
    password TEXT (20),
    power    INTEGER
);


-- 表：author
DROP TABLE IF EXISTS author;

CREATE TABLE author (
    id      INTEGER    PRIMARY KEY ASC AUTOINCREMENT,
    name    TEXT (128),
    [group] INTEGER
);


-- 表：book
DROP TABLE IF EXISTS book;

CREATE TABLE book (
    id       INTEGER PRIMARY KEY AUTOINCREMENT,
    book_id  INTEGER,
    language INTEGER,
    title    TEXT,
    c1       TEXT,
    c2       TEXT
);


-- 表：file_format
DROP TABLE IF EXISTS file_format;

CREATE TABLE file_format (
    id     INTEGER PRIMARY KEY AUTOINCREMENT,
    format TEXT
);


-- 表：index
DROP TABLE IF EXISTS [index];

CREATE TABLE [index] (
    id          INTEGER PRIMARY KEY AUTOINCREMENT
                        NOT NULL,
    book        INTEGER,
    paragraph   INTEGER,
    title       TEXT,
    title_en    TEXT,
    level       INTEGER,
    type        INTEGER,
    language    INTEGER,
    author      TEXT,
    editor      INTEGER,
    share       INTEGER,
    edition     INTEGER NOT NULL
                        DEFAULT 1,
    hit         INTEGER DEFAULT 0
                        NOT NULL,
    album       INTEGER,
    tag         TEXT,
    summary     TEXT,
    create_time INTEGER,
    update_time INTEGER DEFAULT (1) 
);


-- 表：language
DROP TABLE IF EXISTS language;

CREATE TABLE language (
    id   INTEGER  PRIMARY KEY
                  UNIQUE
                  NOT NULL,
    code TEXT (2) NOT NULL,
    note TEXT
);


-- 表：media_type
DROP TABLE IF EXISTS media_type;

CREATE TABLE media_type (
    id   INTEGER PRIMARY KEY
                 UNIQUE
                 NOT NULL,
    type TEXT    UNIQUE
                 NOT NULL,
    note TEXT
);


-- 表：paragraph_info
DROP TABLE IF EXISTS paragraph_info;

CREATE TABLE paragraph_info (
    id        INTEGER PRIMARY KEY AUTOINCREMENT,
    book      INTEGER,
    paragraph INTEGER,
    length    INTEGER,
    prev      INTEGER,
    next      INTEGER,
    parent    INTEGER
);


-- 表：tag
DROP TABLE IF EXISTS tag;

CREATE TABLE tag (
    id       INTEGER PRIMARY KEY AUTOINCREMENT,
    pali     TEXT,
    tag      TEXT,
    language INTEGER,
    ref      INTEGER DEFAULT (0) 
);


-- 索引：search
DROP INDEX IF EXISTS search;

CREATE INDEX search ON [index] (
    "book",
    paragraph,
    "language",
    "title",
    "author",
    "editor",
    "edition",
    hit
);


COMMIT TRANSACTION;
PRAGMA foreign_keys = on;
