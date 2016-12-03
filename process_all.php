<?php include_once("inc/header1.php");

// if you need to return header() statements, do them here. SQL connection has already been established.

include_once("inc/header2.php"); ?>

BBDB</title>

<?php // include anything else you want to put in <head> here.

include_once("inc/header3.php");

echo "<h2>Process all matches</h2>";

include_once("inc/bb1_transformation.php"); 
include_once("inc/stat_update.php");


$sql = $conn->prepare("SELECT u.upload_id, m.match_id, m.match_date FROM `bb_upload` u inner join bb_match m ON u.match_id = m.match_id ORDER BY m.match_date asc");

$sql->execute();
$upload_data = $sql->fetchAll(PDO::FETCH_ASSOC);
foreach ($upload_data as $row)
   {
	echo $row['match_id'];
	echo "<br/>";

	// $blah = $conn->prepare("DELETE FROM bb_player_match_stats WHERE match_id = ?");
	// $blah->bindParam(1, $row['match_id'], PDO::PARAM_INT);
	// $blah->execute();

	// doTransformation($conn, $row['upload_id']);
	// echo "transformations done.</br>";

	// update_league_table($conn, $row['upload_id'], 2);
	update_star_player_record($conn, $row['upload_id']);
	echo "league table and star player records updated.</br>";
   }

include_once("inc/footer.php"); ?>