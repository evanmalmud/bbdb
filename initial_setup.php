<?php include_once("inc/header1.php");

// if you need to return header() statements, do them here. SQL connection has already been established.

include_once("inc/header2.php"); ?>

BBDB</title>


<script>
function checkPassword(password, strengthMsg, submitBtn)
   {
	var strengths = ['Very weak','A bit better','Almost there','Good enough','Strong','Super Strong','Incredibly Strong'];
	
	var score   = 0;
	var distinct_elements = 0;
	var errorMsg = document.getElementById('error');
	
	//if password bigger than 11 give 1 point
	if (password.length > 12) score=score+2;
	
	//if password bigger than 17 give another 1 point
	if (password.length > 17) score++;
	
	//if password bigger than 24 give another 1 point
	if (password.length > 24) score++;
	
	//split characters up into 4 classes. lowercase, uppercase, numbers and others.
	// if we have at least 2 of them then score a point. at least 3 then 2 points.
	if ( password.match(/[a-z]/)) distinct_elements++;
	if ( password.match(/[A-Z]/)) distinct_elements++;
	if ( password.match(/\d+/)) distinct_elements++;
	if ( password.match(/[^A-Za-z0-9]/)) distinct_elements++;
	
	if ( distinct_elements > 1 ) score++;
	if ( distinct_elements > 2 ) score++;
	
	if (score < 2) submitBtn.disabled='disabled';
	if (score > 1) submitBtn.disabled='';
	
	strengthMsg.innerHTML = strengths[score];
	//strengthMsg.innerHTML = score;
	strengthMsg.className = "strength" + score;

	//if (password.length < 11)
	//{
	//strengthMsg.innerHTML = "Password Should be Minimum 12 Characters"
	//strengthMsg.className = "errorclass"
	//}


   }
</script>

<?php // include anything else you want to put in <head> here.

include_once("inc/header3.php"); ?>



<h2>Initial Setup</h2>

<?php 
if (!empty($_POST)): 

  $domain = htmlspecialchars($_POST['community']);
  $comp_name = htmlspecialchars($_POST['initial_competition']);
  $comp_name_short = htmlspecialchars($_POST['initial_competition_abbreviation']);
  
  $uname = htmlspecialchars($_POST['admin_name']);
  $pwd = htmlspecialchars($_POST['password']);
  $pwd2 = htmlspecialchars($_POST['password2']);

  
  if (strlen($domain)<2) {$validation_failure_reasons[] = 'Community name too short. Must be at least 2 chars.';}
  if (strlen($domain)>100) {$validation_failure_reasons[] = 'Community name too long. Must be at most 100 chars.';}
  
  if (strlen($uname)<3) {$validation_failure_reasons[] = 'Username too short. Must be at least 3 chars.';}
  if (strlen($uname)>64) {$validation_failure_reasons[] = 'Username too long. Must be at most 64 chars.';}

  if (strlen($comp_name)<2) {$validation_failure_reasons[] = 'Competition name too short. Must be at least 2 chars.';}
  if (strlen($comp_name)>100) {$validation_failure_reasons[] = 'Competition name too long. Must be at most 100 chars.';}
  
  if (strlen($comp_name_short)<1) {$validation_failure_reasons[] = 'Competition abbreviated name cannot be zero length.';}
  if (strlen($comp_name_short)>6) {$validation_failure_reasons[] = 'Competition abbreviated name too long. Must be at most 6 chars.';}
  
  
  
  $check = preg_replace('/[A-Za-z0-9_]*/', '', $uname);

  if ($check!="") { $validation_failure_reasons[] = 'Username incorrect format. Can only contain A-Z, a-z, 0-9 and underscores.';	}

  if (strlen($pwd)<12) {$validation_failure_reasons[] = 'Password too short. Must be at least 12 chars.';}
  if (strlen($pwd)>70) {$validation_failure_reasons[] = 'Password too long. Maximum length is 70 chars.';}

  if ($pwd <> $pwd2) {$validation_failure_reasons[] = 'Passwords do not match. Try again!';}


  if (count($validation_failure_reasons)>0) {
    echo '<p>Your inputs have failed validation for the following reasons:</p><ul>';
    foreach($validation_failure_reasons AS $fail) {
      echo "<li>$fail</li>".PHP_EOL;
    }
    echo '</ul>';
    die;
  }

  $password_hash = password_hash(htmlspecialchars($pwd), PASSWORD_BCRYPT, ["cost" => 10]);
  
  // create domain
  $sql = $conn->prepare("INSERT INTO bb_domain (description) SELECT ?");
  $sql->execute(array($domain));
  $domain_id = $conn->lastInsertId();
  echo "<p>Community created successfully.</p>".PHP_EOL;
  
  // create default competition
  $sql = $conn->prepare("INSERT INTO bb_competition (domain_id, competition_type_id, description, completed, short_description, auto_enrol, tiebreaker_id) SELECT ?, 0, ?, 0, ?, 1, 1");
  $sql->execute(array($domain_id, $comp_name, $comp_name_short));
  echo "<p>Default competition created successfully.</p>".PHP_EOL;
  
  // create user
  $sql = $conn->prepare("INSERT INTO bb_user (username, pword_hash, default_domain_id) SELECT ?, ?, ?");
  $sql->execute(array($uname, $password_hash, $domain_id));
  $user_id = $conn->lastInsertId();
  echo "<p>Admin user created successfully.</p>".PHP_EOL;

  // give user admin rights
  $sql = $conn->prepare("INSERT INTO bb_user_role (user_id, role_id) SELECT ?, 1");
  $sql->execute(array($user_id));
  echo "<p>Admin user rights assigned successfully.</p>".PHP_EOL;
  
  
  // delete the file!
  unlink(__FILE__);
  
else :
?>
<p>Welcome to BBDB! There are just a few things that need doing in order to set everything up. You need an initial community name, competition name, and set up an admin logon.</p>


<form name="input_form" method="POST" action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]); ?>"> 
<table>
<tr><th>Parameter Name</th><th>Your value</th><th>Comments</th></tr>
<tr><td>Community</td><td><input name="community"></input></td><td>The name of the first gaming group you are setting up.</td></tr>

<tr><td>Initial Competition</td><td><input name="initial_competition"></input></td><td>The first competition. Recommend a group called "friendlies" where you can put your non-competative matches.</td></tr>
<tr><td>Competition Abbreviation</td><td><input name="initial_competition_abbreviation" size="10" maxlength="6"></input></td><td>Max 6 characters. Will be used on the website when space is tight.</td></tr>
<tr><td>Admin username</td><td><input name="admin_name"></input></td><td>BBDB requires a front-end administrator to be set up. Pick any username.</td></tr>

<tr><td>Admin password</td><td><input name="password" type="password" size="72" maxlength="72" onkeyup="checkPassword(this.value,document.getElementById('strength'),document.getElementById('submit'))"></input></td><td></td></tr>
<tr><td>Retype password</td><td><input name="password2" type="password" size="72" maxlength="72"></input></td><td></td></tr>

<tr><td>Password strength:</td><td id="strength">Moo</td><td></td></tr>

<tr><td></td>
<td><input type="submit" id="submit" disabled></td><td></td></tr>

</table>
</form>

<p>Passphrases must be <a href="http://blog.codinghorror.com/your-password-is-too-damn-short/">at least 12 characters long</a>, no more than 70 characters long and either...</p>
<ul><li>At least 18 characters long, or...</li>
<li>Contain characters from at least 2 of these sets.
<ul><li>Lowercase letters<//li>
<li>Uppercase letters</li>
<li>Numbers</li>
<li>Other characters, like @*&^%$£</li>
</li></ul>
<p>See <a href="https://xkcd.com/936/">this webcomic</a> for how passphrases can be easier to remember than passwords, and more secure. Struggling to think up a password? <a href="http://world.std.com/~reinhold/diceware.html">This is a bit techy</a> but might help.</p>


<p>When you submit this, the details will be set up, and in true secret-agent style, this file will self-destruct.</p>




<?php
endif; // end of the "if not self-posted" code

 include_once("inc/footer.php"); ?>
