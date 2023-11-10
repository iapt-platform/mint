# 全文搜索表更新流程

1. 下载字典文件

```bash
php dict_maker.php
```

文件将下载到 storage/dict 目录。下载过程中，该目录下有.stop 文件。下载结束后.stop 文件会被删除。

2. 复制字典文件到 postgresql 目录
3. 运行 `php dict_update.php` 令字典文件生效
4. 运行 `php content_update.php` 重新生成索引
