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
			debug_hidden_log('test-keyword-add', "user=".bors()->user().print_r($_POST, true), 1);

		$obj->add_keyword($keyword, true);
		$obj->set_modify_time(time(), true);
//		require_once('inc/airbase_keywords.php');
		return /*airbase_keyword_linkify*/($keyword);

		return true;
	}
}
