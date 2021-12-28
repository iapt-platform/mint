<?php
require "../vendor/autoload.php";
require "../config/database.php";
use Illuminate\Database\Capsule\Manager as Capsule;

$dbclass = new Database();
$capsule = new Capsule();
$capsule->addConnection($dbclass->EloquentConnection());

// Make this Capsule instance available globally via static methods
$capsule->setAsGlobal();
// Setup the Eloquent ORM
$capsule->bootEloquent();


$users = Capsule::table('user')->where('id', '=', 1)->get();