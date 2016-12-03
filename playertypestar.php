<h3>Match History</h3>

<table id="matchTable" class="tablesorter">
<thead><tr>
<th title="Match ID">ID</th><th title="Match Date">Date</th><th>Hiring Team</th><th title="Hiring Race">H. Race</th><th>Opposition Team</th><th title="Opposition Race">O. Race</th>
<th title="Played">Pl</th><th>MVP</th><th title="Touchdowns Scored">TD</th><th title="Passes">PS</th><th title="Interceptions">I</th><th title="Casualtied Inflicted">CAS</th>
<th title="Knockdowns Inflicted">KDF</th><th title="Knockdowns Recieved">KDA</th>
<th title="Stuns Inflicted">SF</th><th title="Stuns Recieved">SA</th>
<th title="Knockouts Inflicted">KOF</th><th title="Knockouts Recieved">KOA</th>
<th title="Tackles Inflicted">TF</th><th title="Tackles Recieved">TA</th>
<th title="Injuries Recieved">INJ</th><th title="Kills">K</th><th title="Catches">C</th><th title="Meters Run">MR</th><th title="Meters Passed">MP</th>
</tr></thead><tbody>

<?php
// List all races that star player will play for
	$stmt=$conn->prepare("SELECT GROUP_CONCAT(r.description ORDER BY r.description) AS race_list
				FROM bb_lkp_star_player_race l
				INNER JOIN bb_lkp_race r ON l.race_id = r.race_id
				WHERE l.ruleset_id = 1
				AND l.player_type_id = ?");

	$stmt->bindParam(1, $_GET['player_type_id'], PDO::PARAM_INT);
	$stmt->execute();

	$pirate = $stmt->fetch(PDO::FETCH_ASSOC);

	echo '<p>Will play for... <strong>' . $pirate['race_list'] . '</strong>.</p>';


	$sql = $conn->prepare("SELECT	m.match_id
				, UNIX_TIMESTAMP(m.match_date) as match_date
				, ht.team_id AS hired_team_id
				, ht.description AS hired_team_name
				, hr.race_id AS hired_race_id
				, hr.description AS hired_race_name
				, hr.short_description AS hired_race_name_short
				, at.team_id AS enemy_team_id
				, at.description AS enemy_team_name
				, ar.race_id AS enemy_race_id
				, ar.description AS enemy_race_name
				, ar.short_description AS enemy_race_name_short
				, pms.match_played
				, pms.mvp
				, pms.passes
				, pms.catches
				, pms.interceptions
				, pms.touchdowns
				, pms.inflicted_knockdown
				, pms.inflicted_tackles
				, pms.inflicted_ko
				, pms.inflicted_stun
				, pms.inflicted_injury
				, pms.inflicted_dead
				, pms.meters_run
				, pms.meters_pass
				, pms.sustained_interception
				, pms.sustained_knockdown
				, pms.sustained_tackles
				, pms.sustained_ko
				, pms.sustained_stun
				, pms.sustained_injury
				, pms.sustained_dead
	FROM bb_player_match_stats pms
	INNER JOIN bb_player_oneoff po ON pms.match_id = po.match_id AND po.bb1_id = pms.oneoff_id
	INNER JOIN bb_team ht ON po.team_id = ht.team_id
	INNER JOIN bb_lkp_race hr ON ht.race_id = hr.race_id
	INNER JOIN bb_match m ON pms.match_id = m.match_id
	INNER JOIN bb_team at ON at.team_id IN (m.home_team_id, m.away_team_id) AND at.team_id <> po.team_id
	INNER JOIN bb_lkp_race ar ON at.race_id = ar.race_id
	WHERE pms.player_type_id = ?
	AND po.player_status_id = 4
	ORDER BY match_date DESC
  ");

   $sql->bindParam(1, $_GET['player_type_id'], PDO::PARAM_INT);
   $sql->execute();
   $match_list = $sql->fetchAll(PDO::FETCH_ASSOC);
foreach ($match_list as $row) {

	echo '<tr>';
	echo '<td><a href="matchdetail.php?match_id=' . $row['match_id'] . '">'. $row['match_id'] . '</a></td>'.PHP_EOL;
	echo '<td>' . date('M j, Y h:i A', $row['match_date']) . '</td>'.PHP_EOL;
	echo '<td><a href="team.php?team_id=' . $row['hired_team_id'] . '">'. $row['hired_team_name'] . '</a></td>'.PHP_EOL;
	echo '<td><a title = "' . $row['hired_race_name'] . '" href="race.php?race_id=' . $row['hired_race_id'] . '">'. $row['hired_race_name_short'] . '</a></td>'.PHP_EOL;
	echo '<td><a href="team.php?team_id=' . $row['enemy_team_id'] . '">'. $row['enemy_team_name'] . '</a></td>'.PHP_EOL;
	echo '<td><a title = "' . $row['enemy_race_name'] . '" href="race.php?race_id=' . $row['enemy_race_id'] . '">'. $row['enemy_race_name_short'] . '</a></td>'.PHP_EOL;
	echo '<td>'. $row['match_played'] . '</td>'.PHP_EOL;
	echo '<td>'. $row['mvp'] . '</td>'.PHP_EOL;
	echo '<td>'. $row['touchdowns'] . '</td>'.PHP_EOL;
	echo '<td>'. $row['passes'] . '</td>'.PHP_EOL;
	echo '<td>'. $row['interceptions'] . '</td>'.PHP_EOL;
	echo '<td>'. $row['inflicted_injury'] . '</td>'.PHP_EOL;
	echo '<td>'. $row['inflicted_knockdown'] . '</td>'.PHP_EOL;
	echo '<td>'. $row['sustained_knockdown'] . '</td>'.PHP_EOL;
	echo '<td>'. $row['inflicted_stun'] . '</td>'.PHP_EOL;
	echo '<td>'. $row['sustained_stun'] . '</td>'.PHP_EOL;
	echo '<td>'. $row['inflicted_ko'] . '</td>'.PHP_EOL;
	echo '<td>'. $row['sustained_ko'] . '</td>'.PHP_EOL;
	echo '<td>'. $row['inflicted_tackles'] . '</td>'.PHP_EOL;
	echo '<td>'. $row['sustained_tackles'] . '</td>'.PHP_EOL;
	echo '<td>'. $row['sustained_injury'] . '</td>'.PHP_EOL;
	echo '<td>'. $row['inflicted_dead'] . '</td>'.PHP_EOL;
	echo '<td>'. $row['catches'] . '</td>'.PHP_EOL;
	echo '<td>'. $row['meters_run'] . '</td>'.PHP_EOL;
	echo '<td>'. $row['meters_pass'] . '</td>'.PHP_EOL;
	echo '</tr>';
  }

?>
</tbody>
</table>