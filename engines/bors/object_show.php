<?php

	function bors_object_show($obj)
	{
		$page = $obj->set_page($obj->args('page'));
//		echo "Bors class=".get_class($obj); exit();
		if(!$obj)
			return false;

		@header("Status: 200 OK");
		@header("HTTP/1.1 200 OK");
		if(config('bors_version_show'))
		{
			@header("X-Bors-object-class: {$obj->class_name()}");
			@header("X-Bors-object-id: {$obj->id()}");
		}

		$processed = $obj->pre_parse($_GET);
		if($processed === true)
			return true;

		if(!empty($_GET))
		{
			require_once('inc/bors/form_save.php');
			$processed = bors_form_save($obj);
			if($processed === true)
				return true;
		}

		$access_object = $obj->access();
		if(!$access_object)
			debug_exit("Can't load access_engine ({$obj->access_engine()}?) for class {$obj}");

		if(!$access_object->can_read())
			return empty($GLOBALS['cms']['error_show']) ? bors_message(ec("Извините, у Вас не доступа к этому ресурсу")) : true;

		$processed = $obj->pre_show();
		if($processed === true)
			return true;

		$called_url = preg_replace('/\?.*$/', '', $obj->called_url());
		$target_url = preg_replace('/\?.*$/', '', $obj->url($page));
		if($obj->called_url() && !preg_match('!'.preg_quote($target_url).'$!', $called_url))
			return go($obj->url($page), true);

		if($processed === false)
		{
			bors()->set_main_object($obj);

			if(empty($GLOBALS['main_uri']))
				$GLOBALS['main_uri'] = $obj->url();

			$my_user = bors()->user();
			if($my_user && $my_user->id())
				base_page::add_template_data('my_user', $my_user);

			$content = $obj->content();
		}
		else
			$content = $processed;

		if($content === false)
			return false;

		$last_modify = gmdate('D, d M Y H:i:s', $obj->modify_time()).' GMT';
		@header('Last-Modified: '.$last_modify);

		echo $content;

		return true;
	}

function bors_object_create($obj)
{
	$page = $obj->set_page($obj->args('page'));
	if(!$obj)
		return NULL;

	$processed = $obj->pre_parse($_GET);
	if($processed === true)
		return NULL;
			
	$processed = $obj->pre_show();
	if($processed === true)
		return NULL;

	if($obj->called_url() && !preg_match('!'.preg_quote($obj->url($page)).'$!', $obj->called_url()))
		return NULL;

	if($processed === false)
	{
		bors()->set_main_object($obj);

		if(empty($GLOBALS['main_uri']))
			$GLOBALS['main_uri'] = $obj->url();
			
		return $obj->content(true, true);
	}
	
	return NULL;
}
