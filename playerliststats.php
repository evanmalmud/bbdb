<?php include_once("inc/header1.php");

// if you need to return header() statements, do them here. SQL connection has already been established.

include_once("inc/header2.php"); ?>

BBDB</title>

<script>
$(document).ready(function() 
    { 
        $("#playerTable").tablesorter(); 
    } 
); 
    
</script>
<?php // include anything else you want to put in <head> here.

include_once("inc/header3.php"); 

if ((!permission_check(4)) || (!isset($_SESSION['domain_id']))) { // standard read privilages
	echo '<h2>Page not found</h2>';
	echo '<p>Page not found, or you lack the necessary permissions.</p>';
	include_once("../inc/footer.php");
	die();
}
$domain_id = $_SESSION['domain_id'];
?>


<h2>Players</h2>


<p>This is the <strong>statistics</strong> page. <a href="playerlist.php">Click here to switch to characteristics view</a>.</p>
<table id="playerTable" class="tablesorter">
<thead>
<tr><th title="Player Status">St</th><th>Race</th><th>Type</th><th>Name</th><th>Team</th><th>XP</th>
<th title="Played">Pl</th><th>MVP</th><th title="Touchdowns Scored">TD</th><th title="Passes">PS</th><th title="Interceptions">I</th><th title="Casualtied Inflicted">CAS</th>
<th title="Knockdowns Inflicted">KDF</th><th title="Knockdowns Recieved">KDA</th><th title="Knockouts Inflicted">KOF</th><th title="Knockouts Recieved">KOA</th>
<th title="Injuries Recieved">INJ</th><th title="Kills">K</th><th title="Meters Run">MR</th><th title="Meters Passed">MP</th>
</tr>
</thead>
<tbody>
<?php

   $sql = $conn->prepare("SELECT p.player_id
				, p.experience
				, t.team_id
				, t.description AS team_name
				, r.race_id
				, r.description AS race_name
				, r.short_description AS race_short_name
				, pt.player_type_id
				, pt.description AS player_type
				, COALESCE(pt.short_description,pt.description) AS player_type_short
				, ps.player_status_id
				, ps.short_description AS player_status_short
				, ps.description AS player_status_long
				, p.description AS player_name
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
		FROM bb_player p
		INNER JOIN bb_lkp_race r ON p.race_id = r.race_id
		INNER JOIN bb_team t ON p.team_id = t.team_id
		INNER JOIN bb_lkp_player_type pt ON p.player_type_id = pt.player_type_id
		INNER JOIN bb_lkp_player_status ps ON p.player_status_id = ps.player_status_id
		WHERE EXISTS (SELECT * FROM bb_player_match_stats pms
						INNER JOIN bb_match m ON pms.match_id = m.match_id
						WHERE pms.player_id = p.player_id
						AND m.domain_id = ?)
UNION ALL
SELECT 				NULL AS player_id
				, p.experience
				, NULL AS team_id
				, '***STAR PLAYER***' AS team_name
				, NULL AS race_id
				, '***STAR PLAYER***' AS race_name
				, NULL AS race_short_name
				, pt.player_type_id
				, pt.description AS player_type
				, COALESCE(pt.short_description,pt.description) AS player_type_short
				, ps.player_status_id AS player_status_id
				, ps.short_description AS player_status_short
				, ps.description AS player_status_long
				, pt.description AS player_name
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
				, pt.price AS current_value
				, NULL AS squad_number
				, p.sustained_interception
				, p.sustained_knockdown
				, p.sustained_tackles
				, p.sustained_ko
				, p.sustained_stun
				, p.sustained_injury
				, p.sustained_dead
				, p.blocks_attempted
				, p.dodges_made
		FROM bb_stat_star_player p
		INNER JOIN bb_lkp_player_type pt ON p.player_type_id = pt.player_type_id
		INNER JOIN bb_lkp_player_status ps ON 4 = ps.player_status_id
		ORDER BY player_name
	");
   $sql->execute(array($domain_id));
   $match_data = $sql->fetchAll(PDO::FETCH_ASSOC);
   foreach ($match_data as $row)
   {
	echo "<tr>";
	echo '<td title = "' . $row['player_status_long'] . '">'. $row['player_status_short'] . '</td>'.PHP_EOL;
	if ($row['player_status_id']==4) { // star players to be treated differently
		echo '<td>STAR</td>'.PHP_EOL;
		echo '<td>STAR</td>'.PHP_EOL;
		echo '<td><a href="playertype.php?player_type_id=' . $row['player_type_id'] . '">'. $row['player_name'] . '</a></td>'.PHP_EOL;
		echo '<td>STAR</td>'.PHP_EOL;
	}
	else { // if not a star player...
		echo '<td><a title = "' . $row['race_name'] . '" href="race.php?race_id=' . $row['race_id'] . '">'. $row['race_short_name'] . '</a></td>'.PHP_EOL;
		echo '<td><a href="playertype.php?player_type_id=' . $row['player_type_id'] . '">'. $row['player_type_short'] . '</a></td>'.PHP_EOL;
		echo '<td><a href="player.php?player_id=' . $row['player_id'] . '">'. $row['player_name'] . '</a></td>'.PHP_EOL;
		echo '<td><a href="team.php?team_id=' . $row['team_id'] . '">'. $row['team_name'] . '</a></td>'.PHP_EOL;
	}
	echo '<td>'. $row['experience'] . '</td>'.PHP_EOL;
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
</tbody>
</table>



<?php include_once("inc/footer.php"); ?>