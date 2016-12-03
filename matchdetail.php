<?php include_once("inc/header1.php");

// if you need to return header() statements, do them here. SQL connection has already been established.

include_once("inc/header2.php"); ?>

BBDB</title>

<?php

// Check the data is correct... 2 team stats, and match record, and domain_id is correct
// Also, check we are logged in with correct permissions
$check_stmt = $conn->prepare("SELECT COUNT(*) FROM bb_match_team_stats ms
					INNER JOIN bb_match m ON ms.match_id = m.match_id
					WHERE ms.match_id = ?
					AND m.domain_id = ?");

	$check_stmt->bindParam(1, $_GET['match_id'], PDO::PARAM_INT);
	$check_stmt->bindParam(2, $_SESSION['domain_id'], PDO::PARAM_INT);
	$check_stmt->execute();

	$team_count = $check_stmt->fetchColumn();

	if((!permission_check(4)) || ($team_count<>2)) { // standard read privilages
		include_once("inc/no_permission.php");
	}



	$stats = $conn->prepare("SELECT m.match_id, ht.description as 'home_team_name', m.home_team_id, at.description as 'away_team_name', m.away_team_id
					, hr.race_id as home_race_id, hr.description as home_race_name, ar.race_id as away_race_id, ar.description as away_race_name
					, m.home_touchdown_count, m.away_touchdown_count
					, ht.str_logo AS 'home_logo'
					, hst.possession as 'home_possession'
					, hst.occupation_own as 'home_occupation_own'
					, hst.occupation_their as 'home_occupation_their'
					, hst.passes as 'home_passes' , hst.catches as 'home_catches', hst.interceptions as 'home_interceptions'
					, hst.inflicted_knockdown as 'home_knockdown'
					, hst.inflicted_tackles as 'home_tackles'
					, hst.inflicted_ko as 'home_ko'
					, hst.inflicted_injury as 'home_injury'
					, hst.inflicted_dead as 'home_killed'
					, hst.meters_run as 'home_meters_run'
					, hst.meters_pass as 'home_meters_passed'
					, hst.block_success as 'home_block_success'
					, hst.block_attempt as 'home_block_attempt'
					, hst.dodge_count as 'home_dodge'
					, hst.team_value AS 'home_team_value'
					, hst.rerolls AS 'home_rerolls'
					, at.str_logo AS 'away_logo'
					, ast.possession as 'away_possession'
					, ast.occupation_own as 'away_occupation_own'
					, ast.occupation_their as 'away_occupation_their'
					, ast.passes as 'away_passes' , ast.catches as 'away_catches', ast.interceptions as 'away_interceptions'
					, ast.inflicted_knockdown as 'away_knockdown'
					, ast.inflicted_tackles as 'away_tackles'
					, ast.inflicted_ko as 'away_ko'
					, ast.inflicted_injury as 'away_injury'
					, ast.inflicted_dead as 'away_killed'
					, ast.meters_run as 'away_meters_run'
					, ast.meters_pass as 'away_meters_passed'
					, ast.block_success as 'away_block_success'
					, ast.block_attempt as 'away_block_attempt'
					, ast.dodge_count as 'away_dodge'
					, ast.team_value AS 'away_team_value'
					, ast.rerolls AS 'away_rerolls'
					, u.filename
			FROM bb_match m
			INNER JOIN bb_team ht on m.home_team_id = ht.team_id
			INNER JOIN bb_team at on m.away_team_id = at.team_id
			INNER JOIN bb_lkp_race hr on ht.race_id = hr.race_id
			INNER JOIN bb_lkp_race ar on at.race_id = ar.race_id
			INNER JOIN bb_match_team_stats hst on m.match_id = hst.match_id AND m.home_team_id = hst.team_id
			INNER JOIN bb_match_team_stats ast on m.match_id = ast.match_id AND m.away_team_id = ast.team_id
			LEFT JOIN bb_upload u ON m.match_id = u.upload_id
			WHERE m.match_id = ?");

	$stats->bindParam(1, $_GET['match_id'], PDO::PARAM_INT);
	$stats->execute();

	$stat_row = $stats->fetch(PDO::FETCH_ASSOC);

	$pie_chart_array = array();
	$pie_chart_array['cols'] = array(array('label' => 'Team', 'type' => 'string'),
					 array('label' => 'Percentage', 'type' => 'number'));

	$pie_chart_array['rows'] = array(array('c' => array(array('v' => (string) $stat_row['home_team_name']), array('v' => (int) $stat_row['home_possession']))),
					array('c' => array(array('v' => (string) $stat_row['away_team_name']), array('v' => (int) $stat_row['away_possession']))));

	$pie_chart_data = json_encode($pie_chart_array);

	$stat_bar_chart_array = array();

	$stat_bar_chart_array['cols'] = array(array('label' => 'Statistic', 'type' => 'string'),
					 array('label' => $stat_row['home_team_name'], 'type' => 'number'),
					 array('label' => 'No-one', 'type' => 'number'),
					 array('label' => $stat_row['away_team_name'], 'type' => 'number'));

	// Set up dummy values to amke things add up to 100, or cover there being 0 of a particular category
	$no_one_possession = 100 - $stat_row['home_possession'] - $stat_row['away_possession'];
	$no_one_passes = 0;
	if (($stat_row['home_passes']==0) && ($stat_row['away_passes']==0))
		{ $no_one_passes = 1; }
	$no_one_injuries = 0;
	if (($stat_row['home_injury']==0) && ($stat_row['away_injury']==0))
		{ $no_one_injuries = 1; }

	$stat_bar_chart_array['rows'] = array(array('c' => array(array('v' => 'Touchdowns'), array('v' => (int) $stat_row['home_touchdown_count']), array('v' => (int) 0), array('v' => (int) $stat_row['away_touchdown_count'])))
					, array('c' => array(array('v' => 'Passes'), array('v' => (int) $stat_row['home_passes']), array('v' => (int) $no_one_passes), array('v' => (int) $stat_row['away_passes'])))
					, array('c' => array(array('v' => 'Knockdowns'), array('v' => (int) $stat_row['home_knockdown']), array('v' => (int) 0), array('v' => (int) $stat_row['away_knockdown'])))
					, array('c' => array(array('v' => 'Casualties'), array('v' => (int) $stat_row['home_injury']), array('v' => (int) $no_one_injuries), array('v' => (int) $stat_row['away_injury'])))
					, array('c' => array(array('v' => 'Possession'), array('v' => (int) $stat_row['home_possession']), array('v' => (int) $no_one_possession), array('v' => (int) $stat_row['away_possession'])))
					, array('c' => array(array('v' => 'Territory'), array('v' => (int) $stat_row['home_occupation_own']), array('v' => (int) 0), array('v' => (int) $stat_row['away_occupation_own'])))
				);

	$bar_chart_data = json_encode($stat_bar_chart_array);

?>
<script>
$(document).ready(function() 
    { 
	$("#playerTable").tablesorter(); 
    } 
); 

    $(function() {
        $( "#tabs" ).tabs();
    });
</script>
<script src="https://www.google.com/jsapi"></script>
<script>
      // Load the Visualization API and the piechart package.
      google.load('visualization', '1.0', {'packages':['corechart']});

      // Set a callback to run when the Google Visualization API is loaded.
      google.setOnLoadCallback(drawChart);

      // Callback that creates and populates a data table,
      // instantiates the pie chart, passes in the data and
      // draws it.
      function drawChart() {

           // Create our data table out of JSON data loaded from server.
          var data = new google.visualization.DataTable(<?php echo $bar_chart_data; ?>);
          var options = {
              width: 600,
              height: 400,
	      isStacked : 'percent',
	      legend : {position: 'none'},
	      hAxis: {
		minValue: 0,
		ticks: [0, .25, .5, .75, 1]
	      },
	      series: [{color:'blue'}, {color:'black'}, {color:'red'}]
            };

          // Instantiate and draw our chart, passing in some options.
          // Do not forget to check your div ID
          var chart = new google.visualization.BarChart(document.getElementById('chart_div'));
          chart.draw(data, options);
      }
</script>
<?php // include anything else you want to put in <head> here.

include_once("inc/header3.php"); 

?>
	


<table><tr><td style="vertical-align:top">
<h2>Match Report</h2>



<table class="stat_table">
<tr>
<?php
	echo '<th><a href="team.php?team_id=' . $stat_row['home_team_id'] . '">' . $stat_row['home_team_name'] . '</a>';
	echo ' (<a href="race.php?race_id=' . $stat_row['home_race_id'] . '">' . $stat_row['home_race_name']   .'</a>)</th>';
	echo "<td><strong>" . $stat_row['home_touchdown_count'] . "</strong></td>".PHP_EOL;
	echo "<td>" . $stat_row['home_passes'] . "</td>".PHP_EOL;
	echo "<td>" . $stat_row['home_catches'] . "</td>".PHP_EOL;
	echo "<td>" . $stat_row['home_interceptions'] . "</td>".PHP_EOL;
	echo "<td>" . $stat_row['home_knockdown'] . "</td>".PHP_EOL;
	echo "<td>" . $stat_row['home_ko'] . "</td>".PHP_EOL;
	echo "<td>" . $stat_row['home_injury'] . "</td>".PHP_EOL;
	echo "<td>" . $stat_row['home_killed'] . "</td>".PHP_EOL;
	echo "<td>" . $stat_row['home_tackles'] . "</td>".PHP_EOL;
	echo "<td>" . $stat_row['home_meters_run'] . "</td>".PHP_EOL;
	echo "<td>" . $stat_row['home_meters_passed'] . "</td>".PHP_EOL;
	echo "<td>" . $stat_row['home_possession'] . "</td>".PHP_EOL;
	echo "<td>" . $stat_row['home_occupation_their'] . "</td>".PHP_EOL;
	echo "<td>" . $stat_row['home_team_value'] . "</td>".PHP_EOL;
	echo "<td>" . $stat_row['home_rerolls'] . "</td>".PHP_EOL;
	echo '<td rowspan="3">';
	echo '<form action=matchlogdisplaytest.php method="post">';
    echo '<input type="hidden" name="match_id" value = "' . $_GET['match_id'] . '">';
    echo '<input type="submit" value="Match Report">';
    echo '</form></td>';
?>
</tr>
<tr><th></th><th>TD</th><th title="Passes">PS</th><th title="Catches">C</th><th title="Interceptions">I</th><th title="Knockdowns inflicted">KD</th><th title="KO's inflicted">KO</th>
<th title="Injuries inflicted">CAS</th><th title="Kills inflicted">K</th><th title="Tackles inflicted">T</th><th title="Meters Run">MR</th><th title="Meters Passed">MP</th>
<th title="Possession percentage">POS</th><th title="Territory percentage... how much time was in the opposition half">TR</th><th title="Team Value">TV</th><th title="Rerolls">RR</th></tr>

<tr>
<?php

	echo '<th><a href="team.php?team_id=' . $stat_row['away_team_id'] . '">' . $stat_row['away_team_name'] . '</a>';
	echo ' (<a href="race.php?race_id=' . $stat_row['away_race_id'] . '">' . $stat_row['away_race_name']   .'</a>)</th>';
	echo "<td><strong>" . $stat_row['away_touchdown_count'] . "</strong></td>".PHP_EOL;
	echo "<td>" . $stat_row['away_passes'] . "</td>".PHP_EOL;
	echo "<td>" . $stat_row['away_catches'] . "</td>".PHP_EOL;
	echo "<td>" . $stat_row['away_interceptions'] . "</td>".PHP_EOL;
	echo "<td>" . $stat_row['away_knockdown'] . "</td>".PHP_EOL;
	echo "<td>" . $stat_row['away_ko'] . "</td>".PHP_EOL;
	echo "<td>" . $stat_row['away_injury'] . "</td>".PHP_EOL;
	echo "<td>" . $stat_row['away_killed'] . "</td>".PHP_EOL;
	echo "<td>" . $stat_row['away_tackles'] . "</td>".PHP_EOL;
	echo "<td>" . $stat_row['away_meters_run'] . "</td>".PHP_EOL;
	echo "<td>" . $stat_row['away_meters_passed'] . "</td>".PHP_EOL;
	echo "<td>" . $stat_row['away_possession'] . "</td>".PHP_EOL;
	echo "<td>" . $stat_row['away_occupation_their'] . "</td>".PHP_EOL;
	echo "<td>" . $stat_row['away_team_value'] . "</td>".PHP_EOL;
	echo "<td>" . $stat_row['away_rerolls'] . "</td>".PHP_EOL;

	echo "</tr></table>".PHP_EOL;

	// Now for the team logos!
	
	echo '</td><td style="vertical-align:center"><img alt="Home team logo" src ="img/logos/Logo_';
	echo $stat_row['home_logo'];
	echo '.png"/></td><td style="vertical-align:center">VS</td><td style="vertical-align:center"><img alt="Away team logo" src ="img/logos/Logo_';
	echo $stat_row['away_logo'];
	echo '.png"/></td></tr></table>';
	
	// end of match stats
?>



<div id="chart_div"></div>


<?php
	$player_sql=$conn->prepare("SELECT p.description AS player_name, COALESCE(pt.short_description, pt.description) AS player_type_short
					, pt.player_type_id AS real_player_type_id
					, CASE WHEN p.team_id = m.home_team_id THEN 'H' WHEN p.team_id = m.away_team_id THEN 'A' ELSE 'U' END as team_letter
					, p.mv, p.st, p.ag, p.av
					, ps.* 
					, p.experience
					, p.team_id
					, stat.description as player_status
					, stat.short_description as short_player_status
					, cas.cas_text
					, sk.skill_text
				FROM bb_player_match_stats ps
				INNER JOIN bb_player p ON ps.player_id = p.player_id
				LEFT JOIN bb_lkp_player_type pt ON p.player_type_id = pt.player_type_id
				LEFT JOIN bb_match m ON ps.match_id = m.match_id
				INNER JOIN bb_lkp_player_status stat ON stat.player_status_id = 1
				LEFT JOIN ( SELECT pc.player_id, pc.match_id_sustained, GROUP_CONCAT(CONCAT('<a title=\"', c.effect_english , '\">', c.description, '</a>')) AS cas_text
						FROM bb_player_casualty pc
						LEFT JOIN bb_lkp_casualty c ON pc.casualty_id = c.casualty_id
						LEFT JOIN bb_lkp_casualty_status cs ON pc.casualty_status_id = cs.casualty_status_id
						GROUP BY pc.player_id, pc.match_id_sustained
					  ) AS cas ON p.player_id = cas.player_id AND cas.match_id_sustained = ps.match_id
				LEFT JOIN ( SELECT ps.player_id, ps.match_id_debut, GROUP_CONCAT(CONCAT('<a href=\"skill.php?skill_id=', s.skill_id, '\">', s.human_desc, '</a>')) AS skill_text
						FROM bb_player_skill ps
						INNER JOIN bb_lkp_skill s ON ps.skill_id = s.skill_id
						GROUP BY ps.player_id, ps.match_id_debut
					  ) AS sk ON p.player_id = sk.player_id AND sk.match_id_debut = ps.match_id

			WHERE ps.match_id = :match
			UNION ALL
				SELECT p.name, COALESCE(pt.short_description, pt.description) AS player_type_short
					, pt.player_type_id AS real_player_type_id
					, CASE WHEN p.team_id = m.home_team_id THEN 'H' WHEN p.team_id = m.away_team_id THEN 'A' ELSE 'U' END as team_letter
					, p.mv, p.st, p.ag, p.av
					, ps.*
					, NULL AS experience
					, p.team_id
					, stat.description AS player_status
					, stat.short_description as short_player_status
					, NULL
					, NULL
				FROM bb_player_oneoff p
				INNER JOIN bb_match m ON p.match_id = m.match_id
				INNER JOIN bb_player_match_stats ps ON p.match_id = ps.match_id AND p.bb1_id = ps.oneoff_id
				LEFT JOIN bb_lkp_player_type pt ON p.player_type_id = pt.player_type_id
			INNER JOIN bb_lkp_player_status stat ON stat.player_status_id = p.player_status_id
			WHERE p.match_id = :match
			AND p.player_status_id <> 4
			UNION ALL
				SELECT pt.description, NULL AS player_type_short, pt.player_type_id
					, CASE WHEN po.team_id = m.home_team_id THEN 'H' WHEN po.team_id = m.away_team_id THEN 'A' ELSE 'U' END as team_letter
					, pts.mv, pts.st, pts.ag, pts.av
					, ps.*
					, NULL AS experience
					, po.team_id
					, 'Star Player', 'S'
					, NULL
					, NULL
				FROM bb_lkp_player_type pt
				INNER JOIN bb_player_match_stats ps ON pt.player_type_id = ps.player_type_id
				INNER JOIN bb_match m ON ps.match_id = m.match_id
				INNER JOIN bb_lkp_player_type_stats pts ON pt.player_type_id = pts.player_type_id AND pts.ruleset_id = 1
				INNER JOIN bb_player_oneoff po ON pt.player_type_id = po.player_type_id AND po.match_id = ps.match_id
			WHERE ps.match_id = :match AND pt.race_id = 0");

	$player_sql->bindParam(':match', $_GET['match_id'], PDO::PARAM_INT);
	$player_sql->execute();

	$dice_mk1_sql = $conn->prepare("select CASE WHEN t.team_id = bbm.home_team_id THEN 'H' ELSE 'A' END as home_or_away
							, t.description AS team_name
							, rt.description AS roll_type
							, dt.description AS dice_type
							, dt.dice_type_id
							, m.roll_value
							, COUNT(*) AS roll_count
							, COALESCE(tt.description, NULL) AS roll_lookup
						FROM bb_matchlog m
						INNER JOIN bb_match bbm ON m.match_id = bbm.match_id
						INNER JOIN bb_team t ON m.team_id = t.team_id
						INNER JOIN bb_lkp_roll_type rt ON m.roll_type_id = rt.roll_type_id
						INNER JOIN bb_lkp_dice_type dt ON rt.dice_type_id = dt.dice_type_id
						LEFT JOIN bb_lkp_turnover_type tt ON m.roll_type_id = 46 AND m.roll_value = tt.turnover_type_id
						WHERE m.match_id = :match
						GROUP BY CASE WHEN t.team_id = bbm.home_team_id THEN 'H' ELSE 'A' END
							, t.description 
							, rt.description
							, dt.description
							, dt.dice_type_id
							, m.roll_value
						ORDER BY dt.dice_type_id
							, CASE WHEN t.team_id = bbm.home_team_id THEN 'H' ELSE 'A' END DESC
							, rt.description
							, m.roll_value");
							
	$dice_mk1_sql->bindParam(':match', $_GET['match_id'], PDO::PARAM_INT);
	$dice_mk1_sql->execute();
	
	$best_block_dice_sql = $conn->prepare("select CASE WHEN t.team_id = bbm.home_team_id THEN 'H' ELSE 'A' END as home_or_away
							, t.description AS team_name
							, CASE WHEN bdp.block_dice_id_3 IS NOT NULL THEN '3-dice' WHEN bdp.block_dice_id_2 IS NOT NULL THEN '2-dice' ELSE '1-dice' END AS dice_rolled
							, CONCAT(bd.description, CASE WHEN bd.block_dice_id IN (3,4) AND (bdp.block_dice_id_1=2 OR bdp.block_dice_id_2 = 2 OR bdp.block_dice_id_3 = 3) THEN '(+BD)' ELSE '' END) AS best_block_dice
							, bd.block_dice_id
							, CASE WHEN bdp.block_dice_id_3 IS NOT NULL THEN 3 WHEN bdp.block_dice_id_2 IS NOT NULL THEN 2 ELSE 1 END
								+ CASE WHEN t.team_id = bbm.home_team_id THEN 0 ELSE 3 END AS col_num
							, COUNT(*) AS roll_count
						FROM bb_matchlog m
						INNER JOIN bb_match bbm ON m.match_id = bbm.match_id
						INNER JOIN bb_team t ON m.team_id = t.team_id
						INNER JOIN bb_lkp_roll_type rt ON m.roll_type_id = rt.roll_type_id
						INNER JOIN bb_lkp_block_dice_perm bdp ON m.roll_lookup_id = bdp.block_dice_perm_id
						INNER JOIN bb_lkp_block_dice bd ON bd.block_dice_id = CASE WHEN bdp.block_dice_id_1 >= COALESCE(bdp.block_dice_id_2,0) AND bdp.block_dice_id_1 >= COALESCE(bdp.block_dice_id_3,0) THEN bdp.block_dice_id_1 WHEN  bdp.block_dice_id_2 >= bdp.block_dice_id_1 AND bdp.block_dice_id_1 >= COALESCE(bdp.block_dice_id_3,0) THEN bdp.block_dice_id_2 ELSE bdp.block_dice_id_3 END
						WHERE m.match_id = :match
						AND rt.dice_type_id = 4
						GROUP BY  CASE WHEN t.team_id = bbm.home_team_id THEN 'H' ELSE 'A' END
							, t.description
							, CASE WHEN bdp.block_dice_id_3 IS NOT NULL THEN '3-dice' WHEN bdp.block_dice_id_2 IS NOT NULL THEN '2-dice' ELSE '1-dice' END
							, CONCAT(bd.description, CASE WHEN bd.block_dice_id IN (3,4) AND (bdp.block_dice_id_1=2 OR bdp.block_dice_id_2 = 2 OR bdp.block_dice_id_3 = 3) THEN '(+BD)' ELSE '' END)
							, bd.block_dice_id
						ORDER BY  bd.block_dice_id ASC
							, CASE WHEN bd.block_dice_id IN (3,4) AND (bdp.block_dice_id_1=2 OR bdp.block_dice_id_2 = 2 OR bdp.block_dice_id_3 = 3) THEN '(+BD)' ELSE '' END
							, CASE WHEN t.team_id = bbm.home_team_id THEN 'H' ELSE 'A' END DESC
							, CASE WHEN bdp.block_dice_id_3 IS NOT NULL THEN '3-dice' WHEN bdp.block_dice_id_2 IS NOT NULL THEN '2-dice' ELSE '1-dice' END");
	
	$best_block_dice_sql->bindParam(':match', $_GET['match_id'], PDO::PARAM_INT);
	$best_block_dice_sql->execute();
?>

<div id="tabs">
  <ul>
    <li><a href="#tabs-1">Player List</a></li>
    <li><a href="#tabs-2">D6</a></li>
    <li><a href="#tabs-3">2D6</a></li>
    <li><a href="#tabs-4">Block Dice</a></li>
	<li><a href="#tabs-5">Turn ends</a></li>
	<li><a href="#tabs-6">Other</a></li>
  </ul>
  <div id="tabs-1">
	<table id="playerTable" class="tablesorter">
	<thead>
	<tr><th>Type</th><th>Name</th><th title="Team... Home or Away">T</th><th title="This is latest experience, not experience as of this match. Sorry.">XP</th>
	<th title="Played">Pl</th><th>MVP</th><th title="Touchdowns Scored">TD</th><th title="Passes">PS</th><th title="Interceptions">I</th><th title="Casualtied Inflicted">CAS</th>
	<th title="Knockdowns Inflicted">KDF</th><th title="Knockdowns Recieved">KDA</th><th title="Knockouts Inflicted">KOF</th><th title="Knockouts Recieved">KOA</th>
	<th title="Injuries Recieved">INJ</th><th title="Kills">K</th><th title="Meters Run">MR</th><th title="Meters Passed">MP</th>
	<th title="Injuries Sustained">IS</th><th title="Skills Debuted">Sk</th>
	</tr>
	</thead>
	<tbody>
	<?php
	   $player_data = $player_sql->fetchAll(PDO::FETCH_ASSOC);
	   foreach ($player_data as $row)
	   {
		echo "<tr>";
		if ($row['player_status_id']==4) { echo '<td>Star Player</td>'.PHP_EOL; 
			echo '<td><a href="playertype.php?player_type_id=' . $row['real_player_type_id'] . '">'. $row['player_name'] . '</a></td>'.PHP_EOL; 
			} elseif ($row['player_status_id']== 1 || $row['player_status_id']== 2 || $row['player_status_id']== 3)
		{ 	echo '<td><a href="playertype.php?player_type_id=' . $row['real_player_type_id'] . '">'. $row['player_type_short'] . '</a></td>'.PHP_EOL; 
			echo '<td><a href="player.php?player_id=' . $row['player_id'] . '">'. $row['player_name'] . '</a></td>'.PHP_EOL;
			} else
		{	echo '<td><a href="playertype.php?player_type_id=' . $row['real_player_type_id'] . '">'. $row['player_type_short'] . '</a></td>'.PHP_EOL; 
			echo '<td>' . $row['player_name'] . '</td>'.PHP_EOL;
		}
		echo '<td><a href="team.php?team_id=' . $row['team_id'] . '">'. $row['team_letter'] . '</a>';
		if ($row['player_status_id'] > 3) {
			echo ' - ' . $row['short_player_status'] . '</td>'.PHP_EOL;
		} else
		{	echo '</td>'.PHP_EOL; }
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
		echo '<td>'. $row['cas_text'] . '</td>'.PHP_EOL;
		echo '<td>'. $row['skill_text'] . '</td>'.PHP_EOL;
		echo "</tr>".PHP_EOL;
	   }

	?>

	</tbody>
	</table>

  </div>
  <div id="tabs-2">
  <table align="center">
  <?php
	// do the header row
	echo '<tr><td align="center">' . $stat_row['home_team_name'] . '</td><td align="center">' . $stat_row['away_team_name'] . '</td></tr><tr><td valign="top">';
  
	function pad_row($curr_index, $tot, $max_index) {
		while($max_index>$curr_index) { // spit out empty cells until we have done 6 columns
				echo '<td></td>';
				$curr_index++;
			}
			if ($curr_index<>99) { // ie, it's not the first row
				echo '<td>' . $tot . '</td>';
			}
	}
	
	$next_roll_value = 99; // so the "new roll type" fires automatically
	$next_team = 'A';
	$next_dice_type = '2D6';
	$roll_type_total = 0;
	// initialise the total variables
	for ($i=1; $i<7; $i++) {
		$total_rolls_that_were_a[$i] = 0;
	}
	
	echo '<table class="dice-stats"><tr><th>Roll Type</th><th>1</th><th>2</th><th>3</th><th>4</th><th>5</th><th>6</th><th>Total</th>';
	
	while ($row = $dice_mk1_sql->fetch(PDO::FETCH_ASSOC)) {
		if($row['dice_type']==$next_dice_type) {
			pad_row($next_roll_value, $roll_type_total, 6);
			$last_roll_type=$row['roll_type'];
			break;
		}
		
		if($row['home_or_away']==$next_team) {
			pad_row($next_roll_value, $roll_type_total, 6);
			echo '</tr></table>' . PHP_EOL;
			echo '</td><td valign="top">'; // this is the larger "grouping" table
			echo '<table class="dice-stats"><tr><th>Roll Type</th><th>1</th><th>2</th><th>3</th><th>4</th><th>5</th><th>6</th><th>Total</th>';
			$next_team='Z';
			$next_roll_value=99;
			$last_roll_type='Bob';
		}
		
		if($last_roll_type<>$row['roll_type']) { // it's a new roll type.... so do a new row
			pad_row($next_roll_value, $roll_type_total, 6);
			echo '</tr><tr><td>';
			echo $row['roll_type'] . '</td>';
			$next_roll_value = 0;
			$roll_type_total = 0;
		}
		$next_roll_value++;
		while($row['roll_value']<>$next_roll_value) { // spit out empty cells until we get to the roll in question
			echo '<td></td>';
			$next_roll_value++;
			if($next_roll_value==10) {
				die; // something has gone horribly wrong
			}
		}
		echo '<td>' . $row['roll_count'] . '</td>';
		$total_rolls_that_were_a[$row['roll_value']] = $total_rolls_that_were_a[$row['roll_value']] + $row['roll_count'];
		$roll_type_total = $roll_type_total + $row['roll_count'];
		$last_roll_type=$row['roll_type'];
	}
	echo '</tr></table>'
  ?>
  </td></tr></table>
  </div>
  <div id="tabs-3">
  <table align="center">
  <?php
	// do the header row
	echo '<tr><td align="center">' . $stat_row['home_team_name'] . '</td><td align="center">' . $stat_row['away_team_name'] . '</td></tr><tr><td valign="top">';
	
	$next_roll_value = 1;
	$next_team = 'A';
	$next_dice_type = 'Block Dice';
	$roll_type_total = 0;
	// initialise the total variables
	for ($i=1; $i<13; $i++) {
		$total_rolls_that_were_a[$i] = 0;
	}
  
	echo '<table class="dice-stats"><tr><th>Roll Type</th><th>1</th><th>2</th><th>3</th><th>4</th><th>5</th><th>6</th>';
	echo '<th>7</th><th>8</th><th>9</th><th>10</th><th>11</th><th>12</th><th>Total</th>'.PHP_EOL;
	// the first line is still in $row
	echo '</tr><tr><td>'.$row['roll_type'].'</td>';
	while($row['roll_value']>$next_roll_value) { // spit out empty cells until we are up to the current number
				echo '<td></td>';
				$next_roll_value++;
			}
	echo '<td>' . $row['roll_count'] . '</td>';
	$total_rolls_that_were_a[$row['roll_value']] = $total_rolls_that_were_a[$row['roll_value']] + $row['roll_count'];
		$roll_type_total = $roll_type_total + $row['roll_count'];
	
	// now we can just repeat the code from above...?
	while ($row = $dice_mk1_sql->fetch(PDO::FETCH_ASSOC)) {
		if($row['dice_type']==$next_dice_type) {
			pad_row($next_roll_value, $roll_type_total, 12);
			break;
		}
		
		if($row['home_or_away']==$next_team) {
			pad_row($next_roll_value, $roll_type_total, 12);
			echo '</tr></table>' . PHP_EOL;
			echo '</td><td valign="top">'; // this is the larger "grouping" table
			echo '<table class="dice-stats"><tr><th>Roll Type</th><th>1</th><th>2</th><th>3</th><th>4</th><th>5</th><th>6</th>';
			echo '<th>7</th><th>8</th><th>9</th><th>10</th><th>11</th><th>12</th><th>Total</th>'.PHP_EOL;
			$next_team='Z';
			$next_roll_value=99;
			$last_roll_type='Bob';
		}
		
		if($last_roll_type<>$row['roll_type']) { // it's a new roll type.... so do a new row
			pad_row($next_roll_value, $roll_type_total, 12);
			echo '</tr><tr><td>';
			echo $row['roll_type'] . '</td>';
			$next_roll_value = 0;
			$roll_type_total = 0;
		}
		$next_roll_value++;
		while($row['roll_value']<>$next_roll_value) { // spit out empty cells until we get to the roll in question
			echo '<td></td>';
			$next_roll_value++;
			if($next_roll_value==15) {
				die; // something has gone horribly wrong
			}
		}
		echo '<td>' . $row['roll_count'] . '</td>';
		$total_rolls_that_were_a[$row['roll_value']] = $total_rolls_that_were_a[$row['roll_value']] + $row['roll_count'];
		$roll_type_total = $roll_type_total + $row['roll_count'];
		$last_roll_type=$row['roll_type'];
	}
	
	
	echo '</tr></table>';
  
  ?>
  </td></tr></table>
  </div>
  <div id="tabs-4">
  <table><tr><td>
  <table class="dice-stats">
	<tr><th></th><th colspan="3"> <?php echo $stat_row['home_team_name']; ?>
	</th><th colspan="3"> <?php echo $stat_row['away_team_name']; ?>
	</th><th></th></tr>
	<tr><th></th><th>1D</th><th>2D</th><th>3D</th><th>1D</th><th>2D</th><th>3D</th><th>Total</th>
	<?php
	
	$next_col_id = 99;
	$last_block_dice = 'dummy text';
	$row_total[$stat_row['home_team_name']] = 0;
	$row_total[$stat_row['away_team_name']] = 0;
	
	while ($row = $best_block_dice_sql->fetch(PDO::FETCH_ASSOC)) {
		if($row['best_block_dice'] <> $last_block_dice) {
			pad_row($next_col_id, $row_total, 7);
			echo'</tr><tr><td>' . $row['best_block_dice'] . '</td>';
			$next_col_id = 1;
			$row_total = 0;
		}
		
		while($row['col_num']> $next_col_id) {
			$next_col_id++;
			echo '<td></td>';
		}
		echo '<td>' . $row['roll_count'] . '</td>';
		$row_total = $row_total + $row['roll_count'];
		$last_block_dice = $row['best_block_dice'];
		$block_grid[$row['best_block_dice']][$row['col_num']] = $row['roll_count'];
		$next_col_id++;
	}
	pad_row($next_col_id, $row_total, 6);
	echo '</tr>';
	?>
  </table>
  </td></tr></table>
  <p>Limitation: Does not know the difference betweeen 2 or 3 dice against and 2 or 3 dice for. It assumes all are for.</p>
  <h3>Blocking probability analysis</h3>
  <?php // enhanced stats!
		for($i=1;$i<7;$i++) {
			if($i<4) {  // home team
				$home_blocks_with_x_dice[$i] = $block_grid['Red Skull'][$i] + $block_grid['Both Down'][$i] + $block_grid['Pushed'][$i]
												+ $block_grid['Pushed(+BD)'][$i] + $block_grid['Defender Stumbles'][$i]
												+ $block_grid['Defender Stumbles(+BD)'][$i] + $block_grid['Defender Down'][$i];
			}
			else {
				$away_blocks_with_x_dice[$i-3] = $block_grid['Red Skull'][$i] + $block_grid['Both Down'][$i] + $block_grid['Pushed'][$i]
												+ $block_grid['Pushed(+BD)'][$i] + $block_grid['Defender Stumbles'][$i]
												+ $block_grid['Defender Stumbles(+BD)'][$i] + $block_grid['Defender Down'][$i];
			}
		}
		
		$home_total_blocks = $home_blocks_with_x_dice[1] + $home_blocks_with_x_dice[2] + $home_blocks_with_x_dice[3];
		$away_total_blocks = $away_blocks_with_x_dice[1] + $away_blocks_with_x_dice[2] + $away_blocks_with_x_dice[3];
			
		$home_dd_chance = 	(	((1/6) * $home_blocks_with_x_dice[1]) 
							+	((11/36) * $home_blocks_with_x_dice[2])
							+	((91/216) * $home_blocks_with_x_dice[3]))
							/ $home_total_blocks;
		$home_ds_chance = 	(	((1/3) * $home_blocks_with_x_dice[1]) 
							+	((5/9) * $home_blocks_with_x_dice[2])
							+	((19/27) * $home_blocks_with_x_dice[3]))
							/ $home_total_blocks;
		$away_dd_chance = 	(	((1/6) * $away_blocks_with_x_dice[1]) 
							+	((11/36) * $away_blocks_with_x_dice[2])
							+	((91/216) * $away_blocks_with_x_dice[3]))
							/ $away_total_blocks;
		$away_ds_chance = 	(	((1/3) * $away_blocks_with_x_dice[1]) 
							+	((5/9) * $away_blocks_with_x_dice[2])
							+	((19/27) * $away_blocks_with_x_dice[3]))
							/ $away_total_blocks;
		
		$home_dd_actual = $block_grid['Defender Down'][1] + $block_grid['Defender Down'][2] + $block_grid['Defender Down'][3];
		$away_dd_actual = $block_grid['Defender Down'][4] + $block_grid['Defender Down'][5] + $block_grid['Defender Down'][6];
		
		$home_ds_actual = $block_grid['Defender Stumbles'][1] + $block_grid['Defender Stumbles'][2] + $block_grid['Defender Stumbles'][3]
							+ $block_grid['Defender Stumbles(+BD)'][1] + $block_grid['Defender Stumbles(+BD)'][2] + $block_grid['Defender Stumbles(+BD)'][3];
		$away_ds_actual = $block_grid['Defender Stumbles'][4] + $block_grid['Defender Stumbles'][5] + $block_grid['Defender Stumbles'][6]
							+ $block_grid['Defender Stumbles(+BD)'][4] + $block_grid['Defender Stumbles(+BD)'][5] + $block_grid['Defender Stumbles(+BD)'][6];
  
		$home_dd_actual_pc = $home_dd_actual / $home_total_blocks;
		$away_dd_actual_pc = $away_dd_actual / $away_total_blocks;
		
		$home_ds_actual_pc = ($home_dd_actual + $home_ds_actual) / $home_total_blocks;
		$away_ds_actual_pc = ($away_dd_actual + $away_ds_actual) / $away_total_blocks;
  
		$home_ds_forecast_display = FLOOR($home_ds_chance*1000)/10 . '% (' . FLOOR($home_ds_chance*$home_total_blocks*10)/10 . ')';
		$away_ds_forecast_display = FLOOR($away_ds_chance*1000)/10 . '% (' . FLOOR($away_ds_chance*$away_total_blocks*10)/10 . ')';
		
		$home_dd_forecast_display = FLOOR($home_dd_chance*1000)/10 . '% (' . FLOOR($home_dd_chance*$home_total_blocks*10)/10 . ')';
		$away_dd_forecast_display = FLOOR($away_dd_chance*1000)/10 . '% (' . FLOOR($away_dd_chance*$away_total_blocks*10)/10 . ')';
  
  		$home_ds_actual_display = FLOOR($home_ds_actual_pc*1000)/10 . '% (' . ($home_dd_actual+$home_ds_actual) . ')';
		$away_ds_actual_display = FLOOR($away_ds_actual_pc*1000)/10 . '% (' . ($away_dd_actual+$away_ds_actual)  . ')';
		
		$home_dd_actual_display = FLOOR($home_dd_actual_pc*1000)/10 . '% (' . $home_dd_actual . ')';
		$away_dd_actual_display = FLOOR($away_dd_actual_pc*1000)/10 . '% (' . $away_dd_actual . ')';
  
		echo '<table class="dice-stats"><tr><th></th><th>' . $stat_row['home_team_name'] . '</th>';
		echo '<th>' . $stat_row['away_team_name'] . '</th></tr>'.PHP_EOL;
		echo '<tr><td>Total blocks</td><td>' . $home_total_blocks . '</td><td>' . $away_total_blocks . '</td></tr>'.PHP_EOL;
		echo '<tr><td>DS or DD forecast</td><td>' . $home_ds_forecast_display . '</td><td>' . $away_ds_forecast_display . '</td></tr>'.PHP_EOL;
		echo '<tr><td>DS or DD actual</td><td>' . $home_ds_actual_display . '</td><td>' . $away_ds_actual_display . '</td></tr>'.PHP_EOL;
		echo '<tr><td>DD forecast</td><td>' . $home_dd_forecast_display . '</td><td>' . $away_dd_forecast_display . '</td></tr>'.PHP_EOL;
		echo '<tr><td>DD actual</td><td>' . $home_dd_actual_display . '</td><td>' . $away_dd_actual_display . '</td></tr>'.PHP_EOL;

  ?>
  </table>
  </div>
  <div id="tabs-5">
  <p>Here is a brief summary of all the turn endings that BBDB was able to detect.</p>
  <?php

  
	$turnover_summary_sql = $conn->prepare("SELECT bb_team.description AS team_name, tt.description AS turnover_type, COUNT(*) AS tot
											FROM bb_stat_turnovers t
											LEFT JOIN bb_lkp_turnover_type tt ON t.turnover_type_id = tt.turnover_type_id
											LEFT JOIN bb_team ON t.team_id = bb_team.team_id
											WHERE t.match_id = ?
											GROUP BY bb_team.description, tt.description
											ORDER BY bb_team.description, tt.description");
	$turnover_summary_sql->execute(array($_GET['match_id']));
  
	$turnover_summary_data = $turnover_summary_sql->fetchAll(PDO::FETCH_ASSOC);
	foreach ($turnover_summary_data AS $turnover_summary) {
		echo $turnover_summary['team_name'] . " - end of turn type " . $turnover_summary['turnover_type'];
		echo " happened " . $turnover_summary['tot'] . " times.<br/>".PHP_EOL; 
	}
  
	echo '<h3>Turn ending details - listed in the order they happened</h3>';
  
	$turnover_sql = $conn->prepare("
		SELECT tt.description as turnover_type
				, p.player_id
				, CASE WHEN t.player_id IS NULL THEN NULL ELSE COALESCE(p.description, 'merc/star player') END AS player_name
				, t.team_id
				, bb_team.description AS team_name
				, CASE WHEN bdp.short_description IS NOT NULL THEN CONCAT(rt.description, ' - ', bdp.short_description) 
					ELSE
						CONCAT(rt.description, ' - ', COALESCE(roll_value, '?'), ' (', COALESCE(roll_target,'?')
							, CASE WHEN roll_target_exact_flag = 0 THEN '+?' ELSE '+' END, ')') END AS roll_detail
				, CASE WHEN rr.reroll_type_id IS NULL THEN NULL
						ELSE CONCAT('Rerolled ', COALESCE(bdp2.short_description, t.prev_roll_value) 
							, ' via ', rr.description) END AS reroll_detail
		FROM `bb_stat_turnovers` t
		   LEFT JOIN bb_lkp_turnover_type tt ON t.turnover_type_id = tt.turnover_type_id
		   LEFT JOIN bb_player p ON t.player_id = p.player_id
		   LEFT JOIN bb_lkp_roll_type rt ON t.roll_type_id = rt.roll_type_id
		   LEFT JOIN bb_lkp_block_dice_perm bdp ON t.roll_value = bdp.block_dice_perm_id AND rt.dice_type_id = 4
		   LEFT JOIN bb_lkp_block_dice_perm bdp2 ON t.prev_roll_value = bdp2.block_dice_perm_id AND rt.dice_type_id = 4
		   LEFT JOIN bb_lkp_reroll_type rr ON t.reroll_type_id = rr.reroll_type_id
		   LEFT JOIN bb_team ON t.team_id = bb_team.team_id
		   WHERE t.match_id = ?
		ORDER BY predicted_turn_no ASC
	");
	$turnover_sql->execute(array($_GET['match_id']));
  
	$turnover_data = $turnover_sql->fetchAll(PDO::FETCH_ASSOC);
  
	echo '<table class="dice-stats">';
	echo '<tr><th>Team</th><th>Turn end reason</th><th>Who</th><th>Detail</th></tr>'.PHP_EOL;
	foreach ($turnover_data AS $turnover_row) {
		echo '<tr><td>' . $turnover_row['team_name'] . '</td><td> ' . $turnover_row['turnover_type'] . '</td><td>';
		if (!is_null($turnover_row['player_id'])) {
			echo '<a href="player.php?player_id=' . $turnover_row['player_id'] . '">' . $turnover_row['player_name'] . '</a>';
		}
		else {
			echo $turnover_row['player_name'];
		}
		echo '</td><td>';
		echo $turnover_row['roll_detail'];
		if (!is_null($turnover_row['reroll_detail'])) {
			echo ". " . $turnover_row['reroll_detail'];
		}
		echo '</td></tr>'.PHP_EOL;
	}
	echo '</table>';
  
  ?>
  </div>
  <div id="tabs-6">
  <?php
  /*dl_path = "uploads/".$stat_row['filename'];
  if (file_exists($dl_path)) {
		echo "<ul><li>";
		echo '<a href="' . $dl_path . '">Download match replay</a>';
		echo "</li></ul>";
	}
	*/
	echo "<ul>";
	echo '<li><a href="download_match_file.php?match_id=' . $_GET['match_id'] . '&amp;dl_type=1">Download match replay</a></li>'.PHP_EOL;
	
	$sql = $conn->prepare("SELECT u.match_id
							FROM bb_upload u
							INNER JOIN staging_eventlog l ON u.upload_id = l.upload_id
							WHERE u.match_id = ?
							LIMIT 1");
	$sql->execute(array($_GET['match_id']));
	$check_match_id = $sql->fetchColumn(); 
	
	if ($check_match_id==$_GET['match_id']) {
		echo '<li><a href="viewrawlog.php?match_id=' . $_GET['match_id'] . '">View raw match log</a></li>'.PHP_EOL;
	}
	else {
		echo '<li>Cannot directly view match log file - please download the below zip file</a></li>'.PHP_EOL;
	}
	echo '<li><a href="download_match_file.php?match_id=' . $_GET['match_id'] . '&amp;dl_type=2">Download zip archive</a></li>'.PHP_EOL;
	echo "</ul>";
  ?>
  </div>
</div>


<p>Other stuff to be on this page... player skills/stats? - match log (v2), improved presentation?<p>.


<?php include_once("inc/footer.php"); ?>