<?php
	session_start();
	$server = "mysql:host=localhost;dbname=YOUR_DB_HERE";
	
	function permission_check($permission_id) {
		if(isset($_SESSION['permission'])) {
			if (in_array($permission_id,$_SESSION['permission'])) {
				return TRUE;
			}
		}
		return FALSE;
	} 

$conn = new PDO($server, 'YOUR_USERNAME_HERE', 'YOUR_PASSWORD_HERE') or die("Connection to database failed. Try refreshing the page.");
$base_url = "YOUR_URL_HERE";

if (!$conn) 
{
	
	die('Failed to connect to the database');

}

	if(isset($_SESSION['ok'])) // easiesr variables for referring to
	{
		$username = $_SESSION['username'];
		$user_id = $_SESSION['user_id'];
	}

header('Expires: Sun, 01 Jan 2000 00:00:00 GMT');
header("Cache-Control: no-store, no-cache, must-revalidate, max-age=0");
header("Cache-Control: post-check=0, pre-check=0", false);
header("Pragma: no-cache");
	
?>
