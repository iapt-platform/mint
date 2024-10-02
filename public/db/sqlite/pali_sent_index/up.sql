
-- 表：pali_sent_index
CREATE TABLE pali_sent_index (
	id SERIAL PRIMARY KEY,
    book   INTEGER,
    para   INTEGER,
    strlen INTEGER,
    created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP
);

CREATE INDEX pali_sent_index_book ON pali_sent_index (book, para);
