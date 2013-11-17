<?php

class bors_html
{
	static function icon($params)
	{
		extract($params);

		if(empty($size))
			$size = '16x16';

		list($width, $height) = explode('x', $size);

		if(empty($css_class))
			$class = '';
		else
			$class = " class=\"$css_class\"";

		if(!preg_match('/\.(png|gif)$/', $image))
		{

			if($size != '16x16')
				$img_dirs = array(
					BORS_EXT.'/htdocs/_bors-ext/iv' => '/_bors-ext/iv',
				);
			else
				$img_dirs = array();

			$img_dirs = array_merge($img_dirs, array(
				BORS_CORE.'/shared/i16' => '/_bors/i16',
				BORS_EXT.'/htdocs/_bors-ext/i16' => '/_bors-ext/i16',
			));

			if($size == '16x16')
				$img_dirs = array_merge($img_dirs, array(
					BORS_EXT.'/htdocs/_bors-ext/iv' => '/_bors-ext/iv',
				));

			foreach($img_dirs as $dir => $path)
			{
				if(file_exists("$dir/$image.svg"))
				{
					$image = "$path/$image.svg";
					break;
				}

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

		$img = "<img src=\"$image\" width=\"{$width}\" height=\"{$height}\" title=\"$title\" alt=\"$action\" style=\"vertical-align: middle\"$class />";

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
