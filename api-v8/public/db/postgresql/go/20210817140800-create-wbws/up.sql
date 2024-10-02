-- Your SQL goes here

CREATE TABLE wbws (
    id           SERIAL PRIMARY KEY,
    pr_parent_id INTEGER ,

    wbws_index_id     INTEGER ,
    channel_id   INTEGER ,
    
    book_id      INTEGER  NOT NULL,
    paragraph    INTEGER  NOT NULL,
    sn        INTEGER  NOT NULL,

    word         VARCHAR (512) NOT NULL,
    data      TEXT,
    
    lang         VARCHAR (16) NOT NULL DEFAULT('en'),

    status TStatus  NOT NULL DEFAULT('private'), 

    editor_id INTEGER  NOT NULL,
    owner_id INTEGER  NOT NULL,

	version     INTEGER NOT NULL DEFAULT (1),
    deleted_at  TIMESTAMP,

    created_at  TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at  TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP
);


CREATE INDEX wbws_unique ON wbws (channel_id,book_id,paragraph,sn);
CREATE INDEX wbws_word ON wbws (word);

