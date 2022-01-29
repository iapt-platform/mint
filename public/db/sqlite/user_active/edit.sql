--
-- 由SQLiteStudio v3.1.1 产生的文件 周二 12月 14 15:37:03 2021
--
-- 文本编码：UTF-8
--
PRAGMA foreign_keys = off;
BEGIN TRANSACTION;

-- 表：edit
CREATE TABLE edit 
(
	id INTEGER PRIMARY KEY AUTOINCREMENT, 
	user_id CHAR (36), 
	start INTEGER, 
	"end" INTEGER, 
	duration INTEGER, 
	hit INTEGER DEFAULT (0), 
	timezone INTEGER DEFAULT (0)
);

-- 索引：end
CREATE INDEX "end" ON edit ("end" DESC);

COMMIT TRANSACTION;
PRAGMA foreign_keys = on;
