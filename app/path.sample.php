<?php
# 目录
define("_DIR_APPDATA_", __DIR__ . "/../tmp/appdata");

define("_DIR_PALICANON_", __DIR__ . "/../tmp/appdata/palicanon");
define("_DIR_PALICANON_TEMPLET_", __DIR__ . "/../tmp/appdata/palicanon/templet");
define("_DIR_PALICANON_PALITEXT_", __DIR__ . "/../tmp/appdata/palicanon/pali_text");
define("_DIR_PALICANON_WBW_", __DIR__ . "/../tmp/appdata/palicanon/wbw");
define("_DIR_PALICANON_TRAN_", __DIR__ . "/../tmp/appdata/palicanon/translate");

define("_DIR_IMAGES_", __DIR__ . "/../tmp/images");
define("_DIR_IMAGES_ARTICLE_", __DIR__ . "/../tmp/images/article");
define("_DIR_IMAGES_COLLECTION_", __DIR__ . "/../tmp/images/collection");
define("_DIR_IMAGES_COURSE_", __DIR__ . "/../tmp/images/course");
define("_DIR_IMAGES_COURSE_A_", "../../tmp/images/course");
define("_DIR_IMAGES_LESSON_", __DIR__ . "/../tmp/images/lesson");

//语料库

define("_DIR_CSV_PALI_CANON_WORD_", __DIR__ . "/../paliword/book");
define("_DIR_CSV_PALI_CANON_WORD_INDEX_", __DIR__ . "/../paliword/index");

define("_DIR_PALI_CSV_", __DIR__ . "/../tmp/palicsv");
define("_DIR_LOG_", __DIR__ . "/../tmp/log");
define("_DIR_LOG_APP_", __DIR__ . "/../tmp/log/app.log");
define("_DIR_TEMP_", __DIR__ . "/../tmp/temp");
define("_DIR_TEMP_DICT_TEXT_", __DIR__ . "/../tmp/dict_text");
define("_DIR_TMP_", __DIR__ . "/../tmp");
define("_DIR_TMP_EXPORT", __DIR__ . "/../tmp/export");

//dictionary
define("_DIR_DICT_", __DIR__ . "/../tmp/appdata/dict");
define("_DIR_DICT_SYSTEM_", __DIR__ . "/../tmp/appdata/dict/system");
define("_DIR_DICT_3RD_", __DIR__ . "/../tmp/appdata/dict/3rd");
define("_DIR_DICT_REF_", __DIR__ . "/../tmp/appdata/dict/ref");

//界面上的用户指南气泡里面的数据
define("_DIR_USERS_GUIDE_", __DIR__ . "/../app/users_guide");


define("_DIR_FONT_", __DIR__ . "/../font");
define("_DIR_PALI_HTML_", __DIR__ . "/../palihtml");
define("_DIR_DICT_TEXT_", __DIR__ . "/../dicttext");

define("_DIR_PALI_TITLE_", __DIR__ . "/../pali_title");
define("_DIR_APP_", __DIR__ . "/../app");
define("_DIR_LANGUAGE_", __DIR__ . "/../app/public/lang");
define("_DIR_BOOK_INDEX_", __DIR__ . "/../app/public/book_index");

/*user data*/
define("_DIR_USER_BASE_", __DIR__ . "/../tmp/user");
define("_DIR_USER_DOC_", __DIR__ . "/../tmp/user_doc");
define("_DIR_USER_IMG_", __DIR__ . "/../tmp/user/media/3");
define("_DIR_USER_IMG_LINK_", "../../tmp/user/media/3");
define("_DIR_MYDOCUMENT_", "/my_document");

# 逐词解析字典文件
define("_FILE_DB_WBW1_",  __DIR__ . "/../tmp/user/wbw.db3");

#数据库
# 数据库基本参数 pgsql sqlite
define("_DB_ENGIN_", "pgsql");

define("_DB_HOST_", "localhost");
define("_DB_PORT_", "5432");
define("_DB_USERNAME_", "postgres");
define("_DB_PASSWORD_", "");
define("_DB_NAME_", "mint");

//语料库数据表 pali canon db file 


/*
巴利语料模版表
运行app/install/db_insert_templet.php 刷库
*/
define("_FILE_DB_PALICANON_TEMPLET_", _DB_ENGIN_.":host="._DB_HOST_.";port="._DB_PORT_.";dbname="._DB_NAME_.";user="._DB_USERNAME_.";password="._DB_PASSWORD_.";");
//define("_FILE_DB_PALICANON_TEMPLET_","sqlite:" . __DIR__ . "/../tmp/appdata/palicanon/templet.db3");
define("_TABLE_PALICANON_TEMPLET_","wbw_templet");

/*
标题资源表
app/install/db_update_toc.php 刷库
*/
define("_FILE_DB_RESRES_INDEX_", _DB_ENGIN_.":host="._DB_HOST_.";port="._DB_PORT_.";dbname="._DB_NAME_.";user="._DB_USERNAME_.";password="._DB_PASSWORD_.";");
//define("_FILE_DB_RESRES_INDEX_", "sqlite:" . __DIR__ . "/../tmp/appdata/palicanon/res.db3");
define("_TABLE_RES_INDEX_","res_index");

/*
巴利语料段落表
刷库 app/install/db_insert_palitext.php
更新 app/install/db_update_palitext.php
*/
define("_FILE_DB_PALITEXT_", _DB_ENGIN_.":host="._DB_HOST_.";port="._DB_PORT_.";dbname="._DB_NAME_.";user="._DB_USERNAME_.";password="._DB_PASSWORD_.";");
//define("_FILE_DB_PALITEXT_", "sqlite:" . __DIR__ . "/../tmp/appdata/palicanon/pali_text.db3");
define("_TABLE_PALI_TEXT_","pali_text");
define("_TABLE_PALI_BOOK_NAME_","books");

#单词表部分
/*
以书为单位的单词汇总表
填充 /app/install/db_insert_bookword_from_csv.php
*/
define("_FILE_DB_BOOK_WORD_", _DB_ENGIN_.":host="._DB_HOST_.";port="._DB_PORT_.";dbname="._DB_NAME_.";user="._DB_USERNAME_.";password="._DB_PASSWORD_.";");
//define("_FILE_DB_BOOK_WORD_", "sqlite:" . __DIR__ . "/../tmp/appdata/palicanon/bookword.db3");
define("_TABLE_BOOK_WORD_", "bookword");

/*
单词索引
/app/install/db_insert_word_from_csv.php
/app/admin/word_index_weight_refresh.php
*/
define("_FILE_DB_PALI_INDEX_", _DB_ENGIN_.":host="._DB_HOST_.";port="._DB_PORT_.";dbname="._DB_NAME_.";user="._DB_USERNAME_.";password="._DB_PASSWORD_.";");
//define("_FILE_DB_PALI_INDEX_", "sqlite:" . __DIR__ . "/../tmp/appdata/palicanon/paliindex.db3");
define("_TABLE_WORD_", "word");

/*
92万词
/app/install/db_insert_wordindex_from_csv.php
*/
define("_FILE_DB_WORD_INDEX_", _DB_ENGIN_.":host="._DB_HOST_.";port="._DB_PORT_.";dbname="._DB_NAME_.";user="._DB_USERNAME_.";password="._DB_PASSWORD_.";");
//define("_FILE_DB_WORD_INDEX_", "sqlite:" . __DIR__ . "/../tmp/appdata/palicanon/wordindex.db3");
define("_TABLE_WORD_INDEX_", "wordindex");

//单词索引=92万词+单词索引
define("_FILE_DB_INDEX_", _DB_ENGIN_.":host="._DB_HOST_.";port="._DB_PORT_.";dbname="._DB_NAME_.";user="._DB_USERNAME_.";password="._DB_PASSWORD_.";");
//define("_FILE_DB_INDEX_", "sqlite:" . __DIR__ . "/../tmp/appdata/palicanon/index.db3");

//黑体字数据表
//define("_FILE_DB_BOLD_", _DB_ENGIN_.":host="._DB_HOST_.";port="._DB_PORT_.";dbname="._DB_NAME_.";user="._DB_USERNAME_.";password="._DB_PASSWORD_.";");
define("_FILE_DB_BOLD_", "sqlite:" . __DIR__ . "/../tmp/appdata/palicanon/bold.db3");


//单词分析表
//define("_FILE_DB_STATISTICS_", _DB_ENGIN_.":host="._DB_HOST_.";port="._DB_PORT_.";dbname="._DB_NAME_.";user="._DB_USERNAME_.";password="._DB_PASSWORD_.";");
define("_FILE_DB_STATISTICS_", "sqlite:" . __DIR__ . "/../tmp/appdata/palicanon/word_statistics.db3");

//巴利句子表
//define("_FILE_DB_PALI_SENTENCE_", _DB_ENGIN_.":host="._DB_HOST_.";port="._DB_PORT_.";dbname="._DB_NAME_.";user="._DB_USERNAME_.";password="._DB_PASSWORD_.";");
define("_FILE_DB_PALI_SENTENCE_", "sqlite:" . __DIR__ . "/../tmp/appdata/palicanon/pali_sent1.db3");

//相似句
//define("_FILE_DB_PALI_SENTENCE_SIM_", _DB_ENGIN_.":host="._DB_HOST_.";port="._DB_PORT_.";dbname="._DB_NAME_.";user="._DB_USERNAME_.";password="._DB_PASSWORD_.";");
define("_FILE_DB_PALI_SENTENCE_SIM_", "sqlite:" . __DIR__ . "/../tmp/appdata/palicanon/pali_sim.db3");

//标题表
//define("_FILE_DB_PALI_TOC_", _DB_ENGIN_.":host="._DB_HOST_.";port="._DB_PORT_.";dbname="._DB_NAME_.";user="._DB_USERNAME_.";password="._DB_PASSWORD_.";");
define("_FILE_DB_PALI_TOC_", "sqlite:" . __DIR__ . "/../tmp/appdata/palicanon/pali_toc.db3");


//页码对应
//define("_FILE_DB_PAGE_INDEX_", _DB_ENGIN_.":host="._DB_HOST_.";port="._DB_PORT_.";dbname="._DB_NAME_.";user="._DB_USERNAME_.";password="._DB_PASSWORD_.";");
define("_FILE_DB_PAGE_INDEX_", "sqlite:" . __DIR__ . "/../tmp/appdata/palicanon/pagemap.db3");


# 字典数据表 全部存入redis
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


#参考字典
//define("_FILE_DB_REF_", _DB_ENGIN_.":host="._DB_HOST_.";port="._DB_PORT_.";dbname="._DB_NAME_.";user="._DB_USERNAME_.";password="._DB_PASSWORD_.";");
define("_FILE_DB_REF_", "sqlite:" . __DIR__ . "/../tmp/appdata/dict/system/ref.db");
define("_TABLE_DICT_REF_", "dict");
define("_TABLE_DICT_REF_NAME_LIST_", "info");

#参考字典索引
//define("_FILE_DB_REF_INDEX_", _DB_ENGIN_.":host="._DB_HOST_.";port="._DB_PORT_.";dbname="._DB_NAME_.";user="._DB_USERNAME_.";password="._DB_PASSWORD_.";");
define("_FILE_DB_REF_INDEX_", "sqlite:" . __DIR__ . "/../tmp/appdata/dict/system/ref1.db");
define("_TABLE_REF_INDEX_", "dict");

#为了切分复合词 使用的词头表
//define("_FILE_DB_PART_", _DB_ENGIN_.":host="._DB_HOST_.";port="._DB_PORT_.";dbname="._DB_NAME_.";user="._DB_USERNAME_.";password="._DB_PASSWORD_.";");
define("_FILE_DB_PART_", "sqlite:" . __DIR__ . "/../tmp/appdata/dict/system/part.db3");
define("_TABLE_PART_", "part");


# 用户数据表

//读写频繁
# 逐词解析表
//define("_FILE_DB_USER_WBW_", _DB_ENGIN_.":host="._DB_HOST_.";port="._DB_PORT_.";dbname="._DB_NAME_.";user="._DB_USERNAME_.";password="._DB_PASSWORD_.";");
define("_FILE_DB_USER_WBW_", "sqlite:" . __DIR__ . "/../tmp/user/user_wbw.db3");
define("_TABLE_USER_WBW_", "wbw");
define("_TABLE_USER_WBW_BLOCK_", "wbw_block");

# 译文
//define("_FILE_DB_SENTENCE_", _DB_ENGIN_.":host="._DB_HOST_.";port="._DB_PORT_.";dbname="._DB_NAME_.";user="._DB_USERNAME_.";password="._DB_PASSWORD_.";");
define("_FILE_DB_SENTENCE_", "sqlite:" . __DIR__ . "/../tmp/user/sentence.db3");
define("_TABLE_SENTENCE_", "sentence");
define("_TABLE_SENTENCE_BLOCK_", "sent_block");

# 译文编辑历史
//define("_FILE_DB_USER_SENTENCE_HISTORAY_", _DB_ENGIN_.":host="._DB_HOST_.";port="._DB_PORT_.";dbname="._DB_NAME_.";user="._DB_USERNAME_.";password="._DB_PASSWORD_.";");
define("_FILE_DB_USER_SENTENCE_HISTORAY_", "sqlite:" . __DIR__ . "/../tmp/user/usent_historay.db3");
define("_TABLE_SENTENCE_HISTORAY_", "sent_historay");
# 逐词解析字典
//define("_FILE_DB_WBW_", _DB_ENGIN_.":host="._DB_HOST_.";port="._DB_PORT_.";dbname="._DB_NAME_.";user="._DB_USERNAME_.";password="._DB_PASSWORD_.";");
define("_FILE_DB_WBW_", "sqlite:" . __DIR__ . "/../tmp/user/wbw.db3");
define("_TABLE_DICT_WBW_", "wbw");
define("_TABLE_DICT_WBW_INDEX_", "wbw_index");


//写入频繁 读取不频繁
# 用户行为记录
//define("_FILE_DB_PAGE_INDEX_", _DB_ENGIN_.":host="._DB_HOST_.";port="._DB_PORT_.";dbname="._DB_NAME_.";user="._DB_USERNAME_.";password="._DB_PASSWORD_.";");
define("_FILE_DB_USER_ACTIVE_", "sqlite:" . __DIR__ . "/../tmp/user/user_active.db3");


//define("_FILE_DB_USER_ACTIVE_LOG_", _DB_ENGIN_.":host="._DB_HOST_.";port="._DB_PORT_.";dbname="._DB_NAME_.";user="._DB_USERNAME_.";password="._DB_PASSWORD_.";");
define("_FILE_DB_USER_ACTIVE_LOG_", "sqlite:" . __DIR__ . "/../tmp/user/user_active_log.db3");


//读取频繁 写入不频繁 
# 用户账号
//define("_FILE_DB_USERINFO_", _DB_ENGIN_.":host="._DB_HOST_.";port="._DB_PORT_.";dbname="._DB_NAME_.";user="._DB_USERNAME_.";password="._DB_PASSWORD_.";");
define("_FILE_DB_USERINFO_", "sqlite:" . __DIR__ . "/../tmp/user/userinfo.db3");

# 版本风格 
//define("_FILE_DB_CHANNAL_", _DB_ENGIN_.":host="._DB_HOST_.";port="._DB_PORT_.";dbname="._DB_NAME_.";user="._DB_USERNAME_.";password="._DB_PASSWORD_.";");
define("_FILE_DB_CHANNAL_", "sqlite:" . __DIR__ . "/../tmp/user/channal.db3");

# 文章 文集
//define("_FILE_DB_USER_ARTICLE_", _DB_ENGIN_.":host="._DB_HOST_.";port="._DB_PORT_.";dbname="._DB_NAME_.";user="._DB_USERNAME_.";password="._DB_PASSWORD_.";");
define("_FILE_DB_USER_ARTICLE_", "sqlite:" . __DIR__ . "/../tmp/user/article.db3");

# 术语
//define("_FILE_DB_TERM_", _DB_ENGIN_.":host="._DB_HOST_.";port="._DB_PORT_.";dbname="._DB_NAME_.";user="._DB_USERNAME_.";password="._DB_PASSWORD_.";");
define("_FILE_DB_TERM_", "sqlite:" . __DIR__ . "/../tmp/user/dhammaterm.db");

# 协作
//define("_FILE_DB_USER_SHARE_", _DB_ENGIN_.":host="._DB_HOST_.";port="._DB_PORT_.";dbname="._DB_NAME_.";user="._DB_USERNAME_.";password="._DB_PASSWORD_.";");
define("_FILE_DB_USER_SHARE_", "sqlite:" . __DIR__ . "/../tmp/user/share.db3");

# 工作组
//define("_FILE_DB_GROUP_", _DB_ENGIN_.":host="._DB_HOST_.";port="._DB_PORT_.";dbname="._DB_NAME_.";user="._DB_USERNAME_.";password="._DB_PASSWORD_.";");
define("_FILE_DB_GROUP_", "sqlite:" . __DIR__ . "/../tmp/user/group.db3");

# 逐词解析文件索引
//define("_FILE_DB_FILEINDEX_", _DB_ENGIN_.":host="._DB_HOST_.";port="._DB_PORT_.";dbname="._DB_NAME_.";user="._DB_USERNAME_.";password="._DB_PASSWORD_.";");
define("_FILE_DB_FILEINDEX_", "sqlite:" . __DIR__ . "/../tmp/user/fileindex.db");

# 课程
//define("_FILE_DB_COURSE_", "pgsql:host="._DB_HOST_.";port="._DB_PORT_.";dbname="._DB_NAME_.";user="._DB_USERNAME_.";password="._DB_PASSWORD_.";");
define("_FILE_DB_COURSE_", "sqlite:" . __DIR__ . "/../tmp/user/course.db3");
define("_TABLE_COURSE_","course");

# 用户自定义书
//define("_FILE_DB_USER_CUSTOM_BOOK_", _DB_ENGIN_.":host="._DB_HOST_.";port="._DB_PORT_.";dbname="._DB_NAME_.";user="._DB_USERNAME_.";password="._DB_PASSWORD_.";");
define("_FILE_DB_USER_CUSTOM_BOOK_", "sqlite:" . __DIR__ . "/../tmp/user/custom_book.db3");

# 逐词译和译文编辑消息
//define("_FILE_DB_MESSAGE_", _DB_ENGIN_.":host="._DB_HOST_.";port="._DB_PORT_.";dbname="._DB_NAME_.";user="._DB_USERNAME_.";password="._DB_PASSWORD_.";");
define("_FILE_DB_MESSAGE_", "sqlite:" . __DIR__ . "/../tmp/user/message.db");

#点赞
//define("_FILE_DB_LIKE_", _DB_ENGIN_.":host="._DB_HOST_.";port="._DB_PORT_.";dbname="._DB_NAME_.";user="._DB_USERNAME_.";password="._DB_PASSWORD_.";");
define("_FILE_DB_LIKE_", "sqlite:" . __DIR__ . "/../tmp/user/like.db3");



//很少使用
# 网站设置
//define("_FILE_DB_HOSTSETTING_", _DB_ENGIN_.":host="._DB_HOST_.";port="._DB_PORT_.";dbname="._DB_NAME_.";user="._DB_USERNAME_.";password="._DB_PASSWORD_.";");
define("_FILE_DB_HOSTSETTING_", "sqlite:" . __DIR__ . "/../tmp/user/hostsetting.db3");

# 用户图片数据 尚未启用
//define("_FILE_DB_MEDIA_", _DB_ENGIN_.":host="._DB_HOST_.";port="._DB_PORT_.";dbname="._DB_NAME_.";user="._DB_USERNAME_.";password="._DB_PASSWORD_.";");
define("_FILE_DB_MEDIA_", "sqlite:" . __DIR__ . "/../tmp/user/media.db3");

# 用户字典统计数据
//define("_FILE_DB_USER_DICT_", _DB_ENGIN_.":host="._DB_HOST_.";port="._DB_PORT_.";dbname="._DB_NAME_.";user="._DB_USERNAME_.";password="._DB_PASSWORD_.";");
define("_FILE_DB_USER_DICT_", "sqlite:" . __DIR__ . "/../tmp/user/udict.db3");


# 评论 尚未启用
//define("_FILE_DB_COMMENTS_", _DB_ENGIN_.":host="._DB_HOST_.";port="._DB_PORT_.";dbname="._DB_NAME_.";user="._DB_USERNAME_.";password="._DB_PASSWORD_.";");
define("_FILE_DB_COMMENTS_", "sqlite:" . __DIR__ . "/../tmp/user/comments.db3");


//define("_FILE_DB_USER_STATISTICS_", _DB_ENGIN_.":host="._DB_HOST_.";port="._DB_PORT_.";dbname="._DB_NAME_.";user="._DB_USERNAME_.";password="._DB_PASSWORD_.";");
define("_FILE_DB_USER_STATISTICS_", "sqlite:" . __DIR__ . "/../tmp/user/statistics.db3");

#权限管理
//define("_FILE_DB_USER_RBAC_", _DB_ENGIN_.":host="._DB_HOST_.";port="._DB_PORT_.";dbname="._DB_NAME_.";user="._DB_USERNAME_.";password="._DB_PASSWORD_.";");
define("_FILE_DB_USER_RBAC_",  __DIR__ . "/../tmp/user/rbac.db3");
