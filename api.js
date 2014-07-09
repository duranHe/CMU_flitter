var apiTools = {
	evaluateResp: function (resp, expected, fn, sent_args) {
		fn = fn.substring(fn.indexOf("api/")+4, fn.indexOf(".php"));
		sent_args = Array.prototype.slice.call(sent_args);
		sent_args.pop();
		if (expected) {
			if(resp != expected) {
				console.log("\nERROR Response for " + fn);
				console.log("Arguments were: " + sent_args);
				console.log("Expected: " + expected);
				console.log("Received " + resp + "\n");
			} else {
				console.log("Correct response for " + fn + ' with arguments ' + sent_args);
			}
		} else {
			console.log(resp);
		}
	},

	evaluateTweets: function (resp, expected, fn, sent_args) {
		fn = fn.substring(fn.indexOf("api/")+4, fn.indexOf(".php"));
		sent_args = Array.prototype.slice.call(sent_args);
		sent_args.pop();
		if (expected) {
			var respObj = $.parseJSON(resp);
			var expectedObj = $.parseJSON(expected);

			var match = true;
			var msg = "";
			if (expectedObj['status'] == 1) {
				if ( expectedObj['tweets'].length == respObj['tweets'].length ) {
					for(var i = 0; i < expectedObj['tweets'].length; i++) {
						var tw1 = expectedObj['tweets'][i];
						var tw2 = respObj['tweets'][i];
						if (tw1['username'] != tw2['username'] || tw1['message'] != tw2['message'] || !tw2['creation_time']) {
							match = false;
							msg = "Mismatch in tweet details.";
							break;
						}
					}
				}  else {
					match = false;
					msg = "Incorrect number of tweets";
				}
			} else {
				match = (expectedObj['status'] == respObj['status']);
				msg = 'v2';
			}

			if(!match) {
				console.log("\nERROR Response for " + fn);
				console.log("Arguments were: " + sent_args);
				console.log("Expected: " + expected);
				console.log("Received " + resp);
				console.log(msg + "\n");
			} else {
				console.log("Correct response for " + fn + ' with arguments ' + sent_args);
			}
		} else {
			console.log(resp);
		}
	},

	login: function (user, pw, expectedResponse) {
		fn = arguments.callee.toString().substring(0, arguments.callee.toString().indexOf("("));
		my_args = arguments;
		var jqxhr = $.ajax( "api/login.php?username="+user+"&pw="+encodeURIComponent(pw), { async : false} )
		.done(function(resp) {
			apiTools.evaluateResp(resp,expectedResponse,this.url,my_args);
		})
		.fail(function() {
			console.log("The call to " + fn + " failed.");
		})
	},

	logout: function (expectedResponse) {
		fn = arguments.callee.toString().substring(0, arguments.callee.toString().indexOf("("));
		my_args = arguments;
		var jqxhr = $.ajax( "api/logout.php", { async : false})
		.done(function(resp) {
			apiTools.evaluateResp(resp,expectedResponse,this.url,my_args);
		})
		.fail(function() {
			console.log("The call to " + fn + " failed.");
		})
	},

	register: function (user, pw, expectedResponse) {
		fn = arguments.callee.toString().substring(0, arguments.callee.toString().indexOf("("));
		my_args = arguments;
		var jqxhr = $.ajax( "api/register.php?username="+user+"&pw="+encodeURIComponent(pw), { async : false} )
		.done(function(resp) {
			apiTools.evaluateResp(resp,expectedResponse,this.url,my_args);
		})
		.fail(function() {
			console.log("The call to " + fn + " failed.");
		})
	},

	post: function (flit, expectedResponse) {
		fn = arguments.callee.toString().substring(0, arguments.callee.toString().indexOf("("));
		my_args = arguments;
		var jqxhr = $.ajax( "api/post.php?flit="+encodeURIComponent(flit), { async : false})
		.done(function(resp) {
			apiTools.evaluateResp(resp,expectedResponse,this.url,my_args);
		})
		.fail(function() {
			console.log("The call to " + fn + " failed.");
		})
	},

	timeline: function (num, start, expectedResponse) {
		fn = arguments.callee.toString().substring(0, arguments.callee.toString().indexOf("("));
		my_args = arguments;
		if(num < 0) num = 10;
		if(start < 0) start = (new Date()).getTime() + (1000*60*60*24*365*10);
		var jqxhr = $.ajax( "api/timeline.php?count="+num+"&start_time="+start, { async : false})
		.done(function(resp) {
			apiTools.evaluateTweets(resp,expectedResponse,this.url,my_args);
		})
		.fail(function() {
			console.log("The call to " + fn + " failed.");
		})
	},

	user_tweets: function (user, num, start, expectedResponse) {
		fn = arguments.callee.toString().substring(0, arguments.callee.toString().indexOf("("));
		my_args = arguments;
		if(num < 0) num = 10;
		if(start < 0) start = (new Date()).getTime() + (1000*60*60*24*365*10);
		var jqxhr = $.ajax( "api/user_tweets.php?user="+user+"&count="+num+"&start_time="+start, { async : false})
		.done(function(resp) {
			apiTools.evaluateTweets(resp,expectedResponse,this.url,my_args);
		})
		.fail(function() {
			console.log("The call to " + fn + " failed.");
		})
	},

	user_tweets_paginated: function (user, num, expectedResponse2) {
		fn = arguments.callee.toString().substring(0, arguments.callee.toString().indexOf("("));
		my_args = arguments;
		if(num < 0) num = 5;
		var jqxhr = $.ajax( "api/user_tweets.php?user="+user+"&count="+num, { async : false})
		.done(function(resp) {
			var respObj = $.parseJSON(resp);
			var tweets = respObj['tweets'];
			var last_time = tweets[tweets.length-1]['creation_time'];

			var jqxhr2 = $.ajax( "api/user_tweets.php?user="+user+"&count="+num+"&start_time="+last_time, { async : false})
			.done(function(resp) {
				apiTools.evaluateTweets(resp,expectedResponse2,this.url,my_args);
			})


		})
		.fail(function() {
			console.log("The call to " + fn + " failed.");
		})
	},

	follow_user: function (user, expectedResponse) {
		fn = arguments.callee.toString().substring(0, arguments.callee.toString().indexOf("("));
		my_args = arguments;
		var jqxhr = $.ajax( "api/follow.php?user="+user, { async : false})
		.done(function(resp) {
			apiTools.evaluateResp(resp,expectedResponse,this.url,my_args);
		})
		.fail(function() {
			console.log("The call to " + fn + " failed.");
		})
	},

	unfollow_user: function (user, expectedResponse) {
		fn = arguments.callee.toString().substring(0, arguments.callee.toString().indexOf("("));
		my_args = arguments;
		var jqxhr = $.ajax( "api/unfollow.php?user="+user, { async : false})
		.done(function(resp) {
			apiTools.evaluateResp(resp,expectedResponse,this.url,my_args);
		})
		.fail(function() {
			console.log("The call to " + fn + " failed.");
		})
	},

	check_if_follows: function (user, expectedResponse) {
		fn = arguments.callee.toString().substring(0, arguments.callee.toString().indexOf("("));
		my_args = arguments;
		var jqxhr = $.ajax( "api/check_if_follows.php?user="+user, { async : false})
		.done(function(resp) {
			apiTools.evaluateResp(resp,expectedResponse,this.url,my_args);
		})
		.fail(function() {
			console.log("The call to " + fn + " failed.");
		})
	},

	get_followers: function (user, expectedResponse) {
		fn = arguments.callee.toString().substring(0, arguments.callee.toString().indexOf("("));
		my_args = arguments;
		var jqxhr = $.ajax( "api/get_followers.php?user="+user, { async : false})
		.done(function(resp) {
			apiTools.evaluateResp(resp,expectedResponse,this.url,my_args);
		})
		.fail(function() {
			console.log("The call to " + fn + " failed.");
		})
	},

	get_followees: function (user, expectedResponse) {
		fn = arguments.callee.toString().substring(0, arguments.callee.toString().indexOf("("));
		my_args = arguments;
		var jqxhr = $.ajax( "api/get_followees.php?user="+user, { async : false})
		.done(function(resp) {
			apiTools.evaluateResp(resp,expectedResponse,this.url,my_args);
		})
		.fail(function() {
			console.log("The call to " + fn + " failed.");
		})
	},

	get_recommended: function (expectedResponse) {
		fn = arguments.callee.toString().substring(0, arguments.callee.toString().indexOf("("));
		my_args = arguments;
		var jqxhr = $.ajax( "api/recommend_users.php", { async : false})
		.done(function(resp) {
			apiTools.evaluateResp(resp,expectedResponse,this.url,my_args);
		})
		.fail(function() {
			console.log("The call to " + fn + " failed.");
		})
	},

	search: function (keyword, num, expectedResponse) {
		fn = arguments.callee.toString().substring(0, arguments.callee.toString().indexOf("("));
		my_args = arguments;
		if(num < 0) num = 10;
		var jqxhr = $.ajax( "api/search.php?count="+num+"&keyword="+encodeURIComponent(keyword), { async : false})
		.done(function(resp) {
			//apiTools.evaluateResp(resp,expectedResponse,this.url,my_args);
			apiTools.evaluateTweets(resp,expectedResponse,this.url,my_args);
		})
		.fail(function() {
			console.log("The call to " + fn + " failed.");
		})
	},

	user_search: function (name, expectedResponse) {
		fn = arguments.callee.toString().substring(0, arguments.callee.toString().indexOf("("));
		my_args = arguments;
		var jqxhr = $.ajax( "api/user_search.php?username="+name, { async : false})
		.done(function(resp) {
			apiTools.evaluateResp(resp,expectedResponse,this.url,my_args);
		})
		.fail(function() {
			console.log("The call to " + fn + " failed.");
		})
	},

	get_num_followers: function (user, expectedResponse) {
		fn = arguments.callee.toString().substring(0, arguments.callee.toString().indexOf("("));
		my_args = arguments;
		var jqxhr = $.ajax( "api/get_num_followers.php?user="+user, { async : false})
		.done(function(resp) {
			apiTools.evaluateResp(resp,expectedResponse,this.url,my_args);
		})
		.fail(function() {
			console.log("The call to " + fn + " failed.");
		})
	},

	get_num_followees: function (user, expectedResponse) {
		fn = arguments.callee.toString().substring(0, arguments.callee.toString().indexOf("("));
		my_args = arguments;
		var jqxhr = $.ajax( "api/get_num_followees.php?user="+user, { async : false})
		.done(function(resp) {
			apiTools.evaluateResp(resp,expectedResponse,this.url,my_args);
		})
		.fail(function() {
			console.log("The call to " + fn + " failed.");
		})
	},

	get_most_active: function (num, expectedResponse) {
		fn = arguments.callee.toString().substring(0, arguments.callee.toString().indexOf("("));
		my_args = arguments;
		if(num < 0) num = 10;
		var jqxhr = $.ajax( "api/most_active_users.php?count="+num, { async : false})
		.done(function(resp) {
			apiTools.evaluateResp(resp,expectedResponse,this.url,my_args);
		})
		.fail(function() {
			console.log("The call to " + fn + " failed.");
		})
	},

	get_most_popular: function (num, expectedResponse) {
		fn = arguments.callee.toString().substring(0, arguments.callee.toString().indexOf("("));
		my_args = arguments;
		if(num < 0) num = 10;
		var jqxhr = $.ajax("api/most_popular_users.php?count="+num, { async : false})
		.done(function(resp) {
			apiTools.evaluateResp(resp,expectedResponse,this.url,my_args);
		})
		.fail(function() {
			console.log("The call to " + fn + " failed.");
		})
	},

	get_hub_users: function (num, expectedResponse) {
		fn = arguments.callee.toString().substring(0, arguments.callee.toString().indexOf("("));
		my_args = arguments;
		if(num < 0) num = 10;
		var jqxhr = $.ajax( "api/get_hub_users.php?count="+num, { async : false})
		.done(function(resp) {
			apiTools.evaluateResp(resp,expectedResponse,this.url,my_args);
		})
		.fail(function() {
			console.log("The call to " + fn + " failed.");
		})
	},

	reset: function (secret,expectedResponse) {
		fn = arguments.callee.toString().substring(0, arguments.callee.toString().indexOf("("));
		my_args = arguments;
		var jqxhr = $.ajax( "api/reset.php?secret="+secret, { async : false})
		.done(function(resp) {
			apiTools.evaluateResp(resp,expectedResponse,this.url,my_args);
		})
		.fail(function() {
			console.log("The call to " + fn + " failed.");
		})
	}
}
