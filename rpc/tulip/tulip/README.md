# 全文搜索字典更新

1. 下载字典文件

```bash
php dict_maker.php
```

文件将下载到 storage/dict 目录。下载过程中，该目录下有.stop 文件。下载结束后.stop 文件会被删除。

2. 复制字典文件到 postgresql 目录
3. 运行 `php dict_update.php` 令字典文件生效
4. 运行 `php content_update.php` 重新生成索引

# 全文搜索内容数据更新

运行 `php content_download.php` 从 api 获取巴利语全文搜索数据。运行时间约 1 小时。
