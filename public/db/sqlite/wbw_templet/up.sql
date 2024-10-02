--
-- 由SQLiteStudio v3.1.1 产生的文件 周二 11月 16 16:29:17 2021
--
-- 文本编码：UTF-8
--

CREATE TABLE wbw_templet 
( 
	id INTEGER PRIMARY KEY AUTOINCREMENT,
	book INTEGER  NOT NULL, 
	paragraph INTEGER  NOT NULL, 
	wid INTEGER  NOT NULL, 
	word VARCHAR (1024),
	real VARCHAR (1024),
	type VARCHAR (64),
	gramma VARCHAR (64),
	part VARCHAR (1024),
	style VARCHAR (64)
);

-- 索引：search
CREATE INDEX wbw_templet_book ON wbw_templet ("book", "paragraph", "wid");
CREATE INDEX wbw_templet_book1 ON wbw_templet ("book", "paragraph");
