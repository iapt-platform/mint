-- Your SQL goes here

CREATE TABLE ref_dicts (
    id           SERIAL PRIMARY KEY,

    word        VARCHAR (512) NOT NULL,
    word_en        VARCHAR (512) NOT NULL DEFAULT(''),
    meaning        VARCHAR (512) NOT NULL DEFAULT(''),

    lang      VARCHAR (16) NOT NULL DEFAULT('en'),
	ref_dict_name_id     INTEGER NOT NULL DEFAULT (1),

    created_at  TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP
);

CREATE INDEX ref_dicts_word ON ref_dicts (word);
CREATE INDEX ref_dicts_word_en ON ref_dicts (word_en);
