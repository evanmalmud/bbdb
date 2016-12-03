<?php
include_once("../inc/header1.php");

if (permission_check(9)) {  // assign_coach_to_competition
	$coach_id = $_POST['coach_id'];
	$team_id = $_POST['team_id'];

	// do the addition, whilst checking that the coach is from the correct domain
	$sql = $conn->prepare("UPDATE bb_team t
							INNER JOIN bb_coach c ON c.domain_id = ? AND c.coach_id = ?
							SET t.coach_id = ?
							WHERE t.coach_id IS NULL
							AND t.team_id = ?"); // this script is only for assigning to 
	if (!$sql->execute(array($_SESSION['domain_id'], $coach_id, $coach_id, $team_id))) {
		echo "Failed to assign coach. Invalid input.";
	}
	else {
		$rows_added = $sql->rowCount();

		if($rows_added==0) {
			echo "Failed to assign coach to team.";
		}

		else {
			echo "Coach successfully assigned to team!";
		}
	}
}
else {
	echo "You lack the required permissions.";
}
?>