-- Your SQL goes here

CREATE TABLE nissaya_book_maps (
    id           SERIAL PRIMARY KEY,

    nsy_book_id     VARCHAR (64) NOT NULL, 
    book_id    INTEGER NOT NULL, 
    vol    INTEGER NOT NULL, 

    title        VARCHAR (64) NOT NULL,
    type          VARCHAR (16) NOT NULL,

    created_at  TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP
);


CREATE INDEX nissaya_book_maps_nsy_book_id ON nissaya_book_maps (nsy_book_id);
