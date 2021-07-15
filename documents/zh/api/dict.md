# 用户逐词解析字典
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

//引用索引
CREATE TABLE user_wbw_dict_indexs (
    id SERIAL PRIMARY KEY,
    word_index  INTEGER NOT NULL,
    user_id     INTEGER NOT NULL,
    created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
);

```


## API
### 存储一个单词
`PUT /api/dict/user`

body
```
{
    "pali":string
    "type":string
    gramma:string
    parent:string
    mean:string
    note:string
    parts:string
    partmean:string
    lang:string
    confidence:int (1-10)
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
合在一起有重复，就、改变引用次数,并更新引用索引。没有重复，添加新纪录。

### 查社区字典
`GET /api/dict/wbw/word/:word`
返回值：
```json
[
    {
        "id":int,
        "word":string,
        "type":string,
        "gramma":string,
        "mean":string,
        "parent":string,
        "parts":string,
        "creator":string,
        "ref_count":int,
    }
]
```

# 系统字典+第三方字典

字段同逐词解析字典。原始数据[csv文件](/dicttext/readme.md)。全部导入redis。

# 参考字典

>PCED 中的字典

```table
CREATE TABLE dict_ref (
    id SERIAL PRIMARY KEY,
    lang VARCHAR(8),
    dict_id  INTEGER,
    eword    VARCHAR(255),
    word     VARCHAR(255),
    mean     TEXT
);
```
字典名称表

```table
CREATE TABLE dict_ref_name (
    id SERIAL PRIMARY KEY,
    lang VARCHAR(8),
    shortname VARCHAR(16),
    name      VARCHAR(255)
);
```
## API

### 词头列表
`GET /api/dict/ref/head/:word`
返回值：
```json
[
    {
        "id":int,
        "word":string,
        "count"int,
    }
]
```
### 简要意思列表
`GET /api/dict/ref/firstmean`
参数：?lang=en word=word1,word2……wordn

返回值：
```json
[
    {
        "id":int,
        "word":string,
        "count"int,
    }
]
```

### 单词查询

`GET /api/dict/ref/word/:word`

返回值：
```json
[
    {
        "id":int,
        "word":string,
        "dict_id":int,
        "mean":string,
    }
]
```

### 内文查询

`GET /api/dict/ref/content/:word`

返回值：
```json
[
    {
        "id":int,
        "word":string,
        "dict_id":int,
        "mean":string,
    }
]
```

# 逐词解析自动查字典
>逐词解析以两种方式查字典。按照段落，和单个单词。段落匹配用于逐词解析批量查词填充。查单个单词用于鼠标移入单词区域或拆分改变。

## API

### 以段落为单位查词

`GET /api/dict/wbw/para/:book/:para`

返回：
```json
[
    {
        "pali":"value",
        "type":"value",
        "gramma":"value",
        "mean":"value",
        "parts":"value",
        "partmean":"value",
        "note":"value",
        "lang":"value",
        "pali":"value",
        "pali":"value",
        "pali":"value",
    }
]
```

### 以单词为单位查

`GET /api/dict/wbw/word`

参数：?word=word1,word2……wordn

返回：

与以段落为单位查词相同



