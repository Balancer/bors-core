<?php

function smarty_function_input_image($params, &$smarty)
{
	echo bors_forms_image::html($params);
}
