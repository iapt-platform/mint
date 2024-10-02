-- Your SQL goes here

CREATE TABLE wbws_indexs (
    id           SERIAL PRIMARY KEY,

    channel_id   INTEGER ,
    
    book_id      INTEGER  NOT NULL,
    paragraph    INTEGER  NOT NULL,

    owner_id INTEGER  NOT NULL,

    created_at  TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at  TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP
);


CREATE INDEX wbws_indexs_unique ON wbws_indexs (channel_id,book_id,paragraph);

