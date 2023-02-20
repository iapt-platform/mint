# Redis

## Cluster init

- create node 1

```bash
sudo ./setup.sh 1
sudo systemctl start redis-node-1
```

- create cluster

```bash
sudo redis-cli --cluster create 127.0.0.1:6371 127.0.0.1:6372 127.0.0.1:6373 127.0.0.1:6374 127.0.0.1:6375 127.0.0.1:6376
```
