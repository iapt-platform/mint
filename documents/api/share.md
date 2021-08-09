# share

```table
CREATE TABLE share_to (
    id SERIAL PRIMARY KEY,
    res_id          INTEGER NOT NULL,
    res_uuid          VARCHAR (36),
    res_type        INTEGER,
    cooperator_id   INTEGER NOT NULL,
    cooperator_uuid   VARCHAR (36),
    cooperator_type INTEGER NOT NULL,
    right           INTEGER NOT NULL DEFAULT 0,
    version INTEGER NOT NULL DEFAULT 0,
    created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP NOT NULL
);
```
分享资源给用户或者群组。

`res_id` 资源id 原来的表中是uuid 导入后改为 int

`res_type` 资源类型 
1. channel
2. wbw doc
3. article
4. collection
   
`cooperator_id` 用户或工作组id

`cooperator_type` 
1. 用户
2. 工作组

`right` 权限
1. 只读
2. 可写
3. 拥有者




```table
CREATE TABLE share_link (
    id SERIAL PRIMARY KEY,
    link_id     VARCHAR (36) UNIQUE,
    res_id      INTEGER NOT NULL,
    res_type    INTEGER,
    right       INTEGER,
    version INTEGER NOT NULL DEFAULT 0,
    created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP NOT NULL
);
```

分享资源链接

`link_id` uuid

`res_id` 资源id 原来的表中是uuid 导入后改为 int

`res_type` 资源类型 
1. channel
2. wbw doc
3. article
4. collection
