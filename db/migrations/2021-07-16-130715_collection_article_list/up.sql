-- article 关联表
CREATE TABLE article_lists (
    id SERIAL PRIMARY KEY,
    collection_id    INTEGER NOT NULL ,
    article_id    INTEGER  NOT NULL ,
    created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP
);

