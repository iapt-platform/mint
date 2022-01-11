# channel

```table
CREATE TABLE channels (
    id SERIAL PRIMARY KEY,
    uuid         VARCHAR (36),
    owner_id     INTEGER NOT NULL,
    owner        VARCHAR (36) NOT NULL,
    name         VARCHAR (32),
    summary      VARCHAR (255),
    status       INTEGER,
    lang         VARCHAR (8),
	version     INTEGER NOT NULL DEFAULT (1),
    deleted_at  TIMESTAMP,
    created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at  TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP
);
```

`owner` VARCHAR (36) NOT NULL

最初的创建者。
旧表中的用户 id uuid。新表中改用 owner_id(int)。
