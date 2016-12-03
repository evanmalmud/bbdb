<?php include_once("inc/header1_with_logout.php");

// if you need to return header() statements, do them here. SQL connection has already been established.

include_once("inc/header2.php"); ?>

BBDB</title>

<?php // include anything else you want to put in <head> here.

include_once("inc/header3.php"); 



if ($LogoutCode == 0)
{
?>	<h1>You're an idiot</h1>
	<p>You can't logout because you aren't logged in! Crazy fool.</p>

<?php
}
else if ($LogoutCode == 1)
{

	echo "<h1>Logout page</h1>
	<p>OK $username, you are logged out now.</p>";
	
}
else {echo "Well this is weird. You shouldn't be here. Please let the admins know - thanks."; }


include_once("inc/footer.php"); ?>