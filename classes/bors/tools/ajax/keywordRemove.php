<?php

class bors_tools_ajax_keywordRemove extends base_page
{
	function pre_show()
	{
		if(!bors()->user())
			return;

		if(bors()->user()->is_destructive())
			return true;

		$keyword = str_replace(' ', ' ', @$_GET['keyword']);
		$keyword = trim($keyword);
		if(!$keyword)
			return true;

		$obj = object_load(@$_GET['object']);
		if(!$obj)
			return true;

		$obj->remove_keyword($keyword, true);
		$obj->set_modify_time(time(), true);

		if(bors()->user_id() != 10000)
			bors_debug::syslog('tag-remove', "user=".bors()->user().' ['.bors()->user_id().']:'.str_replace("\n", " ", print_r($_POST, true)), false);

		return true;
	}
}
