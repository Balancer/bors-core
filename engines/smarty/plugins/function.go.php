<?php

function smarty_function_go($params, &$smarty)
{
	base_object::add_template_data('form_have_go', true);
	if($params['value'])
		echo "<input type=\"hidden\" name=\"go\" value=\"".addslashes($params['value'])."\" />\n";
}
