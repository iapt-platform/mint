--
-- 由SQLiteStudio v3.1.1 产生的文件 周日 12月 5 21:44:27 2021
--
-- 文本编码：UTF-8
--
PRAGMA foreign_keys = off;
BEGIN TRANSACTION;

-- 表：sentence
CREATE TABLE sentence 
(
	id CHAR (36) PRIMARY KEY, 
	parent CHAR (36), 
	block_id CHAR (36), 
	channal CHAR (36), 
	book INTEGER NOT NULL, 
	paragraph INTEGER NOT NULL, 
	"begin" INTEGER NOT NULL, 
	"end" INTEGER NOT NULL, 
	tag TEXT, 
	author TEXT, 
	editor CHAR (36), 
	text TEXT, 
	language CHAR (8), 
	ver INTEGER, 
	status INTEGER, 
	strlen INTEGER, 
	modify_time INTEGER NOT NULL, 
	receive_time INTEGER, 
	create_time INTEGER
);

COMMIT TRANSACTION;
PRAGMA foreign_keys = on;
