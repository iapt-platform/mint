-- This file should undo anything in `up.sql`

DROP TABLE fts_texts ;

-- 删除全文检索配置 pali
DROP TEXT SEARCH CONFIGURATION  pali ;

-- 删除全文检索配置 pali_unaccent 无标音符号版
DROP TEXT SEARCH CONFIGURATION pali_unaccent ;

-- 删除巴利语词形转换字典
DROP TEXT SEARCH DICTIONARY pali_stem ;

-- 删除巴利语停用词字典
DROP TEXT SEARCH DICTIONARY pali_stopwords ;

-- 删除查询函数

DROP  FUNCTION query_pali ;