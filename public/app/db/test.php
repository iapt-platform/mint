<?php
require_once '../../vendor/autoload.php';
require "./database.php";
use Illuminate\Database\Capsule\Manager as Capsule;

$dbclass = new Database();
$capsule = new Capsule();
$capsule->addConnection($dbclass->EloquentConnection());

// Make this Capsule instance available globally via static methods
$capsule->setAsGlobal();
// Setup the Eloquent ORM
$capsule->bootEloquent();


$users = Capsule::table('wbw_blocks')->where('id', '=', 129195)->get();

var_dump($users);