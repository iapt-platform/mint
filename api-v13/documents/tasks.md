# task list

全部的消息队列和定时任务列表

## MQ workers

worker 不再自建队列，队列不存在会报错退出。部署或清空 RabbitMQ 后先建队列（队列列表见 `config/mint.php` 的 `mint.rabbitmq.queues`）：

```bash
php artisan app:create-queue
```

每条消息处理完无论成功失败都 ack，不重试、不进死信队列。
处理满 `MQ_CONSUMER_LOOP_LIMIT` 条（0 = 不限）或检测到 `.stop` 文件后 worker 退出，需要外部重新拉起。

```bash
# 队列 discussion
php artisan mq:discussion

# 队列 export_pali_chapter
php artisan mq:export.pali.chapter

# 队列 export_article
php artisan mq:export.article

# 队列 progress
php artisan mq:progress

# 队列 suggestion
php artisan mq:pr

# 队列 task
php artisan mq:task

# 队列 wbw-analyses
php artisan mq:wbw.analyses

```

`ai_translate_v2`（`rabbitmq:consume`）待第二阶段重构 worker 后补上。`content` 队列已移除（无消费者，发送端已注释，见 `SentenceController@update` 的 FIXME）。

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

# AI 对话积分：把读经、编辑时长结算成积分。补齐上线以来所有漏结算的日子，
# 漏跑几天都没关系，下次跑会补上；可重复执行，不会重复入账
php artisan credits:settle-activity

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
