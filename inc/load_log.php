<?php 

function bb1_load_log_to_staging($conn, $upload_id, $domain_id, $delete_existing = 0) {

	// if parameter selected, delete staging table records for this upload. Useful for debugging.
	if ($delete_existing==1) {
		$stmt=$conn->prepare("DELETE FROM staging_eventlog WHERE upload_id = ?");
		$stmt->bindParam(1, $upload_id, PDO::PARAM_INT);
		//$stmt->bindParam(2, $domain_id, PDO::PARAM_INT);
		$stmt->execute();
	}
	else { // do records already exist for this upload?
		$stmt=$conn->prepare("SELECT COUNT(*) FROM staging_eventlog WHERE upload_id = ?");
		$stmt->bindParam(1, $upload_id, PDO::PARAM_INT);
		//$stmt->bindParam(2, $domain_id, PDO::PARAM_INT);
		$stmt->execute();
		if($stmt->fetchColumn()) {
			return "Upload failed (22).";
		}
	}

	// find the filename
	$stmt=$conn->prepare("SELECT filename FROM bb_upload WHERE upload_id = ? AND domain_id = ?");
	$stmt->bindParam(1, $upload_id, PDO::PARAM_INT);
	$stmt->bindParam(2, $domain_id, PDO::PARAM_INT);
	$stmt->execute();
	if($stmt->rowCount()==0)
		{ echo "splat"; die("Invalid input."); }
	$file_suffix = $stmt->fetchColumn(0);
	$filenames[0] = 'log_' . $file_suffix;
	$filenames[1] = 'log2_' . $file_suffix;
	$filenames[2] = 'log3_' . $file_suffix;

	// setup the import SQL statement

	$sql = 'INSERT INTO staging_eventlog (upload_id, line_no, raw_text) VALUES ';
	$insertQuery = array();
	$insertData = array();
	$line_no = 0;

	foreach($filenames as $filename) {
	$path = 'uploads/' . $filename;
	if (file_exists($path)) {

		$lines=array();
		$line_count = 0;
		$fp=fopen($path, 'r');
		while (!feof($fp))
		{
			$line=fgets($fp);
			//process line however you like
			$line=trim($line);
			//echo $line;
			//echo '<br/>';

			//add to array
			//$lines[]=$line;

			// construct SQL statement
			$line_no++;
    		 	$insertQuery[] = '(?, ?, ?)';
   		 	$insertData[] = $upload_id;
   		 	$insertData[] = $line_no;
			$insertData[] = $line;

			// row counting
			$line_count++;

		}
		fclose($fp);
		echo $line_count;
		echo " lines in file<br/>";

	}
	// else { echo "Cannot find file " . $filename; }
	}
	
	// Do the SQL statement
	if (!empty($insertQuery)) {
		$sql .= implode(', ', $insertQuery);
		$stmt = $conn->prepare($sql);
		$stmt->execute($insertData);
	}


}

function bb1_transform_log($conn, $upload_id, $domain_id, $delete_existing = 0, $debug_mode = 0) {

	// if parameter selected, delete staging table records for this upload. Useful for debugging.
	if ($delete_existing==1) {
		$stmt=$conn->prepare("DELETE FROM bb_eventlog WHERE upload_id = ?");
		$stmt->bindParam(1, $upload_id, PDO::PARAM_INT);
		//$stmt->bindParam(2, $domain_id, PDO::PARAM_INT);
		$stmt->execute();
	}
	else { // do records already exist for this upload?
		$stmt=$conn->prepare("SELECT COUNT(*) FROM bb_eventlog WHERE upload_id = ?");
		$stmt->bindParam(1, $upload_id, PDO::PARAM_INT);
		//$stmt->bindParam(2, $domain_id, PDO::PARAM_INT);
		$stmt->execute();
		if($stmt->fetchColumn()) {
			return "Upload failed (22).";
		}
	}
	
	$stmt=$conn->prepare("SELECT match_id FROM bb_upload WHERE upload_id = ?");
	$stmt->bindParam(1, $upload_id, PDO::PARAM_INT);
	$stmt->execute();
	$match_id = $stmt->fetchColumn(0);

	$stmt=$conn->prepare("SELECT * FROM staging_eventlog WHERE upload_id = ? ORDER BY line_no");
	$stmt->bindParam(1, $upload_id, PDO::PARAM_INT);
	$stmt->execute();
	$staging_data = $stmt->fetchAll(PDO::FETCH_ASSOC);
	$roll_type_table = get_roll_type_table($conn);
	$block_dice_table = get_block_dice_table($conn);
	$outcome_table = get_outcome_table($conn);

	$new_line_no = 0;
	$array_line_id = 0;
	// Was the previous roll a block roll?
	$block_roll = FALSE;
	$player_data = array();
	$final_array = array();
	$weather_id = 0; // see bb_lkp_weather
	$event_id = 0;
	$skill_reroll_available = 0;
	$reroll_type_id = 0;

	// Some boolean variables that cover chunks of code. Once the chunk is over, set the variable to true... quicker to check these than do a preg_match.
	
	$teams_linked = FALSE;
	$players_loaded = FALSE;
	$ready_to_write = FALSE;
	$end_of_turn = FALSE;

	foreach ($staging_data as $line) {
		//echo $line['raw_text'];
		//echo "<br/>";
		if ($reroll_type_id == 9) { $reroll_type_id = 0;} // if we didnt use the skill reroll, reset the reroll variable

		if ($players_loaded==FALSE) {
			if (preg_match("^(.*) Loaded:([0-9]*) (.*)\.kfm(.*)$^", $line['raw_text'], $preg_matches) == 1) {
				// load the player's data into an array. Key is squad number
				load_player_data($conn, $player_data, $preg_matches, $match_id);
				if ($debug_mode==1) {
					print_r($player_data);
					echo "... that was player_data<br/>";
				}
			}

			if (1==0) { // change this to be some check that all players are loaded
				$players_loaded = TRUE;
			}
		} // end of "if players_loaded == false"

		// If the line just has what block dice were rolled.....
		if ($block_roll) {
			$new_line_no++;
			$block_roll = FALSE; // ie, the next line won't be a block roll
			$line_text = $line['raw_text'];
			$block_perm_id = get_block_roll_permutation($line_text, $conn);
			if ($debug_mode == 1) { echo $line_text . '... block permutation ID ' . $block_perm_id . '<br/>'; }
		}


		
		if(preg_match("^(.*)GameLog\(-?[0-9]+\):(.*)$^", $line['raw_text'], $match) == 1)
		{
			$new_line_no++;
			$line_text = trim($match[2]);
			$modifier_val = 0;
			
	

			// one of the first lines gives the team names and abbreviations. We want to link these up to the team ID's, which is what we will store.
			if (!$teams_linked) {
				if (preg_match("^(.*)\((.*)\) vs (.*)\((.*)\)$^", $line_text, $team_match) == 1) {
					$team_lookup = lookup_team_ids($conn, $team_match);
					$teams_linked = TRUE;
					if ($debug_mode==1) {
						echo "Team ID lookup completed as follows: ";
						echo print_r($team_lookup);
						echo "<br/>";
					}
					// Now move the player data keys to be the intials. It's more intuitive later on.
					//foreach ($team_lookup AS $key=>$val) {
					//	$player_data[$key] = $player_data[$val];
					//	unset($player_data[$val]);
					//}
				}
			}			

			// In this section, get generic information, if it is available. Team ID, Player ID, the number rolled on the die/dice.

			$team_id = $team_lookup[substr($line_text, 0, 3)];
			
			if (preg_match("^....#(..).*$^", $line_text, $squad_no_matches) == 1) {
				$squad_number = (int) $squad_no_matches[1];
			}
			elseif (preg_match("^....\((..)\).*$^", $line_text, $squad_no_matches) == 1) {
				$squad_number = (int) $squad_no_matches[1];
			}
			else	{ $squad_number = 0;}

			$player_id = $player_data[$team_id][$squad_number]['player_id'];

			// eg can't use dodge twice in 1 turn
			if ($player_id<> $skill_reroll_not_usable_by_player_id) {
				unset($skill_reroll_not_usable_by_player_id);
			}
			
			if (preg_match("/: ([1-9]) \+? ?([1-9])? ?(.)/", $line_text, $dice_roll_match) == 1) {
				if ((array_key_exists(2, $dice_roll_match)) && ($dice_roll_match[3] <> '{')) { // it's a 2D6
					$dice_roll = (int) $dice_roll_match[1] + (int) $dice_roll_match[2];
				}
				else {
					$dice_roll = (int) $dice_roll_match[1];
				}
			}
			elseif (preg_match("/: ([1-9])\./", $line_text, $dice_roll_match) == 1) { // eg kickoff table
				$dice_roll = (int) $dice_roll_match[1];
			}
			else { $dice_roll = 0; }

			// Now check for modifiers. They can tell us the (true) type of roll, or otherwise give us useful info
			if (preg_match_all("/(\+ *-?[0-9] ?{[A-Za-z -]+} *)/", $line_text, $modifier_match)) {
				// we have found modifiers!
				$modifier_text = '';
				if ($debug_mode==1) { echo '<ul>'; }
				foreach ($modifier_match[0] AS $row) { 	// this element is an array containing all matches
					$valid_match = TRUE;
					preg_match("/{(.*)}/", $row, $mod_type_match);
					preg_match("/-?[0-9]/", $row, $mod_val_match);
					$mod_name = $mod_type_match[1];
					$mod_val = (int) $mod_val_match[0];
					if ($debug_mode==1) { echo '<li>' . $mod_name . '(' . $mod_val . ')</li>'; }
					// Some modifiers can change the roll type, eg chainsaw, interception
					// we don't want to include these as modifiers but do want to set the roll type
					$possible_roll_type =  array_search_key_val($roll_type_table, 'bb1_desc', $mod_name);
					if (is_array($possible_roll_type))
						{ $valid_match = FALSE; 
							$roll_type = $possible_roll_type; 
							if ($debug_mode==1) { echo "roll type set due to modifier found"; }
						}
					if (($mod_val==0) && ($mod_name <> 'SP'))
					{
						$valid_match = FALSE; // we don't care that, for example, there are 0 tackle zones. Exception - short pass
					}
					if ($valid_match) {
						$modifier_text .= $row;
						$modifier_val = $modifier_val + $mod_val;
					}
				}
				if ($debug_mode==1) { echo '</ul>'; }
				
				// if nothing added, set it back to the default
				if ($modifier_text == '') { $modifier_text = 0;}
				if ($debug_mode==1) { echo "modifier text is " . $modifier_text . "<br/>"; }
				
			}

			// end of generic information ... now onto specifics
			
			// Look for a few turnover types
			if (substr($line_text, 0, 9)=='Half Time') {
				$roll_type_id = 46;
				$dice_roll = 3; // see bb_lkp_turnover_type
				$ready_to_write = TRUE;
			}
			
			if (substr($line_text, 4, 41)=='suffer a TURNOVER! : Time limit exceeded!') {
				$roll_type_id = 46;
				$dice_roll = 1; // see bb_lkp_turnover_type
				$ready_to_write = TRUE;
			}

			if (substr($line_text, 4, 34)=='suffer a TURNOVER! : Knocked Down!') {
				$roll_type_id = 46;
				$dice_roll = 7; // see bb_lkp_turnover_type
				$ready_to_write = TRUE;
			}
			
			if (substr($line_text, 3, 14)==' Touchdown by ') {
				$roll_type_id = 46;
				$dice_roll = 2; // see bb_lkp_turnover_type
				$ready_to_write = TRUE;
			}
			
			if (substr($line_text, 0, 13)=='Weather Table')	{ 
					$roll_lookup_id = get_weather_id($line_text, $conn); 
					// code to log weather goes here?
					$roll_type_id = 28;
					//$to_push = array('NULL', 'NULL', 'NULL', 'NULL', 'NULL', $roll_type_id, 'NULL', 'NULL', 0, $dice_roll, $weather_id, 'NULL', $line_text);
					if ($debug_mode == 1) { echo "Weather = rolled a " . $dice_roll . ", which gives weather ID " . $weather_id . "<br/>"; }
					$ready_to_write = TRUE;
				}
			elseif (substr($line_text, -14)=='Block  Result:') {
					// if this line indicates a block has occurred, then capture the next line as it will give the results of the block die.
					if ($debug_mode==1) { echo "BLOCK!<br/>"; }
					// what if block immediately follows another block? We need to write first block now.
					if ($mid_block) {
						$write_this_first_blocking_player_id = $blocking_player_id;
						$write_this_first_blocking_team_id = $blocking_team_id;
						$write_this_first_block_roll_type_id = $block_roll_type_id;
						$write_the_block = TRUE;
						// say something about the block outcome here. No armour roll has happened, so assuming team is the same, it was a push...??
						if ($blocking_team_id == $team_id)  {$block_outcome_id = 4; }
					}
					$block_roll = TRUE;
					$mid_block = TRUE;
					$blocking_player_id = $player_id;
					$blocking_team_id = $team_id;
					if ($skill_id_used  == 36)  // Frenzy
						{	$block_roll_type_id = 23;}
					elseif ($skill_id_used  == 70)  // Multiple Block
						{	$block_roll_type_id = 24;}
					elseif ($skill_id_used  == 87)  // Blitz
						{	$block_roll_type_id = 22; }
					//elseif ($skill_id_used==70)  // Multiple Block DOESN'T WORK as "skill ID actually looks up on the roll id, and there is no multiple_block_roll
					//	{ 	$roll_type_id = 24; }
					else
						{	$block_roll_type_id = 21; }
				}
			
			elseif (($mid_block) && (preg_match('/.* chooses : (.*)/',$line_text, $chosen_block_die))) {		// Are we choosing which block dice to use?
					$block_row = array_search_key_val($block_dice_table, 'bb1_desc', $chosen_block_die[1]);
					$block_die_chosen = $block_row['block_dice_id']; 
					if ($block_die_chosen==3) {$block_outcome_id = 4;} // pushed die is always a pushed outcome, except for crowd surfing and strip ball
			}
		
			elseif (preg_match("/use a re-roll for .*\(Left : ([0-9])\/([0-9])\)/",$line_text,$reroll_match) == 1) {
				$reroll[$team_id]['remaining'] = $reroll_match[1];
				$reroll[$team_id]['total'] = $reroll_match[2];
				if ($debug_mode==1) { echo "The team has " . $reroll[$team_id]['remaining'] . " re-rolls left of " . $reroll[$team_id]['total'] . ".<br/>"; }
				if ($loner_failed) { // indicates we have just failed a loner roll. Change that & don't follow usual team reroll rules from here.
					$loner_failed = FALSE;
				}
				else {
					$team_reroll_chosen = TRUE;
				}
				if ($mid_block) {$write_the_block = TRUE; $mid_block = FALSE; } // ie if a block is done but re-rolled, write it straight away
			}
		
			elseif (preg_match("/uses (\w+( \w+)?)/", $line_text, $skill_match) == 1) { // Only interesting bit on line says "Player X uses skill Y" Assumes skill Y is 1 or 2 words
					$skill_id_used = find_skill_id($skill_match[1], $conn);
					if ($debug_mode==1) { echo "Skill " . $skill_id_used . " has been used (" . $skill_match[1] . ").</br>"; }
					if (($mid_block) && ($team_id <> $blocking_team_id)) { // eg fend, dodge
						$blocked_player_id = $player_id;
						$blocked_skill_id = $skill_id_used;
						if ($skill_id_used==7) { $block_outcome_id = 4; } // defender dodged means that the outcome is pushed
						
						if (($skill_id_used==30) && ($block_die_chosen==2))  { $block_outcome_id = 8; } // both down chosen, BLOCK skill used
						if (($skill_id_used==68) && ($block_die_chosen==2))  { $block_outcome_id = 10; } // both down chosen, WRESTLE skill used
						
						elseif (($skill_id_used == 35) && (($block_die_chosen==4) || ($block_die_chosen==5))) // Fend with defender down or defender stumbles
								{ $block_outcome_id = 21; }
						elseif (($skill_id_used == 35) && (($block_die_chosen==4) || ($block_die_chosen==5))) // Fend with pushed
								{ $block_outcome_id = 22; }
						//$write_the_block = TRUE; Don't do this otherwise the armour roll gets screwed up. 
						//$mid_block = FALSE; Don't do this otherwise the armour roll gets screwed up. If there is no armour roll, this will get set to false anyway
					}
					elseif (($mid_block) && ($team_id == $blocking_team_id)) { // eg block, wrestle, multiple block??
						if (($skill_id_used==30) && ($block_die_chosen==2))  { $block_outcome_id = 3; } // both down chosen, BLOCK skill used
						if (($skill_id_used==68) && ($block_die_chosen==2))  { $block_outcome_id = 2; } // both down chosen, WRESTLE skill used
						$write_the_block = TRUE;
						$mid_block = FALSE;
					}
					elseif ($skill_id_used==$prev_roll_type['reroll_skill_id']) { // reroll skills, eg dodge
						$reroll_type_id = 1;
					}
					
					
			}
			// this one searches for up to 3 words prior to a space-colon
			// relies on no skill being more than 3 words, and on there being some words at the start for 1 or  2 word skills
			// One regex site doesn't like this, but another one does. It's to get out the modifiers.
			// .+ ([A-Za-z0-9#]+) ([A-Za-z0-9#]+) ([A-Za-z\-]+)(?: \{AG\})?  ?\(([0-9])\+?\) : .*(\+ -[1-9] {TZ})?.* -> (.*)
			//elseif (preg_match("/.+ ([A-Za-z0-9#]+) ([A-Za-z0-9#]+) +([A-Za-z\-]+)(?: \{AG\})? +?\(([0-9])\+?\) :.+ -> (.*)/", $line_text, $roll_type_match)==1) {
			elseif (preg_match("/.+ ([A-Za-z0-9#\-]+) ([A-Za-z0-9#\-]+) +([A-Za-z\-]+)(?: \{AG\})? +?(?:\(([0-9]+)\+?\))? ?:.+ -> (.*)/", $line_text, $roll_type_match)==1) {
			if ($debug_mode==1) { echo "I think I found a roll type!! " . $roll_type_match[1] . $roll_type_match[2]. $roll_type_match[3]. "<br/>"; }
				$outcome_text = trim($roll_type_match[5]);
				if (!is_array($roll_type)) { // we could've set it whilst getting modifiers from the text
					$roll_type = array_search_key_val($roll_type_table, 'bb1_desc', $roll_type_match[3]);
				}
				if($roll_type===FALSE) {
					$roll_type = array_search_key_val($roll_type_table, 'bb1_desc', $roll_type_match[2] . ' ' . $roll_type_match[3]);
				}
				if($roll_type===FALSE) {
					$roll_type = array_search_key_val($roll_type_table, 'bb1_desc', $roll_type_match[1] . ' ' . $roll_type_match[2] . ' ' . $roll_type_match[3]);
				}

				// Get the outcome ID
				$outcome_id = $outcome_table[$outcome_text];
				
				if (($roll_type_match[2] == 'Armour') && ($roll_type_match[3] == 'Value')) {
					// we need to work out what sort of an armour roll it is, ie the cause of it, so we can choose the correct roll_type_id.
					$av = $player_data[$team_id][$squad_number]['av'];
					if ($debug_mode==1) { 
						echo "av roll</br>";
						echo "squad number hit = " . $squad_number . "<br/>";
						echo "av is = " . $av . "<br/>";
					}
					if ($mid_block==TRUE) { // this is an armour roll immediately following a block
						if ($debug_mode==1) { echo "post block<br/>"; }
						$mid_block = FALSE;	// the block is complete - we have all the info we need
						$write_the_block = TRUE;// therefore we should write the block dice to the final array to be written to the DB
						$ready_to_write = TRUE; // also write the av roll
						$roll_type_id = 29; // armour roll from a block
						
						$roll_aim = $av + 1; //ie, you have to roll more than AV to break it.
						$blocked_player_id = $player_id;
						
						if ($block_outcome_id>0) // the outcome has already been set, eg by use of a defensive skill
							{ ; }
						elseif (($block_die_chosen==2) && ($skill_id_used == 30)) 
								{ $block_outcome_id = 3; } // an armour roll after both down and block used means defender down on the spot
						elseif (($block_die_chosen==5) && ($skill_id_used == 16)) // defender down, stand firm = down on the spot
								{ $block_outcome_id = 3; }
						elseif ($block_die_chosen==5)  // other defender down rolls
								{ $block_outcome_id = 5; }
						elseif ($block_die_chosen==4)  // defender stumbles - as this is an armour roll the target player must be down
								{ $block_outcome_id = 5; }
						elseif ($block_die_chosen==1)  // red skull can only end one way...
								{ $block_outcome_id = 8; }
						
						$mighty_blow = 0;
						// squad_number isn't going to work here.... as it will return the hit player, not the one doing the hitting
						if (array_key_exists(13, $player_data[$team_id][$squad_number])) { $mighty_blow = 1; }
					}
					else { // it's another sort of armour roll... need to add code here to differentiate. For now, just write it.
						if ($debug_mode==1) { echo "non-block armour roll detected<br/>"; }
						$roll_type_id = 44; // generic non-block av roll
						$ready_to_write = TRUE;
						$roll_aim = $av + 1 + $modifier_val;
					}
				}
				elseif($roll_type===FALSE) {
					if ($debug_mode==1) { echo 'Unrecognized roll type<br/>'; }
				}
				else { // we have the roll_type as an array
					// Some skill has been rolled. If we were in the middle of a block, we aren't any more
					if ($mid_block) {
						$mid_block = FALSE;
						$write_the_block = TRUE;
						
					}
					
					if ($reroll_type_id==0) { // if this is not a reroll, test to see if we have a skill reroll available
											// limitation... we don't know if there is a reroll-canclling skill around, eg Tackle
						if (array_key_exists($roll_type['reroll_skill_id'],$player_data[$team_id][$squad_number])) {
							if (!($skill_reroll_not_usable_by_player_id==$player_id)) {
								$skill_reroll_available = TRUE;	
								if ($debug_mode==1) {
									echo "looks like player has a reroll skill";
									echo $roll_type['reroll_skill_id'];
									echo '<br/>';
									print_r($player_data[$team_id][$squad_number]);
								}
							}
						}
					}
					
					$roll_type_id = $roll_type['roll_type_id'];
					if ($roll_type['roll_aim_id']==3) {  // fixed 2+
						$roll_aim = 2;
						if (($weather_id==5) && ($roll_type_id==9)) { //snowy GFI
							// 'Snowy GFI';
							$roll_aim = 3;
						}
						
					}
					elseif ($roll_type['roll_aim_id']==4) {  // fixed 4+
						$roll_aim = 4 - $modifier_val;
						if (($roll_type_id==18) && ($dice_roll<$roll_aim)) { // Loner
							$loner_failed = TRUE;
							$reroll_type_id = 10; // a failed loner roll
							$final_array[$array_line_id-1]['reroll_type_id'] = 7; // set previous roll to say it was loner-failed
						}
						elseif($roll_type_id==18) { // Loner passes
							$loner_passes = TRUE;
						}
					}
					elseif (($roll_type['roll_aim_id']==1) || ($roll_type['roll_type_id']==13)) {  // agility, or pass (special type, but agility-like
						$ag = (int) $player_data[$team_id][$squad_number]['ag'];
						if ((array_key_exists(22, $player_data[$team_id][$squad_number])) && ($roll_type_id==7)) // dodge with break tackle
							{	$ag = (int) $player_data[$team_id][$squad_number]['st']; } // use strength to dodge!
						$roll_aim = 7 - $ag - (int) $roll_type['roll_modifier'];
						
						if (($dice_roll <> 1) && ($dice_roll <> 6)) {
							$roll_target_exact_flag = TRUE; // we know our calc will be exact
							$roll_aim = $roll_aim - $modifier_val;
						}
						else {
							$roll_target_exact_flag = FALSE; // except, this can be true, if we know all modifiers. eg we cant know TZ's
							// apply some skills!!
							if ((array_key_exists(16, $player_data[$team_id][$squad_number])) && ($roll_type_id==7))
								{	$roll_aim--; } // Two Heads - Dodge
							if ((array_key_exists(21, $player_data[$team_id][$squad_number])) && ($roll_type_id==13))
								{	$roll_aim--; } // Accurate - Pass
						}	
						if ($roll_aim > 6) { $roll_aim = 6; } // a 6 always succeeds
						if ($roll_aim < 2) { $roll_aim = 2; } // a 2 always fails
					}
					elseif ((int) $roll_type['roll_aim_id']==2) {  // armour value
						$av = $player_data[$team_id][$squad_number]['av'];
						$roll_aim = $av + 1; //ie, you have to roll more than AV to break it.
					}
					
					// not sure if this is the right point for it, but let's do it
					$ready_to_write = TRUE;
					
				}

				// if we are onto a new skill, and we haven't already checked mid_block, then the block is completed and we can write it.
				if ($mid_block) { $mid_block = FALSE; $write_the_block = TRUE; 
							$event_id++; // not sure about this? 
						 }

			}

			// bespoke bug checking for specific lines
			if (($debug_mode==1) && (($line['line_no']==4496) || ($line['line_no']==1942))) {
				
				echo "line . ".$line['line_no']."! skill id " . $skill_id_used . ' roll type ' . $roll_type_id . ' roll aim ' . $roll_aim .  ' mid block ' . $mid_block;
				if (preg_match("/.+ ([A-Za-z0-9#]+) ([A-Za-z0-9#]+) ([A-Za-z\-]+)(?: \{AG\})?  ?\(([0-9])\+?\) :.+ -> (.*)/", $line_text, $roll_type_match)==1)
					{ echo "a roll type has been found"; }
				echo '<br/>';
			}

			if ($debug_mode==1) {
				echo $new_line_no . "(" . $line['line_no'] .")- " . trim($line_text) . " (TEAM ID = " . $team_id . ", player ID = " . $player_id . ", roll = " . $dice_roll . ", roll_aim = " . $roll_aim  . " roll_lkp = " . $roll_lookup_id . ", roll_type " . $roll_type_id . ")</br>". PHP_EOL;
			}
			

		}

		if ($line['raw_text'] == "|  | Init CStateMatchEnd") {
			// end of match detected
			if ($debug_mode==1) { echo "END OF MATCH DETECTED<br/>"; }
			$roll_type_id = 46;
			$dice_roll = 3; // see bb_lkp_turnover_type
			$ready_to_write = TRUE;
		}
	
		// we only know it is time to write the block once the next action has happened.
		// so we effectively need to keep track of 2 sets of variables (??)
		if ($write_the_block) {
			if ($debug_mode==1) { echo "WRITING THE BLOCK!!!!!!!!!!!<br/>"; }
			$write_the_block = FALSE;
			$record_to_append = array();
			$array_line_id++;
			$record_to_append['internal_id'] = $array_line_id;
			
			if ($write_this_first_block_roll_type_id > 0) { $record_to_append['roll_type_id'] = $write_this_first_block_roll_type_id; 
															$write_this_first_block_roll_type_id = 0; } 
			elseif ($block_roll_type_id > 0) { $record_to_append['roll_type_id'] = $block_roll_type_id; $block_roll_type_id = 0; } 
			if ($write_this_first_blocking_team_id > 0)	{ $record_to_append['team_id'] = $write_this_first_blocking_team_id;
															$write_this_first_blocking_team_id = 0; } 
			elseif ($blocking_team_id > 0) { $record_to_append['team_id'] = $blocking_team_id; $blocking_team_id = 0; } 
			if ($write_this_first_blocking_player_id > 0) { $record_to_append['player_id'] = $write_this_first_blocking_player_id; 
																	$write_this_first_blocking_player_id = 0; }
			elseif ($blocking_player_id > 0) { $record_to_append['player_id'] = $blocking_player_id; $blocking_player_id = 0; } 
			if ($block_die_chosen > 0) { $record_to_append['dice_roll'] = $block_die_chosen; $block_die_chosen = 0; } 
			//if ($roll_aim > 0) { $record_to_append['roll_aim'] = $roll_aim; $roll_aim = 0; }
			if ($block_perm_id > 0) { $record_to_append['roll_lookup_id'] = $block_perm_id; $block_perm_id = 0; } 
			if ($blocked_player_id > 0) { $record_to_append['target_player_id'] = $blocked_player_id; $blocked_player_id = 0; } 
			if ($block_outcome_id  > 0) { $record_to_append['outcome_id'] = $block_outcome_id; $block_outcome_id = 0; }
			if (($team_reroll_chosen) && ($loner_passed)) { $record_to_append['reroll_type_id'] = 2; $reroll_type_id = 0; 
																	$team_reroll_chosen = FALSE; $loner_passed = FALSE; }
			elseif ($team_reroll_chosen) { $record_to_append['reroll_type_id'] = 4; $reroll_type_id = 2; $team_reroll_chosen = FALSE; }
			elseif ($reroll_type_id > 0 ) { $record_to_append['reroll_type_id'] = $reroll_type_id; $reroll_type_id = 0; }

			if (!($modifier_text=='0')) { $record_to_append['modifier_text'] = $modifier_text; $modifier_text = 0; }
			
			$record_to_append['event_id'] = $event_id;

			array_push($final_array, $record_to_append);

		}

		if ($end_of_turn) {
			$end_of_turn = FALSE;

		}

		if ($ready_to_write) {
			if ($debug_mode==1) { echo "READY TO WRITE!!!!!!!!!!!"; }
			$ready_to_write = FALSE;
			$record_to_append = array();
			
			if (($team_reroll_chosen) && ($loner_passed)) { 
				$reroll_type_id = 2; 
				$team_reroll_chosen = FALSE; 
				$loner_passed = FALSE; 
			}
			elseif ($team_reroll_chosen) { $record_to_append['reroll_type_id'] = 4; $reroll_type_id = 2; $team_reroll_chosen = FALSE; }
			
			elseif ($team_reroll_chosen) { $reroll_type_id = 2;  // team re-roll
						$final_array[$array_line_id-1]['reroll_type_id'] = 4; 
						$team_reroll_chosen = FALSE; 
			}
			
			if ($loner_passes) {
				;//$final_array[$array_line_id-1]['reroll_type_id'] = 8; // set previous roll to say it was loner-passed
				//$loner_passes = FALSE; no don't do this; it is done when the next line is dealt with, which reads "team uses re-roll"
			}
			
			if ($reroll_type_id==1) { // if this is a skill reroll, set previous record to show skill reroll used
				$final_array[$array_line_id-1]['reroll_type_id'] = 5;
				$skill_reroll_available = FALSE;
				$skill_reroll_not_usable_by_player_id = $player_id; // ie you cant reroll dodges twice in one turn
			}
			
			// if it is any sort of a reroll, and we know the exact target value, modify the previous target to match
			// and vice versa!
			// pro and loner need special treatment!
			// seems to be firiung for some wrong things. eg gfi rolls 1, says target is 3 because of previous dodge attempt
			if(in_array($reroll_type_id, array(1,2)) && ($final_array[$array_line_id-1]['roll_target_exact_flag'] == 1)) {
				if ($debug_mode==1) { echo "PEANUTS"; }
				$roll_target_exact_flag = TRUE;
				$roll_aim = $final_array[$array_line_id-1]['roll_aim'];
			}
			elseif(in_array($reroll_type_id, array(1,2)) && ($roll_target_exact_flag===TRUE)) {
				$final_array[$array_line_id-1]['roll_target_exact_flag'] = 1;
				$final_array[$array_line_id-1]['roll_aim'] = $roll_aim;
			}
			
			// if it's a turnover, and we don't know the team ID, assume the team ID is the previous team ID
			if (($roll_type_id==46) && !($team_id>0)) {
				$team_id = $final_array[$array_line_id-1]['team_id'];
			}
			
			$array_line_id++;
			$record_to_append['internal_id'] = $array_line_id;
			
			if (($dice_roll >= $roll_aim) && ($skill_reroll_available)) {
				$skill_reroll_available = FALSE;
				$reroll_type_id = 9; // reroll available but not used
			}
			
			if ($roll_type_id > 0) { $record_to_append['roll_type_id'] = $roll_type_id; $roll_type_id = 0; 
										$prev_roll_type = $roll_type; unset($roll_type);
									} 
			if ($team_id > 0) { $record_to_append['team_id'] = $team_id; $team_id = 0; } 
			if ($player_id > 0) { $record_to_append['player_id'] = $player_id; $player_id = 0; } 
			if ($dice_roll > 0) { $record_to_append['dice_roll'] = $dice_roll; $dice_roll = 0; } 
			if ($roll_aim > 0) { $record_to_append['roll_aim'] = $roll_aim; $roll_aim = 0; }
			if ($roll_lookup_id > 0) { $record_to_append['roll_lookup_id'] = $roll_lookup_id; $roll_lookup_id = 0; }
			if ($reroll_type_id > 0) { $record_to_append['reroll_type_id'] = $reroll_type_id; $reroll_type_id = 0; } 
			if ($outcome_id > 0) { $record_to_append['outcome_id'] = $outcome_id; $outcome_id = 0; } 
			if ($outcome_text <> '') { $record_to_append['outcome_text'] = $outcome_text; $outcome_text = ''; }
			
			if ($reroll_type_id > 0 ) { $record_to_append['reroll_type_id'] = $reroll_type_id; $reroll_type_id = 0; }

			if (!($modifier_text=='0')) {  $record_to_append['modifier_text'] = $modifier_text; $modifier_text = 0; }
			
			if ($roll_target_exact_flag===TRUE) { $record_to_append['roll_target_exact_flag'] = 1; unset($roll_target_exact_flag); }
			elseif ($roll_target_exact_flag===FALSE) { $record_to_append['roll_target_exact_flag'] = 0; unset($roll_target_exact_flag); }
			
			$record_to_append['event_id'] = $event_id;

			array_push($final_array, $record_to_append);
		}


	} // end of foreach "every line in staging"

	$sql=$conn->prepare("SELECT Auto_increment FROM information_schema.tables WHERE table_name='bb_matchlog'");
	$sql->execute();
	$next_id = $sql->fetchColumn(0);
	// maybe need a transaction here to make sure the id is correct, but will it cause problems with scalability later on?
	
	// the data is all ready in $final_array. Now all we need to do is write it to the database.
	
		$sql=$conn->prepare("DELETE FROM bb_matchlog WHERE match_id = ?");
		$sql->execute(array($match_id));
		
		$sql = 'INSERT INTO bb_matchlog (match_id, team_id, player_id, roll_type_id, roll_target, outcome_id, reroll_type_id, raw_text
					, roll_value, roll_lookup_id, target_player_id, modifier_text, roll_target_exact_flag) VALUES ';
		$insertQuery = array();
		$insertData = array();
		$line_no = 0;
		
		if ($debug_mode==1) {
			echo '<table>,<tr><th>ID</th><th>event ID</th><th>Team ID</th><th>Player ID</th><th>Roll Type</th><th>Roll target</th><th>Roll</th>';
			echo '<th>Roll lookup</th><th>target_player_id</th><th>Outcome</th><th>Reroll type</th><th>Outcome text</th></tr>';
		}
		foreach($final_array AS $row) {
			if ($debug_mode==1) {
				echo '<tr>';
				echo '<td>' . $row['internal_id'] . '</td>';
				echo '<td>' . $row['event_id'] . '</td>';
				echo '<td>' . $row['team_id'] . '</td>';
				echo '<td>' . $row['player_id'] . '</td>';
				echo '<td>' . $row['roll_type_id'] . '</td>';
				echo '<td>' . $row['roll_aim'] . '</td>';
				echo '<td>' . $row['dice_roll'] . '</td>';
				echo '<td>' . $row['roll_lookup_id'] . '</td>';
				echo '<td>' . $row['target_player_id'] . '</td>';
				echo '<td>' . $row['outcome_id'] . '</td>';
				echo '<td>' . $row['reroll_type_id'] . '</td>';
				echo '<td>' . $row['outcome_text'] . '</td>';
				echo '</tr>';
			}
			$insertQuery[] = '(?, ?, ?, ?, ?, ? ,? ,?, ?, ?, ?, ?, ?)';
			$insertData[] = $match_id;
			$insertData[] = $row['team_id'];
			$insertData[] = $row['player_id'];
			$insertData[] = $row['roll_type_id'];
			$insertData[] = $row['roll_aim'];
			$insertData[] = $row['outcome_id'];
			$insertData[] = $row['reroll_type_id'];
			$insertData[] = $row['outcome_text'];
			$insertData[] = $row['dice_roll'];
			$insertData[] = $row['roll_lookup_id'];
			$insertData[] = $row['target_player_id'];
			$insertData[] = $row['modifier_text'];
			$insertData[] = $row['roll_target_exact_flag'];
		}
		if ($debug_mode==1) { echo '</table>'; }
		
		// Do the SQL statement
		if (!empty($insertQuery)) {
			$sql .= implode(', ', $insertQuery);
			$stmt = $conn->prepare($sql);
			$stmt->execute($insertData);
		}
		
		
		$sql=$conn->prepare("SELECT CASE WHEN t.description IS NULL THEN 'N/A' ELSE t.description END AS team
								, p.description AS player
								, rt.description AS roll_type
								, CONCAT('(',CASE WHEN rt.dice_type_id = 4 THEN bdp.short_description ELSE roll_target END,')' 
									,COALESCE(bd.description, roll_value), '= ', COALESCE(raw_text, '')) AS roll_detail
								, COALESCE(w.description, 'blah') AS lookup_val
								, o.description as outcome
							FROM bb_matchlog ml
							LEFT JOIN bb_team t ON ml.team_id = t.team_id
							LEFT JOIN bb_player p ON ml.player_id = p.player_id
							LEFT JOIN bb_lkp_roll_type rt ON ml.roll_type_id = rt.roll_type_id
							LEFT JOIN bb_lkp_weather w ON ml.roll_lookup_id = w.weather_id AND ml.roll_type_id = 28
							LEFT JOIN bb_lkp_block_dice bd ON ml.roll_value = bd.block_dice_id AND rt.dice_type_id = 4
							LEFT JOIN bb_lkp_roll_outcome o ON ml.outcome_id = o.outcome_id
							LEFT JOIN bb_lkp_block_dice_perm bdp ON bdp.block_dice_perm_id = ml.roll_lookup_id
							WHERE ml.match_id = ?");
		$sql->execute(array($match_id));
		
	
} // end of function bb1_transform_log
///////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
///////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
///////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
//// there now follows functions that bb1_transform_log will call

function lookup_team_ids($conn, $text) {
	// text is the output of a preg_match... so contains full team name 1, team abbr 1, full team name 2, team abbr 2

	$return_this_array = array();

	$stmt=$conn->prepare("SELECT team_id FROM bb_team WHERE description = ?");
	$stmt->execute(array(trim($text[1])));
	$return_this_array[$text[2]] = $stmt->fetchColumn();

	$stmt->execute(array(trim($text[3])));
	$return_this_array[$text[4]] = $stmt->fetchColumn();

	return $return_this_array;
} // end of lookup_team_ids


function load_player_data($conn, &$player_array, $preg_matches, $curr_match_id) {
	// preg_match[2] has the bb1_id of the player
	// add an array to player_array[squad_number]. Attributes include player_id, and skills somehow.
	// NEED TO ALLOW FOR THE POSSIBILITY THAT A ONE-OFF PLAYER HAS BEEN INCLUDED!!!
	// NEED TO WORK OUT EFFECTIVE STAT LINES - ie by discounting future injuries or stat boosts
	// Cannot use squad_number as the only key.... as there are 2 teams, hence these will be duplicated
	// maybe go for $player_array['MOO'][squad_number]... we don't know MOO initially though so have to change that later on

	$append_this_array = array();
	$player_id = $preg_matches[2];


	$stmt=$conn->prepare("SELECT p.*, pts.skill_id, sk.human_desc AS skill_description
				FROM bb_player p
				LEFT JOIN bb_lkp_player_type_skill pts ON p.player_type_id = pts.player_type_id
				LEFT JOIN bb_lkp_skill sk ON pts.skill_id = sk.skill_id
				WHERE p.bb1_id = :player_id
				UNION ALL
				SELECT p.*, ps.skill_id, sk.human_desc AS skill_description
				FROM bb_player p
				INNER JOIN bb_player_skill ps ON p.player_id = ps.player_id
				INNER JOIN bb_match m ON ps.match_id_debut = m.match_id
				INNER JOIN bb_match currm ON currm.match_id = :curr_match_id
				INNER JOIN bb_lkp_skill sk ON ps.skill_id = sk.skill_id
				WHERE p.bb1_id = :player_id
				AND m.match_date <= currm.match_date");


	$stmt->bindParam(':player_id', $player_id, PDO::PARAM_INT);
	$stmt->bindParam(':curr_match_id', $curr_match_id, PDO::PARAM_INT);
	$stmt->execute();

	$db_data = $stmt->fetchAll(PDO::FETCH_ASSOC);

	$squad_number = $db_data[0]['squad_number'];
	$append_this_array['player_id'] = $db_data[0]['player_id'];
	$append_this_array['mv'] = $db_data[0]['mv'];
	$append_this_array['st'] = $db_data[0]['st'];
	$append_this_array['ag'] = $db_data[0]['ag'];
	$append_this_array['av'] = $db_data[0]['av'];
	$team_id = $db_data[0]['team_id'];

	foreach($db_data AS $skill_row) {
		if ($skill_row['skill_id'] > 0) {  // player with no skills are having a null entry appended; this seems to fix the issue
			$append_this_array[$skill_row['skill_id']] = $skill_row['skill_description'];
		}
	}

	$player_array[$team_id][$squad_number] = $append_this_array;

} // end of load_player_data


function get_weather_id($line_text, $conn) {
	if(preg_match("^.*\. (.*)$^", $line_text, $m) <> 1) {
		echo "mystery weather 1!<br/>";
	}

	$stmt=$conn->prepare("SELECT weather_id from bb_lkp_weather WHERE description = ?");
	$stmt->execute(array($m[1]));

	return $stmt->fetchColumn(0);

} // end of get_weather_id


function find_skill_id($skill_text, $conn) {
	// some "uses" are rerolls (dodge, pass). Some are really for info only (jump up). Some aren't skill-related (blitz, to a certain extent frenzy).
	// block & loner are exceptions that need to be catered for, or ignored

	$stmt=$conn->prepare("SELECT skill_id
				FROM bb_lkp_skill WHERE human_desc = ?");
	$stmt->execute(array($skill_text));

	return $stmt->fetchColumn(0);
}

function get_block_roll_permutation($line_text, $conn) {
	// As far as I can figure out, the only way to figure out if a 2D or 3D AGAINST block has happened is to derive from the evidence at hand.
	// eg did a turnover occur? What are the respective strengths of the players? Was a reroll used even if there was a winning dice rolled?
	// Was a losing dice selected? (Warning, sometimes this is because time ran out and the game picks a die at random)
	// 95%+ of blocks are not AGAINST blocks so we will assume they are FOR blocks and only change our minds if there is convincing evidence in place.
                     
	// Get everything inside square brackets out of the text
	preg_match_all("/\[([^\]]+)\]/", $line_text, $block_die);
	
	// $block_die[1][x] contains the xth roll, without the square brackets
	//print_r($block_die);
	if (count($block_die[1]) == 1) {
		$stmt = $conn->prepare("SELECT p.block_dice_perm_id
					FROM bb_lkp_block_dice d
					INNER JOIN bb_lkp_block_dice_perm p
						ON d.block_dice_id = p.block_dice_id_1
					WHERE d.bb1_desc = ?
					AND p.block_dice_id_2 IS NULL
					AND p.block_dice_id_3 IS NULL");
		$stmt->execute(array($block_die[1][0]));
		return $stmt->fetchColumn(0);
	}
	//if more than 1 dice, we need to sort them by id, lowest to highest
	$stmt = $conn->prepare("SELECT block_dice_id
				FROM bb_lkp_block_dice d
				WHERE d.bb1_desc = ?
				UNION ALL SELECT block_dice_id
				FROM bb_lkp_block_dice d
				WHERE d.bb1_desc = ?
				UNION ALL SELECT block_dice_id
				FROM bb_lkp_block_dice d
				WHERE d.bb1_desc = ?
				ORDER BY block_dice_id ASC");
	$stmt->bindParam(1, $block_die[1][0], PDO::PARAM_INT);
	$stmt->bindParam(2, $block_die[1][1], PDO::PARAM_INT);
	if (count($block_die[1]) == 2) 	{ $stmt->bindValue(3, null, PDO::PARAM_INT); }
			else		{ $stmt->bindParam(3, $block_die[1][2], PDO::PARAM_INT); }

	// put the id's in an array called $roll_ids. This is sorted already thanks to ORDER BY clause
	$stmt->execute();
	$roll_ids = $stmt->fetchAll(PDO::FETCH_COLUMN, 0);

	if (count($block_die[1]) == 2) {
		$stmt = $conn->prepare("SELECT p.block_dice_perm_id
					FROM bb_lkp_block_dice_perm p
					WHERE p.block_dice_id_1 = ?
					AND p.block_dice_id_2 = ?
					AND block_dice_count_id = 2
					");
		$stmt->execute($roll_ids);
		return $stmt->fetchColumn(0);

	}
	if (count($block_die[1]) == 3) {
		$stmt = $conn->prepare("SELECT p.block_dice_perm_id
					FROM bb_lkp_block_dice_perm p
					WHERE p.block_dice_id_1 = ?
					AND p.block_dice_id_2 = ?
					AND p.block_dice_id_3 = ?
					AND block_dice_count_id = 1
					");
		$stmt->execute($roll_ids);
		return $stmt->fetchColumn(0);
	}

} // end of get_block_roll_permutation

function get_roll_type_table($conn) {
	// returns an array of the roll type table, so we don't have to query it loads of times
	$stmt = $conn->prepare("SELECT * FROM bb_lkp_roll_type");
	$stmt->execute();

	$output_array = $stmt->fetchAll(PDO::FETCH_ASSOC);

	//echo "<br/>Roll type"; print_r($output_array); echo "<br/>";

	return $output_array;

} // end of get_roll_type_table

function get_block_dice_table($conn) {
	// returns an array of the block dice table, so we don't have to query it loads of times
	$stmt = $conn->prepare("SELECT * FROM bb_lkp_block_dice");
	$stmt->execute();

	$output_array = $stmt->fetchAll(PDO::FETCH_ASSOC);

	//echo "<br/>Block_dice"; print_r($output_array); echo "<br/>";

	return $output_array;

} // end of get_block_type_table

function get_outcome_table($conn) {
	// returns an array of the block dice table, so we don't have to query it loads of times
	$stmt = $conn->prepare("SELECT * FROM bb_lkp_roll_outcome");
	$stmt->execute();

	$result = array();
	$sql_array = $stmt->fetchAll(PDO::FETCH_ASSOC);
	foreach ($sql_array as $row) {
		$result[$row['description']] = (int) $row['outcome_id'];
	}

	return $result;

} // end of get_outcome_table

function array_search_key_val($arr, $search_key, $search_value) {
	foreach($arr as $child_arr) {
		if ($child_arr[$search_key]==$search_value) {
			return $child_arr;
		}
	}
	return FALSE;
} // array_search_key_val


function load_stat_turnovers($conn, $match_id, $delete_existing) {
	if ($delete_existing) {
		$sql = $conn->prepare("DELETE FROM bb_stat_turnovers WHERE match_id = ?");
		$sql->execute(array($match_id));
	}

	// create temporary table
	$sql = $conn->prepare("CREATE TEMPORARY TABLE IF NOT EXISTS temp_table LIKE bb_stat_turnovers");
	$sql->execute();
	
	$sql = $conn->prepare("
		INSERT INTO temp_table
		SELECT	l.match_id
				, l.team_id
				, COALESCE(l.player_id, p.player_id, pp.player_id) AS player_id 
				, @rownum := @rownum + 1 AS predicted_turn_no
				, l.matchlog_id
				, l.roll_value AS turnover_type_id
				, p.roll_type_id
				, p.reroll_type_id
				, COALESCE(p.roll_lookup_id, p.roll_value) AS roll_value
				, COALESCE(pp.roll_lookup_id, pp.roll_value) AS prev_roll_value
				, CASE WHEN pp.roll_target_exact_flag = 1 THEN pp.roll_target ELSE p.roll_target END AS roll_target
				, CASE WHEN pp.roll_target_exact_flag = 1 THEN 1 ELSE p.roll_target_exact_flag END AS roll_target_exact_flag
				
				, pp.roll_type_id AS prev_roll_type_id
				, pp.reroll_type_id AS prev_reroll_type_id
		FROM	bb_matchlog l
		LEFT JOIN bb_matchlog p
				ON p.matchlog_id = (SELECT MAX(x.matchlog_id) FROM bb_matchlog x
										INNER JOIN bb_lkp_roll_type rtx ON x.roll_type_id = rtx.roll_type_id
										WHERE x.match_id = l.match_id
										AND x.matchlog_id < l.matchlog_id
										AND rtx.dice_type_id <> 2)
				AND l.roll_value NOT IN (1,2,3)
		LEFT JOIN bb_matchlog pp
				ON pp.matchlog_id = (SELECT MAX(x2.matchlog_id) FROM bb_matchlog x2
										INNER JOIN bb_lkp_roll_type rtx ON x2.roll_type_id = rtx.roll_type_id
										WHERE x2.match_id = l.match_id
										AND x2.matchlog_id < p.matchlog_id
										AND rtx.dice_type_id <> 2)
				AND p.reroll_type_id IS NOT NULL
			, (SELECT @rownum := 0) r
		WHERE	l.roll_type_id = 46
		AND		l.match_id = ?
		ORDER BY l.matchlog_id ASC");

	$sql->execute(array($match_id));
	
	// UPDATE failed dodges
	$sql=$conn->prepare("UPDATE temp_table
							SET turnover_type_id = 5
							WHERE roll_type_id = 7");
	$sql->execute();
	
	// UPDATE failed blocks
	$sql=$conn->prepare("UPDATE temp_table
							SET turnover_type_id = 4
							WHERE roll_type_id IN (21,22,23,24)");
	$sql->execute();
	
	// UPDATE failed GFI's
	$sql=$conn->prepare("UPDATE temp_table
							SET turnover_type_id = 8
							WHERE roll_type_id = 9");
	$sql->execute();
	
	// Everything else is a failed ball handling?
	$sql=$conn->prepare("UPDATE temp_table
							SET turnover_type_id = 6
							WHERE turnover_type_id = 7");
	$sql->execute();
	
	// Now work out the predicted_turn_no! Actually, screw this for now.
	/*
	$sql=$conn->prepare("UPDATE temp_table
							SET predicted_turn_no = 16
							ORDER BY predicted_turn_no DESC
							LIMIT 1");
	*/
							
	// now put it in to the final table
	$sql = $conn->prepare("
		INSERT INTO bb_stat_turnovers (
			match_id
			, team_id
			, player_id
			, predicted_turn_no
			, matchlog_turnover_id
			, turnover_type_id
			, roll_type_id
			, reroll_type_id
			, roll_value
			, prev_roll_value
			, roll_target
			, roll_target_exact_flag
			, prev_roll_type_id
			, prev_reroll_type_id)
		SELECT *
		FROM	temp_table;");
	$sql->execute();
} // end of load_stat_turnovers

?>