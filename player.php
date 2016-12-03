<?php include_once("inc/header1.php");

// if you need to return header() statements, do them here. SQL connection has already been established.

include_once("inc/header2.php"); ?>

BBDB - player</title>

<script>
$(document).ready(function() 
    { 
        $("#matchTable").tablesorter();
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

	$check_stmt = $conn->prepare("SELECT p.*, ps.description as player_status 
				, COALESCE(pt.description, pt.long_description) AS player_type
				, t.description AS team_name
				, GROUP_CONCAT(DISTINCT CONCAT('<a href=\"skill.php?skill_id=', pts.skill_id, '\">', ptsk.human_desc, '</a>') ORDER BY ptsk.human_desc) AS default_skill_list
				, GROUP_CONCAT(DISTINCT CONCAT('<a href=\"skill.php?skill_id=', psk.skill_id, '\">', sk.human_desc, '</a>') ORDER BY sk.human_desc) AS earned_skill_list
					FROM bb_player p
					INNER JOIN bb_lkp_player_status ps ON p.player_status_id = ps.player_status_id
					INNER JOIN bb_lkp_player_type pt ON p.player_type_id = pt.player_type_id
					INNER JOIN bb_team t ON p.team_id = t.team_id
					LEFT JOIN bb_player_skill psk ON p.player_id = psk.player_id
					LEFT JOIN bb_lkp_skill sk ON psk.skill_id = sk.skill_id
					LEFT JOIN bb_lkp_player_type_skill pts ON p.player_type_id = pts.player_type_id
					LEFT JOIN bb_lkp_skill ptsk ON pts.skill_id = ptsk.skill_id
					WHERE p.player_id = ?");

	$check_stmt->bindParam(1, $_GET['player_id'], PDO::PARAM_INT);
	$check_stmt->execute();

	$player = $check_stmt->fetch(PDO::FETCH_ASSOC);

	if(!$player) {
		die("I cannot find that player.");
	}

	echo '<h2>Player Profile - ' . $player['description'] . ' - <a href="playertype.php?player_type_id=' . $player['player_type_id'] . '">' . $player['player_type'] . '</a> - ';
	echo $player['player_status'] . ' - <a href="team.php?team_id=' . $player['team_id'] . '">' . $player['team_name'] . '</a>';
	echo '</h2>'.PHP_EOL;
?>



<h3>Characteristics</h3>

<table class="stat_table"><tr><th>MV</th><th>ST</th><th>AG</th><th>AV</th><th>XP</th><th>Level</th><th>Value</th><th>Basic Skills</th><th>Promotion Skills</th>
</tr><tr>
<?php
	echo '<td>' . $player['mv'] . '</td>' . PHP_EOL;
	echo '<td>' . $player['st'] . '</td>' . PHP_EOL;
	echo '<td>' . $player['ag'] . '</td>' . PHP_EOL;
	echo '<td>' . $player['av'] . '</td>' . PHP_EOL;
	echo '<td>' . $player['experience'] . '</td>' . PHP_EOL;
	echo '<td>' . $player['level'] . '</td>' . PHP_EOL;
	echo '<td>' . $player['current_value'] . '</td>' . PHP_EOL;
	echo '<td>' . $player['default_skill_list'] . '</td>' . PHP_EOL;
	echo '<td>' . $player['earned_skill_list'] . '</td>' . PHP_EOL;

?>
</tr></table>

<h3>Statistics</h3>

<p>Played in <strong><?php echo $player['match_played'];?></strong> matches, winning <strong><?php echo $player['mvp'];?></strong> MVP awards.
<table class="stat_table"><tr><th>FOR:</th>
<?php

	echo '<td>'. $player['touchdowns'] . '</td>'.PHP_EOL;
	echo '<td>'. $player['passes'] . '</td>'.PHP_EOL;
	echo '<td>'. $player['catches'] . '</td>'.PHP_EOL;
	echo '<td>'. $player['interceptions'] . '</td>'.PHP_EOL;
	echo '<td>'. $player['meters_run'] . '</td>'.PHP_EOL;
	echo '<td>'. $player['meters_pass'] . '</td>'.PHP_EOL;
	echo '<td>'. $player['inflicted_knockdown'] . '</td>'.PHP_EOL;
	echo '<td>'. $player['inflicted_tackles'] . '</td>'.PHP_EOL;
	echo '<td>'. $player['inflicted_ko'] . '</td>'.PHP_EOL;
	echo '<td>'. $player['inflicted_injury'] . '</td>'.PHP_EOL;
	echo '<td>'. $player['inflicted_dead'] . '</td>'.PHP_EOL;

?>

</tr><tr><th></th><th title="Touchdowns">TD</th><th title="Passes">PS</th><th title="Catches">C</th><th title="Interceptions">I</th><th title="Meters Run">MR</th><th title="Meters Passed">MP</th>
<th title="Knockdowns">KD</th><th title="Tackles">TK</th><th title="KOs">KO</th>
<th title="Casualties">Cas</th><th title="Killed">K</th></tr>
<tr><th>AGAINST:</th>

<?php

	echo '<td></td>'.PHP_EOL;
	echo '<td></td>'.PHP_EOL;
	echo '<td></td>'.PHP_EOL;
	echo '<td>'. $player['sustained_interception'] . '</td>'.PHP_EOL;
	echo '<td></td>'.PHP_EOL;
	echo '<td></td>'.PHP_EOL;
	echo '<td>'. $player['sustained_knockdown'] . '</td>'.PHP_EOL;
	echo '<td>'. $player['sustained_tackles'] . '</td>'.PHP_EOL;
	echo '<td>'. $player['sustained_ko'] . '</td>'.PHP_EOL;
	echo '<td>'. $player['sustained_injury'] . '</td>'.PHP_EOL;
	echo '<td>'. $player['sustained_dead'] . '</td>'.PHP_EOL;

?>
</tr></table>

<h3>Awards</h3>

<?php

if(isset($_SESSION['domain_id'])) {
	$domain_id = $_SESSION['domain_id'];
}
else {
	$domain_id = 2; // assume RT! This is stupid. Thinking domain_id should be in bb_player_award.
}

$sql = $conn->prepare("
SELECT t.description AS award_title, l.position, CONCAT(' (',c.description,')') AS competition_name
		, CASE WHEN pa.shared = 1 THEN '*' ELSE '' END AS shared_bit
		, t.measured_value
FROM bb_player_award pa
INNER JOIN bb_lkp_award a ON pa.award_id = a.award_id
INNER JOIN bb_lkp_award_type t ON a.award_type_id = t.award_type_id
INNER JOIN bb_lkp_award_level l ON a.award_level_id = l.award_level_id
LEFT JOIN bb_competition c ON pa.competition_id = c.competition_id AND c.domain_id = ?
WHERE pa.player_id = ?");
$sql->bindParam(1, $domain_id, PDO::PARAM_INT);
$sql->bindParam(2, $_GET['player_id'], PDO::PARAM_INT);

$sql->execute();


   $award_list = $sql->fetchAll(PDO::FETCH_ASSOC);
   if (count($award_list)==0) {
	   echo '<p>This player has no awards. What an unremarkable specimen.</p>';
   }
   else {
		echo '<ul>';
		foreach ($award_list as $award) {
			echo '<li>';
			echo '<a title="' . $award['measured_value'] . '">';
			echo  $award['award_title'] . $award['competition_name'] . ' - ' . $award['position'] . $award['shared_bit'];
			echo '</a></li>';
		}
		echo '</ul>';
   }
// $row['award_title'] . $row['competition_name'] ' - ' . $row['position'];

?>

<h3>Match History</h3>

<table id="matchTable" class="tablesorter">

<thead><tr>
<th title="Match ID">ID</th><th title="Match Date">Date</th><th title="Opponent Race">Race</th><th title="Opponent Team">Team</th>
<th title="Played">Pl</th><th>MVP</th><th title="Touchdowns Scored">TD</th><th title="Passes">PS</th><th title="Interceptions">I</th><th title="Casualtied Inflicted">CAS</th>
<th title="Knockdowns Inflicted">KDF</th><th title="Knockdowns Recieved">KDA</th><th title="Knockouts Inflicted">KOF</th><th title="Knockouts Recieved">KOA</th>
<th title="Injuries Recieved">INJ</th><th title="Kills">K</th><th title="Catches">C</th><th title="Meters Run">MR</th><th title="Meters Passed">MP</th>
<th title="Injuries Sustained">IS</th><th title="Skills Debuted">Sk</th>
</tr></thead><tbody>
<?php



$sql = $conn->prepare("SELECT	m.match_id
				, UNIX_TIMESTAMP(m.match_date) as match_date
				, t.team_id
				, t.description AS team_name
				, r.race_id
				, r.short_description AS race_name_short
				, r.description AS race_name
				, pms.match_played
				, pms.mvp
				, pms.passes
				, pms.catches
				, pms.interceptions
				, pms.touchdowns
				, pms.inflicted_knockdown
				, pms.inflicted_tackles
				, pms.inflicted_ko
				, pms.inflicted_stun
				, pms.inflicted_injury
				, pms.inflicted_dead
				, pms.meters_run
				, pms.meters_pass
				, pms.sustained_interception
				, pms.sustained_knockdown
				, pms.sustained_tackles
				, pms.sustained_ko
				, pms.sustained_stun
				, pms.sustained_injury
				, pms.sustained_dead
				, cas.cas_text
				, sk.skill_text
	FROM bb_player_match_stats pms
	INNER JOIN bb_player p ON pms.player_id = p.player_id
	INNER JOIN bb_match m ON pms.match_id = m.match_id
	INNER JOIN bb_team t ON t.team_id IN (m.home_team_id, m.away_team_id) AND t.team_id <> p.team_id
	INNER JOIN bb_lkp_race r ON t.race_id = r.race_id
	LEFT JOIN ( SELECT pc.player_id, pc.match_id_sustained, GROUP_CONCAT(CONCAT('<a title=\"', c.effect_english , '\">', c.description, '</a>')) AS cas_text
			FROM bb_player_casualty pc
			LEFT JOIN bb_lkp_casualty c ON pc.casualty_id = c.casualty_id
			LEFT JOIN bb_lkp_casualty_status cs ON pc.casualty_status_id = cs.casualty_status_id
			GROUP BY pc.player_id, pc.match_id_sustained
		  ) AS cas ON p.player_id = cas.player_id AND cas.match_id_sustained = m.match_id
	LEFT JOIN ( SELECT ps.player_id, ps.match_id_debut, GROUP_CONCAT(CONCAT('<a href=\"skill.php?skill_id=', s.skill_id, '\">', s.human_desc, '</a>')) AS skill_text
			FROM bb_player_skill ps
			INNER JOIN bb_lkp_skill s ON ps.skill_id = s.skill_id
			GROUP BY ps.player_id, ps.match_id_debut
		  ) AS sk ON p.player_id = sk.player_id AND sk.match_id_debut = m.match_id

	WHERE pms.player_id = ?
	ORDER BY match_date DESC
  ");

   $sql->bindParam(1, $_GET['player_id'], PDO::PARAM_INT);
   $sql->execute();
   $match_list = $sql->fetchAll(PDO::FETCH_ASSOC);
   foreach ($match_list as $row) {
	echo '<tr>';
	echo '<td><a href="matchdetail.php?match_id=' . $row['match_id'] . '">'. $row['match_id'] . '</a></td>'.PHP_EOL;
	echo '<td>' . date('M j, Y h:i A', $row['match_date']) . '</td>'.PHP_EOL;
	echo '<td><a title = "' . $row['race_name'] . '" href="race.php?race_id=' . $row['race_id'] . '">'. $row['race_name_short'] . '</a></td>'.PHP_EOL;
	echo '<td><a href="team.php?team_id=' . $row['team_id'] . '">'. $row['team_name'] . '</a></td>'.PHP_EOL;
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
	echo '<td>'. $row['catches'] . '</td>'.PHP_EOL;
	echo '<td>'. $row['meters_run'] . '</td>'.PHP_EOL;
	echo '<td>'. $row['meters_pass'] . '</td>'.PHP_EOL;
	echo '<td>'. $row['cas_text'] . '</td>'.PHP_EOL;
	echo '<td>'. $row['skill_text'] . '</td>'.PHP_EOL;
	echo '</tr>';
  }
?>

</tbody>
</table>


<?php include_once("inc/footer.php"); ?>