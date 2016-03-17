<?php

function smarty_function_form_section($params, &$smarty)
{
	echo "<tr><th colspan=\"2\" class=\"form-section-title\" style=\"text-align: center; font-size: 120%;\">{$params['label']}</th></tr>\n";
}
