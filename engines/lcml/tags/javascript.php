<?php

function lp_javascript($txt, $params)
{
	return save_format("<script type=\"text/javascript\"><!--\n".trim(restore_format($txt))."\n--></script>");
}
