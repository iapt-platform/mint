--
-- 由SQLiteStudio v3.1.1 产生的文件 周五 2月 12 16:26:34 2021
--
-- 文本编码：UTF-8
--
PRAGMA foreign_keys = off;
BEGIN TRANSACTION;

-- 表：course
CREATE TABLE course (id INTEGER PRIMARY KEY AUTOINCREMENT, book INTEGER, para INTEGER, lesson_id CHAR (36), start INTEGER, "end" INTEGER);

-- 表：nissaya
CREATE TABLE nissaya (id INTEGER PRIMARY KEY AUTOINCREMENT, book INTEGER, para INTEGER, lang CHAR (8), media INTEGER);

-- 表：progress
CREATE TABLE progress (id INTEGER PRIMARY KEY AUTOINCREMENT, book INTEGER, para INTEGER, lang CHAR (8), all_strlen INTEGER, public_strlen INTEGER);

-- 表：progress_chapter
CREATE TABLE progress_chapter (id INTEGER PRIMARY KEY AUTOINCREMENT, book INTEGER, para INTEGER, lang CHAR (8), all_trans REAL, public REAL);

COMMIT TRANSACTION;
PRAGMA foreign_keys = on;
