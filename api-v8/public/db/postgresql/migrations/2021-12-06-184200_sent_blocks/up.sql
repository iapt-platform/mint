CREATE TABLE sent_blocks 
(
    id SERIAL PRIMARY KEY,
	uid VARCHAR (36), 
	parent_uid VARCHAR (36), 
	book_id INTEGER, 
	paragraph INTEGER, 
	owner_uid VARCHAR (36), 
	lang VARCHAR (16), 
	author VARCHAR (50), 
	editor_uid VARCHAR (36),
	status INTEGER NOT NULL DEFAULT (10), 
	modify_time BIGINT, 
	create_time BIGINT,
	created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
	updated_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
	deleted_at TIMESTAMP
);

CREATE UNIQUE INDEX sent_blocks_uid ON sent_blocks (uid);
CREATE INDEX sent_blocks_book_para ON sent_blocks (book_id,paragraph);
