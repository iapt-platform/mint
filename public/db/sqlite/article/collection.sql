--
-- 由SQLiteStudio v3.1.1 产生的文件 周六 12月 18 08:56:56 2021
--
-- 文本编码：UTF-8
--
PRAGMA foreign_keys = off;
BEGIN TRANSACTION;

-- 表：collect
CREATE TABLE collect 
(
	id CHAR (36) PRIMARY KEY, 
	title TEXT, 
	subtitle TEXT, 
	summary TEXT, 
	article_list TEXT, 
	status INTEGER, 
	owner CHAR (36), 
	lang CHAR (8), 
	create_time INTEGER, 
	modify_time INTEGER, 
	receive_time INTEGER, 
	tag TEXT
);

COMMIT TRANSACTION;
PRAGMA foreign_keys = on;
