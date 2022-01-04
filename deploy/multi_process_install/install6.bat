
::php ../app/install/db_insert_templet_cli.php 1 217
::php ../app/install/db_update_toc_cli.php 1 217 pali
::php ../app/install/db_update_toc_cli.php 1 217 zh-hans
::php ../app/install/db_update_toc_cli.php 1 217 zh-hant

::php ../app/install/db_insert_palitext_cli.php 1 217
::php ../app/install/db_update_palitext_cli.php 1 217

::php ../app/install/db_insert_bookword_from_csv_cli.php 1 217

::php ../app/install/db_insert_word_from_csv_cli.php 1 217


::php ../app/install/db_insert_wordindex_from_csv_cli.php
::php ../app/admin/word_index_weight_refresh.php 1 217

::php ./migaration/20211202084900_init_pali_serieses.php

::php ./migaration/20211125155600_word_statistics.php

::php ./migaration/20211125155700_pali_sent_org.php
::php ./migaration/20211125165700-pali_sent-upgrade.php
::php ./migaration/20211126220400-pali_sent_index-upgrade.php

php ../migaration/20211127214800_sent_sim.php
php ../migaration/20211127214900-sent_sim_index.php

net time \\127.0.0.1
