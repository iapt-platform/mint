-- Your SQL goes here

CREATE TABLE nissaya_page_maps (
    id           SERIAL PRIMARY KEY,

    type     VARCHAR (8) NOT NULL, 
    nsy_book_id    INTEGER NOT NULL, 
    book_page_number    INTEGER NOT NULL, 

    nsy_id        VARCHAR (8) NOT NULL,
    nsy_page_number    INTEGER NOT NULL, 
    nsy_name          VARCHAR (32) NOT NULL,

    created_at  TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP
);

