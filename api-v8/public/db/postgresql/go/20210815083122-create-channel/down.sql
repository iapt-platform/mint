-- This file should undo anything in `up.sql`

DROP INDEX IF EXISTS channels_title ;
DROP INDEX IF EXISTS channels_status ;
DROP INDEX IF EXISTS channels_lang ;
DROP INDEX IF EXISTS channels_lang_status;
DROP INDEX IF EXISTS channels_uid ;

DROP TABLE channels;
