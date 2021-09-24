<?php 
// Require Composer's autoloader.
require_once '../../vendor/autoload.php';


/*
电子邮件设置
PHPMailer
*/
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\SMTP;
use PHPMailer\PHPMailer\Exception;
define("Email", ["SMTPDebug"=>SMTP::DEBUG_SERVER,//Enable verbose debug output DEBUG_OFF
				 "Host"=>"smtp.gmail.com",//Set the SMTP server to send through
				 "SMTPAuth"=>true,//Enable SMTP authentication
				 "Username"=>'your@gmail.com',//SMTP username
				 "Password"=>'your_password',//SMTP password
				 "SMTPSecure"=>PHPMailer::ENCRYPTION_SMTPS,//Enable implicit TLS encryption
				 "Port"=>465,//TCP port to connect to 465; use 587 if you have set `SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS`
				 "From"=>"your@gmail.com",
				 "Sender"=>"sender"
				 ]);

/*
数据库设置
*/
define("Database",[
	"type"=>"pgsql",
	"port"=>5432,
	"name"=>"mint",
	"sslmode" => "disable",
	"user" => "postgres",
	"password" => ""
]);

/*
Redis 设置，
使用集群
*/
define("Redis",[
	"hosts" => ["127.0.0.1:6376", "127.0.0.1:6377", "127.0.0.1:6378"],
	"password" => "",
	"db" => 0,
	"prefix"=>"aaa://"
]);
				

?>