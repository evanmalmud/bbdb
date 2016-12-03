<?php include_once("inc/header1.php");

// if you need to return header() statements, do them here. SQL connection has already been established.

include_once("inc/header2.php"); ?>

BBDB</title>

<script>
$(document).ready(function() 
    { 
        $("competitionTable").tablesorter(); 
    } 
); 
</script>
<?php // include anything else you want to put in <head> here.

if ((!permission_check(4)) || (!isset($_SESSION['domain_id']))) { // standard read privilages
	include_once("inc/no_permission.php");
}


include_once("inc/header3.php"); 

?>



<h2>Competitions</h2>


<table id="competitionTable" class="tablesorter">
<thead>
<tr><th>Competition name</th><th>Finished</th><th>First match</th><th>Last match</th><th title="Matches Played">P</th><th>Teams</th></tr>
</thead><tbody>
<?php
	$this_domain_id = $_SESSION['domain_id'];

    $sql=$conn->prepare("SELECT c.competition_id
				, c.description AS competition_name
				, c.short_description
				, CASE WHEN c.completed = 1 THEN 'Yes' ELSE 'No' END AS completed
				, ct.description AS competition_type
				, MIN(UNIX_TIMESTAMP(m.match_date)) as earliest_match_date
				, MAX(UNIX_TIMESTAMP(m.match_date)) as latest_match_date
				, COUNT(DISTINCT m.match_id) as match_count
				, COUNT(DISTINCT t.team_id) as team_count
			FROM bb_competition c
				INNER JOIN bb_match_competition AS mc ON c.competition_id = mc.competition_id AND c.domain_id = mc.domain_id
				INNER JOIN bb_match m ON mc.match_id = m.match_id
				INNER JOIN bb_lkp_competition_type ct ON c.competition_type_id = ct.competition_type_id
				INNER JOIN bb_team t ON t.team_id IN (m.home_team_id, m.away_team_id)
			WHERE c.domain_id = ?
			AND c.competition_type_id <> 0
			GROUP BY c.competition_id
			ORDER BY MAX(UNIX_TIMESTAMP(m.match_date)) DESC");

  $sql->execute(array($this_domain_id));
   $competitions = $sql->fetchAll(PDO::FETCH_ASSOC);
   foreach ($competitions as $row)
   {
	echo "<tr>";
	echo '<td><a href="competition.php?competition_id=' . $row['competition_id'] . '">'. $row['competition_name'] . '</a></td>'.PHP_EOL;
	echo '<td>'. $row['completed'] . '</td>'.PHP_EOL;
	echo '<td>'. date('M j, Y h:i A', $row['earliest_match_date']) . '</td>'.PHP_EOL;
	echo '<td>'. date('M j, Y h:i A', $row['latest_match_date']) . '</td>'.PHP_EOL;
	echo '<td>'. $row['match_count'] . '</td>'.PHP_EOL;
	echo '<td>'. $row['team_count'] . '</td>'.PHP_EOL;
	echo "</tr>";
   }


?>
</tbody></table>


<p>To do.... enhance page & information generally. Specific suggestions welcome!</p>



<?php include_once("inc/footer.php"); ?>