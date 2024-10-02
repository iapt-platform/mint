
-- 表：word
CREATE TABLE word 
(
	id SERIAL PRIMARY KEY, 
	sn INTEGER NOT NULL , 
	book INTEGER NOT NULL, 
	paragraph INTEGER NOT NULL, 
	wordindex INTEGER NOT NULL, 
	bold INTEGER NOT NULL  DEFAULT (0), 
	weight INTEGER  NOT NULL  DEFAULT (1),
	created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP
);

-- 索引：pali
CREATE INDEX word_book_paragraph ON word (book, paragraph);

-- 索引：wordindex1
CREATE INDEX word_wordindex ON word (wordindex);

