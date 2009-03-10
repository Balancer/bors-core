<?php

function lp_delayed($txt, $params)
{
	return save_format("<span><!--".trim(restore_format($txt))."--></span>");
}
