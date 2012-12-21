<?php

class bors_tools_ajax_setkeywords extends base_page
{
	function pre_show()
	{
		if(!bors()->user())
			return;

		$keyword = @$_GET['keyword'];
		if(!$keyword)
			return true;

		$obj = object_load(@$_GET['object']);
		if(!$obj)
			return true;

		if(bors()->user_id() != 10000)
			debug_hidden_log('tag-add', "user=".bors()->user().' '.str_replace("\n", " ", print_r($_POST, true)), false);

		$obj->add_keyword($keyword, true);
		$obj->set_modify_time(time(), true);
//		require_once('inc/airbase_keywords.php');
		return /*airbase_keyword_linkify*/($keyword);

		return true;
	}
}
