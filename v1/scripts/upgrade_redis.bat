net time \\127.0.0.1
php ../app/dict/redis_import_dict.php ../../dicttext/system/system.json

php ../app/dict/redis_import_dict.php ../../dicttext/rich/rich.json

php ../app/dict/redis_import_dict.php ../../tmp/dict_text/comp.json

php ../app/dict/redis_import_term.php

php ../app/dict/redis_import_user.php

php ../app/dict/redis_ref_with_mean.php

php ../app/dict/redis_refresh_first_mean.php

php ../app/dict/redis_sys_rgl_part.php

php ../app/dict/redis_pm_part.php

php ../app/pali_sent/redis_upgrade_pali_sent.php

net time \\127.0.0.1