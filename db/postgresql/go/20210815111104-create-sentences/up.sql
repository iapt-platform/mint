-- Your SQL goes here
CREATE TYPE TSentenceType AS ENUM ('origin','translation','relation');

CREATE TABLE sentences (
    id           SERIAL PRIMARY KEY,
    is_pr        BOOL  DEFAULT(FALSE),
    block_id     INTEGER ,

    channel_id   INTEGER ,
    book_id      INTEGER  NOT NULL,
    paragraph    INTEGER  NOT NULL,
    word_start        INTEGER  NOT NULL,
    word_end        INTEGER  NOT NULL,

    content      TEXT,
    content_type TContentType NOT NULL DEFAULT('markdown'), 
    
    type         TSentenceType NOT NULL DEFAULT('translation'), 
    lang         VARCHAR (16) NOT NULL DEFAULT('en'),
    status TStatus  NOT NULL DEFAULT('private'), 

    editor_id INTEGER  NOT NULL,
    owner_id INTEGER  NOT NULL,

	version     INTEGER NOT NULL DEFAULT (1),
    deleted_at  TIMESTAMP,

    created_at  TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at  TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP
);


CREATE INDEX sentences_unique ON sentences ("channel_id","book_id","paragraph","word_start","word_end");

