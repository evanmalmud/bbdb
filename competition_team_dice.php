<?php include_once("inc/header1.php");

// if you need to return header() statements, do them here. SQL connection has already been established.

ini_set('display_errors', 'On');
ini_set('html_errors', 0);
error_reporting(-1);

include_once("inc/header2.php"); ?>

BBDB</title>

<script>
$(document).ready(function() 
    { 
        $("#D6Table").tablesorter();
        $("#2D6Table").tablesorter(); 
    } 
); 
    
</script>
<script src="https://www.google.com/jsapi"></script>
<?php // include anything else you want to put in <head> here.


if ((!permission_check(4)) || (!isset($_SESSION['domain_id']))) { // standard read privilages
	include_once("inc/no_permission.php");
}

include_once("inc/header3.php"); 
	$domain_id = $_SESSION['domain_id'];

	$competition_sql = $conn->prepare("SELECT c.*, t.description as competition_type FROM bb_competition c
						INNER JOIN bb_lkp_competition_type t ON c.competition_type_id = t.competition_type_id
					WHERE competition_id = ?
					AND domain_id = ?
					AND c.competition_type_id <> 0");

	$competition_sql->bindParam(1, $_GET['competition_id'], PDO::PARAM_INT);
	$competition_sql->bindParam(2, $domain_id, PDO::PARAM_INT);
	$competition_sql->execute();
	$competition = $competition_sql->fetch(PDO::FETCH_ASSOC);

	if(!$competition) {
		include_once("inc/header3.php");
		echo "<p>I cannot find that competition.</p>";
		include_once("inc/footer.php");
		die();
	}



echo "<h2>".$competition['description']."</h2>".PHP_EOL;

?>
<h2>D6 rolls by team</h2>

<?php

	function pad_row($curr_index, $max_index) {
		while($max_index>$curr_index) { // spit out empty cells until we have done 6 columns
				echo '<td></td>';
				$curr_index++;
			}
	}
	
	function table_cell_with_link($target_text, $target_link = null) {
		echo '<td>';
		if(is_null($target_link)) {
			echo $target_text;
		} else {
			echo '<a href="' . $target_link . '">';
			echo $target_text . "</a>";
		}
		echo "</td>".PHP_EOL;
	}
	
	function do_start_of_row($totals_row) {
		echo "<tr>";
		table_cell_with_link($totals_row['team_name'], "team.php?team_id=" . $totals_row['team_id']);
		table_cell_with_link($totals_row['roll_type']);
	}
	
	function do_end_of_row ($totals_row) {
		echo "<td>" . $totals_row['total_roll_count'] . "</td><td>" . $totals_row['success_roll_count'];
		echo "</td></tr>".PHP_EOL;
	}
	
	$totals_sql = $conn->prepare("
			SELECT t.team_id, t.description AS team_name
				, rt.roll_type_id, rt.description AS roll_type
				, SUM(CASE WHEN ml.outcome_id = 14 OR ml.outcome_id = 20 THEN 1 ELSE 0 END) AS success_roll_count
				, COUNT(*) as total_roll_count
			FROM bb_matchlog ml 
			INNER JOIN bb_match m ON ml.match_id = m.match_id
			INNER JOIN bb_match_competition mc ON m.match_id = mc.match_id
			INNER JOIN bb_lkp_roll_type rt ON ml.roll_type_id = rt.roll_type_id
			INNER JOIN bb_player p ON ml.player_id = p.player_id
			INNER JOIN bb_team t ON p.team_id = t.team_id
			WHERE mc.competition_id = ?
			AND rt.dice_type_id = 1
			GROUP BY t.team_id, rt.roll_type_id
			ORDER BY rt.description, t.description, t.team_id
	");
	
	
	$main_sql = $conn->prepare("
			SELECT t.team_id, t.description AS team_name
				, rt.roll_type_id, rt.description AS roll_type
				, ml.roll_value
				, COUNT(*) as roll_count
			FROM bb_matchlog ml 
			INNER JOIN bb_match m ON ml.match_id = m.match_id
			INNER JOIN bb_match_competition mc ON m.match_id = mc.match_id
			INNER JOIN bb_lkp_roll_type rt ON ml.roll_type_id = rt.roll_type_id
			INNER JOIN bb_player p ON ml.player_id = p.player_id
			INNER JOIN bb_team t ON p.team_id = t.team_id
			WHERE mc.competition_id = ?
			AND rt.dice_type_id = 1
			GROUP BY t.team_id, rt.roll_type_id, ml.roll_value
			ORDER BY rt.description, t.description, t.team_id, ml.roll_value

		");
	$main_sql->execute(array($_GET['competition_id']));
	$totals_sql->execute(array($_GET['competition_id']));

	echo '<table id="D6Table" class="tablesorter">';
	echo '<thead><tr><th>Team</th><th>Roll Type</th><th>1</th><th>2</th><th>3</th><th>4</th><th>5</th><th>6</th><th>Total</th><th>Successes</th></thead>';
	echo '<tbody>';
	
	$next_roll_value = 0;
	
	$totals_row = $totals_sql->fetch(PDO::FETCH_ASSOC); // get the first Totals row
	
	do_start_of_row($totals_row);
	$last_team_id = $totals_row['team_id']; // this means the first loop will work
	$last_roll_type_id = $totals_row['roll_type_id'];

	while ($row = $main_sql->fetch(PDO::FETCH_ASSOC)) {
			
		if(($last_team_id<>$row['team_id']) || ($last_roll_type_id<>$row['roll_type_id'])) { // it's a new roll type / team.... so do a new row, after padding out to & doing the total # of rolls??
			pad_row($next_roll_value, 6);
			do_end_of_row ($totals_row);
			if(($totals_row = $totals_sql->fetch(PDO::FETCH_ASSOC))===FALSE)  // outer loop moves on one
				{	echo "PANTS!"; break 1;}
			$next_roll_value = 0;
			do_start_of_row ($totals_row);
			$prev_totals_row = $totals_row;  // this is needed for the very last row of the table
		}
		
		$next_roll_value++; // guess what the next roll value is, ie 1 more than before
		while($row['roll_value']<>$next_roll_value) { // spit out empty cells until we get to the roll in question
			echo '<td></td>';
			$next_roll_value++;
			if($next_roll_value==10) {
				die; // something has gone horribly wrong. Ensures we don't get stuck in an infinite loop
			}
		}
		echo '<td>' . $row['roll_count'] . '</td>';
		$last_team_id=$row['team_id'];
		$last_roll_type_id = $row['roll_type_id'];
	}

	do_end_of_row ($prev_totals_row);
	echo '</tbody></table>'.PHP_EOL;


// Now repeat it for 2D6. Clarify who the rolls refer to.  Block dice maybe/maybe not?
// We have the data about if the roll succeeded or not. Could this be included?

include_once("inc/footer.php"); ?>
