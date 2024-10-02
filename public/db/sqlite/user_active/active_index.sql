--
-- 由SQLiteStudio v3.1.1 产生的文件 周二 12月 14 15:36:35 2021
--
-- 文本编码：UTF-8
--
PRAGMA foreign_keys = off;
BEGIN TRANSACTION;

-- 表：active_index
CREATE TABLE active_index 
(
	id INTEGER PRIMARY KEY AUTOINCREMENT, 
	user_id CHAR (36), 
	date INTEGER, 
	duration INTEGER, 
	hit INTEGER
);

COMMIT TRANSACTION;
PRAGMA foreign_keys = on;
