<?php include_once("inc/header1.php");

// if you need to return header() statements, do them here. SQL connection has already been established.

include_once("inc/header2.php"); ?>

BBDB</title>

<?php // include anything else you want to put in <head> here.

include_once("inc/header3.php"); ?>



<h2>Change log</h2>

<p>Not an exhaustive list - especially for older weeks.</p>

<h4>Week starting 2nd May 2016</h4>
<ul><li>Archiving code written, enabling uploads to be zipped (to cut down on space used on the server by 80-85%). Users can download zips, or can just download the replay. The archiving is invisible to the end-user.</li>
<li>Added a second method of sorting leagues. TD difference was the first, and the new way is TD difference plus (0.5* casualties difference). Tables now also display casualtied sustained & state the tiebreaker rules in force.</li>
</ul>

<h4>Week starting 25th April 2016</h4>
<ul><li>Minor API tweaks.</li>
<li>Begun creating commissioner's controls. Can now assign coaches to teams, and create new competitions.</li>
<li>Fixed bug around uploading older matches, where it would retire newer players.</li>
</ul>

<h4>From 11th April 2016 until at least 24th April 2016</h4>
<ul><li>Taking a break.</li>
<li>Fixed the "you can't upload a match more than once" code which was returning false positives.</li>

<h4>Week starting 4th April 2016</h4>
<ul>
<li>Added a link to download matches from the match report page.</li>
<li>Made most of the website unaccessible without a login. Standing data being the main exception.</li>
<li>If you are logged in, you should only see data relating to your community.</li>
<li>Uploader should now disallow the same match being uploaded twice, even by different coaches.</li>
<li>First steps made towards being able to create a new competition from within the client.</li>
</ul>

<h4>Week starting 28th March 2016</h4>
<ul>
<li>Finished typing up all skill descriptions!!!!!!!!</li>
<li>Admins can now refresh league tables & competition statistics via an in-broswer button.</li>
<li>An upload guide created and added to the help menu.</li>
<li>Fixed a couple of the team stats (per competition) that weren't working properly, because calculating "against" stats is more complicated than "for" stats.</li>
<li>After uploading a match, the user is now presented with a link to take them to the match report page. Yay for improving user experience!</li>
<li>Uploader speeded up a little, and improved logging.</li>
</ul>

<h4>Week starting 21st March 2016</h4>
<ul>
<li>Logins implemented. Initially don't do much, besides telling you you're logged in, but is key for future development.</li>
<li>Began front-end work to support multiple communities.</li>
<li>Match report API developed to a sufficient extent to let id3nt1ty create something from it.</li>
<li>Added team logos to match report page and team page. Because colour/graphics are good to have.</li>
<li>Small improvements to upload speed; also improved logging so I can see where it failed.</li>
</ul>

<h4>Week starting 14th March 2016</h4>
<ul>
<li>Player awards (per competition) implemented.</li>
<li>Under the hood API development.</li>
</ul>

<h4>Week starting 29th February 2016</h4>
<ul>
<li>Competition pages link to the "top 10" team/player stats page for that competition.</li>
</ul>

<h4>Week starting 8th Feb 2016</h4>
<ul>
<li>Match report page upgraded to display dice stats. D6 and 2D6 are still missing some rolls, but block dice is complete. With the loss of BB Manager, I wanted to get something up ASAP.</li>
</ul>


<h4>Week starting 10th Jan 2016</h4>
<ul>
<li>First version of match log parsing is released, so people can view the match logs. Many known bugs but the fruit of an awful lot of labour, and a necessary stepping stone towards awesome dice stats.</li>
</ul>



<h4>Week starting 12th Oct 2015</h4>
<ul>
<li>Fixing upload bug fixes.</li>
<li>Finished "version 1.0" which included...</li>
<ul>
<li>Calculated league tables</li>
<li>Skills grouped into categories (General, Agility etc.)</li>
<li>Player lists (by characteristics and statistics)</li>
<li>Current rosters listed on team page (but not dead/retired players)</li>
<li>Player screen created - shows stat lines, skills, statistics and match breakdown</li>
</ul>
</ul>

<h4>Week starting 28th Sep 2015</h4>
<ul>
<li>Uploading games is now possible!</li>
<li>Major release completed. Can't remember what was in it. I called it v0.5 so it must've been good.</li> 
</ul>


<h4>Week starting 31st Aug 2015</h4>
<ul>
<li>Work begins on BBDB!</li>
</ul>


<?php include_once("inc/footer.php"); ?>
