<?php

include "../config.php";
include "../functions.php";
include "../private_functions.php";

if(isset($_GET['user'])) {
	$dbh = db_connect($MY_HOST, $MY_DB_PORT, $MY_DB, $DB_USER, $DB_PW);
	$res = get_followees($dbh, $_GET['user']);
	close_db_connection($dbh);
	echo json_encode($res);
} else {
	echo json_encode(array("status" => -1));
}

?>
