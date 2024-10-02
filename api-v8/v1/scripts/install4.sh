#!/bin/sh

date

php ./migrations/20220205084100_group_info_copy.php
php ./migrations/20220205092400_group_member_copy.php

php ./migrations/20220206143600_fileindex_copy.php
date