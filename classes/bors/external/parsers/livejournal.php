<?php

class bors_external_parsers_livejournal extends bors_object
{
	static function id_prepare($text)
	{
		if(preg_match('/<table.*imhonet.*профиль/s', $text))
			return bors_load('bors_external_parsers_imholj', $text);

		return parent::id_prepare($text);
	}
}
