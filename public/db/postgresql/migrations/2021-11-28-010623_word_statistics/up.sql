-- word_statistics
CREATE TABLE word_statistics
(
	id SERIAL PRIMARY KEY, 
	bookid INTEGER, 
	word TEXT, 
	count INTEGER, 
	base TEXT, 
	end1 TEXT, 
	end2 TEXT, 
	type INTEGER, 
	length INTEGER,
	created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP
);

-- 索引：pali
CREATE INDEX word_statistics_bookid ON word_statistics (bookid);
CREATE INDEX word_statistics_base ON word_statistics (base);

