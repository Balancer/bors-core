<?php

function lp_media($url)
{
	if(preg_match('/youtube/', $url))
		return bors_external_youtube::url2html($url);

	debug_hidden_log('_dev_advice_lcml_media', "Unknown media link: ".$media);
	return lcml($url);
}
