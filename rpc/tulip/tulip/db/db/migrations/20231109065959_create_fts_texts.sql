-- migrate:up
-- 创建表结构

CREATE TABLE public.fts_texts (
	id bigserial NOT NULL,
	book int4 NOT NULL,
	paragraph int4 NOT NULL,
	bold_single text,
	bold_double text,
	bold_multiple text,
	"content" text NOT NULL,
	created_at timestamp(0) NOT NULL DEFAULT CURRENT_TIMESTAMP,
	updated_at timestamp(0) NOT NULL DEFAULT CURRENT_TIMESTAMP,
	pcd_book_id int4 NOT NULL DEFAULT 0,
	CONSTRAINT fts_texts_pkey PRIMARY KEY (id)
);
CREATE INDEX fts_texts_pcd_book_id_index ON public.fts_texts USING btree (pcd_book_id);


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

-- migrate:down
-- 删除全文检索配置 pali
DROP TEXT SEARCH CONFIGURATION  pali ;

-- 删除全文检索配置 pali_unaccent 无标音符号版
DROP TEXT SEARCH CONFIGURATION pali_unaccent ;


-- 删除巴利语词形转换字典
DROP TEXT SEARCH DICTIONARY pali_stem ;

-- 删除巴利语停用词字典
DROP TEXT SEARCH DICTIONARY pali_stopwords ;

drop table fts_texts;

