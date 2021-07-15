# article

## article 文章

```table
CREATE TABLE articles (
    id SERIAL PRIMARY KEY,
    uuid         VARCHAR (36) ,
    title        VARCHAR (32) NOT NULL,
    subtitle     VARCHAR (32),
    summary      VARCHAR (255),
    content      TEXT,
    owner_id     INTEGER  NOT NULL,
    owner        VARCHAR (36),
    setting      JSON,
    status       INTEGER   NOT NULL DEFAULT (10),
	version     INTEGER NOT NULL DEFAULT (1),
    deleted_at  TIMESTAMP,
    created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at  TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP
);

```

`uuid` 旧表中的主键

`setting` json 格式。文章设置

## article_list 关联表

```table
CREATE TABLE article_lists (
    id            INTEGER      PRIMARY KEY AUTOINCREMENT,
    collect_id    VARCHAR (36) NOT NULL REFERENCES collect (id),
    collect_title TEXT,
    article_id    VARCHAR (36)  NOT NULL REFERENCES article (id),
    level         INTEGER  NOT NULL DEFAULT (1),
    title         VARCHAR (64) NOT NULL,
);
```

article 和 collect 的关联表

`level`在目录中的层级 1-8

`title`在目录中的文章标题

## 文集

```table
CREATE TABLE collects (
    id INTEGER PRIMARY KEY,
    uuid         VARCHAR (36) ,
    title        VARCHAR (32) NOT NULL,
    subtitle     VARCHAR (32),
    summary      VARCHAR (255),
    article_list TEXT,
    status       INTEGER   NOT NULL DEFAULT (10),
    owner        CHAR (36),
    lang         CHAR (8),
    create_at  BIGINT,
    update_at  BIGINT,
    delete_at  BIGINT
);
```

uuid 旧表中的主键
