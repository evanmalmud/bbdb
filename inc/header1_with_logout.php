<?php
	session_start();
	$server = "mysql:host=localhost;dbname=YOUR_DB_HERE";
	

$conn = new PDO($server, 'YOUR_USERNAME_HERE', 'YOUR_PASSWORD_HERE') or die("Connection to database failed. Try refreshing the page.");
$base_url = "YOUR_URL_HERE";

if (!$conn) 
{
	
	die('Failed to connect to the database');

}
// START OF LOGOUT CODE 


	if (!$_SESSION['ok']) {
		$LogoutCode = 0;
	}
	else {
		$LogoutCode = 1;
		$username = $_SESSION['username']; // record it before it gets deleted
		$_SESSION = array();    
		session_destroy();
	}

// END OF LOGOUT CODE.
	if($_SESSION['ok']) 
	{
		$username = $_SESSION['username'];
		$user_id = $_SESSION['user_id'];
	}
?>
