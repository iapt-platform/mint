-- This file should undo anything in `up.sql`

DROP INDEX IF EXISTS terms_word ;
DROP INDEX IF EXISTS terms_worden;
DROP INDEX IF EXISTS terms_lang;
DROP INDEX IF EXISTS terms_channel_id;

DROP TABLE terms;

