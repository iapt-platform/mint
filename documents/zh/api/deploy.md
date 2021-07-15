# 运维

```table
CREATE TABLE setting (
    [key]     VARCHAR(32) PRIMARY KEY,
    value     VARCHAR(64) NOT NULL,
    [default] VARCHAR(64)
);
```
## 逐词解析编辑消息message
```table
CREATE TABLE message (
    id        INTEGER   PRIMARY KEY AUTOINCREMENT,
    sender    TEXT      NOT NULL,
    user_id    INTEGER      NOT NULL,
    type      INTEGER   NOT NULL,
    book      INTEGER,
    paragraph INTEGER,
    data      TEXT,
    doc_id    VARCHAR (36),
    time      BIGINT
);
```

`time` 消息发送时间