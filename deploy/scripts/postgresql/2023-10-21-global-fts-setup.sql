-- FROM https://github.com/iapt-platform/mint/blob/laravel/database/migrations/2021_12_30_053602_add_func_to_fts_texts_table.php

CREATE TEXT SEARCH CONFIGURATION pali ( parser = pg_catalog.default );
CREATE TEXT SEARCH CONFIGURATION pali_unaccent ( parser = pg_catalog.default );
CREATE TEXT SEARCH DICTIONARY pali_stem ( TEMPLATE = synonym, SYNONYMS = pali );
CREATE TEXT SEARCH DICTIONARY pali_stopwords ( TEMPLATE = pg_catalog.simple, STOPWORDS = pali, ACCEPT = true);

ALTER TEXT SEARCH CONFIGURATION pali
ADD MAPPING FOR asciiword, word, hword_part, hword_asciipart
WITH pali_stem, pali_stopwords;

CREATE EXTENSION IF NOT EXISTS "unaccent";
ALTER TEXT SEARCH CONFIGURATION pali_unaccent
ADD MAPPING FOR asciiword, word, hword_part, hword_asciipart
WITH unaccent, pali_stem, pali_stopwords;
