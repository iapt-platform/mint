--
-- 由SQLiteStudio v3.1.1 产生的文件 周一 12月 6 19:46:53 2021
--
-- 文本编码：UTF-8
--
PRAGMA foreign_keys = off;
BEGIN TRANSACTION;

-- 表：sent_historay
CREATE TABLE sent_historay 
(
	sent_id CHAR (36), 
	user_id CHAR (36), 
	text TEXT, 
	date INTEGER, 
	landmark TEXT
);

COMMIT TRANSACTION;
PRAGMA foreign_keys = on;
