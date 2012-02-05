<?php
foreach($links as $linkline)
{
	$first = true;
	foreach($linkline as $obj)
	{
		if($first)
			$first = false;
		else
			echo $delim;

		echo "<a href=\"{$obj->url(1)}\" title=\"".htmlspecialchars($obj->title())."\"";
		if($nav_obj->url(1) == $obj->url(1))
			echo " class=\"nav_top_current\"";
		echo '>'.htmlspecialchars($obj->nav_name()).'</a>';
	}
	echo "<br />";
}
