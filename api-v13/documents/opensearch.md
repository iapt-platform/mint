### 查看所有 Index

```bash
curl -X GET "http://127.0.0.1:9200/_cat/indices?v"
```

### 查看指定 Index

```bash
curl -X GET "http://127.0.0.1:9200/your_index"
```

### 删除 Index

```bash
curl -X DELETE "http://127.0.0.1:9200/your_index"
```

### 查看某 ID 数据

```bash
curl -X GET "http://127.0.0.1:9200/your_index/_doc/your_id"
```

### 删除某 ID 数据

```bash
curl -X DELETE "http://127.0.0.1:9200/your_index/_doc/your_id"
```

### 测试 Analyzer（默认）

```bash
curl -X POST "http://127.0.0.1:9200/_analyze" \
-H "Content-Type: application/json" \
-d '{
  "text": "your text here"
}'
```

### 测试 Analyzer（指定 analyzer）

```bash
curl -X POST "http://127.0.0.1:9200/_analyze" \
-H "Content-Type: application/json" \
-d '{
  "analyzer": "standard",
  "text": "your text here"
}'
```

### 测试 Analyzer（指定 index）

```bash
curl -X POST "http://127.0.0.1:9200/your_index/_analyze" \
-H "Content-Type: application/json" \
-d '{
  "field": "your_field",
  "text": "your text here"
}'
```
