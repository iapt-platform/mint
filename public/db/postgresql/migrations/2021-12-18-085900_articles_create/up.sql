CREATE TYPE TContentType AS ENUM 
(
	'text',	
	'markdown',
	'html'
);

CREATE TABLE articles
(
	id SERIAL PRIMARY KEY, 
	uid VARCHAR (36)  NOT NULL, 
	parent_id INTEGER,
	title VARCHAR (128) NOT NULL, 
	subtitle VARCHAR (128), 
	summary VARCHAR (1024), 
	cover TEXT,
	content TEXT ,
	content_type TContentType NOT NULL DEFAULT('markdown'), 
	owner VARCHAR (36) NOT NULL, 
	owner_id INTEGER NOT NULL, 
	editor_id INTEGER NOT NULL, 
	setting TEXT, 
	status INTEGER NOT NULL, 
	lang VARCHAR (16),
	create_time BIGINT, 
	modify_time BIGINT, 
	created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
	updated_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
	deleted_at TIMESTAMP 
);

CREATE INDEX articles_owner ON articles (owner);
CREATE INDEX articles_owner_id ON articles (owner_id);
CREATE INDEX articles_editor_id ON articles (editor_id);
CREATE UNIQUE INDEX articles_uid ON articles (uid);