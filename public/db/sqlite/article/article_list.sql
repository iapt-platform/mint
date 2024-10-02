--
-- 由SQLiteStudio v3.1.1 产生的文件 周六 12月 18 08:55:58 2021
--
-- 文本编码：UTF-8
--
PRAGMA foreign_keys = off;
BEGIN TRANSACTION;

-- 表：article_list
CREATE TABLE article_list 
(
	id INTEGER PRIMARY KEY AUTOINCREMENT, 
	collect_id VARCHAR (36), 
	collect_title TEXT, 
	article_id VARCHAR (36), 
	level INTEGER, 
	title VARCHAR (64), 
	children INTEGER DEFAULT (0)
);

COMMIT TRANSACTION;
PRAGMA foreign_keys = on;
