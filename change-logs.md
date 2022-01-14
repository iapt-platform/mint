# Changelog
All notable changes to this project will be documented in this file.

The format is based on [Keep a Changelog](https://keepachangelog.com/en/1.0.0/),
and this project adheres to [Semantic Versioning](https://semver.org/spec/v2.0.0.html).

## [Unreleased]

## [1.0.0] - 2022-01-13
- add redis setting to README.md
- add .env-global/.env
- .env -> /.env in .gitignore
- rename public/app/table.php to table.config.php
- rename public/app/dir.php to dir.config.php
- add ASSETS_SERVER to .env.sample
- add ASSETS_SERVER to /public/app/config.sample.js

## [1.0.1] - 2022-01-14
- /public/app/config.sample.php 变更 移动 config.dir.php 到其他设置的下面 跟其他的 dev config 放在一起 看着比较整齐。
- remove config.migrate in config.sample.php
- remove file config.migrate.sample.php
- finish command Install:wordbook
