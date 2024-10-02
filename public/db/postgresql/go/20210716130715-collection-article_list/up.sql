-- article 关联表
CREATE TABLE article_lists (
    id SERIAL PRIMARY KEY,
    collection_id    INTEGER NOT NULL ,
    article_id    INTEGER  NOT NULL ,
    created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP
);
CREATE UNIQUE INDEX article_lists_article_collection ON article_lists (collection_id,article_id);
