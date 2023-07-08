# Deployment

## Setup a cluster

```bash
# create cluster
mkdir -p clients/CLUSTER/.ssh
cd clients/CLUSTER
# append your cluster hosts
touch hosts
# generate ssh key
ssh-keygen -t ed25519 -f .ssh/id_ed25519
# upload the ssh public key to target host
ssh-copy-id -i .ssh/id_ed25519 USER@HOST
```

## Deploy

```bash
# test ssh connections
peony -i clients/CLUSTER JOB.yml
# run on all hosts
peony -i clients/CLUSTER JOB.yml
# run on only group
peony -i clients/CLUSTER JOB.yml -l GROUP
```

## System image

- [Raspberry Pi OS Lite](https://www.raspberrypi.com/software/operating-systems/)
- [Armbian](https://www.armbian.com/download/)
- [wiringPi for Orange Pi](https://github.com/orangepi-xunlong/WiringOP)

## Import Database Data

### on deploy a new server

```bash
php ../../public/app/install/db_insert_templet_cli.php 1 217
php ../../public/app/install/db_update_toc_cli.php 1 217 pali
php ../../public/app/install/db_update_toc_cli.php 1 217 zh-hans
php ../../public/app/install/db_update_toc_cli.php 1 217 zh-hant
php ../../public/app/install/db_insert_palitext_cli.php 1 217
php ../../public/app/install/db_update_palitext_cli.php 1 217
php ../../public/app/install/db_insert_bookword_from_csv_cli.php 1 217
php ../../public/app/install/db_insert_word_from_csv_cli.php 1 217
php ../../public/app/install/db_insert_wordindex_from_csv_cli.php

php ./migrations/20211202084900_init_pali_serieses.php
php ./migrations/20211125155600_word_statistics.php
php ./migrations/20211125155700_pali_sent_org.php
php ./migrations/20211125165700-pali_sent-upgrade.php
php ./migrations/20211126220400-pali_sent_index-upgrade.php
php ./migrations/20211127214800_sent_sim.php
php ./migrations/20211127214900-sent_sim_index.php

php ../../public/app/fts/sql.php

php ../../public/app/admin/word_index_weight_refresh.php 1 217
```

### on update

```bash
# public/pali_title目录下文件*_pali.csv改变时触发
php ../../public/app/install/db_update_palitext_cli.php 1 217

# public/pali_title目录下文件其他改变时触发
php ../../public/app/install/db_update_toc_cli.php 1 217 pali
php ../../public/app/install/db_update_toc_cli.php 1 217 zh-hans
php ../../public/app/install/db_update_toc_cli.php 1 217 zh-hant

# public/dependence/pali_sentence/data 目录下文件其他改变时触发
# TODO 导入pali_sent使用上述目录csv文件。目前用的是sqlite db文件
php ./migrations/20211125165700-pali_sent-upgrade.php
php ./migrations/20211126220400-pali_sent_index-upgrade.php

```

## Crontab

### Daily

1. upgrade_pali_toc.php

```bash
/public/app/upgrade/upgrade_pali_toc.php
```
