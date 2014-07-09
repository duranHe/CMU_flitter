<?php

session_start();
include "../config.php";
include "../functions.php";
include "../private_functions.php";

if(isset($_SESSION['auth']) && $_SESSION['auth'] == 1 && isset($_GET['user'])) {
	$dbh = db_connect($MY_HOST, $MY_DB_PORT, $MY_DB, $DB_USER, $DB_PW);
	$res = check_if_follows($dbh, $_GET['user'], $_SESSION['user']);
	close_db_connection($dbh);
	if($res) {
		echo json_encode(array("status" => 1));
	} else {
		echo json_encode(array("status" => 0));
	}
} else {
	echo json_encode(array("status" => -1));
}

?>
