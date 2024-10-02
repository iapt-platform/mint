-- Your SQL goes here
CREATE TABLE studios (
    id          SERIAL PRIMARY KEY,
    uid         VARCHAR(36)  NOT NULL,

    name       VARCHAR(255)  NOT NULL,

    profile     TEXT  NOT NULL DEFAULT (''),
    profile_type TContentType NOT NULL DEFAULT ('markdown'),
    
    status      TStatus  NOT NULL DEFAULT ('public'),
    creator_id    INTEGER NOT NULL,
	version     INTEGER NOT NULL DEFAULT (1),

    created_at  TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at  TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP
);


CREATE UNIQUE INDEX studios_name ON studios (name);
CREATE UNIQUE INDEX studios_uid ON studios (uid);

