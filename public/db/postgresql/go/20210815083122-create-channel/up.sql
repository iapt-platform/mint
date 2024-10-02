-- Your SQL goes here

CREATE TABLE channels (
    id           SERIAL PRIMARY KEY,
    uid          VARCHAR (36) ,

    title        VARCHAR (255) NOT NULL,
    summary      VARCHAR (1024),

    lang      VARCHAR (16) NOT NULL DEFAULT('en'),
    setting      JSON NOT NULL DEFAULT('{}'), 

    status TStatus  NOT NULL DEFAULT('private'), 
    owner_id INTEGER NOT NULL, 

	version     INTEGER NOT NULL DEFAULT (1),
    deleted_at  TIMESTAMP,

    created_at  TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at  TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP
);

CREATE INDEX channels_title ON channels (title);
CREATE INDEX channels_status ON channels (status);
CREATE INDEX channels_lang ON channels (lang);
CREATE INDEX channels_lang_status ON channels (lang,status);

CREATE UNIQUE INDEX channels_uid ON channels (uid);