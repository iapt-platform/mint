# Usage

```bash
composer install

php -d extension=grpc.so -d max_execution_time=300 morus-client.php morus.json
php queue-producer.php queue.json texlive QUEUE
php queue-producer.php queue.json pandoc QUEUE
```
