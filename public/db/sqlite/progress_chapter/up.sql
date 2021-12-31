
-- 表：progress_chapter
CREATE TABLE progress_chapter 
(
	id SERIAL PRIMARY KEY,
	book INTEGER, 
	para INTEGER, 
	lang VARCHAR (16), 
	all_trans REAL, 
	public REAL,
	created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP
);

