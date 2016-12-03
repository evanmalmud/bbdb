<?php
// A home for all functions that involve compressing or decompressing files

$relative_path = ""; // path to root website from wherever this file is

function zip_saved_game ($conn, $match_id, $delete_originals) {
	global $relative_path;
	
	// 1) get replay file name & check for existence
	$sql = $conn->prepare("SELECT u.*, ht.description AS home_team, at.description AS away_team, m.match_date
							FROM bb_upload u
								INNER JOIN bb_match m ON u.match_id = m.match_id
								INNER JOIN bb_team ht ON m.home_team_id = ht.team_id
								INNER JOIN bb_team at ON m.away_team_id = at.team_id
							WHERE m.match_id = ?");
	if(!$sql->execute(array($match_id))) {
		echo "SQL statement failed.";
		return;
	}

	$upload_info = $sql->fetch(PDO::FETCH_ASSOC);
	$replay_file_name = $upload_info['filename'];
	// check that (at least) 3 files exist
	if(!file_exists($relative_path."uploads/".$replay_file_name)) {
		echo "Cannot find replay file. Maybe it has been archived already.";
		return;
	}
	if(!file_exists($relative_path."uploads/mr_".$replay_file_name)) {
		echo "Post-match file cannot be found. This is an unusual error message - tell an admin about it.";
		return;
	}
	if(!file_exists($relative_path."uploads/log_".$replay_file_name)) {
		echo "Log file cannot be found. This is an unusual error message - tell an admin about it.";
		return;
	}
	// this will be the friendly name of the saved game
	$friendly_replay_file_name = "Replay ". $upload_info['home_team'] . " vs " . $upload_info['away_team']."_" . date("Y_m_d", strtotime($upload_info['match_date']));
	$friendly_replay_file_name = $friendly_replay_file_name . '.db'; 
	
	$zip = new ZipArchive();
	$zip_filename = $relative_path."uploads/archive/$match_id.zip";

	if (file_exists($zip_filename)) {
		echo "Archive file already exists";
		return;
	}
	elseif ($zip->open($zip_filename, ZipArchive::CREATE)!==TRUE) {
		echo "cannot open <$zip_filename>\n";
		return;
	}
	else {
		// we have created the zip file, now add the files to it
		$zip->addFile($relative_path."uploads/".$replay_file_name
						, $friendly_replay_file_name);
		$zip->addFile($relative_path."uploads/mr_".$replay_file_name
						, "MatchReport.sqlite");
		$zip->addFile($relative_path."uploads/log_".$replay_file_name
						, "BB_LE000.txt");
		if(file_exists($relative_path."uploads/log2_".$replay_file_name)) {
			$zip->addFile($relative_path."uploads/log2_".$replay_file_name
						, "BB_LE001.txt");
		}
		if(file_exists($relative_path."uploads/log3_".$replay_file_name)) {
			$zip->addFile($relative_path."uploads/log3_".$replay_file_name
						, "BB_LE002.txt");
		
		}
		//echo "New zip file created.<br/>";
		
		// Looks like our zip file is ready! Woop. Delete originals if parameter says so
		if($delete_originals === TRUE) {
			unlink($relative_path."uploads/".$replay_file_name);
			unlink($relative_path."uploads/mr_".$replay_file_name);
			unlink($relative_path."uploads/log_".$replay_file_name);
			if(file_exists($relative_path."uploads/log2_".$replay_file_name)) {
				unlink($relative_path."uploads/log2_".$replay_file_name);
			}
			if(file_exists($relative_path."uploads/log3_".$replay_file_name)) {
				unlink($relative_path."uploads/log3_".$replay_file_name);
			}
		}
	}

}

function get_replay ($conn, $match_id, $target_folder) {
	// fetches the replay file - be that in a zip file or not - and puts it in target_folder
	global $relative_path;
	
	$sql = $conn->prepare("SELECT u.*, ht.description AS home_team, at.description AS away_team, m.match_date
							FROM bb_upload u
								INNER JOIN bb_match m ON u.match_id = m.match_id
								INNER JOIN bb_team ht ON m.home_team_id = ht.team_id
								INNER JOIN bb_team at ON m.away_team_id = at.team_id
							WHERE m.match_id = ?");
	if(!$sql->execute(array($match_id))) {
		echo "SQL statement failed.";
		return;
	}
	
	$upload_info = $sql->fetch(PDO::FETCH_ASSOC);
	$replay_file_name = $upload_info['filename'];
	
	if(file_exists($relative_path."uploads/".$replay_file_name)) {
		// this will be the friendly name of the saved game
		$friendly_replay_file_name = "Replay ". $upload_info['home_team'] . " vs " . $upload_info['away_team']."_" . date("Y_m_d", strtotime($upload_info['match_date'])). '.db';
		// copy copes the file and over-writes it if it already exists. That is OK; contents of dl are to be treated as transitory
		if(copy($relative_path."uploads/".$replay_file_name,
					$relative_path.$target_folder."/".$friendly_replay_file_name)) {
			//File ready for download
			return_download_link($relative_path.$target_folder."/".$friendly_replay_file_name, FALSE);
		}
		else {
			echo "Failed to retrieve the replay file.";
			return;
		}
	}
	else {
		$res = $zip->open($relative_path."uploads/archive/".$match_id.".zip");
		if ($res == TRUE) { //zip archive found and can be opened
			if($zip->extractTo($relative_path.$target_folder."/", $friendly_replay_file_name)) {
				//File ready for download
				return_download_link($relative_path.$target_folder."/".$friendly_replay_file_name, FALSE);
			}
			else { // extraction failed
				echo "Failed to extract file from archive.";
				return;
			}
		}
		else {
			echo "Cannot find replay file.";
			return;	
		}
	}
	
}	

function get_zip_archive ($conn, $match_id, $target_folder) {
	// fetches the zip file - and puts it in target_folder
	// if there isn't a zip archive, create it!!
	global $relative_path;
	
	if(!file_exists($relative_path."uploads/archive/".$match_id.".zip")) {
		zip_saved_game ($conn, $match_id, FALSE);
	}
	if(copy($relative_path."uploads/archive/".$match_id.".zip"
					, $relative_path."dl/".$match_id.".zip")) {
			//File ready for download
			return_download_link($relative_path."dl/".$match_id.".zip", TRUE);
	}
	else {
		echo "Failed to retrieve the zip file.";
		return;
	}
}

// return the link to the file to the browser
function return_download_link($file_path, $is_zip_file = FALSE) {
	if (file_exists($file_path)) {

		header($_SERVER["SERVER_PROTOCOL"] . " 200 OK");
		header("Cache-Control: public"); // needed for i.e.
		if($is_zip_file) {
			header("Content-Type: application/zip");
		}
		else {
			header('Content-type: application/octet-stream');
		}
		header("Content-Transfer-Encoding: Binary");
		header("Content-Length: ".filesize($file_path));
		header('Content-Disposition: attachment; filename="'.basename($file_path).'"');
		readfile($file_path);
		die();        
	} else {
		die("Error: File not found.");
	} 
}

?>