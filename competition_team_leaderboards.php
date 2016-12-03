<?php include_once("inc/header1.php");

// if you need to return header() statements, do them here. SQL connection has already been established.

include_once("inc/header2.php"); ?>

BBDB</title>

<?php // include anything else you want to put in <head> here.

if ((!permission_check(4)) || (!isset($_SESSION['domain_id']))) { // standard read privilages
	include_once("inc/no_permission.php");
}
include_once("inc/header3.php");
$domain_id = $_SESSION['domain_id'];

$table_list_sql = '
	SELECT COALESCE(c.description, t.description) AS description
			, t.table_id, t.column_header
	FROM bb_table_team_competition c
	INNER JOIN bb_lkp_table_team t ON c.table_id = t.table_id
	WHERE c.domain_id = ? AND c.competition_id = ?
	ORDER BY c.order_no, c.table_id';
	

$table_contents_sql = "
	SELECT c.table_id, t.description AS 'team_name', rn.rank, rn.score, r.description AS race_name, r.race_id, t.team_id
	FROM bb_table_player_competition c
	INNER JOIN bb_stat_table_team_competition_rank rn ON c.table_id = rn.table_id AND c.domain_id = rn.domain_id AND c.competition_id = rn.competition_id
	INNER JOIN bb_team t ON rn.team_id = t.team_id
	INNER JOIN bb_lkp_race r ON t.race_id = r.race_id
	WHERE c.domain_id = ? AND c.competition_id = ?
	ORDER BY c.order_no, c.table_id, rn.rank, rn.order_no";

	$table_list_obj = $conn->prepare($table_list_sql);
	$table_list_obj->bindParam(1, $domain_id, PDO::PARAM_INT);
	$table_list_obj->bindParam(2, $_GET['competition_id'], PDO::PARAM_INT);
	
	$table_contents_obj = $conn->prepare($table_contents_sql);
	$table_contents_obj->bindParam(1, $domain_id, PDO::PARAM_INT);
	$table_contents_obj->bindParam(2, $_GET['competition_id'], PDO::PARAM_INT);
	
	$table_list_obj->execute();
	$table_contents_obj->execute();
	
	$table_list = $table_list_obj->fetchAll(PDO::FETCH_ASSOC);

	
?>



<h2>Team statistics for the competition</h2>

<table>
<?php
$left_or_right = 'LEFT';
$row = $table_contents_obj->fetch(PDO::FETCH_ASSOC);
foreach ($table_list AS $table) {
	if ($left_or_right=='LEFT') {
		echo '<tr>';
	}
	echo '<td style="width:450px">';
	// Now the cool stuff
		echo '<h3>' . $table['description'] .'</h3>'.PHP_EOL;
		
		echo '<div style="height:200px;overflow:auto;"><table>';
		echo '<tr><th>Rank</th><th>Team</th><th>Race</th><th>' . $table['column_header'] . '</th></tr>';
		// This neeeds to be fixed
		while ($row['table_id']==$table['table_id']) {
			echo '<tr><td>' . $row['rank'] . '</td>';
			echo '<td><a href="team.php?team_id=' . $row['team_id'] . '">' . $row['team_name'] . '</a></td>';
			echo '<td>' . $row['race_name'] . '</td>';
			echo '<td>' . $row['score'] . '</td></tr>';
			$row = $table_contents_obj->fetch(PDO::FETCH_ASSOC);
		}
		echo '</table></div>';
	
	echo '</td>';
	if ($left_or_right=='RIGHT') {
		echo '</tr>';
		$left_or_right = 'LEFT';
	}
	else {
		$left_or_right = 'RIGHT';
	}
} // now onto the next table!

if ($left_or_right=='LEFT') {
	echo '</tr>';
}

?>
</table>



<?php include_once("inc/footer.php"); ?>