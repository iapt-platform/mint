--
-- 由SQLiteStudio v3.1.1 产生的文件 周三 12月 8 07:46:27 2021
--
-- 文本编码：UTF-8
--
PRAGMA foreign_keys = off;
BEGIN TRANSACTION;

-- 表：user_index
CREATE TABLE user_index 
(
	id INTEGER PRIMARY KEY AUTOINCREMENT UNIQUE, 
	word_index INTEGER NOT NULL, 
	user_id INTEGER NOT NULL, 
	create_time INTEGER DEFAULT (1)
);

COMMIT TRANSACTION;
PRAGMA foreign_keys = on;
