#!/bin/sh

date

sudo su - www-data php ../../public/app/install/db_insert_templet_cli.php 1 217
sudo su - www-data php ../../public/app/install/db_update_toc_cli.php 1 217 pali
sudo su - www-data php ../../public/app/install/db_update_toc_cli.php 1 217 zh-hans
sudo su - www-data php ../../public/app/install/db_update_toc_cli.php 1 217 zh-hant
sudo su - www-data php ../../public/app/install/db_insert_palitext_cli.php 1 217
sudo su - www-data php ../../public/app/install/db_update_palitext_cli.php 1 217
sudo su - www-data php ../../public/app/install/db_insert_bookword_from_csv_cli.php 1 217
sudo su - www-data php ../../public/app/install/db_insert_word_from_csv_cli.php 1 217
sudo su - www-data php ../../public/app/install/db_insert_wordindex_from_csv_cli.php

sudo su - www-data php ./migrations/20211202084900_init_pali_serieses.php
sudo su - www-data php ./migrations/20211125155600_word_statistics.php
sudo su - www-data php ./migrations/20211125155700_pali_sent_org.php
sudo su - www-data php ./migrations/20211125165700-pali_sent-upgrade.php
sudo su - www-data php ./migrations/20211126220400-pali_sent_index-upgrade.php
sudo su - www-data php ./migrations/20211127214800_sent_sim.php
sudo su - www-data php ./migrations/20211127214900-sent_sim_index.php

sudo su - www-data php ../../public/app/fts/sql.php

php ../../public/app/admin/word_index_weight_refresh.php 1 217

date