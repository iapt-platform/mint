-- Your SQL goes here

CREATE TABLE collections (
    id SERIAL PRIMARY KEY,
    uid         VARCHAR (36) ,
    parent_id   INTEGER NOT NULL,
    pr_parent_id INTEGER NOT NULL,

    title        VARCHAR (255) NOT NULL,
    subtitle     VARCHAR (255),
    summary      VARCHAR (1024),

    article_list JSON NOT NULL DEFAULT('[]'), 
    
    lang      VARCHAR (16),
    setting      JSON NOT NULL DEFAULT('{}'), 

    status TStatus  NOT NULL DEFAULT('private'), 
    owner_id INTEGER NOT NULL, 

	version     INTEGER NOT NULL DEFAULT (1),
    deleted_at  TIMESTAMP,
    created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at  TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP
);

CREATE INDEX collections_title ON courses (title);
CREATE INDEX collections_lang ON courses (lang);
CREATE INDEX collections_status ON courses (status);
CREATE INDEX collections_lang_status ON courses (lang,status);
CREATE UNIQUE INDEX collections_uid ON courses (uid);