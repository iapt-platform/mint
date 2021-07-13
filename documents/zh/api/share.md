# share

```table
CREATE TABLE share_cooperator (
    id              INTEGER      PRIMARY KEY AUTOINCREMENT,
    res_id          VARCHAR (36),
    res_type        INTEGER,
    cooperator_id   VARCHAR (36),
    cooperator_type INTEGER,
    right           INTEGER,
    create_at     INTEGER NOT NULL,
    update_at     INTEGER NOT NULL,
    delete_at      INTEGER
);
```

```table
CREATE TABLE share_link (
    id          INTEGER      PRIMARY KEY AUTOINCREMENT,
    link_id     VARCHAR (36) UNIQUE,
    res_id      VARCHAR (36),
    res_type    INTEGER,
    power       INTEGER,
    create_at     INTEGER NOT NULL,
    update_at     INTEGER NOT NULL,
    delete_at      INTEGER
);
```

