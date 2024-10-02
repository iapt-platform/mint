-- 表：progress
CREATE TABLE progress 
(
	id SERIAL PRIMARY KEY,
	book INTEGER, 
	para INTEGER, 
	lang VARCHAR (16), 
	all_strlen INTEGER, 
	public_strlen INTEGER,
	created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP
);

