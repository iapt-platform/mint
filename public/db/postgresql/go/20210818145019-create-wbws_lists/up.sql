-- Your SQL goes here

CREATE TABLE wbws_lists (
    id          SERIAL PRIMARY KEY,

    parent_id   INTEGER ,
    channel_id  INTEGER ,
    book_id     INTEGER ,
    paragraph   INTEGER ,
    title       VARCHAR(255)  NOT NULL,


    setting     TEXT  NOT NULL,
    content     JSON  NOT NULL,

    status      TStatus  NOT NULL,    
    owner_id    INTEGER NOT NULL,
	version     INTEGER NOT NULL DEFAULT (1),
    deleted_at  TIMESTAMP,

    created_at  TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at  TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP
);


CREATE INDEX wbws_lists_title ON wbws_lists (title);

