// config/database.php

// database connection file

<?php
class Database
{
    // specify your own database credentials
    private $host = '127.0.0.1';
    private $db_name = 'eloquent';
    private $username = 'root';
    private $password = 'password';
	
    public $conn;
	
	// public function __construct() {
    // }
	
    public function EloquentConnection()
    {
        return [
            'driver' => 'mysql',
            'host' => $this->host,
            'database' => $this->db_name,
            'username' => $this->username,
            'password' => $this->password,
            'charset' => 'utf8',
            'collation' => 'utf8_unicode_ci',
            'prefix' => '',
        ];
    }
}