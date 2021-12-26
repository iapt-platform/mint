-- Your SQL goes here

CREATE TABLE sentences_historays (
    id           SERIAL PRIMARY KEY,
    sentence_id INTEGER ,

    content      TEXT,
    content_type TContentType NOT NULL DEFAULT('markdown'), 
    
    editor_id INTEGER  NOT NULL,

    created_at  TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP
);



