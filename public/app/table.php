<?php 
#表名设置，不能更改
define("_DB_ENGIN_", Database["type"]);
define("_DB_HOST_", Database["server"]);
define("_DB_PORT_", Database["port"]);
define("_DB_NAME_", Database["name"]);
define("_DB_USERNAME_", Database["user"]);
define("_DB_PASSWORD_", Database["password"]);

//PostgreSQL
define("_PDO_DB_DSN_", Database["type"].":host=".Database["server"].";port=".Database["port"].";dbname=".Database["name"].";user=".Database["user"].";password=".Database["password"].";");

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


# 用户数据表

//读写频繁
# 逐词解析表
#sqlite
define("_SQLITE_DB_USER_WBW_", "sqlite:" . __DIR__ . "/../tmp/user/user_wbw.db3");
define("_SQLITE_TABLE_USER_WBW_", "wbw");
define("_SQLITE_TABLE_USER_WBW_BLOCK_", "wbw_block");

//PostgreSQL
define("_PG_DB_USER_WBW_", _PDO_DB_DSN_);
define("_PG_TABLE_USER_WBW_", "wbws");
define("_PG_TABLE_USER_WBW_BLOCK_", "wbw_blocks");


# 译文
#sqlite
define("_SQLITE_DB_SENTENCE_", "sqlite:" . __DIR__ . "/../tmp/user/sentence.db3");
define("_SQLITE_TABLE_SENTENCE_", "sentence");
define("_SQLITE_TABLE_SENTENCE_BLOCK_", "sent_block");
define("_SQLITE_TABLE_SENTENCE_PR_", "sent_pr");

//PostgreSQL
define("_PG_DB_SENTENCE_", _PDO_DB_DSN_);
define("_PG_TABLE_SENTENCE_", "sentences");
define("_PG_TABLE_SENTENCE_BLOCK_", "sent_blocks");
define("_PG_TABLE_SENTENCE_PR_", "sent_prs");



# 译文编辑历史
#sqlite
define("_SQLITE_DB_USER_SENTENCE_HISTORAY_", "sqlite:" . __DIR__ . "/../tmp/user/usent_historay.db3");
define("_SQLITE_TABLE_SENTENCE_HISTORAY_", "sent_historay");

//PostgreSQL
define("_PG_DB_USER_SENTENCE_HISTORAY_", _PDO_DB_DSN_);
define("_PG_TABLE_SENTENCE_HISTORAY_", "sent_historaies");



# 逐词解析字典
# sqlite
define("_SQLITE_DB_WBW_", "sqlite:" . __DIR__ . "/../tmp/user/wbw.db3");
define("_SQLITE_TABLE_DICT_WBW_", "wbw");
define("_SQLITE_TABLE_DICT_WBW_INDEX_", "wbw_index");

//PostgreSQL
define("_PG_DB_WBW_", _PDO_DB_DSN_);
define("_PG_TABLE_DICT_WBW_", "user_dicts");


//写入频繁 读取不频繁
# 用户行为记录
#sqlite
define("_SQLITE_DB_USER_ACTIVE_", "sqlite:" . __DIR__ . "/../tmp/user/user_active.db3");
define("_SQLITE_TABLE_USER_OPERATION_DAILY_", "active_index");
define("_SQLITE_TABLE_USER_OPERATION_FRAME_", "edit");

//PostgreSQL
define("_PG_DB_USER_ACTIVE_", _PDO_DB_DSN_);
define("_PG_TABLE_USER_OPERATION_DAILY_", "user_operation_dailys");
define("_PG_TABLE_USER_OPERATION_FRAME_", "user_operation_frames");

#sqlite
define("_SQLITE_DB_USER_ACTIVE_LOG_", "sqlite:" . __DIR__ . "/../tmp/user/user_active_log.db3");
define("_SQLITE_TABLE_USER_OPERATION_LOG_", "log");

#PostgreSQL
define("_PG_DB_USER_ACTIVE_LOG_", _PDO_DB_DSN_);
define("_PG_TABLE_USER_OPERATION_LOG_", "user_operation_logs");



//读取频繁 写入不频繁 
# 用户账号
#sqlite
define("_SQLITE_DB_USERINFO_", "sqlite:" . __DIR__ . "/../tmp/user/userinfo.db3");
define("_SQLITE_TABLE_USER_INFO_", "user");

#pg
define("_PG_DB_USERINFO_", _PDO_DB_DSN_);
define("_PG_TABLE_USER_INFO_", "user_infos");


# 版本风格 
#sqlite
define("_SQLITE_DB_CHANNAL_", "sqlite:" . __DIR__ . "/../tmp/user/channal.db3");
define("_SQLITE_TABLE_CHANNEL_", "channal");

#pg
define("_PG_DB_CHANNAL_", _PDO_DB_DSN_);
define("_PG_TABLE_CHANNEL_", "channels");


# 文章 文集
# sqlite
define("_SQLITE_DB_USER_ARTICLE_", "sqlite:" . __DIR__ . "/../tmp/user/article.db3");
define("_SQLITE_TABLE_ARTICLE_", "article");
define("_SQLITE_TABLE_COLLECTION_", "collect");
define("_SQLITE_TABLE_ARTICLE_COLLECTION_", "article_list");


# pg
define("_PG_DB_USER_ARTICLE_", _PDO_DB_DSN_);
define("_PG_TABLE_ARTICLE_", "articles");
define("_PG_TABLE_COLLECTION_", "collections");
define("_PG_TABLE_ARTICLE_COLLECTION_", "article_collection");

# 术语
define("_SQLITE_DB_TERM_", "sqlite:" . __DIR__ . "/../tmp/user/dhammaterm.db");
define("_SQLITE_TABLE_TERM_", "term");

define("_PG_DB_TERM_", _PDO_DB_DSN_);
define("_PG_TABLE_TERM_", "term");

# 协作
define("_SQLITE_DB_USER_SHARE_", "sqlite:" . __DIR__ . "/../tmp/user/share.db3");
define("_SQLITE_TABLE_USER_SHARE_", "share_cooperator");

define("_PG_DB_USER_SHARE_", _PDO_DB_DSN_);
define("_PG_TABLE_USER_SHARE_", "share_cooperator");

# 工作组
define("_SQLITE_DB_GROUP_", "sqlite:" . __DIR__ . "/../tmp/user/group.db3");
define("_SQLITE_TABLE_GROUP_INFO_", "group_info");
define("_SQLITE_TABLE_GROUP_MEMBER_", "group_member");

define("_PG_DB_GROUP_", _PDO_DB_DSN_);
define("_PG_TABLE_GROUP_INFO_", "group_info");
define("_PG_TABLE_GROUP_MEMBER_", "group_member");

# 逐词解析文件索引
define("_SQLITE_DB_FILEINDEX_", "sqlite:" . __DIR__ . "/../tmp/user/fileindex.db");
define("_SQLITE_TABLE_FILEINDEX_", "fileindex");
define("_SQLITE_TABLE_FILEINDEX_POWER_", "power");

define("_PG_DB_FILEINDEX_", _PDO_DB_DSN_);
define("_PG_TABLE_FILEINDEX_", "fileindex");
define("_PG_TABLE_FILEINDEX_POWER_", "power");

# 课程
define("_SQLITE_DB_COURSE_", "sqlite:" . __DIR__ . "/../tmp/user/course.db3");
define("_SQLITE_TABLE_COURSE_","course");
define("_SQLITE_TABLE_LESSON_","lesson");

define("_PG_DB_COURSE_", _PDO_DB_DSN_);
define("_PG_TABLE_COURSE_","course");
define("_PG_TABLE_LESSON_","lesson");

# 用户自定义书
define("_SQLITE_DB_USER_CUSTOM_BOOK_", "sqlite:" . __DIR__ . "/../tmp/user/custom_book.db3");
define("_SQLITE_TABLE_CUSTOM_BOOK_", "custom_book");
define("_SQLITE_TABLE_CUSTOM_BOOK_SENT_", "custom_book_sentence");

define("_PG_DB_USER_CUSTOM_BOOK_", _PDO_DB_DSN_);
define("_PG_TABLE_CUSTOM_BOOK_", "custom_book");
define("_PG_TABLE_CUSTOM_BOOK_SENT_", "custom_book_sentence");

# 逐词译和译文编辑消息 无需迁移数据
define("_SQLITE_DB_MESSAGE_", "sqlite:" . __DIR__ . "/../tmp/user/message.db");
define("_SQLITE_TABLE_MESSAGE_", "message");

define("_PG_DB_MESSAGE_", _PDO_DB_DSN_);
define("_PG_TABLE_MESSAGE_", "message");

#点赞
define("_SQLITE_DB_LIKE_", "sqlite:" . __DIR__ . "/../tmp/user/like.db3");
define("_SQLITE_TABLE_LIKE_", "likes");

define("_PG_DB_LIKE_", _PDO_DB_DSN_);
define("_PG_TABLE_LIKE_", "likes");

# 用户字典统计数据 刷库 - 无需迁移数据
define("_SQLITE_DB_USER_DICT_", "sqlite:" . __DIR__ . "/../tmp/user/udict.db3");
define("_SQLITE_TABLE_USER_DICT_", "udict");

define("_PG_DB_USER_DICT_", _PDO_DB_DSN_);
define("_PG_TABLE_USER_DICT_", "udict");

# 页码对应
define("_SQLITE_DB_PAGE_INDEX_", "sqlite:" . __DIR__ . "/../tmp/appdata/palicanon/pagemap.db3");
define("_SQLITE_TABLE_PAGE_MATCH_", 'pagematch');
define("_SQLITE_TABLE_BOOK_MATCH_", 'book_match');
define("_SQLITE_TABLE_CS6_PARA_", 'cs6_para');
define("_SQLITE_TABLE_MY_PAGE_", 'm');
define("_SQLITE_TABLE_PAGE_NUMBER_", 'multi_edition_page_number');

define("_PG_DB_PAGE_INDEX_", _PDO_DB_DSN_);
define("_PG_TABLE_PAGE_MATCH_", 'pagematch');
define("_PG_TABLE_BOOK_MATCH_", 'book_match');
define("_PG_TABLE_CS6_PARA_", 'cs6_para');
define("_PG_TABLE_MY_PAGE_", 'm');
define("_PG_TABLE_PAGE_NUMBER_", 'multi_edition_page_number');


#参考字典
define("_SQLITE_DB_REF_", "sqlite:" . __DIR__ . "/../tmp/appdata/dict/system/ref.db");
define("_SQLITE_TABLE_DICT_REF_", "dict");
define("_SQLITE_TABLE_DICT_REF_NAME_", "info");

define("_PG_DB_REF_", _PDO_DB_DSN_);
define("_PG_TABLE_DICT_REF_", "dict");
define("_PG_TABLE_DICT_REF_NAME_", "info");

#参考字典索引
define("_SQLITE_DB_REF_INDEX_", "sqlite:" . __DIR__ . "/../tmp/appdata/dict/system/ref1.db");
define("_SQLITE_TABLE_REF_INDEX_", "dict");

define("_PG_DB_REF_INDEX_", _PDO_DB_DSN_);
define("_PG_TABLE_REF_INDEX_", "dict");

#无需迁移

# 用户图片数据 尚未启用
define("_FILE_DB_MEDIA_", "sqlite:" . __DIR__ . "/../tmp/user/media.db3");


#权限管理 casbin使用
define("_FILE_DB_USER_RBAC_",  __DIR__ . "/../tmp/user/rbac.db3");


# 全文搜索
define("_TABLE_FTS_", "fts_texts");

//很少使用
# 网站设置
define("_FILE_DB_HOSTSETTING_", "sqlite:" . __DIR__ . "/../tmp/user/hostsetting.db3");

#巴缅字典
//define("_DICT_DB_PM_", _DB_ENGIN_.":host="._DB_HOST_.";port="._DB_PORT_.";dbname="._DB_NAME_.";user="._DB_USERNAME_.";password="._DB_PASSWORD_.";");
define("_DICT_DB_PM_", "sqlite:" . __DIR__ . "/../tmp/appdata/dict/3rd/pm.db");
define("_TABLE_DICT_PM_", "dict");

#系统规则
//define("_DICT_DB_REGULAR_", _DB_ENGIN_.":host="._DB_HOST_.";port="._DB_PORT_.";dbname="._DB_NAME_.";user="._DB_USERNAME_.";password="._DB_PASSWORD_.";");
define("_DICT_DB_REGULAR_", "sqlite:" . __DIR__ . "/../tmp/appdata/dict/system/sys_regular.db");
define("_TABLE_DICT_REGULAR_", "dict");

#系统不规则
//define("_DICT_DB_IRREGULAR_", _DB_ENGIN_.":host="._DB_HOST_.";port="._DB_PORT_.";dbname="._DB_NAME_.";user="._DB_USERNAME_.";password="._DB_PASSWORD_.";");
define("_DICT_DB_IRREGULAR_", "sqlite:" . __DIR__ . "/../tmp/appdata/dict/system/sys_irregular.db");
define("_TABLE_DICT_IRREGULAR_", "dict");

#自动compone
//define("_DICT_DB_COMP_", _DB_ENGIN_.":host="._DB_HOST_.";port="._DB_PORT_.";dbname="._DB_NAME_.";user="._DB_USERNAME_.";password="._DB_PASSWORD_.";");
define("_DICT_DB_COMP_", "sqlite:" . __DIR__ . "/../tmp/appdata/dict/system/comp.db");
define("_TABLE_DICT_COMP_", "dict");



#为了切分复合词 使用的词头表
//define("_FILE_DB_PART_", _DB_ENGIN_.":host="._DB_HOST_.";port="._DB_PORT_.";dbname="._DB_NAME_.";user="._DB_USERNAME_.";password="._DB_PASSWORD_.";");
define("_FILE_DB_PART_", "sqlite:" . __DIR__ . "/../tmp/appdata/dict/system/part.db3");
define("_TABLE_PART_", "part");

# 评论 已经废弃
//define("_FILE_DB_COMMENTS_", _DB_ENGIN_.":host="._DB_HOST_.";port="._DB_PORT_.";dbname="._DB_NAME_.";user="._DB_USERNAME_.";password="._DB_PASSWORD_.";");
define("_FILE_DB_COMMENTS_", "sqlite:" . __DIR__ . "/../tmp/user/comments.db3");

?>