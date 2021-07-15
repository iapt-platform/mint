# group

## group_info

```table
CREATE TABLE group_info (
    id          int    PRIMARY KEY,
    uuid        VARCHAR (36) ,
    name        VARCHAR (32)    NOT NULL,
    description TEXT,
    status      INTEGER,
    owner_id    INTEGER,
    owner       VARCHAR (36),
    create_at BIGINT      NOT NULL,
    update_at BIGINT NOT NULL,
    delete_at BIGINT 
);
```

`owner`       VARCHAR (36),

最初创建者
旧表中用uuid 新表用 int 取代。数据迁移后删除。

`uuid`        VARCHAR (36) ,

旧表中的主键用uuid 新表用 int 取代。新表中保留此uuid。

## group_member
```table
CREATE TABLE group_member (
    id         INTEGER   PRIMARY KEY AUTOINCREMENT,
    user_id    CHAR (36) NOT NULL,
    group_id   INTEGER   NOT NULL,
    power      INTEGER   NOT NULL
                         DEFAULT (1),
    group_name CHAR (32),
    level      INTEGER   DEFAULT (0),
    status     INTEGER   DEFAULT (1) 
);
```
`user_id` 原表中用uuid  新表替换为 int

`group_name` 与 group_info中的name相同

`power` 目前没有使用

`level` 目前没有使用

`status` 
- 0-禁用
- 1-正常