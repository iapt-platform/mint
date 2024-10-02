-- This file should undo anything in `up.sql`

DROP INDEX IF EXISTS dictionaries_word;
DROP INDEX IF EXISTS dictionaries_base;
DROP INDEX IF EXISTS dictionaries_lang;

DROP TABLE dictionaries;
