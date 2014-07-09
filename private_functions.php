<?php

include "config.php";

function session_login($user) {
	$_SESSION['auth'] = 1;
	$_SESSION['user'] = $user;
}

function hash_pw($pw) {
	global $salt;
	return md5(md5(md5($pw.$salt)));
}

function user_link($user) {
	return '<a href="user.php?user='.$user.'">'.htmlentities($user).'</a>';
}

function html_output_head() {
	   echo '<script src="http://code.jquery.com/jquery-1.11.0.min.js"></script>';
	   echo '<script src="http://code.jquery.com/jquery-migrate-1.2.1.min.js"></script>';
	   echo '<link rel="stylesheet" href="//netdna.bootstrapcdn.com/bootstrap/3.1.1/css/bootstrap.min.css">';
	   echo '<link rel="stylesheet" href="//netdna.bootstrapcdn.com/bootstrap/3.1.1/css/bootstrap-theme.min.css">';
	   echo '<script src="//netdna.bootstrapcdn.com/bootstrap/3.1.1/js/bootstrap.min.js"></script>';
	   echo '<link rel="stylesheet" href="template.css">';
	   echo '<script src="my.js"></script>';
	   echo '<script src="api.js"></script>';
	   echo '<script src="generate.js"></script>';
}

function html_nav($page, $user = "") {
	if(isset($_SESSION['auth']) && $_SESSION['auth'] == 1) {
		if($page == "timeline") {
			$timelineActive = " class='active' ";
		} else if ($page == "search") {
			$searchActive = " class='active' ";
		} else if ($page == "findUsers") {
			$findUsersActive = " class='active' ";
		} else if ($page == "stats") {
			$statsActive = " class='active' ";
		}
		echo '
			<div class="header">
        <ul class="nav nav-pills pull-right">
          <li '.$timelineActive.'><a href="timeline.php">Timeline</a></li>
          <li '.$searchActive.'><a href="search.php">Search</a></li>
          <li '.$findUsersActive.'><a href="find_users.php">Find Users</a></li>
          <li '.$statsActive.'><a href="stats.php">Stats</a></li>
          <li><a href="logout.php">Logout</a></li>
        </ul>';

		if($user != "") {
			echo '<h3 class="text-muted">CMU Flitter : '.user_link($user).'</h3>';
		} else {
			echo '<h3 class="text-muted">CMU Flitter</h3>';
		}
      echo '</div>';

	} else {
		if($page == "login") {
			$loginActive = " class='active' ";
		} else if ($page == "register") {
			$registerActive = " class='active' ";
		}
		echo '
			<div class="header">
        <ul class="nav nav-pills pull-right">
          <li '.$loginActive.'><a href="login.php">Login</a></li>
          <li '.$registerActive.'><a href="register.php">Register</a></li>
        </ul>
        <h3 class="text-muted">CMU Flitter</h3>
      </div>';

	}
}

date_default_timezone_set('America/New_York');

function format_tweet_time($time) {
	return date('m/d/Y h:i:s', $time);
	//return $time;
}

function html_tweet($tweet) {

	echo '<div class="row tweet">
			<div class="col-md-3">'.user_link($tweet['username']).'</div>
			<div class="col-md-9">'.htmlentities($tweet['message']).'<span style="font-size:12px; color: #888; padding:5px; padding-left: 10px;">'.format_tweet_time($tweet['creation_time']).'</span></div>
		</div>';
}

function html_user($dbh, $user, $me = "") {
	$num_followees_res = get_num_followees($dbh, $user);
	$num_followers_res = get_num_followers($dbh, $user);
	$num_followees = $num_followees_res['count'];
	$num_followers = $num_followers_res['count'];

	if ( preg_match('/^[A-Za-z0-9]+$/', $user) ) {
		echo '
			<div class="row user" id="user-'.$user.'">
			<div class="col-md-4">'.user_link($user).'</div>
			<div class="col-md-3"><a href="followers.php?user='.htmlentities($user).'">'.$num_followers.' Followers</a></div>
			<div class="col-md-3"><a href="followees.php?user='.htmlentities($user).'">'.$num_followees.' Followees</a></div>';
		if ($me != "" && $me != $user) {                   
			if (!check_if_follows($dbh, $user, $me)) {
				echo '<div class="col-md-2"><button class="btn btn-lg btn-primary btn-block" type="button" onclick="follow(\''.$user.'\',this);">Follow</button></div>';
			} else {
				echo '<div class="col-md-2"><button class="btn btn-lg btn-primary btn-block" type="button" onclick="unfollow(\''.$user.'\',this);" >Unfollow</button></div>';
			}
		}
		echo '</div>';
	}

}

?>
