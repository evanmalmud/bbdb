<?php include_once("/inc/header1.php");

// if you need to return header() statements, do them here. SQL connection has already been established.

include_once("inc/header2.php"); ?>

Data Management</title>

<?php // include anything else you want to put in <head> here.

include_once("inc/header3.php"); ?>



<h2>FAQ</h2>



<h3>Kills?</h3>
<p>It appears that the kills stat includes kills caused by fouls. Therefore it is possible for the kills count to be higher than the injuries inflicted count (which only counts normal blocking-related injuries - ie ones that grant 2SPP's).</p>

<h3>What's a knockdown?</h3>
<p>When someone breaks armour through a block-related event (definition may be refined, altered or completely changed in the future as more investigations are done).</p>


<h3>Why can knockdowns be greater than the number of successful blocks?</h3>
<p>Not sure. Piling on? See halflings - skaven match?</p>

<h3>Why are thralls beaten up so much? Does bloodlust count as a knockdown?</h3>
<p>Good question.</p>

<h3>How can you give some stats as being the total for that team/player despite not having all the games uploaded?</h3>
<p>Some cumulative stats are stored in the saved games. These include touchdowns, passes, casualties and so on. Some others, such as blocks attempted/successful, dodges made etc. are only available on a game-by-game basis.</p>


<h3>If an injury is re-rolled into a non-injury by an apothacery, does it still count as an injury?</h3>
<p>Yes, it does. This means that it is possible for a player to be injured twice in one game.</p>

<h3>Decay?</h3>
<p>Counts as 1 injury for the injury stats, though both injuries will be attributed to the player in question.</p>

<h3>Casualties does not equal injuries on the match report screen.</h3>
<p>This is because injuries also includes ones suffered via non-blocking means, such as fouls and crowd-surfing.</p>

<?php include_once("inc/footer.php"); ?>
