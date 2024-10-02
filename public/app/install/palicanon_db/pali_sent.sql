--
-- 由SQLiteStudio v3.1.1 产生的文件 周四 9月 17 09:14:02 2020
--
-- 文本编码：UTF-8
--
PRAGMA foreign_keys = off;
BEGIN TRANSACTION;

-- 表：pali_sent
DROP TABLE IF EXISTS pali_sent;
CREATE TABLE pali_sent (id INTEGER PRIMARY KEY AUTOINCREMENT, book INTEGER, paragraph INTEGER, "begin" INTEGER, "end" INTEGER, length INTEGER, count INTEGER, text TEXT, html TEXT, real TEXT, real_en TEXT, sim_sents TEXT);

-- 索引：
DROP INDEX IF EXISTS "";
CREATE INDEX "" ON pali_sent (book, paragraph, "begin", "end", count);

COMMIT TRANSACTION;
PRAGMA foreign_keys = on;
