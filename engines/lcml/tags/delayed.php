<?php

function lp_delayed($txt, $params)
{
	return "<span><!--".save_format($txt)."--></span>";
}
