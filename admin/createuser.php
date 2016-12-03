<?php include_once("../inc/header1.php");

// if you need to return header() statements, do them here. SQL connection has already been established.

ini_set('display_errors', 'On');
ini_set('html_errors', 0);
error_reporting(-1);

include_once("../inc/header2.php"); ?>

BBDB</title> <?php
if (!permission_check(5)) { // manually create users
	include_once("../inc/header3.php"); 
	echo '<h2>Page not found</h2>';
	echo '<p>Page not found, or you lack the necessary permissions.</p>';
	include_once("../inc/footer.php");
	die();
}
?>
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

include_once("../inc/header3.php"); 
include_once("../inc/stat_update.php");

if (!empty($_POST)): ?>
<h3>Adding user to database</h3>

<?php
$uname = htmlspecialchars($_POST['username']);
$pwd = htmlspecialchars($_POST['password']);
$pwd2 = htmlspecialchars($_POST['password2']);
$email = htmlspecialchars($_POST['email']);
$role_id = htmlspecialchars($_POST['role_id']);
$domain_id = (int) $_POST['domain_id'];
$validation_failure_reasons = array();

if (strlen($uname)<3) {$validation_failure_reasons[] = 'Username too short. Must be at least 3 chars.';}
if (strlen($uname)>64) {$validation_failure_reasons[] = 'Username too long. Must be at most 64 chars.';}


$check = preg_replace('/[A-Za-z0-9_]*/', '', $uname);

if ($check!="") { $validation_failure_reasons[] = 'Username incorrect format. Can only contain A-Z, a-z, 0-9 and underscores.';	}

if (strlen($pwd)<12) {$validation_failure_reasons[] = 'Password too short. Must be at least 12 chars.';}
if (strlen($pwd)>70) {$validation_failure_reasons[] = 'Password too long. Maximum length is 70 chars.';}

if ($pwd <> $pwd2) {$validation_failure_reasons[] = 'Passwords do not match. Try again!';}

if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
	$validation_failure_reasons[] = 'Entered email address not valid.';
}

if (count($validation_failure_reasons)>0) {
	echo '<p>Your inputs have failed validation for the following reasons:</p><ul>';
	foreach($validation_failure_reasons AS $fail) {
		echo "<li>$fail</li>".PHP_EOL;
	}
	echo '</ul>';
	die;
}

$password_hash = password_hash(htmlspecialchars($pwd), PASSWORD_BCRYPT, ["cost" => 10]); // this is "type 5"


// check that role_id is valid


$sql = $conn->prepare("INSERT INTO bb_user (username, pword_hash, default_domain_id) SELECT ?, ?, ?");
$sql->execute(array($uname, $password_hash, $domain_id));

$sql = $conn->prepare("SELECT user_id FROM bb_user WHERE username = ?");
$sql->execute(array($uname));
$uid = $sql->fetchColumn();

$sql = $conn->prepare("INSERT INTO bb_user_role (user_id, role_id, user_activation_status_id) SELECT ?, ?, 4");
$sql->execute(array($uid, $role_id));

echo "User successfully created!";

else: ?>
<h3>Add user to database</h3>

<form action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]); ?>" method="POST">
<table><tr><td>Username:</td>
<td><input name="username" size="64" maxlength="64"></td></tr>
<tr><td>Email:</td>
<td><input name="email" size="72" maxlength="255"></td></tr>
<tr><td>Passphrase:</td>
<td><input type="password" name="password" size="72" maxlength="72" onkeyup="checkPassword(this.value,document.getElementById('strength'),document.getElementById('submit'))"></td></tr>
<tr><td>Passphrase again:</td>
<td><input type="password" name="password2" size="72" maxlength="72"></td></tr>
<tr><td>Strength:</td>
<td id="strength">Moo</td></tr>
<tr><td>Role:</td>
<td><select name="role_id"><?php
$sql = $conn->prepare("SELECT role_id, description FROM bb_role ORDER BY description");
$sql->execute();

$dataset = $sql->fetchAll(PDO::FETCH_ASSOC);

foreach($dataset AS $row) {
	echo '<option value="';
	echo $row['role_id'];
	echo'">';
	echo $row['description'] . "</option>";
	echo PHP_EOL;
}
?>
</select></td></tr>
<tr><td>Community:</td>
<td><select name="domain_id"><?php
$sql = $conn->prepare("SELECT domain_id, description FROM bb_domain ORDER BY description");
$sql->execute();

$dataset = $sql->fetchAll(PDO::FETCH_ASSOC);

foreach($dataset AS $row) {
	echo '<option value="';
	echo $row['domain_id'];
	echo'">';
	echo $row['description'] . "</option>";
	echo PHP_EOL;
}
?>
</select></td></tr>
<tr><td></td>
<td><input type="submit" id="submit" disabled></td></tr>
</table>
</form>
<p>Passphrases must be <a href="http://blog.codinghorror.com/your-password-is-too-damn-short/">at least 12 characters long</a>, no more than 70 characters long and either...</p>
<ul><li>At least 18 characters long, or...</li>
<li>Contain characters from at least 2 of these sets.
<ul><li>Lowercase letters<//li>
<li>Uppercase letters</li>
<li>Numbers</li>
<li>Other characters, like @*&^%$£</li>
</li></ul></ul>
<p>See <a href="https://xkcd.com/936/">this webcomic</a> for how passphrases can be easier to remember than passwords, and more secure. Struggling to think up a password? <a href="http://world.std.com/~reinhold/diceware.html">This is a bit techy</a> but might help.</p>

<p><strong>Why do you need my email address?</strong> Three reasons - to check that you're a real person, for password resets, and rarely for really important news about BBDB. That's it. No spam, no selling-on of addresses, nothing like that.</p>
<? endif;


 include_once("../inc/footer.php"); ?>