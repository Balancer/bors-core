<?php
    foreach(explode(" ",'b big em i s strong sub sup small u') as $tag)
		eval("function lp_$tag(\$txt){return '<$tag>'.lcml(\$txt).'</$tag>';}");

    foreach(explode(" ","br hr") as $tag)
		eval("function lt_$tag(){return '<$tag />';}");

	function lp_html_iframe($inner, $params)
	{
		if(@$params['width'] < 200)
			$params['width'] = 200;
		if(@$params['height'] < 200)
			$params['height'] = 200;

		$params['src'] = html_entity_decode(@$params['src']);
		
		return "<iframe ".make_enabled_params($params, 'width height frameborder scrolling marginheight marginwidth src').">$inner</iframe>";
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
	return "<table ".make_enabled_params($params, 'cellpadding cellspacing class style border').">".lcml($inner)."</table>";
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
