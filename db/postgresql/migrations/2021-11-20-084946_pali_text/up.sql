-- Your SQL goes here

CREATE TABLE pali_texts
(
	id SERIAL PRIMARY KEY,
	book INTEGER, 
	paragraph INTEGER, 
	level INTEGER, 
	class varchar (255), 
	toc TEXT, 
	text TEXT, 
	html TEXT, 
	lenght INTEGER, 
	album_index INTEGER, 
	chapter_len INTEGER, 
	next_chapter INTEGER, 
	prev_chapter INTEGER, 
	parent INTEGER, 
	chapter_strlen INTEGER,
    created_at  TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP
);

-- 索引
CREATE INDEX pali_text_vri ON pali_texts (book, paragraph );
