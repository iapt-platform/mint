--
-- 由SQLiteStudio v3.1.1 产生的文件 周五 2月 11 15:33:54 2022
--
-- 文本编码：UTF-8
--
PRAGMA foreign_keys = off;
BEGIN TRANSACTION;

-- 表：custom_book
CREATE TABLE custom_book 
(
    id INTEGER PRIMARY KEY AUTOINCREMENT, 
    book_id INTEGER NOT NULL, 
    title VARCHAR (256) NOT NULL, 
    owner VARCHAR (36) NOT NULL, 
    lang VARCHAR (8) NOT NULL, 
    status INTEGER NOT NULL DEFAULT (0), 
    modify_time INTEGER NOT NULL,
    create_time INTEGER NOT NULL
);

-- 表：custom_book_sentence
CREATE TABLE custom_book_sentence 
(
    id INTEGER PRIMARY KEY AUTOINCREMENT, 
    book INTEGER, 
    paragraph INTEGER, 
    "begin" INTEGER, 
    "end" INTEGER, 
    length INTEGER, 
    text TEXT, 
    lang VARCHAR (8), 
    owner VARCHAR (36) NOT NULL, 
    status INTEGER DEFAULT (0), 
    modify_time INTEGER NOT NULL, 
    create_time INTEGER NOT NULL
);

COMMIT TRANSACTION;
PRAGMA foreign_keys = on;
