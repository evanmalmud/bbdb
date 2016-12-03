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


<p>This is the <strong>characteristics</strong> page. <a href="playerliststats.php">Click here to switch to statistics view</a>.</p>
<table id="playerTable" class="tablesorter">
<thead>
<tr><th>Status</th><th>Race</th><th>Type</th><th>Name</th><th>Team</th><th title="Matches Played">P</th><th>MV</th><th>ST</th><th>AG</th><th>AV</th><th>Lv</th><th>XP</th><th>Val</th><th>Skills</th></tr>
</thead>
<tbody>
<?php
	$conn->query("SET group_concat_max_len = 99999");

   $sql = $conn->prepare("SELECT p.player_id
				, t.team_id
				, t.description AS team_name
				, r.race_id
				, r.description AS race_name
				, pt.player_type_id
				, pt.description AS player_type
				, COALESCE(pt.short_description,pt.description,pt.long_description) AS player_type_short
				, ps.player_status_id
				, ps.description AS player_status
				, p.description AS player_name
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
				, GROUP_CONCAT(DISTINCT CONCAT('<a href=\"skill.php?skill_id=', pts.skill_id, '\">', ptsk.human_desc, '</a>') ORDER BY ptsk.human_desc) AS default_skill_list
				, GROUP_CONCAT(DISTINCT CONCAT('<a href=\"skill.php?skill_id=', psk.skill_id, '\">', sk.human_desc, '</a>') ORDER BY sk.human_desc) AS earned_skill_list
		FROM bb_player p
		INNER JOIN bb_lkp_race r ON p.race_id = r.race_id
		INNER JOIN bb_team t ON p.team_id = t.team_id
		INNER JOIN bb_lkp_player_type pt ON p.player_type_id = pt.player_type_id
		INNER JOIN bb_lkp_player_status ps ON p.player_status_id = ps.player_status_id
		LEFT JOIN bb_player_skill psk ON p.player_id = psk.player_id
		LEFT JOIN bb_lkp_skill sk ON psk.skill_id = sk.skill_id
		LEFT JOIN bb_lkp_player_type_skill pts ON p.player_type_id = pts.player_type_id
		LEFT JOIN bb_lkp_skill ptsk ON pts.skill_id = ptsk.skill_id
		WHERE EXISTS (SELECT * FROM bb_player_match_stats pms
						INNER JOIN bb_match m ON pms.match_id = m.match_id
						WHERE pms.player_id = p.player_id
						AND m.domain_id = ?)
		GROUP BY p.player_id
 ORDER BY p.description ASC");
   $sql->execute(array($domain_id));
   $match_data = $sql->fetchAll(PDO::FETCH_ASSOC);
   foreach ($match_data as $row)
   {
	echo "<tr>";
	echo '<td>'. $row['player_status'] . '</td>'.PHP_EOL;
	echo '<td><a href="race.php?race_id=' . $row['race_id'] . '">'. $row['race_name'] . '</a></td>'.PHP_EOL;
	echo '<td><a href="playertype.php?player_type_id=' . $row['player_type_id'] . '">'. $row['player_type_short'] . '</a></td>'.PHP_EOL;
	echo '<td><a href="player.php?player_id=' . $row['player_id'] . '">'. $row['player_name'] . '</a></td>'.PHP_EOL;
	echo '<td><a href="team.php?team_id=' . $row['team_id'] . '">'. $row['team_name'] . '</a></td>'.PHP_EOL;
	echo '<td>'. $row['match_played'] . '</td>'.PHP_EOL;
	echo '<td>'. $row['mv'] . '</td>'.PHP_EOL;
	echo '<td>'. $row['st'] . '</td>'.PHP_EOL;
	echo '<td>'. $row['ag'] . '</td>'.PHP_EOL;
	echo '<td>'. $row['av'] . '</td>'.PHP_EOL;
	echo '<td>'. $row['level'] . '</td>'.PHP_EOL;
	echo '<td>'. $row['experience'] . '</td>'.PHP_EOL;
	echo '<td>'. $row['current_value'] . '</td>'.PHP_EOL;
	echo '<td>'. $row['default_skill_list'] . " | " . $row['earned_skill_list'] . '</td>'.PHP_EOL;
	echo "</tr>".PHP_EOL;
   }
?>
</tbody>
</table>



<?php include_once("inc/footer.php"); ?>