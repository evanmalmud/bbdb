<?php
	session_start();
	$server = "mysql:host=localhost;dbname=YOUR_DB_HERE";
	

$conn = new PDO($server, 'YOUR_USERNAME_HERE', 'YOUR_PASSWORD_HERE') or die("Connection to database failed. Try refreshing the page.");
$base_url = "YOUR_URL_HERE";

if (!$conn) 
{
	
	die('Failed to connect to the database');

}
// START OF LOGIN CODE 
$LoginCode = 0;
	if ($LogoutAttempt) {
		if (!$_SESSION['ok'])
		{ $LogoutCode = 0;}
		else {
			$LogoutCode = 1;
			$_SESSION = array();    
			session_destroy();
		}
	}

	elseif (isset($_POST['uname'])){ // it's a login attempt
		$input_username = $_POST['uname'];
		$pwd = $_POST['pwd'];


		if ((strlen($input_username) < 3 or strlen($input_username) > 64)) {
			$LoginCode = 1;
			$error_message = 'Username is invalid length.';
		}


		$check = preg_replace('/[A-Za-z0-9_]*/', '', $input_username);

		if ($check!="") {
			$LoginCode = 1;
			$error_message = 'Username is invalid format.';
		}

		$login_sql = $conn->prepare("SELECT u.*, d.description AS domain FROM bb_user u 
											INNER JOIN bb_domain d ON u.default_domain_id = d.domain_id
											WHERE u.username = ?");
		$login_sql->execute(array($input_username));
		$row_count = $login_sql->rowCount();
		
		if ($row_count==0) {
			$LoginCode = 1;
			$error_message = 'Username not found.';
		}
		elseif ($row_count>1) {  // as usernames are unique in the DB, this should never happen
			$LoginCode = 1;
			$error_message = 'Completely unexpected error occurred.';
		}
		else {
			$user = $login_sql->fetch(PDO::FETCH_ASSOC);
			$password_ok = FALSE;
			
			if ($user['hash_scheme']==5) {
				if (password_verify($pwd, $user['pword_hash'])) {
					$password_ok = TRUE;
				}
			}
			// if passwords are stored any other way, then try to verify them here.
		
			if ($password_ok) { // Here is the code for "if login successful"
				$_SESSION['ok'] = TRUE;
				$_SESSION['user_id'] = $user['user_id'];
				$_SESSION['username'] = $input_username;
				$_SESSION['domain_id'] = $user['default_domain_id'];
				$_SESSION['domain'] = $user['domain'];
				
				$session_id = session_id();
				$sql = $conn->prepare("UPDATE bb_user SET last_login = NOW(), last_login_session_id = ?
										WHERE username = ?");
				$sql->execute(array($session_id, $input_username));
				
				$LoginCode = 2;
			}
			else {
				$LoginCode = 1;
				$error_message = 'Password incorrect.';
			}
		
		}
	}
	
// END OF LOGIN CODE.
	if($_SESSION['ok']) 
	{
		$username = $_SESSION['username'];
		$user_id = $_SESSION['user_id'];
	}
?>
