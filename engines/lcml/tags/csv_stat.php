<?php

function lp_csv_stat($txt, $params)
{
	require_once('inc/csv.php');

	$delim	= defval_ne($params, 'delim', ';');
	$width	= defval_ne($params, 'width', 500);
	$height	= defval_ne($params, 'height', 500);

   	$data = array();
   	$avg = array();
   	$total = array();
    foreach(explode("\n", $txt) as $s)
    {
		if(!($s = trim($s)))
			continue;

		list($title, $value) = csv_explode($s, $delim);

		$data[$title][] = $value;
		$data[$title]['series_name'] = $title;
		@$avg[$title] += $value;
		@$total[$title]++;
	}

	foreach($total as $key => $n)
		$avg[$key] /= $n;

	$GLOBALS['lp_csv_stat_avg'] = $avg;;

	uksort($data, create_function('$a, $b', 'return $GLOBALS["lp_csv_stat_avg"][$a] > $GLOBALS["lp_csv_stat_avg"][$b];'));

	$image = object_load('bors_image_generated', serialize(array(
		'class_name' => 'bors_image_generated_boxplot',
		'width' => $width,
		'height' => $height,
		'data' => $data,
		'crop' => defval($params, 'crop'),
		'show_description' => defval($params, 'show_description'),
	)));

	return $image->html_code();
}
