--
-- 由SQLiteStudio v3.1.1 产生的文件 周三 12月 15 21:29:34 2021
--
-- 文本编码：UTF-8
--
PRAGMA foreign_keys = off;
BEGIN TRANSACTION;

-- 表：channal
CREATE TABLE channal 
(
	id CHAR (36) PRIMARY KEY, 
	owner CHAR (36) NOT NULL, 
	name TEXT, 
	summary TEXT, 
	status INTEGER, 
	lang CHAR (8), 
	create_time INTEGER, 
	modify_time INTEGER, 
	receive_time INTEGER, 
	setting TEXT
);

COMMIT TRANSACTION;
PRAGMA foreign_keys = on;
