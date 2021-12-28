-- Your SQL goes here

CREATE TABLE active_time_frames (
    id          SERIAL PRIMARY KEY,
    user_id     INTEGER NOT NULL,
    hit         INTEGER NOT NULL DEFAULT(1),
    timezone    VARCHAR (32) NOT NULL,

    updated_at  TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    created_at  TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP
);
