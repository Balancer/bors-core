<?php

function _e($str)
{
	return $str;
}

function get_bloginfo($key)
{
	global $wp_object;

	switch($key)
	{
		case 'charset':
			return $wp_object->output_charset();
		case 'description':
			return $wp_object->description();
		case 'name':
			return ($o=$wp_object->owner()) ? $o->title() : config('default_owner_name');
		case 'rss2_url':
			return $wp_object->rss_url();
		case 'stylesheet_url':
			return '/css/wordpress/'.$wp_object->template().'/style.css';
		case 'url':
			return '/';
		default:
			return "$key;";
	}
}

function bloginfo($key)
{
	echo get_bloginfo($key);
}

function get_header()
{
	global $wp_object;
	$base = $wp_object->template_wordpress_base_dir();
	include_once($base.'/header.php');
}

function get_sidebar()
{
	global $wp_object;
	$base = $wp_object->template_wordpress_base_dir();
	include_once($base.'/sidebar.php');
}

function get_footer()
{
	global $wp_object;
	$base = $wp_object->template_wordpress_base_dir();
	include_once($base.'/footer.php');
}

function have_posts()
{
	static $shown = 0;
	if($shown++ > 1)
		return false;

	return true;
}

function the_post() { echo 'Post'; }
function the_title() { echo $GLOBALS['wp_object']->title(); }
function the_content() { echo $GLOBALS['wp_object']->body(); }
function the_category() { echo 'category'; }
function the_tags() { echo 'tags'; }
function edit_post_link() { echo $GLOBALS['wp_object']->admin()->imaged_edit_link(); }
function next_post_link() { }
function next_posts_link() { }
function previous_post_link() { }
function previous_posts_link() { }

function wp_footer() { echo ''; }
function wp_get_archives($link) { echo ''; } // ??
function wp_head() { echo ''; } // Допись в хедере
function wp_list_pages() { echo '<li><a href="#">wp_list_pages;</a></li>'; }
function wp_list_categories() { echo 'wp_list_categories;'; }
function wp_loginout() { echo 'wp_loginout;'; }
function wp_meta() { echo 'wp_meta;'; }
function wp_register() { echo 'wp_register;'; }
function wp_tag_cloud($count) { echo '<a href="#">wp_tag_cloud;</a>'; }
function wp_title() { echo $GLOBALS['wp_object']->title(); }

function single_post_title() { return $GLOBALS['wp_object']->title(); }

function get_archives($type, $count) { echo '<li><a href="#">get_archives;</a></li>'; }
function get_links($x1, $open_tag, $close_tag, $x2) { echo 'get_links;'; }

function is_404() { return false; }
function is_category() { return false; }
function is_single() { return false; }
function is_tag() { return false; }
function is_month() { return false; }
function is_year() { return false; }
function is_search() { return false; }
function is_page() { return true; }
function is_author() { return false; }

function is_front_page() { return false; }
