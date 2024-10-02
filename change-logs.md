# Changelog
All notable changes to this project will be documented in this file.

The format is based on [Keep a Changelog](https://keepachangelog.com/en/1.0.0/),
and this project adheres to [Semantic Versioning](https://semver.org/spec/v2.0.0.html).

## [2.1.0] - 2023-11-8

- rename RPC_SERVER to GRPC_WEB_SERVER in .env.example
- rename RPC_SERVER to GRPC_WEB_SERVER in .config.example.php
- rename RPC_SERVER to GRPC_WEB_SERVER in .config.example.js


## [2.0.3] - 2023-07-20

- add DASHBOARD_URL to .env
- 
## [2.0.2] - 2023-5-7

- add intervention/image

## [2.0.1] - 2023-5-17

- migrate user_info table to pg

## [1.0.12] - 2022-12-16

- add ALLOWED_ORIGINS to .env

## [1.0.11] - 2022-09-6

- add doctrine/dbal

## [1.0.10] - 2022-07-31

- rename REDIS_NAMESPACE to REDIS_PREFIX  .env.example
- rename Redis['namespace'] to Redis['prefix'] config.sample.php
 
## [1.0.9] - 2022-02-24

- add SCHEDULE_EMAIL_OUTPUTTO to .env
- add SCHEDULE_EMAIL_OUTPUTONFAILURE to .env

## [1.0.8] - 2022-02-24

- 优化打印显示效果 去掉channel列表 逐句首字母大写

## [1.0.7] - 2022-02-01

- add REDIS_NAMESPACE to .env.example
- add redis namespace to config.sample.php

## [1.0.6]- 2022-01-27

- remove HELP_SERVER GRAMMAR_SERVER from .env.example
- remove HELP_SERVER GRAMMAR_SERVER from config.sample.php
- remove HELP_SERVER GRAMMAR_SERVER from config.sample.php

## [1.0.5] - 2022-01-24

- add 帮助文件路径 URL_HELP to config.dir.php
- add 巴利语手册路径 URL_PALI_HANDBOOK to config.dir.php

## [1.0.4] - 2022-01-21

- add SNOWFLAKE to .env.example
- add SnowFlake to config.sample.php
- add SnowFlakeDate to config.table.php
- add dependency godruoyi/php-snowflake
- add help & handbook link 

## [1.0.3] - 2022-01-16

- remove WWW_DOMAIN_PROTOCOL WWW_DOMAIN_NAME from config.sample.php
- rename RPC_DOMAIN_NAME -> RPC_SERVER in config.sample.php
- remove WWW_DOMAIN_NAME  from config.sample.js
- rename RPC_DOMAIN_NAME -> RPC_SERVER in config.sample.js
- add RPC_SERVER to .env.example

## [1.0.2] - 2022-01-15

- add HELP_SERVER to .env.example /public/app/config.sample.js /public/app/config.sample.php
- add GRAMMAR_SERVER to .env.example /public/app/config.sample.js /public/app/config.sample.php

## [1.0.1] - 2022-01-14
- /public/app/config.sample.php 变更 移动 config.dir.php 到其他设置的下面 跟其他的 dev config 放在一起 看着比较整齐。
- remove config.migrate in config.sample.php
- remove file config.migrate.sample.php
- finish command Install:wordbook

## [1.0.0] - 2022-01-13
- add redis setting to README.md
- add .env-global/.env
- .env -> /.env in .gitignore
- rename public/app/table.php to table.config.php
- rename public/app/dir.php to dir.config.php
- add ASSETS_SERVER to .env.example
- add ASSETS_SERVER to /public/app/config.sample.js














