<?php

	// Возвращает false при ошибке показа объекта
	// true - если была какая-то отработка и требуется прекратить дальнейшую работу.
	// Иначе - строку с результатом для вывода.
	function bors_object_show($obj)
	{
		$page = $obj->set_page($obj->args('page'));

//		if(debug_is_balancer()) { echo "Bors class=".get_class($obj); exit('>'.time()); }
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
			return empty($GLOBALS['cms']['error_show']) ? bors_message(ec("Извините, у Вас нет доступа к этому ресурсу
				<!-- access=$access_object
				access_object=$access_object
				class_file = ".(method_exists($access_object, 'class_file') ? $access_object->class_file() : 'none')."
			-->")) : true;

		$processed = $obj->pre_show();
		if($processed === true)
			return true;

		// [HTTP_IF_MODIFIED_SINCE] => Mon, 27 Jul 2009 19:03:37 GMT
		// [If-Modified-Since] => Mon, 27 Jul 2009 19:03:37 GMT
		if(!empty($_SERVER['HTTP_IF_MODIFIED_SINCE']) && config('ims_enabled'))
		{
			$check_date = strtotime($_SERVER['HTTP_IF_MODIFIED_SINCE']);
			if($check_date >= $obj->modify_time())
			{
				@header('HTTP/1.1 304 Not Modified');
				return bors_exit();
			}
		}

		$called_url = preg_replace('/\?.*$/', '', $obj->called_url());
		$target_url = preg_replace('/\?.*$/', '', $obj->url($page));
		if($obj->called_url() && !preg_match('!'.preg_quote($target_url).'$!', $called_url) && $obj->_auto_redirect())
			return go($obj->url($page), true);

		if($processed === false)
		{
			if(!(bors()->main_object()))
				bors()->set_main_object($obj);

			if(empty($GLOBALS['main_uri']))
				$GLOBALS['main_uri'] = $obj->url();

			$content = $obj->content();
		}
		else
			$content = $processed;

		if($content === false)
			return false;

		$access_object = $obj->access();
		if(!$access_object)
			debug_exit("Can't load access_engine ({$obj->access_engine()}?) for class {$obj}");

		if(!$access_object->can_read())
			return empty($GLOBALS['cms']['error_show']) ? bors_message(ec("Извините, у Вас нет доступа к этому ресурсу [2]\n<!-- $access_object, class_file = {$access_object->class_file()}-->")) : true;

		$last_modify = @gmdate('D, d M Y H:i:s', $obj->modify_time()).' GMT';
		@header('Last-Modified: '.$last_modify);

		return $content;
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
