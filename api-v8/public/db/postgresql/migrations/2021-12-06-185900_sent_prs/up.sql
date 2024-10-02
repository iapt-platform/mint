CREATE TABLE sent_prs 
(
    id SERIAL PRIMARY KEY,
	book_id INTEGER NOT NULL, 
	paragraph INTEGER NOT NULL, 
	word_start INTEGER NOT NULL, 
	word_end INTEGER NOT NULL, 
	channel_uid VARCHAR (36),
	author VARCHAR (40), 
	editor_uid VARCHAR (36), 
	content TEXT, 
	language VARCHAR (16), 
	status INTEGER DEFAULT (1), 
	strlen INTEGER, 
	create_time BIGINT NOT NULL, 
	modify_time BIGINT NOT NULL,
	created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
	updated_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP
);

CREATE INDEX sent_prs_channel_book_para_start_end ON sent_prs (channel_uid,book_id,paragraph,word_start,word_end);

