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
}
