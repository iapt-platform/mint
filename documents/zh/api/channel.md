# channel

```table
CREATE TABLE channal (
    id           int PRIMARY KEY,
    uuid           CHAR (36),
    owner_id     INTEGER NOT NULL,
    owner        VARCHAR (36) NOT NULL,
    name         VARCHAR (32),
    summary      VARCHAR (255),
    status       INTEGER,
    lang         CHAR (8),
    create_at  BIGINT,
    update_at  BIGINT,
    delete_at BIGINT
);
```

`owner`        VARCHAR (36) NOT NULL

最初的创建者。
旧表中的用户id uuid。新表中改用owner_id(int)。

