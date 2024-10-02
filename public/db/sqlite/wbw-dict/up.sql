--
-- 由SQLiteStudio v3.1.1 产生的文件 周三 12月 8 07:45:42 2021
--
-- 文本编码：UTF-8
--
PRAGMA foreign_keys = off;
BEGIN TRANSACTION;

-- 表：dict
CREATE TABLE dict 
(
	id INTEGER PRIMARY KEY AUTOINCREMENT NOT NULL, 
	guid TEXT, 
	pali TEXT NOT NULL, 
	type TEXT, 
	gramma TEXT, 
	parent TEXT, 
	parent_id TEXT, 
	mean TEXT, 
	note TEXT, 
	factors TEXT, 
	factormean TEXT, 
	part_id TEXT, 
	status INTEGER, 
	dict_name TEXT, 
	language TEXT, 
	confidence INTEGER DEFAULT (100), 
	creator INTEGER, 
	ref_counter INTEGER DEFAULT (1),
	time INTEGER, 
);

-- 索引：
CREATE INDEX "" ON dict (pali);

COMMIT TRANSACTION;
PRAGMA foreign_keys = on;
