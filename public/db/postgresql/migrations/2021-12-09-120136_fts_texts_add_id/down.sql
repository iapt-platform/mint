-- This file should undo anything in `up.sql`

ALTER TABLE fts_texts DROP COLUMN updated_at;
ALTER TABLE fts_texts DROP COLUMN created_at;
ALTER TABLE fts_texts DROP CONSTRAINT fts_texts_pkey;
ALTER TABLE fts_texts DROP COLUMN id;
