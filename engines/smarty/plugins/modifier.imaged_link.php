<?php

function smarty_modifier_imaged_link($object, $title, $link, $icon = NULL)
{
	if(preg_match('/^\->(\w+)$/', $link, $m))
	{
		$link = $m[1];
		$url = $object->url_ex($link);
	}
	elseif(preg_match('/^\w+$/', $link))
	{
		$url = bors()->main_object()->called_url_no_get();
		$url .= '?act='.$link.'&obj='.$object->internal_uri_ascii().'&ref='.urlencode(bors()->main_object()->called_url());
	}
	else
	{
		$url = $link;
	}

	if(!$icon)
		$icon = $link;

	if(file_exists(BORS_CORE."/shared/i/$icon-16.png"))
	{
		$icon = "/_bors/i/$icon-16.png";
	}
	elseif(file_exists(BORS_CORE."/shared/i/$icon-16.gif"))
	{
		$icon = "/_bors/i/$icon-16.gif";
	}

	$action = $icon;
	echo "<a href=\"{$url}\"><img width=\"16\" height=\"16\" style=\"vertical-align:middle\" src=\"$icon\" alt=\"$action\" title=\"{$title}\" /></a>";
}
