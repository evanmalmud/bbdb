<div id='cssmenu'>

<ul>
<?php
if (isset($_SESSION['ok'])) {
?>
<li><a href='<?php echo $base_url;?>index.php'><span>Home</span></a></li>

<li><a href='<?php echo $base_url;?>competitionlist.php'><span>Competitions</span></a></li>

<li class='active has-sub'><a href='<?php echo $base_url;?>teamlist.php'><span>Teams</span></a>

	<ul>
		<li><a href='<?php echo $base_url;?>teamlist.php'><span>Basic View</span></a></li>
	    <li><a href='<?php echo $base_url;?>teamlistadv.php'><span>Advanced View</span></a></li>
	    <li><a href='<?php echo $base_url;?>teamlistadvavg.php'><span>Average Per Match View</span></a></li>
		<li><a href='<?php echo $base_url;?>team_awards.php'><span>Awards</span></a></li>
		<li><a href='<?php echo $base_url;?>team_global_stats.php'><span>Global Stats</span></a></li>
	</ul>

</li>

<li class='active has-sub'><a href='<?php echo $base_url;?>matchlist.php'><span>Matches</span></a>

	<ul>
<li><a href='<?php echo $base_url;?>matchlist.php'><span>Basic View</span></a>
</li>
	    <li><a href='<?php echo $base_url;?>matchlistadv.php'><span>Advanced View</span></a>
</li>
	</ul>

</li>

<li class='active has-sub'><a href='<?php echo $base_url;?>playerlist.php'><span>Players</span></a>

	<ul>
		<li><a href='<?php echo $base_url;?>playerlist.php'><span>Characteristics View</span></a></li>
	    <li><a href='<?php echo $base_url;?>playerliststats.php'><span>Statistics View</span></a></li>
		<li><a href='<?php echo $base_url;?>player_awards.php'><span>Awards</span></a></li>
	</ul>

</li>

<li class='active has-sub'><a href='<?php echo $base_url;?>standingdata.php'><span>Standing Data</span></a>

	<ul>
<li><a href='<?php echo $base_url;?>playertypelist.php'><span>Player Type</span></a>
</li>

<li><a href='<?php echo $base_url;?>playersupertypelist.php'><span>Supertype</span></a>
</li>
	
<li><a href='<?php echo $base_url;?>standingdata.php?datatypeid=3'><span>Races</span></a>
</li>
	
<li><a href='<?php echo $base_url;?>standingdata.php?datatypeid=1'><span>Skills</span></a>
</li>
	    
<li class='has-sub'><a href='#'><span>Skill Categories</span></a>

		<ul>
		<li><a href='<?php echo $base_url;?>standingdata.php?datatypeid=2&amp;categoryid=2'><span>Agility</span></a></li>

		<li><a href='<?php echo $base_url;?>standingdata.php?datatypeid=2&amp;categoryid=6'><span>Extraordinary</span></a></li>

		<li><a href='<?php echo $base_url;?>standingdata.php?datatypeid=2&amp;categoryid=7'><span>Extraordinary (nega-traits)</span></a></li>

		<li><a href='<?php echo $base_url;?>standingdata.php?datatypeid=2&amp;categoryid=1'><span>General</span></a></li>

		<li><a href='<?php echo $base_url;?>standingdata.php?datatypeid=2&amp;categoryid=5'><span>Mutation</span></a></li>

		<li><a href='<?php echo $base_url;?>standingdata.php?datatypeid=2&amp;categoryid=3'><span>Passing</span></a></li>

		<li class='last'><a href='<?php echo $base_url;?>standingdata.php?datatypeid=2&amp;categoryid=4'><span>Strength</span></a></li>

	    </ul>
</li>
	
<li><a href='<?php echo $base_url;?>standingdata.php?datatypeid=5'><span>SPP Levels</span></a>
</li>
	</ul>

</li>



<li><a href='<?php echo $base_url;?>upload.php'><span>Submit Match</span></a></li>

<li class='active has-sub'><a href='<?php echo $base_url;?>faq.php'><span>Help</span></a>

	<ul>
		<li><a href='<?php echo $base_url;?>upload_tutorial.php'><span>Uploading</span></a></li>
		<li><a href='<?php echo $base_url;?>changelog.php'><span>Change Log</span></a></li>
	</ul>

</li>
<?php

}
else {
?>
<li><a href='<?php echo $base_url;?>index.php'><span>Home</span></a></li>

<li class='active has-sub'><a href='<?php echo $base_url;?>teamlist.php'><span>Teams</span></a>

	<ul>
		<li><a href='<?php echo $base_url;?>team_global_stats.php'><span>Global Stats</span></a></li>
	</ul>

</li>

<li class='active has-sub'><a href='<?php echo $base_url;?>standingdata.php'><span>Standing Data</span></a>

	<ul>
<li><a href='<?php echo $base_url;?>playertypelist.php'><span>Player Type</span></a>
</li>
	
<li><a href='<?php echo $base_url;?>standingdata.php?datatypeid=3'><span>Races</span></a>
</li>
	
<li><a href='<?php echo $base_url;?>standingdata.php?datatypeid=1'><span>Skills</span></a>
</li>
	    
<li class='has-sub'><a href='#'><span>Skill Categories</span></a>

		<ul>
		<li><a href='<?php echo $base_url;?>standingdata.php?datatypeid=2&amp;categoryid=2'><span>Agility</span></a></li>

		<li><a href='<?php echo $base_url;?>standingdata.php?datatypeid=2&amp;categoryid=6'><span>Extraordinary</span></a></li>

		<li><a href='<?php echo $base_url;?>standingdata.php?datatypeid=2&amp;categoryid=7'><span>Extraordinary (nega-traits)</span></a></li>

		<li><a href='<?php echo $base_url;?>standingdata.php?datatypeid=2&amp;categoryid=1'><span>General</span></a></li>

		<li><a href='<?php echo $base_url;?>standingdata.php?datatypeid=2&amp;categoryid=5'><span>Mutation</span></a></li>

		<li><a href='<?php echo $base_url;?>standingdata.php?datatypeid=2&amp;categoryid=3'><span>Passing</span></a></li>

		<li class='last'><a href='<?php echo $base_url;?>standingdata.php?datatypeid=2&amp;categoryid=4'><span>Strength</span></a></li>

	    </ul>
</li>
	
<li><a href='<?php echo $base_url;?>standingdata.php?datatypeid=5'><span>SPP Levels</span></a>
</li>
	</ul>

</li>

<li class='active has-sub'><a href='<?php echo $base_url;?>faq.php'><span>Help</span></a>

	<ul>
		<li><a href='<?php echo $base_url;?>upload_tutorial.php'><span>Uploading</span></a></li>
		<li><a href='<?php echo $base_url;?>changelog.php'><span>Change Log</span></a></li>
	</ul>

</li>

<?php

}
?>
</ul>

</div>