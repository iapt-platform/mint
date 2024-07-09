<?php

/*
数据库设置
*/

$config = [
	'db_a' => [
		'driver' => 'pgsql',
		'host' => 'localhost',
		'port' => 5432,
		'database' => 'wikipali',
		'username' => 'postgres',
		'password' => '123456',
	],
	'db_b' => [
		'driver' => 'pgsql',
		'host' => 'localhost',
		'port' => 5432,
		'database' => 'wikipali',
		'username' => 'postgres',
		'password' => '123456',
	],
	'db_c' => [
		'driver' => 'pgsql',
		'host' => 'localhost',
		'port' => 5432,
		'database' => 'wikipali',
		'username' => 'postgres',
		'password' => '123456',
	],
	'db_d' => [
		'driver' => 'pgsql',
		'host' => 'localhost',
		'port' => 5432,
		'database' => 'wikipali',
		'username' => 'postgres',
		'password' => '123456',
	],
];

define("DB_A", 'pgsql:'.
				"host=localhost;".
				"port=5432;".
				"dbname=wikipali;".
				"user=postgres;".
				"password=123456;");

define("DB_B", 'pgsql:'.
				"host=localhost;".
				"port=5432;".
				"dbname=wikipali;".
				"user=postgres;".
				"password=123456;");

define("DB_C", 'pgsql:'.
				"host=localhost;".
				"port=5432;".
				"dbname=wikipali;".
				"user=postgres;".
				"password=123456;");
define("DB_D", 'pgsql:'.
				"host=localhost;".
				"port=5432;".
				"dbname=wikipali;".
				"user=postgres;".
				"password=123456;");