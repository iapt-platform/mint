-- This file should undo anything in `up.sql`
DROP INDEX IF EXISTS word_statistics_bookid ;
DROP INDEX IF EXISTS word_statistics_base ;
DROP TABLE word_statistics ;
