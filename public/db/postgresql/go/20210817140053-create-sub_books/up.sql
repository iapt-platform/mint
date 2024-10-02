-- Your SQL goes here

CREATE TABLE sub_books (
    id           SERIAL PRIMARY KEY,

    book_id INTEGER NOT NULL, 
    paragraph INTEGER NOT NULL, 

    title    VARCHAR(255) NOT NULL, 
    set_title    VARCHAR(255) NOT NULL, 

    created_at  TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
);

CREATE INDEX sub_books_book_para ON sub_books (book_id,paragraph);
CREATE INDEX sub_books_title ON sub_books (title);
CREATE INDEX sub_books_set_title ON sub_books (set_title);
