-- This file should undo anything in `up.sql`

DROP INDEX  books_title ;
DROP INDEX  books_status ;
DROP INDEX  books_lang ;
DROP INDEX  books_lang_status;

DROP TABLE books;

