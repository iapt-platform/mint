# 用户字典
```table
CREATE TABLE user_wbw_dicts (
    id SERIAL PRIMARY KEY,
    pali        TEXT    NOT NULL,
    type        TEXT,
    gramma      TEXT,
    parent      TEXT,
    mean        TEXT,
    note        TEXT,
    factors     TEXT,
    factormean  TEXT,
    status      INTEGER,
    lang    VARCHAR(8),
    confidence  INTEGER DEFAULT (100),
    creator     INTEGER NOT NULL,
    ref_counter INTEGER DEFAULT (1) 
    created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP
    updated_at TIMESTAMP NOT NULL

);

```

PUT /api/dict/user

body
```
{
    pali:
    type:
    gramma:
    parent:
    mean:
    note:
    factors:
    factormean:
    lang:
    confidence:
}
```
>提交时查重。如果    pali:
    type:
    gramma:
    parent:
    mean:
    note:
    factors:
    factormean:
合在一起有重复，就、改变引用次数。没有重复，添加新纪录。

# 用户字典引用索引
```table
CREATE TABLE user_wbw_dict_indexs (
    id SERIAL PRIMARY KEY,
    word_index  INTEGER NOT NULL,
    user_id     INTEGER NOT NULL,
    created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
);
```

# 系统字典+第三方字典


# 参考字典


# 参考字典索引
