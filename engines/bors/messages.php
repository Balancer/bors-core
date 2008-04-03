<?php

function bors_message($text, $params=array())
{
	header("Cache-Control: no-cache, must-revalidate"); // HTTP/1.1
	header("Expires: Mon, 26 Jul 1997 05:00:00 GMT"); // Date in the past

	$redir = defval($params, 'redirect', false);
	$title = defval($params, 'title', ec('Ошибка!'));
	$timeout = defval($params, 'timeout', -1);
	$template = defval($params, 'template', NULL);

	if(!$redir)
	{
		$link_text = defval($params, 'link_text', ec('вернуться на предыдущую страницу'));
		$link_url = defval($params, 'link_url', 'javascript:history.go(-1)');
	}
	elseif($redir !== true)
	{
		$link_text = defval($params, 'link_text', ec('дальше'));
		$link_url = defval($params, 'link_url', $redir);
	}
		
	$data = array();
	foreach(explode(' ', 'title text link_text link_url') as $key)
		$data[$key] = $$key;

	foreach(explode(' ', 'login_form login_referer') as $key)
		$data[$key] = @$params[$key];

	require_once('engines/smarty/assign.php');
	$body = template_assign_data("xfile:messages.html", $data);

	$GLOBALS['page_data']['title'] = $title;
	$GLOBALS['page_data']['source'] = $body;

//	show_page(@$GLOBALS['main_uri']);

	//TODO: исправить!!
	echo $body;

	if($redir === true)
	{
		if(!empty($_POST['ref']))
			$redir = $_POST['ref'];
		else
			$redir = user_data('level') > 3 ? "/admin/news/" : "/";
	}
		
	if($redir && $timeout >= 0)
		go($redir, false, $timeout);
		
	return true;
}

function bors_message_tpl($template, $obj, $params)
{
	require_once('engines/smarty/assign.php');

	$redir = defval($params, 'redirect', false);
	$title = defval($params, 'title', ec('Ошибка!'));
	$timeout = defval($params, 'timeout', -1);
	
	$params['this'] = &$obj;
	$params['template_dir'] = $obj->class_dir();
	
	$body = template_assign_data($template, $params);

//	print_d($params); exit($body);

	$GLOBALS['page_data']['title'] = $title;
	$GLOBALS['page_data']['source'] = $body;

//	show_page(@$GLOBALS['main_uri']);
	echo $body;

	if($redir === true)
	{
		if(!empty($_POST['ref']))
			$redir = $_POST['ref'];
		else
			$redir = user_data('level') > 3 ? "/admin/news/" : "/";
	}
		
	if($redir && $timeout >= 0)
		go($redir, false, $timeout);
		
	return true;
}
