<?php

class bors_external_picasaweb extends bors_object
{
	static function parse($data)
	{
		extract($data);

		$text .= "<p>{$title}</p>";
		$text = str_replace('<table>', '<table class="small">', $text);

		if(!empty($link))
			$text .= "<br/><br/><span class=\"transgray\">// Транслировано с ".bors_external_feeds_entry::url_host_link_html($link)."</span>";

		return array(
			'text' => $text,
			'bb_code' => $text,
			'markup' => 'bors_markup_html',
		);
	}
}
