-- This file should undo anything in `up.sql`
DROP INDEX  IF EXISTS  courses_title ;
DROP INDEX  IF EXISTS  courses_status ;
DROP INDEX  IF EXISTS  courses_lang ;
DROP INDEX  IF EXISTS  courses_lang_status ;
DROP INDEX  IF EXISTS  courses_uid ;

DROP TABLE courses ;

DROP TYPE TStatus ;
DROP TYPE TContentType ;

