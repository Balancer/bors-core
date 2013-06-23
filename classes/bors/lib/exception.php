<?php

class bors_lib_exception extends bors_object
{
	static function catch_show($e, $message = NULL)
	{
		$trace = debug_trace(0, false, -1, $e->getTrace());
		$message = $e->getMessage();
		debug_hidden_log('exception', "$message\n\n$trace", true, array('dont_show_user' => true));

		try
		{
			bors_message(ec("При попытке просмотра этой страницы возникла ошибка:\n")
				.(config('show_errors', false) !== false ? ":\n<div class=\"red_box alert alert-error\">$message</div>\n" : ".\n")
				.ec("Администраторы будут извещены об этой проблеме и постараются её устранить. Извините за неудобство.\n")
				.(config('site.is_dev') ? "<pre>$trace</pre>" : "<!-- ~~~2 \n\n$trace\n\n-->"), array(
//					'template' => 'xfile:default/popup.html',
			));
		}
		catch(Exception $e2)
		{
			bors()->set_main_object(NULL);
			bors_message(ec("При попытке просмотра этой страницы возникли ошибки:\n")
				.(config('show_errors', false) !== false ? ":\n<div class=\"red_box alert alert-error\">$message</div>\n" : ".\n")
				.ec("Администраторы будут извещены об этой проблеме и постараются её устранить. Извините за неудобство.\n")
				.(config('site.is_dev') ? "<pre>$trace</pre>" : "<!-- ~~~4 \n\n$trace\n\n-->"), array(
				'template' => 'xfile:default/popup.html',
			));
		}
	}

	static function catch_trace($e)
	{
		$trace = debug_trace(0, false, -1, $e->getTrace());
		$message = $e->getMessage();
		return "$message\n$trace";
	}

	static function catch_html_code($e, $message = NULL)
	{
		$trace = debug_trace(0, false, -1, $e->getTrace());
		$message = $e->getMessage();
		debug_hidden_log('exception', "$message\n\n$trace", true, array('dont_show_user' => true));

		try
		{
			return ec("При попытке просмотра этой страницы возникла ошибка")
				.(config('show_errors', false) !== false ? ":\n<div class=\"red_box alert alert-error\">$message</div>\n" : ".\n")
				.ec("Администраторы будут извещены об этой проблеме и постараются её устранить. Извините за неудобство.\n")
				."<!-- ~~~5 \n\n$trace\n\n-->";
		}
		catch(Exception $e2)
		{
			bors()->set_main_object(NULL);
			return ec("При попытке просмотра этой страницы возникли ошибки:\n")
				.(config('show_errors', false) !== false ? ":\n<div class=\"red_box alert alert-error\">$message</div>\n" : ".\n")
				.ec("Администраторы будут извещены об этой проблеме и постараются её устранить. Извините за неудобство.\n")
				."<!-- ~~~6 \n\n$trace\n\n-->";
		}
	}
}
