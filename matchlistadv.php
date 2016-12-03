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

if ((!permission_check(4)) || (!isset($_SESSION['domain_id']))) { // standard read privilages
	echo '<h2>Page not found</h2>';
	echo '<p>Page not found, or you lack the necessary permissions.</p>';
	include_once("../inc/footer.php");
	die();
}
?>


<h2>Advanced Match List</h2>



<p>Shows more stats per match!</p>

<br/>

<table id="matchTable" class="tablesorter">
<thead>
<tr><th>ID</th><th>Date</th><th title="Default Competition">Comp</th><th title="Home Race">H.R.</th><th>Home Team</th><th>TD1</th><th>TD2</th><th>Away Team</th><th title="Away Race">A.R.</th><th title="Match rating out of 20">MR</th>
<th title="Total Touchdowns">TD</th>
<th title="Total Passes">PS</th>
<th title="Total Catches">C</th>
<th title="Total Interceptions">I</th>
<th title="Total Knockdowns - ie armour rolls from blocks">KD</th>
<th title="Total Tackles">T</th>
<th title="Total KO'ed">KO</th>
<th title="Total Injuries (inflicted via block-like activities?)">CAS</th>
<th title="Total Kills">K</th>
<th title="Total Meters Run">MR</th>
<th title="Total Meters Passed">MP</th>
</tr>
</thead>
<tbody>
<?php
   $sql = $conn->prepare("SELECT m.match_id
				, hr.short_description AS home_race_short
				, hr.description AS home_race
				, COALESCE(hc.description, '???') AS home_coach
				, ht.description as home_team
				, m.home_touchdown_count
				, m.away_touchdown_count
				, ar.description AS away_race
				, ar.short_description AS away_race_short
				, COALESCE(ac.description, '???') AS away_coach
				, at.description as away_team
				, ht.team_id as home_team_id
				, at.team_id as away_team_id
				, hr.race_id as home_race_id
				, ar.race_id as away_race_id
				, hc.coach_id as home_coach_id
				, ac.coach_id as away_coach_id
				, UNIX_TIMESTAMP(m.match_date) as match_date
				, comp.competition_id
				, comp.description as competition_name
				, comp.short_description as competition_name_short
				, m.rating
				, m.home_touchdown_count + m.away_touchdown_count AS ttl_touchdowns
				, SUM(COALESCE(s.passes,0)) AS ttl_passes
				, SUM(COALESCE(s.catches,0)) AS ttl_catches
				, SUM(COALESCE(s.interceptions,0)) AS ttl_interceptions
				, SUM(COALESCE(s.inflicted_knockdown,0)) AS ttl_inflicted_kd
				, SUM(COALESCE(s.inflicted_tackles,0)) AS ttl_inflicted_tackle
				, SUM(COALESCE(s.inflicted_ko,0)) AS ttl_inflicted_ko
				, SUM(COALESCE(s.inflicted_injury,0)) AS ttl_inflicted_injury
				, SUM(COALESCE(s.inflicted_dead,0)) AS ttl_inflicted_dead
				, SUM(COALESCE(s.meters_run,0)) AS ttl_meters_run
				, SUM(COALESCE(s.meters_pass,0)) AS ttl_meters_pass
				, NULL AS ttl_block_success
				, NULL AS ttl_block_attempt
				, NULL AS ttl_dodges
		FROM bb_match m
		INNER JOIN bb_team ht ON m.home_team_id = ht.team_id
		INNER JOIN bb_team at ON m.away_team_id = at.team_id
		LEFT JOIN bb_coach hc ON ht.coach_id = hc.coach_id
		LEFT JOIN bb_coach ac ON at.coach_id = ac.coach_id
		INNER JOIN bb_lkp_race hr ON ht.race_id = hr.race_id
		INNER JOIN bb_lkp_race ar ON at.race_id = ar.race_id
		LEFT JOIN bb_match_competition mc ON m.match_id = mc.match_id AND mc.default_competition = 1
		LEFT JOIN bb_competition comp ON mc.competition_id = comp.competition_id AND m.domain_id = comp.domain_id
		LEFT JOIN bb_match_team_stats s ON m.match_id = s.match_id
		WHERE m.domain_id = ?
GROUP BY m.match_id
				, hr.description
				, hr.description
				, COALESCE(hc.description, '???')
				, ht.description
				, m.home_touchdown_count
				, m.away_touchdown_count
				, ar.description
				, ar.short_description
				, COALESCE(ac.description, '???')
				, at.description
				, ht.team_id
				, at.team_id
				, hr.race_id
				, ar.race_id
				, hc.coach_id
				, ac.coach_id
				, UNIX_TIMESTAMP(m.match_date) 
				, comp.competition_id
				, comp.description 
				, comp.short_description
				, m.rating
				, m.home_touchdown_count + m.away_touchdown_count
 ORDER BY m.match_date DESC");
   $sql->execute(array($_SESSION['domain_id']));
   $match_data = $sql->fetchAll(PDO::FETCH_ASSOC);
   foreach ($match_data as $row)
   {
	echo "<tr>";
	echo '<td><a href="matchdetail.php?match_id=' . $row['match_id'] . '">'. $row['match_id'] . '</a></td>'.PHP_EOL;
	echo '<td>'. date('Y-m-d', $row['match_date']) . '</td>'.PHP_EOL;
	echo '<td title="'. $row['competition_name'] . '"><a href="competition.php?competition_id=' . $row['competition_id'] . '">'. $row['competition_name_short'] . '</a></td>'.PHP_EOL;
	echo '<td title="'. $row['home_race'] .'"><a href="race.php?race_id=' . $row['home_race_id'] . '">'. $row['home_race_short'] . '</a></td>'.PHP_EOL;
	echo '<td><a href="team.php?team_id=' . $row['home_team_id'] . '">'. $row['home_team'] . '</a></td>'.PHP_EOL;

	echo '<td>'. $row['home_touchdown_count'] . '</td>'.PHP_EOL;
	echo '<td>'. $row['away_touchdown_count'] . '</td>'.PHP_EOL;

	echo '<td><a href="team.php?team_id=' . $row['away_team_id'] . '">'. $row['away_team'] . '</a></td>'.PHP_EOL;
	echo '<td title="'. $row['away_race'] .'"><a href="race.php?race_id=' . $row['away_race_id'] . '">'. $row['away_race_short'] . '</a></td>'.PHP_EOL;
	echo '<td>'. $row['rating'] . '</td>'.PHP_EOL;
	echo '<td>'. $row['ttl_touchdowns'] . '</td>'.PHP_EOL;
	echo '<td>'. $row['ttl_passes'] . '</td>'.PHP_EOL;
	echo '<td>'. $row['ttl_catches'] . '</td>'.PHP_EOL;
	echo '<td>'. $row['ttl_interceptions'] . '</td>'.PHP_EOL;
	echo '<td>'. $row['ttl_inflicted_kd'] . '</td>'.PHP_EOL;
	echo '<td>'. $row['ttl_inflicted_tackle'] . '</td>'.PHP_EOL;
	echo '<td>'. $row['ttl_inflicted_ko'] . '</td>'.PHP_EOL;
	echo '<td>'. $row['ttl_inflicted_injury'] . '</td>'.PHP_EOL;
	echo '<td>'. $row['ttl_inflicted_dead'] . '</td>'.PHP_EOL;
	echo '<td>'. $row['ttl_meters_run'] . '</td>'.PHP_EOL;
	echo '<td>'. $row['ttl_meters_pass'] . '</td>'.PHP_EOL;
	echo "</tr>".PHP_EOL;
   }
?>
</tbody>
</table>



<?php include_once("inc/footer.php"); ?>