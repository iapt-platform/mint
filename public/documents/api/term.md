# 术语

```table
CREATE TABLE terms (
    id SERIAL PRIMARY KEY,
    uuid          VARCHAR (36),
    word          VARCHAR (256),
    word_en       VARCHAR (256),
    tag           VARCHAR (128),
    meaning       TEXT,
    other_meaning TEXT,
    note          TEXT,
    channal       VARCHAR (36),
    owner         VARCHAR (36),
    lang          VARCHAR (8),
    hit           INTEGER  DEFAULT (0),
	version     INTEGER NOT NULL DEFAULT (1),
    deleted_at  TIMESTAMP,
    created_at  TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at  TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP
);
```
