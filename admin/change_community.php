<?php include_once("../inc/header1.php");

// if you need to return header() statements, do them here. SQL connection has already been established.

ini_set('display_errors', 'On');
ini_set('html_errors', 0);
error_reporting(-1);

include_once("../inc/header2.php"); ?>

BBDB</title> <?php
if (!permission_check(3)) { // Change domains
	include_once("../inc/header3.php"); 
	echo '<h2>Page not found</h2>';
	echo '<p>Page not found, or you lack the necessary permissions.</p>';
	include_once("../inc/footer.php");
	die();
}
?>
<?php // include anything else you want to put in <head> here.

include_once("../inc/header3.php"); 

if (!empty($_POST)): ?>
<h3>Change domains</h3>

<?php
if(!isset($_POST['domain_id'])) {
	echo "<p>You appear to have put some stupid data in. No action taken.</p>";
	include_once("../inc/footer.php");
	die();
}
$domain_id = (int) $_POST['domain_id'];
$validation_failure_reasons = array();

if (($domain_id<1) || ($domain_id > 255))  {$validation_failure_reasons[] = 'Invalid community ID.';}


if (count($validation_failure_reasons)>0) {
	echo '<p>Your inputs have failed validation for the following reasons:</p><ul>';
	foreach($validation_failure_reasons AS $fail) {
		echo "<li>$fail</li>".PHP_EOL;
	}
	echo '</ul>';
	include_once("../inc/footer.php");
	die;
}

// check that domain_id is valid


$sql = $conn->prepare("SELECT domain_id, description FROM bb_domain WHERE domain_id = ?");
$sql->execute(array($domain_id));
$data = $sql->fetch(PDO::FETCH_ASSOC);

if (count($data) > 0) { //ie we have returned some data

	$_SESSION['domain_id'] = $data['domain_id'];
	$_SESSION['domain'] = $data['description'];

	echo "<p>Community successfully changed! It will show on your next page load.</p>";
}
else {
	echo "<p>Problem with inputs. Community not changed.</p>";
}
else: ?>
<h3>Change domains</h3>

<form action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]); ?>" method="POST">
<table>
<tr><td>Community:</td>
<td><select name="domain_id"><?php
$sql = $conn->prepare("SELECT domain_id, description FROM bb_domain ORDER BY description");
$sql->execute();

$dataset = $sql->fetchAll(PDO::FETCH_ASSOC);

foreach($dataset AS $row) {
	echo '<option value="';
	echo $row['domain_id'];
	echo'">';
	echo $row['description'] . "</option>";
	echo PHP_EOL;
}
?>
</select></td></tr>
<tr><td></td>
<td><input type="submit" id="submit"></td></tr>
</table>
</form><? endif;


 include_once("../inc/footer.php"); ?>