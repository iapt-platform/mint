-- Your SQL goes here

CREATE TABLE groups (
    id           SERIAL PRIMARY KEY,
    uid          VARCHAR (36) ,

    name         VARCHAR (255) NOT NULL,

    description      TEXT,
    description_type TContentType NOT NULL DEFAULT('markdown'), 
    
    setting      JSON NOT NULL DEFAULT('{}'), 

    status       TStatus  NOT NULL DEFAULT('private'), 
    owner_id     INTEGER NOT NULL, 

	version     INTEGER NOT NULL DEFAULT (1),
    deleted_at  TIMESTAMP,

    created_at  TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at  TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP
);

CREATE INDEX groups_name ON groups (name);
CREATE INDEX groups_status ON groups (status);

CREATE UNIQUE INDEX groups_uid ON groups (uid);