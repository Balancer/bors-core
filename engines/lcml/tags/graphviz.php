<?php

config_set('graphviz_dot_command', '/usr/bin/dot');

function lp_graphviz($text, $params)
{
	$text = restore_format($text);
	if(!preg_match('/^\w+\s+\w+\s+\{\s.+\}/s', trim($text)))
		$text = "digraph G { {$text} }";

	if(config('is_developer')) var_dump('x', $text);

	$image = bors_load('bors_image_generated', serialize(array(
		'class_name' => 'bors_image_generated_graphviz',
		'width' => defval_ne($params, 'width', NULL),
		'height' => defval_ne($params, 'height', NULL),
		'data' => $text,
		'crop' => defval($params, 'crop'),
		'show_description' => defval($params, 'show_description'),
	)));

	return $image->html_code();
}
