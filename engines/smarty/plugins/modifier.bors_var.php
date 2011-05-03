<?php

function smarty_modifier_bors_var($name, $default = NULL)
{
	return bors_var::get($name, $default);
}
