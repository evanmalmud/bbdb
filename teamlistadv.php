<?php include_once("inc/header1.php");

// if you need to return header() statements, do them here. SQL connection has already been established.

include_once("inc/header2.php"); ?>

BBDB</title>

<script>
$(document).ready(function() 
    { 
        $("#teamTable").tablesorter(); 
    } 
); 
    
</script>
<?php // include anything else you want to put in <head> here.

if ((!permission_check(4)) || (!isset($_SESSION['domain_id']))) { // standard read privilages
	include_once("inc/no_permission.php");
}
include_once("inc/header3.php"); 
$domain_id = $_SESSION['domain_id'];
?>



<h2>Teams</h2>


<table id="teamTable" class="tablesorter">
<thead>
<tr><th>Team Name</th><th>Race</th><th>Coach</th><th>Value</th><th title="Matches played">Pl</th><th>W</th><th>D</th><th>L</th><th title="Points (win=3, draw=1)">Pts</th><th title="Touchdowns Scored">F</th>
	<th title="Touchdowns Conceded">A</th><th title="Passes">Ps</th>
<th title="Interceptions">I</th>
<th title="Knockdowns caused">KDF</th><th title="Knockdowns suffered">KDA</th>
<th title="KO's caused">KOF</th><th title="KO's suffered">KOA</th>
<th title="Casualties caused">CASF</th><th title="Casualties suffered">CASA</th>
<th title="Kills inflicted">KF</th><th title="Kills suffered">KA</th>
<th title="Meters Run">MR</th><th title="Meters Passed">MP</th></tr>
</thead>
<tbody>
<?php
   $sql = $conn->prepare("SELECT t.team_id
				, t.description AS team_name
				, t.race_id
				, r.description AS race_name
				, t.coach_id
				, COALESCE(c.description, '???') AS coach_name
				, t.str_logo
				, t.motto
				, t.background
				, t.value
				, t.rerolls
				, t.fan_factor
				, t.cheerleaders
				, t.apothecary
				, t.balms
				, t.cash
				, t.assistant_coaches
				, t.meters_run
				, t.meters_pass
				, t.touchdowns
				, t.sustained_touchdowns
				, t.inflicted_injury
				, t.sustained_injury
				, t.wins
				, t.draws
				, t.losses
				, t.passes
				, t.inflicted_ko
				, t.sustained_ko
				, t.interceptions
				, t.inflicted_dead
				, t.sustained_dead
				, t.inflicted_knockdown
				, t.sustained_knockdown
		FROM bb_team t
		INNER JOIN bb_lkp_race r ON t.race_id = r.race_id
		LEFT JOIN bb_coach c ON t.coach_id = c.coach_id
		WHERE EXISTS (SELECT * FROM bb_match m WHERE t.team_id IN (m.home_team_id, m.away_team_id) AND m.domain_id = ?)
 ORDER BY t.description ASC");
   $sql->execute(array($domain_id));
   $match_data = $sql->fetchAll(PDO::FETCH_ASSOC);
   foreach ($match_data as $row)
   {
	$total_played = $row['wins']+$row['draws']+$row['losses'];
	$total_pts = ($row['wins']*3)+($row['draws']);
	echo "<tr>";
	echo '<td><a href="team.php?team_id=' . $row['team_id'] . '">'. $row['team_name'] . '</a></td>'.PHP_EOL;
	echo '<td><a href="race.php?race_id=' . $row['race_id'] . '">'. $row['race_name'] . '</a></td>'.PHP_EOL;
	if ($row['coach_name']=="???") 
		{ echo '<td>???</td>'.PHP_EOL; }
	else
		{ echo '<td><a href="coach.php?coach_id=' . $row['coach_id'] . '">'. $row['coach_name'] . '</a></td>'.PHP_EOL; }
	echo '<td>'. $row['value'] . '</td>'.PHP_EOL;
	echo '<td>'. $total_played  . '</td>'.PHP_EOL;
	echo '<td>'. $row['wins'] . '</td>'.PHP_EOL;
	echo '<td>'. $row['draws'] . '</td>'.PHP_EOL;
	echo '<td>'. $row['losses'] . '</td>'.PHP_EOL;
	echo '<td>'. $total_pts  . '</td>'.PHP_EOL;
	echo '<td>'. $row['touchdowns'] . '</td>'.PHP_EOL;
	echo '<td>'. $row['sustained_touchdowns'] . '</td>'.PHP_EOL;
	echo '<td>'. $row['passes'] . '</td>'.PHP_EOL;
	echo '<td>'. $row['interceptions'] . '</td>'.PHP_EOL;
	echo '<td>'. $row['inflicted_knockdown'] . '</td>'.PHP_EOL;
	echo '<td>'. $row['sustained_knockdown'] . '</td>'.PHP_EOL;
	echo '<td>'. $row['inflicted_ko'] . '</td>'.PHP_EOL;
	echo '<td>'. $row['sustained_ko'] . '</td>'.PHP_EOL;
	echo '<td>'. $row['inflicted_injury'] . '</td>'.PHP_EOL;
	echo '<td>'. $row['sustained_injury'] . '</td>'.PHP_EOL;
	echo '<td>'. $row['inflicted_dead'] . '</td>'.PHP_EOL;
	echo '<td>'. $row['sustained_dead'] . '</td>'.PHP_EOL;
	echo '<td>'. $row['meters_run'] . '</td>'.PHP_EOL;
	echo '<td>'. $row['meters_pass'] . '</td>'.PHP_EOL;
	echo "</tr>".PHP_EOL;
   }
?>
</tbody>
</table>


<p>Disclaimer - these stats aim to be as accurate as possible as of the end of the team's last match. Hiring/firing since then will not be included, neither is cash earned as a result of their last match.</p>


<?php include_once("inc/footer.php"); ?>