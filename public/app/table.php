<?php 
#表名设置，不能更改


//语料库数据表 pali canon db file 

/*
巴利语料模版表
运行app/install/db_insert_templet.php 刷库
*/
#sqlite
define("_SQLITE_DB_PALICANON_TEMPLET_","sqlite:" . __DIR__ . "/../tmp/appdata/palicanon/templet.db3");
define("_SQLITE_TABLE_PALICANON_TEMPLET_","wbw_templates");

#pg
define("_PG_DB_PALICANON_TEMPLET_", _DB_ENGIN_.":host="._DB_HOST_.";port="._DB_PORT_.";dbname="._DB_NAME_.";user="._DB_USERNAME_.";password="._DB_PASSWORD_.";");
define("_PG_TABLE_PALICANON_TEMPLET_","wbw_templates");

/*
标题资源表
app/install/db_update_toc.php 刷库
*/
#sqlite
define("_SQLITE_DB_RESRES_INDEX_", "sqlite:" . __DIR__ . "/../tmp/appdata/palicanon/res.db3");
define("_SQLITE_TABLE_RES_INDEX_","res_index");

#pg
define("_PG_DB_RESRES_INDEX_", _DB_ENGIN_.":host="._DB_HOST_.";port="._DB_PORT_.";dbname="._DB_NAME_.";user="._DB_USERNAME_.";password="._DB_PASSWORD_.";");
define("_PG_TABLE_RES_INDEX_","res_indices");

/*
巴利语料段落表
刷库 app/install/db_insert_palitext.php
更新 app/install/db_update_palitext.php
*/
#sqlite
define("_SQLITE_DB_PALITEXT_", "sqlite:" . __DIR__ . "/../tmp/appdata/palicanon/pali_text.db3");
define("_SQLITE_TABLE_PALI_TEXT_","pali_text");
define("_SQLITE_TABLE_PALI_BOOK_NAME_","books");

#pg
define("_PG_DB_PALITEXT_", _DB_ENGIN_.":host="._DB_HOST_.";port="._DB_PORT_.";dbname="._DB_NAME_.";user="._DB_USERNAME_.";password="._DB_PASSWORD_.";");
define("_PG_TABLE_PALI_TEXT_","pali_texts");
define("_PG_TABLE_PALI_BOOK_NAME_","book_titles");

#单词表部分
/*
以书为单位的单词汇总表
填充 /app/install/db_insert_bookword_from_csv.php
*/
//sqlite
define("_SQLITE_DB_BOOK_WORD_", "sqlite:" . __DIR__ . "/../tmp/appdata/palicanon/bookword.db3");
define("_SQLITE_TABLE_BOOK_WORD_", "bookword");

//PostgreSQL
define("_PG_DB_BOOK_WORD_", _DB_ENGIN_.":host="._DB_HOST_.";port="._DB_PORT_.";dbname="._DB_NAME_.";user="._DB_USERNAME_.";password="._DB_PASSWORD_.";");
define("_PG_TABLE_BOOK_WORD_", "book_words");

/*
单词索引
/app/install/db_insert_word_from_csv.php
/app/admin/word_index_weight_refresh.php
*/
//sqlite
define("_SQLITE_DB_PALI_INDEX_", "sqlite:" . __DIR__ . "/../tmp/appdata/palicanon/paliindex.db3");
define("_SQLITE_TABLE_WORD_", "word");

define("_PG_DB_PALI_INDEX_", _DB_ENGIN_.":host="._DB_HOST_.";port="._DB_PORT_.";dbname="._DB_NAME_.";user="._DB_USERNAME_.";password="._DB_PASSWORD_.";");
define("_PG_TABLE_WORD_", "word_lists");

/*
92万词
/app/install/db_insert_wordindex_from_csv.php
*/
//sqlite
define("_SQLITE_DB_WORD_INDEX_", "sqlite:" . __DIR__ . "/../tmp/appdata/palicanon/wordindex.db3");
define("_SQLITE_TABLE_WORD_INDEX_", "wordindex");

//PostgreSQL
define("_PG_DB_WORD_INDEX_", _DB_ENGIN_.":host="._DB_HOST_.";port="._DB_PORT_.";dbname="._DB_NAME_.";user="._DB_USERNAME_.";password="._DB_PASSWORD_.";");
define("_PG_TABLE_WORD_INDEX_", "word_indices");

//单词索引=92万词+单词索引
//sqlite
define("_SQLITE_DB_INDEX_", "sqlite:" . __DIR__ . "/../tmp/appdata/palicanon/index.db3");

//PostgreSQL
define("_PG_DB_INDEX_", _DB_ENGIN_.":host="._DB_HOST_.";port="._DB_PORT_.";dbname="._DB_NAME_.";user="._DB_USERNAME_.";password="._DB_PASSWORD_.";");

//黑体字数据表
//sqlite
define("_SQLITE_DB_BOLD_", "sqlite:" . __DIR__ . "/../tmp/appdata/palicanon/bold.db3");
define("_SQLITE_TABLE_WORD_BOLD_", "bold");

//PostgreSQL
define("_PG_DB_BOLD_", _DB_ENGIN_.":host="._DB_HOST_.";port="._DB_PORT_.";dbname="._DB_NAME_.";user="._DB_USERNAME_.";password="._DB_PASSWORD_.";");
define("_PG_TABLE_WORD_BOLD_", "bolds");

/*
单词分析表
数据迁移： php /deploy/migaration/word_statistics.php
*/
//sqlite
define("_SQLITE_DB_STATISTICS_", "sqlite:" . __DIR__ . "/../tmp/appdata/palicanon/word_statistics.db3");
define("_SQLITE_TABLE_WORD_STATISTICS_", "word");

//PostgreSQL
define("_PG_DB_STATISTICS_", _DB_ENGIN_.":host="._DB_HOST_.";port="._DB_PORT_.";dbname="._DB_NAME_.";user="._DB_USERNAME_.";password="._DB_PASSWORD_.";");
define("_PG_TABLE_WORD_STATISTICS_", "word_statistics");



/*
巴利句子表
数据迁移： php ./deploy/migaration/20211125155700_pali_sent_org.php
数据迁移： php ./deploy/migaration/20211125165700-pali_sent-upgrade.php
数据迁移： php ./deploy/migaration/20211126220400-pali_sent_index-upgrade.php

*/
//sqlite
define("_SQLITE_DB_PALI_SENTENCE_", "sqlite:" . __DIR__ . "/../tmp/appdata/palicanon/pali_sent2.db3");
define("_SQLITE_TABLE_PALI_SENT_", "pali_sent");
define("_SQLITE_TABLE_PALI_SENT_ORG_", "pali_sent_org");
define("_SQLITE_TABLE_PALI_SENT_INDEX_", "pali_sent_index");

//PostgreSQL
define("_PG_DB_PALI_SENTENCE_", _DB_ENGIN_.":host="._DB_HOST_.";port="._DB_PORT_.";dbname="._DB_NAME_.";user="._DB_USERNAME_.";password="._DB_PASSWORD_.";");
define("_PG_TABLE_PALI_SENT_", "pali_sentences");
define("_PG_TABLE_PALI_SENT_ORG_", "pali_sent_orgs");
define("_PG_TABLE_PALI_SENT_INDEX_", "pali_sent_indices");


/*
相似句
数据迁移 
php ./deploy/migaration/20211127214800_sent_sim.php
php ./deploy/migaration/20211127214900-sent_sim_index.php
redis: 
php ./app/pali_sent/redis_upgrade_pali_sent.php
*/
//sqlite
define("_SQLITE_DB_PALI_SENTENCE_SIM_", "sqlite:" . __DIR__ . "/../tmp/appdata/palicanon/pali_sim.db3");
define("_SQLITE_TABLE_SENT_SIM_", "sent_sim");
define("_SQLITE_TABLE_SENT_SIM_INDEX_", "sent_sim_index");

//PostgreSQL
define("_PG_DB_PALI_SENTENCE_SIM_", _DB_ENGIN_.":host="._DB_HOST_.";port="._DB_PORT_.";dbname="._DB_NAME_.";user="._DB_USERNAME_.";password="._DB_PASSWORD_.";");
define("_PG_TABLE_SENT_SIM_", "sent_sims");
define("_PG_TABLE_SENT_SIM_INDEX_", "sent_sim_indices");


/*
完成度
数据迁移
php ./app/upgrade/upgrade_pali_toc.php
*/
//sqlite
define("_SQLITE_DB_PALI_TOC_", "sqlite:" . __DIR__ . "/../tmp/appdata/palicanon/pali_toc.db3");
define("_SQLITE_TABLE_PROGRESS_", "progress");
define("_SQLITE_TABLE_PROGRESS_CHAPTER_", "progress_chapter");

//PostgreSQL
define("_PG_DB_PALI_TOC_", _DB_ENGIN_.":host="._DB_HOST_.";port="._DB_PORT_.";dbname="._DB_NAME_.";user="._DB_USERNAME_.";password="._DB_PASSWORD_.";");
define("_PG_TABLE_PROGRESS_", "progress");
define("_PG_TABLE_PROGRESS_CHAPTER_", "progress_chapters");





?>