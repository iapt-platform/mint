-- Your SQL goes here
--不同版本页码
CREATE TABLE cs_para_numbers (
    id           SERIAL PRIMARY KEY,

    book_id      INTEGER NOT NULL, 
    paragraph    INTEGER NOT NULL, 
    sub_book_id  INTEGER NOT NULL, 
    cs_paragraph INTEGER NOT NULL, 
    book_name    VARCHAR(8) NOT NULL, 

    created_at   TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP
);


CREATE INDEX cs_para_numbers_book ON cs_para_numbers (book_id,paragraph,cs_paragraph);
