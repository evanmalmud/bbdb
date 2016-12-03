<?php include_once("inc/header1.php");

// if you need to return header() statements, do them here. SQL connection has already been established.

include_once("inc/header2.php"); ?>

BBDB</title>

<script>
$(document).ready(function() 
    { 
        $("#matchTable").tablesorter(); 
	$("#playerTable").tablesorter(); 
	$("#exPlayerTable").tablesorter(); 
    } 
); 
    
</script>
<?php // include anything else you want to put in <head> here.

include_once("inc/header3.php"); 

	$check_stmt = $conn->prepare("SELECT t.* FROM bb_team t
					WHERE t.team_id = ?");

	$check_stmt->bindParam(1, $_GET['team_id'], PDO::PARAM_INT);
	$check_stmt->execute();

	$team = $check_stmt->fetch(PDO::FETCH_ASSOC);

	if(!$team) {
		die("I cannot find that race.");
	}

echo "<table><tr><td style=\"vertical-align:top\"><h2>Team Profile - " . trim($team['description']) . "</h2>".PHP_EOL;
echo "<h3>Overall record</h3>";
$team_sql = $conn->prepare("
	SELECT  t.description as team_name
		, c.description as coach_name
		, r.description as race_name
		, UNIX_TIMESTAMP(MAX(m.match_date)) AS last_match_played
		, c.coach_id
		, r.race_id
		, t.*
	FROM bb_team t
	INNER JOIN bb_lkp_race r ON t.race_id = r.race_id
	INNER JOIN bb_match m ON t.team_id = m.home_team_id OR t.team_id = m.away_team_id
	LEFT JOIN bb_coach c ON t.coach_id = c.coach_id
	LEFT JOIN bb_match_team_stats s ON m.match_id = s.match_id AND t.team_id = s.team_id
	WHERE t.team_id = ?
	GROUP BY t.description, c.description, t.team_id, c.coach_id, r.race_id, r.description
	ORDER BY COUNT(*) DESC");

	$team_sql->bindParam(1, $_GET['team_id'], PDO::PARAM_INT);
	$team_sql->execute();

	$team = $team_sql->fetch(PDO::FETCH_ASSOC);
	$losses = $team['total_matches'] - $team['wins'] - $team['draws'];

	echo '<p>This <a href="race.php?race_id=' . $team['race_id'] . '">' . $team['race_name'] .'</a> team is managed by <a href="coach.php?coach_id=' . $team['coach_id'] . '"> ' . $team['coach_name'] .'</a></p>'.PHP_EOL;

 $match_sql = $conn->prepare("SELECT m.match_id
				, UNIX_TIMESTAMP(m.match_date) as match_date
				, CASE WHEN t.team_id = m.home_team_id THEN m.home_touchdown_count ELSE m.away_touchdown_count END AS td_scored
				, CASE WHEN t.team_id = m.home_team_id THEN m.away_touchdown_count ELSE m.home_touchdown_count END AS td_conceded
				, CASE WHEN m.home_touchdown_count = m.away_touchdown_count THEN 'D'
					WHEN t.team_id = m.home_team_id AND m.home_touchdown_count > m.away_touchdown_count THEN 'W'
					WHEN t.team_id = m.away_team_id AND m.away_touchdown_count > m.home_touchdown_count THEN 'W'
					ELSE 'L' END AS result_letter
				, ot.description AS team_name
				, r.description AS race_name
				, COALESCE(oc.description, '???') AS coach_name
				, ot.team_id as team_id
				, r.race_id as race_id
				, oc.coach_id as coach_id
				, s.passes
				, s.catches
				, s.interceptions
				, s.inflicted_knockdown
				, s.inflicted_ko
				, s.inflicted_tackles
				, s.inflicted_injury
				, s.inflicted_dead
				, s.meters_run
				, s.meters_pass
				, c.description as 'competition_name'
				, c.competition_id
		FROM bb_team t
		
		INNER JOIN bb_match m ON t.team_id = m.home_team_id OR t.team_id = m.away_team_id
		INNER JOIN bb_team ot ON ot.team_id = CASE WHEN  t.team_id = m.home_team_id THEN m.away_team_id ELSE m.home_team_id END
		LEFT JOIN bb_coach oc ON ot.coach_id = oc.coach_id
		INNER JOIN bb_lkp_race r ON ot.race_id = r.race_id
		LEFT JOIN bb_match_team_stats s ON m.match_id = s.match_id AND t.team_id = s.team_id
		LEFT JOIN bb_match_competition mc ON m.match_id = mc.match_id AND mc.default_competition = 1
		LEFT JOIN bb_competition c ON mc.competition_id = c.competition_id AND c.domain_id = m.domain_id
		WHERE t.team_id = ?
 ORDER BY m.match_date DESC");

	$match_sql->bindParam(1, $_GET['team_id'], PDO::PARAM_INT);
	$match_sql->execute();
	// this statement will potentially fetch a lot of data, but should make the page load all at once??? Maybe?
	$match_data = $match_sql->fetchAll(PDO::FETCH_ASSOC);

?>

<table>
<tr><th title="Played">Pl</th><th title="Won">W</th><th title="Draw">D</th><th title="Loss">L</th><th title="Touchdowns Scored">F</th><th Title="Touchdowns Conceded">A</th>
<th Title="Average Possession">A.Pos</th><th Title="Average Occupation (own)">A.OccO</th><th Title="Average Occupation (their)">A.OccT</th>
<th Title="Total Cash earned">Tot Cash</th><th Title="Total Spectators">Tot Spec</th>

</tr>
<tr>
<?php

	

	echo '<td>'. $team['match_played'] . '</td>'.PHP_EOL;
	echo '<td>'. $team['wins'] . '</td>'.PHP_EOL;
	echo '<td>'. $team['draws'] . '</td>'.PHP_EOL;
	echo '<td>'. $team['losses']  . '</td>'.PHP_EOL;
	echo '<td>'. $team['touchdowns'] . '</td>'.PHP_EOL;
	echo '<td>'. $team['sustained_touchdowns'] . '</td>'.PHP_EOL;

	echo '<td>'. $team['avg_possession'] . '</td>'.PHP_EOL;
	echo '<td>'. $team['avg_occupation_own'] . '</td>'.PHP_EOL;
	echo '<td>'. $team['avg_occupation_their'] . '</td>'.PHP_EOL;
	echo '<td>'. $team['total_cash'] . '</td>'.PHP_EOL;
	echo '<td>'. $team['total_spectators'] . '</td>'.PHP_EOL;

echo '</tr></table><br/><table><tr><th>FOR:</th>';

	echo '<td>'. $team['passes'] . '</td>'.PHP_EOL;
	echo '<td>'. $team['catches'] . '</td>'.PHP_EOL;
	echo '<td>'. $team['interceptions'] . '</td>'.PHP_EOL;
	echo '<td>'. $team['meters_run'] . '</td>'.PHP_EOL;
	echo '<td>'. $team['meters_pass'] . '</td>'.PHP_EOL;
	echo '<td>'. $team['inflicted_knockdown'] . '</td>'.PHP_EOL;
	echo '<td>'. $team['inflicted_tackles'] . '</td>'.PHP_EOL;
	echo '<td>'. $team['inflicted_ko'] . '</td>'.PHP_EOL;
	echo '<td>'. $team['inflicted_injury'] . '</td>'.PHP_EOL;
	echo '<td>'. $team['inflicted_dead'] . '</td>'.PHP_EOL;

echo '</tr><tr><th></th><th title="Passes">PS</th><th title="Catches">C</th><th title="Interceptions">I</th><th title="Meters Run">MR</th><th title="Meters Passed">MP</th>
<th title="Knockdowns">KD</th><th title="Tackles">TK</th><th title="KOs">KO</th>
<th title="Casualties">Cas</th><th title="Killed">K</th></tr>';

echo '<tr><th>AGAINST:</th>';

	echo '<td title="Data not stored in saved games file">'. $team['sustained_passes'] . '</td>'.PHP_EOL;
	echo '<td title="Data not stored in saved games file">'. $team['sustained_catches'] . '</td>'.PHP_EOL;
	echo '<td>'. $team['sustained_interceptions'] . '</td>'.PHP_EOL;
	echo '<td>'. $team['sustained_meters_run'] . '</td>'.PHP_EOL;
	echo '<td>'. $team['sustained_meters_pass'] . '</td>'.PHP_EOL;
	echo '<td>'. $team['sustained_knockdown'] . '</td>'.PHP_EOL;
	echo '<td>'. $team['sustained_tackles'] . '</td>'.PHP_EOL;
	echo '<td>'. $team['sustained_ko'] . '</td>'.PHP_EOL;
	echo '<td>'. $team['sustained_injury'] . '</td>'.PHP_EOL;
	echo '<td>'. $team['sustained_dead'] . '</td>'.PHP_EOL;

?>
</tr></table>
</td><td style="vertical-align:top"><img src="http://www.gandalfgames.net/bbdb/img/logos/Logo_<?php echo $team['str_logo']; ?>.png" alt="Team logo"/>
</td></tr></table>

<h3>Players</h3>
<table id="playerTable" class="tablesorter">
<thead>
<tr><th title="Squad Number">#</th><th>Type</th><th>Name</th><th>MV</th><th>ST</th><th>AG</th><th>AV</th><th>Lv</th><th>XP</th><th>Val</th><th>Skills</th>
<th title="Played">Pl</th><th>MVP</th><th title="Touchdowns Scored">TD</th><th title="Passes">PS</th><th title="Interceptions">I</th><th title="Casualtied Inflicted">CAS</th>
<th title="Knockdowns Inflicted">KDF</th><th title="Knockdowns Recieved">KDA</th><th title="Knockouts Inflicted">KOF</th><th title="Knockouts Recieved">KOA</th>
<th title="Injuries Recieved">INJ</th><th title="Kills">K</th><th title="Meters Run">MR</th><th title="Meters Passed">MP</th>
</tr>
</thead>
<tbody>


<?php

  $sql = $conn->prepare("SELECT p.player_id
				, pt.player_type_id
				, pt.description AS player_type
				, COALESCE(pt.short_description,pt.description,pt.long_description) AS player_type_short
				, p.description AS player_name
				, p.player_status_id
				, pstat.description AS player_status
				, p.mv
				, p.st
				, p.ag
				, p.av
				, p.level
				, p.experience
				, p.base_value
				, p.current_value
				, p.squad_number
				, p.match_played
				, p.mvp
				, p.passes
				, p.catches
				, p.interceptions
				, p.touchdowns
				, p.inflicted_knockdown
				, p.inflicted_tackles
				, p.inflicted_ko
				, p.inflicted_stun
				, p.inflicted_injury
				, p.inflicted_dead
				, p.meters_run
				, p.meters_pass
				, p.current_value
				, p.squad_number
				, p.sustained_interception
				, p.sustained_knockdown
				, p.sustained_tackles
				, p.sustained_ko
				, p.sustained_stun
				, p.sustained_injury
				, p.sustained_dead
				, p.blocks_attempted
				, p.dodges_made
				, GROUP_CONCAT(DISTINCT CONCAT('<a href=\"skill.php?skill_id=', pts.skill_id, '\">', ptsk.human_desc, '</a>') ORDER BY ptsk.human_desc) AS default_skill_list
				, GROUP_CONCAT(DISTINCT CONCAT('<a href=\"skill.php?skill_id=', psk.skill_id, '\">', sk.human_desc, '</a>') ORDER BY sk.human_desc) AS earned_skill_list
		FROM bb_player p
		INNER JOIN bb_lkp_player_type pt ON p.player_type_id = pt.player_type_id
		INNER JOIN bb_lkp_player_status pstat ON p.player_status_id = pstat.player_status_id
		LEFT JOIN bb_player_skill psk ON p.player_id = psk.player_id
		LEFT JOIN bb_lkp_skill sk ON psk.skill_id = sk.skill_id
		LEFT JOIN bb_lkp_player_type_skill pts ON p.player_type_id = pts.player_type_id
		LEFT JOIN bb_lkp_skill ptsk ON pts.skill_id = ptsk.skill_id
		WHERE p.team_id = ?
		GROUP BY p.player_id
 	ORDER BY p.player_status_id, p.squad_number ASC");
   $sql->bindParam(1, $_GET['team_id'], PDO::PARAM_INT);
   $sql->execute();
   $player_list = $sql->fetchAll(PDO::FETCH_ASSOC);
   foreach ($player_list as $row)
   {
	if ($row['player_status_id']<>1) { break; } // We only want active players here - as soon as we come across a non-active one we can stop, due to SQL statement ordering.
	echo "<tr>";
	echo '<td>'. $row['squad_number'] . '</td>'.PHP_EOL;
	echo '<td><a href="playertype.php?player_type_id=' . $row['player_type_id'] . '">'. $row['player_type_short'] . '</a></td>'.PHP_EOL;
	echo '<td><a href="player.php?player_id=' . $row['player_id'] . '">'. $row['player_name'] . '</a></td>'.PHP_EOL;
	echo '<td>'. $row['mv'] . '</td>'.PHP_EOL;
	echo '<td>'. $row['st'] . '</td>'.PHP_EOL;
	echo '<td>'. $row['ag'] . '</td>'.PHP_EOL;
	echo '<td>'. $row['av'] . '</td>'.PHP_EOL;
	echo '<td>'. $row['level'] . '</td>'.PHP_EOL;
	echo '<td>'. $row['experience'] . '</td>'.PHP_EOL;
	echo '<td>'. $row['current_value'] . '</td>'.PHP_EOL;
	echo '<td>'. $row['default_skill_list'] . " | " . $row['earned_skill_list'] . '</td>'.PHP_EOL;
	echo '<td>'. $row['match_played'] . '</td>'.PHP_EOL;
	echo '<td>'. $row['mvp'] . '</td>'.PHP_EOL;
	echo '<td>'. $row['touchdowns'] . '</td>'.PHP_EOL;
	echo '<td>'. $row['passes'] . '</td>'.PHP_EOL;
	echo '<td>'. $row['interceptions'] . '</td>'.PHP_EOL;
	echo '<td>'. $row['inflicted_injury'] . '</td>'.PHP_EOL;
	echo '<td>'. $row['inflicted_knockdown'] . '</td>'.PHP_EOL;
	echo '<td>'. $row['sustained_knockdown'] . '</td>'.PHP_EOL;
	echo '<td>'. $row['inflicted_ko'] . '</td>'.PHP_EOL;
	echo '<td>'. $row['sustained_ko'] . '</td>'.PHP_EOL;
	echo '<td>'. $row['sustained_injury'] . '</td>'.PHP_EOL;
	echo '<td>'. $row['inflicted_dead'] . '</td>'.PHP_EOL;
	echo '<td>'. $row['meters_run'] . '</td>'.PHP_EOL;
	echo '<td>'. $row['meters_pass'] . '</td>'.PHP_EOL;
	echo "</tr>".PHP_EOL;
   }

?>
</tbody></table>

<h3>Former players</h3>

<table id="exPlayerTable" class="tablesorter">
<thead>
<tr><th>Status</th><th title="Squad Number">#</th><th>Type</th><th>Name</th><th>MV</th><th>ST</th><th>AG</th><th>AV</th><th>Lv</th><th>XP</th><th>Val</th><th>Skills</th>
<th title="Played">Pl</th><th>MVP</th><th title="Touchdowns Scored">TD</th><th title="Passes">PS</th><th title="Interceptions">I</th><th title="Casualtied Inflicted">CAS</th>
<th title="Knockdowns Inflicted">KDF</th><th title="Knockdowns Recieved">KDA</th><th title="Knockouts Inflicted">KOF</th><th title="Knockouts Recieved">KOA</th>
<th title="Injuries Recieved">INJ</th><th title="Kills">K</th><th title="Meters Run">MR</th><th title="Meters Passed">MP</th>
</tr>
</thead>
<tbody>
<?php

 foreach ($player_list as $row)
   {
	if ($row['player_status_id']==1) { continue; }  // non-active players only wanted here, so skip any active players we find
	echo "<tr>";
	echo '<td>'. $row['player_status'] . '</td>'.PHP_EOL;
	echo '<td>'. $row['squad_number'] . '</td>'.PHP_EOL;
	echo '<td><a href="playertype.php?player_type_id=' . $row['player_type_id'] . '">'. $row['player_type_short'] . '</a></td>'.PHP_EOL;
	echo '<td><a href="player.php?player_id=' . $row['player_id'] . '">'. $row['player_name'] . '</a></td>'.PHP_EOL;
	echo '<td>'. $row['mv'] . '</td>'.PHP_EOL;
	echo '<td>'. $row['st'] . '</td>'.PHP_EOL;
	echo '<td>'. $row['ag'] . '</td>'.PHP_EOL;
	echo '<td>'. $row['av'] . '</td>'.PHP_EOL;
	echo '<td>'. $row['level'] . '</td>'.PHP_EOL;
	echo '<td>'. $row['experience'] . '</td>'.PHP_EOL;
	echo '<td>'. $row['current_value'] . '</td>'.PHP_EOL;
	echo '<td>'. $row['default_skill_list'] . " | " . $row['earned_skill_list'] . '</td>'.PHP_EOL;
	echo '<td>'. $row['match_played'] . '</td>'.PHP_EOL;
	echo '<td>'. $row['mvp'] . '</td>'.PHP_EOL;
	echo '<td>'. $row['touchdowns'] . '</td>'.PHP_EOL;
	echo '<td>'. $row['passes'] . '</td>'.PHP_EOL;
	echo '<td>'. $row['interceptions'] . '</td>'.PHP_EOL;
	echo '<td>'. $row['inflicted_injury'] . '</td>'.PHP_EOL;
	echo '<td>'. $row['inflicted_knockdown'] . '</td>'.PHP_EOL;
	echo '<td>'. $row['sustained_knockdown'] . '</td>'.PHP_EOL;
	echo '<td>'. $row['inflicted_ko'] . '</td>'.PHP_EOL;
	echo '<td>'. $row['sustained_ko'] . '</td>'.PHP_EOL;
	echo '<td>'. $row['sustained_injury'] . '</td>'.PHP_EOL;
	echo '<td>'. $row['inflicted_dead'] . '</td>'.PHP_EOL;
	echo '<td>'. $row['meters_run'] . '</td>'.PHP_EOL;
	echo '<td>'. $row['meters_pass'] . '</td>'.PHP_EOL;
	echo "</tr>".PHP_EOL;
   }

?>
</tbody></table>

<h3>Match History</h3>

<table id="matchTable" class="tablesorter">
<thead>
<tr><th>ID</th><th title="Result (W/D/L)">R</th><th>Match Date</th><th title="Default Competition">Competition</th><th>Opposition Team</th><th>Opposition Race</th><th title="Touchdowns Scored">F</th>
<th title="Touchdowns Conceded">A</th><th title="Passes">P</th><th title="Catches">C</th><th title="Interceptions">I</th><th title="Knockdowns (ie armour breaks)">KD</th>
<th title="Inflicted KO's">KO</th><th title="Inflicted tackles">T</th><th title="Casualties (ie injuries inflicted)">Cas</th><th title="Kills inflicted">K</th>
<th title="Meters Run">MR</th><th title="Meters Passed">MP</th>
</thead>
<tbody>

<?php

foreach ($match_data as $row)
   {
	echo "<tr>";
	echo '<td><a href="matchdetail.php?match_id=' . $row['match_id'] . '">'. $row['match_id'] . '</a></td>'.PHP_EOL;
	echo '<td';
		if ($row['result_letter']=="W") { echo ' class="win"'; }
		if ($row['result_letter']=="D") { echo ' class="draw"'; }
		if ($row['result_letter']=="L") { echo ' class="loss"'; }
	echo '>' . $row['result_letter'] . '</td>'.PHP_EOL;
	echo '<td>'. date('M j, Y h:i A', $row['match_date']) . '</td>'.PHP_EOL;

	echo '<td><a href="competition.php?competition_id=' . $row['competition_id'] . '">'. $row['competition_name'] . '</a></td>'.PHP_EOL;
	echo '<td><a href="team.php?team_id=' . $row['team_id'] . '">'. $row['team_name'] . '</a></td>'.PHP_EOL;
	echo '<td><a href="race.php?race_id=' . $row['race_id'] . '">'. $row['race_name'] . '</a></td>'.PHP_EOL;


	echo '<td>'. $row['td_scored'] . '</td>'.PHP_EOL;
	echo '<td>'. $row['td_conceded'] . '</td>'.PHP_EOL;


	echo '<td>'. $row['passes'] . '</td>'.PHP_EOL;
	echo '<td>'. $row['catches'] . '</td>'.PHP_EOL;
	echo '<td>'. $row['interceptions'] . '</td>'.PHP_EOL;
	echo '<td>'. $row['inflicted_knockdown'] . '</td>'.PHP_EOL;
	echo '<td>'. $row['inflicted_ko'] . '</td>'.PHP_EOL;
	echo '<td>'. $row['inflicted_tackles'] . '</td>'.PHP_EOL;
	echo '<td>'. $row['inflicted_injury'] . '</td>'.PHP_EOL;
	echo '<td>'. $row['inflicted_dead'] . '</td>'.PHP_EOL;
	echo '<td>'. $row['meters_run'] . '</td>'.PHP_EOL;
	echo '<td>'. $row['meters_pass'] . '</td>'.PHP_EOL;
	echo "</tr>".PHP_EOL;
   }

?>

</tbody>
</table>


<p>To do... colour the W/D/L in green/yellow/red (v1)... also add some more stats here... blocks/dodges (v2).</p>


<?php include_once("inc/footer.php"); ?>