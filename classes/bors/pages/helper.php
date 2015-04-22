<?php

class bors_pages_helper
{
	static function parents_links_lines($obj, $show_self = true, &$shown = array())
	{
		$result = array(array());

		if(!$obj || !$obj->internal_uri())
			return $result;

		if(@$shown[$obj->internal_uri()])
			return $result;

		$shown[$obj->internal_uri()] = true;

		if(!$obj->parents())
			return $result;

		$result = array();
		foreach($obj->parents() as $parent)
		{
			$links = array();

			if($parent == 'http:///')
			{
				debug_hidden_log('internal-errors', "Incorrect parent url for '{$obj}': $parent");
				continue;
			}

			$parent_obj = object_load($parent);
//			echo "p($obj): $parent -> $parent_obj<br/>";
			if(!$parent_obj || $parent_obj->internal_uri() == $obj->internal_uri())
				continue;

//			$shown[$parent_obj->internal_uri()] = true;

			$parent_link_line = self::parents_links_lines($parent_obj, false, $shown);

			for($i = 0; $i < count($parent_link_line); $i++)
				$parent_link_line[$i][] = $parent_obj;

			$result = array_merge($result, $parent_link_line);
		}

		if(empty($result))
			$result = array(array());

		if($show_self)
			for($i = 0; $i < count($result); $i++)
				$result[$i][] = $obj;

		return $result;
	}

/*
	$style = array with styles
*/
	static function style($style)
	{
		if(empty($style))
			return '';

		return "<style type=\"text/css\" media=\"all\"><!--\n"
			.join("\n", $style)
			."\n--></style>\n";
	}

	static function make_sortable_th($view, $property, $title)
	{
		if(is_numeric($property))
		{
			$property = $title;
			$x = bors_lib_orm::parse_property($view->model_class(), $property);
			$title = defval($x, 'title', $property);
		}

		$sorts = $view->get('sortable', array());
		if($x = $view->get('_sortable_append', array()))
			$sorts = array_merge($x, $sorts);

		$parsed_sorts = array();

		$model_class = $view->get('model_class');

		if(!$model_class)
			$model_class = $view->get('main_class');

		// Для использования в bors_pages_module_paginated_items::make_sortable_th
		if(!$model_class && ($items = $view->args('items')))
			$model_class = get_class($items[0]);

		if(!$model_class)
			bors_throw("Can't get model class for view ".$view->debug_title());

		foreach($sorts as $f => $p)
		{
			if(is_numeric($f))
			{
				$f = $p;
				$x = bors_lib_orm::parse_property($model_class, $f);
				$t = defval($x, 'title', $f);
			}

			$parsed_sorts[$f] = $p;
		}

		$sorts = $parsed_sorts;

		if(!($sort_key = @$sorts[$property]))
			return "<th>$title</th>";

		$current_sort = bors()->request()->data_parse('signed_names', 'sort');
		if(preg_match('/^(.+)\*$/', $sort_key, $m))
		{
			$sort_key = $m[1];
			$is_default = true;
		}
		else
			$is_default = false;

		if($is_default && !$current_sort)
			$current_sort = $sort_key;

		$sort = bors_lib_orm::reverse_sign($sort_key, $current_sort);

		$sign = bors_lib_orm::property_sign($sort);
		if($is_default && $sort_key == $sort)
			$sort = NULL;

		$url = bors()->request()->url();

		$url = bors_lib_urls::replace_query($url, 'sort', $sort);

		bors_lib_orm::property_sign($current_sort, true);
		bors_lib_orm::property_sign($sort_key, true);
		if($current_sort != $sort_key)
			$sort_class = 'sort_ascdesc';
		else
			$sort_class = $sign == '-' ? 'sort_asc' : 'sort_desc';

		return "<th class=\"$sort_class\"><a href=\"{$url}\">$title</a></th>";
	}
}
