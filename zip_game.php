<?php include_once("inc/header1.php");
include_once("inc/archive_functions.php");

if(!isset($_GET['match_id'])) {
	die("No match specified.");
}

$match_id = $_GET['match_id'];

zip_saved_game ($conn, $match_id, FALSE); //3rd parameter is "do we delete originals"