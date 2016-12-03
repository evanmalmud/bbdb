
</head>

<body>

<?php include_once("menubar.php"); ?>

<p>
<?php if (in_array('ok',$_SESSION)) { echo "You are logged on as $username of " . $_SESSION['domain']; 
										echo ' (<a href="' . $base_url . 'logout.php">logout</a>)'; }
		else {echo "You are not logged on."; echo '<a href="' . $base_url . 'login.php">Login here</a>.';}
?>
</p>