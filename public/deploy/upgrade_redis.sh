#!/bin/sh

date
php ../app/dict/redis_import_dict.php ../../dicttext/system/system.json
date
php ../app/dict/redis_import_dict.php ../../dicttext/rich/rich.json
date
php ../app/dict/redis_import_dict.php ../../tmp/dict_text/comp.json
date
php ../app/dict/redis_import_term.php
date
php ../app/dict/redis_import_user.php
date
php ../app/dict/redis_ref_with_mean.php
date
php ../app/dict/redis_refresh_first_mean.php
date
php ../app/dict/redis_sys_rgl_part.php
date
php ../app/dict/redis_pm_part.php
date
php ../app/pali_sent/redis_upgrade_pali_sent.php

date