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
				."<div class=\"red_box\">$message</div>\n"
				.ec("Администраторы будут извещены об этой проблеме и постараются её устранить. Извините за неудобство.\n")
				."<!--\n\n$trace\n\n-->", array(
//					'template' => 'xfile:default/popup.html',
			));
		}
		catch(Exception $e2)
		{
			bors()->set_main_object(NULL);
			bors_message(ec("При попытке просмотра этой страницы возникли ошибки:\n")
				."<div class=\"red_box\">$message</div>\n"
				.ec("Администраторы будут извещены об этой проблеме и постараются её устранить. Извините за неудобство.\n")
				."<!--\n\n$trace\n\n-->", array(
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
			return ec("При попытке просмотра этой страницы возникла ошибка:\n")
				."<div class=\"red_box\">$message</div>\n"
				.ec("Администраторы будут извещены об этой проблеме и постараются её устранить. Извините за неудобство.\n")
				."<!--\n\n$trace\n\n-->";
		}
		catch(Exception $e2)
		{
			bors()->set_main_object(NULL);
			return ec("При попытке просмотра этой страницы возникли ошибки:\n")
				."<div class=\"red_box\">$message</div>\n"
				.ec("Администраторы будут извещены об этой проблеме и постараются её устранить. Извините за неудобство.\n")
				."<!--\n\n$trace\n\n-->";
		}
	}
}
