# Course课程

## Table

```
CREATE TABLE course 
( 
    id SERIAL PRIMARY KEY, 
    cover BYTEA, 
    title VARCHAR(32) NOT NULL, 
    subtitle VARCHAR(32),
    summary VARCHAR(255),
    teacher UUID NOT NULL, 
    tag VARCHAR(255), 
    lang VARCHAR (8), 
    speech_lang VARCHAR (8), 
    status INTEGER, 
    lesson_num INTEGER, 
    creator UUID NOT NULL, 
    create_time INTEGER, 
    update_time INTEGER, 
    delete_time INTEGER , 
    content TEXT
)
```

```
CREATE TABLE 'lesson' 
( 
    id SERIAL PRIMARY KEY, 
    course_id INTEGER,
    title VARCHAR(32) NOT NULL, 
    subtitle VARCHAR(32), 
    creator CHAR (36), 
    tag VARCHAR(255), 
    summary VARCHAR(255), 
    status INTEGER, 
    cover BYTEA, 
    teacher UUID NOT NULL, 
    lang VARCHAR (8), 
    speech_lang VARCHAR (8), 
    create_time INTEGER, 
    modify_time INTEGER, 
    receive_time INTEGER , 
    'date' INTEGER, 
    'duration' INTEGER, 
    'content' TEXT
)

```