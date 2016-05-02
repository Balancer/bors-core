<?php

use Symfony\Component\HttpFoundation\Response;

function bors_message($text, $params=array())
{
	template_nocache();
	template_css('/_bors/css/messages.css');

	$ocs = config('output_charset', config('internal_charset', 'utf-8'));
	$ics = config('internal_charset', 'utf-8');

	@header('Content-Type: text/html; charset='.$ocs);
	@header('Content-Language: '.config('page_lang', 'ru'));

	if($status = defval($params, 'http_status'))
	{
		$resp = new Response($text, $status);
		$resp->sendHeaders();
	}

	$redir = defval($params, 'go', defval($params, 'redirect', false));
	$title = defval($params, 'title', ec('Ошибка! [2]'));
	$nav_name = defval($params, 'nav_name', $title);
	$timeout = defval($params, 'timeout', -1);
	$hidden_log = defval($params, 'hidden_log');

	if(!empty($params['link_url']))
		$redir = 'true';

	if(!$redir)
	{
		$ref = bors()->client()->referer();
		$ud = url_parse($ref);
		if($ud['path'] == '/')
			$ref = NULL;

		if($ref)
		{
			$link_text = defval($params, 'link_text', ec('вернуться на предыдущую страницу'));
			$link_url = defval($params, 'link_url', 'javascript:history.go(-1)');
		}
		else
		{
			$link_text = defval($params, 'link_text', ec('Перейти к началу сайта'));
			$link_url = defval($params, 'link_url', '/');
		}
	}
	elseif($redir !== true)
	{
		$link_text = defval($params, 'link_text', ec('дальше'));
		$link_url = defval($params, 'link_url', $redir);
	}

	$is_error = preg_match('/шибк/i', bors_lower($title));

	if($redir === true)
	{
		if(!empty($_POST['ref']))
			$redir = $_POST['ref'];
		else
			$redir = user_data('level') > 3 ? "/admin/news/" : "/";
	}

	$data = array();
	foreach(explode(' ', 'is_error title text link_text link_url nav_name redir timeout') as $key)
		$data[$key] = $$key;

	foreach(explode(' ', 'login_form login_referer') as $key)
		$data[$key] = @$params[$key];

	if(empty($data['this']))
		$data['this'] = object_load('bors_page', NULL);

	bors_function_include('debug/trace');
	$data['debug_trace'] = bors_debug::trace(0, false);

	$body_template = "xfile:messages.html";
	if(!empty($params['choises']))
	{
		$choises = array();
		foreach($params['choises'] as $title => $target)
		{
			$c = array(
				'title' => $title,
				'target' => $target,
			);
			if(preg_match('/^\w+$/', $target))
			{
				$c['act'] = $target;
				$c['class'] = $params['this']->class_name();
				$c['go'] = $params['this']->url();
			}
			else
			{
				$c['act'] = '__go';
				$c['class'] = 'NULL';
				$c['go'] = $target;
			}

			$choises[] = $c;
		}

		$body_template = "xfile:messages-confirm.html";
		$data['choises'] = $choises;
	}

	$body = bors_templates_smarty::fetch($body_template, $data);
	// Если возникла какая-то ошибка рендеринга, выводим исходный текст.
	if(!$body)
		$body = $text;

	$data['url_engine'] = 'url_calling';

	$page_class_name = defval($params, 'page_class_name', 'bors_page');
	$page = new $page_class_name(NULL);
	$page->b2_configure();
	try { $page->template_data_fill(); }
	catch(Exception $e) { }
	$page->set_fields($data, false);

	$page->set_parents(array(@$_SERVER['REQUEST_URI']), false);

	$data = array(
		'title' => $title,
		'nav_name' => $nav_name,
		'source' => $body,
		'body' => $body,
		'this' => $page,
		'is_error' => $is_error,
	);

	$page->set_fields($data, false);

	$page->set_attr('theme_class', defval($params, 'theme_class'));

	if($is_error)
		$data['skip_nav'] = true;

	$data = array_merge($data, array(
		'success_message' => session_var('success_message'),
		'notice_message'  => session_var('notice_message'),
		'error_message'   => session_var('error_message'),
	));

	if($data['success_message'] || $data['notice_message'] || $data['error_message'])
		config_set('skip_cache_static', true);

	$theme_class = defval($params, 'theme_class');
	$template = defval($params, 'template');

	if(($theme_class && class_exists($theme_class)) || (!$template && class_exists($theme_class = 'bors_themes_bootstrap3')))
	{
		$page->set_parents(array('/'));

		if($is_error)
			$class = "alert alert-danger";
		else
			$class = "alert alert-warning";

		$page->set_body('<div class="'.$class.'" style="font-size: 24px">'.$page->body().'</div><hr/>');
		$renderer = bors_load($theme_class, $page);
//		if($layout_class = $renderer->get('layout_class'))
//			$page->set_attr('layout_class', $layout_class);

		$renderer->append_data($data);

		$message = $renderer->render();
	}
	else
	{
		if(!$template && defval($params, 'page_class_name'))
			$template = $page->template();

		if(!$template)
			$template = config('default_message_template', config('default_template'));

//		if(!preg_match('/^xfile:/', $template) && !preg_match('/^bors:/', $template))
//			$template = "xfile:$template";

		try
		{
			$message = NULL; // bors_templates_smarty::fetch($template, $data);
		}
		catch(Exception $e)
		{
			$message = NULL;
		}
	}

	if(!$message) // Если всё плохо
		$message = $body;

	// Используем до первого вывода по echo ниже
	if(empty($params['save_session']))
		clean_all_session_vars();

	//TODO: исправить!!
	if($ics != $ocs)
		echo iconv($ics, $ocs, $message);
	else
		echo $message;

	if($hidden_log)
		bors_debug::syslog($hidden_log, "message: $text");

	if($redir && $timeout >= 0)
		return go($redir, false, $timeout);

	return true;
}

function bors_message_tpl($message_template, $obj, $params)
{
	@header("Cache-Control: no-cache, must-revalidate"); // HTTP/1.1
	@header("Expires: Mon, 26 Jul 1997 05:00:00 GMT"); // Date in the past

	$ocs = config('output_charset', config('internal_charset', 'utf-8'));
	$ics = config('internal_charset', 'utf-8');

	@header('Content-Type: text/html; charset='.$ocs);
	@header('Content-Language: '.config('page_lang', 'ru'));

	require_once('engines/smarty/assign.php');

	$redir = defval($params, 'redirect', false);
	$title = defval($params, 'title', ec('Ошибка! [3]'));
	$timeout = defval($params, 'timeout', -1);
	$page_template = defval($params, 'template', config('default_template'));

	$params['this'] = $obj;

	$params['template_dir'] = $obj->class_dir();

	$body = template_assign_data($message_template, $params);

	$params['this'] = $foo = bors_foo('bors_page');
	$foo->set_attr('title', $params['title'] = $title);
	$foo->set_attr('source', $params['source'] = $body);
	$foo->set_attr('body', $params['body'] = $body);
	echo template_assign_data($page_template, $params);
//	var_dump($message_template, $page_template, $body);

//	echo iconv($ics, $ocs, $message);

	if($redir === true)
	{
		if(!empty($_POST['ref']))
			$redir = $_POST['ref'];
		else
			$redir = user_data('level') > 3 ? "/admin/news/" : "/";
	}

	if(empty($params['save_session']))
		clean_all_session_vars();

	if($redir && $timeout >= 0)
		return go($redir, false, $timeout);

	return true;
}


function bors_http_error($errno)
{
	switch($errno)
	{
		case 404:
			@header("HTTP/1.0 404 Not Found");
			if(config('404_page_url'))
				return go(config('404_page_url'), true);
			if(config('404_show', true))
				echo ec("404 Not found<br/>Page '{$GLOBALS['bors_full_request_url']}' not found!");
			break;
	}

	bors_exit();
}
