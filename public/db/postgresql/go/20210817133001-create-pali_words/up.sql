-- Your SQL goes here

CREATE TABLE pali_words (
    id           SERIAL PRIMARY KEY,

    book_id      INTEGER NOT NULL, 
    paragraph     INTEGER NOT NULL, 
    pali_word_index_id       INTEGER NOT NULL, 
    is_base    BOOL NOT NULL, 
    weight       INTEGER NOT NULL, 

    created_at   TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP
);


CREATE INDEX pali_words_book_para ON pali_words (book_id,paragraph);
