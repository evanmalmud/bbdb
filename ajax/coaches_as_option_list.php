<?php
// return a list of all coaches in the current domain, in the form of <option> tags
include_once("../inc/header1.php");

if (permission_check(4)) {  // standard read privilages

	// list of all coaches in the current domain
	$sql = $conn->prepare("SELECT coach_id, description
							FROM bb_coach
							WHERE domain_id = ?
							order by description");
	$sql->execute(array($_SESSION['domain_id']));
	$coach_list = $sql->fetchAll(PDO::FETCH_ASSOC);
	foreach($coach_list as $coach) {
		echo "<option value=";
		echo $coach['coach_id'] . '">';
		echo $coach['description'];
		echo "</option>".PHP_EOL;
	}
}
	
else {
	echo "You lack the required permissions.";
}
?>