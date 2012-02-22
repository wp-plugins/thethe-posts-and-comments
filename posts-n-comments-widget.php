<?php
/*
Plugin Name: TheThe Posts and Comments Widget
Plugin URI: http://thethefly.com/wp-plugins/thethe-posts-and-comments/
Description: TheThe Posts and Comments Widget

Version: 1.0.0
Author: TheThe Fly
Author URI: http://www.thethefly.com
*/
/**
 * @version 	$Id: posts-n-comments-widget.php 1649 2012-02-08 11:46:04Z dmitriy.f $
 */
/**
 * Init classes,func and libs
 */
/** Require RSS lib */
require_once ABSPATH . WPINC . '/class-simplepie.php';
require_once ABSPATH . WPINC . '/class-feed.php';
require_once ABSPATH . WPINC . '/feed.php';
/** Require WP Plugin API */
require_once ABSPATH . '/wp-admin/includes/plugin.php';
require_once realpath(dirname(__FILE__) . '/lib/lib.core.php');
TheTheFly_require(dirname(__FILE__) . '/inc', array('data.'));
TheTheFly_require(dirname(__FILE__) . '/lib', array('func.','lib.'));
TheTheFly_require(dirname(__FILE__) . '/lib', array('class.','widget.'));

/**
 * Current plugin config
 * @var array
 */
$Plugin_Config = array(
	'shortname' => 'posts-widget',
	'plugin-hook' => 'thethe-posts-n-comments-widget/posts-n-comments-widget.php',
	'options' => array(
		'default' => array(
			'enable-custom-css' => false,
			'custom-css' => '.thethe-posts{}
.thethe-posts .thethe-post{}
.thethe-posts .post-thumbnail{}
.thethe-posts .post-thumbnail img{}
.thethe-posts .post-body{}
.thethe-posts .post-title{}
.thethe-posts .post-date{}
.thethe-posts .post-author{}
.thethe-posts .post-category{}
.thethe-posts .comment-num{}
.thethe-posts .post-category{}
.thethe-posts .post-text{}
.thethe-posts .post-author-avatar{}
.thethe-posts .post-author-avatar img{}

.thethe-comments{}
.thethe-comments .thethe-comment{}
.thethe-comments .comment-author-avatar{}
.thethe-comments .comment-author-avatar img{}
.thethe-comments .comment-title{}
.thethe-comments .comment-meta{}
.thethe-comments .comment-text{}
'
		),
		'posts-widget' => array(
    		'post_loop_type' => 'recent',
    		'quantity' => '5',
    		'use_template' => 'default_template',
    		// What to Display
    		'display_title' => '1',
    		'display_excerpt' => '1',
    		'display_featured_image' => '1',
    		'display_comments_number' => '1',
    		'display_author' => '1',
    		'display_author_avatar' => '1',
    		'display_date' => '1',
    		'display_tags' => '1',
    		'display_categoris' => '1',
    		// How to Display
    		'excerpt_length' => '5',
			'excerpt_length_type' => 'words',
    		'size_f_image' => '50',
    		'size_avatar' => '24',
    		'none_text' => 'No Matches Found',
    		'output_template' =>'<li class="thethe-post">
	<div class="post-thumbnail">{image}</div>					
	<div class="post-body">{title}
	<div class="post-date">{date}</div>
	<div class="post-text">{excerpt}</div>
	<div class="clr"></div>
	<div class="post-category">
		Category: {category}
	</div>
	<div class="post-tags">
		Tags:{tags}
	</div>
	<div class="post-author">
		<span class="post-author-avatar">{avatar}</span>
		<span class="post-author-name">{author}</span>
	</div>
	<div class="comment-num">
		{commentcount}
	</div>
	</div>				
</li>',
    		//Filter by Types & Formats
    			//Display Posts Types
    		'display_type' => 'post,page',
    			//Display Posts Status
    		'display_status' => 'publish',
    			//Display Posts Formats
    		'display_format' => '',
    		'enable_sort' => '0',
    		'order_post' => 'DESC',
    		'orderby_post' => 'date'
    	),
    	'comments-widget' => array(
    		'quantity' => '5',
    		'use_template' => 'default_template',
    		// What to Display
    		'display_title_post' => '1',
    		'display_link_post' => '1',
    		'display_link_comment' => '1',
    		'display_link_author' => '1',
    		'display_excerpt' => '1',
    		'display_author' => '1',
    		'display_author_avatar' => '1',
    		'display_date' => '1',
    		// How to Display
    		'excerpt_length' => '10',
    		'excerpt_length_type' => 'words',
    		'size_avatar' => '32',
    		'none_text' => 'No Matches Found',
    		'output_template' => '<li class="thethe-comment">
<div class="comment-title">
	<a href="{authorlink}">{avatar} {author}</a> on <a href="{postlink}">{posttitle}</a>
</div>
<div class="comment-body">
	<p align="justify"><a href="{commentlink}">{excerpt}...</a></p>
</div>
<div class="comment-info">Posted on: {commentdate}</div>
</li>',
    		'date_format' => 'd-m-Y',
    		//Display Posts Types
    		'display_type' => 'post',
    	)
	),
	'requirements' => array('wp' => '3.1')
) + array('meta' => get_plugin_data(realpath(__FILE__)) + array(
	'wp_plugin_dir' => dirname(__FILE__),
	'wp_plugin_dir_url' => plugin_dir_url(__FILE__)
)) + array(
	'clubpanel' => array(),
	'adminpanel' => array('sidebar.donate' => true)
);

/**
 * @var PluginPostsCommentsWidget
 */
$GLOBALS['PluginPostsCommentsWidget'] = new PluginPostsCommentsWidget();
/**
 * Configure
 */
$GLOBALS['PluginPostsCommentsWidget']->configure($Plugin_Config);

/**
 * Init
 */
TheTheFly_require(dirname(__FILE__),array('init.'));
$GLOBALS['PluginPostsCommentsWidget']->init();

/** @todo fixme */
if (!function_exists('TheThe_makeAdminPage')) {
	function TheThe_makeAdminPage() {
		$GLOBALS['PluginPostsCommentsWidget']->displayAboutClub();
	}
}

load_plugin_textdomain('thethe-posts-n-comments-widget', false, dirname(plugin_basename(__FILE__)).'/languages' );