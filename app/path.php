<?php
define("_DB_ENGIN_", "sqlite");

define("_DB_HOST_", "localhost");
define("_DB_USERNAME_", "");
define("_DB_PASSWORD_", "");

define("_DB_NAME_PALICANON_", "palicanon");
define("_DB_NAME_DICTIONARY_", "dictionary");
define("_DB_NAME_USERDATA_", "userdata");

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

//pali canon db file 语料库
define("_FILE_DB_PALICANON_TEMPLET_", __DIR__ . "/../tmp/appdata/palicanon/templet.db3");
define("_FILE_DB_RESRES_INDEX_", "sqlite:" . __DIR__ . "/../tmp/appdata/palicanon/res.db3");
define("_FILE_DB_PALITEXT_", "sqlite:" . __DIR__ . "/../tmp/appdata/palicanon/pali_text.db3");
define("_FILE_DB_STATISTICS_", "sqlite:" . __DIR__ . "/../tmp/appdata/palicanon/word_statistics.db3");
define("_FILE_DB_PALI_SENTENCE_", "sqlite:" . __DIR__ . "/../tmp/appdata/palicanon/pali_sent1.db3");
define("_FILE_DB_PALI_SENTENCE_SIM_", "sqlite:" . __DIR__ . "/../tmp/appdata/palicanon/pali_sim.db3");
define("_FILE_DB_PALI_TOC_", "sqlite:" . __DIR__ . "/../tmp/appdata/palicanon/pali_toc.db3");
define("_FILE_DB_INDEX_", "sqlite:" . __DIR__ . "/../tmp/appdata/palicanon/index.db3");
define("_FILE_DB_WORD_INDEX_", "sqlite:" . __DIR__ . "/../tmp/appdata/palicanon/wordindex.db3");
define("_FILE_DB_PALI_INDEX_", "sqlite:" . __DIR__ . "/../tmp/appdata/palicanon/paliindex.db3");
define("_FILE_DB_PAGE_INDEX_", "sqlite:" . __DIR__ . "/../tmp/appdata/palicanon/pagemap.db3");
define("_FILE_DB_BOOK_WORD_", "sqlite:" . __DIR__ . "/../tmp/appdata/palicanon/bookword.db3");
define("_FILE_DB_BOLD_", "sqlite:" . __DIR__ . "/../tmp/appdata/palicanon/bold.db3");

//语料库

define("_DIR_CSV_PALI_CANON_WORD_", __DIR__ . "/../paliword/book");
define("_DIR_CSV_PALI_CANON_WORD_INDEX_", __DIR__ . "/../paliword/index");

define("_DIR_PALI_CSV_", __DIR__ . "/../tmp/palicsv");
define("_DIR_LOG_", __DIR__ . "/../tmp/log");
define("_DIR_TEMP_", __DIR__ . "/../tmp/temp");
define("_DIR_TMP_", __DIR__ . "/../tmp");

//dictionary
define("_DIR_DICT_", __DIR__ . "/../tmp/appdata/dict");
define("_DIR_DICT_SYSTEM_", __DIR__ . "/../tmp/appdata/dict/system");
define("_DIR_DICT_3RD_", __DIR__ . "/../tmp/appdata/dict/3rd");
define("_DIR_DICT_REF_", __DIR__ . "/../tmp/appdata/dict/ref");

define("_DIR_USERS_GUIDE_", __DIR__ . "/../documents/users_guide");

#参考字典
define("_FILE_DB_REF_", "sqlite:" . __DIR__ . "/../tmp/appdata/dict/system/ref.db");
define("_TABLE_DICT_REF_", "dict");
define("_TABLE_DICT_REF_NAME_LIST_", "info");

#参考字典索引
define("_FILE_DB_REF_INDEX_", "sqlite:" . __DIR__ . "/../tmp/appdata/dict/system/ref1.db");
define("_TABLE_REF_INDEX_", "dict");

#为了切分复合词 使用的词头表
define("_FILE_DB_PART_", "sqlite:" . __DIR__ . "/../tmp/appdata/dict/system/part.db3");
define("_TABLE_PART_", "part");

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

define("_FILE_DB_USER_WBW_", "sqlite:" . __DIR__ . "/../tmp/user/user_wbw.db3");
define("_FILE_DB_COMMENTS_", "sqlite:" . __DIR__ . "/../tmp/user/comments.db3");
define("_FILE_DB_SENTENCE_", "sqlite:" . __DIR__ . "/../tmp/user/sentence.db3");
define("_FILE_DB_TERM_", "sqlite:" . __DIR__ . "/../tmp/user/dhammaterm.db");
define("_FILE_DB_GROUP_", "sqlite:" . __DIR__ . "/../tmp/user/group.db3");
define("_FILE_DB_USERINFO_", "sqlite:" . __DIR__ . "/../tmp/user/userinfo.db3");
define("_FILE_DB_FILEINDEX_", "sqlite:" . __DIR__ . "/../tmp/user/fileindex.db");
define("_FILE_DB_WBW_", "sqlite:" . __DIR__ . "/../tmp/user/wbw.db3");
define("_FILE_DB_COURSE_", "sqlite:" . __DIR__ . "/../tmp/user/course.db3");
define("_FILE_DB_MEDIA_", "sqlite:" . __DIR__ . "/../tmp/user/media.db3");
define("_FILE_DB_MESSAGE_", "sqlite:" . __DIR__ . "/../tmp/user/message.db");
define("_FILE_DB_USER_STATISTICS_", "sqlite:" . __DIR__ . "/../tmp/user/statistics.db3");
define("_FILE_DB_CHANNAL_", "sqlite:" . __DIR__ . "/../tmp/user/channal.db3");
define("_FILE_DB_USER_DICT_", "sqlite:" . __DIR__ . "/../tmp/user/udict.db3");
define("_FILE_DB_USER_ARTICLE_", "sqlite:" . __DIR__ . "/../tmp/user/article.db3");
define("_FILE_DB_HOSTSETTING_", "sqlite:" . __DIR__ . "/../tmp/user/hostsetting.db3");
define("_FILE_DB_USER_SENTENCE_HISTORAY_", "sqlite:" . __DIR__ . "/../tmp/user/usent_historay.db3");
define("_FILE_DB_USER_ACTIVE_", "sqlite:" . __DIR__ . "/../tmp/user/user_active.db3");
define("_FILE_DB_USER_ACTIVE_LOG_", "sqlite:" . __DIR__ . "/../tmp/user/user_active_log.db3");
