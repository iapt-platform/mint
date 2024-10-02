// config/database.php

// database connection file

<?php
class Database
{
    // specify your own database credentials
    private $host = 'localhost';
    private $db_name = 'mint';
    private $username = 'postgras';
    private $password = '123456';
	
    public $conn;
	
	// public function __construct() {
    // }
	
    public function EloquentConnection()
    {
        return [
            'driver' => 'pgsql',
            'host' => $this->host,
            'database' => $this->db_name,
            'port' => 5432,
            'username' => $this->username,
            'password' => $this->password,
            'charset' => 'utf8',
			'options' => [
				PDO::ATTR_PERSISTENT => true,
			],
            'prefix' => '',
            'prefix_indexes' => true,
            'schema' => 'public',
            'sslmode' => 'prefer',
        ];
    }
}