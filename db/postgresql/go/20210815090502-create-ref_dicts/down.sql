-- This file should undo anything in `up.sql`

DROP INDEX IF EXISTS ref_dicts_word ;
DROP INDEX IF EXISTS ref_dicts_word_en;

DROP TABLE ref_dicts;
