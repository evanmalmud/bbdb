<?php include_once("inc/header1.php");

// if you need to return header() statements, do them here. SQL connection has already been established.

include_once("inc/header2.php"); ?>

BBDB</title>

<?php // include anything else you want to put in <head> here.

include_once("inc/header3.php"); 

$match_id = $_GET['match_id'];

$sql = $conn->prepare(
	"SELECT l.raw_text
	FROM bb_upload u
	INNER JOIN staging_eventlog l ON u.upload_id = l.upload_id
	WHERE u.match_id = ?
	ORDER BY l.line_no ASC");

$sql->execute(array($match_id));



echo '<h2>Match log - <a href="matchdetail.php?match_id=' . $match_id . '">Match ID ' . $match_id . '</a></h2>'.PHP_EOL;

echo '<p>'.PHP_EOL;

while ($row = $sql->fetch(PDO::FETCH_ASSOC)) {
	echo $row['raw_text'] . '<br/>'.PHP_EOL;
}

echo '</p>';

 include_once("inc/footer.php"); ?>
