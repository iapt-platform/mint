# raw php fcgi

api 入口文件 morus.php

启动

```bash
php -S localhost:8080
```

测试

http://localhost:8080

laravel 设置样例

```
MORUS_GRPC_HOST="http://localhost:8080/morus.php"
```

> MORUS_GRPC_PORT 弃用
