<?php

config_set('graphviz_dot_command', '/usr/bin/dot');

function lp_graphviz($text, $params)
{
	if(!preg_match('/^\w+\s+\w+\s+\{/s.+\}', trim($text)))
		$text = "digraph G { {$text} }";

	$image = bors_load('bors_image_generated', serialize(array(
		'class_name' => 'bors_image_generated_graphviz',
		'width' => defval_ne($params, 'width', NULL),
		'height' => defval_ne($params, 'height', NULL),
		'data' => restore_format($text),
		'crop' => defval($params, 'crop'),
		'show_description' => defval($params, 'show_description'),
	)));

	return $image->html_code();
}
