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

		echo "<a href=\"{$obj->url()}\" title=\"".htmlspecialchars($obj->title())."\"";
		if($nav_obj->url() == $obj->url())
			echo " class=\"nav_top_current\"";
		echo ">{$obj->nav_name()}</a>";
	}
	echo "<br />";
}
