# 译文

## 句子块 sent_block

```table
CREATE TABLE sent_block (
    id SERIAL PRIMARY KEY,
    uid         VARCHAR (36),
    book        INTEGER,
    paragraph   INTEGER,
    owner_id    INTEGER NOT NULL,
    lang        VARCHAR (16),
    status      INTEGER,
    created_at  TIMESTAMP,
    updated_at  TIMESTAMP,
    deleted_at  TIMESTAMP
);
```

`book` 书号

`paragraph` 书号

`author`

编辑者列表。还没设计好。 可能是这样

```
[
    {"creater":"bhikkhu bodhi"},
    {"translater":"bhikkhu kosalla"},
]
```

`editor` 数据提交者 旧表中用 user_name 新表用 editor_id

`owner` 记录的创建者

## sentence

```table
CREATE TABLE sentence (
    id           SERIAL PRIMARY KEY,
    parent_id    INTEGER,
    block_id     INTEGER,
    channel_id   INTEGER,
    book         INTEGER   NOT NULL,
    paragraph    INTEGER   NOT NULL,
    start        INTEGER   NOT NULL,
    end          INTEGER   NOT NULL,
    author       TEXT,
    editor_id    INTEGER,
    owner_id     INTEGER,
    text         TEXT,
    language     VARCHAR (16),
    status       INTEGER,
    strlen       INTEGER,
    created_at    TIMESTAMP  NOT NULL,
    updated_at    TIMESTAMP   NOT NULL,
    deleted_at    TIMESTAMP,

);
```

`uuid` 原表的主键 新表中用 int 代替

`channel_id` 外键 原表 channal uuid 导入后应改为 int

`editor_id` 记录的上传者 外键 原表用 uuid 现在改用 int

`text` 句子文本

`version` 用于乐观锁 每次更新+1

`strlen` 句子字符数

```table
CREATE TABLE sent_pr (
    id           SERIAL      PRIMARY KEY AUTOINCREMENT,
    book         INTEGER      NOT NULL,
    paragraph    INTEGER      NOT NULL,
    start      INTEGER      NOT NULL,
    end        INTEGER      NOT NULL,
    channel_id      VARCHAR (36),
    author       VARCHAR (40),
    editor_id       VARCHAR (36),
    text         TEXT,
    language     VARCHAR (8),
    status       INTEGER,
    strlen       INTEGER,
    create_at    BIGINT  NOT NULL,
    update_at    BIGINT   NOT NULL,
    delete_at    BIGINT,
);

```

# 修改历史

```table
CREATE TABLE sent_historay (
    sent_id  CHAR (36),
    user_id  CHAR (36),
    text     TEXT,
    created_at     BIGINT NOT NULL,
    landmark VARCHAR(32)
);
```

`sent_id` 外键 导入后改为 int

`user_id` 外键 导入后改为 int

`text` 句子文本

`landmark` 里程碑标记
