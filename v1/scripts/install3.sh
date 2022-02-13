#!/bin/sh

date

php ./migrations/20220202172100_dhamma_terms_copy.php
php ./migrations/20220204081300_share_copy.php

date