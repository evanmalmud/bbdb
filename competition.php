<?php include_once("inc/header1.php");

// if you need to return header() statements, do them here. SQL connection has already been established.

include_once("inc/header2.php"); ?>

BBDB</title>

<script>
$(document).ready(function() 
    { 
        $("#leagueTable").tablesorter(); 
    } 
); 
    
</script>
<script src="https://www.google.com/jsapi"></script>
<?php // include anything else you want to put in <head> here.

if (permission_check(6)) { // Refresh tables?>
	<script>
	function refreshLeagueData() {
		$("#do_calc_msg").html("Calculating...");
		
		var request = $.ajax({
			url: "inc/refresh_league_data.php",
			type: "POST",			
			dataType: "html",
			data: "competition_id=<?php echo $_GET['competition_id']; ?>"
		});

		request.done(function(msg) {
			$("#do_calc_msg").html(msg);			
		});

		request.fail(function(jqXHR, textStatus, errorThrown) {
			alert( "Request failed: " + textStatus );
			$("#do_calc_msg").html("Failed!");
			console.error(
            "The following error occurred: "+
            textStatus, errorThrown
			);
		});
	}
	</script>
<?php }
if (permission_check(7)) { // Close a competition. Currently this just does player awards. Need to be extended to team awards, & setting competition as closed.
?>
	<script>
	function closeLeague() {
		$("#close_league_msg").html("Calculating...");
		
		var request = $.ajax({
			url: "inc/assign_player_awards.php",
			type: "POST",			
			dataType: "html",
			data: "competition_id=<?php echo $_GET['competition_id']; ?>"
		});

		request.done(function(msg) {
			$("#close_league_msg").html(msg);			
		});

		request.fail(function(jqXHR, textStatus, errorThrown) {
			alert( "Request failed: " + textStatus );
			$("#close_league_msg").html("Failed!");
			console.error(
            "The following error occurred: "+
            textStatus, errorThrown
			);
		});
	}
	</script>
<?php }

if ((!permission_check(4)) || (!isset($_SESSION['domain_id']))) { // standard read privilages
	include_once("inc/no_permission.php");
}

include_once("inc/header3.php"); 
	$domain_id = $_SESSION['domain_id'];

	$competition_sql = $conn->prepare("SELECT c.*, t.description as competition_type FROM bb_competition c
						INNER JOIN bb_lkp_competition_type t ON c.competition_type_id = t.competition_type_id
					WHERE competition_id = ?
					AND domain_id = ?
					AND c.competition_type_id <> 0");

	$competition_sql->bindParam(1, $_GET['competition_id'], PDO::PARAM_INT);
	$competition_sql->bindParam(2, $domain_id, PDO::PARAM_INT);
	$competition_sql->execute();
	$competition = $competition_sql->fetch(PDO::FETCH_ASSOC);

	if(!$competition) {
		include_once("inc/header3.php");
		echo "<p>I cannot find that competition.</p>";
		include_once("inc/footer.php");
		die();
	}



echo "<h2>".$competition['description']."</h2>".PHP_EOL;

echo "<p>This is a ".$competition['competition_type'].".</p>";

if (permission_check(6)) { // Refresh tables
	echo '<table><tr><td><input type="button" value="Refresh standings and stats" onclick="refreshLeagueData();"/></td>
			<td id="do_calc_msg"></td></tr></table>';
	
}
if (permission_check(7)) { // Close a competition. Currently only does player awards, which is reflected in the button text.
	echo '<table><tr><td><input type="button" value="Dish out player awards" onclick="closeLeague();"/></td>
			<td id="close_league_msg"></td></tr></table>';
	
}



?>
<h2>Table</h2>


<p>To do.... work to be able to deal with more than one league table in a competition(v3?)</p>

<table id="leagueTable" class="tablesorter">
<thead>
<tr>
<th>Rank</th><th>Team</th><th>Coach</th><th>Race</th><th>Pts</th><th>P</th><th>W</th><th>D</th><th>L</th><th>F</th><th>A</th><th title="Touchdown Difference">Df</th>
<th title="Passes">PS</th><th title="casualties inflicted">CASF</th><th title="casualties sustained">CASA</th></tr>
</thead>
<tbody>
<?php

$league_sql = $conn->prepare("
	SELECT r.*
		, t.description as team_name
		, c.coach_id
		, c.description as coach_name
		, race.race_id
		, race.description as race_name
		, tb.table_name
		, ss.description AS scoring_system
	FROM bb_stat_comp_table_rank r
	INNER JOIN bb_team t ON r.team_id = t.team_id
	INNER JOIN bb_lkp_race race ON t.race_id = race.race_id
	LEFT JOIN bb_coach c ON t.coach_id = c.coach_id
	INNER JOIN bb_stat_comp_table tb ON r.domain_id = tb.domain_id
					AND r.competition_id = tb.competition_id
					AND r.group_id = tb.group_id
	LEFT JOIN bb_lkp_scoring_system ss ON tb.scoring_system_id = ss.scoring_system_id
	WHERE r.domain_id = ?
	AND r.competition_id = ?
	");
$league_sql->bindParam(1, $domain_id, PDO::PARAM_INT);
$league_sql->bindParam(2, $competition['competition_id'], PDO::PARAM_INT);
$league_sql->execute();
$league_table = $league_sql->fetchAll(PDO::FETCH_ASSOC);

$casualty_list = array();

foreach ($league_table as $row)
   {
	$scoring_system = $row['scoring_system'];
	   
	echo "<tr>";
	echo '<td>'. $row['rank'] . '</td>'.PHP_EOL;
	echo '<td><a href="team.php?team_id=' . $row['team_id'] . '">'. $row['team_name'] . '</a></td>'.PHP_EOL;
	echo '<td><a href="coach.php?coach_id=' . $row['coach_id'] . '">'. $row['coach_name'] . '</a></td>'.PHP_EOL;
	echo '<td><a href="race.php?race_id=' . $row['race_id'] . '">'. $row['race_name'] . '</a></td>'.PHP_EOL;

	echo '<td>'. $row['points'] . '</td>'.PHP_EOL;
	echo '<td>'. $row['played'] . '</td>'.PHP_EOL;
	echo '<td>'. $row['wins'] . '</td>'.PHP_EOL;
	echo '<td>'. $row['draws'] . '</td>'.PHP_EOL;
	echo '<td>'. $row['losses'] . '</td>'.PHP_EOL;
	echo '<td>'. $row['touchdown_scored'] . '</td>'.PHP_EOL;
	echo '<td>'. $row['touchdown_conceded'] . '</td>'.PHP_EOL;
	echo '<td>'. $row['touchdown_diff'] . '</td>'.PHP_EOL;
	echo '<td>'. $row['passes'] . '</td>'.PHP_EOL;
	echo '<td>'. $row['casualties'] . '</td>'.PHP_EOL;
	echo '<td>'. $row['casualties_sustained'] . '</td>'.PHP_EOL;

	echo "</tr>".PHP_EOL;

	$temp = array();
	$temp[] = array('v' => (string) $row['team_name']);
	$temp[] = array('v' => (int) $row['casualties']);
	if ($row['casualties']==0)
		{ $estimated_future_casualties = 0; }
	else {
		$estimated_future_casualties = (1- ($row['played']/(count($league_table)-1))) * $row['casualties'];
	}
	$temp[] = array('v' => (int) $estimated_future_casualties);
	$casualty_list[] = array('c' => $temp);
   }

  $unsorted = $casualty_list;

// sort array
// Custom function - sort first by actual value, and if a tie, then predicted to-come value
   function mysort($a, $b) {
	if ($a['c'][1] > $b['c'][1]) {
	    return -1;
	}
	if ($a['c'][1] < $b['c'][1]) {
	    return 1;
	}
	if ($a['c'][2] > $b['c'][2]) {
	    return -1;
	}
	if ($a['c'][2] < $b['c'][2]) {
	    return 1;
	}
	return 0;
   }
   uasort($casualty_list, 'mysort');


// jsonify array
    $stat_bar_chart_array = array();
	$stat_bar_chart_array['cols'] = array(array('label' => 'Team', 'type' => 'string'),
					 array('label' => 'Casualties',' type' => 'number'),
					 array('label' => 'Predicted future casualties', 'type' => 'number')
					);

// array_values will reindex from zero (after usort moved the keys around). They need to be zero-indexed for google charts to do its thing.
   $stat_bar_chart_array['rows']=array_values($casualty_list);

   $bar_chart_data = json_encode($stat_bar_chart_array);


echo '</tbody></table><p>Scoring System : ' . $scoring_system .'.</p>'.PHP_EOL;

?>

<h3>Links... <a href="matchlist.php?competition_id=<?php echo $competition['competition_id']; ?>">complete list of fixtures</a>,
 <a href="competition_team_leaderboards.php?competition_id=<?php echo $competition['competition_id']; ?>">team charts</a>
 , and <a href="competition_player_leaderboards.php?competition_id=<?php echo $competition['competition_id']; ?>">player charts</a>.</h3>

<h3>If you like walls of numbers to do with dice rolls, check out the <a href="competition_player_dice.php?competition_id=<?php echo $competition['competition_id']; ?>">Dice rolls per player</a>
   or the <a href="competition_team_dice.php?competition_id=<?php echo $competition['competition_id']; ?>">Dice rolls per team</a>.</h3>

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
	      title: 'Casualties caused in season',
              width: 800,
              height: 600,
	      legend : {position: 'none'},
	      isStacked : true,
	      series: [{color:'red'}, {color:'black'}]
            };

          // Instantiate and draw our chart, passing in some options.
          // Do not forget to check your div ID
          var chart = new google.visualization.BarChart(document.getElementById('chart_div'));
          chart.draw(data, options);
      }
</script>


<div id="chart_div"></div>



<?php
 include_once("inc/footer.php"); ?>