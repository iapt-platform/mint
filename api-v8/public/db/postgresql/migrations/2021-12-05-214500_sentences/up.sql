CREATE TABLE sentences
(
    id SERIAL PRIMARY KEY,
	uid VARCHAR (36), 
	parent_uid VARCHAR (36), 
	block_uid VARCHAR (36), 
	channel_uid VARCHAR (36), 
	book_id INTEGER NOT NULL, 
	paragraph INTEGER NOT NULL, 
	word_start INTEGER NOT NULL, 
	word_end INTEGER NOT NULL, 
	author TEXT, 
	editor_uid VARCHAR (36), 
	content TEXT, 
	language VARCHAR (16), 
	version INTEGER NOT NULL DEFAULT (1), 
	status INTEGER NOT NULL DEFAULT (10), 
	strlen INTEGER, 
	modify_time BIGINT NOT NULL, 
	create_time BIGINT NOT NULL,
	created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
	updated_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
	deleted_at TIMESTAMP
);

CREATE UNIQUE INDEX sentences_uid ON sentences (uid);
CREATE INDEX sentences_block_uid ON sentences (block_uid);
CREATE INDEX sentences_channel_book_para ON sentences (channel_uid,book_id,paragraph);
CREATE INDEX sentences_channel_book_para_start_end ON sentences (channel_uid,book_id,paragraph,word_start,word_end);
