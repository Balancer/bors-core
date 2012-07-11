<?php

class blib_css
{
	function mk_class($classes, $index = NULL, $default = '')
	{
		if($index)
			$classes = defval($classes, $index, $default);

		if(empty($classes))
			return "";

		if(is_array($classes))
			$classes = join(" ", $classes);

		return " class=\"$classes\"";
	}
}
