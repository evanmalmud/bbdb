<?php include_once("inc/header1.php");

// if you need to return header() statements, do them here. SQL connection has already been established.

include_once("inc/header2.php"); ?>

BBDB</title>

<?php // include anything else you want to put in <head> here.

include_once("inc/header3.php"); ?>



<h2>Team Global Stats</h2>

<p>Here are some numbers regarding all the teams in BBDB, across the different communities that have been set up. More data will be added in the future (eg comparing different races, impact of team value on match results etc); for now this is basically a page to show that the website is in use and maybe to make you go "wow those are big numbers!"</p>

<?php
	$sql = $conn->prepare("SELECT COUNT(*) FROM bb_team");
	$sql->execute();
	$team_count = $sql->fetchColumn();
	
	$sql = $conn->prepare("SELECT COUNT(*) AS cnt, MAX(match_date) AS last_match
							, SUM(home_touchdown_count + away_touchdown_count) AS td_total
							FROM bb_match");
	$sql->execute();
	$match_data = $sql->fetch(PDO::FETCH_ASSOC);
	
	$sql = $conn->prepare("SELECT COUNT(*) AS cnt, SUM(experience) AS sum_xp
							, SUM(inflicted_injury) AS inflicted_cas
							FROM bb_player");
	$sql->execute();
	$player_data = $sql->fetch(PDO::FETCH_ASSOC);
?>
<table>
<tr><th>Statistic</th><th>Value</th></tr>
<?php
	echo "<tr><td>Number of teams</td><td>" . $team_count . "</td></tr>".PHP_EOL;
	echo "<tr><td>Number of matches</td><td>" . $match_data['cnt'] . "</td></tr>".PHP_EOL;
	echo "<tr><td>Most recent match date</td><td>" . $match_data['last_match'] . "</td></tr>".PHP_EOL;
	echo "<tr><td>Touchdowns scored</td><td>" . $match_data['td_total'] . "</td></tr>".PHP_EOL;
	echo "<tr><td>Number of players</td><td>" . $player_data['cnt'] . "</td></tr>".PHP_EOL;
	echo "<tr><td>Total amount of XP</td><td>" . $player_data['sum_xp'] . "</td></tr>".PHP_EOL;
	echo "<tr><td>Injuries inflicted</td><td>" . $player_data['inflicted_cas'] . "</td></tr>".PHP_EOL;
	echo "</table>".PHP_EOL;
	
include_once("inc/footer.php"); ?>