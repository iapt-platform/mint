
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

net time \\127.0.0.1
