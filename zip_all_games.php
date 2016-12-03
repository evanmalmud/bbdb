<?php include_once("inc/header1.php");
include_once("inc/archive_functions.php");


// write something to loop through all uploads

zip_saved_game ($conn, $match_id, FALSE); //3rd parameter is "do we delete originals"