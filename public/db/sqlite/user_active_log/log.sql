--
-- 由SQLiteStudio v3.1.1 产生的文件 周二 12月 14 15:41:28 2021
--
-- 文本编码：UTF-8
--
PRAGMA foreign_keys = off;
BEGIN TRANSACTION;

-- 表：log
CREATE TABLE log 
(
	id INTEGER PRIMARY KEY AUTOINCREMENT, 
	user_id INTEGER, 
	active INTEGER, 
	content TEXT, 
	time INTEGER, 
	timezone INTEGER
);

COMMIT TRANSACTION;
PRAGMA foreign_keys = on;
