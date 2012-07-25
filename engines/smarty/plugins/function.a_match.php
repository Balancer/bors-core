<?php

bors_use('url_section_match');

function smarty_function_a_match($params, &$smarty)
{
	extract($params);

//	assert("'$href'");
//	assert("'$text'");

	$this_url = bors()->request()->url();

	static $last_css = false;

	if(!empty($selected_css))
		$last_css = $selected_css;

	if($last_css && url_section_match($this_url, $href))
		$class = " class=\"$last_css\"";
	else
		$class = "";

	echo "<a{$class} href=\"$href\">$text</a>";
}
