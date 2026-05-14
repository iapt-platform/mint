# task list

## MQ workers

```bash
php artisan mq:discussion

php artisan mq:export.pali.chapter

php artisan mq:export.article

php artisan mq:progress

php artisan mq:task

php artisan mq:wbw.analyses

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

start at: Mon 16:00 UTC

```bash
# update corpus
# dir is option omit to storage/resources
php artisan app:update-corpus --dir=***

# index tipitaka
php artisan opensearch:index-tipitaka 0 --granularity=chapter

# index term
php artisan opensearch:index-term

# 逐词译数据库分析
php artisan upgrade:wbw.analyses

# 段落更新图
php artisan app:upgrade-progress

# 段落更新图
php artisan upgrade:chapter.dynamic.weekly

```
