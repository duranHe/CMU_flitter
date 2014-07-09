<?php
session_start();
include "config.php";
include "private_functions.php";
include "functions.php";

if(!isset($_GET['user'])) {
	header('Location: '.$home.'login.php');
}

$dbh = db_connect($MY_HOST, $MY_DB_PORT, $MY_DB, $DB_USER, $DB_PW);
?>
<html>
	<head>
		<title>Flitter</title>
		<?php html_output_head(); ?>
	</head>
	<body>
 <div class="container">
		<?php html_nav('', $_SESSION['user']); ?>

<div class="row" style='border-bottom: 1px solid #ccc;'>
	<h3>Followees of <?php echo htmlentities($_GET['user']); ?> </h3>
</div>


<?php

$resp = get_followees($dbh, $_GET['user']);
if($resp['status'] == 1) {
	$users = $resp['users'];
	for($i = 0; $i < count($users); $i++) {
		html_user($dbh, $users[$i], $_SESSION['user']);  
		$num_results++;
	}
} else { 
	echo "There was  an error with your query";
}

?>
	</body>
</html>
