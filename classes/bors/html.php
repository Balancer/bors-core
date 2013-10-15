<?php

class bors_html
{
	static function icon($params)
	{
		extract($params);

		if(empty($css_class))
			$class = '';
		else
			$class = " class=\"$css_class\"";

		if(!preg_match('/\.(png|gif)$/', $image))
		{
			foreach(array(BORS_CORE.'/shared/i16' => '/_bors/i16', BORS_EXT.'/htdocs/_bors-ext/i16' => '/_bors-ext/i16') as $dir => $path)
			{
				if(file_exists("$dir/$image.png"))
				{
					$image = "$path/$image.png";
					break;
				}

				if(file_exists("$dir/$image.gif"))
				{
					$image = "$path/$image.gif";
					break;
				}
			}
		}

		$img = "<img src=\"$image\" width=\"16\" height=\"16\" title=\"$title\" alt=\"$action\" style=\"vertical-align: middle\"$class />";


		if(!empty($action) && empty($link))
		{
			if(!empty($smarty))
			{
				$self = bors_templates_smarty::get_var($smarty, 'this');
				$link = $self->url().'?act='.$action;
			}
		}

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
}
