CREATE TABLE collections
(
	id SERIAL PRIMARY KEY, 
	uid VARCHAR (36)  NOT NULL, 
	parent_id INTEGER,
	title VARCHAR (128) NOT NULL, 
	subtitle VARCHAR (128), 
	summary VARCHAR (1024), 
	cover TEXT,
	article_list TEXT, 
	owner VARCHAR (36) NOT NULL, 
	owner_id INTEGER NOT NULL, 
	editor_id INTEGER NOT NULL, 
	setting TEXT,
	status INTEGER, 	
	lang VARCHAR (16), 
	create_time BIGINT, 
	modify_time BIGINT, 
	created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
	updated_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
	deleted_at TIMESTAMP 
);

CREATE INDEX collections_owner ON collections (owner);
CREATE INDEX collections_owner_id ON collections (owner_id);
CREATE INDEX collections_editor_id ON collections (editor_id);
CREATE UNIQUE INDEX collections_uid ON collections (uid);