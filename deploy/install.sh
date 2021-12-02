#!/bin/sh

date
cd ./migaration
php 20211202084900_init_pali_serieses.php
php 20211125155600_word_statistics.php
php 20211125155700_pali_sent_org.php
php 20211125165700-pali_sent-upgrade.php
php 20211126220400-pali_sent_index-upgrade.php
php 20211127214800_sent_sim.php
php 20211127214900-sent_sim_index.php
date