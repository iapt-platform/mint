<?php

return [

    'snowflake'=>[
        'data_center_id' => env('SNOWFLAKE_DATA_CENTER_ID', 1),
        'worker_id' => env('SNOWFLAKE_WORKER_ID', 1),
        /*
        |--------------------------------------------------------------------------
        | snowflake id start date don't modify
        |--------------------------------------------------------------------------
        */
        'start' => "2021-12-22",
    ],

    'server' => [
        'rpc' => [

            'morus' => env('MORUS_RPC_SERVER', "localhost:9999"),

            'lily' => env('LILY_RPC_SERVER', "192.168.43.100:9000"),
        ],

        'assets' => env('ASSETS_SERVER', "localhost:9999"),

        'dashboard_base_path' => env('DASHBOARD_BASE_PATH', "http://127.0.0.1:3000/my"),
    ],

    'cache' => [
        //这个值prod,staging无需设置
        'expire' => env('CACHE_EXPIRE', 3600),
    ],

    /*
    |--------------------------------------------------------------------------
    | 另外增添的路径
    |--------------------------------------------------------------------------
    |
    |
    */
	'path' => [
		'dependence' => storage_path('depandence'),
		'palitext' => public_path('palihtml'),
		'palitext_filelist' => public_path('palihtml').'/filelist.csv',
		'palicsv' => public_path('tmp').'/palicsv',
		'pali_title' => public_path('pali_title'),
		'paliword' => public_path('paliword'),
		'paliword_book' => public_path('paliword')."/book",
		'paliword_index' => public_path('paliword')."/index",
		'word_statistics' => public_path('dependence')."/word_statistics/data",
		'dict_text' => public_path('dicttext'),
	],

	'admin' => [
		'root_uuid' => '6e12f8ea-ee4d-4e0f-a6b0-472f2d99a814',
		'robot_uuid' => '6e12f8ea-ee4d-4e0f-a6b0-472f2d99a814',
		'cs_channel' => '1e4b926d-54d7-4932-b8a6-7cdc65abd992',
	],

	'dependence' => [
		[
			'url' => 'https://www.github.com/iapt-platform/wipali-globle',
			'path' => 'wipali-globle',
		],
	],

	'email' => [
		'ScheduleEmailOutputTo' => env('SCHEDULE_EMAIL_OUTPUTTO', 'kosalla1987@126.com'),
		'ScheduleEmailOutputOnFailure' => env('SCHEDULE_EMAIL_OUTPUTONFAILURE', 'kosalla1987@126.com'),
	]
];