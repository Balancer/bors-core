<?php
function smarty_modifier_array_rows($array, $columns)
{
	return ceil(count($array)/$columns);
}
