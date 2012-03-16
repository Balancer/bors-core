<?php

$extends = config('smarty3_enable') ? '3' : '2';

$class_code = "
class bors_templates_smarty extends bors_templates_smarty{$extends}
{
	static function get_var(\$smarty, \$name)
	{
		if(method_exists(\$smarty, 'getTemplateVars'))
			return \$smarty->getTemplateVars(\$name);
		else
			return \$smarty->get_template_vars(\$name);
	}
}
";

eval($class_code);