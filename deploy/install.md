# 安装步骤

## 配置文件
```
cd app
cp config.sample.php config.php
cp path.sample.php config.php
cp config.sample.js config.js
```

修改配置文件里的数据库密码等

## 数据库迁移

./.env.sample rename to ./.env

```dash
DATABASE_URL=<user>:<pwd>//postgres@<ip>:<port>/<dbname>
MIGRATION_DIRECTORY=db/postgresql/migrations
```

```dash
DATABASE_URL=postgres:123456//postgres@127.0.0.1:5432/mint
MIGRATION_DIRECTORY=db/postgresql/migrations
```

**Runs all pending migrations**
```
diesel migration run
```

## 数据表数据导入

### 语料库数据表 pali canon db file 

- 巴利语料模版表

```dash
php /app/install/db_insert_templet_cli.php 1 217
```

- 标题资源表
```
php /app/install/db_update_toc.php 1 217 pali
php /app/install/db_update_toc.php 1 217 zh-hans
php /app/install/db_update_toc.php 1 217 zh-hant
```

- 巴利语料段落表
```
刷库 
php /app/install/db_insert_palitext.php 1 217
更新 
php /app/install/db_update_palitext.php 1 217
```

- 以书为单位的单词汇总表
```
填充 
php /app/install/db_insert_bookword_from_csv_cli.php 1 217
```

- 单词索引
```
php /app/install/db_insert_word_from_csv_cli.php 1 217
php /app/admin/word_index_weight_refresh.php
```

- 92万词
```
php /app/install/db_insert_wordindex_from_csv_cli.php
```

- 单词分析表
```
sqlite => pg： 
php /deploy/migaration/word_statistics.php
```

- 巴利句子表
```
sqlite => pg： 
php ./deploy/migaration/20211125155700_pali_sent_org.php
消除错误切分 当 pali_sent_org 修改了句子合并 数据时运行 
php ./deploy/migaration/20211125165700-pali_sent-upgrade.php
生成索引： 
php ./deploy/migaration/20211126220400-pali_sent_index-upgrade.php
```

- 相似句
```
sqlite => pg：
php ./deploy/migaration/20211127214800_sent_sim.php
index:
php ./deploy/migaration/20211127214900-sent_sim_index.php
```

- 句子数据导入redis
```
php ./app/pali_sent/redis_upgrade_pali_sent.php
```

- 完成度

根据句子译文库计算段落完成度。每天运行一次
```
php ./app/upgrade/upgrade_pali_toc.php
```