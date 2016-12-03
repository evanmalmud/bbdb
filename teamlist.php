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



<p>For a more detailed view of teams and their stats, try the <a href="teamlistadv.php">Advanced Team List</a>.</p>

<table id="teamTable" class="tablesorter">
<thead>
<tr><th>Team Name</th><th>Race</th><th>Coach</th><th>Value</th><th title="Team re-rolls">Rerolls</th><th title="Total SPP on current team">SPP</th><th title="Fan factor">FF</th>
<th title="Assistant Coaches">AC</th><th title="Cheerleaders">CL</th><th title="Apothecary">AP</th><th>Cash</th></tr>
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
		FROM bb_team t
		INNER JOIN bb_lkp_race r ON t.race_id = r.race_id
		LEFT JOIN bb_coach c ON t.coach_id = c.coach_id
		WHERE EXISTS (SELECT * FROM bb_match m WHERE t.team_id IN (m.home_team_id, m.away_team_id) AND m.domain_id = ?)
 ORDER BY t.description ASC");
   $sql->execute(array($domain_id));
   $match_data = $sql->fetchAll(PDO::FETCH_ASSOC);
   foreach ($match_data as $row)
   {
	echo "<tr>";
	echo '<td><a href="team.php?team_id=' . $row['team_id'] . '">'. $row['team_name'] . '</a></td>'.PHP_EOL;
	echo '<td><a href="race.php?race_id=' . $row['race_id'] . '">'. $row['race_name'] . '</a></td>'.PHP_EOL;
	if ($row['coach_name']=="???") 
		{ echo '<td>???</td>'.PHP_EOL; }
	else
		{ echo '<td><a href="coach.php?coach_id=' . $row['coach_id'] . '">'. $row['coach_name'] . '</a></td>'.PHP_EOL; }
	echo '<td>'. $row['value'] . '</td>'.PHP_EOL;
	echo '<td>'. $row['rerolls'] . '</td>'.PHP_EOL;
	echo '<td>???</td>'.PHP_EOL;
	echo '<td>'. $row['fan_factor'] . '</td>'.PHP_EOL;
	echo '<td>'. $row['assistant_coaches'] . '</td>'.PHP_EOL;
	echo '<td>'. $row['cheerleaders'] . '</td>'.PHP_EOL;
	echo '<td>'. $row['apothecary'] . '</td>'.PHP_EOL;
	echo '<td>'. $row['cash'] . '</td>'.PHP_EOL;
	echo "</tr>".PHP_EOL;
   }
?>
</tbody>
</table>


<p>Disclaimer - these stats aim to be as accurate as possible as of the end of the team's last match. Hiring/firing since then will not be included, neither is cash earned as a result of their last match.</p>


<p>To do - ensure that the statistics are as up to date as possible (v1) - currently the stats are just from the first game that was uploaded featuring that team.</p>

<?php include_once("inc/footer.php"); ?>