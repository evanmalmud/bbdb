<?php include_once("inc/header1.php");

// if you need to return header() statements, do them here. SQL connection has already been established.

include_once("inc/header2.php"); ?>

BBDB</title>

<?php // include anything else you want to put in <head> here.

include_once("inc/header3.php"); ?>



<h2>Player Awards</h2>

<h3>Seasonal awards</h3>

<?php
$sql = $conn->prepare("
SELECT concat(t.description, ' Award for ', t.measured_value) AS award_waffle
		, a.award_type_id
		, GROUP_CONCAT(l.position ORDER BY l.award_level_id DESC SEPARATOR ',' ) AS award_levels
FROM bb_lkp_award a
INNER JOIN bb_lkp_award_type t ON a.award_type_id = t.award_type_id
INNER JOIN bb_lkp_award_level l ON a.award_level_id = l.award_level_id
WHERE t.award_category_id = 1
GROUP BY concat(t.description, ' for ', t.measured_value)
		, a.award_type_id
ORDER BY concat(t.description, ' for ', t.measured_value)");

$sql->execute();
$dataset = $sql->fetchAll(PDO::FETCH_ASSOC);

echo '<ul>';
foreach($dataset AS $row) {
	echo '<li><a href="award_type.php?award_type_id=' . $row['award_type_id'] . '">' . $row['award_waffle'] . '</a> (' . $row['award_levels'] . ')</li>'.PHP_EOL;
	
}
echo '</ul>';
?>
<h3>Longevity awards - Gold, Silver and Bronze awards for reaching the following milestones...</h3>
<ul><li>XP (176, 76, 51)</li>
<li>Played (60, 40, 20)</li>
<li>MVP (7,5,3)</li>
<li>TD (35, 20, 10)</li>
<li>Passes (40, 20, 10)</li>
<li>Casualties (35, 20, 10)</li>
<li>KDF (100, 60, 35)</li>
<li>KDA (55, 40, 25)</li>
<li>INJ (10, 7, 5)</li>
<li>K (6, 4, 2)</li>
<li>MR (1000, 600, 300)</li>
<li>MP (400, 200, 100)</li>
</ul>

<h3>Other - awarded for incredible individual feats, such as...</h3>
<ul><li>Scoring 3 TD's in a game</li>
<li>Scoring for 5 consecutive matches</li>
<li>reaching level 3 or 4 in a short number of matches</li>
</ul>

<h3>"Other" is not yet in development scope - a feature for the future.</h3>


<?php include_once("inc/footer.php"); ?>
