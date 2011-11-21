<?php

$html = "<div class=\"yellow_box\"><ul class=\"none\">";

$out = array();
foreach(bors_lib_orm::main_fields($target) as $idx => $args)
{
	if(@$args['is_editable'] === false)
		continue;

	$value = $target->get($args['property']);
//	var_dump($args, $value);
	if(!empty($args['class']))
		$value = object_property(bors_load($args['class'], $value), 'titled_link');
	elseif(!empty($args['named_list']))
		$value = object_property(bors_load($args['named_list'], $value), 'title');
	elseif($args['type'] == 'freedate')
		$value = bors_lower(part_date($value, true));
	elseif($args['type'] == 'image') // http://ucrm.wrk.ru/persons/4/
	{
//		var_dump($value);
		if($image = bors_load('bors_image', $value))
			array_unshift($out, $image->thumbnail('200x200')->html(array('append' => 'align="left" hspace="10"')));

		continue;
	}
	else
		$value = htmlspecialchars($value);

	if($value && ($title = $args['title']))
		$out[] = "<li>{$title}: ".$value."</li>\n";
}

$html .= join("", $out);
$html .= "</ul><div class=\"clear\">&nbsp;</div></div>";

echo $html;
echo lcml_bb($target->get('source'));
