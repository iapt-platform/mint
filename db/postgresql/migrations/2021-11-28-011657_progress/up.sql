-- 表：progress
CREATE TABLE progresss
(
	id SERIAL PRIMARY KEY,
	book INTEGER, 
	para INTEGER, 
	lang VARCHAR (16), 
	all_strlen INTEGER, 
	public_strlen INTEGER,
	created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP
);

CREATE INDEX progresss_book_para ON progresss (book,para);
CREATE INDEX progresss_book_para_lang ON progresss (book,para,lang);
