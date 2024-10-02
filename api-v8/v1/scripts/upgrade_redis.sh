#!/bin/sh

date

php ../../public/app/dict/redis_import_dict.php ../../dicttext/system/system.json
php ../../public/app/dict/redis_import_dict.php ../../dicttext/rich/rich.json
php ../../public/app/dict/redis_import_dict.php ../../tmp/dict_text/comp.json
php ../../public/app/dict/redis_import_term.php
php ../../public/app/dict/redis_import_user.php
php ../../public/app/dict/redis_ref_with_mean.php
php ../../public/app/dict/redis_refresh_first_mean.php
php ../../public/app/dict/redis_sys_rgl_part.php
php ../../public/app/dict/redis_pm_part.php
php ../../public/app/pali_sent/redis_upgrade_pali_sent.php

date