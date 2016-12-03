<?php include_once("inc/header1.php");

// if you need to return header() statements, do them here. SQL connection has already been established.

include_once("inc/header2.php"); ?>

BBDB</title>
<script>
$(document).ready(function() 
    { 
        $("#playerTypeTable").tablesorter();
        $("#playerTable").tablesorter(); 
    } 
); 
</script>

<?php // include anything else you want to put in <head> here.

include_once("inc/header3.php"); 

$check_stmt = $conn->prepare("SELECT * FROM bb_lkp_player_supertype WHERE player_supertype_id = ?");
$check_stmt->execute(array($_GET['player_supertype_id']));

$check_stmt_data = $check_stmt->fetchAll(PDO::FETCH_ASSOC);

if (count($check_stmt_data)==0) {
	echo "<p>Invalid input detected.</p>";
	include_once("inc/footer.php");
	die();
}

?>

<h2>Supertype - <?php echo $check_stmt_data[0]['description']; ?></h2>

<h3>Player type list</h3>

<table id="playerTypeTable" class="tablesorter">
<thead>
<tr><th>Description</th><th>Race</th><th>MV</th><th>ST</th><th>AG</th><th>AV</th><th>Price</th><th title="Player Count - how many of these are you allowed in your lineup">PC</th><th title="Skills accessible on a normal roll">SN</th><th title="Skills accessible on a double roll">SD</th><th>Skill List</th></tr>
</thead>
<tbody>
<?php
// GROUP_CONCAT should be fine but doesn't give click-through or hover-over functionality. So think of this as a short-term measure

$stmt=$conn->prepare("
	SELECT COALESCE(pt.description, pt.long_description, pt.short_description) AS simple_description
		, COALESCE(r.description, 'Star Player') AS race_name
		, CASE WHEN r.description IS NULL THEN COALESCE(pt.description, pt.long_description, pt.short_description)
			ELSE CONCAT(r.description, ' ', COALESCE(pt.description, pt.long_description, pt.short_description)) END AS longer_description
		, r.race_id
		, CASE WHEN pt.race_id = 0 THEN 'STAR' ELSE r.short_description END AS short_race_description
		, zzz.mv, zzz.st, zzz.ag, zzz.av, zzz.price
		, pt.player_type_id
		, pt.max_quantity
		, CASE WHEN pt.max_quantity = 16 THEN '1-16' WHEN pt.max_quantity = 12 THEN '1-12' ELSE CONCAT('0-',pt.max_quantity) END as player_count
		, GROUP_CONCAT(DISTINCT CONCAT('<a href=\"skill.php?skill_id=', pts.skill_id, '\">', s.human_desc, '</a>') ORDER BY s.human_desc) AS skill_list
		, GROUP_CONCAT(DISTINCT CONCAT('<a href=\"standingdata.php?datatypeid=2&categoryid=', ns.skill_category_id, '\">', ns.skill_category, '</a>') ORDER BY ns.skill_category) as normal_skill_two
		, GROUP_CONCAT(DISTINCT UPPER(LEFT(ns.skill_category,1)) ORDER BY ns.skill_category) AS normal_skill_access
		, GROUP_CONCAT(DISTINCT UPPER(LEFT(ds.skill_category,1)) ORDER BY ds.skill_category) AS double_skill_access
	FROM bb_lkp_player_type pt
	LEFT JOIN bb_lkp_player_type_stats zzz ON pt.player_type_id = zzz.player_type_id AND zzz.ruleset_id = 1
	LEFT JOIN bb_lkp_race r ON pt.race_id = r.race_id
	LEFT JOIN bb_lkp_player_type_skill pts ON pt.player_type_id = pts.player_type_id
	LEFT JOIN bb_lkp_skill s ON pts.skill_id = s.skill_id
	LEFT JOIN bb_lkp_player_type_skill_access nsa ON pt.player_type_id = nsa.player_type_id AND nsa.access_roll = 'N'
	LEFT JOIN bb_lkp_skill_category ns ON nsa.skill_category_id = ns.skill_category_id
	LEFT JOIN bb_lkp_player_type_skill_access dsa ON pt.player_type_id = dsa.player_type_id AND dsa.access_roll = 'D'
	LEFT JOIN bb_lkp_skill_category ds ON dsa.skill_category_id = ds.skill_category_id
	WHERE pt.player_supertype_id = ?
	GROUP BY pt.player_type_id
	ORDER BY CASE WHEN r.description IS NULL THEN CONCAT('zzzz',COALESCE(pt.description, pt.long_description, pt.short_description))
		ELSE CONCAT(r.description, ' ', COALESCE(pt.description, pt.long_description, pt.short_description)) END");
	
   $stmt->bindParam(1, $_GET['player_supertype_id'], PDO::PARAM_INT);
$stmt->execute();
$player_types = $stmt->fetchAll(PDO::FETCH_ASSOC);

 foreach ($player_types as $row)
   {
	echo "<tr>";
	echo '<td><a href="playertype.php?player_type_id=' . $row['player_type_id'] . '">'. $row['longer_description'] . '</a></td>'.PHP_EOL;
	if ($row['race_id'] > 0)
		{ echo '<td><a href="race.php?race_id=' . $row['race_id'] . '">'. $row['short_race_description'] . '</a></td>'.PHP_EOL;}
	else	{ echo '<td>' . $row['short_race_description'] . '</td>'.PHP_EOL;}
	echo '<td>'. $row['mv'] . '</td>'.PHP_EOL;
	echo '<td>'. $row['st'] . '</td>'.PHP_EOL;
	echo '<td>'. $row['ag'] . '</td>'.PHP_EOL;
	echo '<td>'. $row['av'] . '</td>'.PHP_EOL;
	echo '<td>'. $row['price'] . '</td>'.PHP_EOL;
	echo '<td>'. $row['player_count'] . '</td>'.PHP_EOL;

	echo '<td>'. $row['normal_skill_access'] . '</td>'.PHP_EOL;
	echo '<td>'. $row['double_skill_access'] . '</td>'.PHP_EOL;
	echo '<td>'. $row['skill_list'] . '</td></tr>'.PHP_EOL;

   }
?>
</tbody>
</table>


<h3>Player List</h3>

<?php
	if(!permission_check(4)) { // ie - lack basic read rights
		echo "<p>If you were signed in, you would see all players of this type in your community.</p>".PHP_EOL;
	}
	else {
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
				, p.mvp
				, p.passes
				, p.catches
				, p.interceptions
				, p.touchdowns
				, p.inflicted_knockdown
				, p.inflicted_injury
				, p.sustained_knockdown
				, p.sustained_injury
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
		WHERE pt.player_supertype_id = ?
		AND EXISTS (SELECT * FROM bb_match m WHERE t.team_id IN (m.home_team_id, m.away_team_id) AND m.domain_id = ?)
		GROUP BY p.player_id
 ORDER BY p.description ASC");
   $sql->bindParam(1, $_GET['player_supertype_id'], PDO::PARAM_INT);
   $sql->bindParam(2, $_SESSION['domain_id'], PDO::PARAM_INT);
   $sql->execute();
   $player_data = $sql->fetchAll(PDO::FETCH_ASSOC);
   
   if  (count($player_data)==0) {
	   echo "<p>There are no players of this supertype in your community.</p>".PHP_EOL;
   }
   else {
?>

<table id="playerTable" class="tablesorter">
<thead>
<tr><th>Status</th><th>Race</th><th>Type</th><th>Name</th><th>Team</th><th title="Matches Played">P</th><th>MV</th><th>ST</th><th>AG</th><th>AV</th><th>Lv</th><th>XP</th><th>Val</th>
<th>MVP</th><th title="Touchdowns Scored">TD</th><th title="Passes">PS</th><th title="Interceptions">I</th><th title="Casualtied Inflicted">CAS</th>
<th title="Knockdowns Inflicted">KDF</th><th title="Knockdowns Recieved">KDA</th>
<th>Skills</th></tr>
</thead>
<tbody>

<?php

   foreach ($player_data as $row)
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
	echo '<td>'. $row['mvp'] . '</td>'.PHP_EOL;
	echo '<td>'. $row['touchdowns'] . '</td>'.PHP_EOL;
	echo '<td>'. $row['passes'] . '</td>'.PHP_EOL;
	echo '<td>'. $row['interceptions'] . '</td>'.PHP_EOL;
	echo '<td>'. $row['inflicted_injury'] . '</td>'.PHP_EOL;
	echo '<td>'. $row['inflicted_knockdown'] . '</td>'.PHP_EOL;
	echo '<td>'. $row['sustained_knockdown'] . '</td>'.PHP_EOL;
	echo '<td>'. $row['default_skill_list'] . " | " . $row['earned_skill_list'] . '</td>'.PHP_EOL;
	echo "</tr>".PHP_EOL;
   }
?>
</tbody>
</table>

<?php
	} // end of if  count($player_data)> 0
	} // end of "if you are logged in..."
?>	


<?php include_once("inc/footer.php"); ?>
