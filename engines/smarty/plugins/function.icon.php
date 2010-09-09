<?php

function smarty_function_icon($params, &$smarty)
{
	extract($params);

	$img = "<img src=\"/_bors/i16/$image\" width=\"16\" height=\"16\" title=\"$title\" alt=\"$action\" />";

	$self = $smarty->get_template_vars('this');

	if(!empty($action) && empty($link))
		$link = $self->url().'?act='.$action;

	if(!empty($target))
		$link .= '&target='.$target->internal_uri_ascii();

	if(!empty($link))
		$img = "<a href=\"{$link}\">{$img}</a>";

	return $img;
}
