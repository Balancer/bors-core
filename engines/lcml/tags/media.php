<?php

function lp_media($url)
{
	if(preg_match('/youtube/', $url))
		return bors_external_youtube::url2html($url);

	bors_debug::syslog('_dev_advice_lcml_media', "Unknown media link: ".$media);
	return lcml($url);
}
