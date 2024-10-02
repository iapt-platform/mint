
-- 表：wordindex
CREATE TABLE wordindex 
(
	id SERIAL PRIMARY KEY, 
	word TEXT NOT NULL UNIQUE, 
	word_en TEXT  NOT NULL, 
	count INTEGER NOT NULL DEFAULT (0), 
	normal INTEGER NOT NULL DEFAULT (0), 
	bold INTEGER NOT NULL DEFAULT (0), 
	is_base INTEGER NOT NULL DEFAULT (0), 
	len INTEGER NOT NULL DEFAULT (0), 
	final INTEGER
);

-- 索引：
CREATE INDEX wordindex_worden ON wordindex (word_en);
CREATE INDEX wordindex_word ON wordindex (word);

