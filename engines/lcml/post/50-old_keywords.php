<?php

function lcml_old_keywords($text)
{
	if(!config('lcml_old_keywords'))
		return $text;

	static $keywords = false;
	if(!$keywords)
	{
		$dbh = new driver_mysql('HTS');
		$GLOBALS['lcml_ok_this_url'] = bors()->main_object() ? bors()->main_object()->url() : NULL;

		$keywords = array_filter(
			$dbh->select_array('hts_data_keyword', 'id as url, value as keyword', array('order' => '-value')),
			create_function('$x', 'return !is_numeric($x["url"]) 
				&& $GLOBALS["lcml_ok_this_url"] != $x["url"]
				&& $GLOBALS["lcml_ok_this_url"] != "http://".$_SERVER["HTTP_HOST"].$x["url"];')
		);
	}

	foreach($keywords as $x)
		if(strpos($text, $x['keyword']) !== false)
			$text = preg_replace("/(?<=\s|^)(".preg_quote($x['keyword'], '/').")(?=\s|$|\.|;|:)/ims", "<a href=\"{$x['url']}\">{$x['keyword']}</a>", $text);

	return $text;
}
