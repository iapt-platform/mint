# wikipali demo wikipali 的功能演示

## Breif Introduction 简介

wikipali 的功能演示。用 php pdo js html css3 写成。没有使用任何框架。jQuray 除外。

wikipali demo build by php pdo js html css3 jQuray . no any framework.

本仓库为 demo 测试版代码，bug 多多，供设计者提交设计思路，仅供测试和功能演示，并非最终代码。

This is the demo code for disigners’ submit, but only for test and check not the final code.

请注意，我们的协作仅仅在 Github 上进行，但我们会让【码云】上的代码与 github 保持强制同步，这会覆盖不知情者在【码云】上的修改。

Attention please! we collaborate on Github only, but we will keep the synchronization of the code on Gitee with Github, which will overwrite unknowers’ commits.

## Resources 相关资源

### web demo 网站演示版

visit [www.wikipali.org/demo](https://www.wikipali.org/demo) to scan, test and check for the developer only, not for the normal users.

访问[www.wikipali.org/demo](https://www.wikipali.org/demo) 仅仅是针对开发者来进行浏览、测试和检查的，而不是面向普通用户。

### code for wikipali website Demo 网站演示版代码

1. If anyone want to push his github code to the server, please contact with Ven. Bhikkhu Visuddhinanda and Ven. Bhikkhu Kosalla by wechat or teams.
   想要将其 github 上的代码推送至服务器，请微信联络 Bhikkhu Visuddhinanda 和 Bhikkhu Kosalla.
2. download and install "gitbash", "TortoiseGit" and "github desktop" and make the submit and communication esay.
   下载并安装"gitbash", "TortoiseGit"和"github desktop"来进行代码提交，这样相互交流更加便捷。

### videos 视频

[functions demo 功能演示](https://www.youtube.com/playlist?list=PL_1iJBQvNPFFNLOaZh2u3VwDYUyJuK_xa)

[Example Class 课程范例](https://www.youtube.com/playlist?list=PL_1iJBQvNPFHwP1ZL4sbhtJTnYeMiEm29)

[conference of translating platform 三藏翻译会议简报](https://www.youtube.com/playlist?list=PL_1iJBQvNPFHT6UisME_cOSts5fFecK14)

Due to the unstable connection the online video conference had been influnced, organizers re-recorded this presentation by screen shot after the conference.
由于会议现场网络出现故障，导致会议直播时断时续，为保证质量，会务组于会后以录屏的方式进行了重新录制。

[collection of conference papers 会议文集](https://drive.google.com/file/d/1CDPKLXMUX30IXc27BeNleHI3uI5OhsFL/view?usp=sharing)（2020.2.12-14）

# Summary of Database 数据库总体描述

## 不包含用户数据的

- 字典数据库
  - 标准字典（逐词解析下拉菜单用的字典数据）
    - 第三方字典
      appdata\dict\3rd
    - 标准变形
      appdata\dict\system\sys_regular.db
    - 非标准变形
      appdata\dict\system\sys_regular.db
  - 参考字典（字典模块用的字典数据）
- 三藏全文 Full Text of nonPali Canon
  - 逐词解析模板 Word By Word Translation
    appdata/palicaono/templet
  - 单词索引 Pali Canon Word Index
  - 句子数据 Pali Canon Sentence
    appdata/palicaono/sentence.db3
  - 段落数据 Pali Canon Paragraph
    appdata/palicaono/pali_text.db3

## 包含用户数据 Include User Data

- User Infomation 用户
- File Index 文件目录
- Dynamic User Dictionary 动态用户词典
- Word by Word Analyze 逐词解析
- Translation by Sentence 逐句翻译
- Term 术语
- [db manager(temporary) 数据库管理器（临时）](https://www.wikipali.org/demo/app/studio/dictadmin/user/pla.php)

# Import Pali Canon Data From HTML 基础三藏数据导入

（discription unfinished 描述未完成）

# User Center 用户管理

## 功能

## 数据结构

```
CREATE TABLE user (
    id       INTEGER    PRIMARY KEY AUTOINCREMENT,
    userid   TEXT       UNIQUE,
    username TEXT (64)  NOT NULL,
    password TEXT       NOT NULL,
    nickname TEXT (64)  NOT NULL,
    email    TEXT (256),
    ctime    INTEGER
);

```

| Field name | Type                               | Summary  |
| ---------- | ---------------------------------- | -------- |
| id         | INTEGER PRIMARY KEY AUTOINCREMENT, |          |
| userid     | TEXT UNIQUE,                       | uuid     |
| username   | TEXT (64) NOT NULL,                |          |
| password   | TEXT NOT NULL,                     |          |
| nickname   | TEXT (64) NOT NULL,                |          |
| email      | TEXT (256),                        |          |
| ctime      | INTEGER                            | 创建时间 |

## 算法

## demo

[login 登录](https://www.wikipali.org/demo/app/ucenter/index.php?language=en)

[Sign in 注册](https://www.wikipali.org/demo/app/ucenter/index.php?language=en&op=new)

# File Manager

## database

user/fileindex.db

## 数据结构

| field name   | type                               | summary                                           |
| ------------ | ---------------------------------- | ------------------------------------------------- |
| id           | INTEGER PRIMARY KEY AUTOINCREMENT, | 整数不方便离线建立数据的在线同步，以后将用 doc_id |
| userid       | INTEGER,                           |                                                   |
| parent_id    | TEXT (40),                         | 从其他共享文档拉取的文档，有父文档的 id           |
| doc_id       | TEXT (40),                         | 文档 uuid                                         |
| book         | INTEGER DEFAULT (0),               | 书号 1-217                                        |
| paragraph    | INTEGER DEFAULT (0),               | 段落号                                            |
| file_name    | TEXT NOT NULL,                     | 文件名                                            |
| title        | TEXT,                              | 标题                                              |
| tag          | TEXT,                              | 标签 过滤文档用                                   |
| status       | INTEGER DEFAULT (1),               | 状态 0 回收站 1 正常                              |
| create_time  | INTEGER,                           | 建立时间                                          |
| modify_time  | INTEGER,                           | 修改时间                                          |
| accese_time  | INTEGER,                           | 访问时间                                          |
| file_size    | INTEGER,                           | 文件大小                                          |
| share        | INTEGER DEFAULT (0),               | 是否是共享文档 0 否 1 是                          |
| doc_info     | TEXT,                              | (json)                                            |
| doc_block    | TEXT,                              | (json)                                            |
| receive_time | INTEGER                            |                                                   |

用户文件索引。用于用户文件管理。所有用户用一张表。

## 文件存储方式

1. xml 文件
2. 数据库

### xml 文件

扩展名 pcs。保存在 user/user-uuid/my_document  
文件格式见[Document Format 文档格式](#document-format文档格式)

### 数据库

使用数据库中的记录在服务器端生成相同的 xml 数据，并传送到浏览器。文件头使用 doc_info 字段。doc_block 是数据块列表。
从两个数据库中提取 doc_block 数据.详情见[database to memory XML Data](#new-method--database-to-memory-xml-data)

**以后的程序使用数据库方式。可以实现网路同步等功能。**

# Studio 编辑器

（discription unfinished 描述未完成）

[demo 链接 link](https://www.wikipali.org/demo/app/studio)

## Document Format 文档格式

XML format use for the editable data.

studio/js/data.js is document manager

```

<set>
    <head>
        <type>pcdsset</type>
        <mode>package</mode>
        <ver>1</ver>
        <toc></toc>
        <style></style>
	</head>
	<dict></dict>
	<message></message>
	<body>
        <block></block>
        ....
        <block></block>
	</body>
</set>

```

- type:aways pcdsset
- mode:aways package
- var:varsion
- toc:not used
- style:Document css. css only for this Document
- block: one block is one paragraph on one Media Type. Two Kinds of Type. 1. wbw(word by word translation) 2. translate

```
<block>
	<info>
		<type>heading</type>
		<book>85</book>
		<paragraph>11</paragraph>
		<album_id>85</album_id>
		<album_guid>4FC0BE7B1C3042B79742D7D5BA90E77A</album_guid>
		<author>VRI</author>
		<language>pali</language>
		<version>4</version>
		<edition>CSCD4</edition>
		<level>4</level>
		<id>3F44D173-E266-46B6-B131-B8B26C37E45E</id>
	</info>
	<data>
	<text>2. Padhānasuttaṃ</text>
	</data>
</block>

```

- type
  - heading
  - pali_text
  - translate
  - wbw (word by word translation)
- level 目录层级
- id 数据块 uuid

## Document Load 文档载入

（discription unfinished 描述未完成）

### Old Method —— XML File

数据载入:studio/project_load.php

js 数据解析：studio/js/data.js
function projectDataParse(xmlBookData)

### New Method —— database to memory XML Data

数据拼接:studio/project_load_db.php

#### 逐词解析数据库

数据库 user/user_wbw.db3
数据块头信息

```
CREATE TABLE wbw_block (
    id           CHAR (36) PRIMARY KEY,
    parent_id    CHAR (36),
    owner        CHAR (36),
    book         INTEGER,
    paragraph    INTEGER,
    style        CHAR (16),
    lang         CHAR (8),
    status       INTEGER,
    modify_time  INTEGER,
    receive_time INTEGER
);
```

逐词解析单词数据<br>
每条记录一个单词

```
CREATE TABLE wbw (
    id           CHAR (36) PRIMARY KEY,
    block_id     CHAR (36),
    book         INTEGER,
    paragraph    INTEGER,
    wid          INTEGER,
    word         TEXT,
    data         TEXT,
    modify_time  INTEGER,
    receive_time INTEGER,
    status       INTEGER,
    owner        CHAR (36)
);
```

data:xml 格式单词数据

#### 译文数据库

块索引

```
CREATE TABLE sent_block (
    id           CHAR (36),
    book         INTEGER,
    paragraph    INTEGER,
    owner        CHAR (36),
    lang         CHAR (8),
    author       CHAR (50),
    editor       TEXT,
    tag          TEXT,
    modify_time  INTEGER,
    receive_time INTEGER
);

```

句子内容。一条记录是一句。

```

CREATE TABLE sentence (
    id           CHAR (36),
    block_id     CHAR (36) DEFAULT (0),
    book         INTEGER   NOT NULL,
    paragraph    INTEGER   NOT NULL,
    [begin]      INTEGER   NOT NULL,
    [end]        INTEGER   NOT NULL,
    tag          CHAR (40),
    author       CHAR (40),
    editor       INTEGER,
    text         TEXT,
    language     CHAR (8),
    ver          INTEGER,
    status       INTEGER,
    modify_time  INTEGER   NOT NULL,
    receive_time INTEGER
);
```

- begin:句子起始单词索引
- end:句子终止单词索引
-

## Edit Word by Word Translation 逐词解析编辑

## 用户词典

### 功能

### 数据结构

见[标准字典格式](#标准字典)

### 算法

## Edit Translation 译文编辑

当译文修改时，会立即提交修改句子数据到[译文数据库](#译文数据库)

## Term 专业术语

### 功能

在译文中显示术语
如：输入[[bhikkhu]] 会显示为 比库(bhikkhu,比丘)
在注释中可以加入三藏句子链接
格式{{书号-段号-起始单词-结束单词}} 渲染为巴利原文+译文
是百科功能的数据引擎

### 文件列表

- term\
  - note.js - 生成注释
  - note.php -
  - sync.php - 与 internet 或其他主机同步数据
  - term.js
  - term.php-术语数据库新增，修改，等

### 数据结构

```
CREATE TABLE term (
    id            INTEGER   PRIMARY KEY AUTOINCREMENT,
    guid          TEXT (36),
    word          TEXT,
    word_en       TEXT,
    meaning       TEXT,
    other_meaning TEXT,
    note          TEXT,
    tag           TEXT,
    create_time   INTEGER,
    owner         TEXT,
    hit           INTEGER   DEFAULT (0),
    language      CHAR (8),
    receive_time  INTEGER,
    modify_time   INTEGER
);
```

term

| field name    | type                              | summary                              |
| ------------- | --------------------------------- | ------------------------------------ |
| id            | INTEGER PRIMARY KEY AUTOINCREMENT | 以后会停用，改为 uuid                |
| guid          | TEXT (36)                         | uuid                                 |
| word          | TEXT                              | pali 词头                            |
| word_en       | TEXT                              | 英文字母表示，ā->a ṭ->t 用于模糊搜索 |
| meaning       | TEXT                              | 首选意思                             |
| other_meaning | TEXT                              | 备选意思                             |
| note          | TEXT                              | 注解内容                             |
| tag           | TEXT                              | 标签                                 |
| create_time   | INTEGER                           | \*                                   |
| owner         | TEXT                              | \*                                   |
| hit           | INTEGER DEFAULT (0)               | 点击量                               |
| language      | CHAR (8)                          | \*                                   |
| receive_time  | INTEGER                           | 服务器接收到数据的时间               |
| modify_time   | INTEGER                           | \*                                   |

### 算法

[data manager(temporary) 数据管理器（临时）](https://www.wikipali.org/demo/app/studio/dictadmin/term/pla.php)

## Plugin 插件

（discription unfinished 描述未完成）

# Dictionary 字典

## demo

https://www.wikipali.org/demo/app/dict/index.php

## 数据结构

### 标准字典

```
CREATE TABLE dict (
    id         INTEGER,
    pali       TEXT     NOT NULL,
    type       TEXT,
    gramma     TEXT,
    parent     TEXT,
    mean       TEXT,
    note       TEXT,
    parts      TEXT,
    partmean   TEXT,
    status     INTEGER  DEFAULT (1),
    confidence INTEGER  DEFAULT (100),
    len        INTEGER,
    dict_name  TEXT,
    lang       CHAR (3) DEFAULT sc
);
```

| Field name | Type                  | Summary        |
| ---------- | --------------------- | -------------- |
| id         | INTEGER               | ---            |
| pali       | TEXT NOT NULL         | 巴利单词       |
| type       | TEXT                  | ---            |
| gramma     | TEXT                  | ---            |
| parent     | TEXT                  | ---            |
| mean       | TEXT                  | 释义           |
| note       | TEXT                  | ---            |
| parts      | TEXT                  | ---            |
| partmean   | TEXT                  | ---            |
| status     | INTEGER DEFAULT (1)   | ---            |
| confidence | INTEGER DEFAULT (100) | 信心指数 1-100 |
| len        | INTEGER               | 单词长度       |
| dict_name  | TEXT                  | 字典名         |
| lang       | CHAR (3) DEFAULT sc   | 语言           |

### 参考字典

# Full Text Search 全文搜索

## Demo

https://www.wikipali.org/demo/app/search/index.php

## 功能

## 数据结构

## 算法

# Encyclopedia 百科

## Demo

## 功能

## 数据结构

见[term](#term-专业术语)

## 算法

base on the Term Database.
基于术语数据库
