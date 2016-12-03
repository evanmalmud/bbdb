<?php include_once("inc/header1_with_login.php");

// if you need to return header() statements, do them here. SQL connection has already been established.

include_once("inc/header2.php"); ?>

BBDB</title>

<?php // include anything else you want to put in <head> here.

include_once("inc/header3.php"); 


if ($LoginCode == 1) {
		echo "<h1>Access denied!</h1><p>$error_message</p>";
}
if ($LoginCode == 2)

	{ // login is OK!
		$login = TRUE;

		echo "<h1>Login successful</h1><p>All looks good here - login is good to go!</p>";

//		if ((mysql_numrows(mysql_query("SELECT * FROM feedback WHERE UserEntryID = $UserEntryID"))==0) && (time() < 1279756799)) {
//			echo "<p style=\"font-weight:bold\">I would be very grateful if you could fill in the <a href=\"feedback.php\">feedback form</a> - Thanks!</p>";
//		}

		$sql = $conn->prepare("SELECT DISTINCT rp.permission_id, p.description AS permission_desc
		FROM bb_user_role ru
		INNER JOIN bb_role_permission rp ON ru.role_id = rp.role_id
		INNER JOIN bb_permission p ON rp.permission_id = p.permission_id
		WHERE ru.user_id = ?
		ORDER BY p.description");
		$sql->execute(array($user_id));

		$permission_data = $sql->fetchAll(PDO::FETCH_ASSOC);
		$_SESSION['permission'] = array();

		echo '<p>You have the following permissions...</p>'.PHP_EOL.'<ul>'.PHP_EOL;
		foreach($permission_data AS $row) {
			$_SESSION['permission'][] = (int) $row['permission_id'];
			echo '<li>' . $row['permission_desc'] . '</li>'.PHP_EOL;
		}
		
		echo '</ul><p>Woo.</p>';
	}

if ($LoginCode == 0)
{
?>

	<h1>Login page</h1>
	<form method="post" action = "login.php">
	<table>
	<tr><td>User Name</td>
	<td><input name="uname" type = "text" size = "50" maxlength="64"/></td></tr>
	<tr><td>Password</td>
	<td><input name="pwd" type = "password" size="50" maxlength="70"/></td></tr>
	<tr><td></td><td><input type="submit" name ="submit" value="Log me in"/></td></tr>
	</table>
	</form>
	

	<p>Argh, actually I want you to <a href="logout.php">log me out!</a></p>

	<p>If you have forgotten your password, then <a href="contact.php">contact me</a> so I can reset it for you.</p>


<?php
}
include_once("inc/footer.php"); ?>