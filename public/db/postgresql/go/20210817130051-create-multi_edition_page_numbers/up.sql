-- Your SQL goes here
--不同版本页码
CREATE TABLE multi_edition_page_numbers (
    id           SERIAL PRIMARY KEY,

    edition     VARCHAR (8) NOT NULL, 
    book_id    INTEGER NOT NULL, 
    paragraph    INTEGER NOT NULL, 
    vol    INTEGER NOT NULL, 
    page    INTEGER NOT NULL, 

    created_at  TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP
);


CREATE INDEX multi_edition_page_numbers_book_para ON multi_edition_page_numbers (book_id,page);
