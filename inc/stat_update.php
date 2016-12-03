<?php 

function update_league_table($conn, $upload_id, $domain_id, $input_competition_id = 0) {

	// find the competition id
	if ($input_competition_id <> 0) {
		$competition_id = $input_competition_id;
	}
	else {
		$stmt=$conn->prepare("SELECT default_competition_id FROM bb_upload WHERE upload_id = ?");
		$stmt->bindParam(1, $upload_id, PDO::PARAM_INT);
		$stmt->execute();
		if($stmt->rowCount()==0)
			{ die("Invalid competition id."); }
		$competition_id = $stmt->fetchColumn(0);
	}
	
		
	// If it's a 1-group league, create table if there isn't already one
	$stmt=$conn->prepare("INSERT INTO bb_stat_comp_table (competition_id, domain_id, group_id, table_name)
			SELECT competition_id, domain_id, 1, 'League table'
			FROM bb_competition c
				INNER JOIN bb_lkp_competition_type ct ON c.competition_type_id = ct.competition_type_id
			WHERE c.competition_id = ? AND c.domain_id = ?
			AND ct.start_league = 1 AND ct.start_group_count = 1
			AND NOT EXISTS (SELECT * FROM bb_stat_comp_table sct
					WHERE sct.competition_id = c.competition_id
					AND sct.domain_id = c.domain_id)");

	$stmt->bindParam(1, $competition_id, PDO::PARAM_INT);
	$stmt->bindParam(2, $domain_id, PDO::PARAM_INT);
	$stmt->execute();

	// Get a list of tables (may be needed for multi-table competitions)
	// This isn't really implemented yet as all tables are produced by one statement
	$stmt=$conn->prepare("SELECT * FROM bb_stat_comp_table WHERE competition_id = ? AND domain_id = ?");
	$stmt->bindParam(1, $competition_id, PDO::PARAM_INT);
	$stmt->bindParam(2, $domain_id, PDO::PARAM_INT);
	//trigger_error($competition_id . "Fatal error" .$domain_id, E_USER_ERROR);
	$stmt->execute();
	$table_list = $stmt->fetchAll(PDO::FETCH_ASSOC);

	// This is slightly silly coding for now, as we deal with all tables in the same competition together
	foreach($table_list AS $table) {
		$scoring_system_id = $table['scoring_system_id'];
	}
	
	//If the competition doesn't have a table, don't create one
	if (count($table_list)==0)
	{
		return;
	}
	
	// Delete existing table
	$dlt = $conn->prepare("DELETE FROM bb_stat_comp_table_rank
				WHERE competition_id = ?
				AND domain_id = ?");

	$dlt->bindParam(1, $competition_id, PDO::PARAM_INT);
	$dlt->bindParam(2, $domain_id, PDO::PARAM_INT);
	$dlt->execute();
			
	// retard complicated fucntion due to mysql's lack of a rank function!!!
	if($scoring_system_id==1) { // 3pts win, 1 pt draw, tibreaker is TD diff
		$insanely_complicated_sql = $conn->prepare("
		INSERT INTO bb_stat_comp_table_rank (domain_id, competition_id, group_id, rank, team_id, played, wins, draws, losses
				, touchdown_scored, touchdown_conceded, touchdown_diff, passes, casualties, points)

		SELECT  domain_id, competition_id, initial_group_id, rank, team_id
			, played, wins, draws, losses, td_for, td_against, td_diff
			, passes, casualties_inflicted, pts

		FROM
		(
		SELECT competition_id, domain_id, team_id
			, played, wins, draws, losses, td_for, td_against, td_for-td_against AS td_diff, passes, casualties_inflicted, initial_group_id
			, (wins*3) + draws as pts
			, @curRank := if(initial_group_id<>@last_group_id,1,@curRank + 1) AS row_num
			, @tempRank := if(@last_pts=(wins*3) + draws AND @last_tdiff = td_for-td_against,@tempRank, @curRank) AS rank
			, @last_pts :=(wins*3) + draws AS last_pts
			, @last_tdiff :=td_for-td_against AS last_tdiff
			, @last_group_id := initial_group_id AS last_groupid

		FROM
		(
			SELECT 
			ct.competition_id, ct.domain_id, t.team_id
			, t.description as team_name
			, COUNT(*) AS played
			, SUM(CASE WHEN m.home_team_id = t.team_id AND m.home_touchdown_count > m.away_touchdown_count THEN 1
				WHEN m.away_team_id = t.team_id AND m.home_touchdown_count < m.away_touchdown_count THEN 1
				ELSE 0 END) as wins
			, SUM(CASE WHEN m.home_team_id = t.team_id AND m.home_touchdown_count < m.away_touchdown_count THEN 1
				WHEN m.away_team_id = t.team_id AND m.home_touchdown_count > m.away_touchdown_count THEN 1
				ELSE 0 END) as losses
			, SUM(CASE WHEN m.home_touchdown_count = m.away_touchdown_count THEN 1 ELSE 0 END) AS draws
			, SUM(CASE WHEN m.home_team_id = t.team_id THEN m.home_touchdown_count ELSE m.away_touchdown_count END) as td_for
			, SUM(CASE WHEN m.home_team_id = t.team_id THEN m.away_touchdown_count ELSE m.home_touchdown_count END) as td_against
			, SUM(ts.passes) AS passes
			, SUM(ts.inflicted_injury) AS casualties_inflicted
			, ct.initial_group_id
		from bb_competition_team ct 
		inner join bb_competition c ON ct.competition_id = c.competition_id AND c.domain_id = ct.domain_id
		inner join bb_lkp_competition_type ctype ON c.competition_type_id = ctype.competition_type_id
		inner join bb_team t ON ct.team_id = t.team_id
		inner join bb_match m ON t.team_id IN (m.home_team_id, m.away_team_id)
		inner join bb_match_competition mc ON m.match_id = mc.match_id AND mc.competition_id = ct.competition_id AND mc.domain_id = ct.domain_id
		left join bb_match_team_stats ts ON ts.match_id = m.match_id AND ts.team_id = t.team_id
		WHERE  ct.competition_id = ?
		AND ct.domain_id = ?
		AND m.match_status_id = 1
		GROUP BY t.team_id) AS unsorted
		, (SELECT @curRank := 0) r
		, (SELECT @tempRank := 0) z
		, (SELECT @last_tdiff :=0) w
		, (SELECT @last_pts :=0) x
		, (SELECT @last_group_id :=0) www
		ORDER BY initial_group_id, (wins*3) + draws DESC, td_for-td_against DESC, team_name
		) a");
	} // end of "if $scoring_system_id == 1"
	


	if($scoring_system_id==2) { // 3pts win, 1 pt draw, tie breakers decided by td diff plus 0.5*cas diff
		$insanely_complicated_sql = $conn->prepare("
		INSERT INTO bb_stat_comp_table_rank (domain_id, competition_id, group_id, rank, team_id, played, wins, draws, losses
				, touchdown_scored, touchdown_conceded, touchdown_diff, passes, casualties, points, casualties_sustained)

		SELECT  domain_id, competition_id, initial_group_id, rank, team_id
			, played, wins, draws, losses, td_for, td_against, td_diff
			, passes, casualties_inflicted, pts, casualties_sustained

		FROM
		(
		SELECT competition_id, domain_id, team_id
			, played, wins, draws, losses, td_for, td_against, td_for-td_against AS td_diff, passes
			, casualties_inflicted
			, casualties_sustained
			, initial_group_id
			, (td_for-td_against) + (0.5*(casualties_inflicted-casualties_sustained)) AS tie_breaker
			, (wins*3) + draws as pts
			, @curRank := if(initial_group_id<>@last_group_id,1,@curRank + 1) AS row_num
			, @tempRank := if(@last_pts=(wins*3) + draws AND @last_tiebreaker = td_for-td_against + (0.5*(casualties_inflicted-casualties_sustained)),@tempRank, @curRank) AS rank
			, @last_pts :=(wins*3) + draws AS last_pts
			, @last_tiebreaker :=td_for-td_against + (0.5*(casualties_inflicted-casualties_sustained)) AS last_tiebreaker
			, @last_tie_breaker := (td_for-td_against) + (0.5*(casualties_inflicted-casualties_sustained)) AS last_tie_breaker
			, @last_group_id := initial_group_id AS last_groupid

		FROM
		(
			SELECT 
			ct.competition_id, ct.domain_id, t.team_id
			, t.description as team_name
			, COUNT(*) AS played
			, SUM(CASE WHEN m.home_team_id = t.team_id AND m.home_touchdown_count > m.away_touchdown_count THEN 1
				WHEN m.away_team_id = t.team_id AND m.home_touchdown_count < m.away_touchdown_count THEN 1
				ELSE 0 END) as wins
			, SUM(CASE WHEN m.home_team_id = t.team_id AND m.home_touchdown_count < m.away_touchdown_count THEN 1
				WHEN m.away_team_id = t.team_id AND m.home_touchdown_count > m.away_touchdown_count THEN 1
				ELSE 0 END) as losses
			, SUM(CASE WHEN m.home_touchdown_count = m.away_touchdown_count THEN 1 ELSE 0 END) AS draws
			, SUM(CASE WHEN m.home_team_id = t.team_id THEN m.home_touchdown_count ELSE m.away_touchdown_count END) as td_for
			, SUM(CASE WHEN m.home_team_id = t.team_id THEN m.away_touchdown_count ELSE m.home_touchdown_count END) as td_against
			, SUM(ts.passes) AS passes
			, SUM(ts.inflicted_injury) AS casualties_inflicted
			, SUM(ots.inflicted_injury) AS casualties_sustained
			, ct.initial_group_id
		from bb_competition_team ct 
		inner join bb_competition c ON ct.competition_id = c.competition_id AND c.domain_id = ct.domain_id
		inner join bb_lkp_competition_type ctype ON c.competition_type_id = ctype.competition_type_id
		inner join bb_team t ON ct.team_id = t.team_id
		inner join bb_match m ON t.team_id IN (m.home_team_id, m.away_team_id)
		inner join bb_match_competition mc ON m.match_id = mc.match_id AND mc.competition_id = ct.competition_id AND mc.domain_id = ct.domain_id
		left join bb_match_team_stats ts ON ts.match_id = m.match_id AND ts.team_id = t.team_id
		left join bb_match_team_stats ots ON ots.match_id = m.match_id AND (		(ots.team_id = m.away_team_id AND m.home_team_id = t.team_id)
																			OR  (ots.team_id = m.home_team_id AND m.away_team_id = t.team_id))
		WHERE  ct.competition_id = ?
		AND ct.domain_id = ?
		AND m.match_status_id = 1
		GROUP BY t.team_id) AS unsorted
		, (SELECT @curRank := 0) r
		, (SELECT @tempRank := 0) z
		, (SELECT @last_tiebreaker :=0) w
		, (SELECT @last_pts :=0) x
		, (SELECT @last_group_id :=0) www
		, (SELECT @last_tie_breaker :=0) qwe
		ORDER BY initial_group_id, (wins*3) + draws DESC, (td_for-td_against) + (0.5*(casualties_inflicted-casualties_sustained)) DESC, team_name
		) a");
	}
	
	$insanely_complicated_sql->bindParam(1, $competition_id, PDO::PARAM_INT);
	$insanely_complicated_sql->bindParam(2, $domain_id, PDO::PARAM_INT);
	$insanely_complicated_sql->execute();
	
}

function update_star_player_record($conn, $upload_id) {

	// 1) update records, where the star player already has a record, and the player match stats flag shows we haven't incorporated the data yet.

	$stmt=$conn->prepare("
	UPDATE bb_stat_star_player p
		INNER JOIN bb_player_match_stats pms ON p.player_type_id = pms.player_type_id
		INNER JOIN bb_upload u ON pms.match_id = u.match_id
	SET	p.mvp = p.mvp + pms.mvp
		, p.match_played = p.match_played + pms.match_played
		, p.passes = p.passes + pms.passes
		, p.catches = p.catches + pms.catches
		, p.interceptions = p.interceptions + pms.interceptions
		, p.touchdowns = p.touchdowns + pms.touchdowns
		, p.inflicted_knockdown = p.inflicted_knockdown + pms.inflicted_knockdown
		, p.inflicted_tackles = p.inflicted_tackles + pms.inflicted_tackles
		, p.inflicted_ko = p.inflicted_ko + pms.inflicted_ko
		, p.inflicted_stun = p.inflicted_stun + pms.inflicted_stun
		, p.inflicted_injury = p.inflicted_injury + pms.inflicted_injury
		, p.inflicted_dead = p.inflicted_dead + pms.inflicted_dead
		, p.meters_run = p.meters_run + pms.meters_run
		, p.meters_pass = p.meters_pass + pms.meters_pass
		, p.sustained_interception = p.sustained_interception + pms.sustained_interception
		, p.sustained_knockdown = p.sustained_knockdown + pms.sustained_knockdown
		, p.sustained_tackles = p.sustained_tackles + pms.sustained_tackles
		, p.sustained_ko = p.sustained_ko + pms.sustained_ko
		, p.sustained_stun = p.sustained_stun + pms.sustained_stun
		, p.sustained_injury = p.sustained_injury + pms.sustained_injury
		, p.sustained_dead = p.sustained_dead + pms.sustained_dead
		, p.blocks_attempted = p.blocks_attempted + pms.blocks_attempted
		, p.dodges_made = p.dodges_made + pms.dodges_made
		, p.experience = p.experience + (5*pms.mvp) + (3*pms.touchdowns) + (pms.passes) + (2*pms.interceptions) + (2*pms.inflicted_injury)
	WHERE	pms.player_status_id = 4
	AND	pms.star_player_stats_updated = 0
	AND	p.domain_id = u.domain_id
	AND	u.upload_id = ?");

	$stmt->bindParam(1, $upload_id, PDO::PARAM_INT);
	$stmt->execute();

	// 2) insert new records, where the star player(s) in question don't have a stat record yet.

	$stmt=$conn->prepare("
	INSERT INTO bb_stat_star_player (domain_id, player_type_id, match_played, mvp, passes, catches, interceptions, touchdowns, inflicted_knockdown, inflicted_tackles, inflicted_ko
					, inflicted_stun, inflicted_injury, inflicted_dead, meters_run, meters_pass
					, sustained_interception, sustained_knockdown, sustained_tackles, sustained_ko, sustained_stun, sustained_injury, sustained_dead
					, blocks_attempted, dodges_made, experience)
	SELECT		u.domain_id
			, pms.player_type_id
			, pms.match_played
			, pms.mvp
			, pms.passes
			, pms.catches
			, pms.interceptions
			, pms.touchdowns
			, pms.inflicted_knockdown
			, pms.inflicted_tackles
			, pms.inflicted_ko
			, pms.inflicted_stun
			, pms.inflicted_injury
			, pms.inflicted_dead
			, pms.meters_run
			, pms.meters_pass
			, pms.sustained_interception
			, pms.sustained_knockdown
			, pms.sustained_tackles
			, pms.sustained_ko
			, pms.sustained_stun
			, pms.sustained_injury
			, pms.sustained_dead
			, pms.blocks_attempted
			, pms.dodges_made
			, (5*pms.mvp) + (3*pms.touchdowns) + (pms.passes) + (2*pms.interceptions) + (2*pms.inflicted_injury)
	FROM 		bb_player_match_stats pms
	INNER JOIN 	bb_upload u ON pms.match_id = u.match_id
	WHERE		pms.star_player_stats_updated = 0
	AND		pms.player_status_id = 4
	AND		u.upload_id = ?
	AND NOT EXISTS
		(	SELECT * FROM bb_stat_star_player b2
			WHERE b2.domain_id = u.domain_id
			AND b2.player_type_id = pms.player_type_id
		)");

	$stmt->bindParam(1, $upload_id, PDO::PARAM_INT);
	$stmt->execute();

	// 3) Mark the player match stats as having been updated.

	$stmt=$conn->prepare("
		UPDATE	bb_player_match_stats pms
			INNER JOIN bb_upload u ON pms.match_id = u.match_id
		SET	pms.star_player_stats_updated = 1
		WHERE	pms.star_player_stats_updated = 0
		AND	u.upload_id = ?");

	$stmt->bindParam(1, $upload_id, PDO::PARAM_INT);
	$stmt->execute();

}

function update_player_tables($domain_id, $competition_id, $conn) {
	$table_list_sql = $conn->prepare("
			SELECT table_id, chart_size, description
			FROM bb_table_player_competition
			WHERE domain_id = ? AND competition_id = ?
			ORDER BY order_no, table_id");
	$table_list_sql->bindParam(1, $domain_id, PDO::PARAM_INT);
	$table_list_sql->bindParam(2, $competition_id, PDO::PARAM_INT);
	
	$table_list_sql->execute();
	
	$table_list = $table_list_sql->fetchAll(PDO::FETCH_ASSOC);
	foreach ($table_list as $row) {
		$table_id = $row['table_id'];
		$chart_size = $row['chart_size'];
		$delete_ranks = $conn->prepare("DELETE FROM bb_stat_table_player_competition_rank
						WHERE domain_id = ? AND competition_id = ? AND table_id = ?");
		$delete_ranks->bindParam(1, $domain_id, PDO::PARAM_INT);
		$delete_ranks->bindParam(2, $competition_id, PDO::PARAM_INT);
		$delete_ranks->bindParam(3, $table_id, PDO::PARAM_INT);
		$delete_ranks->execute();
		
		// call function that creates the fully ranked table & writes it to the DB
		build_player_table_ranks($table_id, $domain_id, $competition_id, $chart_size, $conn);
	}
}

function build_player_table_ranks($table_id, $domain_id, $competition_id, $chart_size, $conn) {
	//dynamically build the string in a safe way, using SWITCH
	switch($table_id) {
		case 1:
			$field_name ='touchdowns';
			break;
		
		case 2:
			$field_name ='passes';
			break;
		
		case 3:
			$field_name ='catches';
			break;
		
		case 4:
			$field_name ='mvp';
			break;
		
		case 5:
			$field_name ='meters_run';
			break;
		
		case 6:
			$field_name ='meters_pass';
			break;
			
		case 7:
			$field_name ='inflicted_knockdown';
			break;
		
		case 8:
			$field_name ='sustained_knockdown';
			break;
		
		case 9:
			$field_name ='inflicted_injury';
			break;
		
		case 10:
			$field_name ='sustained_injury';
			break;
		
		default:
			$field_name = 'touchdowns';
			break;
	}
	$sql = "
		INSERT INTO bb_stat_table_player_competition_rank
		SELECT :table_id, :competition_id, :domain_id, rank, player_id, score, rowNum
		FROM
		( SELECT player_id, score, description, 
			@curRank := IF(@prevRank = score, @curRank, @incRank) AS rank, 
			@incRank := @incRank + 1, 
			@prevRank := score,
			@rowNum := @rowNum + 1 AS rowNum
			FROM
			(SELECT DISTINCT real_q.player_id, real_q.score, real_q.description
			FROM (SELECT  SUM($field_name) as score
					FROM bb_match_competition mc
					INNER JOIN bb_player_match_stats ps ON ps.match_id = mc.match_id
					WHERE mc.domain_id = :domain_id AND mc.competition_id = :competition_id
					AND $field_name > 0
					GROUP BY ps.player_id
					ORDER BY SUM($field_name) DESC
					LIMIT :lim) lq
			INNER JOIN (
					SELECT ps.player_id, p.description, SUM(ps.$field_name) as score
					FROM bb_match_competition mc
					INNER JOIN bb_player_match_stats ps ON ps.match_id = mc.match_id
					INNER JOIN bb_player p ON ps.player_id = p.player_id
					WHERE mc.domain_id = :domain_id AND mc.competition_id = :competition_id
					GROUP BY ps.player_id, p.description)
				real_q
				ON real_q.score = lq.score
			) a,
			( SELECT @curRank :=0, @prevRank := NULL, @incRank := 1, @rowNum := 0
			) r
			ORDER BY score DESC, description
		) b
		ORDER BY rowNum ASC";

	$stmt=$conn->prepare($sql);
	$intval_chartsize = intval($chart_size);
	$stmt->bindParam(':table_id', $table_id, PDO::PARAM_INT);
	$stmt->bindParam(':domain_id', $domain_id, PDO::PARAM_INT);
	$stmt->bindParam(':competition_id', $competition_id, PDO::PARAM_INT);
	$stmt->bindParam(':lim', $intval_chartsize, PDO::PARAM_INT);

	$stmt->execute();
}

function update_team_tables($domain_id, $competition_id, $conn) {
	$table_list_sql = $conn->prepare("
			SELECT table_id, COALESCE(chart_size,100) AS chart_size, description
			FROM bb_table_team_competition
			WHERE domain_id = ? AND competition_id = ?
			ORDER BY order_no, table_id");
	$table_list_sql->bindParam(1, $domain_id, PDO::PARAM_INT);
	$table_list_sql->bindParam(2, $competition_id, PDO::PARAM_INT);
	
	$table_list_sql->execute();
	
	$table_list = $table_list_sql->fetchAll(PDO::FETCH_ASSOC);
	foreach ($table_list as $row) {
		$table_id = $row['table_id'];
		$chart_size = $row['chart_size'];
		$delete_ranks = $conn->prepare("DELETE FROM bb_stat_table_team_competition_rank
						WHERE domain_id = ? AND competition_id = ? AND table_id = ?");
		$delete_ranks->bindParam(1, $domain_id, PDO::PARAM_INT);
		$delete_ranks->bindParam(2, $competition_id, PDO::PARAM_INT);
		$delete_ranks->bindParam(3, $table_id, PDO::PARAM_INT);
		$delete_ranks->execute();
		
		// call function that creates the fully ranked table & writes it to the DB
		build_team_table_ranks($table_id, $domain_id, $competition_id, $chart_size, $conn);
	}
}

function build_team_table_ranks($table_id, $domain_id, $competition_id, $chart_size, $conn) {
	//dynamically build the string in a safe way, using SWITCH
	switch($table_id) {
		case 1:
			$field_name ='touchdowns_scored';
			$source_table = 'bb_match';
			$sort_order = 'DESC';
			break;
		
		case 2:
			$field_name ='passes';
			$source_table = 'bb_match_team_stats';
			$sort_order = 'DESC';
			break;
		
		case 3:
			$field_name ='catches';
			$source_table = 'bb_match_team_stats';
			$sort_order = 'DESC';
			break;
		
		case 4:
			$field_name ='touchdowns_conceded';
			$source_table = 'bb_match';
			$sort_order = 'ASC';
			break;
		
		case 5:
			$field_name ='meters_run';
			$source_table = 'bb_match_team_stats';
			$sort_order = 'DESC';
			break;
		
		case 6:
			$field_name ='meters_pass';
			$source_table = 'bb_match_team_stats';
			$sort_order = 'DESC';
			break;
			
		case 7:
			$field_name ='inflicted_knockdown';
			$source_table = 'bb_match_team_stats';
			$sort_order = 'DESC';
			break;
		
		case 8:
			$field_name ='inflicted_knockdown';
			$source_table = 'bb_match_team_stats_reverse';
			$sort_order = 'DESC';
			break;
		
		case 9:
			$field_name ='inflicted_injury';
			$source_table = 'bb_match_team_stats';
			$sort_order = 'DESC';
			break;
		
		case 10:
			$field_name ='inflicted_injury';
			$source_table = 'bb_match_team_stats_reverse';
			$sort_order = 'DESC';
			break;
		
		default:
			$field_name = 'touchdowns_scored';
			$source_table = 'bb_match';
			$sort_order = 'DESC';
			break;
	}
	$sql = "
		INSERT INTO bb_stat_table_team_competition_rank
		SELECT :table_id, :competition_id, :domain_id, rank, team_id, score, rowNum
		FROM
		( SELECT team_id, score, description, 
			@curRank := IF(@prevRank = score, @curRank, @incRank) AS rank, 
			@incRank := @incRank + 1, 
			@prevRank := score,
			@rowNum := @rowNum + 1 AS rowNum
			FROM
			(SELECT DISTINCT real_q.team_id, real_q.score, real_q.description
			FROM ";
	if ($source_table=='bb_match_team_stats') {		
		$sql = $sql . "	(SELECT  SUM($field_name) as score
					FROM bb_match_competition mc
					INNER JOIN bb_match_team_stats ts ON ts.match_id = mc.match_id
					WHERE mc.domain_id = :domain_id AND mc.competition_id = :competition_id
					AND $field_name > 0
					GROUP BY ts.team_id
					ORDER BY SUM($field_name) $sort_order
					LIMIT :lim) lq
			INNER JOIN (
					SELECT ts.team_id, t.description, SUM(ts.$field_name) as score
					FROM bb_match_competition mc
					INNER JOIN bb_match_team_stats ts ON ts.match_id = mc.match_id
					INNER JOIN bb_team t ON ts.team_id = t.team_id
					WHERE mc.domain_id = :domain_id AND mc.competition_id = :competition_id
					GROUP BY ts.team_id, t.description)
				real_q
				ON real_q.score = lq.score";
		}
	elseif ($source_table=='bb_match_team_stats_reverse') {
		$sql = $sql . "	(SELECT  SUM($field_name) as score
					FROM bb_match_competition mc
					INNER JOIN bb_match_team_stats ts ON ts.match_id = mc.match_id
					INNER JOIN (SELECT match_id, home_team_id AS join_team_id, away_team_id AS other_team_id
					FROM bb_match
					UNION ALL
					SELECT match_id, away_team_id AS join_team_id, home_team_id AS other_team_id
					FROM bb_match
						) mirror ON ts.match_id = mirror.match_id AND ts.team_id = join_team_id
					WHERE mc.domain_id = :domain_id AND mc.competition_id = :competition_id
					AND $field_name > 0
					GROUP BY mirror.other_team_id
					ORDER BY SUM($field_name) $sort_order
					LIMIT :lim) lq
			INNER JOIN (
					SELECT mirror.other_team_id AS team_id, t.description, SUM(ts.$field_name) as score
					FROM bb_match_competition mc
					INNER JOIN bb_match_team_stats ts ON ts.match_id = mc.match_id
					INNER JOIN (SELECT match_id, home_team_id AS join_team_id, away_team_id AS other_team_id
					FROM bb_match
					UNION ALL
					SELECT match_id, away_team_id AS join_team_id, home_team_id AS other_team_id
					FROM bb_match
						) mirror ON ts.match_id = mirror.match_id AND ts.team_id = join_team_id
					INNER JOIN bb_team t ON mirror.other_team_id = t.team_id
					WHERE mc.domain_id = :domain_id AND mc.competition_id = :competition_id
					GROUP BY t.team_id, t.description)
				real_q
				ON real_q.score = lq.score";
		}
	elseif (($source_table=='bb_match') && ($field_name == 'touchdowns_scored')) {
		$sql = $sql . " (SELECT  SUM(score) AS score
					FROM
					(
						SELECT m.home_team_id AS team_id, home_touchdown_count as score
						FROM bb_match_competition mc
						INNER JOIN bb_match m ON mc.match_id = m.match_id
						WHERE mc.domain_id = :domain_id AND mc.competition_id = :competition_id
						UNION ALL
						SELECT m.away_team_id AS team_id, away_touchdown_count as score
						FROM bb_match_competition mc
						INNER JOIN bb_match m ON mc.match_id = m.match_id
						WHERE mc.domain_id = :domain_id AND mc.competition_id = :competition_id
					) bob
					GROUP BY bob.team_id
					ORDER BY SUM(score) $sort_order
					LIMIT :lim) lq
			INNER JOIN (
					SELECT t.team_id, t.description, SUM(score) AS score
					FROM
					(
						SELECT m.home_team_id AS team_id, home_touchdown_count as score
						FROM bb_match_competition mc
						INNER JOIN bb_match m ON mc.match_id = m.match_id
						WHERE mc.domain_id = :domain_id AND mc.competition_id = :competition_id
						UNION ALL
						SELECT m.away_team_id AS team_id, away_touchdown_count as score
						FROM bb_match_competition mc
						INNER JOIN bb_match m ON mc.match_id = m.match_id
						WHERE mc.domain_id = :domain_id AND mc.competition_id = :competition_id
					) bob
					INNER JOIN bb_team t ON bob.team_id = t.team_id
					GROUP BY t.team_id
				) real_q
				ON real_q.score = lq.score";
		}
	elseif (($source_table=='bb_match') && ($field_name == 'touchdowns_conceded')) {
		$sql = $sql . " (SELECT  SUM(score) AS score
					FROM
					(
						SELECT m.home_team_id AS team_id, away_touchdown_count as score
						FROM bb_match_competition mc
						INNER JOIN bb_match m ON mc.match_id = m.match_id
						WHERE mc.domain_id = :domain_id AND mc.competition_id = :competition_id
						UNION ALL
						SELECT m.away_team_id AS team_id, home_touchdown_count as score
						FROM bb_match_competition mc
						INNER JOIN bb_match m ON mc.match_id = m.match_id
						WHERE mc.domain_id = :domain_id AND mc.competition_id = :competition_id
					) bob
					GROUP BY bob.team_id
					ORDER BY SUM(score) $sort_order
					LIMIT :lim) lq
			INNER JOIN (
					SELECT t.team_id, t.description, SUM(score) AS score
					FROM
					(
						SELECT m.home_team_id AS team_id, away_touchdown_count as score
						FROM bb_match_competition mc
						INNER JOIN bb_match m ON mc.match_id = m.match_id
						WHERE mc.domain_id = :domain_id AND mc.competition_id = :competition_id
						UNION ALL
						SELECT m.away_team_id AS team_id, home_touchdown_count as score
						FROM bb_match_competition mc
						INNER JOIN bb_match m ON mc.match_id = m.match_id
						WHERE mc.domain_id = :domain_id AND mc.competition_id = :competition_id
					) bob
					INNER JOIN bb_team t ON bob.team_id = t.team_id
					GROUP BY t.team_id
				) real_q
				ON real_q.score = lq.score";
		}
	else {
		$sql = $sql . ""; // screw it. Let it fail.
	}
	$sql = $sql . ") a,
			( SELECT @curRank :=0, @prevRank := NULL, @incRank := 1, @rowNum := 0
			) r
			ORDER BY score $sort_order, description
		) b
		ORDER BY rowNum ASC";

	$stmt=$conn->prepare($sql);
	$intval_chartsize = intval($chart_size);
	$stmt->bindParam(':table_id', $table_id, PDO::PARAM_INT);
	$stmt->bindParam(':domain_id', $domain_id, PDO::PARAM_INT);
	$stmt->bindParam(':competition_id', $competition_id, PDO::PARAM_INT);
	$stmt->bindParam(':lim', $intval_chartsize, PDO::PARAM_INT);

	$stmt->execute();
}

function assign_player_awards($conn, $domain_id, $competition_id, $override = 0) {
	if ($override==1) {
		$stmt = $conn->prepare("DELETE  pa 
									FROM bb_player_award pa
									WHERE pa.competition_id = ? AND pa.domain_id = ?");
		$stmt->bindParam(1, $competition_id, PDO::PARAM_INT);
		$stmt->bindParam(2, $domain_id, PDO::PARAM_INT);
		$stmt->execute();
	}
	else {
		$stmt = $conn->prepare("SELECT COUNT(*) bb_player_award pa
									WHERE pa.competition_id = ? AND pa.domain_id = ?");
		$stmt->bindParam(1, $competition_id, PDO::PARAM_INT);
		$stmt->bindParam(2, $domain_id, PDO::PARAM_INT);
		$stmt->execute();
		$award_rows = (int) $stmt->fetchColumn();
		if ($award_rows>0) {
			echo 'Awards already exist. Aborting.';
			return;
		}
	}
	
	$stmt = $conn->prepare("INSERT INTO bb_player_award (award_id, player_id, awarded_datetime, competition_id, shared, domain_id)
			SELECT a.award_id, st.player_id, NOW(), st.competition_id, 0, ?
			FROM bb_lkp_award a
			INNER JOIN bb_lkp_award_type t ON a.award_type_id= t.award_type_id
			INNER JOIN bb_lkp_award_level al ON a.award_level_id = al.award_level_id
			INNER JOIN bb_stat_table_player_competition_rank st ON t.table_id = st.table_id AND st.competition_id = ?
					AND st.rank = al.rank_no
					AND st.domain_id = ?
			WHERE t.award_category_id = 1");
	$stmt->bindParam(1, $domain_id, PDO::PARAM_INT);
	$stmt->bindParam(2, $competition_id, PDO::PARAM_INT);
	$stmt->bindParam(3, $domain_id, PDO::PARAM_INT);
	$stmt->execute();
	
	// This will update to shared titles, where appropriate
	
	$stmt = $conn->prepare("UPDATE bb_player_award a
			INNER JOIN
			(SELECT award_id FROM bb_player_award WHERE competition_id = ? AND domain_id = ? GROUP BY award_id HAVING COUNT(*) > 1) b 
			ON b.award_id = a.award_id
			SET a.shared = 1
			WHERE a.competition_id = ?
			AND a.domain_id = ?");
	
	$stmt->bindParam(1, $competition_id, PDO::PARAM_INT);
	$stmt->bindParam(2, $domain_id, PDO::PARAM_INT);
	$stmt->bindParam(3, $competition_id, PDO::PARAM_INT);
	$stmt->bindParam(4, $domain_id, PDO::PARAM_INT);
	$stmt->execute();
	
	// -- Also need to remove awards if > 5 people earn them. No examples in season 7 :(
	$stmt = $conn->prepare("
	DELETE pa
	FROM bb_player_award pa
	INNER JOIN bb_lkp_award la ON pa.award_id = la.award_id
	INNER JOIN (SELECT la2.award_type_id
					FROM bb_lkp_award la2
					INNER JOIN bb_player_award pa2 ON la2.award_id - pa2.award_id
					WHERE pa2.competition_id = ?
					AND pa2.domain_id = ?
					GROUP BY la2.award_type_id
					HAVING COUNT(*) > 5) x ON la.award_type_id = x.award_type_id
	WHERE la.award_level_id = 30
	AND pa.competition_id = ?
	AND pa.domain_id = ?");
	$stmt->bindParam(1, $competition_id, PDO::PARAM_INT);
	$stmt->bindParam(2, $domain_id, PDO::PARAM_INT);
	$stmt->bindParam(3, $competition_id, PDO::PARAM_INT);
	$stmt->bindParam(4, $domain_id, PDO::PARAM_INT);
	$stmt->execute();

	// Now remove silver awards too, if > 5 people still have it
	$stmt = $conn->prepare("
	DELETE pa
	FROM bb_player_award pa
	INNER JOIN bb_lkp_award la ON pa.award_id = la.award_id
	INNER JOIN (SELECT la2.award_type_id
					FROM bb_lkp_award la2
					INNER JOIN bb_player_award pa2 ON la2.award_id - pa2.award_id
					WHERE pa2.competition_id = ?
					AND pa2.domain_id = ?
					GROUP BY la2.award_type_id
					HAVING COUNT(*) > 5) x ON la.award_type_id = x.award_type_id
	WHERE la.award_level_id = 20
	AND pa.competition_id = ?
	AND pa.domain_id = ?");
	$stmt->bindParam(1, $competition_id, PDO::PARAM_INT);
	$stmt->bindParam(2, $domain_id, PDO::PARAM_INT);
	$stmt->bindParam(3, $competition_id, PDO::PARAM_INT);
	$stmt->bindParam(4, $domain_id, PDO::PARAM_INT);
	$stmt->execute();
	
}

?>