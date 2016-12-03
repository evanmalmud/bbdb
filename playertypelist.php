<?php include_once("inc/header1.php");

// if you need to return header() statements, do them here. SQL connection has already been established.

include_once("inc/header2.php"); ?>

BBDB</title>

<script>
$(document).ready(function() 
    { 
        $("#playerTypeTable").tablesorter(); 
    } 
); 
</script>
<?php // include anything else you want to put in <head> here.

include_once("inc/header3.php"); ?>



<h2>Player types</h2>



<p>All player types!</p>

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
	GROUP BY pt.player_type_id
	ORDER BY CASE WHEN r.description IS NULL THEN CONCAT('zzzz',COALESCE(pt.description, pt.long_description, pt.short_description))
		ELSE CONCAT(r.description, ' ', COALESCE(pt.description, pt.long_description, pt.short_description)) END");
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





<?php include_once("inc/footer.php"); ?>