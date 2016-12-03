<?php include_once("inc/header1.php");

// if you need to return header() statements, do them here. SQL connection has already been established.

include_once("inc/header2.php"); ?>

BBDB - upload</title>

<?php // include anything else you want to put in <head> here.

if (!permission_check(1)) { // Upload match
	include_once("../inc/header3.php"); 
	echo '<h2>Page not found</h2>';
	echo '<p>Page not found, or you lack the necessary permissions.</p>';
	include_once("../inc/footer.php");
	die();
}
include_once("inc/header3.php"); ?>

<h1>Upload page</h1>

<p>Please note, <strong>you can only upload the most recent game that you have played or watched in the Blood Bowl Cyanide client</strong>. If you want to upload an older game, you need to re-watch the game and then upload it.</p>

<p><strong>For information regarding the location of these files, hover over the questions.</strong></p>

<form action="doupload.php" method="post" enctype="multipart/form-data">
<table><tr><td title="Can be found in c:\Users\YOURUSERNAME\Documents\BloodBowlLegendary\Saves\Replays">
    Select replay file:
</td><td>
    <input type="file" name="replay_file" id="replay_file">
</td></tr><tr><td title="Can be found in c:\Users\YOURUSERNAME\Documents\BloodBowlLegendary">
Select the MatchReport.sqlite file:
</td><td>
    <input type="file" name="matchreport" id="matchreport">
</td></tr><tr><td title="Can be found in c:\Users\YOURUSERNAME\Documents\BloodBowlLegendary">
Select the BB_LE000.log file:
</td><td>
    <input type="file" name="txtfile" id="txtfile">
</td></tr><tr><td title="Can be found in c:\Users\YOURUSERNAME\Documents\BloodBowlLegendary. You won't always have one">
Select the BB_LE001.log file:
</td><td>
    <input type="file" name="txtfile2" id="txtfile2">
</td></tr><tr><td title="Can be found in c:\Users\YOURUSERNAME\Documents\BloodBowlLegendary. You won't always have one">
Select the BB_LE002.log file:
</td><td>
    <input type="file" name="txtfile3" id="txtfile3">
</td></tr><tr><td>
    Brief description of the upload:
</td><td>
    <input type="text" name = "comment">
</td></tr><tr><td>
    Community:
</td><td>
    <?php echo $_SESSION['domain']; ?>
</td></tr><tr><td>
    Competition: league/cup/friendly
</td><td>
    <select name="competition">
<?php
	$sql=$conn->prepare("SELECT c.competition_id, c.description, MAX(COALESCE(m.match_date, '31 Dec 9999')) z FROM bb_competition c
				LEFT JOIN bb_match_competition mc ON c.competition_id = mc.competition_id AND mc.domain_id = c.domain_id
				LEFT JOIN bb_match m ON mc.match_id = m.match_id
				WHERE c.completed = 0 AND c.short_description <> 'TEST'
				AND c.domain_id = ?
				GROUP BY c.competition_id
				ORDER BY MAX(COALESCE(m.match_date, '31 Dec 9999')) DESC");

	$sql->execute(array($_SESSION['domain_id']));

	$competition_types = $sql->fetchAll(PDO::FETCH_ASSOC);

 foreach ($competition_types as $row)
   {
	echo '<option value = "' . $row['competition_id'] . '">' . $row['description'] . '</option>' . PHP_EOL;

   }
?>
    </select>
</td></tr><tr><td colspan="2">
    <input type="submit" value="Upload Report" name="submit">
</td></tr>
</table>
</form>

<br/>



<?php include_once("inc/footer.php"); ?>
