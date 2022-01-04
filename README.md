# IAPT Pali Canon Platform

## About

这是一个开放的基于语料库的巴利语学习和翻译平台。

后端：
- PHP 8.0+
- [laravel](https://laravel.com/docs) 8.x
- PostgreSQL v12+
- Redis

前端
- jQuery



## 安装

### 开发工具

#### PostgreSQL

最小版本v12。下载链接
https://www.postgresql.org/download/

>温馨提示：windows环境安装完之后 将 安装目录/bin加入环境变量 PATH。重启电脑。在命令行输入 psql -v 查看版本号。

建立新的数据库，如：create database iapt

![createdb](public/documents/imgs/createdb.jpg)

#### PHP 8

#### Redis

### Fork

Fork https://github.com/iapt-platform/mint 到你自己的仓库

### Clone

```
git clone https://github.com/<your>/mint.git  --recurse-submodules

```
### 修改配置文件

复制 .env.example -> .env

修改为你的db配置
```
DB_CONNECTION=pgsql
DB_HOST=127.0.0.1
DB_PORT=5432
DB_DATABASE=iapt
DB_USERNAME=postgres
DB_PASSWORD=
```

复制 public/.env.example -> .env

复制 public/app/config.example.php -> config.php

复制 public/app/config.example.js -> config.js

修改相关配置

### 复制巴利语全文搜索单词表

获取pg share dir

```
pg_config --sharedir
```

把下面`/usr/share/postgresql/14`替换为你自己的shardir

liunx
```bash
sudo cp ./public/app/fts/pali.stop /usr/share/postgresql/14/tsearch_data/
sudo cp ./public/app/fts/pali.syn /usr/share/postgresql/14/tsearch_data/
```
windows
```bash
cp ./public/app/fts/pali.stop /usr/share/postgresql/14/tsearch_data/
cp ./public/app/fts/pali.syn /usr/share/postgresql/14/tsearch_data/
```
### 数据库迁移

在根目录下运行

```
php artisan migrate
```

### 语料数据库填充

liunx
```
cd public/deploy
sh ./install.sh
```

window
```
cd public/deploy
./install.bat
```
运行时间较长。本地开发环境大约4小时。


### 运行dev server

```
php artisan serve
```

在浏览器中访问

http://127.0.0.1:8000
