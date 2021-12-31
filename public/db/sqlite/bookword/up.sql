--
-- 由SQLiteStudio v3.1.1 产生的文件 周四 11月 18 13:08:19 2021
--
-- 文本编码：UTF-8
--

-- 表：bookword
CREATE TABLE bookword 
(
	id SERIAL PRIMARY KEY,
	book INTEGER  NOT NULL, 
	wordindex INTEGER  NOT NULL, 
	count INTEGER NOT NULL,
    created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP
);

-- 索引：
CREATE INDEX bookword_wordindex ON bookword (wordindex);

