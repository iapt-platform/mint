# Redis 预热

>下面任务需在安装或者redis重置的时候运行

```bash
php artisan cache:wbw.preference

php artisan cache:dict.preference
```

# 每日任务

```bash
php artisan upgrade:daily
```

# 每周任务

```bash
php artisan upgrade:weekly
```

# 字典相关

```bash
#更新某个字典

#更新所有字典

#更新字典搜索索引表

#复合词拆分

#变格表
```

# 语料库

# 逐词解析

>逐词解析数据库有更新时，会自动更新这些数据。请在安装，redis重置，或者mq失效时运行。

```bash
#逐词解析首选项
php artisan cache:wbw.preference

php artisan cache:dict.preference
```
