--
-- 由SQLiteStudio v3.1.1 产生的文件 周四 2月 11 17:30:07 2021
--
-- 文本编码：UTF-8
--
PRAGMA foreign_keys = off;
BEGIN TRANSACTION;

-- 表：course
DROP TABLE IF EXISTS course;
CREATE TABLE course (id INTEGER PRIMARY KEY AUTOINCREMENT, book INTEGER, para INTEGER, lesson_id CHAR (36), start INTEGER, "end" INTEGER);

-- 表：nissaya
DROP TABLE IF EXISTS nissaya;
CREATE TABLE nissaya (id INTEGER PRIMARY KEY AUTOINCREMENT, book INTEGER, para INTEGER, lang CHAR (8), media INTEGER);

-- 表：progress
DROP TABLE IF EXISTS progress;
CREATE TABLE progress (id INTEGER PRIMARY KEY AUTOINCREMENT, book INTEGER, para INTEGER, lang CHAR (8), "all" REAL, public REAL);

COMMIT TRANSACTION;
PRAGMA foreign_keys = on;
