-- This file should undo anything in `up.sql`
DROP INDEX comments_title ;
DROP INDEX comments_uid ;

DROP TABLE comments;

DROP TYPE TCommentStatus;
