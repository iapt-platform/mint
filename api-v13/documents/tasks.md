# task list

## MQ workers

```bash

```

## every five minutes

```bash
php artisan app:index-open-search
```

## daily

start at: 16:00 UTC

```bash
# 更新单词首选意思
php artisan upgrade:dict.default.meaning

# 社区术语表
php artisan upgrade:community.term zh-Hans

# 导出离线数据
php artisan export:offline lzma

```

## weekly

```bash
# 逐词译数据库分析
php artisan upgrade:wbw.analyses

# 段落更新图
php artisan upgrade:chapter.dynamic

```
