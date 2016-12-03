<?php include_once("inc/header1.php");

// if you need to return header() statements, do them here. SQL connection has already been established.

//ini_set('display_errors', 'On');
//ini_set('html_errors', 0);
//error_reporting(-1);

include_once("inc/header2.php"); ?>

BBDB</title> <?php
if (!permission_check(1)) { // Upload match
	include_once("../inc/header3.php"); 
	echo '<h2>Page not found</h2>';
	echo '<p>Page not found, or you lack the necessary permissions.</p>';
	include_once("../inc/footer.php");
	die();
}
// include anything else you want to put in <head> here.

include_once("inc/header3.php"); ?>

<h1>Do Upload page</h1>

<p><strong>Please be patient. Due to all the cleverness that is happening, an upload usually takes around 15 seconds. Please do not navigate away from this page until it is complete!</strong></p>

<?php
set_time_limit(90);
// Firstly validate non-file selections. Have a valid domain and competition been selected?
//$domain_id = $_POST['domain'];
$domain_id = $_SESSION['domain_id'];
$default_competition_id = $_POST['competition'];


// ctype_digit is a function applied to text strings (form inputs are always text) and checks all inputs are digits
// Since PHP 5.1.0, returns false for empty strings, yay.
// BUT IT THINKS 1 IS NOT A NUMBER!!!!!!!!!!!!

if (($default_competition_id!=1) || ($domain_id!=1))
	{
		if (!(ctype_digit($domain_id)) || !(ctype_digit($default_competition_id)))
		{ die ("Non-numeric input detected where number expected. Upload aborted."); }
	}

$competition_check_sql = $conn->prepare("SELECT * FROM bb_competition c INNER JOIN bb_domain d ON c.domain_id = d.domain_id WHERE c.completed = 0 AND c.competition_id = ? and d.domain_id = ?");

$competition_check_sql->bindParam(1, $default_competition_id, PDO::PARAM_INT);
$competition_check_sql->bindParam(2, $domain_id, PDO::PARAM_INT);
$competition_check_sql->execute();

if (!$competition_check_sql->fetchColumn()) {
	echo $default_competition_id;
	echo $domain_id;
	die ("Invalid domain and/or competition. Upload aborted.");
}

// get the current time, according to mysql. don't get PHP time, as it may confuse things
$get_date_time = $conn->prepare("SET @start_time =  NOW()");
$get_date_time -> execute();


$target_dir = "uploads/";
$replay_file_target = $target_dir . basename($_FILES["replay_file"]["name"]);
$matchreport_target = $target_dir . 'mr_' . basename($_FILES["replay_file"]["name"]);
$txtfile_target = $target_dir . 'log_' . basename($_FILES["replay_file"]["name"]);
$txtfile2_target = $target_dir . 'log2_' . basename($_FILES["replay_file"]["name"]);
$txtfile3_target = $target_dir . 'log3_' . basename($_FILES["replay_file"]["name"]);
$uploadOk = 1;
$replay_file_type = pathinfo($replay_file_target,PATHINFO_EXTENSION);
$matchreport_type = pathinfo($_FILES["matchreport"]["name"],PATHINFO_EXTENSION);
$txtfile_type = pathinfo($_FILES["txtfile"]["name"],PATHINFO_EXTENSION);


// Check password. NOW OBSOLETE - we do a permissions check earlier on instead.
/*if ($_POST["password"]<> 'Palmtree') {
    die("Password incorrect.");
} 

if (!isset($_POST["password"])) {
    die("Password not entered.");
} 
*/

// Check if file already exists
if (file_exists($replay_file_target)) {
    echo "Sorry, file already exists. </br>";
    $uploadOk = 0;
} 

 // Check file size
if ($_FILES["replay_file"]["size"] > 500000) {
    echo "Sorry, your file is too large.</br>";
    $uploadOk = 0;
} 

// Allow certain file formats
if($replay_file_type != "db") {
    echo "That's not a DB file and thus not allowed.</br>";
    $uploadOk = 0;
} 

if($matchreport_type != "sqlite") {
    echo "That's not a sqlite file and thus not allowed.</br>";
    $uploadOk = 0;
}

if($txtfile_type != "log") {
    echo "That's not a log file and thus not allowed.</br>";
    $uploadOk = 0;
}

if(!empty($_FILES["txtfile2"]["name"])) {
	if(pathinfo($_FILES["txtfile2"]["name"],PATHINFO_EXTENSION) !="log") {
	    echo "Your second file looks suspicious. Aborting.</br>";
	    $uploadOk = 0;
	}
}

if(!empty($_FILES["txtfile3"]["name"])) {
	if(pathinfo($_FILES["txtfile3"]["name"],PATHINFO_EXTENSION) !="log") {
	    echo "Your third file looks suspicious. Aborting.</br>";
	    $uploadOk = 0;
	}
}

// Check if $uploadOk is set to 0 by an error
if ($uploadOk == 0) {
    echo "Sorry, your file was not uploaded.";
// if everything is ok, try to upload file
} else {
    if (move_uploaded_file($_FILES["replay_file"]["tmp_name"], $replay_file_target)) {
        echo "File 1 uploaded. ";
    } else {
        echo "Sorry, there was an error uploading your replay file.";
	$uploadOk = 0;
    }
    echo "<br/>";
    if (move_uploaded_file($_FILES["matchreport"]["tmp_name"], $matchreport_target)) {
        echo "File 2 uploaded. ";
    } else {
        echo "Sorry, there was an error uploading your matchreport file.";
	$uploadOk = 0;
    }
    echo "<br/>";
    if (move_uploaded_file($_FILES["txtfile"]["tmp_name"], $txtfile_target)) {
        echo "File 3 uploaded. ";
    } else {
        echo "Sorry, there was an error uploading your first log file.";
	$uploadOk = 0;
    }
    if(!empty($_FILES["txtfile2"]["name"])) {
	if (move_uploaded_file($_FILES["txtfile2"]["tmp_name"], $txtfile2_target)) {
		echo "File 4 uploaded. ";
		echo "<br/>";
        } else {
		echo "Sorry, there was an error uploading your second log file.";
		$uploadOk = 0;
        }
    }
    if(!empty($_FILES["txtfile3"]["name"])) {
	if (move_uploaded_file($_FILES["txtfile3"]["tmp_name"], $txtfile3_target)) {
		echo "File 5 uploaded. ";
		echo "<br/>";
        } else {
		echo "Sorry, there was an error uploading your third log file.";
		$uploadOk = 0;
        }
    }

}

if ($uploadOk ==0)
	{ 
	    if (file_exists($replay_file_target)) { unlink($replay_file_target); } // deletes file
	    if (file_exists($matchreport_target)) { unlink($matchreport_target); } // deletes file
	    if (file_exists($txtfile_target)) { unlink($txtfile_target); } // deletes file
	    if (file_exists($txtfile2_target)) { unlink($txtfile2_target); } // deletes file
	    if (file_exists($txtfile3_target)) { unlink($txtfile3_target); } // deletes file
	die; }
// If we have got here, the files uploaded just fine. Now see if we can open it.

$dir = 'sqlite:' . $replay_file_target ;
 
try {
    $dbh_replay  = new PDO($dir);
    //$dbh_replay ->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION); // set error mode on during dev
}
catch(PDOException $e) {
    echo "Cannot open database. Deleting file. " . $e->getMessage();
    if (file_exists($replay_file_target)) { unlink($replay_file_target); } // deletes file
    if (file_exists($matchreport_target)) { unlink($matchreport_target); } // deletes file
    if (file_exists($txtfile_target)) { unlink($txtfile_target); } // deletes file
    if (file_exists($txtfile2_target)) { unlink($txtfile2_target); } // deletes file
    if (file_exists($txtfile3_target)) { unlink($txtfile3_target); } // deletes file
    die;
}



$dir = 'sqlite:' . $matchreport_target;

try {
    $dbh_report  = new PDO($dir);
    //$dbh_report ->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION); // set error mode on during dev
}
catch(PDOException $e) {
    echo "Cannot open database. Deleting file. " . $e->getMessage();
    if (file_exists($replay_file_target)) { unlink($replay_file_target); } // deletes file
    if (file_exists($matchreport_target)) { unlink($matchreport_target); } // deletes file
    if (file_exists($txtfile_target)) { unlink($txtfile_target); } // deletes file
    if (file_exists($txtfile2_target)) { unlink($txtfile2_target); } // deletes file
    if (file_exists($txtfile3_target)) { unlink($txtfile3_target); } // deletes file
    die;
}
// We already have a connection to mysql, from the header inc files, it's called $conn.

// Check that the files match up - ie that they both refer to the same game.
// How to do this?? Is a team-id match sufficient?

	$replay_check_sql = $dbh_replay->prepare("SELECT h.ID as home_id, a.ID as away_id
		from Home_Team_Listing h
		CROSS JOIN Away_Team_Listing a");
	$replay_check_sql->execute();
	$replay_check = $replay_check_sql->fetch(PDO::FETCH_ASSOC);

	$report_check_sql = $dbh_report->prepare("SELECT c.idTeam_Listing_Home as home_id, c.idTeam_Listing_Away as away_id
				, c.Away_iPossessionBall, c.Home_Inflicted_iInjuries
				, c.Home_iScore, c.Away_iScore
				from Calendar c");
	$report_check_sql->execute();
	$report_check = $report_check_sql->fetch(PDO::FETCH_ASSOC);

	if (($replay_check['home_id']!==$report_check['home_id']) || ($replay_check['away_id']!==$report_check['away_id'])) {
		if (file_exists($replay_file_target)) { unlink($replay_file_target); } // deletes file
		if (file_exists($matchreport_target)) { unlink($matchreport_target); } // deletes file
		if (file_exists($txtfile_target)) { unlink($txtfile_target); } // deletes file
		if (file_exists($txtfile2_target)) { unlink($txtfile2_target); } // deletes file
		if (file_exists($txtfile3_target)) { unlink($txtfile3_target); } // deletes file
		die("Your selected files do not match. Aborting upload.");
	}
	
	// Check to see if the game has been uploaded with a different name.
	// Use the away possession and home_injuries and TD's score to check. This is a reasonable approximation for uniqueness.
$duplicate_check_sql = $conn->prepare("SELECT ht.bb1_id AS home_id, at.bb1_id AS away_id, m.home_touchdown_count, m.away_touchdown_count
		, hts.inflicted_knockdown AS home_kd , ats.inflicted_knockdown AS away_kd
		, hts.possession AS home_poss, ats.possession AS away_poss
	FROM bb_match m
		INNER JOIN bb_match_team_stats hts ON m.home_team_id = hts.team_id AND m.match_id = hts.match_id
		INNER JOIN bb_match_team_stats ats ON m.away_team_id = ats.team_id AND m.match_id = ats.match_id
		INNER JOIN bb_team ht ON m.home_team_id = ht.team_id
		INNER JOIN bb_team at ON m.away_team_id = at.team_id
	WHERE ht.bb1_id IN (:ht, :at) AND at.bb1_id IN (:ht, :at)
		AND ((m.home_touchdown_count = :hscore AND m.away_touchdown_count = :ascore)
	     OR (m.home_touchdown_count = :ascore AND m.away_touchdown_count = :hscore))");
	$duplicate_check_sql->bindParam(':ht', $report_check['home_id'], PDO::PARAM_INT);
	$duplicate_check_sql->bindParam(':at', $report_check['away_id'], PDO::PARAM_INT);
	$duplicate_check_sql->bindParam(':hscore', $report_check['Home_iScore'], PDO::PARAM_INT);
	$duplicate_check_sql->bindParam(':ascore', $report_check['Away_iScore'], PDO::PARAM_INT);
	$duplicate_check_sql->execute();
	$duplicate_found = FALSE;
	while($row = $duplicate_check_sql->fetch(PDO::FETCH_ASSOC)) {// loop through all matches between the two teams with this scoreline
		if(($row['home_poss']==$report_check['Away_iPossessionBall']) && ($row['away_kd']==$report_check['Home_Inflicted_iInjuries'])) {
			$duplicate_found = TRUE;
		}
		elseif (($row['away_poss']==$report_check['Away_iPossessionBall']) && ($row['home_kd']==$report_check['Home_Inflicted_iInjuries'])) {
			$duplicate_found = TRUE;
		}
	}
	
	if ($duplicate_found) {
		if (file_exists($replay_file_target)) { unlink($replay_file_target); } // deletes file
		if (file_exists($matchreport_target)) { unlink($matchreport_target); } // deletes file
		if (file_exists($txtfile_target)) { unlink($txtfile_target); } // deletes file
		if (file_exists($txtfile2_target)) { unlink($txtfile2_target); } // deletes file
		if (file_exists($txtfile3_target)) { unlink($txtfile3_target); } // deletes file
		die("It appears you have tried to upload the same match twice. Aborting.");
	}
	
// Now we have confirmed that the upload is valid, let's store the fact in the bb_upload database.
$insert_upload_data = $conn->prepare('INSERT INTO bb_upload (comment, upload_started, upload_date, filename, domain_id, default_competition_id, user_id)
VALUES (?, @start_time, NOW(), ?, ?, ?, ?)');

$array = array($_POST["comment"], basename($_FILES["replay_file"]["name"]), $domain_id, $default_competition_id, $user_id);
$insert_upload_data->execute($array);
$upload_id = $conn->lastInsertId();


// Put all table names that we want to upload in an array
// Relies on table name in database all being as expected
// "Replay_NetCommands" removed from this list 28th Mar 2016
$replay_table_list = array("SavedGameInfo", "Home_Team_Listing", "Away_Team_Listing"
			, "Home_Player_Listing", "Away_Player_Listing"
			, "Away_Player_Skills", "Home_Player_Skills"
			, "Away_Player_Types", "Home_Player_Types"
			, "Away_Player_Type_Skills", "Home_Player_Type_Skills"
			, "Away_Player_Type_Skill_Categories_Double", "Home_Player_Type_Skill_Categories_Double"
			, "Away_Player_Type_Skill_Categories_Normal", "Home_Player_Type_Skill_Categories_Normal"
			, "Away_Races", "Home_Races"
			, "Away_Statistics_Players", "Home_Statistics_Players"
			, "Away_Statistics_Season_Players", "Home_Statistics_Season_Players"
			, "Away_Statistics_Season_Teams", "Home_Statistics_Season_Teams"
			, "Away_Statistics_Teams", "Home_Statistics_Teams"
			, "Away_Player_Casualties", "Home_Player_Casualties"
);

foreach ($replay_table_list AS $table_name) {
	$replay_file = $dbh_replay->query("SELECT * FROM ".$table_name);
	//foreach($result as $row)
	while ($row = $replay_file->fetch(PDO::FETCH_ASSOC))
	{
		$row_text = $upload_id;
		foreach ($row AS $element)
		{
			// quote gives us quote marks and escapes naughty characters.
			// can't use prepared statements as you have to specify table names for those.
			// check the security notice on the php online manual.
			$row_text = $row_text.",". $dbh_replay->quote($element);
		}
		// need something clever to detect data types? or just put quotes around everything?
		$conn->exec("INSERT INTO staging_".$table_name." SELECT ".$row_text);
    	}
}


// REPLAY IS LOADED!!!! NOW FOR THE MATCHREPORT FILE!

$matchreport_table_list = array("Away_Player_Casualties","Home_Player_Casualties", "Away_Statistics_Players", "Home_Statistics_Players", "Calendar");

foreach ($matchreport_table_list AS $table_name) {
	$matchreport_file = $dbh_report->query("SELECT * FROM ".$table_name);
	//foreach($result as $row)
	while ($row = $matchreport_file->fetch(PDO::FETCH_ASSOC))
	{
		$row_text = $upload_id;
		foreach ($row AS $element)
		{
			// quote gives us quote marks and escapes naughty characters.
			// can't use prepared statements as you have to specify table names for those.
			// check the security notice on the php online manual.
			$row_text = $row_text.",". $dbh_report->quote($element);
		}
		// need something clever to detect data types? or just put quotes around everything?
		$conn->exec("INSERT INTO staging_mr_".$table_name." SELECT ".$row_text);
    	}
}

// MATCHREPORT IS LOADED NOW AS WELL!!!!!!!!
echo "<p>Databases uploaded.</p>"; 

include_once("inc/load_log.php");


bb1_load_log_to_staging($conn, $upload_id, $domain_id);

$sql=$conn->prepare("UPDATE bb_upload SET staging_load_complete = NOW()
					WHERE upload_id = ?");
$sql->execute(array($upload_id));
					
echo "<p>Log file uploaded. Beginning transformations....</p>";

include_once("inc/bb1_transformation.php");


// call the transformation function, when it has been written of course.
doTransformation($conn, $upload_id);

echo "<p>Transformations complete!</p>";

$sql=$conn->prepare("UPDATE bb_upload SET upload_completed = NOW()
					WHERE upload_id = ?");
$sql->execute(array($upload_id));


echo "<p>Updating statistical tables...</p>";

include_once("inc/stat_update.php");


update_league_table($conn, $upload_id, $domain_id);
update_star_player_record($conn, $upload_id);
update_player_tables($domain_id, $default_competition_id, $conn);
update_team_tables($domain_id, $default_competition_id, $conn);

$sql=$conn->prepare("UPDATE bb_upload SET extra_stuff_completed = NOW()
					WHERE upload_id = ?");
$sql->execute(array($upload_id));



echo '<p><strong>All files uploaded and subsequent cleverness is complete!</strong></p>'.PHP_EOL;

$sql=$conn->prepare("SELECT match_id FROM bb_upload
					WHERE upload_id = ?");
$sql->execute(array($upload_id));
$match_id = $sql->fetchColumn();

echo '<h1><a href="matchdetail.php?match_id=' . $match_id . '">CLICK HERE TO VIEW YOUR MATCH REPORT</a></h1>'.PHP_EOL; 

include_once("inc/footer.php"); ?>