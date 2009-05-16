<?php

function _e($str)
{
	return $str;
}

function bloginfo($key)
{
	global $wp_object;

	switch($key)
	{
		case 'charset':
			echo $wp_object->output_charset();
			break;
		case 'description':
			echo $wp_object->description();
			break;
		case 'name':
			echo ($o=$wp_object->owner()) ? $o->title() : config('default_owner_name');
			break;
		case 'stylesheet_url':
			echo '/css/wordpress/'.$wp_object->template().'/style.css';
			break;
		case 'url':
			echo '/';
			break;
		default:
			echo "$key;";
			break;
	}
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

function have_posts()
{
	return false;
}

function get_footer()
{
	global $wp_object;
	$base = $wp_object->template_wordpress_base_dir();
	include_once($base.'/footer.php');
}

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

function get_archives($type, $count) { echo '<li><a href="#">get_archives;</a></li>'; }
function get_links($x1, $open_tag, $close_tag, $x2) { echo 'get_links;'; }

function is_404() { return false; }
function is_category() { return false; }
function is_single() { return false; }
function is_tag() { return false; }
function is_month() { return false; }
function is_year() { return false; }
function is_search() { return false; }
function is_page() { return false; }
function is_author() { return false; }

function is_front_page() { return true; }
