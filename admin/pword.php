<?php include_once("../inc/header1.php");

// if you need to return header() statements, do them here. SQL connection has already been established.

ini_set('display_errors', 'On');
ini_set('html_errors', 0);
error_reporting(-1);

include_once("../inc/header2.php"); ?>

BBDB</title>

<?php // include anything else you want to put in <head> here.

include_once("../inc/header3.php"); 
include_once("../inc/stat_update.php");

/**
 * This code will benchmark your server to determine how high of a cost you can
 * afford. You want to set the highest cost that you can without slowing down
 * you server too much. 8-10 is a good baseline, and more is good if your servers
 * are fast enough. The code below aims for ≤ 50 milliseconds stretching time,
 * which is a good baseline for systems handling interactive logins.
 */
$timeTarget = 0.15; // 50 milliseconds 

$cost = 8;
do {
    $cost++;
    $start = microtime(true);
    password_hash("test", PASSWORD_BCRYPT, ["cost" => $cost]);
    $end = microtime(true);
} while (($end - $start) < $timeTarget);

echo "Appropriate Cost Found: " . $cost . "\n";


 include_once("../inc/footer.php"); ?>