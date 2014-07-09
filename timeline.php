<?php
session_start();
include "config.php";
include "private_functions.php";
include "functions.php";


if(!isset($_SESSION['auth']) || $_SESSION['auth'] == 0) {
	header('Location: '.$home.'login.php');
}

if(isset($_POST['message'])) {
	$dbh = db_connect($MY_HOST, $MY_DB_PORT, $MY_DB, $DB_USER, $DB_PW);
	$res = post_tweet($dbh, $_POST['message'],$_SESSION['user']);
	if($res['status'] == 0) {
		$err_msg = "<h4 style='text-align:center;'>Error posting message</h4>";
	}
}

?>
<html>
	<head>
		<title>Flitter</title>
		<?php html_output_head(); ?>
	</head>
	<body>
 <div class="container">
		<?php html_nav('timeline', $_SESSION['user']); ?>
	  <div class="row" style='border-bottom: 1px solid #ccc;'>
<?php echo $err_msg; ?>
		<form action='timeline.php' method='POST'>
	  <div class="row" style='padding-left: 30px; padding-right: 30px'>
			<textarea class="form-control" rows="4" name="message" placeholder="What's up? Type a message!"></textarea>
	  </div>
	  <div class="row" style='padding-left: 30px; padding-right: 30px; padding-top: 5px;'>
			<div class="col-md-10"></div>
			<div class="col-md-2"> <button class="btn btn-lg btn-primary btn-block" type="submit">Post</button> </div>
	  </div>
	  </form>
		</div>
<?php
$num_output = 0;
$dbh = db_connect($MY_HOST, $MY_DB_PORT, $MY_DB, $DB_USER, $DB_PW);
if(isset($_GET['start'])) {
	$timeline = get_timeline($dbh, $_SESSION['user'], 15, $_GET['start']);
} else {
	$timeline = get_timeline($dbh, $_SESSION['user'], 15);
}
$last_time = -1;
if($timeline['status'] == 1) {
	$tweets= $timeline['tweets'];
	for($i = 0; $i < count($tweets); $i++) {
		html_tweet($tweets[$i]);
		$num_output++;
		$last_time = $tweets[$i]['creation_time'];
	}
}

if($num_output == 0) {
	echo "<div class='row' style='font-size: 21px; padding-left: 30px; padding-right: 30px; padding-top: 5px;'> There appears to be no tweets here.</div>";
} else if ($last_time > 0) {
	$timeline = get_timeline($dbh, $_SESSION['user'], 15, $last_time);
	if($timeline['status'] == 1) {
		if(count($timeline['tweets']) > 0) {
			echo "<div class='row' style='text-align: center; font-size: 21px; padding-left: 30px; padding-right: 30px; margin-top: 35px;'> <a href='timeline.php?start=".$last_time."'>More</a></div>";
		}
	}
}

?>
	</body>
</html>
