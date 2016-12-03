<?php include_once("inc/header1.php");

// if you need to return header() statements, do them here. SQL connection has already been established.

include_once("inc/header2.php"); ?>

BBDB</title>

<?php // include anything else you want to put in <head> here.

include_once("inc/load_log.php");
include_once("inc/header3.php");
 ?>



<h2>Testing viewing the match log</h2>





<?php if (!empty($_POST)) :
    $match_id = $_POST['match_id'];
    $domain_id = 2;

    $sql = $conn->prepare("SELECT u.*, m.home_team_id FROM bb_upload u INNER JOIN bb_match m ON u.match_id = m.match_id WHERE u.match_id = ?");

	$sql->bindParam(1, $match_id, PDO::PARAM_INT);
	$sql->execute();

	$upload = $sql->fetch(PDO::FETCH_ASSOC);
	$home_team_id = $upload['home_team_id'];
	//echo "Home team is ";
	//echo $home_team_id;
	//echo '<br/>';

    bb1_transform_log($conn, $upload['upload_id'], $domain_id, 1,0);
	load_stat_turnovers($conn, $match_id, TRUE);
	
	$sql=$conn->prepare("SELECT CASE WHEN t.description IS NULL THEN 'N/A' ELSE t.description END AS team
								, p.description AS player
								, rt.description AS roll_type
								, COALESCE(CASE WHEN rt.dice_type_id = 4 THEN bdp.short_description ELSE roll_target END
											, '?') AS roll_target
								, COALESCE(bd.description, roll_value,'???') AS roll_value
								, CONCAT('(',CASE WHEN rt.dice_type_id = 4 THEN bdp.short_description ELSE roll_target END,')' 
									,COALESCE(bd.description, roll_value), '= ', COALESCE(raw_text, '')) AS roll_detail
								, COALESCE(w.description, 'blah') AS lookup_val
								, COALESCE(rr.description, 'no data') as reroll_status
								, COALESCE(o.description, rr.description, '???') as outcome
								, CASE WHEN t.team_id = :home_team_id THEN 'H' ELSE 'A' END as home_or_away
								, CASE WHEN roll_target_exact_flag = 0 THEN '?' ELSE '' END as accuracy_text
							FROM bb_matchlog ml
							LEFT JOIN bb_team t ON ml.team_id = t.team_id
							LEFT JOIN bb_player p ON ml.player_id = p.player_id
							LEFT JOIN bb_lkp_roll_type rt ON ml.roll_type_id = rt.roll_type_id
							LEFT JOIN bb_lkp_weather w ON ml.roll_lookup_id = w.weather_id AND ml.roll_type_id = 28
							LEFT JOIN bb_lkp_block_dice bd ON ml.roll_value = bd.block_dice_id AND rt.dice_type_id = 4
							LEFT JOIN bb_lkp_roll_outcome o ON ml.outcome_id = o.outcome_id
							LEFT JOIN bb_lkp_block_dice_perm bdp ON bdp.block_dice_perm_id = ml.roll_lookup_id
							LEFT JOIN bb_lkp_reroll_type rr ON ml.reroll_type_id = rr.reroll_type_id
							WHERE ml.match_id = :match_id
							ORDER BY ml.matchlog_id");
	
	$sql->bindParam(':home_team_id', $home_team_id, PDO::PARAM_INT);
	$sql->bindParam(':match_id', $match_id, PDO::PARAM_INT);
							
	$sql->execute();
	
	$matchlog_data = $sql->fetchAll(PDO::FETCH_ASSOC);
	
	echo '<ol>';
	foreach($matchlog_data AS $event) {
		if ($event['home_or_away']=='H') {
			echo '<li style=" color: #990000">';
		}
		else {
			echo '<li>';
		}
		echo '(RR: ' . $event['reroll_status'] . ') ';
		echo $event['player'] . ' ';
		echo $event['roll_type'] . ' (';
		echo $event['roll_target'];
		echo $event['accuracy_text'] . ')';
		echo $event['roll_value'];
		echo ' => ';
		echo $event['outcome'];
		echo '</li>';
	}
	echo '</ol>';
	
else: ?>
    <form action=<?php echo htmlspecialchars($_SERVER["PHP_SELF"]); ?> method="post">
        Match ID: <input type="text" name="match_id"><br/>
        <input type="submit">
    </form>
<?php endif; ?>







<?php include_once("inc/footer.php"); ?>
