<?php
require __DIR__."/../../vendor/autoload.php";
require_once __DIR__."/../config.php";

use Illuminate\Database\Capsule\Manager as Capsule;

$capsule = new Capsule;
$capsule->addConnection([
	'driver' => Database["type"],
	'host' => Database["server"],
	'port' => Database["port"],
	'database' => Database["name"],
	'username' =>  Database["user"],
	'password' => Database["password"],
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