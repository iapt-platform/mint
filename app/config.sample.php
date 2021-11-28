<?php 

#域名设置
define(WWW_DOMAIN_NAME,"sg.wikipali.org");
define(RPC_DOMAIN_NAME,"rpc.wikipali.org");
/*
电子邮件设置
PHPMailer
*/
define("Email", [
				 "Host"=>"smtp.gmail.com",//Set the SMTP server to send through
				 "SMTPAuth"=>true,//Enable SMTP authentication
				 "Username"=>'your@gmail.com',//SMTP username
				 "Password"=>'your_password',//SMTP password
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
	"password" => "123456"
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