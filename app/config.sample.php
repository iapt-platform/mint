<?php 
// Require Composer's autoloader.
require_once '../../vendor/autoload.php';

// Require Composer's autoloader.
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\SMTP;
use PHPMailer\PHPMailer\Exception;

define("Email", ["SMTPDebug"=>SMTP::DEBUG_SERVER,//Enable verbose debug output
				 "Host"=>"smtp.gmail.com",//Set the SMTP server to send through
				 "SMTPAuth"=>true,//Enable SMTP authentication
				 "Username"=>'your@gmail.com',//SMTP username
				 "Password"=>'your_password',//SMTP password
				 "SMTPSecure"=>PHPMailer::ENCRYPTION_SMTPS,//Enable implicit TLS encryption
				 "Port"=>465,//TCP port to connect to 465; use 587 if you have set `SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS`
				 "From"=>"your@gmail.com",
				 "Sender"=>"sender"
				 ]);

?>