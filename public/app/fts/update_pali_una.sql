CREATE TEXT SEARCH DICTIONARY pali_stem_unaccent (
    TEMPLATE = synonym,
    SYNONYMS = pali_unaccent
);

ALTER TEXT SEARCH CONFIGURATION pali_unaccent
    ALTER MAPPING FOR  word, hword_part, hword_asciipart , asciiword
    WITH pali_stem_unaccent, pali_stopwords;
    UPDATE fts_texts  SET content = content;


