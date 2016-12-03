<?php include_once("inc/header1.php");
include_once("inc/archive_functions.php");

if(!isset($_GET['match_id'])) {
	die("No match specified.");
}

if(!isset($_GET['dl_type'])) {
	die("Bad input.");
}

$match_id = $_GET['match_id'];

$dl_type = $_GET['dl_type'];

$check_code = $conn->prepare("SELECT domain_id FROM bb_match WHERE match_id = ?");
$check_code->execute(array($match_id));

$check_domain_id = $check_code->fetchColumn();

if (!($check_domain_id==$_SESSION['domain_id'])) {
	die ("You lack the ncessary permissions.");
}

if (!permission_check(4)) {
	die ("You lack the ncessary permissions.");	
}

if ((!$dl_type==1) && (!$dl_type==2)) {
	die("dl_type is invalid.");
}

if ($dl_type==1) {
	get_replay ($conn, $match_id, "dl"); //3rd parameter is download folder location
}
elseif ($dl_type==2) {
	get_zip_archive ($conn, $match_id, "dl"); //3rd parameter is download folder location
}
?>