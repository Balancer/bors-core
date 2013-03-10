<?php

$class_code = "
class bors_templates_smarty extends bors_templates_smarty3
{
	static function get_var(\$smarty, \$name)
	{
		return \$smarty->getTemplateVars(\$name);
	}
}
";

eval($class_code);
