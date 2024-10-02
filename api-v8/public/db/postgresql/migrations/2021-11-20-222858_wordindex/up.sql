-- Your SQL goes here

-- 表：wordindex
CREATE TABLE word_indexs
(
	id SERIAL PRIMARY KEY, 
	word TEXT NOT NULL UNIQUE, 
	word_en TEXT  NOT NULL, 
	count INTEGER NOT NULL DEFAULT (0), 
	normal INTEGER NOT NULL DEFAULT (0), 
	bold INTEGER NOT NULL DEFAULT (0), 
	is_base INTEGER NOT NULL DEFAULT (0), 
	len INTEGER NOT NULL DEFAULT (0), 
	final INTEGER,
	created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP
);

-- 索引：
CREATE INDEX word_indexs_worden ON word_indexs (word_en);
CREATE INDEX word_indexs_word ON word_indexs (word);