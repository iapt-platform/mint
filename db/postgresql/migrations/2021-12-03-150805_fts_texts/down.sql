-- This file should undo anything in `up.sql`

DROP TABLE fts_texts ;

DROP TEXT SEARCH CONFIGURATION pali;

DROP TEXT SEARCH CONFIGURATION pali_unaccent;

DROP TEXT SEARCH DICTIONARY pali_stem;

DROP TEXT SEARCH DICTIONARY pali_stopwords;

DROP FUNCTION query_pali;