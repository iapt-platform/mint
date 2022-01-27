--
-- 由SQLiteStudio v3.1.1 产生的文件 周六 12月 18 08:55:22 2021
--
-- 文本编码：UTF-8
--
PRAGMA foreign_keys = off;
BEGIN TRANSACTION;

-- 表：article
CREATE TABLE article 
(
	id CHAR (36) PRIMARY KEY, 
	title TEXT (50), 
	subtitle TEXT, 
	summary TEXT, 
	content TEXT, 
	tag TEXT, 
	owner CHAR (36), 
	setting TEXT, 
	status INTEGER, 
	create_time INTEGER, 
	modify_time INTEGER, 
	receive_time INTEGER, 
	lang VARCHAR (16)
);

COMMIT TRANSACTION;
PRAGMA foreign_keys = on;
