-- Your SQL goes here

CREATE TABLE bolds (
    id           SERIAL PRIMARY KEY,

    book_id      INTEGER NOT NULL, 
    paragraph       INTEGER NOT NULL, 
    word_spell       VARCHAR(512) NOT NULL, 
    word_real       VARCHAR(512) NOT NULL, 
    word_en       VARCHAR(512) NOT NULL, 

    created_at   TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP
);
CREATE INDEX bolds_word_real ON bolds (word_real);
