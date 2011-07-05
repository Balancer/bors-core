<?php

class bors_external_tumblr extends bors_object
{
	static function parse($data)
	{
//		var_dump($data); exit();
		extract($data);

		if(!empty($link))
			$text .= "<br/><br/><span class=\"transgray\">// Транслировано с ".bors_external_feeds_entry::url_host_link_html($link)."</span>";


		return array(
			'text' => $text,
			'bb_code' => $text,
			'markup' => 'bors_markup_html',
		);
	}
}
