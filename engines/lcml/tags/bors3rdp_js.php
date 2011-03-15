<?php

function lp_bors3rdp_js($txt, &$params)
{
	$params['skip_around_cr'] = true;
//	bors_page::add_template_data_array('js_include', "/_bors3rdp/$txt");
	return save_format("<script type=\"text/javascript\" src=\"/_bors3rdp/$txt\" />");
}
