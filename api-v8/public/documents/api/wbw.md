## 逐词解析
### wbw_block
```
CREATE TABLE wbw_block (
    id SERIAL PRIMARY KEY,
    uuid           VARCHAR(36),
    parent_id      UUID,
    channal_id     INTEGER NOT NULL,
    channal        UUID,
    parent_channel_id INTEGER NOT NULL,
    parent_channel UUID,
    owner_id       INTEGER NOT NULL,
    owner          UUID,
    book           INTEGER  NOT NULL  DEFAULT 0,
    paragraph      INTEGER  NOT NULL  DEFAULT 0,
    style          VARCHAR (16),
    lang           VARCHAR (8),
    status         INTEGER,
    deleted_at TIMESTAMP,
    version INTEGER NOT NULL DEFAULT 0,
    created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP NOT NULL
);
```
#### `parent_id`
上游id
#### `channal`
channel
#### `parent_channel`
从哪个channel 复制的
#### `owner`
最初拥有者
#### `book`
书号
#### 'paragraph'
段落号


### 逐词解析数据 wbw-data
```
CREATE TABLE wbw_datas (
    id SERIAL PRIMARY KEY,
    block_id     INTEGER NOT NULL,
    block_uuid     VARCHAR(36),
    book         INTEGER NOT NULL,
    paragraph    INTEGER NOT NULL,
    wid          INTEGER NOT NULL,
    word         TEXT,
    data         TEXT,
    status       INTEGER,
    owner_id     INTEGER NOT NULL,
    owner        VARCHAR(36),
    deleted_at TIMESTAMP,
    created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP NOT NULL
);

```

`id` 原表uuid 不需要导入因为没有其他表链接到这个id

`block_id`
与wbw_block表关联的字段

`data`
单词数据 xml格式


### API
GET api/wbw/:channel/:book/:para/

PUT api/wbw/:channel/:book/:para/

POST api/wbw/:channel/:book/:para/

DELETE api/wbw/:channel/:book/:para/