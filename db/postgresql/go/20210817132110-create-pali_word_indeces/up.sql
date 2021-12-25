-- Your SQL goes here
-- 93 万词
CREATE TABLE pali_word_indexs (
    id           SERIAL PRIMARY KEY,
    word    VARCHAR(8) NOT NULL, 
    word_en    VARCHAR(8) NOT NULL, 

    count      INTEGER NOT NULL, 
    normal     INTEGER NOT NULL, 
    bold       INTEGER NOT NULL, 
    is_base    BOOL NOT NULL, 
    str_len       INTEGER NOT NULL, 

    created_at   TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP
);


CREATE INDEX pali_word_indexs_word ON pali_word_indexs (word);
CREATE INDEX pali_word_indexs_word_en ON pali_word_indexs (word_en);
