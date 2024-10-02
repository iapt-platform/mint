-- This file should undo anything in `up.sql`

DROP INDEX IF EXISTS collections_title;
DROP INDEX IF EXISTS collections_lang ;
DROP INDEX IF EXISTS collections_status ;
DROP INDEX IF EXISTS collections_lang_status;
DROP INDEX IF EXISTS collections_uid;

DROP TABLE collections;
