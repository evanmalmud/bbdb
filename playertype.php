<?php include_once("inc/header1.php");

// if you need to return header() statements, do them here. SQL connection has already been established.

include_once("inc/header2.php"); ?>

BBDB</title>

<script>
$(document).ready(function() 
    { 
        $("#matchTable").tablesorter();
    } 
); 
</script>
<?php // include anything else you want to put in <head> here.

include_once("inc/header3.php"); 

// Get player_type checked, and find out if it is a star player or not. We load a different page depending on the answer.

$check_stmt = $conn->prepare("SELECT COALESCE(pt.description, pt.long_description) AS player_type
				, COALESCE(r.description, '***STAR PLAYER***') AS race_name
				, pts.mv, pts.st, pts.ag, pts.av, pts.price
				, pt.player_type_id
				, pt.race_id
				, CASE WHEN pt.race_id = 0 THEN NULL WHEN pt.max_quantity = 16 THEN '1-16' WHEN pt.max_quantity = 12 THEN '1-12' ELSE CONCAT('0-',pt.max_quantity) END as player_count
				, GROUP_CONCAT(DISTINCT CONCAT('<a href=\"skill.php?skill_id=', s1.skill_id, '\">', s1.human_desc, '</a>') ORDER BY s1.human_desc) AS default_skill_list
				, GROUP_CONCAT(DISTINCT CONCAT('<a href=\"standingdata.php?datatypeid=2&categoryid=', ns.skill_category_id, '\">', ns.skill_category, '</a>') ORDER BY ns.skill_category) as normal_skill
				, GROUP_CONCAT(DISTINCT CONCAT('<a href=\"standingdata.php?datatypeid=2&categoryid=', ds.skill_category_id, '\">', ds.skill_category, '</a>') ORDER BY ds.skill_category) as double_skill
				, psup.player_supertype_id
				, psup.description AS 'player_supertype'
					FROM bb_lkp_player_type pt
					LEFT JOIN bb_lkp_race r ON pt.race_id = r.race_id
					LEFT JOIN bb_lkp_player_type_stats pts ON pt.player_type_id = pts.player_type_id AND pts.ruleset_id = 1
					LEFT JOIN bb_lkp_player_type_skill ptsk ON pt.player_type_id = ptsk.player_type_id
					LEFT JOIN bb_lkp_skill s1 ON ptsk.skill_id = s1.skill_id

					LEFT JOIN bb_lkp_player_type_skill_access ptns ON pt.player_type_id = ptns.player_type_id AND ptns.access_roll='N'
					LEFT JOIN bb_lkp_skill_category ns ON ptns.skill_category_id = ns.skill_category_id
					LEFT JOIN bb_lkp_player_type_skill_access ptds ON pt.player_type_id = ptds.player_type_id AND ptds.access_roll='D'
					LEFT JOIN bb_lkp_skill_category ds ON ptds.skill_category_id = ds.skill_category_id
					LEFT JOIN bb_lkp_player_supertype psup ON pt.player_supertype_id = psup.player_supertype_id
					WHERE pt.player_type_id = ?
				GROUP BY pt.player_type_id");

	$check_stmt->bindParam(1, $_GET['player_type_id'], PDO::PARAM_INT);
	$check_stmt->execute();

	$player_type = $check_stmt->fetch(PDO::FETCH_ASSOC);

	if(!$player_type) {
		die("I cannot find that player type.");
	}

// Star Player will have player stats, will list the races that he can play for, and have a match history/overall stats
// Normal page will have player stats (and also skill access), and a list of all the players who have filled in that position.

echo '<h2>' . $player_type['player_type'] . '</h2>'.PHP_EOL;

?>





<table>
<?php
	echo '<tr><td colspan="6">' . $player_type['player_type'] . '</td></tr>'.PHP_EOL;
	echo '<tr><td colspan="6">' . $player_type['race_name'] . '</td></tr>'.PHP_EOL;
	echo '<tr><td>MA</td><td>ST</td><td>AG</td><td>AV</td><td>Price</td><td>Qty</td></tr>'.PHP_EOL;
	echo '<tr><td>' . $player_type['mv'] . '</td><td>' . $player_type['st'] . '</td><td>' . $player_type['ag'] . '</td><td>' . $player_type['av'] . '</td><td>' . $player_type['price'] . '</td><td>' . $player_type['player_count'] . '</td></tr>'.PHP_EOL;
	if (!is_null($player_type['player_supertype'])) {
		echo '<tr><td colspan="2">Supertype</td><td colspan="4"><a href="playersupertype.php?player_supertype_id=' . $player_type['player_supertype_id'];
		echo '">' . $player_type['player_supertype'] . '</a></td></tr>'.PHP_EOL;
	}
	
	echo '<tr><td>Skills</td><td colspan="5">' . $player_type['default_skill_list'] . '</td></tr>'.PHP_EOL;
	
	echo '<tr><td colspan="2" title="Skill categories available with a normal roll">Normal Skills</td><td colspan="4">' . $player_type['normal_skill'] . '</td></tr>'.PHP_EOL;
	echo '<tr><td colspan="2" title="Skill categories available with a double roll">Double Skills</td><td colspan="4">' . $player_type['double_skill'] . '</td></tr>'.PHP_EOL;

?>
</table>

<?php
	if ($player_type['race_id']==0) { include_once("playertypestar.php"); }
	else { include_once("playertypenormal.php"); }



include_once("inc/footer.php"); ?>