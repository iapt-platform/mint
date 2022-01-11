-- Your SQL goes here

CREATE TABLE wbw_templates (
    id           SERIAL PRIMARY KEY,

    book_id      INTEGER NOT NULL, 
    paragraph    INTEGER NOT NULL, 
    word_sn        INTEGER NOT NULL, 

    word        VARCHAR (512) NOT NULL,
    real_word          VARCHAR (512) NOT NULL,
    type          VARCHAR (32),
    grammar          VARCHAR (32),
    factors          VARCHAR (512),

    created_at  TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP
);


CREATE UNIQUE INDEX wbw_templates_unique ON wbw_templates (book_id,paragraph,word_sn);
