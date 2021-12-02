#!/bin/sh

date
php ../app/install/db_insert_templet_cli.php 1 217
php ../app/install/db_update_toc.php 1 217 pali
php ../app/install/db_update_toc.php 1 217 zh-hans
php ../app/install/db_update_toc.php 1 217 zh-hant
php ../app/install/db_insert_palitext.php 1 217
php ../app/install/db_update_palitext.php 1 217
php ../app/install/db_insert_bookword_from_csv_cli.php 1 217
php ../app/install/db_insert_word_from_csv_cli.php 1 217
php ../app/install/db_insert_wordindex_from_csv_cli.php
php ../app/admin/word_index_weight_refresh.php 1 1

cd /deploy/migaration
php 20211202084900_init_pali_serieses.php
php 20211125155600_word_statistics.php
php 20211125155700_pali_sent_org.php
php 20211125165700-pali_sent-upgrade.php
php 20211126220400-pali_sent_index-upgrade.php
php 20211127214800_sent_sim.php
php 20211127214900-sent_sim_index.php
date