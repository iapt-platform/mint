-- Your SQL goes here


CREATE TABLE wbw_blocks
(
    id BIGINT PRIMARY KEY,
    uid VARCHAR(36)  NOT NULL, 
    parent_id VARCHAR(36), 
    channel_uid VARCHAR(36), 
    channel_id BIGINT, 
    parent_channel_uid VARCHAR(36), 
    creator_uid VARCHAR(36), 
    editor_id BIGINT  NOT NULL, 
    book_id INTEGER  NOT NULL, 
    paragraph INTEGER  NOT NULL, 
    style VARCHAR (16), 
    lang VARCHAR (16)  NOT NULL, 
    status INTEGER  NOT NULL, 
    modify_time bigint  NOT NULL, 
    create_time bigint  NOT NULL,
    created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
	updated_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
	deleted_at TIMESTAMP
);

CREATE INDEX wbwblock_book_paragraph ON wbw_blocks (book_id, paragraph);
CREATE UNIQUE INDEX wbwblock_uid ON wbw_blocks (uid);
