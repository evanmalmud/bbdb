<?php
include_once("header1.php");
	// This is called by competition table
	// return a message saying it's done, or otherwise.
	// include the requisite data file
	if (permission_check(7)) {  // close a competition
		include_once("stat_update.php");
		$competition_id = $_POST['competition_id'];
		if(ctype_digit($competition_id)) {
			$competition_id = (int) $competition_id;
			
			assign_player_awards($conn, $_SESSION['domain_id'], $competition_id);
			
			echo "Complete!";
		}
		else {
			echo "Invalid input.";
		}
	}
	else {
		echo "Permission failure";
	}
?>