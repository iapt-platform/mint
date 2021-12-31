-- Your SQL goes here

CREATE TABLE pali_texts (
    id           SERIAL PRIMARY KEY,

    book_id      INTEGER NOT NULL, 
    paragraph    INTEGER NOT NULL, 
    level        INTEGER NOT NULL, 

    class        VARCHAR (32) NOT NULL,
    toc          VARCHAR (255) NOT NULL,
    text         TEXT,
    html         TEXT,

    str_length   INTEGER NOT NULL, 
    chapter_len  INTEGER NOT NULL, 
    next_chapter INTEGER NOT NULL, 
    prev_chapter INTEGER NOT NULL, 
    parent       INTEGER NOT NULL, 
    chapter_strlen        INTEGER NOT NULL, 
    path         JSON NOT NULL, 

	version     INTEGER NOT NULL DEFAULT (1),

    created_at  TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at  TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP
);


CREATE UNIQUE INDEX pali_text ON pali_texts (book_id,paragraph);
