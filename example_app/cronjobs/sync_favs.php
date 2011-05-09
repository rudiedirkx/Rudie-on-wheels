<?php

// no namespace

// This cronjob is very inefficient: many queries and objects and
// too much data. It does illustrate how you can use db + Models.

require(dirname(__DIR__).'/config/cronjob-bootstrap.php');

$data = @file_get_contents('http://external.server.com/api/favs.json') ?: '[{"uid": 1000, "posts": [[99, 0]], "users": []}, {"uid": 2, "posts": [[44, 0], [76, 1], [4, 1]], "users": []}]';
$data = json_decode($data);

// Following
$deletePosts = $addPosts = $deleteUsers = $addUsers = array();

use app\models\User;
use app\models\Post;
use \Exception; // for clarity's sake

echo "<pre>\n";

foreach ( $data AS $userData ) {

	try {

echo "Looking for User # ".$userData->uid."\n";
		$user = User::get($userData->uid);
echo "User '".$user."' found\n";

		// following posts
//echo "Parsing ".count($userData->posts)." posts\n";
		foreach ( $userData->posts AS $postData ) {
			list($_pid, $_status) = $postData;
//echo "Looking for Post # ".$_pid."\n";
			try {
				$post = Post::get($_pid);
//echo "Post '".$post."' found\n";
				if ( $_status ) {
					$user->stopFollowingPost($post);
echo "- User '".$user."' stopped following Post '".$post."'\n";
				}
				else {
					$user->startFollowingPost($post);
echo "+ User '".$user."' started following Post '".$post."'\n";
				}
			}
			catch ( Exception $ex2 ) {
//				echo $ex2->getMessage()."\n";
			}
		}

		// following users
		foreach ( $userData->users AS $followUserData ) {
			list($_uid, $_status) = $followUserData;
			$followUser = User::get($_uid);
			if ( $_status ) {
				$user->stopFollowingUser($followUser); // doesn't exist in example_app
			}
			else {
				$user->startFollowingUser($followUser); // doesn't exist in example_app
			}
		}

	}
	catch ( Exception $ex ) {
		echo "User NOT found: '".$ex->getMessage()."'\n";
	}

	echo "\n";

}


logCronjobResult('oele');


