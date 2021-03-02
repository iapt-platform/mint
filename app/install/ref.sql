--
-- 由SQLiteStudio v3.1.1 产生的文件 周三 7月 29 16:58:33 2020
--
-- 文本编码：UTF-8
--
PRAGMA foreign_keys = off;

BEGIN TRANSACTION;

-- 表：dict
CREATE TABLE dict (
	id INTEGER,
	language TEXT,
	dict_id INTEGER,
	eword TEXT,
	word TEXT,
	paliword TEXT,
	mean TEXT,
	length INTEGER,
	status INTEGER DEFAULT (1)
);

-- 表：info
CREATE TABLE info (
	language TEXT,
	id INTEGER PRIMARY KEY NOT NULL,
	shortname TEXT,
	name TEXT
);

-- 索引：eword
DROP INDEX IF EXISTS eword;

CREATE INDEX eword ON dict ("eword" ASC);

-- 索引：len
DROP INDEX IF EXISTS len;

CREATE INDEX len ON dict ("length" ASC);

-- 索引：word
DROP INDEX IF EXISTS word;

CREATE INDEX word ON dict ("word" ASC);

COMMIT TRANSACTION;

PRAGMA foreign_keys = on;