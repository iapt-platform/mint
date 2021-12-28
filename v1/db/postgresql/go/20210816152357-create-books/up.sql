-- Your SQL goes here

CREATE TABLE books (
    id           SERIAL PRIMARY KEY,

    title        VARCHAR (255) NOT NULL,
    summary      VARCHAR (1024),
    lang      VARCHAR (16) NOT NULL DEFAULT('en'),

    channel_id INTEGER NOT NULL, 

    status TStatus  NOT NULL DEFAULT('private'), 
    owner_id INTEGER NOT NULL, 

	version     INTEGER NOT NULL DEFAULT (1),
    deleted_at  TIMESTAMP,

    created_at  TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at  TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP
);

CREATE INDEX books_title ON books (title);
CREATE INDEX books_status ON books (status);
CREATE INDEX books_lang ON books (lang);
CREATE INDEX books_lang_status ON books (lang,status);
