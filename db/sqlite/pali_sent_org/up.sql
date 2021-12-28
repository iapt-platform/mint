-- 表：pali_sent
CREATE TABLE pali_sent_org (
    id SERIAL PRIMARY KEY,
    book      INTEGER,
    paragraph INTEGER,
    word_begin   INTEGER,
    word_end     INTEGER,
    length    INTEGER,
    count     INTEGER,
    text      TEXT,
    html      TEXT,
	cell      INTEGER NOT NULL DEFAULT (1),
    sim_sents TEXT,
    sim_sents_count INTEGER NOT NULL DEFAULT (0),
    created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP
);

-- 索引：sentId
CREATE INDEX pali_sent_org_uid ON pali_sent_org (book, paragraph, word_begin, word_end);

