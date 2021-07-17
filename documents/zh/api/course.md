# Course 课程

## Table

```
CREATE TABLE courses
(
    id SERIAL PRIMARY KEY,
    cover VARCHAR(255),
    title VARCHAR(32) NOT NULL,
    subtitle VARCHAR(32),
    summary VARCHAR(255),
    teacher INTEGER NOT NULL,
    lang VARCHAR (8),
    speech_lang VARCHAR (8),
    status INTEGER NOT NULL DEFAULT(0),
    lesson_num INTEGER NOT NULL DEFAULT(0),
    content TEXT ,
    creator INTEGER NOT NULL,
    version INTEGER,
    created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP
);
```

```
CREATE TABLE lessons
(
    id SERIAL PRIMARY KEY,
    course_id INTEGER NOT NULL,
    course_uuid VARCHAR(36),
    title VARCHAR(32) NOT NULL,
    subtitle VARCHAR(32),
    summary VARCHAR(255),
    status INTEGER  NOT NULL DEFAULT(0),
    cover VARCHAR(255),
    teacher INTEGER,
    lang VARCHAR(8),
    speech_lang VARCHAR(8),
    start_date TIMESTAMP,
    duration INTEGER,
    content TEXT,
    creator INTEGER  NOT NULL,
    version INTEGER,
    created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP
);
```

## API

GET /api/course/:cid
PUT /api/course/?data=data
POST /api/course/:cid/?data=data
DELETE /api/course/:cid

GET /api/lesson/:lid
GET /api/lessons/:cid
PUT /api/lesson/?data=data
POST /api/lesson/:lid/?data=data
DELETE /api/lesson/:lid
