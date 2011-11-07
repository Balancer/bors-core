<?php

echo "<ul class=\"yellow_box none\">";
foreach(bors_lib_orm::main_fields($target) as $idx => $args)
{
	if(@$args['is_editable'] === false)
		continue;

//	var_dump($args);
	$value = $target->get($args['property']);
	if(!empty($args['class']))
		$value = object_property(bors_load($args['class'], $value), 'titled_link');
	elseif($args['type'] == 'freedate')
		$value = bors_lower(part_date($value, true));
	else
		$value = htmlspecialchars($value);

	if($value)
		echo "<li>{$args['title']}: ".$value."</li>\n";
}
echo "</ul>";

echo lcml_bb($target->get('source'));
