--
-- 由SQLiteStudio v3.1.1 产生的文件 周一 12月 6 11:43:06 2021
--
-- 文本编码：UTF-8
--
PRAGMA foreign_keys = off;
BEGIN TRANSACTION;

-- 表：sent_block
CREATE TABLE sent_block 
(
	id CHAR (36), 
	parent_id CHAR (36), 
	book INTEGER, 
	paragraph INTEGER, 
	owner CHAR (36), 
	lang CHAR (8), 
	author CHAR (50), 
	editor TEXT, 
	tag TEXT, 
	status INTEGER, 
	modify_time INTEGER, 
	receive_time INTEGER
);

COMMIT TRANSACTION;
PRAGMA foreign_keys = on;
