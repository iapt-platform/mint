-- This file should undo anything in `up.sql`

DROP INDEX IF EXISTS articles_title ;
DROP INDEX IF EXISTS articles_status ;
DROP INDEX IF EXISTS articles_lang ;
DROP INDEX IF EXISTS articles_lang_status;
DROP INDEX IF EXISTS articles_uid ;

DROP TABLE articles;

