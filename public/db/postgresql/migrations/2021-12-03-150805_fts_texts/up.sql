-- Your SQL goes here

-- 创建表结构

CREATE TABLE fts_texts (
       paragraph        integer,
       book             integer,
       wid              varchar(50),
       -- 单个出现的黑体字，权重较大
       bold_single      text,
       -- 成对出现的黑体字，权重一般
       bold_double      text,
       -- 连续三个或三个以上的黑体字，权重较低
       bold_multiple    text,
       content          text
);

-- 创建全文检索配置 pali
CREATE TEXT SEARCH CONFIGURATION pali ( parser = pg_catalog.default );

-- 创建全文检索配置 pali_unaccent 无标音符号版
CREATE TEXT SEARCH CONFIGURATION pali_unaccent ( parser = pg_catalog.default );

-- 创建巴利语词形转换字典
CREATE TEXT SEARCH DICTIONARY pali_stem (
    TEMPLATE = synonym,
    SYNONYMS = pali
);

-- 创建巴利语停用词字典
CREATE TEXT SEARCH DICTIONARY pali_stopwords (
    TEMPLATE = pg_catalog.simple, STOPWORDS = pali,
    ACCEPT = true
);

-- 修改全文检索配置 pali 使用我们创建的字典
ALTER TEXT SEARCH CONFIGURATION pali
    ADD MAPPING FOR asciiword, word, hword_part, hword_asciipart
    WITH pali_stem, pali_stopwords;

-- 修改全文检索配置 pali_unaccent 使用我们创建的字典

CREATE EXTENSION IF NOT EXISTS "unaccent";

ALTER TEXT SEARCH CONFIGURATION pali_unaccent
    ADD MAPPING FOR asciiword, word, hword_part, hword_asciipart
    WITH unaccent, pali_stem, pali_stopwords;


-- 添加自动更新的 TSVECTOR 字段

ALTER TABLE fts_texts
      ADD COLUMN full_text_search_weighted TSVECTOR
      GENERATED ALWAYS AS (
         setweight(to_tsvector('pali', coalesce(content,'')), 'A')  || ' ' ||
         setweight(to_tsvector('pali', coalesce(bold_single,'')), 'B') || ' '  ||
         setweight(to_tsvector('pali', coalesce(bold_double,'')), 'C') || ' ' ||
         setweight(to_tsvector('pali', coalesce(bold_multiple,'')), 'D')
      ) STORED;

ALTER TABLE fts_texts
      ADD COLUMN full_text_search_weighted_unaccent TSVECTOR
      GENERATED ALWAYS AS (
         setweight(to_tsvector('pali_unaccent', coalesce(content,'')), 'A')  || ' ' ||
         setweight(to_tsvector('pali_unaccent', coalesce(bold_single,'')), 'B') || ' '  ||
         setweight(to_tsvector('pali_unaccent', coalesce(bold_double,'')), 'C') || ' ' ||
         setweight(to_tsvector('pali_unaccent', coalesce(bold_multiple,'')), 'D')
      ) STORED;

-- 为该字段创建索引

CREATE INDEX full_text_search_weighted_idx
       ON fts_texts USING GIN (full_text_search_weighted);

CREATE INDEX full_text_search_weighted__unaccent_idx
       ON fts_texts USING GIN (full_text_search_weighted_unaccent);

-- 创建查询函数

CREATE OR REPLACE FUNCTION query_pali(query_str TEXT) 
  RETURNS TABLE(
          rank REAL,
          paragraph INTEGER,
          wid VARCHAR,
          bold_single TEXT,
          bold_double TEXT,
          bold_multiple TEXT,
          content TEXT,
          full_text_search_weighted TSVECTOR,
          full_text_search_weighted_unaccent TSVECTOR) 
AS $$
    SELECT
    ts_rank('{0.1, 0.2, 0.4, 1}',
        full_text_search_weighted,
        websearch_to_tsquery('pali', query_str)) +
    ts_rank('{0.1, 0.2, 0.4, 1}',
        full_text_search_weighted_unaccent,
        websearch_to_tsquery('pali_unaccent', query_str)), -- AS rank
        paragraph, wid, bold_single, bold_double, bold_multiple, content,
        full_text_search_weighted, full_text_search_weighted_unaccent
    FROM fts_texts
    WHERE
        full_text_search_weighted @@ websearch_to_tsquery('pali', query_str) OR
        full_text_search_weighted_unaccent @@ websearch_to_tsquery('pali_unaccent', query_str);
$$ LANGUAGE SQL;
