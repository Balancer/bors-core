<?php

/**
	Тэг, эмуляция html-тэга <a>
	Пример использования: [a href="http://balancer.ru"]Сайт расходящихся тропок[/a]
*/

class bors_lcml_tag_pair_a extends bors_lcml_tag_pair
{
	function html($text, $params)
	{
		if(empty($params['href']))
			debug_hidden_log('errors_lcml_parameters', "Tag [a] without href param for '{$text}'");

		$url = @$params['href'];
		return "<a href=\"$url\""
			.bors_lib_urls::check_nofollow($url)
			.bors_lib_urls::check_external($url)
			.">$text</a>";
	}

	static function __unit_test($suite)
	{
		config_set('seo_domains_whitelist_regexp', 'balancer.ru');

		$code = '[a href="http://balancer.ru"]Сайт расходящихся тропок[/a]';
		$html = lcml($code);
		$suite->assertRegexp('#<a.*balancer.ru.*Сайт расходящихся тропок</a>#', $html);
		$suite->assertNotRegexp('#nofollow#', $html);

		$code = '[a href="http://example.com"]Гугль, не ходи туда![/a]';
		$html = lcml($code);
		$suite->assertRegexp('#<a.*example.com.*nofollow.*Гугль, не ходи туда!</a>#', $html);
		$suite->assertRegexp('#external#', $html);
	}
}
