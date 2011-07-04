<?php

function smarty_modifier_css_class($idx, $class_list)
{
//	echo "############\n";
	$class_list = str_replace('|', ',', $class_list);
//	print_dd("\$class_list = array($class_list);");

	eval("\$class_list = array($class_list);");
//	print_dd($class_list);

//	echo "$idx:==={$class_list[$idx]}===\n";
	if($class = @$class_list[$idx])
	{
//		echo "found class='$class'\n\n";
		echo " class='$class'";
	}

}
