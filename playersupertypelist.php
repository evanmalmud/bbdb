<?php include_once("inc/header1.php");

// if you need to return header() statements, do them here. SQL connection has already been established.

include_once("inc/header2.php"); ?>

BBDB</title>

<script>
$(document).ready(function() 
    { 
        $("#playerSuperTypeTable").tablesorter(); 
    } 
); 
</script>
<?php // include anything else you want to put in <head> here.

include_once("inc/header3.php"); ?>



<h2>Player super-types</h2>



<p>A player type is specific to a team. For example, an Undead Ghoul, a Human Lineman and so on. However these can be further grouped up. Some by the type of creature they are (eg Treemen appear in Halflings and Wood Elf teams) or by their position (Blitzer, Thrower etc.). These are what are called super-types.</p>

<p>Via this page you can answer the question who is the best skeleton ever? The best line-elf? Which teams have access to Goblins? And other such questions.</p>

<table id="playerSuperTypeTable" class="tablesorter">
<thead>
<tr><th>Description</th><th>Race Count</th></tr>
</thead>
<tbody>
<?php
// GROUP_CONCAT should be fine but doesn't give click-through or hover-over functionality. So think of this as a short-term measure

$stmt=$conn->prepare("
	SELECT pst.player_supertype_id, pst.description, COUNT(*) as type_count
	FROM bb_lkp_player_supertype pst
	INNER JOIN bb_lkp_player_type pt ON pst.player_supertype_id = pt.player_supertype_id
	GROUP BY pst.player_supertype_id, pst.description

	ORDER BY COUNT(*) DESC");
$stmt->execute();
$player_types = $stmt->fetchAll(PDO::FETCH_ASSOC);

 foreach ($player_types as $row)
   {
	echo "<tr>";
	echo '<td><a href="playersupertype.php?player_supertype_id=' . $row['player_supertype_id'] . '">'. $row['description'] . '</a></td>'.PHP_EOL;

	echo '<td>'. $row['type_count'] . '</td>'.PHP_EOL;
	echo '</tr>';

   }
?>
</tbody>
</table>





<?php include_once("inc/footer.php"); ?>