-- Your SQL goes here

CREATE TABLE groups_users (
    id          SERIAL PRIMARY KEY,
    group_id    INTEGER NOT NULL,
    user_id     INTEGER NOT NULL, 

    created_at  TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP
);

CREATE UNIQUE INDEX group_user_id ON groups_users (group_id,user_id);