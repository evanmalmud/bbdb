<?php
// Add a coach. Simples!
include_once("../inc/header1.php");

if (permission_check(10)) {  // create coaches
	$coach_name = $_POST['coach_name'];
	
	$sql = $conn->prepare("INSERT INTO bb_coach (domain_id, description)
							SELECT ?, ?");
	if(!$sql->execute(array($_SESSION['domain_id'], $coach_name))) 
	{
		echo "Failed to create coach. Invalid input.";
	}
	else {
		$rows_added = $sql->rowCount();

		if($rows_added==0) {
			echo "Failed to create coach. Probably because this coach already exists.";
		}

		else {
			echo "Coach successfully created.";
		}
	}
}
	
else {
	echo "You lack the required permissions.";
}
?>