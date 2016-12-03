<?php include_once("../inc/header1.php");

// if you need to return header() statements, do them here. SQL connection has already been established.

ini_set('display_errors', 'On');
ini_set('html_errors', 0);
error_reporting(-1);

include_once("../inc/header2.php"); ?>

BBDB - admin</title>
<script>
$(function() {
	$( "#tabs" ).tabs();
});
</script>
<script>
	$(document).ready(function() {
<?php // include anything else you want to put in <head> here.

if (permission_check(8)) { // Create competition?>

	
		$('#newcompform').submit(function(event) {
			event.preventDefault();
			 var formData = {
				'comp_name'              : $('input[name=comp_name]').val(),
				'comp_short_name'        : $('input[name=comp_short_name]').val(),
				'comp_type_id'    		 : $('select[name=comp_type_id]').val()
			};
			
			request = $.ajax({
				url: "<?php echo $base_url;?>ajax/setup_new_league.php",
				type: "POST",			
				dataType: "html",
				data: formData
			});
			
			request.done(function(msg) {
				$("#create_competition_feedback").html(msg);			
			});

			request.fail(function(jqXHR, textStatus, errorThrown) {
				alert( "Request failed: " + textStatus );
				$("#create_competition_feedback").html("Failed!");
				console.error(
				"The following error occurred: "+
				textStatus, errorThrown
				);
			});
		});
	<?php } if  (permission_check(9)) { // Assign user?>		
		$('#assign_user').submit(function(event) {
			event.preventDefault();
			var button_name = $("input[type=submit][clicked=true]").attr('name');
			var button_no = button_name.split('_')[1];
			
			var formData = {
				'coach_id'              : $('select[name=coach_id_'+button_no).val(),
				'team_id'        		: button_no
			};
			
			request = $.ajax({
				url: "<?php echo $base_url;?>ajax/assign_coach_to_unassigned_team.php",
				type: "POST",			
				dataType: "html",
				data: formData
			});
			
			request.done(function(msg) {
				$("#result_"+button_no).html(msg);			
			});

			request.fail(function(jqXHR, textStatus, errorThrown) {
				alert( "Request failed: " + textStatus );
				$("#result_"+button_no).html("Failed!");
				console.error(
				"The following error occurred: "+
				textStatus, errorThrown
				);
			});
		});
		
		$("#assign_user input[type=submit]").click(function() { // not sure what this is for. But the internet suggested it.
			$("input[type=submit]", $(this).parents("#assign_user")).removeAttr("clicked");
			$(this).attr("clicked", "true");
		});
	
	
<?php	
} // end of permission_check(9)
if  (permission_check(10)) { // Create new user?>
	function load_coaches($param) {
		request = $.ajax({
			url: "<?php echo $base_url;?>ajax/coaches_as_option_list.php",
			type: "POST",			
			dataType: "html"
		});
		
		request.done(function(msg) {
			$("#coach_list_options").html('<select id="coach_list" size="10">' + msg + '</select>');			
		});
		return this;
	}; 
	
	load_coaches($('#tabs')); // doesn't matter what parameter goes here, but it whines if it's blank
	
	// add some function here that calls load_players whenever a change of players is made?

<?php
} // end of permission_check(10)
?>
});
</script> <!-- end of jquery stuff -->
<?php

include_once("../inc/header3.php"); 
include_once("../inc/stat_update.php");

echo "<h2>Admin control panel</h2>
<p>Very much in development...</p>";

?>
<div id="tabs">
  <ul>
	<?php if (permission_check(10)) {?><li><a href="#tabs-0">Coaches</a></li><?php }?>
    <?php if (permission_check(9)) {?><li><a href="#tabs-1">Coaches->Teams</a></li><?php }?>
    <?php if (permission_check(8)) {?><li><a href="#tabs-2">Competitions</a></li><?php }?>
  </ul>
  <?php if (permission_check(10)) {?>
		<div id="tabs-0">
		<h3>List of current coaches</h3>
		<form>
		<div id="coach_list_options">
		</div>
		</form>
		<h3>Add a coach</h3>
		<form>
		<input name="new_coach_name"/>
		<br/>
		<input type="submit" value="Add coach"/>
		<br/>
		<div id="new_coach_result"></div>
		</form>
		</div>
  <?php } // end of if(permission_check(10))
	if (permission_check(9)) {?>
	  <div id="tabs-1">
	  <p>This is where you will be able to assign coaches to teams.</p>
	  <?php
		$a = $conn->prepare("SELECT DISTINCT t.team_id, t.description FROM bb_team t
									INNER JOIN bb_match m ON t.team_id IN (m.home_team_id, m.away_team_id)
								WHERE t.coach_id IS NULL
								AND m.domain_id = ?
								ORDER BY t.description");

		$a->execute(array($_SESSION['domain_id']));
													
		$teams_without_coaches = $a->fetchAll(PDO::FETCH_ASSOC);

		$b = $conn->prepare("SELECT coach_id, description FROM bb_coach
								WHERE domain_id = ? ORDER BY description");
		$b->execute(array($_SESSION['domain_id']));

		$coach_list = $b->fetchAll(PDO::FETCH_ASSOC);
		
		$c = $conn->prepare("SELECT competition_type_id, description FROM bb_lkp_competition_type
								WHERE competition_type_id <> 0
								ORDER BY description");
		$c->execute(array($_SESSION['domain_id']));

		$competition_types = $c->fetchAll(PDO::FETCH_ASSOC);
		
								
		echo '<form method="post"  id="assign_user">'.PHP_EOL;
								
		echo "<table><tr><td>Team Name</td><td>Coach</td><td></td><td></td></tr>";

		foreach ($teams_without_coaches AS $team) {
			// logic for sending to ajax script
			// botton name will be of the format button_x where x is the ID of the team
			// ajax script gets this number, validates it has no coach,
			// and then applies the submitted coach ID to it (from the field coach_id_x)
			$team_id = (int) $team['team_id'];
			echo '<tr>';
			echo "<td>" . $team['description'] . "</td>".PHP_EOL;
			echo '<td id="optionbox_' . $team_id . '"><select name="coach_id_' . $team_id . '">';
			echo '<option value="null">**pick a coach**</option>'.PHP_EOL;
			foreach ($coach_list AS $coach) {
				echo '<option value="' . $coach['coach_id'] .'">' . $coach['description'] . '</option>'.PHP_EOL;
			}
			echo '</select></td>';
			echo '<td><input type="submit" name="button_' . $team_id . '" value="Assign coach to team"></td>' . PHP_EOL;
			echo '<td id="result_' . $team_id . '"></td>'.PHP_EOL; // for returning result to user
			echo "</tr>".PHP_EOL;
		}

		echo "</table></form>";
	  ?>
	  </div>
  <?php } // end of "if permission_check(9)"  
  if (permission_check(8)) { ?>
	  <div id="tabs-2">
	  <p>Create new competitions here.</p>
	  <form method="post" id="newcompform">
	  <table><tr><td>Competition Name:</td><td><input name="comp_name"/></td></tr>
	  <tr><td>Competition Short Name:</td><td><input name="comp_short_name"/></td></tr>
	  <tr><td>Competition Type:</td><td><select name="comp_type_id">
	  <?php
	  foreach ($competition_types AS $row) {
				echo '<option value="' . $row['competition_type_id'] .'">' . $row['description'] . '</option>'.PHP_EOL;
			}
	  ?>
	  </select></td></tr>
	  <tr><td colspan="2"><input type="submit" value="Create Competition"></td></tr>
	  </table>
	  </form>
	  <p id="create_competition_feedback"></p>
  </div>
  <?php } // end of "if (permission_check(8))" ?>
</div>  <!-- end of the tabs display model -->

<?php
include_once("../inc/footer.php"); ?>