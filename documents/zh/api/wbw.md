## 逐词解析
### wbw_block
```
CREATE TABLE wbw_block (
    id             UUID    PRIMARY KEY,
    parent_id      UUID,
    channal        UUID,
    parent_channel UUID,
    owner          UUID,
    book           INTEGER,
    paragraph      INTEGER,
    style          VARCHAR (16),
    lang           VARCHAR (8),
    status         INTEGER,
    update_time  INTEGER,
    delete_time  INTEGER,
    create_time  INTEGER
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


### wbw-data
```
CREATE TABLE wbw_data (
    id           UUID PRIMARY KEY,
    block_id     UUID,
    book         INTEGER,
    paragraph    INTEGER,
    wid          INTEGER,
    word         TEXT,
    data         XML,
    status       INTEGER,
    owner        UUID,
    update_time  INTEGER,
    delete_time  INTEGER,
    create_time  INTEGER
);

```
#### `block_id`
与wbw_block表关联的字段
#### `data`
单词数据 xml格式

### API
GET api/wbw/:channel/:book/:para/
PUT api/wbw/:channel/:book/:para/
POST api/wbw/:channel/:book/:para/
DELETE api/wbw/:channel/:book/:para/