--
-- 由SQLiteStudio v3.1.1 产生的文件 周日 11月 21 21:37:23 2021
--
-- 文本编码：UTF-8
--

-- 表：bold
CREATE TABLE bold (
    id SERIAL PRIMARY KEY,
    book      INTEGER NOT NULL,
    paragraph INTEGER NOT NULL,
    word      TEXT    NOT NULL,
    word2     TEXT    NOT NULL,
    word_en   TEXT,
    pali      TEXT,
    base      TEXT,
	created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP
);

CREATE INDEX bold_bookpara ON bold (book,paragraph);
CREATE INDEX bold_word ON bold (word);
CREATE INDEX bold_word_en ON bold (word_en);

