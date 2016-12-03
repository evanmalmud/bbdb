<?php include_once("../inc/header1.php");

header('Content-Type: application/json');

$request_datetimestamp = microtime(true); // current unix timestamp with microseconds, as a float
$request_datetime = time(); // current time in an easy format, for logging purposes

if (!isset($_GET['api_key'])) {
	$result = array('error_code' => 100, 'error_description' => 'No API key given');
	echo json_encode($result);
	die;
}

if ((!isset($_GET['match_id'])) || (!isset($_GET['version'])))  {
	$result = array('error_code' => 300, 'error_description' => 'Required parameter missing');
	echo json_encode($result);
	die;
}

// api key is base62 and 32 char in length. Other parameters are int. preg_match doesn't work for some reason
if ((!ctype_digit($_GET['match_id'])) || (!ctype_digit($_GET['version'])) || (0===preg_match('/[A-Za-z0-9]{32}/', $_GET['api_key']))) {
	$result = array('error_code' => 310, 'error_description' => 'Parameter in unexpected format');
	echo json_encode($result);
	die;
}
$api_key = $_GET['api_key'];
$match_id = (int) $_GET['match_id'];
$version = (int) $_GET['version'];


$check_stmt = $conn->prepare("SELECT u.api_user_id, t.api_type_id, t.depracated
FROM bb_api_user u
LEFT JOIN bb_api_type t ON t.entity_name = 'matchdetail' AND t.entity_version = ?
WHERE u.api_key = ?");

	$check_stmt->bindParam(1, $version, PDO::PARAM_INT);
	//$check_stmt->bindParam(2, $match_id, PDO::PARAM_INT);
	$check_stmt->bindParam(2, $api_key, PDO::PARAM_STR);
	
	$check_stmt->execute();
	$check_data = $check_stmt->fetchAll(PDO::FETCH_ASSOC);
	
// this should be impossible	
if (count($check_data)>1) {
	$result = array('error_code' => 999, 'error_description' => 'Unexpected error');
	echo json_encode($result);
	die;
} 

if (count($check_data)==0) {
	$result = array('error_code' => 110, 'error_description' => 'Invalid API key submitted');
	echo json_encode($result);
	die;
}

if(is_null($check_data[0]['api_type_id'])) {
	$result = array('error_code' => 200, 'error_description' => 'API call type not found');
	echo json_encode($result);
	die;
}

if(1===is_null($check_data[0]['depracated'])) {
	$result = array('error_code' => 210, 'error_description' => 'API call type depracated');
	echo json_encode($result);
	die;
}


//Otherwise, everything is OK, it's a valid call. So log it now... and store the call_id for the future.
//What do we return if there are no results, eg the match_id is wrong? empty array I guess

$check_stmt = $conn->prepare("SELECT COUNT(*) FROM bb_match_team_stats m
					WHERE m.match_id = ?");

	$check_stmt->bindParam(1, $_GET['match_id'], PDO::PARAM_INT);
	$check_stmt->execute();

	$team_count = $check_stmt->fetchColumn();

	if($team_count<>2) {
		$result = array('error_code' => 330, 'error_description' => 'Match report not found');
		echo json_encode($result);
		die;
	}

	if ($version==1) {
	
		$stats = $conn->prepare("SELECT m.match_id, ht.description as 'home_team_name', m.home_team_id, at.description as 'away_team_name', m.away_team_id
					, hr.race_id as home_race_id, hr.description as home_race_name, ar.race_id as away_race_id, ar.description as away_race_name
					, m.home_touchdown_count, m.away_touchdown_count
					, hst.possession as 'home_possession'
					, hst.occupation_own as 'home_occupation_own'
					, hst.occupation_their as 'home_occupation_their'
					, hst.passes as 'home_passes' , hst.catches as 'home_catches', hst.interceptions as 'home_interceptions'
					, hst.inflicted_knockdown as 'home_knockdown'
					, hst.inflicted_tackles as 'home_tackles'
					, hst.inflicted_ko as 'home_ko'
					, hst.inflicted_injury as 'home_injury'
					, hst.inflicted_dead as 'home_killed'
					, hst.meters_run as 'home_meters_run'
					, hst.meters_pass as 'home_meters_passed'
					, hst.block_success as 'home_block_success'
					, hst.block_attempt as 'home_block_attempt'
					, hst.dodge_count as 'home_dodge'
					, ast.possession as 'away_possession'
					, ast.occupation_own as 'away_occupation_own'
					, ast.occupation_their as 'away_occupation_their'
					, ast.passes as 'away_passes' , ast.catches as 'away_catches', ast.interceptions as 'away_interceptions'
					, ast.inflicted_knockdown as 'away_knockdown'
					, ast.inflicted_tackles as 'away_tackles'
					, ast.inflicted_ko as 'away_ko'
					, ast.inflicted_injury as 'away_injury'
					, ast.inflicted_dead as 'away_killed'
					, ast.meters_run as 'away_meters_run'
					, ast.meters_pass as 'away_meters_passed'
					, ast.block_success as 'away_block_success'
					, ast.block_attempt as 'away_block_attempt'
					, ast.dodge_count as 'away_dodge'
			FROM bb_match m
			INNER JOIN bb_team ht on m.home_team_id = ht.team_id
			INNER JOIN bb_team at on m.away_team_id = at.team_id
			INNER JOIN bb_lkp_race hr on ht.race_id = hr.race_id
			INNER JOIN bb_lkp_race ar on at.race_id = ar.race_id
			INNER JOIN bb_match_team_stats hst on m.match_id = hst.match_id AND m.home_team_id = hst.team_id
			INNER JOIN bb_match_team_stats ast on m.match_id = ast.match_id AND m.away_team_id = ast.team_id
			WHERE m.match_id = ?");

	$stats->bindParam(1, $match_id, PDO::PARAM_INT);
	$stats->execute();

	$stat_row = $stats->fetch(PDO::FETCH_ASSOC);


	echo json_encode($stat_row);
	
	
	$served_datetimestamp = microtime(true); // current unix timestamp with microseconds, stored as a float

	// prepare the values to log in an array, called v for values
	$v[1] = $check_data[0]['api_user_id'];
	$v[2] = $check_data[0]['api_type_id'];
	$v[3] = "v=" . $_GET['version'] . "match=" . $_GET['match_id'];
	$v[4] = date( 'Y-m-d H:i:s', $request_datetime );
	$v[5] = 1000 * ($served_datetimestamp - $request_datetimestamp);
	
	$make_a_record = $conn->prepare("INSERT INTO bb_api_call (api_user_id, api_type_id, parameters, request_datetime, time_to_serve_ms)
			SELECT ?, ?, ?, ?, ?");
	$make_a_record->execute(array($v[1],$v[2],$v[3],$v[4],$v[5]));
	
	} // end of "if version == 1"
	
	if ($version==2) {
		
		/*
		{
			  "teams" : [
				{
				  "teamName": "Team A",
				  "score": 2,
				  "passes": 5,
				  ..., // Other team stats/coach name/race
				  "touchdowns": [ // An array of 0 or more touchdowns
					{
					  "scoredBy": "Player A",
					  "turn": 2
					},
					...
				  ]
				},
				{
				  // Team 2 details
				}
			  ],
			  "overallStats" : {
				"mostYardsRun" : {
				  "name": "Player B",
				  "value": 123
				}
			  }
			}
			*/
		
		$stats = $conn->prepare("SELECT m.match_id, t.description as 'teamName', t.team_id
					, r.race_id as race_id, r.description as raceName
					, c.description AS coachName
					, CASE WHEN t.team_id = m.home_team_id THEN home_touchdown_count ELSE away_touchdown_count END AS touchdownCount
					, ts.possession as 'possession'
					, ts.occupation_own as 'occupationOwn'
					, ts.occupation_their as 'occupationTheir'
					, ts.passes as 'passes' , ts.catches as 'catches', ts.interceptions as 'interceptions'
					, ts.inflicted_knockdown as 'knockdownsInflicted'
					, ts.inflicted_tackles as 'tackles'
					, ts.inflicted_ko as 'ko'
					, ts.inflicted_injury as 'casualtiesInflicted'
					, ts.inflicted_dead as 'killedInflicted'
					, ts.meters_run as 'metersRun'
					, ts.meters_pass as 'metersPassed'
					, tds.description AS TDScorerName
					, tds.touchdowns
					, tds.player_type AS TDScorerPlayerType
					, ts.team_value AS teamValue
					, m.rating
					, m.spectators
			FROM bb_match m
			INNER JOIN bb_team t on t.team_id IN (m.home_team_id, m.away_team_id)
			INNER JOIN bb_lkp_race r on t.race_id = r.race_id
			INNER JOIN bb_match_team_stats ts on m.match_id = ts.match_id AND t.team_id = ts.team_id
			LEFT JOIN bb_coach c ON t.coach_id = c.coach_id
			LEFT JOIN (SELECT p.team_id, p.description, ps.touchdowns, p.player_id, pt.description AS player_type
						FROM bb_player_match_stats ps
						INNER JOIN bb_player p ON ps.player_id = p.player_id
						INNER JOIN bb_lkp_player_type pt ON p.player_type_id = pt.player_type_id
						WHERE ps.touchdowns >= 1
						AND ps.match_id = ?
					) tds ON tds.team_id = t.team_id
			WHERE m.match_id = ?
			AND ((tds.player_id IS NOT NULL) 
				OR (CASE WHEN t.team_id = m.home_team_id THEN home_touchdown_count ELSE away_touchdown_count END = 0
						AND (tds.player_id IS NULL OR tds.player_id = 0)))
			ORDER BY CASE WHEN t.team_id = m.home_team_id THEN 1 ELSE 2 END, tds.description");

		$stats->bindParam(1, $match_id, PDO::PARAM_INT);
		$stats->bindParam(2, $match_id, PDO::PARAM_INT);
		$stats->execute();
		
		$fields_we_want = array('teamName' => 'Bob', 'raceName' => 'Bob', 'coachName' => 'Bob'
								, 'touchdownCount' => 0, 'possession' => 0, 'passes' => 0, 'knockdownsInflicted' => 0
								, 'teamValue' => 0, 'occupationOwn' => 0, 'catches' => 0, 'interceptions' => 0
								, 'casualtiesInflicted' => 0, 'killedInflicted' => 0, 'metersRun' => 0, 'metersPassed' => 0);
		
		$dataset = $stats->fetchAll(PDO::FETCH_ASSOC);
		$curr_team_id = 0;
		$final_result = array();
		$team_array = array();
		$scorers_array = array();
		foreach ($dataset AS $row) {
			if ($row['team_id'] <> $curr_team_id) {
				if ($curr_team_id <> 0) { // ie we have just gone from home team to away team
					$team_record["touchdowns"] = $scorers_array;
					array_push($team_array, $team_record);
				}
				$curr_team_id = $row['team_id'];
				$scorers_array = array();
				$team_record = array_intersect_key($row, $fields_we_want);
			}
			if (!is_null($row['TDScorerName'])) {
				for ($i=$row['touchdowns'];$i>0;$i--) { // one guy could score many TDs
					array_push($scorers_array, array("scoredBy" => $row['TDScorerName'], "scoredByType" => $row['TDScorerPlayerType']));
				}
			}
		}
		
		$team_record["touchdowns"] = $scorers_array;
		array_push($team_array, $team_record);
		
		$final_result = array("teams" => $team_array, "spectators" => $row["spectators"], "matchRating" => $row['rating']);
		
		echo json_encode($final_result, JSON_NUMERIC_CHECK);
		
		$served_datetimestamp = microtime(true); // current unix timestamp with microseconds, stored as a float
	}
?>