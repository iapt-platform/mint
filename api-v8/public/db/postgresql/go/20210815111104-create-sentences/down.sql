-- This file should undo anything in `up.sql`
DROP INDEX IF EXISTS sentences_unique ;

DROP TABLE sentences;


DROP TYPE TSentenceType