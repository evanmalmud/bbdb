<?php include_once("inc/header1.php");

// if you need to return header() statements, do them here. SQL connection has already been established.

include_once("inc/header2.php"); ?>

BBDB</title>

<script>
$(document).ready(function() 
    { 
	$("#teamTable").tablesorter(); 
	$("#playerTypeTable").tablesorter();
	$("#starPlayerTable").tablesorter();
    } 
); 
</script>
<?php // include anything else you want to put in <head> here.

include_once("inc/header3.php"); 

	$race_sql = $conn->prepare("SELECT r.* FROM bb_lkp_race r
					WHERE r.race_id = ?");

	$race_sql->bindParam(1, $_GET['race_id'], PDO::PARAM_INT);
	$race_sql->execute();

	$race = $race_sql->fetch(PDO::FETCH_ASSOC);

	if(!$race) {
		die("I cannot find that race.");
	}



echo "<h2>Race Profile - " . $race['description'] . "</h2>".PHP_EOL;
echo "<ul><li>Reroll price = " . $race['reroll_price'] . "</li></ul>".PHP_EOL;
?>

<h3>Race roster</h3>

<table id="playerTypeTable" class="tablesorter">
<thead>
<tr><th>Game</th><th>Description</th><th>MV</th><th>ST</th><th>AG</th><th>AV</th><th>Price</th><th title="Player Count - how many of these are you allowed in your lineup">PC</th><th title="Skills accessible on a normal roll">SN</th><th title="Skills accessible on a double roll">SD</th><th>Skill List</th></tr>
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
		, r.short_description AS short_race_description
		, ptst.mv, ptst.st, ptst.ag, ptst.av, ptst.price
		, pt.player_type_id
		, pt.max_quantity
		, CASE WHEN pt.max_quantity = 16 THEN '1-16' WHEN pt.max_quantity = 12 THEN '1-12' ELSE CONCAT('0-',pt.max_quantity) END as player_count
		, GROUP_CONCAT(DISTINCT s.human_desc ORDER BY s.human_desc) AS skill_list
		, GROUP_CONCAT(DISTINCT UPPER(LEFT(ns.skill_category,1)) ORDER BY ns.skill_category) AS normal_skill_access
		, GROUP_CONCAT(DISTINCT UPPER(LEFT(ds.skill_category,1)) ORDER BY ds.skill_category) AS double_skill_access
		, rs.description as ruleset_description
	FROM bb_lkp_player_type pt
	LEFT JOIN bb_lkp_player_type_stats ptst ON pt.player_type_id = ptst.player_type_id
	LEFT JOIN bb_lkp_ruleset rs ON ptst.ruleset_id = rs.ruleset_id
	LEFT JOIN bb_lkp_race r ON pt.race_id = r.race_id
	LEFT JOIN bb_lkp_player_type_skill pts ON pt.player_type_id = pts.player_type_id
	LEFT JOIN bb_lkp_skill s ON pts.skill_id = s.skill_id
	LEFT JOIN bb_lkp_player_type_skill_access nsa ON pt.player_type_id = nsa.player_type_id AND nsa.access_roll = 'N'
	LEFT JOIN bb_lkp_skill_category ns ON nsa.skill_category_id = ns.skill_category_id
	LEFT JOIN bb_lkp_player_type_skill_access dsa ON pt.player_type_id = dsa.player_type_id AND dsa.access_roll = 'D'
	LEFT JOIN bb_lkp_skill_category ds ON dsa.skill_category_id = ds.skill_category_id
	WHERE r.race_id = ?
	GROUP BY pt.player_type_id
	ORDER BY rs.description, pt.price ASC, CASE WHEN r.description IS NULL THEN CONCAT('zzzz',COALESCE(pt.description, pt.long_description, pt.short_description))
		ELSE CONCAT(r.description, ' ', COALESCE(pt.description, pt.long_description, pt.short_description)) END");
$stmt->bindParam(1, $_GET['race_id'], PDO::PARAM_INT);
$stmt->execute();
$player_types = $stmt->fetchAll(PDO::FETCH_ASSOC);

 foreach ($player_types as $row)
   {
	echo "<tr>";
	echo '<td>'. $row['ruleset_description'] . '</td>'.PHP_EOL;
	echo '<td><a href="playertype.php?player_type_id=' . $row['player_type_id'] . '">'. $row['longer_description'] . '</a></td>'.PHP_EOL;
	echo '<td>'. $row['mv'] . '</td>'.PHP_EOL;
	echo '<td>'. $row['st'] . '</td>'.PHP_EOL;
	echo '<td>'. $row['ag'] . '</td>'.PHP_EOL;
	echo '<td>'. $row['av'] . '</td>'.PHP_EOL;
	echo '<td>'. $row['price'] . '</td>'.PHP_EOL;
	echo '<td>'. $row['player_count'] . '</td>'.PHP_EOL;

	echo '<td>'. $row['normal_skill_access'] . '</td>'.PHP_EOL;
	echo '<td>'. $row['double_skill_access'] . '</td>'.PHP_EOL;
	echo '<td>'. $row['skill_list'] . '</td>'.PHP_EOL;

   }
?>
</tbody>
</table>

<p><strong>Star Player Access</strong></p>

<table id="starPlayerTable" class="tablesorter">
<thead>
<tr><th>Game</th><th>Description</th><th>MV</th><th>ST</th><th>AG</th><th>AV</th><th>Price</th><th>Skill List</th></tr>
</thead>
<tbody>
<?php
// GROUP_CONCAT should be fine but doesn't give click-through or hover-over functionality. So think of this as a short-term measure

$stmt=$conn->prepare("
	SELECT COALESCE(pt.description, pt.long_description, pt.short_description) AS simple_description
		, stats.mv, stats.st, stats.ag, stats.av, stats.price
		, pt.player_type_id
		, GROUP_CONCAT(DISTINCT s.human_desc ORDER BY s.human_desc) AS skill_list
		, r.description AS ruleset_description
	FROM bb_lkp_player_type pt
	INNER JOIN bb_lkp_star_player_race spr ON pt.player_type_id = spr.player_type_id
	LEFT JOIN bb_lkp_player_type_stats stats ON pt.player_type_id = stats.player_type_id
	LEFT JOIN bb_lkp_ruleset r ON stats.ruleset_id = r.ruleset_id
	LEFT JOIN bb_lkp_player_type_skill pts ON pt.player_type_id = pts.player_type_id
	LEFT JOIN bb_lkp_skill s ON pts.skill_id = s.skill_id
	WHERE spr.race_id = ?
	GROUP BY pt.player_type_id
	ORDER BY pt.price DESC, r.description, COALESCE(pt.description, pt.long_description, pt.short_description)");
$stmt->bindParam(1, $_GET['race_id'], PDO::PARAM_INT);
$stmt->execute();
$player_types = $stmt->fetchAll(PDO::FETCH_ASSOC);

 foreach ($player_types as $row)
   {
	echo "<tr>";
	echo '<td>'. $row['ruleset_description'] . '</td>'.PHP_EOL;
	echo '<td><a href="playertype.php?player_type_id=' . $row['player_type_id'] . '">'. $row['simple_description'] . '</a></td>'.PHP_EOL;
	echo '<td>'. $row['mv'] . '</td>'.PHP_EOL;
	echo '<td>'. $row['st'] . '</td>'.PHP_EOL;
	echo '<td>'. $row['ag'] . '</td>'.PHP_EOL;
	echo '<td>'. $row['av'] . '</td>'.PHP_EOL;
	echo '<td>'. $row['price'] . '</td>'.PHP_EOL;

	echo '<td>'. $row['skill_list'] . '</td>'.PHP_EOL;

   }

echo '</tbody>
</table>

<h3>Teams</h3>';

if (!permission_check(4)) { // Standard read privilages
	echo '<p>If you were logged on, then here you will be able to see a list of all teams of this race in your community.</p>';
}
else {
?>

<table id="teamTable" class="tablesorter">
<thead>
<tr><th>Team Name</th><th>Coach Name</th><th>Pl</th><th>W</th><th>D</th><th>L</th><th>F</th><th>A</th><th>Last Match</th><th title="Passes">Pa</th><th>KDF</th><th>KDA</th></tr>
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
		, t.passes
		, t.inflicted_knockdown
		, t.sustained_knockdown
	FROM bb_match m
	INNER JOIN bb_team t ON t.team_id = m.home_team_id OR t.team_id = m.away_team_id
	LEFT JOIN bb_coach c ON t.coach_id = c.coach_id
	WHERE t.race_id = ?
	AND m.domain_id = ?
	GROUP BY t.team_id
	ORDER BY MAX(m.match_date) DESC");
$sql->bindParam(1, $_GET['race_id'], PDO::PARAM_INT);
$sql->bindParam(2, $_SESSION['domain_id'], PDO::PARAM_INT);
$sql->execute();

   $team_data = $sql->fetchAll(PDO::FETCH_ASSOC);
   foreach ($team_data as $row)
   {
	$losses = $row['total_matches'] - $row['wins'] - $row['draws'];
	echo "<tr>";
	echo '<td><a href="team.php?team_id=' . $row['team_id'] . '">'. $row['team_name'] . '</a></td>'.PHP_EOL;
	echo '<td><a href="coach.php?coach_id=' . $row['coach_id'] . '">'. $row['coach_name'] . '</a></td>'.PHP_EOL;
	echo '<td>'. $row['total_matches'] . '</td>'.PHP_EOL;
	echo '<td>'. $row['wins'] . '</td>'.PHP_EOL;
	echo '<td>'. $row['draws'] . '</td>'.PHP_EOL;
	echo '<td>'. $row['losses']  . '</td>'.PHP_EOL;
	echo '<td>'. $row['td_scored'] . '</td>'.PHP_EOL;
	echo '<td>'. $row['td_conceded'] . '</td>'.PHP_EOL;
	echo '<td>'. date('d-M-y H:i', $row['last_match_played']) . '</td>'.PHP_EOL;
	echo '<td>'. $row['passes'] . '</td>'.PHP_EOL;
	echo '<td>'. $row['inflicted_knockdown'] . '</td>'.PHP_EOL;
	echo '<td>'. $row['sustained_knockdown'] . '</td>'.PHP_EOL;
	echo "</tr>".PHP_EOL;
   }
?>


</tbody>

</table>




<?php
} // end of "if you have read privilages"
 include_once("inc/footer.php"); ?>