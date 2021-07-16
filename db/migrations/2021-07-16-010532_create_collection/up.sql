-- Your SQL goes here

CREATE TABLE collections (
    id SERIAL PRIMARY KEY,
    uuid         VARCHAR (36) ,
    title        VARCHAR (32) NOT NULL,
    subtitle     VARCHAR (32),
    summary      VARCHAR (255),
    article_list TEXT,
    status       INTEGER   NOT NULL DEFAULT (10),
    creator_id   INTEGER,
    owner        VARCHAR (36),
    lang         CHAR (8),
	version     INTEGER NOT NULL DEFAULT (1),
    deleted_at  TIMESTAMP,
    created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at  TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP
);
