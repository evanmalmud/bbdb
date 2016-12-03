<?php include_once("inc/header1.php");

// if you need to return header() statements, do them here. SQL connection has already been established.

include_once("inc/header2.php"); ?>

Skill</title>

<?php // include anything else you want to put in <head> here.

include_once("inc/header3.php"); 
$check_stmt = $conn->prepare("SELECT s.*, sc.skill_category FROM bb_lkp_skill s
					INNER JOIN bb_lkp_skill_category sc ON s.skill_category_id = sc.skill_category_id
					WHERE skill_id = ?");

	$check_stmt->bindParam(1, $_GET['skill_id'], PDO::PARAM_INT);
	$check_stmt->execute();

	$skill = $check_stmt->fetch(PDO::FETCH_ASSOC);

	if(!$skill) {
		die("I cannot find that skill.");
	}
echo "<h2>Skill Details - " . $skill['human_desc'] . "</h2>".PHP_EOL;

echo "<h3>Category - " . $skill['skill_category'] . "</h3>".PHP_EOL;

echo "
<h3>Description</h3>".PHP_EOL;

echo "<p>" . $skill['long_description'] . "</p>";
?>

<p>Todo.... which players start with that skill, how many players (and of what sort?) have been given that skill.</p>




<?php include_once("inc/footer.php"); ?>