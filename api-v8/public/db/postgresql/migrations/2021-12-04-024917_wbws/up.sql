-- Your SQL goes here

-- wbw definition

CREATE TABLE wbws
(
	id BIGINT PRIMARY KEY,
    uid varchar(36)  NOT NULL, 
    block_uid varchar(36), 
    block_id BIGINT, 
    channel_id BIGINT, 
    book_id INTEGER NOT NULL, 
    paragraph INTEGER NOT NULL, 
    wid bigint NOT NULL, 
    word TEXT NOT NULL, 
    data TEXT, 
    status INTEGER NOT NULL, 
    creator_uid varchar(36) NOT NULL, 
    editor_id BIGINT NOT NULL, 
    create_time BIGINT NOT NULL,
    modify_time BIGINT NOT NULL,  
	created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
	updated_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
	deleted_at TIMESTAMP
);

CREATE UNIQUE INDEX wbw_uid ON wbws (uid);
CREATE INDEX wbw_index_book_paragraph ON wbws (book_id, paragraph);
CREATE INDEX wbw_index_book_paragraph_wid ON wbws (book_id, paragraph,wid);
CREATE INDEX wbw_index_block_id ON wbws (block_id);
CREATE INDEX wbw_index_block_uid ON wbws (block_uid);
CREATE INDEX wbw_index_modify_time ON wbws (modify_time);
CREATE INDEX wbw_index_updated ON wbws (updated_at);

