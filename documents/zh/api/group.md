# group

## group_info

```table
CREATE TABLE groups (
    id SERIAL PRIMARY KEY,
    uuid        VARCHAR (36) ,
    name        VARCHAR (32)    NOT NULL,
    description TEXT,
    status      INTEGER,
    owner_id    INTEGER,
    owner_uuid       VARCHAR (36),
	version     INTEGER NOT NULL DEFAULT (1),
    deleted_at  TIMESTAMP,
    created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at  TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP
);
```

旧的表叫group_info

`owner`       VARCHAR (36),

最初创建者
旧表中用uuid 新表用 int 取代。数据迁移后删除。

`uuid`        VARCHAR (36) ,

旧表中的主键用uuid 新表用 int 取代。新表中保留此uuid。

## group_member
```table
CREATE TABLE group_member (
    id SERIAL PRIMARY KEY,
    user_id    INTEGER NOT NULL,
    group_id   INTEGER   NOT NULL,
    right      INTEGER   NOT NULL
                         DEFAULT (1),
    group_name VARCHAR (32),
    status     INTEGER   DEFAULT (10) 
	version     INTEGER NOT NULL DEFAULT (1),
    created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at  TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP
);
```
`user_id` 原表中用uuid  新表替换为 int

`group_name` 与 group_info中的name相同

`right` 权限，目前没有使用

`status` 
- 0-禁用
- 1-正常