<?php include_once("inc/header1.php");

// if you need to return header() statements, do them here. SQL connection has already been established.

include_once("inc/header2.php"); ?>

BBDB</title>

<?php // include anything else you want to put in <head> here.

include_once("inc/header3.php"); ?>



<h2>Standing Data Viewer</h2>

<?php

$display_table = 1;

// datatypeid
// 1 = skills. 2 = skills for a given category. 3 = race list. 4 = playertype list. 5 = player levels. 6 = dice rolls

if(!isset($_GET['datatypeid']))
{
   echo '<form action = "' . htmlspecialchars($_SERVER["PHP_SELF"]) . '" method="get">';
   echo '<input type="hidden" name="datatypeid" value="1">';
   echo '<input type="submit" value="Skills">';
   echo "</form>".PHP_EOL;
   $display_table = 0;
}
elseif($_GET['datatypeid']==1)
{
	$table_data = $conn->prepare("SELECT CONCAT('<a href=skill.php?skill_id=', s.skill_id, '>', s.human_desc, '</a>'), c.skill_category
					, CASE WHEN LENGTH(s.long_description) <= 160 THEN s.long_description
						WHEN s.long_description IS NULL THEN NULL
						ELSE CONCAT(SUBSTR(s.long_description, 1, 150), '...') END AS description
		FROM bb_lkp_skill s INNER JOIN bb_lkp_skill_category c ON s.skill_category_id = c.skill_category_id
		ORDER BY s.human_desc ASC");

	$input_array = array();

	echo "
<table>";
	echo '<tr><th>ID</th><th>Skill name</th><th>Skill Category</th><th>Description</th></tr>'.PHP_EOL;

}
elseif($_GET['datatypeid']==2) //all skills in a given category
{
	if(($_GET['categoryid'] >=0) && ($_GET['categoryid'] <=7)) {

		$table_data = $conn->prepare("SELECT CONCAT('<a href=skill.php?skill_id=', s.skill_id, '>', s.human_desc, '</a>'), c.skill_category
						, CASE WHEN LENGTH(s.long_description) <= 160 THEN s.long_description
							WHEN s.long_description IS NULL THEN NULL
							ELSE CONCAT(SUBSTR(s.long_description, 1, 150), '...') END AS description 
						FROM bb_lkp_skill s 
						INNER JOIN bb_lkp_skill_category c ON s.skill_category_id = c.skill_category_id
						WHERE s.skill_category_id = ?
						ORDER BY s.human_desc");

		$input_array = array($_GET['categoryid']);

		echo "
<table>";
		echo '<tr><th>Skill name</th><th>Skill Category</th><th>Description</th></tr>'.PHP_EOL;
	}
	else {
		die('That is not right.');
	}
}
elseif($_GET['datatypeid']==3) {
	$table_data = $conn->prepare("SELECT CONCAT('<a href=race.php?race_id=', r.race_id, '>', r.description, '</a>'), r.reroll_price, r.short_description
					, CASE WHEN bb1_id IS NULL THEN 'No' ELSE 'Yes' END as in_bb1
		FROM bb_lkp_race r
		ORDER BY r.description");

	$input_array = array();

	echo "
<table>";
	echo '<tr><th>Race Name</th><th>Reroll cost</th><th>Abbr</th><th>BB1</th></tr>'.PHP_EOL;

}
elseif($_GET['datatypeid']==5) {
	$table_data = $conn->prepare("SELECT spp_level, description, limit_spp
		FROM bb_lkp_spp_levels s
		ORDER BY spp_level ASC");

	$input_array = array();

	echo "
<table>";
	echo '<tr><th>Level</th><th>Level Name</th><th>SPP to be promoted</th></tr>'.PHP_EOL;

}
elseif($_GET['datatypeid']==6) {
	$table_data = $conn->prepare("SELECT rt.description, dt.description, ra.description
			, roll_modifier, ft.description, CASE WHEN optional_modifier_flag = 1 THEN 'Yes' ELSE 'No' END
			, sk.human_desc
			, rt.modify_desc
		FROM bb_lkp_roll_type rt
		LEFT JOIN bb_lkp_dice_type dt ON rt.dice_type_id = dt.dice_type_id
		LEFT JOIN bb_lkp_roll_aim ra ON rt.roll_aim_id = ra.roll_aim_id
		LEFT JOIN bb_lkp_fail_turnover ft ON rt.fail_turnover_id = ft.fail_turnover_id
		LEFT JOIN bb_lkp_skill sk ON sk.skill_id = rt.reroll_skill_id
		ORDER BY rt.description");

	$input_array = array();

	echo "
<table>";
	echo '<tr><th>Roll name</th><th>Dice</th><th>Roll target</th><th>Mod</th><th>Fail = turnover</th><th>Optional modifiers</th><th>Reroll skill</th><th>Modifier description</th></tr>'.PHP_EOL;

}
// put any other valid id's here!
else
{
die("INVALID INPUT DETECTED");
}

if ($display_table == 1)
{
?>




<?php
   // Generic code to output a generic table with an unspecified number of columns
   $table_data->execute($input_array);
   $rows = $table_data->fetchAll(PDO::FETCH_NUM);
   foreach ($rows as $row)
   {
	echo "<tr>";
	foreach ($row AS $cell) {
		echo '<td>' . $cell . '</td>' . PHP_EOL;
	}
	echo "</tr>".PHP_EOL;
   }
?>
</table>





<?php
} // end of "display table" code
 include_once("inc/footer.php"); ?>