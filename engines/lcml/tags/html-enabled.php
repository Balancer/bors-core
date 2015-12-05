<?php

foreach(array('b' => 'strong') as $tag => $html)
	eval("function lp_$tag(\$txt){return '<$html>'.lcml(\$txt).'</$html>';}");

foreach(explode(" ",'big em i s strike strong sub sup small tbody u') as $tag)
	eval("function lp_$tag(\$txt){return '<$tag>'.lcml(\$txt).'</$tag>';}");

foreach(explode(" ","br hr") as $tag)
	eval("function lt_$tag(){return '<$tag />';}");

function lp_html_iframe($inner, $params)
{
	// http://www.balancer.ru/g/p3577020
	if(@$params['width'] < 200 && !preg_match('/width:/', @$params['style']))
		$params['width'] = 200;
	if(@$params['height'] < 200 && !preg_match('/height:/', @$params['style']))
		$params['height'] = 200;

	$params['src'] = html_entity_decode(@$params['src']);

	$iframes_whitelist = preg_split('/[^\w\.\-]+/', config('security.irames.whitelist', 'coub.com,vk.com,player.vgtrk.com'));

	$url_info = parse_url($params['src']);
	if(!in_array($url_info['host'], $iframes_whitelist))
		$sandbox = " sandbox";
	else
		$sandbox = "";

	return "<iframe ".make_enabled_params($params, 'width height frameborder scrolling style marginheight marginwidth src webkitAllowFullScreen mozallowfullscreen allowfullscreen')."{$sandbox}>$inner</iframe>";
}

/*
function lp_style($inner, $params)
{
	return "<style ".make_enabled_params($params, 'type').">$inner</style>";
}
*/
//TODO: сделать проверку на наличие активного кода в стилях.
function lp_style($inner, $params)
{
	return "<style type=\"text/css\">{$inner}</style>";
}

function lp_table($inner, $params)
{
	if(empty($params['class']) && empty($params['style']) && !empty($params['border']))
	{
		unset($params['border']);
		$params['class'] = 'btab';
	}

	$params['lcml']->set_p('last_tag', 'table');
	$inner_html = $params['lcml']->parse($inner);
//	$params['lcml']->set_p('last_tag', NULL);
	return "<table ".make_enabled_params($params, 'cellpadding cellspacing class style border').">{$inner_html}</table>";
}

function lp_table_html($inner, $params)
{
	if(empty($params['class']) && empty($params['style']) && !empty($params['border']))
	{
		unset($params['border']);
		$params['class'] = 'btab';
	}
	return "<table ".make_enabled_params($params, 'cellpadding cellspacing class style border').">".lcml(str_replace("\n"," ",$inner))."</table>";
}

function lp_form($inner, $params)
{
	if(!preg_match('!^http://(aeterna\.ru)!', @$params['action']))
	{
		debug_hidden_log('lcml-need-attention', "Need check form action {$params['action']}");
		return ec('Публикация форм неизвестных ресурсов запрещена. Администратору отправлена заявка на проверку этого ресурса.');
	}

	return "<form ".make_enabled_params($params, 'name method action').">".lcml($inner)."</form>\n";
}

function lt_input($params)
{
	return "<input ".make_enabled_params($params, 'type name value')." />\n";
}
