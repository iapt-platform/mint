<?php
require "../../vendor/autoload.php";

use Illuminate\Database\Capsule\Manager as Capsule;

$capsule = new Capsule;
$capsule->addConnection([
	'driver' => 'pgsql',
	'host' => '127.0.0.1',
	'port' => '5432',
	'database' => 'mint',
	'username' =>  'postgres',
	'password' => '123456',
	'charset' => 'utf8',
	'options' => [
		PDO::ATTR_PERSISTENT => true,
	],
	'prefix' => '',
	'prefix_indexes' => true,
	'schema' => 'public',
	'sslmode' => 'prefer',
]);
$capsule->setAsGlobal();
$capsule->bootEloquent();