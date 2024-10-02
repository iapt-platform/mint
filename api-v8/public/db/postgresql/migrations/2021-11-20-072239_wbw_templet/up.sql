-- Your SQL goes here

CREATE TABLE wbw_templates
( 
	id SERIAL PRIMARY KEY,
	book INTEGER  NOT NULL, 
	paragraph INTEGER  NOT NULL, 
	wid INTEGER  NOT NULL, 
	word VARCHAR (1024),
	real VARCHAR (1024),
	type VARCHAR (64),
	gramma VARCHAR (64),
	part VARCHAR (1024),
	style VARCHAR (64)
);

-- 索引：search
CREATE INDEX wbw_templates_book ON wbw_templates ("book", "paragraph", "wid");
CREATE INDEX wbw_templates_book1 ON wbw_templates ("book", "paragraph");