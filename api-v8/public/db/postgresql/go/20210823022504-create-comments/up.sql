-- Your SQL goes here
CREATE TYPE TCommentStatus AS ENUM ('disable','checking','checked');

CREATE TABLE comments (
    id          SERIAL PRIMARY KEY,
    uid         VARCHAR(36)  NOT NULL,

    parent_id   INTEGER NOT NULL,
    parent_type   TResourceType NOT NULL,

    title       VARCHAR(255)  NOT NULL,

    content     TEXT  NOT NULL,
    content_type TContentType NOT NULL DEFAULT ('markdown'),
    
    status      TCommentStatus  NOT NULL DEFAULT ('checking'),    
    owner_id    INTEGER NOT NULL,
	version     INTEGER NOT NULL DEFAULT (1),

    created_at  TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at  TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP
);


CREATE INDEX comments_title ON comments (title);
CREATE UNIQUE INDEX comments_uid ON comments (uid);

