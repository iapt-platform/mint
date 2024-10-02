-- Your SQL goes here
CREATE TYPE TActiveType AS ENUM ('dict','wbw','term','sentence','lookup','course','article','collection');

CREATE TABLE active_logs (
    id          SERIAL PRIMARY KEY,
    user_id     INTEGER NOT NULL,
    active_type TActiveType  NOT NULL, 
    content     TEXT,
    timezone    VARCHAR (32) NOT NULL,

    created_at  TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP
);
