<?php include_once("inc/header1.php");

// if you need to return header() statements, do them here. SQL connection has already been established.

include_once("inc/header2.php"); ?>

BBDB - coach</title>

<script>
$(document).ready(function() 
    { 
        $("#matchTable").tablesorter(); 
	$("#teamTable").tablesorter(); 
    } 
); 
    
</script>
<?php // include anything else you want to put in <head> here.

include_once("inc/header3.php"); 

	$check_stmt = $conn->prepare("SELECT c.* FROM bb_coach c
					WHERE c.coach_id = ?");

	$check_stmt->bindParam(1, $_GET['coach_id'], PDO::PARAM_INT);
	$check_stmt->execute();

	$coach = $check_stmt->fetch(PDO::FETCH_ASSOC);

	if(!$coach) {
		die("I cannot find that coach.");
	}

echo "<h2>Coach Profile - " . $coach['description'] . "</h2>".PHP_EOL;

if ($_GET['coach_id']==9)
{
	echo '<p>Also featuring his Technical Analyst... <strong>Robbie Savage!</strong><img src="https://metrouk2.files.wordpress.com/2015/02/avage-e1422918275432.jpg" width="300"/></p>';

}
?>


<table id="teamTable" class="tablesorter">
<thead>
<tr><th>Team Name</th><th>Race</th><th>P</th><th>W</th><th>D</th><th>L</th><th>F</th><th>A</th><th title="Team Value - probably prior to last match">TV</th><th>Last Match</th><th title="Passes">Pa</th><th>KDF</th><th>KDA</th></tr>
</thead>
<tbody>
<?php
$sql = $conn->prepare("
	SELECT  t.description as team_name
		, c.description as coach_name
		, t.wins
		, t.draws
		, t.losses
		, t.wins+t.draws+t.losses AS total_matches
		, t.touchdowns AS td_scored
		, t.sustained_touchdowns AS td_conceded
		, UNIX_TIMESTAMP(MAX(m.match_date)) AS last_match_played
		, t.team_id
		, c.coach_id
		, r.race_id
		, r.description as race_name
		, t.value
		, t.passes
		, t.inflicted_knockdown
		, t.sustained_knockdown
	FROM bb_match m
	INNER JOIN bb_team t ON t.team_id = m.home_team_id OR t.team_id = m.away_team_id
	INNER JOIN bb_coach c ON t.coach_id = c.coach_id
	INNER JOIN bb_lkp_race r ON t.race_id = r.race_id
	WHERE c.coach_id = ?
	GROUP BY t.team_id
	ORDER BY t.wins+t.draws+t.losses DESC");
$sql->bindParam(1, $_GET['coach_id'], PDO::PARAM_INT);
$sql->execute();

   $team_data = $sql->fetchAll(PDO::FETCH_ASSOC);
   foreach ($team_data as $row)
   {
	$losses = $row['total_matches'] - $row['wins'] - $row['draws'];
	echo "<tr>";
	echo '<td><a href="team.php?team_id=' . $row['team_id'] . '">'. $row['team_name'] . '</a></td>'.PHP_EOL;
	echo '<td><a href="race.php?race_id=' . $row['race_id'] . '">'. $row['race_name'] . '</a></td>'.PHP_EOL;
	echo '<td>'. $row['total_matches'] . '</td>'.PHP_EOL;
	echo '<td>'. $row['wins'] . '</td>'.PHP_EOL;
	echo '<td>'. $row['draws'] . '</td>'.PHP_EOL;
	echo '<td>'. $losses  . '</td>'.PHP_EOL;
	echo '<td>'. $row['td_scored'] . '</td>'.PHP_EOL;
	echo '<td>'. $row['td_conceded'] . '</td>'.PHP_EOL;
	echo '<td>'. $row['value'] . '</td>'.PHP_EOL;
	echo '<td>'. date('d-M-y H:i', $row['last_match_played']) . '</td>'.PHP_EOL;
	echo '<td>'. $row['passes'] . '</td>'.PHP_EOL;
	echo '<td>'. $row['inflicted_knockdown'] . '</td>'.PHP_EOL;
	echo '<td>'. $row['sustained_knockdown'] . '</td>'.PHP_EOL;
	echo "</tr>".PHP_EOL;
   }
?>


</tbody>

</table>


<h3>Match History</h3>

<table id="matchTable" class="tablesorter">
<thead>
<tr><th>ID</th><th title="Result (W/D/L)">R</th><th>Match Date</th><th>Coaches Team</th><th>Opposition Team</th><th>Opposition Race</th><th title="Touchdowns Scored">F</th>
<th title="Touchdowns Conceded">A</th><th title="Passes">P</th><th title="Catches">C</th><th title="Interceptions">I</th><th title="Knockdowns (ie armour breaks)">KD</th>
<th title="Inflicted KO's">KO</th><th title="Inflicted tackles">T</th><th title="Casualties (ie injuries inflicted)">Cas</th><th title="Kills inflicted">K</th>
<th title="Meters Run">MR</th><th title="Meters Passed">MP</th>
</thead>
<tbody>

<?php


 $match_sql = $conn->prepare("SELECT m.match_id
				, UNIX_TIMESTAMP(m.match_date) as match_date
				, CASE WHEN t.team_id = m.home_team_id THEN m.home_touchdown_count ELSE m.away_touchdown_count END AS td_scored
				, CASE WHEN t.team_id = m.home_team_id THEN m.away_touchdown_count ELSE m.home_touchdown_count END AS td_conceded
				, CASE WHEN m.home_touchdown_count = m.away_touchdown_count THEN 'D'
					WHEN t.team_id = m.home_team_id AND m.home_touchdown_count > m.away_touchdown_count THEN 'W'
					WHEN t.team_id = m.away_team_id AND m.away_touchdown_count > m.home_touchdown_count THEN 'W'
					ELSE 'L' END AS result_letter
				, ot.description AS opposition_team_name
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
				, t.description as own_team_name
		FROM bb_team t
		
		INNER JOIN bb_match m ON t.team_id = m.home_team_id OR t.team_id = m.away_team_id
		INNER JOIN bb_team ot ON ot.team_id = CASE WHEN  t.team_id = m.home_team_id THEN m.away_team_id ELSE m.home_team_id END
		LEFT JOIN bb_coach oc ON ot.coach_id = oc.coach_id
		INNER JOIN bb_lkp_race r ON ot.race_id = r.race_id
		LEFT JOIN bb_match_team_stats s ON m.match_id = s.match_id AND t.team_id = s.team_id
		WHERE t.coach_id = ?
 ORDER BY m.match_date DESC");

	$match_sql->bindParam(1, $_GET['coach_id'], PDO::PARAM_INT);
	$match_sql->execute();
	// this statement will potentially fetch a lot of data, but should make the page load all at once??? Maybe?
	$match_data = $match_sql->fetchAll(PDO::FETCH_ASSOC);

foreach ($match_data as $row)
   {
	echo "<tr>";
	echo '<td><a href="matchdetail.php?match_id=' . $row['match_id'] . '">'. $row['match_id'] . '</a></td>'.PHP_EOL;
	echo '<td>'. $row['result_letter'] . '</td>'.PHP_EOL;
	echo '<td>'. date('M j, Y h:i A', $row['match_date']) . '</td>'.PHP_EOL;
	echo '<td>'. $row['own_team_name'] . '</td>'.PHP_EOL;
	echo '<td><a href="team.php?team_id=' . $row['team_id'] . '">'. $row['opposition_team_name'] . '</a></td>'.PHP_EOL;
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



<p>Shade W/D/L as green/yellow/red (v1). Overall record for coach (v1) - or will that be irrelevant/misleading, coaches who have coached lightweight teams will naturally have lower win rates. Silverware/awards won (v2).</p>




<?php include_once("inc/footer.php"); ?>