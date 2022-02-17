# 基于 PostgreSQL 的中文全文检索方案

## 概述

直接使用 PostgreSQL [结巴分词插件](https://github.com/jaiminpan/pg_jieba)，本处仅仅是做简化安装与配置。

### 当前局限

不支持简繁混查。

也就是查询 `世間` 只能匹配到 `世間` 而不能匹配到 `世间`，同样的 `世间` 只能匹配到 `世间` 而不能匹配到 `世間`。

这个局限可以使用 [OpenCC](https://github.com/BYVoid/OpenCC) 解决，当前未实现，如果需要，可以在下个版本加入。

注意：简繁混查实现后，混查时将以简体为准，因为：一繁对一简，一简对多繁。

## 安装

此处仅整理 Ubuntu 的安装脚本，如果服务器类型有调整，可以联系我修正以下脚本：

```bash
# 安装 PostgreSQL 以及其他依赖项
sudo apt install postgresql postgresql-contrib postgresql-server-dev-all libpq-dev

# 安装 gcc, cmake 用来编译插件
sudo apt install gcc g++ cmake

# 下载插件源码
git clone https://github.com/jaiminpan/pg_jieba
cd pg_jieba
git submodule update --init --recursive

# 编译插件 (当前目录 pg_jieba)
mkdir build
cd build
# 编译插件 (当前目录 pg_jieba/build)
cmake .. # <- 注意有两个小点点
make
# 安装插件 (当前目录 pg_jieba/build)
sudo make install
```

## 配置繁体中文支持（请按需配置）

```bash
# 切换到词典目录
cd $(pg_config --sharedir)/tsearch_data

# 下载繁体中文分词词典
sudo curl -LO https://github.com/fxsjy/jieba/raw/master/extra_dict/dict.txt.big
sudo mv dict.txt.big jieba_dict.txt.big

# 配置 postgresql.conf 使用该词典
# 使用下面命令找到配置文件
sudo -u postgres psql -c 'SHOW config_file'

# 在文件末尾添加如下配置 (请自行替换路径为上面下载的词典绝对路径，注意单引号)
pg_jieba.base_dict = '/usr/share/postgresql/<version>/tsearch_data/jieba_dict.txt.big'

# 重启 PostgreSQL
sudo systemctl restart postgresql
```

## 使用

与巴利语全文检索不同，中文的全文检索需求比较纯粹，没有「变形词」、「黑体字」等需求，所以中文全文检索功能比较通用。

任意数据表均可使用该功能，只需要添加一个索引字段用来加快检索速度。

### 激活插件

Bash 命令

```bash
# 切换到 postgres 用户
sudo -i -u postgres
# 进入 psql 环境
psql
```

SQL 命令

```SQL
-- 激活插件
CREATE EXTENSION IF NOT EXISTS "pg_jieba";
-- 测试效果
select * from to_tsquery('jiebacfg', '尊者们啊！在去年一月份，我为了禅修乘飞机从中国来到位于斯里兰卡的圣法大长老的龙树林禅林。');
```

预期测试结果：

```bash
to_tsquery
-------------------------------------------------------------------
 '尊者' & '去年' & '一月份' & '禅修' & '乘飞机' & '中国' & '来到' & '位于' & '斯里兰卡' & '圣法' & '大长老' & '龙' & '树林' & '禅林'
(1 row)
```

### 在已有数据表上使用

#### 测试数据准备

假设已有以下数据表：

```SQL
CREATE TABLE sample (
    id           SERIAL PRIMARY KEY,
    author       TEXT,
    title        TEXT,
    subtitle     TEXT,
    content      TEXT
);
```

数据表内有以下数据：

```SQL
INSERT INTO sample (author, title, subtitle, content)
       VALUES ('三藏法师', '纷争分歧经', '纷争分歧何处生', '请你说说，许多争吵争论及悲哀、忧伤和妒忌来源于何处？');
INSERT INTO sample (author, title, subtitle, content)
       VALUES ('鸠摩罗什', '纷争分歧经', '爱故纷争分歧生', '爱故纷争分歧生，（由于所爱，纷争、分歧生起）');
INSERT INTO sample (author, title, subtitle, content)
       VALUES ('不空大师', '纷争分歧经', '爱于世间何因生', '爱于世间何因生？ 贪者流连于世间； （所爱源自世间何因？贪婪者于世间四处游荡）');
INSERT INTO sample (author, title, subtitle, content)
       VALUES ('支娄迦谶', '纷争分歧经', '世间所爱生于欲', '世间所爱生于欲，贪者流连于世间; （于此世间中所爱产生于欲，贪婪者流连于世间）');
```

繁体版本测试数据：
```SQL
INSERT INTO sample (author, title, subtitle, content)
       VALUES ('三藏法師', '紛爭分歧經', '紛爭分歧何處生', '請你說說，許多爭吵爭論及悲哀、憂傷和妒忌來源於何處？');
INSERT INTO sample (author, title, subtitle, content)
       VALUES ('鳩摩羅什', '紛爭分歧經', '愛故紛爭分歧生', '愛故紛爭分歧生，（由於所愛，紛爭、分歧生起）');
INSERT INTO sample (author, title, subtitle, content)
       VALUES ('不空大師', '紛爭分歧經', '愛於世間何因生', '愛於世間何因生？ 貪者流連於世間； （所愛源自世間何因？貪婪者於世間四處遊蕩）');
INSERT INTO sample (author, title, subtitle, content)
       VALUES ('支婁迦讖', '紛爭分歧經', '世間所愛生於欲', '世間所愛生於欲，貪者流連於世間; （於此世間中所愛產生於欲，貪婪者流連於世間）');
```

#### 创建动态索引

分别为 author / title / subtitle / content 设置权重标记 D / A / B / C

```SQL
-- 添加自动更新的 TSVECTOR 字段
ALTER TABLE sample
      ADD COLUMN full_text_search_weighted TSVECTOR
      GENERATED ALWAYS AS (
         setweight(to_tsvector('jiebacfg', coalesce(author,'')), 'D') || ' '  ||
         setweight(to_tsvector('jiebacfg', coalesce(title,'')), 'A')  || ' ' ||
         setweight(to_tsvector('jiebacfg', coalesce(subtitle,'')), 'B') || ' '  ||
         setweight(to_tsvector('jiebacfg', coalesce(content,'')), 'C')
      ) STORED;
-- 为该字段创建索引
CREATE INDEX full_text_search_weighted_idx
       ON sample USING GIN (full_text_search_weighted);
```

#### 查询

```SQL
SELECT
    ts_rank('{0.1, 0.2, 0.4, 1}',
        full_text_search_weighted,
        websearch_to_tsquery('jiebacfg', '世间分歧'))  -- AS rank
        author, title, subtitle, content, full_text_search_weighted
    FROM sample
    WHERE
        full_text_search_weighted @@ websearch_to_tsquery('jiebacfg', '世间分歧');
```

繁体版本查询语句：

```SQL
SELECT
    ts_rank('{0.1, 0.2, 0.4, 1}',
        full_text_search_weighted,
        websearch_to_tsquery('jiebacfg', '世間分歧'))  -- AS rank
        author, title, subtitle, content, full_text_search_weighted
    FROM sample
    WHERE
        full_text_search_weighted @@ websearch_to_tsquery('jiebacfg', '世間分歧');
```

注意以上语句中 {0.1, 0.2, 0.4, 1} 分别对应上边 D, C, B, A 标记, 也就是：

- D = 0.1
- C = 0.2
- B = 0.4
- A = 1

给 ABCD 分别了赋予权重值，取值区间为 0 - 1，实际使用应当按照需求调整权重。

注意：动态索引的创建，不一定非要把所有的文字拼接在一起，请依据实际需要进行调整。

### 使用 PHP 查询数据：

可参考 [example.php](./example.php
)，在当前目录下执行：

```bash
php -d memory_limit=1024M -S 127.0.0.1:8000
```

即可通过浏览器测试效果：

![Example](./example.png "Example Screenshot")

繁体版本测试效果：

![Example](./example-traditional.png "Example Screenshot")

## 用户自定义字典

### 词典格式

1. 词语 权重 词性
2. 词语 词性
3. 词语

### 内容示例

```text
龙树林
异熟识
祗陀林 nz
我执 10 nz
```

词性[一览表](https://github.com/fxsjy/jieba#%E4%BD%BF%E7%94%A8%E7%A4%BA%E4%BE%8B)：

| 标签 | 含义     | 标签 | 含义     | 标签 | 含义     | 标签 | 含义     |
| ---- | -------- | ---- | -------- | ---- | -------- | ---- | -------- |
| n    | 普通名词 | f    | 方位名词 | s    | 处所名词 | t    | 时间     |
| nr   | 人名     | ns   | 地名     | nt   | 机构名   | nw   | 作品名   |
| nz   | 其他专名 | v    | 普通动词 | vd   | 动副词   | vn   | 名动词   |
| a    | 形容词   | ad   | 副形词   | an   | 名形词   | d    | 副词     |
| m    | 数量词   | q    | 量词     | r    | 代词     | p    | 介词     |
| c    | 连词     | u    | 助词     | xc   | 其他虚词 | w    | 标点符号 |
| PER  | 人名     | LOC  | 地名     | ORG  | 机构名   | TIME | 时间     |

### 词典位置

词典命名：`jieba_user.dict`

编辑后拷贝到对应位置：

```bash
cp jieba_user.dict /usr/share/pgsql/tsearch_data/jieba_user.dict
```

其中 `/usr/share/pgsql/` 路径可通过 `pg_config --sharedir` 获得。

## 感谢

https://github.com/jaiminpan/pg_jieba

https://github.com/yanyiwu/cppjieba

https://github.com/fxsjy/jieba
