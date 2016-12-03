<?php include_once("inc/header1.php");

// if you need to return header() statements, do them here. SQL connection has already been established.

include_once("inc/header2.php"); ?>

BBDB</title>

<?php // include anything else you want to put in <head> here.

include_once("inc/header3.php"); ?>



<h2>Upload tutorial</h2>

<p>The upload process is via a web form. It's a little bit fiddly, but unfortunately there is no way around this without writing a desktop app to do it automatically (like BB Manager did). A web browser cannot automatically find files on your hard disk - imagine if they could - it would potentially be a huge security issue.</p>

<p>You can only upload the last played (or viewed) game. So it is recommended you upload the game soon after playing it. It has been reported that even closing & reopening blood bowl before uploading can cause issues.</p>

<p>If you want to upload an old game, you can load the replay in the Blood Bowl client, watch it through (press "+" several times to increase the speed of the replay) and then upload it.</p>

<p>You get to the upload screen by logging in, then you will see a "submit match" option in the toolbar at the top of the screen, if you have the correct permissions. The upload screen looks a bit like this:</p>

<img src="upload_example.png" alt="screenshot" width="600"/>

<p>There are several files to upload as the save game file does not contain all the required information - also needed are extra files produced by the client upon a game completing - the MatchReport file (contains end game stats) and the log files (contains information on the dice rolls - basically everything that you can see on the log screen in-game). If you hover over the titles it tells you where you can find these files on your hard disk.</p>

<p>Apart from finding all the files you only have to provide two more pieces of information. Firstly the competition - this will be limited to "open" competitions in your community (once a competition is complete the commissioner will set it to "complete" and it won't appear on this list anymore). Also there is space for a brief description - it helps me as the administrator if you could type something very brief here about the coaches/races who were playing, eg Nurgle - Orcs.</p>

<p>After clicking submit you will have to wait a period of time for the upload to process. This ranges from about 3 seconds to 90 seconds depending on how busy my webhost's server is. If you get an error message, let me know what the error message is and I will try and sort it out (improving the upload process reliability is an ongoing piece of work).</p>

<?php include_once("inc/footer.php"); ?>
