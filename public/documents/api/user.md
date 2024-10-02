# 用户

## users

用户信息

旧表：user_info

```
CREATE TABLE users (
    id           INTEGER    PRIMARY KEY AUTOINCREMENT,
    userid       TEXT       UNIQUE,
    path         CHAR (36),
    username     TEXT (64)  NOT NULL,
    password     TEXT       NOT NULL,
    nickname     TEXT (64)  NOT NULL,
    email        TEXT (256),
    create_time  INTEGER,
    modify_time  INTEGER,
    receive_time INTEGER,
    setting      TEXT
);
```

#### `id`

INTEGER 唯一自增 id

#### `userid`

CHAR (36) uuid

#### `path`

CHAR (36) 用户在服务器上私有文件的路径

#### `username`

TEXT (64) 用户登录名

#### `password`

TEXT 密码 md5 加密

#### `nickname`

TEXT (64) 昵称

#### `email`

TEXT (256) 电邮地址

#### create_time

INTEGER 账户建立时间

#### `modify_time`

INTEGER 数据修改时间

#### `receive_time`

INTEGER 服务器收到此数据时间

#### `setting`

TEXT 用户设置 json 数据

## profile

用户简历

```
CREATE TABLE user_profile (
    id SERIAL PRIMARY KEY,
    user_id   CHAR (36),
    bio       TEXT,
    lang      CHAR (8),
    isdefault INTEGER,
    email     TEXT
);
```

#### `id`

INTEGER 唯一自增 id

#### `user_id`

CHAR (36) uuid

#### `bio`

TEXT

#### `lang`

简介语言，一个用户可以建立多个语言版本的简历。用户的显示与用户的语言设置有关。不能匹配到相同语言时，匹配相同语族，还是不行，就显示默认记录。

#### `isdefault`

是否是默认记录。

#### `email`

电邮地址

## 编辑记录索引 active_index

用户行为记录。以日期计算的使用与编辑有关功能的时间和操作次数

```table
CREATE TABLE event_day_frames (
    id SERIAL PRIMARY KEY,
    user_id  INTEGER NOT NULL,
    user_uuid  VARCHAR (36),
    date     TIMESTAMP NOT NULL,
    duration INTEGER NOT NULL,
    hit      INTEGER NOT NULL
);
```

`date` 日期

`duration` 持续时间 毫秒

`hit` 操作次数

## 编辑记录 edit_records
将十分钟内的编辑行为组块,时间段

```table
CREATE TABLE event_time_frames (
    id SERIAL PRIMARY KEY,
    user_id  INTEGER NOT NULL,
    user_uuid  VARCHAR (36),
    start_at    TIMESTAMP,
    end_at    TIMESTAMP,
    duration INTEGER,
    hit      INTEGER   DEFAULT (0),
    timezone INTEGER   DEFAULT (0)
);
```

## 编辑行为记录

```table
CREATE TABLE event_logs (
    id SERIAL PRIMARY KEY,
    user_id  INTEGER NOT NULL,
    active   INTEGER NOT NULL,
    content  TEXT,
    timezone INTEGER,
    created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP
);
```

只添加不删除

### api

`PUT /api/useractive/log`

body:

```
{
    active:aid,
    content:text,
    timezone:timezone,
}
```
