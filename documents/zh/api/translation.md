# 译文

## 句子块sent_block

```table
CREATE TABLE sent_block (
    id           int PRIMARY KEY,
    uuid         CHAR (36),
    parent_id    CHAR (36),
    book         INTEGER,
    paragraph    INTEGER,
    owner_id     INTEGER NOT NULL,
    owner        CHAR (36) NOT NULL,
    lang         CHAR (8),
    author       CHAR (50),
    editor_id    INTEGER,
    editor       TEXT,
    status       INTEGER,
    create_at  BIGINT,
    update_at  BIGINT,
    delete_at  BIGINT
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
    id           int PRIMARY KEY,
    uuid           CHAR (36) ,
    parent       CHAR (36),
    block_id     CHAR (36),
    channel_id      CHAR (36),
    book         INTEGER   NOT NULL,
    paragraph    INTEGER   NOT NULL,
    start      INTEGER   NOT NULL,
    end        INTEGER   NOT NULL,
    author       TEXT,
    editor_id       CHAR (36),
    text         TEXT,
    language     CHAR (8),
    version      INTEGER,
    status       INTEGER,
    strlen       INTEGER,
    create_at    BIGINT  NOT NULL, 
    update_at    BIGINT   NOT NULL,
    delete_at    BIGINT,

);
```
`uuid` 原表的主键 新表中用int代替

`channel_id` 外键 原表 channal uuid 导入后应改为int

`editor_id` 记录的上传者 外键 原表用 uuid 现在改用 int

`text` 句子文本 

`version` 用于乐观锁 每次更新+1

`strlen` 句子字符数


```table
CREATE TABLE sent_pr (
    id           INTEGER      PRIMARY KEY AUTOINCREMENT,
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