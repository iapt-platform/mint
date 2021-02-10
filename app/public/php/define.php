<?PHP
# 内容的可见性，应用于文集，用户译文等 当有些内容不适合删除时，disable 代替 deleted
define("_CONTENT_VISIBILITY_DELETED_" , -1);
define("_CONTENT_VISIBILITY_DISABLE_" , 0);
define("_CONTENT_VISIBILITY_PRIVATE_" , 10);
define("_CONTENT_VISIBILITY_UNLISTED_" , 20);
define("_CONTENT_VISIBILITY_PUBLIC_" , 30);

#用户操作
define("_CHANNEL_EDIT_",10);
define("_CHANNEL_NEW_",11);
define("_ARTICLE_EDIT_",20);
define("_ARTICLE_NEW_",21);
define("_DICT_LOOKUP_",30);
define("_TERM_EDIT_",40);
define("_TERM_LOOKUP_",41);
define("_TERM_IN_SENT_",42);
define("_SEARCH_",50);
define("_WBW_EDIT_",60);
define("_WBW_RELATION_",61);
define("_WBW_NEW_",62);
define("_SENT_EDIT_",70);
define("_SENT_NEW_",71);
define("_COLLECTION_EDIT_",80);
define("_COLLECTION_NEW_",81);
define("_NISSAYA_FIND_",90);
define("_OPEN_READER_",100);
define("_OPEN_STUDIO_",101);

?>