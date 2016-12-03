<?php include_once("inc/header1.php");

// if you need to return header() statements, do them here. SQL connection has already been established.

include_once("inc/header2.php"); ?>

BBDB</title>

<script>
$(document).ready(function() 
    { 
        $("#playerTable").tablesorter(); 
    } 
); 
    
</script>
<?php // include anything else you want to put in <head> here.

include_once("inc/header3.php"); ?>


<h2>Players</h2>



<br/>

<table id="playerTable" class="tablesorter">
<thead>
<tr><th>Race</th><th>Type</th><th>Name</th><th>Team</th><th>MV</th><th>ST</th><th>AG</th><th>AV</th><th>Lv</th><th>XP</th><th>Val</th></tr>
</thead>
<tbody>
</tbody>
</table>


<p>To do - everything (v2).</p>


<?php include_once("inc/footer.php"); ?>