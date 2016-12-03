<?php
include_once("../inc/header1.php");

if (permission_check(8)) {  // close a competition
	$competition_desc = $_POST['comp_name'];
	$competition_short_desc = $_POST['comp_short_name'];
	$competition_type_id = (int) $_POST['comp_type_id'];

	// create entry in tbl_competition
	$sql = $conn->prepare("INSERT INTO bb_competition (domain_id, competition_type_id, description, completed, short_description, auto_enrol, tiebreaker_id)
							SELECT ?, ?, ?, 0, ?, 1, 1");
	if (!$sql->execute(array($_SESSION['domain_id'], $competition_type_id, $competition_desc, $competition_short_desc))) {
		echo "Failed to create competition. Probably the name or short name already exists.";
	}
	else {
		$competition_id = $conn->lastInsertId();

		// FOR THE FUTURE... some form of putting teams in seperate divisions?

		// populate bb_table_team_competition
		$sql = $conn->prepare("INSERT INTO bb_table_team_competition
								SELECT table_id, ?, ?, chart_size, description, featured_chart_no, order_no
								FROM bb_table_team_competition
								WHERE competition_id = 8");
		$sql->execute(array($competition_id, $_SESSION['domain_id']));

		// populate bb_table_player_competition
		$sql = $conn->prepare("INSERT INTO bb_table_player_competition
								SELECT table_id, ?, ?, chart_size, description, featured_chart_no, order_no
								FROM bb_table_player_competition
								WHERE competition_id = 8");
		$sql->execute(array($competition_id, $_SESSION['domain_id']));

		// Done?
		echo "League created!";
	}
}
else {
	echo "You lack the required permissions.";
}
?>