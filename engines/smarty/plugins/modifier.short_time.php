<?php

function smarty_modifier_short_time($time, $def = '')
{
   	return bors_lib_time::short($time, $def);
}
