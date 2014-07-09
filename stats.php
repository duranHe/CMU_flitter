<?php
session_start();
include "config.php";
include "private_functions.php";
include "functions.php";

$dbh = db_connect($MY_HOST, $MY_DB_PORT, $MY_DB, $DB_USER, $DB_PW);
?>
<html>
	<head>
		<title>Flitter</title>
		<?php html_output_head(); ?>
	</head>
	<body>
 <div class="container">
		<?php html_nav('stats', $_SESSION['user']); ?>

	  <div class="row" style='border-bottom: 1px solid #000; margin-bottom: 10px;'>
		  <div class="row" style='padding-left: 10px; padding-right: 10px; padding-top: 5px;'>
				<div class="col-md-10"><h2>Most Active Users</h2></div>
		  </div>
		</div>
<?php
$resp = get_most_active_users($dbh, 5);
if($resp['status'] == 1) {
	$users = $resp['users'];
	for($i = 0; $i < count($users); $i++) {
		html_user($dbh, $users[$i], $_SESSION['user']);  
		$num_results++;
	}
}

?>
	  <div class="row" style='border-bottom: 1px solid #000; margin-bottom: 10px; padding-top:20px;'>
		  <div class="row" style='padding-left: 10px; padding-right: 10px; padding-top: 5px;'>
				<div class="col-md-10"><h2>Most Popular Users</h2></div>
		  </div>
		</div>
<?php
$resp = get_most_popular_users($dbh, 5);
if($resp['status'] == 1) {
	$users = $resp['users'];
	for($i = 0; $i < count($users); $i++) {
		html_user($dbh, $users[$i], $_SESSION['user']);  
		$num_results++;
	}
}

?>
	  <div class="row" style='border-bottom: 1px solid #000; margin-bottom: 10px; padding-top:20px;'>
		  <div class="row" style='padding-left: 10px; padding-right: 10px; padding-top: 5px;'>
				<div class="col-md-10"><h2>Most Connected Users</h2></div>
		  </div>
		</div>
<?php
$resp = get_hub_users($dbh, 5);
if($resp['status'] == 1) {
	$users = $resp['users'];
	for($i = 0; $i < count($users); $i++) {
		html_user($dbh, $users[$i], $_SESSION['user']);  
		$num_results++;
	}
}

?>
	</body>
</html>
