-- Your SQL goes here
CREATE TYPE TStatus AS ENUM ('disable','private','public');
CREATE TYPE TContentType AS ENUM ('text','markdown','html');

CREATE TABLE courses
( 
    id SERIAL PRIMARY KEY, 
    uid VARCHAR(36)  NOT NULL,
    parent_id INTEGER , 
    pr_parent_id INTEGER ,
    
    cover VARCHAR(255), 
    title VARCHAR(255) NOT NULL, 
    subtitle VARCHAR(255),
    summary VARCHAR(1024),

    teacher_id INTEGER, 
    lang      VARCHAR (16) NOT NULL DEFAULT('en'),
    speech_lang VARCHAR (16) NOT NULL DEFAULT('en'),
    lesson_num INTEGER NOT NULL DEFAULT(0), 

    start_at TIMESTAMP ,
    end_at TIMESTAMP ,
    

    content TEXT , 
    content_type TContentType NOT NULL DEFAULT('markdown'), 
    
    status TStatus  NOT NULL DEFAULT('private'), 
    editor_id INTEGER NOT NULL, 
    studio_id INTEGER NOT NULL, 
    owner_id INTEGER NOT NULL, 

    version     INTEGER NOT NULL DEFAULT (1),
    deleted_at TIMESTAMP ,

    created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP
);
CREATE INDEX courses_title ON courses (title);
CREATE INDEX courses_lang ON courses (lang);
CREATE INDEX courses_status ON courses (status);
CREATE INDEX courses_lang_status ON courses (lang,status);
CREATE UNIQUE INDEX courses_uid ON courses (uid);