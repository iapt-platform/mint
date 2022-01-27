--
-- 由SQLiteStudio v3.1.1 产生的文件 周一 12月 6 11:42:32 2021
--
-- 文本编码：UTF-8
--
PRAGMA foreign_keys = off;
BEGIN TRANSACTION;

-- 表：sent_pr
CREATE TABLE sent_pr 
(
	id INTEGER PRIMARY KEY AUTOINCREMENT, 
	book INTEGER NOT NULL, 
	paragraph INTEGER NOT NULL, 
	"begin" INTEGER NOT NULL, 
	"end" INTEGER NOT NULL, 
	channel VARCHAR (36), 
	tag VARCHAR (40), 
	author VARCHAR (40), 
	editor VARCHAR (36), 
	text TEXT, 
	language VARCHAR (8), 
	status INTEGER, 
	strlen INTEGER, 
	create_time INTEGER, 
	modify_time INTEGER NOT NULL, 
	receive_time INTEGER
);

-- 索引：book
CREATE INDEX book ON sent_pr (book, paragraph, "begin", "end", channel);

COMMIT TRANSACTION;
PRAGMA foreign_keys = on;
