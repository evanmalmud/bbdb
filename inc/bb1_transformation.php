<?php 

function doTransformation($conn, $upload_id, $debug_id = 0) {
	if ($debug_id ==1) { echo "Main function called.<br/>"; }
	// Call all the sub-functions from here
	// Does upload_id exist?
	$match_data = $conn->prepare("SELECT CASE WHEN ht.team_id IS NOT NULL AND at.team_id IS NOT NULL THEN 'Y' ELSE 'N' END AS match_exists, u.upload_id, u.upload_date, u.filename
						FROM bb_upload u 
						INNER JOIN staging_mr_Calendar c ON u.upload_id = c.upload_id 
						INNER JOIN staging_SavedGameInfo sg ON u.upload_id = sg.upload_id 
						LEFT JOIN bb_match m ON STR_TO_DATE(right(sg.strName, 19), '%Y-%m-%d_%H-%i-%s') = m.match_date
						LEFT JOIN bb_team ht ON m.home_team_id = ht.team_id AND ht.bb1_id = c.idTeam_Listing_Home
						LEFT JOIN bb_team at ON m.away_team_id = at.team_id AND at.bb1_id = c.idTeam_Listing_Away
						WHERE u.upload_id = ?");
	$match_id_array = array($upload_id);
	$match_data->execute($match_id_array);
	$count = $match_data->rowCount();

	if ($count==0) {
		die("Cannot find that upload.");
	}

//	if ($match_data->fetchColumn(0)=='Y') {
//		die("This match is already in the database. Aborting.");
//	}

	// Load race
	import_race($conn,$upload_id);
	import_player_type($conn,$upload_id);

	// Load team, match, players
	import_team($conn,$upload_id);
	$match_id = import_match_summary($conn,$upload_id);	// This will also link the match to its default competition
				// the function isn't 100% reloadable.
	import_players($conn, $upload_id,$match_id, $debug_id); // loads any players who aren't in the database at all, and their injuries/skills, and looks for retirements

	// Is this the latest match for the teams in question?

	$latest_match_away = FALSE;
	$latest_match_home = FALSE;

	$stmt = $conn->prepare("
		SELECT	m.match_id
		FROM	staging_Away_Team_Listing s
		INNER JOIN bb_team t ON s.ID = t.bb1_id
		INNER JOIN bb_match m ON t.team_id = m.home_team_id OR t.team_id = m.away_team_id
		WHERE	s.upload_id = ?
		ORDER BY match_date DESC
		LIMIT 1");
	$stmt->bindParam(1, $upload_id, PDO::PARAM_INT);
	$stmt->execute();
	if ($match_id==$stmt->fetchColumn())
		{ $latest_match_away = TRUE; }

	$stmt = $conn->prepare("
		SELECT	m.match_id
		FROM	staging_Home_Team_Listing s
		INNER JOIN bb_team t ON s.ID = t.bb1_id
		INNER JOIN bb_match m ON t.team_id = m.home_team_id OR t.team_id = m.away_team_id
		WHERE	s.upload_id = ?
		ORDER BY match_date DESC
		LIMIT 1");
	$stmt->bindParam(1, $upload_id, PDO::PARAM_INT);
	$stmt->execute();
	if ($match_id==$stmt->fetchColumn())
		{ $latest_match_home = TRUE; }


	// Load match detailed info
	import_match_team_stats($conn,$upload_id,$match_id,$latest_match_away,$latest_match_home); // might need more code in here, using the last 2 parameters
	import_match_player_stats($conn,$upload_id,$match_id,$latest_match_away,$latest_match_home);



}

 

function import_race($conn, $upload_id) {

	$check_stmt = $conn->prepare("SELECT COUNT(*) FROM bb_lkp_race a INNER JOIN staging_Home_Races b ON a.bb1_id = b.ID WHERE b.upload_id = ? LIMIT 1");
	$check_stmt->bindParam(1, $upload_id, PDO::PARAM_INT);
	$check_stmt->execute();

	if(!$check_stmt->fetchColumn()) {
		$insert_stmt = $conn->prepare("INSERT INTO bb_lkp_race (bb1_id, description, reroll_price) SELECT ID, DATA_CONSTANT, iRerollPrice FROM staging_Home_Races WHERE upload_id = ?");
		$insert_stmt->bindParam(1, $upload_id, PDO::PARAM_INT);
		$insert_stmt->execute();
	}

	$check_stmt = $conn->prepare("SELECT COUNT(*) FROM bb_lkp_race a INNER JOIN staging_Away_Races b ON a.bb1_id = b.ID WHERE b.upload_id = ? LIMIT 1");
	$check_stmt->bindParam(1, $upload_id, PDO::PARAM_INT);
	$check_stmt->execute();

	if(!$check_stmt->fetchColumn()) {
		$insert_stmt = $conn->prepare("INSERT INTO bb_lkp_race (bb1_id, description, reroll_price) SELECT ID, DATA_CONSTANT, iRerollPrice FROM staging_Away_Races WHERE upload_id = ?");
		$insert_stmt->bindParam(1, $upload_id, PDO::PARAM_INT);
		$insert_stmt->execute();
	}

}


function import_player_type($conn, $upload_id) {
	//can be decommissioned once all players are in
	//need 6 statements! players, skills and skill access for home and away players.

	$aptype = $conn->prepare("
	INSERT INTO bb_lkp_player_type (bb1_id, long_description, race_id, max_quantity)
	SELECT pt.ID, pt.DATA_CONSTANT
		, COALESCE(r.race_id, pt.idRaces)
		, pt.iMaxQuantity
	FROM staging_Away_Player_Types pt
	LEFT JOIN bb_lkp_race r ON pt.idRaces = r.bb1_id
	WHERE pt.upload_id = ?
	AND pt.ID NOT IN (SELECT bb1_id FROM bb_lkp_player_type)");

	$aptype->bindParam(1, $upload_id, PDO::PARAM_INT);
	$aptype->execute();

	$aptype = $conn->prepare("
	INSERT INTO bb_lkp_player_type_stats (player_type_id, ruleset_id, mv, st, ag, av, price)
	SELECT lk.player_type_id, 1, mv.human_val, st.human_val, ag.human_val, av.human_val, pt.iPrice
	FROM staging_Away_Player_Types pt
	INNER JOIN bb_lkp_player_type lk ON pt.ID = lk.bb1_id
	INNER JOIN bb_lkp_mv mv ON mv.bb1_id > pt.Characteristics_fMovementAllowance-0.01 AND mv.bb1_id < pt.Characteristics_fMovementAllowance+0.01
	INNER JOIN bb_lkp_st st ON st.bb1_id > pt.Characteristics_fStrength-0.01 AND st.bb1_id < pt.Characteristics_fStrength+0.01
	INNER JOIN bb_lkp_ag ag ON ag.bb1_id > pt.Characteristics_fAgility-0.01 AND ag.bb1_id < pt.Characteristics_fAgility+0.01
	INNER JOIN bb_lkp_av av ON av.bb1_id > pt.Characteristics_fArmourValue-0.01 AND av.bb1_id < pt.Characteristics_fArmourValue+0.01
	WHERE pt.upload_id = ?
	AND lk.player_type_id NOT IN (SELECT player_type_id FROM bb_lkp_player_type_stats WHERE ruleset_id = 1)");

	$aptype->bindParam(1, $upload_id, PDO::PARAM_INT);
	$aptype->execute();

	$hptype = $conn->prepare("
	INSERT INTO bb_lkp_player_type (bb1_id, long_description, race_id, max_quantity)
	SELECT pt.ID, pt.DATA_CONSTANT
		, COALESCE(r.race_id, pt.idRaces)
		, pt.iMaxQuantity
	FROM staging_Home_Player_Types pt
	LEFT JOIN bb_lkp_race r ON pt.idRaces = r.bb1_id
	WHERE pt.upload_id = ?
	AND pt.ID NOT IN (SELECT bb1_id FROM bb_lkp_player_type)");

	$hptype->bindParam(1, $upload_id, PDO::PARAM_INT);
	$hptype->execute();


	$hptype = $conn->prepare("
	INSERT INTO bb_lkp_player_type_stats (player_type_id, ruleset_id, mv, st, ag, av, price)
	SELECT lk.player_type_id, 1, mv.human_val, st.human_val, ag.human_val, av.human_val, pt.iPrice
	FROM staging_Home_Player_Types pt
	INNER JOIN bb_lkp_player_type lk ON pt.ID = lk.bb1_id
	INNER JOIN bb_lkp_mv mv ON mv.bb1_id > pt.Characteristics_fMovementAllowance-0.01 AND mv.bb1_id < pt.Characteristics_fMovementAllowance+0.01
	INNER JOIN bb_lkp_st st ON st.bb1_id > pt.Characteristics_fStrength-0.01 AND st.bb1_id < pt.Characteristics_fStrength+0.01
	INNER JOIN bb_lkp_ag ag ON ag.bb1_id > pt.Characteristics_fAgility-0.01 AND ag.bb1_id < pt.Characteristics_fAgility+0.01
	INNER JOIN bb_lkp_av av ON av.bb1_id > pt.Characteristics_fArmourValue-0.01 AND av.bb1_id < pt.Characteristics_fArmourValue+0.01
	WHERE pt.upload_id = ?
	AND lk.player_type_id NOT IN (SELECT player_type_id FROM bb_lkp_player_type_stats WHERE ruleset_id = 1)");

	$hptype->bindParam(1, $upload_id, PDO::PARAM_INT);
	$hptype->execute();

	$apskilla = $conn->prepare("
		INSERT INTO bb_lkp_player_type_skill_access (player_type_id, skill_category_id, access_roll)
		SELECT pt.player_type_id, s.idSkill_Categories, 'N'
		FROM staging_Away_Player_Type_Skill_Categories_Normal s
		INNER JOIN bb_lkp_player_type pt ON s.idPlayer_Types = pt.bb1_id
		WHERE s.upload_id = ?
		AND pt.player_type_id NOT IN (SELECT player_type_id FROM bb_lkp_player_type_skill_access)
		UNION ALL
		SELECT pt.player_type_id, s.idSkill_Categories, 'D'
		FROM staging_Away_Player_Type_Skill_Categories_Double s
		INNER JOIN bb_lkp_player_type pt ON s.idPlayer_Types = pt.bb1_id
		WHERE s.upload_id = ?
		AND pt.player_type_id NOT IN (SELECT player_type_id FROM bb_lkp_player_type_skill_access)");
	$apskilla->bindParam(1, $upload_id, PDO::PARAM_INT);
	$apskilla->bindParam(2, $upload_id, PDO::PARAM_INT);
	$apskilla->execute();

	$hpskilla = $conn->prepare("
		INSERT INTO bb_lkp_player_type_skill_access (player_type_id, skill_category_id, access_roll)
		SELECT pt.player_type_id, s.idSkill_Categories, 'N'
		FROM staging_Home_Player_Type_Skill_Categories_Normal s
		INNER JOIN bb_lkp_player_type pt ON s.idPlayer_Types = pt.bb1_id
		WHERE s.upload_id = ?
		AND pt.player_type_id NOT IN (SELECT player_type_id FROM bb_lkp_player_type_skill_access)
		UNION ALL
		SELECT pt.player_type_id, s.idSkill_Categories, 'D'
		FROM staging_Home_Player_Type_Skill_Categories_Double s
		INNER JOIN bb_lkp_player_type pt ON s.idPlayer_Types = pt.bb1_id
		WHERE s.upload_id = ?
		AND pt.player_type_id NOT IN (SELECT player_type_id FROM bb_lkp_player_type_skill_access)");
	$hpskilla->bindParam(1, $upload_id, PDO::PARAM_INT);
	$hpskilla->bindParam(2, $upload_id, PDO::PARAM_INT);
	$hpskilla->execute();


	$apskill=$conn->prepare("
		INSERT INTO bb_lkp_player_type_skill (player_type_id, skill_id)
		SELECT pt.player_type_id, s.idSkill_Listing
		FROM staging_Away_Player_Type_Skills s
		INNER JOIN bb_lkp_player_type pt ON s.idPlayer_Types = pt.bb1_id
		WHERE s.upload_id = ?
		AND pt.player_type_id NOT IN (SELECT player_type_id FROM bb_lkp_player_type_skill)");
	$apskill->bindParam(1, $upload_id, PDO::PARAM_INT);
	$apskill->execute();

	$hpskill=$conn->prepare("
		INSERT INTO bb_lkp_player_type_skill (player_type_id, skill_id)
		SELECT pt.player_type_id, s.idSkill_Listing
		FROM staging_Home_Player_Type_Skills s
		INNER JOIN bb_lkp_player_type pt ON s.idPlayer_Types = pt.bb1_id
		WHERE s.upload_id = ?
		AND pt.player_type_id NOT IN (SELECT player_type_id FROM bb_lkp_player_type_skill)");
	$hpskill->bindParam(1, $upload_id, PDO::PARAM_INT);
	$hpskill->execute();

	// add star player - race links
	$stmt=$conn->prepare("
		INSERT INTO bb_lkp_star_player_race (player_type_id, race_id, ruleset_id)
		SELECT pt.player_type_id, r.race_id, 1
		FROM staging_Away_Player_Types st
		INNER JOIN bb_lkp_player_type pt ON st.ID = pt.bb1_id
		INNER JOIN staging_Away_Races sr ON st.upload_id = sr.upload_id
		INNER JOIN bb_lkp_race r ON sr.ID = r.bb1_id
		WHERE NOT EXISTS (SELECT * FROM bb_lkp_star_player_race sp2 WHERE sp2.player_type_id = pt.player_type_id AND sp2.race_id = r.race_id)
		AND st.upload_id = ?
		AND st.idRaces = 0 -- this means star players");
	$stmt->bindParam(1, $upload_id, PDO::PARAM_INT);
	$stmt->execute();

	$stmt=$conn->prepare("
		INSERT INTO bb_lkp_star_player_race (player_type_id, race_id, ruleset_id)
		SELECT pt.player_type_id, r.race_id, 1
		FROM staging_Home_Player_Types st
		INNER JOIN bb_lkp_player_type pt ON st.ID = pt.bb1_id
		INNER JOIN staging_Home_Races sr ON st.upload_id = sr.upload_id
		INNER JOIN bb_lkp_race r ON sr.ID = r.bb1_id
		WHERE NOT EXISTS (SELECT * FROM bb_lkp_star_player_race sp2 WHERE sp2.player_type_id = pt.player_type_id AND sp2.race_id = r.race_id)
		AND st.upload_id = ?
		AND st.idRaces = 0 -- this means star players");
	$stmt->bindParam(1, $upload_id, PDO::PARAM_INT);
	$stmt->execute();

}

function import_team($conn, $upload_id) {

	// is the team in the bb_team table?
	$check_stmt = $conn->prepare("SELECT * FROM bb_team a INNER JOIN staging_Away_Team_Listing b ON a.bb1_id = b.ID WHERE b.upload_id = ? LIMIT 1");
	$check_stmt->bindParam(1, $upload_id, PDO::PARAM_INT);
	$check_stmt->execute();

	if(!$check_stmt->fetchColumn()) {

		$stmt = $conn->prepare("INSERT INTO bb_team (bb1_id, description, race_id, str_logo, motto, background, value, rerolls, fan_factor, cheerleaders, apothecary, balms, cash, assistant_coaches
				, passes, catches, interceptions, inflicted_knockdown, inflicted_tackles, inflicted_ko, inflicted_injury, inflicted_dead, meters_run, meters_pass
				, sustained_interception,sustained_knockdown,sustained_tackles,sustained_ko,sustained_stun,sustained_injury,sustained_dead
				, match_played, mvp, sustained_touchdowns, sustained_meters_run, sustained_meters_pass, wins, draws, losses
				, avg_possession, avg_occupation_own, avg_occupation_their, total_cash, total_spectators, touchdowns)
			SELECT l.ID, l.strName, r.race_id, l.strLogo, l.strLeitmotiv, l.strBackground, l.iValue, l.iRerolls, l.iPopularity, l.iCheerleaders, l.bApothecary, l.iBalms, l.iCash, l.iAssistantCoaches
				, st.Inflicted_iPasses, st.Inflicted_iCatches, st.Inflicted_iInterceptions, st.Inflicted_iInjuries, st.Inflicted_iTackles, st.Inflicted_iKO, st.Inflicted_iCasualties
				, st.Inflicted_iDead, st.Inflicted_iMetersRunning, st.Inflicted_iMetersPassing
				, st.Sustained_iInterceptions
				, st.Sustained_iInjuries
				, st.Sustained_iTackles
				, st.Sustained_iKO
				, NULL	-- stuns aren't in the team statistics file
				, st.Sustained_iCasualties
				, st.Sustained_iDead
				, st.iMatchPlayed, st.iMVP, st.Sustained_iTouchdowns, st.Sustained_iMetersRunning, st.Sustained_iMetersPassing, st.iWins, st.iDraws, st.iLoss
				, st.iPossessionBall, st.Occupation_iOwn, st.Occupation_iTheir, st.iCashEarned, st.iSpectators, st.Inflicted_iTouchdowns
			FROM staging_Away_Team_Listing l
			INNER JOIN bb_lkp_race r ON l.idRaces = r.bb1_id
			INNER JOIN staging_Away_Statistics_Teams st ON l.upload_id = st.upload_id
			WHERE l.upload_id = ?");
		$stmt->bindParam(1, $upload_id, PDO::PARAM_INT);
		$stmt->execute();
	}
	else
	{ // is this the latest match? check_stmt says "are there any matches with a later match date"
		$check_stmt = $conn->prepare("SELECT t.team_id, CASE WHEN m.match_date > STR_TO_DATE(right(sg.strName, 19), '%Y-%m-%d_%H-%i-%s') THEN 1 ELSE 0 END as match_is_later
					FROM staging_SavedGameInfo sg
					INNER JOIN staging_mr_Calendar c ON sg.upload_id = c.upload_id
					INNER JOIN bb_team t ON t.bb1_id = c.idTeam_Listing_Away
					INNER JOIN bb_match m ON t.team_id IN (m.home_team_id, m.away_team_id)
					WHERE sg.upload_id = ?
					ORDER BY m.match_date DESC
					LIMIT 1");
		$check_stmt->bindParam(1, $upload_id, PDO::PARAM_INT);
		$check_stmt->execute();
		$the_row = $check_stmt->fetch(PDO::FETCH_ASSOC);
		if($the_row['match_is_later']==0) {
			$this_team_id = $the_row['team_id'];
			$stmt = $conn->prepare("UPDATE bb_team t
							INNER JOIN staging_Away_Statistics_Teams st ON t.bb1_id = st.idTeam_Listing
							INNER JOIN staging_Away_Team_Listing s ON s.upload_id = st.upload_id AND s.ID = t.bb1_id
						SET str_logo = s.strLogo, motto = s.strLeitmotiv, background = s.strBackground, value = s.iValue
							, rerolls = s.iRerolls, fan_factor = s.iPopularity, cheerleaders = s.iCheerleaders
							, apothecary = s.bApothecary, balms = s.iBalms, cash = s.iCash, assistant_coaches = s.iAssistantCoaches 
							, match_played = st.iMatchPlayed
							, mvp = st.iMVP
							, passes = st.Inflicted_iPasses
							, catches = st.Inflicted_iCatches
							, interceptions = st.Inflicted_iInterceptions
							, touchdowns = st.Inflicted_iTouchdowns
							, inflicted_knockdown = st.Inflicted_iInjuries	-- actually means knockdowns
							, inflicted_tackles = st.Inflicted_iTackles
							, inflicted_ko = st.Inflicted_iKO
							
							, inflicted_injury = st.Inflicted_iCasualties
							, inflicted_dead = st.Inflicted_iDead
							, meters_run = st.Inflicted_iMetersRunning
							, meters_pass = st.Inflicted_iMetersPassing
							, sustained_interception = st.Sustained_iInterceptions
							, sustained_knockdown = st.Sustained_iInjuries
							, sustained_tackles = st.Sustained_iTackles
							, sustained_ko = st.Sustained_iKO
							
							, sustained_injury = st.Sustained_iCasualties
							, sustained_dead = st.Sustained_iDead
							, touchdowns = st.Inflicted_iTouchdowns
							, sustained_touchdowns = st.Sustained_iTouchdowns
							, sustained_meters_run = st.Sustained_iMetersRunning
							, sustained_meters_pass = st.Sustained_iMetersPassing
							, wins = st.iWins
							, draws = st.iDraws
							, losses = st.iLoss
							, avg_possession = st.iPossessionBall
							, avg_occupation_own = st.Occupation_iOwn
							, avg_occupation_their = st.Occupation_iTheir
							, total_cash = st.iCashEarned
							, total_spectators = st.iSpectators
						WHERE st.upload_id = ?");
			$stmt->bindParam(1, $upload_id, PDO::PARAM_INT);
			$stmt->execute();
		}
	}

	$check_stmt = $conn->prepare("SELECT * FROM bb_team a INNER JOIN staging_Home_Team_Listing b ON a.bb1_id = b.ID WHERE b.upload_id = ? LIMIT 1");
	$check_stmt->bindParam(1, $upload_id, PDO::PARAM_INT);
	$check_stmt->execute();

	if(!$check_stmt->fetchColumn()) {

	$stmt = $conn->prepare("INSERT INTO bb_team (bb1_id, description, race_id, str_logo, motto, background, value, rerolls, fan_factor, cheerleaders, apothecary, balms, cash, assistant_coaches
				, passes, catches, interceptions, inflicted_knockdown, inflicted_tackles, inflicted_ko, inflicted_injury, inflicted_dead, meters_run, meters_pass
				, sustained_interception,sustained_knockdown,sustained_tackles,sustained_ko,sustained_stun,sustained_injury,sustained_dead
				, match_played, mvp, sustained_touchdowns, sustained_meters_run, sustained_meters_pass, wins, draws, losses
				, avg_possession, avg_occupation_own, avg_occupation_their, total_cash, total_spectators)
			SELECT l.ID, l.strName, r.race_id, l.strLogo, l.strLeitmotiv, l.strBackground, l.iValue, l.iRerolls, l.iPopularity, l.iCheerleaders, l.bApothecary, l.iBalms, l.iCash, l.iAssistantCoaches
				, st.Inflicted_iPasses, st.Inflicted_iCatches, st.Inflicted_iInterceptions, st.Inflicted_iInjuries, st.Inflicted_iTackles, st.Inflicted_iKO, st.Inflicted_iCasualties
				, st.Inflicted_iDead, st.Inflicted_iMetersRunning, st.Inflicted_iMetersPassing
				, st.Sustained_iInterceptions
				, st.Sustained_iInjuries
				, st.Sustained_iTackles
				, st.Sustained_iKO
				, NULL	-- stuns aren't in the team statistics file
				, st.Sustained_iCasualties
				, st.Sustained_iDead
				, st.iMatchPlayed, st.iMVP, st.Sustained_iTouchdowns, st.Sustained_iMetersRunning, st.Sustained_iMetersPassing, st.iWins, st.iDraws, st.iLoss
				, st.iPossessionBall, st.Occupation_iOwn, st.Occupation_iTheir, st.iCashEarned, st.iSpectators
			FROM staging_Home_Team_Listing l
			INNER JOIN bb_lkp_race r ON l.idRaces = r.bb1_id
			INNER JOIN staging_Home_Statistics_Teams st ON l.upload_id = st.upload_id
			WHERE l.upload_id = ?");
		$stmt->bindParam(1, $upload_id, PDO::PARAM_INT);
		$stmt->execute();
	}
	else
	{ // is this the latest match?
		$check_stmt = $conn->prepare("SELECT t.team_id, CASE WHEN m.match_date > STR_TO_DATE(right(sg.strName, 19), '%Y-%m-%d_%H-%i-%s') THEN 1 ELSE 0 END as match_is_later
					FROM staging_SavedGameInfo sg
					INNER JOIN staging_mr_Calendar c ON sg.upload_id = c.upload_id
					INNER JOIN bb_team t ON t.bb1_id = c.idTeam_Listing_Home
					INNER JOIN bb_match m ON t.team_id IN (m.home_team_id, m.away_team_id)
					WHERE sg.upload_id = ?
					ORDER BY m.match_date DESC
					LIMIT 1");
		$check_stmt->bindParam(1, $upload_id, PDO::PARAM_INT);
		$check_stmt->execute();
		$the_row = $check_stmt->fetch(PDO::FETCH_ASSOC);
		if($the_row['match_is_later']==0) {
			$this_team_id = $the_row['team_id'];
			$stmt = $conn->prepare("UPDATE bb_team t
							INNER JOIN staging_Home_Statistics_Teams st ON t.bb1_id = st.idTeam_Listing
							INNER JOIN staging_Home_Team_Listing s ON s.upload_id = st.upload_id AND s.ID = t.bb1_id
						SET str_logo = s.strLogo, motto = s.strLeitmotiv, background = s.strBackground, value = s.iValue
							, rerolls = s.iRerolls, fan_factor = s.iPopularity, cheerleaders = s.iCheerleaders
							, apothecary = s.bApothecary, balms = s.iBalms, cash = s.iCash, assistant_coaches = s.iAssistantCoaches 
							, match_played = st.iMatchPlayed
							, mvp = st.iMVP
							, passes = st.Inflicted_iPasses
							, catches = st.Inflicted_iCatches
							, interceptions = st.Inflicted_iInterceptions
							, touchdowns = st.Inflicted_iTouchdowns
							, inflicted_knockdown = st.Inflicted_iInjuries	-- actually means knockdowns
							, inflicted_tackles = st.Inflicted_iTackles
							, inflicted_ko = st.Inflicted_iKO
							
							, inflicted_injury = st.Inflicted_iCasualties
							, inflicted_dead = st.Inflicted_iDead
							, meters_run = st.Inflicted_iMetersRunning
							, meters_pass = st.Inflicted_iMetersPassing
							, sustained_interception = st.Sustained_iInterceptions
							, sustained_knockdown = st.Sustained_iInjuries
							, sustained_tackles = st.Sustained_iTackles
							, sustained_ko = st.Sustained_iKO
							
							, sustained_injury = st.Sustained_iCasualties
							, sustained_dead = st.Sustained_iDead
							, touchdowns = st.Inflicted_iTouchdowns
							, sustained_touchdowns = st.Sustained_iTouchdowns
							, sustained_meters_run = st.Sustained_iMetersRunning
							, sustained_meters_pass = st.Sustained_iMetersPassing
							, wins = st.iWins
							, draws = st.iDraws
							, losses = st.iLoss
							, avg_possession = st.iPossessionBall
							, avg_occupation_own = st.Occupation_iOwn
							, avg_occupation_their = st.Occupation_iTheir
							, total_cash = st.iCashEarned
							, total_spectators = st.iSpectators
						WHERE st.upload_id = ?");
			$stmt->bindParam(1, $upload_id, PDO::PARAM_INT);
			$stmt->execute();
		}
	}

}

function import_match_summary($conn, $upload_id) {


	$check_stmt = $conn->prepare("SELECT m.match_id
						FROM bb_upload u 
						INNER JOIN staging_mr_Calendar c ON u.upload_id = c.upload_id 
						INNER JOIN staging_SavedGameInfo sg ON u.upload_id = sg.upload_id 
						INNER JOIN bb_match m ON STR_TO_DATE(right(sg.strName, 19), '%Y-%m-%d_%H-%i-%s') = m.match_date
						INNER JOIN bb_team ht ON m.home_team_id = ht.team_id AND ht.bb1_id = c.idTeam_Listing_Home
						INNER JOIN bb_team at ON m.away_team_id = at.team_id AND at.bb1_id = c.idTeam_Listing_Away
						WHERE u.upload_id = ?");
	$check_stmt->bindParam(1, $upload_id, PDO::PARAM_INT);
	$check_stmt->execute();

	$match_id_1 = $check_stmt->fetchColumn(0);

	if (ctype_digit($match_id_1))
		{ return $match_id_1; }

	$stmt = $conn->prepare("INSERT INTO bb_match (bb1_id, domain_id, match_date, home_team_id, away_team_id, home_touchdown_count, away_touchdown_count, spectators, rating, rulestype_id, overtime_flag)
			SELECT
			NULL
			, u.domain_id
			, STR_TO_DATE(right(sg.strName, 19), '%Y-%m-%d_%H-%i-%s')
			, ht.team_id, at.team_id
			, c.Home_iScore, c.Away_iScore
			, c.iSpectators, c.iRating
			, c.Championship_idRule_Types
			, CASE WHEN c.Home_Inflicted_iTouchdowns = c.Home_iScore AND c.Away_Inflicted_iTouchdowns = c.Away_iScore THEN 0 ELSE 1 END
			FROM staging_mr_Calendar c
			INNER JOIN staging_SavedGameInfo sg ON c.upload_id = sg.upload_id
			INNER JOIN bb_team ht ON c.idTeam_Listing_Home = ht.bb1_id
			INNER JOIN bb_team at ON c.idTeam_Listing_Away = at.bb1_id
			INNER JOIN bb_upload u ON c.upload_id = u.upload_id
			WHERE c.upload_id = ?");
	$stmt->bindParam(1, $upload_id, PDO::PARAM_INT);
	$stmt->execute();

	$match_id = $conn->lastInsertId();

	$stmt = $conn->prepare("UPDATE bb_upload SET match_id = ?, transformed = 1 WHERE upload_id = ?");
	$stmt->bindParam(1, $match_id, PDO::PARAM_INT);
	$stmt->bindParam(2, $upload_id, PDO::PARAM_INT);
	$stmt->execute();

	// Add teams to competitions

	$stmt = $conn->prepare("
		INSERT INTO bb_competition_team (competition_id, domain_id, team_id, initial_group_id)
		SELECT c.competition_id, u.domain_id, m.team_id
			, CASE WHEN ct.start_league = 1 AND ct.start_group_count = 1 THEN 1 ELSE NULL END
		FROM bb_upload u
		INNER JOIN bb_competition c ON u.default_competition_id = c.competition_id AND u.domain_id = c.domain_id
		INNER JOIN bb_lkp_competition_type ct ON c.competition_type_id = ct.competition_type_id
		INNER JOIN (SELECT home_team_id AS team_id, match_id FROM bb_match 
				UNION ALL SELECT away_team_id AS team_id, match_id FROM bb_match)
				m ON u.match_id = m.match_id
		WHERE c.auto_enrol = 1
		AND c.completed = 0
		AND NOT EXISTS (SELECT * FROM bb_competition_team ct WHERE c.competition_id = ct.competition_id 
					AND u.domain_id = ct.domain_id
					AND m.team_id = ct.team_id)
		AND u.upload_id = ?");
	$stmt->bindParam(1, $upload_id, PDO::PARAM_INT);
	$stmt->execute();

	// add match-competition record

	$stmt = $conn->prepare("INSERT INTO bb_match_competition (match_id, domain_id, competition_id, default_competition)
			SELECT ?, ?, default_competition_id, 1
			FROM bb_upload WHERE upload_id = ?");
	$stmt->bindParam(1, $match_id, PDO::PARAM_INT);
	$stmt->bindParam(2, $_SESSION['domain_id'], PDO::PARAM_INT);
	$stmt->bindParam(3, $upload_id, PDO::PARAM_INT);
	$stmt->execute();

	return $match_id;

}

function import_match_team_stats($conn, $upload_id, $match_id,$latest_match_away,$latest_match_home) {
	// need to check if data exists
	$check_stmt = $conn->prepare("SELECT 1 FROM bb_match_team_stats m
					INNER JOIN bb_team t ON m.team_id = t.team_id
					INNER JOIN staging_mr_Calendar c on c.idTeam_Listing_Away = t.bb1_id
					 WHERE m.match_id = ? and c.upload_id = ?");
	$check_stmt->bindParam(1, $match_id, PDO::PARAM_INT);
	$check_stmt->bindParam(2, $upload_id, PDO::PARAM_INT);
	$check_stmt->execute();

	if (!$check_stmt->fetchColumn())
	{

		$stmt = $conn->prepare("
			INSERT INTO bb_match_team_stats (match_id, team_id, cash_earned, possession, occupation_own, occupation_their, passes, catches, interceptions, 
					inflicted_knockdown, inflicted_tackles, inflicted_ko, inflicted_injury, inflicted_dead, meters_run, meters_pass, team_value, rerolls)
			SELECT ?, t.team_id, c.Away_iCashEarned, c.Away_iPossessionBall, c.Away_Occupation_iOwn, c.Away_Occupation_iTheir, c.Away_Inflicted_iPasses, c.Away_Inflicted_iCatches
				, c.Away_Inflicted_iInterceptions, c.Away_Inflicted_iInjuries, c.Away_Inflicted_iTackles, c.Away_Inflicted_iKO, c.Away_Inflicted_iCasualties
				, c.Away_Inflicted_iDead, c.Away_Inflicted_iMetersRunning, c.Away_Inflicted_iMetersPassing, l.iValue, l.iRerolls
			FROM staging_mr_Calendar c
			INNER JOIN bb_team t ON c.idTeam_Listing_Away = t.bb1_id
			INNER JOIN staging_Away_Team_Listing l ON c.upload_id = l.upload_id
			WHERE c.upload_id = ?
		");

		$stmt->bindParam(1, $match_id, PDO::PARAM_INT);
		$stmt->bindParam(2, $upload_id, PDO::PARAM_INT);
		$stmt->execute();
	}


	$check_stmt = $conn->prepare("SELECT 1 FROM bb_match_team_stats m
					INNER JOIN bb_team t ON m.team_id = t.team_id
					INNER JOIN staging_mr_Calendar c on c.idTeam_Listing_Home = t.bb1_id
					 WHERE m.match_id = ? and c.upload_id = ?");
	$check_stmt->bindParam(1, $match_id, PDO::PARAM_INT);
	$check_stmt->bindParam(2, $upload_id, PDO::PARAM_INT);
	$check_stmt->execute();

	if (!$check_stmt->fetchColumn())
	{

		$stmt = $conn->prepare("
			INSERT INTO bb_match_team_stats (match_id, team_id, cash_earned, possession, occupation_own, occupation_their, passes, catches, interceptions, 
					inflicted_knockdown, inflicted_tackles, inflicted_ko, inflicted_injury, inflicted_dead, meters_run, meters_pass, team_value, rerolls)
			SELECT ?, t.team_id, c.Home_iCashEarned, c.Home_iPossessionBall, c.Home_Occupation_iOwn, c.Home_Occupation_iTheir, c.Home_Inflicted_iPasses, c.Home_Inflicted_iCatches
				, c.Home_Inflicted_iInterceptions, c.Home_Inflicted_iInjuries, c.Home_Inflicted_iTackles, c.Home_Inflicted_iKO, c.Home_Inflicted_iCasualties
				, c.Home_Inflicted_iDead, c.Home_Inflicted_iMetersRunning, c.Home_Inflicted_iMetersPassing, l.iValue, l.iRerolls
			FROM staging_mr_Calendar c
			INNER JOIN bb_team t ON c.idTeam_Listing_Home = t.bb1_id
			INNER JOIN staging_Home_Team_Listing l ON c.upload_id = l.upload_id
			WHERE c.upload_id = ?
		");

		$stmt->bindParam(1, $match_id, PDO::PARAM_INT);
		$stmt->bindParam(2, $upload_id, PDO::PARAM_INT);
		$stmt->execute();
	}

	// NOW TO UPDATE TEAM OVERALL STATS, IF IT IS APPROPRIATE TO DO SO. ie the current team's total stats equal the uploaded game's pre-game totals
	// Basically this happens when a game is uploaded that is the latest for that team.

	$check_stmt = $conn->prepare("SELECT t.team_id FROM bb_team t
					INNER JOIN staging_Away_Statistics_Teams ss ON t.bb1_id = ss.idTeam_Listing
					WHERE t.match_played = ss.iMatchPlayed
					AND ss.upload_id = ?
					LIMIT 1");
	$check_stmt->bindParam(1, $upload_id, PDO::PARAM_INT);
	$check_stmt->execute();

	if($check_stmt->fetchColumn()) {

		$stmt = $conn->prepare("UPDATE bb_team t
						INNER JOIN bb_match_team_stats ms ON t.team_id = ms.team_id
						INNER JOIN bb_match m ON ms.match_id = m.match_id AND m.away_team_id = t.team_id
						INNER JOIN bb_match_team_stats oms ON m.home_team_id = oms.team_id AND oms.match_id = m.match_id

						SET t.match_played = t.match_played + 1
						, t.mvp = t.mvp + 1 -- just guessing here :-)
						, t.passes = t.passes + COALESCE(ms.passes,0)
						, t.catches = t.catches + COALESCE(ms.catches,0)
						, t.interceptions = t.interceptions + COALESCE(ms.interceptions,0)
						, t.inflicted_knockdown = t.inflicted_knockdown + COALESCE(ms.inflicted_knockdown,0)
						, t.inflicted_tackles = t.inflicted_tackles + COALESCE(ms.inflicted_tackles,0)
						, t.inflicted_ko = t.inflicted_ko + COALESCE(ms.inflicted_ko,0)
							
						, t.inflicted_injury = t.inflicted_injury + COALESCE(ms.inflicted_injury,0)
						, t.inflicted_dead = t.inflicted_dead + COALESCE(ms.inflicted_dead,0)
						, t.meters_run = t.meters_run + COALESCE(ms.meters_run,0)
						, t.meters_pass = t.meters_pass + COALESCE(ms.meters_pass,0)
				-- THESE ONES REQUIRE LINKING TO THE OPPOSITE TEAM
						, t.sustained_interception = t.sustained_interception + COALESCE(oms.interceptions,0)
						, t.sustained_knockdown = t.sustained_knockdown + COALESCE(oms.inflicted_knockdown,0)
						, t.sustained_tackles = t.sustained_tackles + COALESCE(oms.inflicted_tackles,0)
						, t.sustained_ko = t.sustained_ko + COALESCE(oms.inflicted_ko,0)
							
						, t.sustained_injury = t.sustained_injury + COALESCE(oms.inflicted_injury,0)
						, t.sustained_dead = t.sustained_dead + COALESCE(oms.inflicted_dead,0)
						, t.sustained_meters_run = t.sustained_meters_run + COALESCE(oms.meters_run,0)
						, t.sustained_meters_pass = t.sustained_meters_pass + COALESCE(oms.meters_pass,0)
				-- END OF THESE ONES REQUIRE LINKING TO THE OPPOSITE TEAM
				-- THESE ONES REQUIRE LINKING TO THE MATCH
						, t.touchdowns = t.touchdowns + m.away_touchdown_count
						, t.sustained_touchdowns = t.sustained_touchdowns + m.home_touchdown_count
						, t.wins = t.wins + CASE WHEN m.away_touchdown_count > m.home_touchdown_count THEN 1 ELSE 0 END
						, t.draws = t.draws + CASE WHEN m.away_touchdown_count = m.home_touchdown_count THEN 1 ELSE 0 END
						, t.losses = t.losses + CASE WHEN m.away_touchdown_count < m.home_touchdown_count THEN 1 ELSE 0 END
					WHERE m.match_id = ?");
			$stmt->bindParam(1, $match_id, PDO::PARAM_INT);
			$stmt->execute();
		}


	// NOW THE SAME FOR THE HOME TEAM

	$check_stmt = $conn->prepare("SELECT t.team_id FROM bb_team t
					INNER JOIN staging_Home_Statistics_Teams ss ON t.bb1_id = ss.idTeam_Listing
					WHERE t.match_played = ss.iMatchPlayed
					AND ss.upload_id = ?
					LIMIT 1");
	$check_stmt->bindParam(1, $upload_id, PDO::PARAM_INT);
	$check_stmt->execute();

	if($check_stmt->fetchColumn()) {

		$stmt = $conn->prepare("UPDATE bb_team t
						INNER JOIN bb_match_team_stats ms ON t.team_id = ms.team_id
						INNER JOIN bb_match m ON ms.match_id = m.match_id AND m.home_team_id = t.team_id
						INNER JOIN bb_match_team_stats oms ON m.away_team_id = oms.team_id AND oms.match_id = m.match_id

						SET t.match_played = t.match_played + 1
						, t.mvp = t.mvp + 1 -- just guessing here :-)
						, t.passes = t.passes + COALESCE(ms.passes,0)
						, t.catches = t.catches + COALESCE(ms.catches,0)
						, t.interceptions = t.interceptions + COALESCE(ms.interceptions,0)
						, t.inflicted_knockdown = t.inflicted_knockdown + COALESCE(ms.inflicted_knockdown,0)
						, t.inflicted_tackles = t.inflicted_tackles + COALESCE(ms.inflicted_tackles,0)
						, t.inflicted_ko = t.inflicted_ko + COALESCE(ms.inflicted_ko,0)
							
						, t.inflicted_injury = t.inflicted_injury + COALESCE(ms.inflicted_injury,0)
						, t.inflicted_dead = t.inflicted_dead + COALESCE(ms.inflicted_dead,0)
						, t.meters_run = t.meters_run + COALESCE(ms.meters_run,0)
						, t.meters_pass = t.meters_pass + COALESCE(ms.meters_pass,0)
				-- THESE ONES REQUIRE LINKING TO THE OPPOSITE TEAM
						, t.sustained_interception = t.sustained_interception + COALESCE(oms.interceptions,0)
						, t.sustained_knockdown = t.sustained_knockdown + COALESCE(oms.inflicted_knockdown,0)
						, t.sustained_tackles = t.sustained_tackles + COALESCE(oms.inflicted_tackles,0)
						, t.sustained_ko = t.sustained_ko + COALESCE(oms.inflicted_ko,0)
							
						, t.sustained_injury = t.sustained_injury + COALESCE(oms.inflicted_injury,0)
						, t.sustained_dead = t.sustained_dead + COALESCE(oms.inflicted_dead,0)
						, t.sustained_meters_run = t.sustained_meters_run + COALESCE(oms.meters_run,0)
						, t.sustained_meters_pass = t.sustained_meters_pass + COALESCE(oms.meters_pass,0)
				-- END OF THESE ONES REQUIRE LINKING TO THE OPPOSITE TEAM
				-- THESE ONES REQUIRE LINKING TO THE MATCH
						, t.touchdowns = t.touchdowns + m.home_touchdown_count
						, t.sustained_touchdowns = t.sustained_touchdowns + m.away_touchdown_count
						, t.wins = t.wins + CASE WHEN m.home_touchdown_count > m.away_touchdown_count THEN 1 ELSE 0 END
						, t.draws = t.draws + CASE WHEN m.home_touchdown_count = m.away_touchdown_count THEN 1 ELSE 0 END
						, t.losses = t.losses + CASE WHEN m.home_touchdown_count < m.away_touchdown_count THEN 1 ELSE 0 END
					WHERE m.match_id = ?");
			$stmt->bindParam(1, $match_id, PDO::PARAM_INT);
			$stmt->execute();
		}



}



function import_players($conn, $upload_id,$match_id, $debug_id) {
	// Loads any players who are not in the database at all

	if ($debug_id==1) { $start_time = microtime(TRUE); echo "p1<br/>"; }

	$stmt=$conn->prepare("INSERT INTO bb_player(bb1_id,description,team_id,race_id,player_type_id,mv,st,ag,av,level,experience,base_value,current_value,squad_number,player_status_id,match_played,mvp,passes,catches,interceptions,touchdowns,inflicted_knockdown,inflicted_tackles,inflicted_ko,inflicted_stun,inflicted_injury,inflicted_dead,meters_run,meters_pass,sustained_interception,sustained_knockdown,sustained_tackles,sustained_ko,sustained_stun,sustained_injury,sustained_dead,blocks_attempted,dodges_made)
		SELECT CASE WHEN l.bStar = 1 THEN NULL ELSE l.ID END
		, l.strName
		, CASE WHEN l.bStar = 1 THEN NULL ELSE t.team_id END
		, r.race_id, pt.player_type_id, mv.human_val, st.human_val, ag.human_val, av.human_val, l.idPlayer_Levels, l.iExperience
		, l.iSalary AS base_value
		, l.iValue AS current_value
		, l.iNumber
		, CASE WHEN l.bStar = 1 THEN 4 ELSE 1 END AS player_status_id -- 1 = Current, 4 = Star Player
		, p.iMatchPlayed
		, p.iMVP
		, p.Inflicted_iPasses
		, p.Inflicted_iCatches
		, p.Inflicted_iInterceptions
		, p.Inflicted_iTouchdowns
		, p.Inflicted_iInjuries	-- actually means knockdowns
		, p.Inflicted_iTackles
		, p.Inflicted_iKO
		, p.Inflicted_iStuns
		, p.Inflicted_iCasualties
		, p.Inflicted_iDead
		, p.Inflicted_iMetersRunning
		, p.Inflicted_iMetersPassing
		, p.Sustained_iInterceptions
		, p.Sustained_iInjuries
		, p.Sustained_iTackles
		, p.Sustained_iKO
		, p.Sustained_iStuns
		, p.Sustained_iCasualties
		, p.Sustained_iDead
		, NULL
		, NULL
		FROM staging_Away_Player_Listing l
		LEFT JOIN staging_Away_Statistics_Players p ON l.upload_id = p.upload_id AND l.ID = p.idPlayer_Listing
		INNER JOIN bb_team t ON l.idTeam_Listing = t.bb1_id
		INNER JOIN bb_lkp_race r ON l.idRaces = r.bb1_id
		INNER JOIN bb_lkp_player_type pt ON l.idPlayer_Types = pt.bb1_id
		INNER JOIN bb_lkp_mv mv ON mv.bb1_id > l.Characteristics_fMovementAllowance-0.01 AND mv.bb1_id < l.Characteristics_fMovementAllowance+0.01
		INNER JOIN bb_lkp_st st ON st.bb1_id > l.Characteristics_fStrength-0.01 AND st.bb1_id < l.Characteristics_fStrength+0.01
		INNER JOIN bb_lkp_ag ag ON ag.bb1_id > l.Characteristics_fAgility-0.01 AND ag.bb1_id < l.Characteristics_fAgility+0.01
		INNER JOIN bb_lkp_av av ON av.bb1_id > l.Characteristics_fArmourValue-0.01 AND av.bb1_id < l.Characteristics_fArmourValue+0.01
		WHERE l.upload_id = ?
		AND NOT EXISTS (SELECT * FROM bb_player p WHERE p.bb1_id = l.ID)
		AND l.bStar = 0 AND l.bGenerated = 0");

	$stmt->bindParam(1, $upload_id, PDO::PARAM_INT);
	$stmt->execute();

	if ($debug_id==1) { $end_time = microtime(TRUE); echo "p2 "; echo $end_time - $start_time; echo "<br/>"; }

	$stmt=$conn->prepare("INSERT INTO bb_player(bb1_id,description,team_id,race_id,player_type_id,mv,st,ag,av,level,experience,base_value,current_value,squad_number,player_status_id,match_played,mvp,passes,catches,interceptions,touchdowns,inflicted_knockdown,inflicted_tackles,inflicted_ko,inflicted_stun,inflicted_injury,inflicted_dead,meters_run,meters_pass,sustained_interception,sustained_knockdown,sustained_tackles,sustained_ko,sustained_stun,sustained_injury,sustained_dead,blocks_attempted,dodges_made)
		SELECT CASE WHEN l.bStar = 1 THEN NULL ELSE l.ID END
		, l.strName
		, CASE WHEN l.bStar = 1 THEN NULL ELSE t.team_id END
		, r.race_id, pt.player_type_id, mv.human_val, st.human_val, ag.human_val, av.human_val, l.idPlayer_Levels, l.iExperience
		, l.iSalary AS base_value
		, l.iValue AS current_value
		, l.iNumber
		, CASE WHEN l.bStar = 1 THEN 4 ELSE 1 END AS player_status_id -- 1 = Current, 4 = Star Player
		, p.iMatchPlayed
		, p.iMVP
		, p.Inflicted_iPasses
		, p.Inflicted_iCatches
		, p.Inflicted_iInterceptions
		, p.Inflicted_iTouchdowns
		, p.Inflicted_iInjuries	-- actually means knockdowns
		, p.Inflicted_iTackles
		, p.Inflicted_iKO
		, p.Inflicted_iStuns
		, p.Inflicted_iCasualties
		, p.Inflicted_iDead
		, p.Inflicted_iMetersRunning
		, p.Inflicted_iMetersPassing
		, p.Sustained_iInterceptions
		, p.Sustained_iInjuries
		, p.Sustained_iTackles
		, p.Sustained_iKO
		, p.Sustained_iStuns
		, p.Sustained_iCasualties
		, p.Sustained_iDead
		, NULL
		, NULL
		FROM staging_Home_Player_Listing l
		LEFT JOIN staging_Home_Statistics_Players p ON l.upload_id = p.upload_id AND l.ID = p.idPlayer_Listing
		INNER JOIN bb_team t ON l.idTeam_Listing = t.bb1_id
		INNER JOIN bb_lkp_race r ON l.idRaces = r.bb1_id
		INNER JOIN bb_lkp_player_type pt ON l.idPlayer_Types = pt.bb1_id
		INNER JOIN bb_lkp_mv mv ON mv.bb1_id > l.Characteristics_fMovementAllowance-0.01 AND mv.bb1_id < l.Characteristics_fMovementAllowance+0.01
		INNER JOIN bb_lkp_st st ON st.bb1_id > l.Characteristics_fStrength-0.01 AND st.bb1_id < l.Characteristics_fStrength+0.01
		INNER JOIN bb_lkp_ag ag ON ag.bb1_id > l.Characteristics_fAgility-0.01 AND ag.bb1_id < l.Characteristics_fAgility+0.01
		INNER JOIN bb_lkp_av av ON av.bb1_id > l.Characteristics_fArmourValue-0.01 AND av.bb1_id < l.Characteristics_fArmourValue+0.01
		WHERE l.upload_id = ?
		AND NOT EXISTS (SELECT * FROM bb_player p WHERE p.bb1_id = l.ID)
		AND l.bStar = 0 AND l.bGenerated = 0");

	$stmt->bindParam(1, $upload_id, PDO::PARAM_INT);
	$stmt->execute();

	if ($debug_id==1) { $end_time = microtime(TRUE); echo "p3 "; echo $end_time - $start_time; echo "<br/>"; }

	// If the player already exists - but the stats suggest that the new stats are more up to date than what is already there... we need to do a big update statement.

	$stmt=$conn->prepare("UPDATE bb_player pl
			INNER JOIN staging_Away_Player_Listing l ON pl.bb1_id = l.ID
			INNER JOIN staging_Away_Statistics_Players p ON l.upload_id = p.upload_id AND l.ID = p.idPlayer_Listing
			INNER JOIN bb_lkp_mv mv ON mv.bb1_id > l.Characteristics_fMovementAllowance-0.01 AND mv.bb1_id < l.Characteristics_fMovementAllowance+0.01
			INNER JOIN bb_lkp_st st ON st.bb1_id > l.Characteristics_fStrength-0.01 AND st.bb1_id < l.Characteristics_fStrength+0.01
			INNER JOIN bb_lkp_ag ag ON ag.bb1_id > l.Characteristics_fAgility-0.01 AND ag.bb1_id < l.Characteristics_fAgility+0.01
			INNER JOIN bb_lkp_av av ON av.bb1_id > l.Characteristics_fArmourValue-0.01 AND av.bb1_id < l.Characteristics_fArmourValue+0.01
		SET pl.mv = mv.human_val
		, pl.st = st.human_val
		, pl.ag = ag.human_val
		, pl.av = av.human_val
		, pl.level = l.idPlayer_Levels
		, pl.experience = l.iExperience
		, pl.current_value = l.iValue*1000
		, pl.match_played = p.iMatchPlayed
		, pl.mvp = p.iMVP
		, pl.passes = p.Inflicted_iPasses
		, pl.catches = p.Inflicted_iCatches
		, pl.interceptions = p.Inflicted_iInterceptions
		, pl.touchdowns = p.Inflicted_iTouchdowns
		, pl.inflicted_knockdown = p.Inflicted_iInjuries	-- actually means knockdowns
		, pl.inflicted_tackles = p.Inflicted_iTackles
		, pl.inflicted_ko = p.Inflicted_iKO
		, pl.inflicted_stun = p.Inflicted_iStuns
		, pl.inflicted_injury = p.Inflicted_iCasualties
		, pl.inflicted_dead = p.Inflicted_iDead
		, pl.meters_run = p.Inflicted_iMetersRunning
		, pl.meters_pass = p.Inflicted_iMetersPassing
		, pl.sustained_interception = p.Sustained_iInterceptions
		, pl.sustained_knockdown = p.Sustained_iInjuries
		, pl.sustained_tackles = p.Sustained_iTackles
		, pl.sustained_ko = p.Sustained_iKO
		, pl.sustained_stun = p.Sustained_iStuns
		, pl.sustained_injury = p.Sustained_iCasualties
		, pl.sustained_dead = p.Sustained_iDead
	
		WHERE l.upload_id = ?
		AND pl.player_status_id = 1 -- active, not a star player
		AND p.iMatchPlayed >= pl.match_played -- the stats we have in staging are newer than the ones in live
		");
	$stmt->bindParam(1, $upload_id, PDO::PARAM_INT);
	$stmt->execute();

	if ($debug_id==1) { $end_time = microtime(TRUE); echo "p4 "; echo $end_time - $start_time; echo "<br/>"; }

	$stmt=$conn->prepare("UPDATE bb_player pl
			INNER JOIN staging_Home_Player_Listing l ON pl.bb1_id = l.ID
			INNER JOIN staging_Home_Statistics_Players p ON l.upload_id = p.upload_id AND l.ID = p.idPlayer_Listing
			INNER JOIN bb_lkp_mv mv ON mv.bb1_id > l.Characteristics_fMovementAllowance-0.01 AND mv.bb1_id < l.Characteristics_fMovementAllowance+0.01
			INNER JOIN bb_lkp_st st ON st.bb1_id > l.Characteristics_fStrength-0.01 AND st.bb1_id < l.Characteristics_fStrength+0.01
			INNER JOIN bb_lkp_ag ag ON ag.bb1_id > l.Characteristics_fAgility-0.01 AND ag.bb1_id < l.Characteristics_fAgility+0.01
			INNER JOIN bb_lkp_av av ON av.bb1_id > l.Characteristics_fArmourValue-0.01 AND av.bb1_id < l.Characteristics_fArmourValue+0.01
		SET pl.mv = mv.human_val
		, pl.st = st.human_val
		, pl.ag = ag.human_val
		, pl.av = av.human_val
		, pl.level = l.idPlayer_Levels
		, pl.experience = l.iExperience
		, pl.current_value = l.iValue*1000
		, pl.match_played = p.iMatchPlayed
		, pl.mvp = p.iMVP
		, pl.passes = p.Inflicted_iPasses
		, pl.catches = p.Inflicted_iCatches
		, pl.interceptions = p.Inflicted_iInterceptions
		, pl.touchdowns = p.Inflicted_iTouchdowns
		, pl.inflicted_knockdown = p.Inflicted_iInjuries	-- actually means knockdowns
		, pl.inflicted_tackles = p.Inflicted_iTackles
		, pl.inflicted_ko = p.Inflicted_iKO
		, pl.inflicted_stun = p.Inflicted_iStuns
		, pl.inflicted_injury = p.Inflicted_iCasualties
		, pl.inflicted_dead = p.Inflicted_iDead
		, pl.meters_run = p.Inflicted_iMetersRunning
		, pl.meters_pass = p.Inflicted_iMetersPassing
		, pl.sustained_interception = p.Sustained_iInterceptions
		, pl.sustained_knockdown = p.Sustained_iInjuries
		, pl.sustained_tackles = p.Sustained_iTackles
		, pl.sustained_ko = p.Sustained_iKO
		, pl.sustained_stun = p.Sustained_iStuns
		, pl.sustained_injury = p.Sustained_iCasualties
		, pl.sustained_dead = p.Sustained_iDead
		WHERE l.upload_id = ?
		AND pl.player_status_id = 1 -- active, not a star player
		AND p.iMatchPlayed >= pl.match_played -- the stats we have in staging are newer than the ones in live
		");
	$stmt->bindParam(1, $upload_id, PDO::PARAM_INT);
	$stmt->execute();

	if ($debug_id==1) { $end_time = microtime(TRUE); echo "p5 "; echo $end_time - $start_time; echo "<br/>"; }

	// Now put one-off players in... mercs/journeymen
	// star players also go here... as otherwise there is no way to know what team they played for.
	// Put lots of nulls in there if it is a star player... as their attributes are in the lkp_player_type table
	$stmt=$conn->prepare("INSERT INTO bb_player_oneoff (match_id, team_id, bb1_id, player_type_id, player_status_id
							, name, mv, st, ag, av, salary, value)
			SELECT :match_id, team_id, l.ID, pt.player_type_id, CASE WHEN l.bStar = 1 THEN 4 WHEN l.iSalary = l.iValue*1000 THEN 6 ELSE 5 END
				, CASE WHEN l.bStar = 1 THEN NULL ELSE l.strName END
				, CASE WHEN l.bStar = 1 THEN NULL ELSE st.human_val END
				, CASE WHEN l.bStar = 1 THEN NULL ELSE mv.human_val END
				, CASE WHEN l.bStar = 1 THEN NULL ELSE ag.human_val END
				, CASE WHEN l.bStar = 1 THEN NULL ELSE av.human_val END
				, CASE WHEN l.bStar = 1 THEN NULL ELSE l.iSalary END
				, CASE WHEN l.bStar = 1 THEN NULL ELSE l.iValue*1000 END
		FROM staging_Away_Player_Listing l
		INNER JOIN bb_team t ON l.idTeam_Listing = t.bb1_id
		INNER JOIN bb_lkp_player_type pt ON l.idPlayer_Types = pt.bb1_id
		INNER JOIN bb_lkp_mv mv ON mv.bb1_id > l.Characteristics_fMovementAllowance-0.01 AND mv.bb1_id < l.Characteristics_fMovementAllowance+0.01
		INNER JOIN bb_lkp_st st ON st.bb1_id > l.Characteristics_fStrength-0.01 AND st.bb1_id < l.Characteristics_fStrength+0.01
		INNER JOIN bb_lkp_ag ag ON ag.bb1_id > l.Characteristics_fAgility-0.01 AND ag.bb1_id < l.Characteristics_fAgility+0.01
		INNER JOIN bb_lkp_av av ON av.bb1_id > l.Characteristics_fArmourValue-0.01 AND av.bb1_id < l.Characteristics_fArmourValue+0.01
		WHERE l.upload_id = :upload_id
		AND NOT EXISTS (SELECT * FROM bb_player_oneoff p WHERE p.team_id = t.team_id AND p.bb1_id = l.ID AND p.match_id = :match_id)
		AND l.bGenerated = 1");
	$stmt->bindParam(':match_id', $match_id, PDO::PARAM_INT);
	$stmt->bindParam(':upload_id', $upload_id, PDO::PARAM_INT);
	$stmt->execute();

	if ($debug_id==1) { $end_time = microtime(TRUE); echo "p6 "; echo $end_time - $start_time; echo "<br/>"; }

	$stmt=$conn->prepare("INSERT INTO bb_player_oneoff (match_id, team_id, bb1_id, player_type_id, player_status_id
							, name, mv, st, ag, av, salary, value)
			SELECT :match_id, team_id, l.ID, pt.player_type_id, CASE WHEN l.bStar = 1 THEN 4 WHEN l.iSalary = l.iValue*1000 THEN 6 ELSE 5 END
				, CASE WHEN l.bStar = 1 THEN NULL ELSE l.strName END
				, CASE WHEN l.bStar = 1 THEN NULL ELSE st.human_val END
				, CASE WHEN l.bStar = 1 THEN NULL ELSE mv.human_val END
				, CASE WHEN l.bStar = 1 THEN NULL ELSE ag.human_val END
				, CASE WHEN l.bStar = 1 THEN NULL ELSE av.human_val END
				, CASE WHEN l.bStar = 1 THEN NULL ELSE l.iSalary END
				, CASE WHEN l.bStar = 1 THEN NULL ELSE l.iValue*1000 END
		FROM staging_Home_Player_Listing l
		INNER JOIN bb_team t ON l.idTeam_Listing = t.bb1_id
		INNER JOIN bb_lkp_player_type pt ON l.idPlayer_Types = pt.bb1_id
		INNER JOIN bb_lkp_mv mv ON mv.bb1_id > l.Characteristics_fMovementAllowance-0.01 AND mv.bb1_id < l.Characteristics_fMovementAllowance+0.01
		INNER JOIN bb_lkp_st st ON st.bb1_id > l.Characteristics_fStrength-0.01 AND st.bb1_id < l.Characteristics_fStrength+0.01
		INNER JOIN bb_lkp_ag ag ON ag.bb1_id > l.Characteristics_fAgility-0.01 AND ag.bb1_id < l.Characteristics_fAgility+0.01
		INNER JOIN bb_lkp_av av ON av.bb1_id > l.Characteristics_fArmourValue-0.01 AND av.bb1_id < l.Characteristics_fArmourValue+0.01
		WHERE l.upload_id = :upload_id
		AND NOT EXISTS (SELECT * FROM bb_player_oneoff p WHERE p.team_id = t.team_id AND p.bb1_id = l.ID AND p.match_id = :match_id)
		AND l.bGenerated = 1");
	$stmt->bindParam(':match_id', $match_id, PDO::PARAM_INT);
	$stmt->bindParam(':upload_id', $upload_id, PDO::PARAM_INT);
	$stmt->execute();

	if ($debug_id==1) { $end_time = microtime(TRUE); echo "p7 "; echo $end_time - $start_time; echo "<br/>"; }

	// Clear existing injuries. ASSUMPTION - no injury lasts more than 1 match
	// I'm sure there's a few ways of doing this... and that this isn't the best...
	// Simply assume that ANYONE who played in the last match... their injury is no longer active now the match is done.
	// As this is processed before the the match player stats (and associated injuries), it should be OK
	$stmt=$conn->prepare("UPDATE bb_player_casualty pc
		INNER JOIN bb_player p
			ON pc.player_id = p.player_id
		INNER JOIN staging_Away_Player_Listing s
			ON p.bb1_id = s.ID
		INNER JOIN bb_match mc
			ON pc.match_id_sustained = mc.match_id
		CROSS JOIN
			(SELECT MAX(m2.match_date) last_match_date FROM bb_upload u
			INNER JOIN bb_match m1 ON u.match_id = m1.match_id
			INNER JOIN bb_match m2 ON m1.away_team_id = m2.home_team_id OR m1.away_team_id = m2.away_team_id 
			WHERE upload_id = ?
			AND m1.match_id <> m2.match_id
			AND m2.match_date < m1.match_date) zzz
		SET pc.active = 0
			, match_id_missed = ?
		WHERE pc.active = 1
		AND s.iMatchSuspended = 1
		AND s.upload_id = ?
		AND mc.match_date = zzz.last_match_date");

	$stmt->bindParam(1, $upload_id, PDO::PARAM_INT);
	$stmt->bindParam(2, $match_id, PDO::PARAM_INT);
	$stmt->bindParam(3, $upload_id, PDO::PARAM_INT);
	$stmt->execute();

	if ($debug_id==1) { $end_time = microtime(TRUE); echo "p8 "; echo $end_time - $start_time; echo "<br/>"; }

	$stmt=$conn->prepare("UPDATE bb_player_casualty pc
		INNER JOIN bb_player p
			ON pc.player_id = p.player_id
		INNER JOIN staging_Home_Player_Listing s
			ON p.bb1_id = s.ID
		INNER JOIN bb_match mc
			ON pc.match_id_sustained = mc.match_id
		CROSS JOIN
			(SELECT MAX(m2.match_date) last_match_date FROM bb_upload u
			INNER JOIN bb_match m1 ON u.match_id = m1.match_id
			INNER JOIN bb_match m2 ON m1.home_team_id = m2.home_team_id OR m1.home_team_id = m2.away_team_id 
			WHERE upload_id = ?
			AND m1.match_id <> m2.match_id
			AND m2.match_date < m1.match_date) zzz
		SET pc.active = 0
			, match_id_missed = ?
		WHERE pc.active = 1
		AND s.iMatchSuspended = 1
		AND s.upload_id = ?
		AND mc.match_date = zzz.last_match_date");

	$stmt->bindParam(1, $upload_id, PDO::PARAM_INT);
	$stmt->bindParam(2, $match_id, PDO::PARAM_INT);
	$stmt->bindParam(3, $upload_id, PDO::PARAM_INT);
	$stmt->execute();

	if ($debug_id==1) { $end_time = microtime(TRUE); echo "p9 "; echo $end_time - $start_time; echo "<br/>"; }

	// INJURIES WILL HAVE A BLANK MATCH_ID_SUSTAINED, AND AN ACTIVE FLAG OF 0

	$stmt=$conn->prepare("INSERT INTO bb_player_casualty (player_id, casualty_id, casualty_status_id, match_id_sustained, match_id_missed, active)
	SELECT p.player_id, c.casualty_id, 1, NULL, ?, 0
	FROM staging_Away_Player_Casualties s
	INNER JOIN bb_player p ON s.idPlayer_Listing = p.bb1_id
	INNER JOIN bb_lkp_casualty c ON s.idPlayer_Casualty_Types = c.bb1_id
	WHERE s.upload_id = ?
	AND NOT EXISTS (SELECT * FROM bb_player_casualty c1 WHERE c1.player_id = p.player_id AND c1.casualty_id = c.casualty_id AND c.match_id_missed = c1.match_id_missed)");

	$stmt->bindParam(1, $match_id, PDO::PARAM_INT);
	$stmt->bindParam(2, $upload_id, PDO::PARAM_INT);
	$stmt->execute();

	if ($debug_id==1) { $end_time = microtime(TRUE); echo "p10 "; echo $end_time - $start_time; echo "<br/>"; }

	$stmt=$conn->prepare("INSERT INTO bb_player_casualty (player_id, casualty_id, casualty_status_id, match_id_sustained, match_id_missed, active)
	SELECT p.player_id, c.casualty_id, 1, NULL, ?, 0
	FROM staging_Home_Player_Casualties s
	INNER JOIN bb_player p ON s.idPlayer_Listing = p.bb1_id
	INNER JOIN bb_lkp_casualty c ON s.idPlayer_Casualty_Types = c.bb1_id
	WHERE s.upload_id = ?
	AND NOT EXISTS (SELECT * FROM bb_player_casualty c1 WHERE c1.player_id = p.player_id AND c1.casualty_id = c.casualty_id AND c.match_id_missed = c1.match_id_missed)");

	$stmt->bindParam(1, $match_id, PDO::PARAM_INT);
	$stmt->bindParam(2, $upload_id, PDO::PARAM_INT);
	$stmt->execute();

	if ($debug_id==1) { $end_time = microtime(TRUE); echo "p11 "; echo $end_time - $start_time; echo "<br/>"; }

	// Add skills that the player have, if we didn't know about them already.
	// Skill_order can be populated somewhere else. A function of match_id_debut and such

	$stmt=$conn->prepare("INSERT INTO bb_player_skill (player_id, skill_id, skill_order, match_id_debut)
		SELECT p.player_id, s.idSkill_Listing, NULL, ?
		FROM staging_Away_Player_Skills s
		INNER JOIN bb_player p ON s.idPlayer_Listing = p.bb1_id
		WHERE s.upload_id = ?
		AND NOT EXISTS (SELECT * FROM bb_player_skill s2 WHERE s2.player_id = p.player_id AND s2.skill_id = s.idSkill_Listing)");

	$stmt->bindParam(1, $match_id, PDO::PARAM_INT);
	$stmt->bindParam(2, $upload_id, PDO::PARAM_INT);
	$stmt->execute();

	if ($debug_id==1) { $end_time = microtime(TRUE); echo "p12 "; echo $end_time - $start_time; echo "<br/>"; }

	$stmt=$conn->prepare("INSERT INTO bb_player_skill (player_id, skill_id, skill_order, match_id_debut)
		SELECT p.player_id, s.idSkill_Listing, NULL, ?
		FROM staging_Home_Player_Skills s
		INNER JOIN bb_player p ON s.idPlayer_Listing = p.bb1_id
		WHERE s.upload_id = ?
		AND NOT EXISTS (SELECT * FROM bb_player_skill s2 WHERE s2.player_id = p.player_id AND s2.skill_id = s.idSkill_Listing)");

	$stmt->bindParam(1, $match_id, PDO::PARAM_INT);
	$stmt->bindParam(2, $upload_id, PDO::PARAM_INT);
	$stmt->execute();

	if ($debug_id==1) { $end_time = microtime(TRUE); echo "p13 "; echo $end_time - $start_time; echo "<br/>"; }

	// Now skills for oneoff players
	$stmt=$conn->prepare("INSERT INTO bb_player_oneoff_skill (match_id, team_id, bb1_id, skill_id)
		SELECT :match_id, t.team_id, l.ID, s.idSkill_Listing
		FROM staging_Away_Player_Skills s
		INNER JOIN staging_Away_Player_Listing l ON s.idPlayer_Listing = l.ID AND s.upload_id = l.upload_id
		INNER JOIN bb_team t ON l.idTeam_Listing = t.bb1_id
		WHERE s.upload_id = :upload_id
		AND NOT EXISTS (SELECT * FROM bb_player_oneoff_skill s2 WHERE s2.bb1_id = l.ID AND s2.skill_id = s.idSkill_Listing AND s2.team_id = t.team_id AND :match_id = s2.match_id)
		AND l.bGenerated = 1");

	$stmt->bindParam(':match_id', $match_id, PDO::PARAM_INT);
	$stmt->bindParam(':upload_id', $upload_id, PDO::PARAM_INT);
	$stmt->execute();

	if ($debug_id==1) { $end_time = microtime(TRUE); echo "p14 "; echo $end_time - $start_time; echo "<br/>"; }

	$stmt=$conn->prepare("INSERT INTO bb_player_oneoff_skill (match_id, team_id, bb1_id, skill_id)
		SELECT :match_id, t.team_id, l.ID, s.idSkill_Listing
		FROM staging_Home_Player_Skills s
		INNER JOIN staging_Home_Player_Listing l ON s.idPlayer_Listing = l.ID AND s.upload_id = l.upload_id
		INNER JOIN bb_team t ON l.idTeam_Listing = t.bb1_id
		WHERE s.upload_id = :upload_id
		AND NOT EXISTS (SELECT * FROM bb_player_oneoff_skill s2 WHERE s2.bb1_id = l.ID AND s2.skill_id = s.idSkill_Listing AND s2.team_id = t.team_id AND :match_id = s2.match_id)
		AND l.bGenerated = 1");

	$stmt->bindParam(':match_id', $match_id, PDO::PARAM_INT);
	$stmt->bindParam(':upload_id', $upload_id, PDO::PARAM_INT);
	$stmt->execute();

	if ($debug_id==1) { $end_time = microtime(TRUE); echo "p15 "; echo $end_time - $start_time; echo "<br/>"; }

	// Now check for existing skills, is the match we have uploaded an older match with that same skill? If so, update match_id_debut

	$stmt=$conn->prepare("UPDATE bb_player_skill ps
		INNER JOIN bb_player p ON p.player_id = ps.player_id
		INNER JOIN staging_Away_Player_Skills s ON s.idPlayer_Listing = p.bb1_id AND ps.skill_id = s.idSkill_Listing AND s.upload_id = :upload_id
		CROSS JOIN bb_match lm
		INNER JOIN bb_match om ON ps.match_id_debut = om.match_id
	SET ps.match_id_debut = :match_id
	WHERE lm.match_id = :match_id
	AND lm.match_date < om.match_date");

	$stmt->bindParam(':upload_id', $match_id, PDO::PARAM_INT);
	$stmt->bindParam(':match_id', $match_id, PDO::PARAM_INT);
	$stmt->execute();

	if ($debug_id==1) { $end_time = microtime(TRUE); echo "p16 "; echo $end_time - $start_time; echo "<br/>"; }

	$stmt=$conn->prepare("UPDATE bb_player_skill ps
		INNER JOIN bb_player p ON p.player_id = ps.player_id
		INNER JOIN staging_Home_Player_Skills s ON s.idPlayer_Listing = p.bb1_id AND ps.skill_id = s.idSkill_Listing AND s.upload_id = :upload_id
		CROSS JOIN bb_match lm
		INNER JOIN bb_match om ON ps.match_id_debut = om.match_id
	SET ps.match_id_debut = :match_id
	WHERE lm.match_id = :match_id
	AND lm.match_date < om.match_date");

	$stmt->bindParam(':upload_id', $match_id, PDO::PARAM_INT);
	$stmt->bindParam(':match_id', $match_id, PDO::PARAM_INT);
	$stmt->execute();

	if ($debug_id==1) { $end_time = microtime(TRUE); echo "p17 "; echo $end_time - $start_time; echo "<br/>"; }

	// PROCESS RETIREMENTS - ARE THERE ANY PLAYERS NOT IN THIS MATCH WHO WERE IN A PREVIOUS ONE???
	// IF SO, ADD A CASUALTY RECORD FOR THEM, AND CHANGE THEIR PLAYER STATUS. SHOULD BE ABLE TO DO HOME/AWAY ALL IN ONE STATEMENT??
	
	// first get the date!
	
	// Active players only. We don't want to retire players who are dead, already retired, or star players
	// 28-Apr-2016. Changed to check that the player's other match was before the uploaded match
	$stmt=$conn->prepare("
		SELECT DISTINCT p.player_id
		FROM staging_Away_Team_Listing st
		INNER JOIN bb_team t ON st.ID = t.bb1_id
		INNER JOIN bb_player p ON p.team_id = t.team_id
		INNER JOIN bb_match curr_m ON 1 = 1
		INNER JOIN bb_player_match_stats pms ON p.player_id = pms.player_id
		INNER JOIN bb_match m ON pms.match_id = m.match_id
		WHERE p.player_status_id IN (1)
		AND NOT EXISTS (SELECT * FROM staging_Away_Player_Listing l WHERE p.bb1_id = l.ID AND l.upload_id = st.upload_id)
		AND st.upload_id = :upload_id
		AND curr_m.match_id = :match_id
		AND m.match_date < curr_m.match_date
		UNION ALL
		SELECT DISTINCT p.player_id
		FROM staging_Home_Team_Listing st
		INNER JOIN bb_team t ON st.ID = t.bb1_id
		INNER JOIN bb_player p ON p.team_id = t.team_id
		INNER JOIN bb_match curr_m ON 1 = 1
		INNER JOIN bb_player_match_stats pms ON p.player_id = pms.player_id
		INNER JOIN bb_match m ON pms.match_id = m.match_id
		WHERE p.player_status_id IN (1)
		AND NOT EXISTS (SELECT * FROM staging_Home_Player_Listing l WHERE p.bb1_id = l.ID  AND l.upload_id = st.upload_id)
		AND st.upload_id = :upload_id
		AND curr_m.match_id = :match_id
		AND m.match_date < curr_m.match_date");

	$stmt->bindParam(':upload_id', $upload_id, PDO::PARAM_INT);
	$stmt->bindParam(':match_id', $match_id, PDO::PARAM_INT);
	if (!$stmt->execute()) { echo "ERROR!</br>"; }
		else { echo "Worked.</br>"; }

	if ($debug_id==1) { $end_time = microtime(TRUE); echo "p18 "; echo $end_time - $start_time; echo "<br/>"; }

	// This is sub-optimal, we are effectively using a cursor when there is a better way. At least it is clear what is happening.
	while ($row = $stmt->fetch(PDO::FETCH_ASSOC)){
		//echo $row['player_id']."<br/>";
 
		$stmt_two=$conn->prepare("INSERT INTO bb_player_casualty (player_id, casualty_id, casualty_status_id, match_id_sustained, match_id_missed, active)
			SELECT ?, 19, 1, ?, NULL, 1");


		$stmt_two->bindParam(1, $row['player_id'], PDO::PARAM_INT);
		$stmt_two->bindParam(2, $match_id, PDO::PARAM_INT);
		$stmt_two->execute();



		$stmt_two=$conn->prepare("UPDATE bb_player SET player_status_id = 3 WHERE player_id = ?");
		$stmt_two->bindParam(1, $row['player_id'], PDO::PARAM_INT);
		$stmt_two->execute();
	}

	if ($debug_id==1) { $end_time = microtime(TRUE); echo "pEND "; echo $end_time - $start_time; echo "<br/>"; }

} // end of import_players

function import_match_player_stats($conn,$upload_id,$match_id,$latest_match_away,$latest_match_home) {

	$stmt=$conn->prepare("INSERT INTO bb_player_match_stats(player_id,match_id,oneoff_id,match_played,mvp,passes,catches,interceptions,touchdowns,inflicted_knockdown,inflicted_tackles,inflicted_ko,inflicted_stun,inflicted_injury,inflicted_dead,meters_run,meters_pass,sustained_interception,sustained_knockdown,sustained_tackles,sustained_ko,sustained_stun,sustained_injury,sustained_dead
							, player_type_id, player_status_id)
		SELECT CASE WHEN lst.bStar = 0 AND lst.bGenerated = 0 THEN pl.player_id ELSE NULL END
		, ?
		, CASE WHEN lst.bGenerated = 1 THEN lst.ID ELSE NULL END
		, p.iMatchPlayed
		, p.iMVP
		, p.Inflicted_iPasses
		, p.Inflicted_iCatches
		, p.Inflicted_iInterceptions
		, p.Inflicted_iTouchdowns
		, p.Inflicted_iInjuries	-- actually means knockdowns
		, p.Inflicted_iTackles
		, p.Inflicted_iKO
		, p.Inflicted_iStuns
		, p.Inflicted_iCasualties
		, p.Inflicted_iDead
		, p.Inflicted_iMetersRunning
		, p.Inflicted_iMetersPassing
		, p.Sustained_iInterceptions
		, p.Sustained_iInjuries
		, p.Sustained_iTackles
		, p.Sustained_iKO
		, p.Sustained_iStuns
		, p.Sustained_iCasualties
		, p.Sustained_iDead
		, pt.player_type_id
		, CASE WHEN lst.ID IS NULL THEN 7 WHEN lst.bStar = 1 THEN 4 WHEN lst.bGenerated=1 AND lst.iSalary = lst.iValue*1000 THEN 6 
			WHEN lst.bGenerated = 1 THEN 5 ELSE 1 END
	FROM staging_mr_Away_Statistics_Players p
	LEFT JOIN staging_Away_Player_Listing lst ON p.upload_id = lst.upload_id AND p.idPlayer_Listing = lst.ID
	LEFT JOIN bb_player pl ON p.idPlayer_Listing = pl.bb1_id
	LEFT JOIN bb_lkp_player_type pt ON pt.bb1_id = lst.idPlayer_Types
	WHERE p.upload_id = ?
	AND NOT EXISTS (SELECT * FROM bb_player_match_stats c WHERE match_id = ? AND (
				(c.player_id > 0 AND c.player_id = pl.player_id) OR
				(c.player_id = 0 AND c.oneoff_id > 0 AND c.oneoff_id = lst.ID) OR
				(lst.bStar = 1 AND c.player_type_id = pt.player_type_id)
			))");

	$stmt->bindParam(1, $match_id, PDO::PARAM_INT);
	$stmt->bindParam(2, $upload_id, PDO::PARAM_INT);
	$stmt->bindParam(3, $match_id, PDO::PARAM_INT);
	$stmt->execute();


	$stmt=$conn->prepare("INSERT INTO bb_player_match_stats(player_id,match_id,oneoff_id,match_played,mvp,passes,catches,interceptions,touchdowns,inflicted_knockdown,inflicted_tackles,inflicted_ko,inflicted_stun,inflicted_injury,inflicted_dead,meters_run,meters_pass,sustained_interception,sustained_knockdown,sustained_tackles,sustained_ko,sustained_stun,sustained_injury,sustained_dead
							, player_type_id, player_status_id)
		SELECT CASE WHEN lst.bStar = 0 AND lst.bGenerated = 0 THEN pl.player_id ELSE NULL END
		, :match_id
		, CASE WHEN lst.bGenerated = 1 THEN lst.ID ELSE NULL END
		, p.iMatchPlayed
		, p.iMVP
		, p.Inflicted_iPasses
		, p.Inflicted_iCatches
		, p.Inflicted_iInterceptions
		, p.Inflicted_iTouchdowns
		, p.Inflicted_iInjuries	-- actually means knockdowns
		, p.Inflicted_iTackles
		, p.Inflicted_iKO
		, p.Inflicted_iStuns
		, p.Inflicted_iCasualties
		, p.Inflicted_iDead
		, p.Inflicted_iMetersRunning
		, p.Inflicted_iMetersPassing
		, p.Sustained_iInterceptions
		, p.Sustained_iInjuries
		, p.Sustained_iTackles
		, p.Sustained_iKO
		, p.Sustained_iStuns
		, p.Sustained_iCasualties
		, p.Sustained_iDead
		, pt.player_type_id
		, CASE WHEN lst.ID IS NULL THEN 7 WHEN lst.bStar = 1 THEN 4 WHEN lst.bGenerated=1 AND lst.iSalary = lst.iValue*1000 THEN 6 
			WHEN lst.bGenerated = 1 THEN 5 ELSE 1 END
	FROM staging_mr_Home_Statistics_Players p
	LEFT JOIN staging_Home_Player_Listing lst ON p.upload_id = lst.upload_id AND p.idPlayer_Listing = lst.ID
	LEFT JOIN bb_player pl ON p.idPlayer_Listing = pl.bb1_id
	LEFT JOIN bb_lkp_player_type pt ON pt.bb1_id = lst.idPlayer_Types
	WHERE p.upload_id = :upload_id
	AND NOT EXISTS (SELECT * FROM bb_player_match_stats c WHERE match_id = :match_id AND (
				(c.player_id > 0 AND c.player_id = pl.player_id) OR
				(c.player_id = 0 AND c.oneoff_id > 0 AND c.oneoff_id = lst.ID) OR
				(lst.bStar = 1 AND c.player_type_id = pt.player_type_id)
			))");

	$stmt->bindParam(':match_id', $match_id, PDO::PARAM_INT);
	$stmt->bindParam(':upload_id', $upload_id, PDO::PARAM_INT);
	$stmt->execute();

	// Process deaths for both teams, in one statement! Woohoo.
	// THINK ABOUT STAR PLAYERS HERE...
	$stmt=$conn->prepare("UPDATE bb_player p
				INNER JOIN bb_player_match_stats s ON p.player_id = s.player_id
				SET p.player_status_id = 2
				WHERE s.match_id = ?
				AND s.sustained_dead > 0");
	$stmt->bindParam(1, $match_id, PDO::PARAM_INT);
	$stmt->execute();

	// Casualty Status = 1 means "Valid". Active is set, if this is the most recent match for the player.
	// THINK ABOUT STAR PLAYERS HERE...

	$stmt = $conn->prepare("INSERT INTO bb_player_casualty (player_id, casualty_id, casualty_status_id, match_id_sustained, active)
		SELECT pl.player_id, c.casualty_id, 1, ?, CASE WHEN pl.player_type_id = 4 THEN 0 ELSE ? END
		FROM staging_mr_Away_Player_Casualties s
		INNER JOIN bb_player pl ON s.idPlayer_Listing = pl.bb1_id
		INNER JOIN bb_lkp_casualty c ON s.idPlayer_Casualty_Types = c.bb1_id
		WHERE s.upload_id = ?
		AND NOT EXISTS (SELECT * FROM bb_player_casualty c2 WHERE pl.player_id = c2.player_id AND c.casualty_id = c2.casualty_id AND c2.match_id_sustained = ?)");

	$stmt->bindParam(1, $match_id, PDO::PARAM_INT);
	if ($latest_match_away) { $a=1; }
		else { $a=2; }
	$stmt->bindParam(2, $a, PDO::PARAM_INT);
	$stmt->bindParam(3, $upload_id, PDO::PARAM_INT);
	$stmt->bindParam(4, $match_id, PDO::PARAM_INT);
	$stmt->execute();

	// Now, home casualties

	$stmt = $conn->prepare("INSERT INTO bb_player_casualty (player_id, casualty_id, casualty_status_id, match_id_sustained, active)
		SELECT pl.player_id, c.casualty_id, 1, ?, CASE WHEN pl.player_type_id = 4 THEN 0 ELSE ? END
		FROM staging_mr_Home_Player_Casualties s
		INNER JOIN bb_player pl ON s.idPlayer_Listing = pl.bb1_id
		INNER JOIN bb_lkp_casualty c ON s.idPlayer_Casualty_Types = c.bb1_id
		WHERE s.upload_id = ?
		AND NOT EXISTS (SELECT * FROM bb_player_casualty c2 WHERE pl.player_id = c2.player_id AND c.casualty_id = c2.casualty_id AND c2.match_id_sustained = ?)");

	$stmt->bindParam(1, $match_id, PDO::PARAM_INT);
	if ($latest_match_home) { $a=1; }
		else { $a=2; }
	$stmt->bindParam(2, $a, PDO::PARAM_INT);
	$stmt->bindParam(3, $upload_id, PDO::PARAM_INT);
	$stmt->bindParam(4, $match_id, PDO::PARAM_INT);
	$stmt->execute();

	// IF THIS IS THE LATEST MATCH, THE PLAYER'S CURRENT STATS NEEDS TO BE UPDATE TO INCLUDE THE LATEST MATCH!
	// THINK ABOUT STAR PLAYERS HERE. Their stats coming into a match will always be zero. So this check won't work.
	// Maybe we need to mark games stats as having been uploaded to the overall player stats ??????

	// Maybe we can get rid of the latest_match check... as it may not be the team's latest match but it could be the player's latest match?

	if ($latest_match_away) {
		$stmt=$conn->prepare("
		UPDATE bb_player p
		INNER JOIN bb_player_match_stats ms
			ON p.player_id = ms.player_id
		INNER JOIN bb_match m
			ON ms.match_id = m.match_id
			AND p.team_id = m.away_team_id
		INNER JOIN staging_Away_Statistics_Players old_stat
			ON p.bb1_id = old_stat.idPlayer_Listing
			AND old_stat.upload_id = ?
		SET p.match_played = p.match_played + ms.match_played
		   ,p.experience = p.experience + (5*ms.mvp) + (3*ms.touchdowns) + (2*ms.interceptions) + (2*ms.inflicted_injury) + ms.passes
		   ,p.mvp = p.mvp + ms.mvp
		   ,p.passes = p.passes + ms.passes
		   ,p.catches = p.catches + ms.catches
		   ,p.interceptions = p.interceptions + ms.interceptions
		   ,p.touchdowns = p.touchdowns + ms.touchdowns
		   ,p.inflicted_knockdown = p.inflicted_knockdown + ms.inflicted_knockdown
		   ,p.inflicted_tackles = p.inflicted_tackles + ms.inflicted_tackles
		   ,p.inflicted_ko = p.inflicted_ko + ms.inflicted_ko
		   ,p.inflicted_stun = p.inflicted_stun + ms.inflicted_stun
		   ,p.inflicted_injury = p.inflicted_injury + ms.inflicted_injury
		   ,p.inflicted_dead = p.inflicted_dead + ms.inflicted_dead
		   ,p.meters_run = p.meters_run + ms.meters_run
		   ,p.meters_pass = p.meters_pass + ms.meters_pass
		   ,p.sustained_interception = p.sustained_interception + ms.sustained_interception
		   ,p.sustained_knockdown = p.sustained_knockdown + ms.sustained_knockdown
		   ,p.sustained_tackles = p.sustained_tackles + ms.sustained_tackles
		   ,p.sustained_ko = p.sustained_ko + ms.sustained_ko
		   ,p.sustained_stun = p.sustained_stun + ms.sustained_stun
		   ,p.sustained_injury = p.sustained_injury + ms.sustained_injury
		   ,p.sustained_dead = p.sustained_dead + ms.sustained_dead
		WHERE ms.match_id = ?
		AND p.match_played = old_stat.iMatchPlayed 
		"); // -- double-checking that these stats haven't been added to the total already
		$stmt->bindParam(1, $upload_id, PDO::PARAM_INT);
		$stmt->bindParam(2, $match_id, PDO::PARAM_INT);
		$stmt->execute();
	}

	if ($latest_match_home) {
		$stmt=$conn->prepare("
		UPDATE bb_player p
		INNER JOIN bb_player_match_stats ms
			ON p.player_id = ms.player_id
		INNER JOIN bb_match m
			ON ms.match_id = m.match_id
			AND p.team_id = m.home_team_id
		INNER JOIN staging_Home_Statistics_Players old_stat
			ON p.bb1_id = old_stat.idPlayer_Listing
			AND old_stat.upload_id = ?
		SET p.match_played = p.match_played + ms.match_played
		   ,p.experience = p.experience + (5*ms.mvp) + (3*ms.touchdowns) + (2*ms.interceptions) + (2*ms.inflicted_injury) + ms.passes
		   ,p.mvp = p.mvp + ms.mvp
		   ,p.passes = p.passes + ms.passes
		   ,p.catches = p.catches + ms.catches
		   ,p.interceptions = p.interceptions + ms.interceptions
		   ,p.touchdowns = p.touchdowns + ms.touchdowns
		   ,p.inflicted_knockdown = p.inflicted_knockdown + ms.inflicted_knockdown
		   ,p.inflicted_tackles = p.inflicted_tackles + ms.inflicted_tackles
		   ,p.inflicted_ko = p.inflicted_ko + ms.inflicted_ko
		   ,p.inflicted_stun = p.inflicted_stun + ms.inflicted_stun
		   ,p.inflicted_injury = p.inflicted_injury + ms.inflicted_injury
		   ,p.inflicted_dead = p.inflicted_dead + ms.inflicted_dead
		   ,p.meters_run = p.meters_run + ms.meters_run
		   ,p.meters_pass = p.meters_pass + ms.meters_pass
		   ,p.sustained_interception = p.sustained_interception + ms.sustained_interception
		   ,p.sustained_knockdown = p.sustained_knockdown + ms.sustained_knockdown
		   ,p.sustained_tackles = p.sustained_tackles + ms.sustained_tackles
		   ,p.sustained_ko = p.sustained_ko + ms.sustained_ko
		   ,p.sustained_stun = p.sustained_stun + ms.sustained_stun
		   ,p.sustained_injury = p.sustained_injury + ms.sustained_injury
		   ,p.sustained_dead = p.sustained_dead + ms.sustained_dead
		WHERE ms.match_id = ?
		AND p.match_played = old_stat.iMatchPlayed
		"); //  -- double-checking that these stats haven't been added to the total already
		$stmt->bindParam(1, $upload_id, PDO::PARAM_INT);
		$stmt->bindParam(2, $match_id, PDO::PARAM_INT);
		$stmt->execute();
	}
}

 ?>
