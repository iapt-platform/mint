-- Your SQL goes here

CREATE TABLE terms (
    id           SERIAL PRIMARY KEY,
	pr_parent_id    INTEGER,

    word        VARCHAR (512) NOT NULL,
    word_en        VARCHAR (512) NOT NULL ,
    tag        VARCHAR (512) NOT NULL DEFAULT(''),
    channel_id        INTEGER,
    meaning        VARCHAR (512) NOT NULL DEFAULT(''),
    meaning2        VARCHAR (512) NOT NULL DEFAULT(''),
    note        TEXT NOT NULL DEFAULT(''),

    lang      VARCHAR (16) NOT NULL DEFAULT('en'),
    sourse        VARCHAR (512) NOT NULL DEFAULT(''),
	confidence     INTEGER NOT NULL DEFAULT (10),

    owner_id INTEGER NOT NULL, 
	version     INTEGER NOT NULL DEFAULT (1),

    created_at  TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at  TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP
);

CREATE INDEX terms_word ON terms (word);
CREATE INDEX terms_worden ON terms (word_en);
CREATE INDEX terms_lang ON terms (word,lang);
CREATE INDEX terms_channel_id ON terms (word,channel_id);
