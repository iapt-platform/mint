-- Your SQL goes here

CREATE TABLE articles (
    id           SERIAL PRIMARY KEY,
    uid          VARCHAR (36) ,
    parent_id    INTEGER ,
    pr_parent_id INTEGER ,

    title        VARCHAR (255) NOT NULL,
    subtitle     VARCHAR (255),
    summary      VARCHAR (1024),

    content      TEXT,
    content_type TContentType NOT NULL DEFAULT('markdown'), 
    
    lang      VARCHAR (16) NOT NULL DEFAULT('en'),
    setting      JSON NOT NULL DEFAULT('{}'), 

    status TStatus  NOT NULL DEFAULT('private'), 
    owner_id INTEGER NOT NULL, 

	version     INTEGER NOT NULL DEFAULT (1),
    deleted_at  TIMESTAMP,

    created_at  TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at  TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP
);

CREATE INDEX articles_title ON courses (title);
CREATE INDEX articles_status ON courses (status);
CREATE INDEX articles_lang ON courses (lang);
CREATE INDEX articles_lang_status ON articles (lang,status);

CREATE UNIQUE INDEX articles_uid ON courses (uid);