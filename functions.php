<?php

include "config.php";

/*
 * For all functions $dbh is a database connection
 */

/*
 * @return handle to database connection
 */
function db_connect($host, $port, $db, $user, $pw) {
	$db_conn = pg_connect("host=$host port=$port dbname=$db user=$user password=$pw");
	return $db_conn;;
}

/*
 * Close database connection
 */ 
function close_db_connection($dbh) {
	pg_close($dbh);
}

/*
 * Login if user and password match
 * Return associative array of the form:
 * array(
 *		'status' =>   (1 for success and 0 for failure)
 *		'userID' => '[USER ID]'
 * )
 */
function login($dbh, $user, $pw) {
	$user = pg_escape_string($user);
	$pw = pg_escape_string($pw);

	$query = "select * from myuser where username = '$user' and password = '$pw'";
	$result = pg_query($dbh, $query);
	if($result)
	{
		while($row = pg_fetch_row($result))
		{
			$ret = array('status' => 1, 'userID' => $row[0]);
			return $ret;
		}
	}
	else
	{
		$ret = array('status' => 0, 'userID' => NULL);
		return $ret;
	}
}

/*
 * Register user with given password 
 * Return associative array of the form:
 * array(
 *		'status' =>   (1 for success and 0 for failure)
 *		'userID' => '[USER ID]'
 * )
 */
function register($dbh, $user, $pw) {
	$user = pg_escape_string($user);
	$pw = pg_escape_string($pw);
	
	$query = "insert into myuser(username, password) values ('$user', '$pw')";
	$result = pg_query($dbh, $query);
	if($result)
	{
		$ret = array('status' => 1, 'userID' => $user);
		return $ret;
	}
	else
	{
		$ret = array('status' => 0, 'userID' => NULL);
		return $ret;
	}
}

/*
 * Register user with given password 
 * Return associative array of the form:
 * array(
 *		'status' =>   (1 for success and 0 for failure)
 * )
 */
function post_tweet($dbh, $msg, $me) {
	$msg = pg_escape_string($msg);
	$me = pg_escape_string($me);
	
	$timestamp = time();
	$query = "insert into tweet(username, content, timestamp) values ('$me', '$msg', $timestamp)";
	$result = pg_query($dbh, $query);
	if($result)
	{
		$ret = array('status' => 1);
		return $ret;
	}
	else
	{
		$ret = array('status' => 0);
		return $ret;
	}
}


/*
 * Get timeline of $count most recent tweets that were written before timestamp $start
 * For a user $user, the timeline should include tweets from all people that user follows as well as himself. 
 * Order by time of the tweet (going backward in time), and break ties by sorting by the username alphabetically
 * Return associative array of the form:
 * array(
 *		'status' =>   (1 for success and 0 for failure)
 *		'tweets' => [ (Array of tweet objects) ]
 * )
 * Each tweet should be of the form:
 * array(
 *		'username' => (USERNAME)
 *		'message' => (TWEET CONTENT)
 *		'creation_time' => (TIMESTAMP AS UNIXTIME)
 * )
 */
function get_timeline($dbh, $user, $count = 10, $start = PHP_INT_MAX) {
	$user = pg_escape_string($user);
	
	$query = "select username, content, timestamp from (select * from tweet where username = '$user' union all select tid, username, content, timestamp from tweet, follow where follower = '$user' and followee = username) as temp where timestamp < $start order by timestamp desc, username asc limit $count";
	$result = pg_query($dbh, $query);
	if($result)
	{
		$tweets = array();
		while($row = pg_fetch_row($result))
		{
			$onetweet = array('username' => $row[0], 'message' => $row[1], 'creation_time' => $row[2]);
			array_push($tweets, $onetweet);
		}
		$ret = array('status' => 1, 'tweets' => $tweets);
		return $ret;
	}
	else
	{
		$ret = array('status' => 0, 'tweets' => NULL);
		return $ret;
	}
}

/*
 * Get list of $count most recent tweets that were written by user $user before timestamp $start
 * Order by time of the tweet (going backward in time)
 * Return associative array of the form:
 * array(
 *		'status' =>   (1 for success and 0 for failure)
 *		'tweets' => [ (Array of tweet objects) ]
 * )
 * Each tweet should be of the form:
 * array(
 *		'username' => (USERNAME)
 *		'message' => (TWEET CONTENT)
 *		'creation_time' => (TIMESTAMP AS UNIXTIME)
 * )
 */
function get_user_tweets($dbh, $user, $count = 10, $start = PHP_INT_MAX) {
	$user = pg_escape_string($user);
	
	$query = "select * from tweet where username = '$user' and timestamp < $start order by timestamp desc limit $count";
	$result = pg_query($dbh, $query);	
	if($result)
	{
		$tweets = array();
		while($row = pg_fetch_row($result))
		{
			$onetweet = array('username' => $user, 'message' => $row[2], 'creation_time' => $row[3]);
			array_push($tweets, $onetweet);
		}
		$ret = array('status' => 1, 'tweets' => $tweets);
		return $ret;
	}
	else
	{
		$ret = array('status' => 0, 'tweets' => NULL);
		return $ret;
	}
}

/*
 * Make user $me follow user $user
 * Return associative array of the form:
 * array(
 *		'status' => 1  (For success)
 *		'status' => 0  (For failure)
 * )
 */
function follow_user($dbh, $user, $me) {
	$user = pg_escape_string($user);
	$me = pg_escape_string($me);
	
	if(strcmp($user, $me) == 0)
	{
		$ret = array('status' => 0);
		return $ret;
	}
	$query = "insert into follow values ('$user', '$me')";
	$result = pg_query($dbh, $query);
	if($result)
	{
		$ret = array('status' => 1);
		return $ret;
	}
	else
	{
		$ret = array('status' => 0);
		return $ret;
	}
}

/*
 * Make user $me unfollow user $user
 * Return associative array of the form:
 * array(
 *		'status' => 1  (For success)
 *		'status' => 0  (For failure)
 *		'status' => 2  (If the $me wasn't previously following $user)
 * )
 */
function unfollow_user($dbh, $user, $me) {
	$user = pg_escape_string($user);
	$me = pg_escape_string($me);
	
	$query = "select count(*) from follow where followee = '$user' and follower = '$me'";
	$result = pg_query($dbh, $query);
	if($result)
	{
		while($row = pg_fetch_row($result))
		{
			if($row[0])
			{
				$query2 = "delete from follow where followee = '$user' and follower = '$me'";
				$result2 = pg_query($dbh, $query2);
				if($result2)
				{
					$ret = array('status' => 1);
					return $ret;
				}
				else
				{
					$ret = array('status' => 0);
					return $ret;
				}
			}
			else
			{
				$ret = array('status' => 2);
				return $ret;
			}
		}
		$ret = array('status' => 0);
		return $ret;
	}
	else
	{
		$ret = array('status' => 0);
		return $ret;
	}
}

/*
 * Check if user $me follows user $user
 * Return true if user $me follows user $user or false otherwise
 */
function check_if_follows($dbh, $user, $me) {
	$user = pg_escape_string($user);
	$me = pg_escape_string($me);
	
	$query = "select count(*) from follow where followee = '$user' and follower = '$me'";
	$result = pg_query($dbh, $query);
	if($result)
	{
		while($row = pg_fetch_row($result))
		{
			if($row[0])
			{
				return true;
			}
			else
			{
				return false;
			}
		}
	}
	else
	{
		return false;
	}
}

/*
 * Get the followers of user $user
 * Order by the username of the followers
 * Return associative array of the form:
 * array(
 *		'status' =>   (1 for success and 0 for failure)
 *		'users' => [ (Array of user IDs) ]
 * )
 */
function get_followers($dbh, $user) {
	$user = pg_escape_string($user);
	
	$query = "select follower from follow where followee = '$user' order by follower";
	$result = pg_query($dbh, $query);
	if($result)
	{
		$users = array();
		while($row = pg_fetch_row($result))
		{
			array_push($users, $row[0]);	
		}
		$ret = array('status' => 1, 'users' => $users);
		return $ret;
	}
	else
	{
		$ret = array('status' => 0, 'users' => NULL);
		return $ret;
	}
}

/*
 * Get the followees of user $user
 * Order by the username of the followees
 * Return associative array of the form:
 * array(
 *		'status' =>   (1 for success and 0 for failure)
 *		'users' => [ (Array of user IDs) ]
 * )
 */
function get_followees($dbh, $user) {
	$user = pg_escape_string($user);
	
	$query = "select followee from follow where follower = '$user' order by followee";
	$result = pg_query($dbh, $query);
	if($result)
	{
		$users = array();
		while($row = pg_fetch_row($result))
		{
			array_push($users, $row[0]);	
		}
		$ret = array('status' => 1, 'users' => $users);
		return $ret;
	}
	else
	{
		$ret = array('status' => 0, 'users' => NULL);
		return $ret;
	}
}

/*
 * Recommend people for user $me to follow
 * A person $user2 is a good candidate for $user to follow if one or more of $user's followees follow $user2
 * Rank the recommended users by how many of $user's followees follow the recommended user, and then order by username
 * Do not include users who $user already follows
 * Return associative array of the form:
 * array(
 *		'status' =>   (1 for success and 0 for failure)
 *		'users' => [ (Array of user IDs) ]
 * )
 */
function recommend_followees($dbh, $me) {
	$me = pg_escape_string($me);

	$query = "select f2.followee from myuser, follow as f1, follow as f2 where f2.followee <> '$me' and myuser.username = '$me' and f1.follower = '$me' and f1.followee = f2.follower and f2.followee not in (select followee from follow where follower = '$me') group by f2.followee order by count(*) desc, f2.followee asc";
	$result = pg_query($dbh, $query);
	if($result)
	{
		$users = array();
		while($row = pg_fetch_row($result))
		{
			array_push($users, $row[0]);
		}
		$ret = array('status' => 1, 'users' => $users);
		return $ret;
	}
	else
	{
		$ret = array('status' => 0, 'users' => NULL);
		return $ret;
	}
}

/*
 * Find the $count most recent tweets that contain the string $key
 * Order by time of the tweet and break ties by the username (sorted alphabetically A-Z)
 * Return associative array of the form:
 * array(
 *		'status' =>   (1 for success and 0 for failure)
 *		'tweets' => [ (Array of Tweet objects) ]
 * )
 */
function search($dbh, $key, $count = 50) {
	$key = pg_escape_string($key);

	$query = "select * from tweet where content like '%$key%' order by timestamp desc, username asc limit $count";
	$result = pg_query($dbh, $query);
	if($result)
	{
		$tweets = array();
		while($row = pg_fetch_row($result))
		{
			$onetweet = array('username' => $row[1], 'message' => $row[2], 'creation_time' => $row[3]);
			array_push($tweets, $onetweet);
		}
		$ret = array('status' => 1, 'tweets' => $tweets);
		return $ret;
	}
	else
	{
		$ret = array('status' => 0, 'tweets' => NULL);
		return $ret;
	}
}

/*
 * Find all users whose username includes the string $name
 * Sort the users alphabetically (A-Z)
 * Return associative array of the form:
 * array(
 *		'status' =>   (1 for success and 0 for failure)
 *		'users' => [ (Array of user IDs) ]
 * )
 */
function user_search($dbh, $name) {
	$name = pg_escape_string($name);

	$query = "select username from myuser where username like '%$name%' order by username asc";
	$result = pg_query($dbh, $query);
	if($result)
	{
		$users = array();
		while($row = pg_fetch_row($result))
		{
			array_push($users, $row[0]);
		}
		$ret = array('status' => 1, 'users' => $users);
		return $ret;
	}
	else
	{
		$ret = array('status' => 0, 'users' => NULL);
		return $ret;
	}
}


/*
 * Get the number of followers of $user
 * Return associative array of the form:
 * array(
 *		'status' =>   (1 for success and 0 for failure)
 *		'count' => (The number of followers)
 * )
 */
function get_num_followers($dbh, $user) {
	$user = pg_escape_string($user);

	$query = "select count(*) from follow where followee = '$user'";
	$result = pg_query($dbh, $query);
	if($result)
	{
		while($row = pg_fetch_row($result))
		{
			$ret = array('status' => 1, 'count' => $row[0]);
			return $ret;
		}
	}
	else
	{
		$ret = array('status' => 0, 'count' => NULL);
	}
}

/*
 * Get the number of followees of $user
 * Return associative array of the form:
 * array(
 *		'status' =>   (1 for success and 0 for failure)
 *		'count' => (The number of followees)
 * )
 */
function get_num_followees($dbh, $user) {
	$user = pg_escape_string($user);

	$query = "select count(*) from follow where follower = '$user'";
	$result = pg_query($dbh, $query);
	if($result)
	{
		while($row = pg_fetch_row($result))
		{
			$ret = array('status' => 1, 'count' => $row[0]);
			return $ret;
		}
	}
	else
	{
		$ret = array('status' => 0, 'count' => NULL);
	}
}

/*
 * Get the list of $count users that have posted the most tweets
 * Order by the number of tweets (descending), and then by username (A-Z)
 * Return associative array of the form:
 * array(
 *		'status' =>   (1 for success and 0 for failure)
 *		'users' => [ (Array of user IDs) ]
 * )
 */
function get_most_active_users($dbh, $count = 10) {
	$query = "select username from tweet group by username order by count(*) desc, username asc limit $count";
	$result = pg_query($dbh, $query);
	if($result)
	{
		$users = array();
		while($row = pg_fetch_row($result))
		{
			array_push($users, $row[0]);	
		}
		$ret = array('status' => 1, 'users' => $users);
		return $ret;
	}
	else
	{
		$ret = array('status' => 0, 'users' => NULL);
		return $ret;
	}
}

/*
 * Get the list of $count users that have the most followers
 * Order by the number of followees (descending), and then by username (A-Z)
 * Return associative array of the form:
 * array(
 *		'status' =>   (1 for success and 0 for failure)
 *		'users' => [ (Array of user IDs) ]
 * )
 */
function get_most_popular_users($dbh, $count = 10) {
	$query = "select followee from follow group by followee order by count(*) desc, followee asc limit $count";
	$result = pg_query($dbh, $query);
	if($result)
	{
		$users = array();
		while($row = pg_fetch_row($result))
		{
			array_push($users, $row[0]);	
		}
		$ret = array('status' => 1, 'users' => $users);
		return $ret;
	}
	else
	{
		$ret = array('status' => 0, 'users' => NULL);
		return $ret;
	}
}

/*
 * Get the list of $count users that have the most followers + followees
 * Order by the number of followers and followees (descending), and then by username (A-Z)
 * Return associative array of the form:
 * array(
 *		'status' =>   (1 for success and 0 for failure)
 *		'users' => [ (Array of user IDs) ]
 * )
 */
function get_hub_users($dbh, $count = 10) {
	$query = "select username from (select follower as username from follow union all select followee as username from follow) as temp group by username order by count(*) desc, username asc limit $count";
	$result = pg_query($dbh, $query);
	if($result)
	{
		$users = array();
		while($row = pg_fetch_row($result))
		{
			array_push($users, $row[0]);	
		}
		$ret = array('status' => 1, 'users' => $users);
		return $ret;
	}
	else
	{
		$ret = array('status' => 0, 'users' => NULL);
		return $ret;
	}
}


/*
 * Delete all tables in the database and then recreate them (without any data)
 * Return associative array of the form:
 * array(
 *		'status' =>   (1 for success and 0 for failure)
 * )
 */
function reset_database($dbh) {
	$query = "drop table tweet, follow, myuser";
	$result = pg_query($dbh, $query);
	$flag = true;
	if(!$result)
	{
		$flag = false;
	}

	$query = "create table myuser (username varchar(50), password varchar(50), primary key(username))";
	$result = pg_query($dbh, $query);
	if(!$result)
	{
		$flag = false;
	}

	$query = "create table follow (followee varchar(50), follower varchar(50), foreign key(followee) references myuser(username), foreign key(follower) references myuser(username), primary key(followee, follower))";
	$result = pg_query($dbh, $query);
	if(!$result)
	{
		$flag = false;
	}

	$query = "create table tweet(tid serial, username varchar(50), content varchar(140), timestamp bigint, primary key(tid), foreign key(username) references myuser(username))";
	$result = pg_query($dbh, $query);
	if(!$result)
	{
		$flag = false;
	}

	if($flag)
	{
		$ret = array('status' => 1);
		return $ret;
	}
	else
	{
		$ret = array('status' => 0);
		return $ret;
	}
}

?>
