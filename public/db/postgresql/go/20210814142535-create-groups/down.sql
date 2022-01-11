-- This file should undo anything in `up.sql`

DROP INDEX IF EXISTS groups_name;
DROP INDEX IF EXISTS groups_status;

DROP TABLE groups;

DROP INDEX IF EXISTS groups_uid;