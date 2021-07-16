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
    id SERIAL PRIMARY KEY,
    collect_id    INTEGER NOT NULL REFERENCES collections (id),
    article_id    INTEGER  NOT NULL REFERENCES articles (id),
);
```

article 和 collect 的关联表


## 文集

```table
CREATE TABLE collections (
    id SERIAL PRIMARY KEY,
    uuid         VARCHAR (36) ,
    title        VARCHAR (32) NOT NULL,
    subtitle     VARCHAR (32),
    summary      VARCHAR (255),
    article_list TEXT,
    status       INTEGER   NOT NULL DEFAULT (10),
    creator_id   INTEGER,
    owner        VARCHAR (36),
    lang         CHAR (8),
	version     INTEGER NOT NULL DEFAULT (1),
    deleted_at  TIMESTAMP,
    created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at  TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP
);
```

`uuid` 旧表中的主键

`creator_id` 创建者。原来的表中是 owner uuid 导入后更新为int 然后删除owner