<?php

class bors_lib_string
{
	static function switch_layout($string)
	{
		$srl = ec("ё й ц у к е н г ш щ з х ъ ф ы в а п р о л д ж э я ч с м и т ь б ю");
		$sru = ec('Ё Й Ц У К Е Н Г Ш Щ З Х Ъ Ф Ы В А П Р О Л Д Ж Э Я Ч С М И Т Ь Б Ю');
		$sll =    "` q w e r t y u i o p [ ] a s d f g h i k l ; ' z x c v b n m , .";
		$slu =    '~ Q W E R T Y U I O P { } A S D F G H J K L : " Z X C V B N M , >';
		if(preg_match('/[а-яА-ЯёЁ]/u', $string))
			return str_replace(explode(' ', "$srl $sru"), explode(' ', "$sll $slu"), $string);
		else
			return str_replace(explode(' ', "$sll $slu"), explode(' ', "$srl $sru"), $string);
	}
}
