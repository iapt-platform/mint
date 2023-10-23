<?php

$tables=[
    'article_collections' => [
        'key'=>'id',
        'time1'=>'',
        'time2'=>'updated_at',
        'user'=>true,
        'fields'=>[
                'id',
                'collect_id',
                'article_id',
                'level',
                'title',
                'children',
                'editor_id',
                'created_at',
                'updated_at',
                'deleted_at',
        ],
    ],

    'articles' =>[
        'key'=>'uid',
        'time1'=>'modify_time',
        'time2'=>'updated_at',
        'user'=>true,
        'fields'=>[
            'id',
            'uid' ,
            'parent_id' ,
            'default_channel_id' ,
            'title' ,
            'subtitle' ,
            'summary' ,
            'cover' ,
            'content' ,
            'content_type' ,
            'owner' ,
            'owner_id',
            'editor_id',
            'setting' ,
            'status' ,
            'lang' ,
            'create_time',
            'modify_time',
            'created_at' ,
            'updated_at' ,
            'deleted_at' ,
        ],
    ],

    'attachments' =>[
        'key'=>'id',
        'time1'=>'',
        'time2'=>'updated_at',
        'user'=>true,
        'fields'=>[
            'id' ,
            'user_uid' ,
            'bucket' ,
            'name' ,
            'title' ,
            'size',
            'content_type' ,
            'status' ,
            'version' ,
            'deleted_at' ,
            'created_at' ,
            'updated_at' ,
        ]
    ],

    'bolds' =>[
        'key'=>'id',
        'time1'=>'',
        'time2'=>'created_at',
        'user'=>false,
        'fields'=>[
            'id',
            'book' ,
            'paragraph' ,
            'word'  ,
            'word2'  ,
            'word_en'  ,
            'pali'  ,
            'base'  ,
            'created_at' ,
        ],
    ],

    'book_titles' =>[
        'key'=>'id',
        'time1'=>'',
        'time2'=>'updated_at',
        'user'=>false,
        'fields'=>[
            'id',
            'book' ,
            'paragraph' ,
            'title'  ,
            'created_at' ,
            'updated_at' ,
            'sn' 
        ],
    ],

    'book_words' =>[
        'key'=>'id',
        'time1'=>'',
        'time2'=>'updated_at',
        'user'=>false,
        'fields'=>[
            'id',
            'book' ,
            'wordindex' ,
            'count' ,
            'created_at' ,
            'updated_at' 
        ]
    ],

    'channels' =>[
        'key'=>'uid',
        'time1'=>'modify_time',
        'time2'=>'updated_at',
        'user'=>true,
        'fields'=>[
            'id',
            'type'  ,
            'owner_uid' ,
            'editor_id',
            'name' ,
            'summary' ,
            'lang' ,
            'status' ,
            'setting' ,
            'create_time',
            'modify_time',
            'created_at' ,
            'updated_at' ,
            'deleted_at' ,
            'uid'  ,
            'config' ,
        ]
    ],

    'collections' =>[
        'key'=>'uid',
        'time1'=>'modify_time',
        'time2'=>'updated_at',
        'user'=>true,
        'fields'=>[
            'id',
            'uid' ,
            'parent_id' ,
            'default_channel_id' ,
            'title' ,
            'subtitle' ,
            'summary' ,
            'cover' ,
            'article_list' ,
            'owner' ,
            'owner_id',
            'editor_id',
            'setting' ,
            'status' ,
            'lang' ,
            'create_time',
            'modify_time',
            'created_at' ,
            'updated_at' ,
            'deleted_at' 
        ]
    ],

    'course_members' =>[
        'key'=>'id',
        'time1'=>'',
        'time2'=>'updated_at',
        'user'=>true,
        'fields'=>[
            'id'  ,
            'user_id' ,
            'role'  ,
            'course_id' ,
            'channel_id' ,
            'status'   ,
            'created_at' ,
            'updated_at'         
        ]
    ],

    'courses' =>[
        'key'=>'id',
        'time1'=>'',
        'time2'=>'updated_at',
        'user'=>true,
        'fields'=>[
            'id'  ,
            'title'  ,
            'subtitle' ,
            'cover' ,
            'content' ,
            'content_type' ,
            'teacher' ,
            'start_at' ,
            'end_at' ,
            'studio_id' ,
            'created_at' ,
            'updated_at' ,
            'anthology_id' ,
            'publicity' ,
            'channel_id' ,
            "join"  ,
            'request_exp' ,
            'summary' ,
        ]
    ],


    'custom_book_ids' =>[
        'key'=>'key',
        'time1'=>'',
        'time2'=>'updated_at',
        'user'=>true,
        'fields'=>[
            'id',
            'key'   ,
            'value' ,
            'created_at' ,
            'updated_at'         
        ]
    ],


    'custom_book_sentences' =>[
        'key'=>'id',
        'time1'=>'modify_time',
        'time2'=>'updated_at',
        'user'=>true,
        'fields'=>[
            'id',
            'book' ,
            'paragraph' ,
            'word_start' ,
            'word_end' ,
            'content'  ,
            'content_type' ,
            'length' ,
            'owner' ,
            'lang' ,
            'status' ,
            'create_time',
            'modify_time',
            'created_at' ,
            'updated_at' ,        
        ]
    ],

    'custom_books' =>[
        'key'=>'id',
        'time1'=>'',
        'time2'=>'updated_at',
        'user'=>true,
        'fields'=>[
            'id',
            'book_id' ,
            'title' ,
            'owner' ,
            'editor_id',
            'lang' ,
            'status' ,
            'created_at' ,
            'updated_at'         
        ]
    ],

    'dhamma_terms' =>[
        'key'=>'guid',
        'time1'=>'modify_time',
        'time2'=>'updated_at',
        'user'=>true,
        'fields'=>[
            'id',
            'guid' ,
            'word'  ,
            'word_en'  ,
            'meaning'  ,
            'other_meaning' ,
            'note' ,
            'tag' ,
            'channal' ,
            'language'  ,
            'owner' ,
            'editor_id',
            'create_time',
            'modify_time',
            'created_at' ,
            'updated_at' ,
            'deleted_at'         
        ]
    ],

    'dict_infos' =>[
        'key'=>'id',
        'time1'=>'',
        'time2'=>'updated_at',
        'user'=>false,
        'fields'=>[
            'id'  ,
            'name'  ,
            'shortname' ,
            'description' ,
            'src_lang'  ,
            'dest_lang'  ,
            'rows' ,
            'owner_id' ,
            'meta'  ,
            'created_at' ,
            'updated_at'         
        ]
    ],

    'discussions' =>[
        'key'=>'id',
        'time1'=>'',
        'time2'=>'updated_at',
        'user'=>true,
        'fields'=>[
            'id'  ,
            'res_id' ,
            'res_type'  ,
            'parent' ,
            'title' ,
            'content' ,
            'children_count' ,
            'editor_uid' ,
            'publicity'   ,
            'created_at' ,
            'updated_at' ,
            'content_type' ,
            'status'  ,
            'tpl_id' ,        
        ]
    ],

    'file_indices' =>[
        'key'=>'uid',
        'time1'=>'modify_time',
        'time2'=>'updated_at',
        'user'=>true,
        'fields'=>[
            'id',
            'uid' ,
            'parent_id' ,
            'user_id',
            'book' ,
            'paragraph' ,
            'channal' ,
            'file_name' ,
            'title' ,
            'tag' ,
            'status' ,
            'file_size' ,
            'share' ,
            'doc_info'  ,
            'doc_block'  ,
            'create_time',
            'modify_time',
            'accese_time',
            'created_at' ,
            'accesed_at' ,
            'updated_at' ,
            'deleted_at'         
        ]
    ],

    'fts_texts' =>[
        'key'=>'id',
        'time1'=>'',
        'time2'=>'updated_at',
        'user'=>false,
        'fields'=>[
            'id',
            'book' ,
            'paragraph' ,
            'wid'  ,
            'bold_single'  ,
            'bold_double'  ,
            'bold_multiple'  ,
            'content'  ,
            'created_at' ,
            'updated_at' ,
            'pcd_book_id' ,        
        ]
    ],

    'group_infos' =>[
        'key'=>'uid',
        'time1'=>'modify_time',
        'time2'=>'updated_at',
        'user'=>true,
        'fields'=>[
            'id',
            'uid' ,
            'name'  ,
            'description' ,
            'status'  ,
            'owner' ,
            'create_time',
            'modify_time',
            'created_at' ,
            'updated_at'         
        ] 
    ],

    'group_members' =>[
        'key'=>['user_id','group_id'],
        'time1'=>'',
        'time2'=>'updated_at',
        'user'=>true,
        'fields'=>[
            'id',
            'user_id' ,
            'group_id' ,
            'group_name' ,
            'power'  ,
            'level' ,
            'status'  ,
            'created_at' ,
            'updated_at'         
        ]
    ],

    'invites' =>[
        'key'=>'id',
        'time1'=>'',
        'time2'=>'updated_at',
        'user'=>true,
        'fields'=>[
            'id' ,
            'user_uid' ,
            'email'  ,
            'status' ,
            'created_at' ,
            'updated_at'         
        ]
    ],

    'likes' =>[
        'key'=>'id',
        'time1'=>'',
        'time2'=>'updated_at',
        'user'=>true,
        'fields'=>[
            'id'  ,
            'type'  ,
            'target_id' ,
            'target_type'  ,
            'context' ,
            'user_id' ,
            'created_at' ,
            'updated_at'         
        ]   
    ],

    'nissaya_endings' =>[
        'key'=>'id',
        'time1'=>'',
        'time2'=>'updated_at',
        'user'=>true,
        'fields'=>[
            'id'  ,
            'ending'  ,
            'lang' ,
            'relation' ,
            "case" ,
            'strlen' ,
            'count' ,
            'editor_id' ,
            'created_at' ,
            'updated_at' ,
            "from"         
        ]
    ],


    'page_numbers' =>[
        'key'=>'id',
        'time1'=>'',
        'time2'=>'updated_at',
        'user'=>false,
        'fields'=>[
            'id',
            'type' ,
            'volume' ,
            'page' ,
            'book' ,
            'paragraph' ,
            'wid' ,
            'pcd_book_id' ,
            'created_at' ,
            'updated_at'         
        ]
    ],

    'pali_sent_indices' =>[
        'key'=>'id',
        'time1'=>'',
        'time2'=>'updated_at',
        'user'=>false,
        'fields'=>[ 
            'id',
            'book' ,
            'para' ,
            'strlen' ,
            'created_at' ,
            'updated_at'         
        ]
    ],

    'pali_sent_orgs' =>[
        'key'=>'id',
        'time1'=>'',
        'time2'=>'updated_at',
        'user'=>false,
        'fields'=>[
            'id' ,
            'book' ,
            'paragraph' ,
            'word_begin' ,
            'word_end' ,
            'length' ,
            'count' ,
            'text'  ,
            'html'  ,
            'merge'  ,
            'sim_sents' ,
            'sim_sents_count'  ,
            'created_at' ,
            'updated_at' 
        ]
    ],


    'pali_sentences' =>[
        'key'=>'id',
        'time1'=>'',
        'time2'=>'updated_at',
        'user'=>false,
        'fields'=>[
            'id' ,
            'book' ,
            'paragraph' ,
            'word_begin' ,
            'word_end' ,
            'length' ,
            'count' ,
            'text'  ,
            'html'  ,
            'sim_sents' ,
            'sim_sents_count'  ,
            'created_at' ,
            'updated_at' 
        ]
    ],

    'pali_texts' =>[
        'key'=>'uid',
        'time1'=>'',
        'time2'=>'updated_at',
        'user'=>false,
        'fields'=>[
            'id',
            'book' ,
            'paragraph' ,
            'level' ,
            'class' ,
            'toc'  ,
            'text'  ,
            'html'  ,
            'lenght' ,
            'album_index' ,
            'chapter_len' ,
            'next_chapter' ,
            'prev_chapter' ,
            'parent' ,
            'chapter_strlen' ,
            'created_at' ,
            'updated_at' ,
            'path' ,
            'uid'  ,
            'title_en' ,
            'title' ,
            'pcd_book_id'         
        ]
    ],

    'progress' =>[
        'key'=>'id',
        'time1'=>'',
        'time2'=>'updated_at',
        'user'=>false,
        'fields'=>[
            'id',
            'book' ,
            'para' ,
            'lang' ,
            'all_strlen' ,
            'public_strlen' ,
            'created_at' ,
            'updated_at' ,
            'channel_id'         
        ]
    ],

    'progress_chapters' =>[
        'key'=>'id',
        'time1'=>'',
        'time2'=>'updated_at',
        'user'=>false,
        'fields'=>[
            'id',
            'book' ,
            'para' ,
            'lang' ,
            'all_trans'  ,
            'public'  ,
            'created_at' ,
            'updated_at' ,
            'channel_id' ,
            'progress'  ,
            'title' ,
            'uid'  ,
            'summary'         
        ]
    ],



    'recents' =>[
        'key'=>'id',
        'time1'=>'',
        'time2'=>'updated_at',
        'user'=>true,
        'fields'=>[
            'id' ,
            'type' ,
            'article_id' ,
            'param' ,
            'user_uid' ,
            'created_at' ,
            'updated_at'
        ]
    ],


    'related_paragraphs' =>[
        'key'=>'id',
        'time1'=>'',
        'time2'=>'updated_at',
        'user'=>false,
        'fields'=>[
            'id',
            'book' ,
            'para' ,
            'book_id' ,
            'cs_para' ,
            'book_name'  ,
            'created_at' ,
            'updated_at'         
        ]
    ],



    'relations'=>[
        'key'=>'id',
        'time1'=>'',
        'time2'=>'updated_at',
        'user'=>true,
        'fields'=>[
            'id'  ,
            'name'  ,
            "case" ,
            "to" ,
            'editor_id' ,
            'created_at' ,
            'updated_at' ,
            "from" ,
            'category' ,
            'match'         
        ]
    ],

    'res_indices' =>[
        'key'=>'id',
        'time1'=>'update_time',
        'time2'=>'updated_at',
        'user'=>false,
        'fields'=>[
            'id',
            'book' ,
            'paragraph' ,
            'title'  ,
            'title_en' ,
            'level' ,
            'type' ,
            'language' ,
            'author' ,
            'editor' ,
            'share'  ,
            'edition'  ,
            'hit' ,
            'album' ,
            'tag' ,
            'summary' ,
            'create_time',
            'update_time',
            'created_at' ,
            'updated_at'         
        ]
    ],


    'sent_blocks' =>[
        'key'=>'id',
        'time1'=>'modify_time',
        'time2'=>'updated_at',
        'user'=>true,
        'fields'=>[
            'id',
            'uid' ,
            'parent_uid' ,
            'book_id' ,
            'paragraph' ,
            'owner_uid' ,
            'editor_uid' ,
            'lang' ,
            'author'  ,
            'status' ,
            'create_time',
            'modify_time',
            'created_at' ,
            'updated_at' ,
            'deleted_at'         
        ]
    ],


    'sent_histories' =>[
        'key'=>'id',
        'time1'=>'create_time',
        'time2'=>'created_at',
        'user'=>true,
        'fields'=>[
            'id',
            'sent_uid' ,
            'user_uid' ,
            'content'  ,
            'landmark' ,
            'create_time',
            'created_at'         
        ]
    ],

    'sent_prs' => [
        'key'=>'id',
        'time1'=>'modify_time',
        'time2'=>'updated_at',
        'user'=>true,
        'fields'=>[
            'id',
            'old_id' ,
            'book_id' ,
            'paragraph' ,
            'word_start' ,
            'word_end' ,
            'channel_uid' ,
            'author' ,
            'editor_uid' ,
            'content' ,
            'language' ,
            'status' ,
            'strlen' ,
            'create_time',
            'modify_time',
            'created_at' ,
            'updated_at' ,
            'deleted_at'
        ]
    ],


    'sent_sim_indices' =>[
        'key'=>'id',
        'time1'=>'',
        'time2'=>'updated_at',
        'user'=>false,
        'fields'=>[
            'id',
            'sent_id' ,
            'count' ,
            'created_at' ,
            'updated_at'         
        ]
    ],

    'sent_sims' =>[
        'key'=>'id',
        'time1'=>'',
        'time2'=>'updated_at',
        'user'=>false,
        'fields'=>[
            'id',
            'sent1' ,
            'sent2' ,
            'sim'  ,
            'created_at' ,
            'updated_at'         
        ]
    ],

    'sentences' =>[
        'key'=>'id',
        'time1'=>'modify_time',
        'time2'=>'updated_at',
        'user'=>true,
        'fields'=>[
            'id',
            'uid' ,
            'parent_uid' ,
            'block_uid' ,
            'channel_uid' ,
            'book_id' ,
            'paragraph' ,
            'word_start' ,
            'word_end' ,
            'author' ,
            'editor_uid' ,
            'content' ,
            'content_type' ,
            'language' ,
            'version'  ,
            'strlen' ,
            'status' ,
            'create_time',
            'modify_time',
            'created_at' ,
            'updated_at' ,
            'deleted_at' ,
            'pr_edit_at' ,
            'acceptor_uid' ,
            'pr_id' 
        ]
    ],


    'shares' =>[
        'key'=>'id',
        'time1'=>'modify_time',
        'time2'=>'updated_at',
        'user'=>true,
        'fields'=>[
            'id',
            'res_id' ,
            'res_type' ,
            'cooperator_id' ,
            'cooperator_type' ,
            'power' ,
            'create_time',
            'modify_time',
            'accepted_at' ,
            'acceptor' ,
            'created_at' ,
            'updated_at'         
        ]
    ],


    'tag_maps' =>[
        'key'=>'id',
        'time1'=>'',
        'time2'=>'updated_at',
        'user'=>false,
        'fields'=>[
            'id' ,
            'table_name' ,
            'anchor_id' ,
            'tag_id' ,
            'created_at' ,
            'updated_at'         
        ]
    ],


    'tags'=>[
        'key'=>'id',
        'time1'=>'',
        'time2'=>'updated_at',
        'user'=>false,
        'fields'=>[
            'id'  ,
            'name'  ,
            'description' ,
            'color'  ,
            'owner_id' ,
            'created_at' ,
            'updated_at'         
        ]
    ],


    'transfers'=>[
        'key'=>'id',
        'time1'=>'',
        'time2'=>'updated_at',
        'user'=>true,
        'fields'=>[
            'id'  ,
            'origin_owner' ,
            'res_type'  ,
            'res_id' ,
            'transferor_id' ,
            'new_owner' ,
            'editor_id' ,
            'status'  ,
            'created_at' ,
            'updated_at'
        ]
    ],


    'user_dicts' =>[
        'key'=>'id',
        'time1'=>'create_time',
        'time2'=>'created_at',
        'user'=>true,
        'fields'=>[
            'id',
            'word'  ,
            'type' ,
            'grammar' ,
            'parent' ,
            'mean' ,
            'note' ,
            'factors' ,
            'factormean' ,
            'status' ,
            'source' ,
            'language'   ,
            'confidence'  ,
            'exp' ,
            'creator_id',
            'ref_counter'   ,
            'create_time',
            'created_at' ,
            'updated_at' ,
            'deleted_at' ,
            'dict_id' ,
            'flag' 
        ]
    ],

    'user_experiences' =>[
        'key'=>'id',
        'time1'=>'',
        'time2'=>'updated_at',
        'user'=>true,
        'fields'=>[
            'id'  ,
            'user_id' ,
            'date' ,
            'user_exp' ,
            'user_level'  ,
            'edit_exp' ,
            'wbw_count' ,
            'wbw_edit' ,
            'trans_character' ,
            'created_at' ,
            'updated_at'         
        ]
    ],

    'user_infos' =>[
        'key'=>'id',
        'time1'=>'modify_time',
        'time2'=>'updated_at',
        'user'=>true,
        'fields'=>[
            'id',
            'userid' ,
            'path' ,
            'username'  ,
            'password'  ,
            'nickname'  ,
            'email'  ,
            'create_time' ,
            'modify_time' ,
            'receive_time' ,
            'setting' ,
            'reset_password_token' ,
            'reset_password_sent_at' ,
            'confirmation_token' ,
            'confirmed_at' ,
            'confirmation_sent_at' ,
            'unconfirmed_email' ,
            'created_at' ,
            'updated_at'         
        ]
    ],



    'user_operation_dailies' => [
        'key'=>'id',
        'time1'=>'',
        'time2'=>'updated_at',
        'user'=>true,
        'fields'=>[
            'id',
            'user_id',
            'date_int',
            'duration',
            'hit'  ,
            'created_at' ,
            'updated_at'
        ]
    ],


    'user_operation_frames' =>[
        'key'=>'id',
        'time1'=>'',
        'time2'=>'updated_at',
        'user'=>true,
        'fields'=>[
            'id',
            'user_id',
            'duration',
            'hit'  ,
            'timezone'   ,
            'op_start',
            'op_end',
            'created_at' ,
            'updated_at'         
        ]
    ],

    'user_operation_logs' =>[
        'key'=>'id',
        'time1'=>'create_time',
        'time2'=>'created_at',
        'user'=>true,
        'fields'=>[
            'id',
            'user_id',
            'op_type_id',
            'op_type'  ,
            'content' ,
            'timezone' ,
            'create_time',
            'created_at' ,        
        ]
    ],

    'views'=>[
        'key'=>'id',
        'time1'=>'',
        'time2'=>'updated_at',
        'user'=>false,
        'fields'=>[
            'id'  ,
            'target_id' ,
            'target_type'  ,
            'user_id' ,
            'user_ip' ,
            'created_at' ,
            'updated_at' ,
            'title' ,
            'org_title' ,
            'count' ,
            'meta'         
        ]
    ],


    'vocabularies'=>[
        'key'=>'id',
        'time1'=>'',
        'time2'=>'updated_at',
        'user'=>false,
        'fields'=>[
            'id',
            'word'  ,
            'word_en'  ,
            'count' ,
            'flag' ,
            'created_at' ,
            'updated_at' ,
            'strlen'         
        ]
    ],

    'wbw_analyses' =>[
        'key'=>'id',
        'time1'=>'',
        'time2'=>'updated_at',
        'user'=>false,
        'fields'=>[
            'id',
            'wbw_id',
            'wbw_word'  ,
            'book_id' ,
            'paragraph' ,
            'wid' ,
            'type' ,
            'data'  ,
            'confidence' ,
            'lang' ,
            'd1' ,
            'd2' ,
            'd3' ,
            'editor_id',
            'created_at' ,
            'updated_at'         
        ]
    ],

    'wbw_blocks' =>[
        'key'=>'uid',
        'time1'=>'modify_time',
        'time2'=>'updated_at',
        'user'=>true,
        'fields'=>[
            'id',
            'uid' ,
            'parent_id' ,
            'block_uid' ,
            'block_id' ,
            'channel_id' ,
            'channel_uid' ,
            'parent_channel_uid' ,
            'creator_uid' ,
            'editor_id',
            'book_id' ,
            'paragraph' ,
            'style' ,
            'lang' ,
            'status' ,
            'create_time',
            'modify_time',
            'created_at' ,
            'updated_at' ,
            'deleted_at' 
        ]
    ],

    'wbw_templates' =>[
        'key'=>'id',
        'time1'=>'',
        'time2'=>'updated_at',
        'user'=>false,
        'fields'=>[
            'id',
            'book' ,
            'paragraph' ,
            'wid' ,
            'word'  ,
            "real"  ,
            'type'  ,
            'gramma'  ,
            'part'  ,
            'style'  ,
            'created_at' ,
            'updated_at' ,
            'pcd_book_id'         
        ]
    ],

    'wbws' =>[
        'key'=>'uid',
        'time1'=>'modify_time',
        'time2'=>'updated_at',
        'user'=>true,
        'fields'=>[
            'id',
            'uid' ,
            'block_uid' ,
            'block_id' ,
            'channel_id' ,
            'book_id' ,
            'paragraph' ,
            'wid',
            'word'  ,
            'data' ,
            'status' ,
            'creator_uid' ,
            'editor_id',
            'create_time',
            'modify_time',
            'created_at' ,
            'updated_at' ,
            'deleted_at' 
        ]
    ],


    'web_hooks' =>[
        'key'=>'id',
        'time1'=>'',
        'time2'=>'updated_at',
        'user'=>true,
        'fields'=>[
            'id' ,
            'res_type'  ,
            'res_id' ,
            'url' ,
            'receiver'  ,
            'event' ,
            'fail' ,
            'success' ,
            'status' ,
            'editor_uid' ,
            'created_at' ,
            'updated_at' 
        ]
    ],

    'word_indices'=>[
        'key'=>'id',
        'time1'=>'',
        'time2'=>'updated_at',
        'user'=>false,
        'fields'=>[
            'id' ,
            'word'  ,
            'word_en'  ,
            'count' ,
            'normal' ,
            'bold' ,
            'is_base' ,
            'len' ,
            'final' ,
            'created_at' ,
            'updated_at'         
        ]
    ],

    'word_lists' =>[
        'key'=>'id',
        'time1'=>'',
        'time2'=>'updated_at',
        'user'=>false,
        'fields'=>[
            'id',
            'sn' ,
            'book' ,
            'paragraph' ,
            'wordindex' ,
            'bold' ,
            'weight'  ,
            'created_at' ,
            'updated_at'         
        ]
    ],

    'word_parts' =>[
        'key'=>'id',
        'time1'=>'',
        'time2'=>'updated_at',
        'user'=>false,
        'fields'=>[
            'id',
            'word'  ,
            'weight',
            'created_at' ,
            'updated_at'         
        ]
    ],

    'word_statistics' =>[
        'key'=>'id',
        'time1'=>'',
        'time2'=>'updated_at',
        'user'=>false,
        'fields'=>[
            'id',
            'bookid' ,
            'word'  ,
            'count' ,
            'base'  ,
            'end1'  ,
            'end2'  ,
            'type' ,
            'length' ,
            'created_at' ,
            'updated_at'         
        ]
    ],
];
