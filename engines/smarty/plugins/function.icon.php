<?php

function smarty_function_icon($params, &$smarty)
{
	extract($params);

	if(empty($css_class))
		$class = '';
	else
		$class = " class=\"$css_class\"";

	if(!preg_match('/\.(png|gif)$/', $image))
		if(file_exists(BORS_CORE.'/shared/i16/'.$image.'.png'))
			$image .= '.png';
		else
			$image .= '.gif';

	$img = "<img src=\"/_bors/i16/$image\" width=\"16\" height=\"16\" title=\"$title\" alt=\"$action\" style=\"vertical-align: middle\"$class />";

	$self = bors_templates_smarty::get_var($smarty, 'this');

	if(!empty($action) && empty($link))
		$link = $self->url().'?act='.$action;

	if(!empty($target))
		$link .= '&target='.$target->internal_uri_ascii();

	if(!empty($ref))
		$link .= '&ref='.urlencode($ref);

	if(empty($link_target))
		$link_target = "";
	else
		$link_target = " target=\"{$link_target}\"";

	if(!empty($link))
		$img = "<a href=\"{$link}\"{$link_target}>{$img}</a>";

	return $img;
}
