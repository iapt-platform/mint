-- Your SQL goes here

CREATE TABLE bookword 
(
	id SERIAL PRIMARY KEY,
	book INTEGER  NOT NULL, 
	wordindex INTEGER  NOT NULL, 
	count INTEGER NOT NULL,
    created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP
);

-- 索引：
CREATE INDEX bookword_wordindex ON bookword (wordindex);
