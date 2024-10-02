-- Your SQL goes here

CREATE TABLE dictionaries_users (
    id          SERIAL PRIMARY KEY,
    dictionaries_id    INTEGER NOT NULL,
    user_id     INTEGER NOT NULL, 

    created_at  TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP
);

CREATE UNIQUE INDEX dictionaries_user_id ON dictionaries_users (dictionaries_id,user_id);