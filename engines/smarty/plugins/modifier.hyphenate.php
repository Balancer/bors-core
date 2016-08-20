<?php
function smarty_modifier_hyphenate($string)
{
	return \B2\Hypher::hyphenate($string);
}
