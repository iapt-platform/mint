
-- 表：progress_chapter
CREATE TABLE progress_chapters
(
	id SERIAL PRIMARY KEY,
	book INTEGER, 
	para INTEGER, 
	lang VARCHAR (16), 
	all_trans REAL, 
	public REAL,
	created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP
);

CREATE INDEX progress_chapters_book_para ON progress_chapters (book,para);
CREATE INDEX progress_chapters_book_para_lang ON progress_chapters (book,para,lang);