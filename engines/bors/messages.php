<?php

function bors_message($text, $params=array())
{
	@header("Cache-Control: no-cache, must-revalidate"); // HTTP/1.1
	@header("Expires: Mon, 26 Jul 1997 05:00:00 GMT"); // Date in the past

	$ocs = config('output_charset', config('default_character_set', 'utf-8'));
	$ics = config('internal_charset', 'utf-8');

	@header('Content-Type: text/html; charset='.$ocs);
	@header('Content-Language: '.config('page_lang', 'ru'));

	$redir = defval($params, 'redirect', false);
	$title = defval($params, 'title', ec('Ошибка!'));
	$nav_name = defval($params, 'nav_name', $title);
	$timeout = defval($params, 'timeout', -1);
	$template = defval($params, 'template', config('default_template'));

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
	foreach(explode(' ', 'title text link_text link_url nav_name') as $key)
		$data[$key] = $$key;

	foreach(explode(' ', 'login_form login_referer') as $key)
		$data[$key] = @$params[$key];

	if(empty($data['this']))
		$data['this'] = new base_page(NULL);

	require_once('engines/smarty/assign.php');
	$body = template_assign_data("xfile:messages.html", $data);

	$data['url_engine'] = 'url_calling';

	$page = new base_page(NULL);
	$page->set_fields($data, false);

	$page->set_parents(array(@$_SERVER['REQUEST_URI']), false);

	$data = array(
		'title' => $title,
		'nav_name' => $nav_name,
		'source' => $body,
		'body' => $body,
		'this' => $page,
	);

	if(!preg_match('/^xfile:/', $template))
		$template = "xfile:$template";

	$message = template_assign_data($template, $data);

//	show_page(@$GLOBALS['main_uri']);

	//TODO: исправить!!
	if($ics != $ocs)
		echo iconv($ics, $ocs, $message);
	else
		echo $message;

	if($redir === true)
	{
		if(!empty($_POST['ref']))
			$redir = $_POST['ref'];
		else
			$redir = user_data('level') > 3 ? "/admin/news/" : "/";
	}

	if($redir && $timeout >= 0)
		return go($redir, false, $timeout);

	return true;
}

function bors_message_tpl($template, $obj, $params)
{
	@header("Cache-Control: no-cache, must-revalidate"); // HTTP/1.1
	@header("Expires: Mon, 26 Jul 1997 05:00:00 GMT"); // Date in the past
	
	$ocs = config('output_charset', config('default_character_set', 'utf-8'));
	$ics = config('internal_charset', 'utf-8');
	
	@header('Content-Type: text/html; charset='.$ocs);
	@header('Content-Language: '.config('page_lang', 'ru'));

	require_once('engines/smarty/assign.php');

	$redir = defval($params, 'redirect', false);
	$title = defval($params, 'title', ec('Ошибка!'));
	$timeout = defval($params, 'timeout', -1);
	$global_template = defval($params, 'template', config('default_template'));
	
	$params['this'] = &$obj;
	$params['template_dir'] = $obj->class_dir();
	
	$body = template_assign_data($template, $params);

	$params['title'] = $title;
	$params['source'] = $body;
	$params['body'] = $body;

	echo $message = template_assign_data($global_template, $params);

//	echo iconv($ics, $ocs, $message);

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
