<?php

class bors_message extends bors_object
{
	static function error($text, $params)
	{
		$message = "<div class=\"alert alert-error\">{$text}</div>";
		$redir = defval($params, 'go', defval($params, 'redirect', false));
		$title = defval($params, 'title', ec('Ошибка!'));
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

		if($link_url)
			$message .= "<p><a href=\"{$link_url}\" class=\"btn btn-primary\">{$link_text}</a></p>";

		if($si = @$params['sysinfo'])
			$message .= twitter_bootstrap::collapsed(ec('Служебная информация'), $si.bors_debug::trace(0, false));

		echo twitter_bootstrap::raw_message(array(
			'this' => bors_load('bors_pages_fake', array(
				'title' => ec('Ошибка прав доступа'),
				'body' => $message,
			)),
		));

		return true;
	}
}
