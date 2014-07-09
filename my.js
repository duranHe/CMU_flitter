
function follow(user, btn) {
	var jqxhr = $.ajax( "api/follow.php?user="+user )
	.done(function(resp) {
		var json = $.parseJSON(resp);
		if(json['status'] == 1) {
			updateFollowRelationship(user);
			btn.innerHTML = "Unfollow";
			btn.onclick = (function(u, b) {
				return function() {
					unfollow(u, b);
				}
			})(user,btn);
		} else {
			alert("There was an error in your request.");
		}
	})
}


function unfollow(user, btn) {
	var jqxhr = $.ajax( "api/unfollow.php?user="+user )
	.done(function(resp) {
		var json = $.parseJSON(resp);
		if(json['status'] == 1) {
			updateFollowRelationship(user);
			btn.innerHTML = "Follow";
			btn.onclick = (function(u, b) {
				return function() {
					follow(u, b);
				}
			})(user,btn);
		} else {
			alert("There was an error in your request.");
		}
	})
}

function updateFollowRelationship(user) {
	var jqxhr = $.ajax( "api/get_num_followers.php?user="+user+"&r="+(new Date()).getTime() )
	.done(function(resp) {
		var json = $.parseJSON(resp);
		if(json['status'] == 1) {
			userRow = document.getElementById("user-"+user);
			//userRow.children[1].innerHTML = json['count'] + " Followers";
			userRow.children[1].innerHTML ='<a href="followers.php?user='+user+'">' + json['count'] + " Followers</a>";
		} 
	})

	var jqxhr2 = $.ajax( "api/get_num_followees.php?user="+user+"&r="+(new Date()).getTime() )
	.done(function(resp) {
		var json = $.parseJSON(resp);
		if(json['status'] == 1) {
			userRow = document.getElementById("user-"+user);
			userRow.children[2].innerHTML ='<a href="followees.php?user='+user+'">' + json['count'] + " Followees</a>";
		} 
	})
}
