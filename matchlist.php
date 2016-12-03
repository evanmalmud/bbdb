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

$filter_text = "";
if (!isset($_GET['competition_id'])) { $p_competition_id = 0; }
	else {
		$p_competition_id = $_GET['competition_id'];
		$competition_sql = $conn->prepare("SELECT c.*, t.description as competition_type FROM bb_competition c
						INNER JOIN bb_lkp_competition_type t ON c.competition_type_id = t.competition_type_id
					WHERE competition_id = ?
					AND domain_id = ?");
		$competition_sql->bindParam(1, $p_competition_id, PDO::PARAM_INT);
		$competition_sql->bindParam(2, $_SESSION['domain_id'], PDO::PARAM_INT);
		$competition_sql->execute();
		$competition = $competition_sql->fetch(PDO::FETCH_ASSOC);
		if(!$competition) {
			die("I cannot find that competition.");
		}

		$filter_text .= "; " . $competition['description'] . "";
	}

if (substr($filter_text,1)==";") { $filter_text = substr($filter_text, 2, 0);} // remove last character
?>


<h2>Matches</h2>




<p>DO you want MOAR STATS?! Then why not try the <a href="matchlistadv.php">Advanced Match List</a>?? Mr. Savage would be proud!</p>
<?php
	if ($filter_text <> "") { echo "<p><strong>FILTER:</strong> $filter_text</p>".PHP_EOL; }
?>
<table id="matchTable" class="tablesorter">
<thead>
<tr><th>ID</th><th>Date</th><th title="Default Competition">Competition</th><th>Home Race</th><th>Home Coach</th><th>Home Team</th><th>TD1</th><th>TD2</th><th>Away Team</th><th>Away Coach</th><th>Away Race</th><th title="Match rating out of 20">MR</th></tr>
</thead>
<tbody>
<?php
   $sql = $conn->prepare("SELECT m.match_id
				, hr.description AS home_race
				, COALESCE(hc.description, '???') AS home_coach
				, ht.description as home_team
				, m.home_touchdown_count
				, m.away_touchdown_count
				, ar.description AS away_race
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
				, m.rating
		FROM bb_match m
		INNER JOIN bb_team ht ON m.home_team_id = ht.team_id
		INNER JOIN bb_team at ON m.away_team_id = at.team_id
		LEFT JOIN bb_coach hc ON ht.coach_id = hc.coach_id
		LEFT JOIN bb_coach ac ON at.coach_id = ac.coach_id
		INNER JOIN bb_lkp_race hr ON ht.race_id = hr.race_id
		INNER JOIN bb_lkp_race ar ON at.race_id = ar.race_id
		LEFT JOIN bb_match_competition mc ON m.match_id = mc.match_id AND mc.default_competition = 1
		LEFT JOIN bb_competition comp ON mc.competition_id = comp.competition_id AND m.domain_id = comp.domain_id
		WHERE (? = 0 OR mc.competition_id = ?)
		AND m.domain_id = ?
 ORDER BY m.match_date DESC");
   $sql->bindParam(1, $p_competition_id, PDO::PARAM_INT);
   $sql->bindParam(2, $p_competition_id, PDO::PARAM_INT);
   $sql->bindParam(3, $_SESSION['domain_id'], PDO::PARAM_INT);

   $sql->execute();
   $match_data = $sql->fetchAll(PDO::FETCH_ASSOC);
   foreach ($match_data as $row)
   {
	echo "<tr>";
	echo '<td><a href="matchdetail.php?match_id=' . $row['match_id'] . '">'. $row['match_id'] . '</a></td>'.PHP_EOL;
	echo '<td>'. date('M j, Y h:i A', $row['match_date']) . '</td>'.PHP_EOL;
	echo '<td><a href="competition.php?competition_id=' . $row['competition_id'] . '">'. $row['competition_name'] . '</a></td>'.PHP_EOL;
	echo '<td><a href="race.php?race_id=' . $row['home_race_id'] . '">'. $row['home_race'] . '</a></td>'.PHP_EOL;
	echo '<td><a href="coach.php?coach_id=' . $row['home_coach_id'] . '">'. $row['home_coach'] . '</a></td>'.PHP_EOL;
	echo '<td><a href="team.php?team_id=' . $row['home_team_id'] . '">'. $row['home_team'] . '</a></td>'.PHP_EOL;

	echo '<td>'. $row['home_touchdown_count'] . '</td>'.PHP_EOL;
	echo '<td>'. $row['away_touchdown_count'] . '</td>'.PHP_EOL;

	echo '<td><a href="team.php?team_id=' . $row['away_team_id'] . '">'. $row['away_team'] . '</a></td>'.PHP_EOL;
	echo '<td><a href="coach.php?coach_id=' . $row['away_coach_id'] . '">'. $row['away_coach'] . '</a></td>'.PHP_EOL;
	echo '<td><a href="race.php?race_id=' . $row['away_race_id'] . '">'. $row['away_race'] . '</a></td>'.PHP_EOL;
	echo '<td>'. $row['rating'] . '</td>'.PHP_EOL;
	echo "</tr>".PHP_EOL;
   }
?>
</tbody>
</table>



<?php include_once("inc/footer.php"); ?>