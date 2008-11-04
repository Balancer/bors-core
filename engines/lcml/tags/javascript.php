<?php

function lp_javascript($txt, $params)
{
	return "<script type=\"text/javascript\"><!--".save_format("\n$txt\n")."--></script>";
}
