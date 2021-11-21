-- Your SQL goes here

CREATE TABLE dictionaries (
    id           SERIAL PRIMARY KEY,

    word        VARCHAR (512) NOT NULL,
    type        VARCHAR (512) NOT NULL DEFAULT(''),
    grammar        VARCHAR (512) NOT NULL DEFAULT(''),
    base        VARCHAR (512) NOT NULL DEFAULT(''),
    meaning        VARCHAR (512) NOT NULL DEFAULT(''),
    note        TEXT NOT NULL DEFAULT(''),
    factors        VARCHAR (512) NOT NULL DEFAULT(''),
    factors_meaning        VARCHAR (512) NOT NULL DEFAULT(''),

    lang      VARCHAR (16) NOT NULL DEFAULT('en'),
    sourse        VARCHAR (512) NOT NULL DEFAULT(''),
    owner_id INTEGER NOT NULL, 

	version     INTEGER NOT NULL DEFAULT (1),

    created_at  TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at  TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP
);

CREATE INDEX dictionaries_word ON dictionaries (word);
CREATE INDEX dictionaries_base ON dictionaries (base);
CREATE INDEX dictionaries_lang ON dictionaries (word,lang);
