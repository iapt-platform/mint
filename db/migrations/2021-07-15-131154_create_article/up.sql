-- Your SQL goes here

CREATE TABLE articles (
    id SERIAL PRIMARY KEY,
    uuid         VARCHAR (36) ,
    title        VARCHAR (32) NOT NULL,
    subtitle     VARCHAR (32),
    summary      VARCHAR (255),
    content      TEXT,
    owner_id     INTEGER  NOT NULL,
    owner        VARCHAR (36),
    setting      JSON,
    status       INTEGER   NOT NULL DEFAULT (10),
	version     INTEGER NOT NULL DEFAULT (1),
    deleted_at  TIMESTAMP,
    created_at  TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at  TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP
);
