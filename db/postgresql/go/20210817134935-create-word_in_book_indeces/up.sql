-- Your SQL goes here

CREATE TABLE word_in_book_indexs (
    id           SERIAL PRIMARY KEY,

    book_id      INTEGER NOT NULL, 
    pali_word_index_id       INTEGER NOT NULL, 
    count       INTEGER NOT NULL, 

    created_at   TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP
);
