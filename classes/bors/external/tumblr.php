<?php

class bors_external_tumblr extends bors_object
{
	static function parse($data)
	{
//		var_dump($data); exit();
		extract($data);

		return array(
			'text' => $text,
			'bb_code' => $text,
			'markup' => 'bors_markup_html',
		);
	}
}
