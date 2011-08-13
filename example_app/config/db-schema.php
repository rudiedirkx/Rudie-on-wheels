<?php

$schema = array(
	'tables' => array(
		'categories' => array(
			'model' => 'Category',
			'columns' => array(
				'category_id' => array(
					'type' => 'int',
					'unsigned' => true,
					'null' => false,
					'primary' => true
				),
				'category_name' => array(
					'type' => 'text',
					'size' => 60,
					'null' => false,
					'default' => '',
				),
			),
			'engine' => 'myisam',
			'charset' => 'utf8',
		),

		'comments' => array(
			'model' => 'Comment',
			'columns' => array(
				'comment_id' => array(
					'type' => 'int',
					'unsigned' => true,
					'null' => false,
					'primary' => true
				),
				'author_id' => array(
					'type' => 'int',
					'unsigned' => true,
					'null' => false,
					'default' => 0,
				),
				'post_id' => array(
					'type' => 'int',
					'unsigned' => true,
					'null' => false,
					'default' => 0,
				),
				'comment' => array(
					'type' => 'text',
					'null' => false,
					'default' => '',
				),
				'created_on' => array(
					'type' => 'int',
					'unsigned' => true,
					'null' => false,
					'default' => 0,
				),
				'created_by_ip' => array(
					'type' => 'text',
					'size' => 40,
					'null' => false,
					'default' => '',
				),
			),
			'indexes' => array(
				array(
					'columns' => array('post_id'),
				),
				array(
					'columns' => array('author_id'),
				),
			),
			'relationships' => array(
				'post_id' => array('posts', 'post_id'),
				'author_id' => array('users', 'user_id'),
			),
			'engine' => 'myisam',
			'charset' => 'utf8',
		),

		'following_posts' => array(
			'model' => 'FollowingPost',
			'columns' => array(
				'user_id' => array(
					'type' => 'int',
					'unsigned' => true,
					'null' => false,
					'primary' => true
				),
				'post_id' => array(
					'type' => 'int',
					'unsigned' => true,
					'null' => false,
					'primary' => true
				),
				'started_on' => array(
					'type' => 'int',
					'unsigned' => true,
					'null' => false,
					'default' => 0
				),
			),
			'indexes' => array(
				array(
					'columns' => array('post_id'),
				),
			),
			'relationships' => array(
				'user_id' => array('users', 'user_id'),
				'post_id' => array('posts', 'post_id'),
			),
			'engine' => 'myisam',
			'charset' => 'utf8',
		),

		'following_users' => array(
			'model' => 'FollowingUser',
			'columns' => array(
				'user_id' => array(
					'type' => 'int',
					'unsigned' => true,
					'null' => false,
					'primary' => true
				),
				'follows_user_id' => array(
					'type' => 'int',
					'unsigned' => true,
					'null' => false,
					'primary' => true
				),
				'started_on' => array(
					'type' => 'int',
					'unsigned' => true,
					'null' => false,
					'default' => 0
				),
			),
			'indexes' => array(
				array(
					'columns' => array('follows_user_id'),
				),
			),
			'relationships' => array(
				'user_id' => array('users', 'user_id'),
				'follows_user_id' => array('users', 'user_id'),
			),
			'engine' => 'myisam',
			'charset' => 'utf8',
		),

		'posts' => array(
			'model' => 'Post',
			'columns' => array(
				'post_id' => array(
					'type' => 'int',
					'unsigned' => true,
					'null' => false,
					'primary' => true
				),
				'category_id' => array(
					'type' => 'int',
					'unsigned' => true,
					'null' => false,
					'default' => 0,
				),
				'author_id' => array(
					'type' => 'int',
					'unsigned' => true,
					'null' => false,
					'default' => 0,
				),
				'title' => array(
					'type' => 'text',
					'size' => 222,
					'null' => false,
					'default' => '',
				),
				'original_slug' => array(
					'type' => 'text',
					'size' => 222,
					'null' => false,
					'default' => '',
				),
				'body' => array(
					'type' => 'text',
					'null' => false,
					'default' => '',
				),
				'created_on' => array(
					'type' => 'int',
					'unsigned' => true,
					'null' => false,
					'default' => 0,
				),
				'is_published' => array(
					'type' => 'boolean',
					'null' => false,
					'default' => true,
				),
			),
			'indexes' => array(
				array(
					'columns' => array('category_id'),
				),
				array(
					'columns' => array('author_id'),
				),
				array(
					'columns' => array('original_slug'),
					'unique' => true,
				),
				array(
					'columns' => array('is_published'),
				),
			),
			'relationships' => array(
				'user_id' => array('users', 'user_id'),
				'follows_user_id' => array('users', 'user_id'),
			),
			'engine' => 'myisam',
			'charset' => 'utf8',
		),

		'users' => array(
			
		),

		'domains' => array(
			
		),
	),
);