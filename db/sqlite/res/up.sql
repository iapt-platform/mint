

CREATE TABLE res_index 
(
	id SERIAL PRIMARY KEY,
	book INTEGER, 
	paragraph INTEGER, 
	title VARCHAR (1024), 
	title_en VARCHAR (1024),  
	level INTEGER, 
	type INTEGER, 
	language VARCHAR (16), 
	author VARCHAR (256), 
	editor INTEGER, 
	share INTEGER, 
	edition INTEGER NOT NULL DEFAULT 1, 
	hit INTEGER DEFAULT 0 NOT NULL, 
	album INTEGER, 
	tag VARCHAR (1024), 
	summary VARCHAR (1024), 
	create_time bigint, 
	update_time bigint
);


CREATE INDEX res_index_title ON res_index (title);
CREATE INDEX res_index_title_en ON res_index (title_en);

