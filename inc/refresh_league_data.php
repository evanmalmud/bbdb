<?php
include_once("header1.php");
	// This is called by competition table
	// return a message saying it's done, or otherwise.
	// include the requisite data file
	if (permission_check(6)) {
		include_once("stat_update.php");
		$competition_id = $_POST['competition_id'];
		if(ctype_digit($competition_id)) {
			$competition_id = (int) $competition_id;
			update_league_table($conn, 0, $_SESSION['domain_id'], $competition_id);
			//update_star_player_record($conn, $upload_id);
			update_player_tables($_SESSION['domain_id'], $competition_id, $conn);
			update_team_tables($_SESSION['domain_id'], $competition_id, $conn);
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