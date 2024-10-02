-- Your SQL goes here
CREATE TYPE TLikeType AS ENUM ('like','favorite','watch');

CREATE TABLE likes (
    id           SERIAL PRIMARY KEY,
    like_type    TLikeType  NOT NULL,

    resource_id   INTEGER  NOT NULL,
    resource_type  TResourceType NOT NULL,

    user_id   INTEGER  NOT NULL,
    
    emoji      VARCHAR(8),

    created_at  TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP
);

CREATE UNIQUE INDEX likes_unique ON likes (like_type,resource_id,resource_type,user_id);



