<?php

class bors_lcml_tag_pair_reference extends bors_lcml_tag_pair
{
	function html($text, &$params)
	{
		// Какой-то трюк с добавлением и убиранием мусора, чтобы не считалось за начало строки.
		return "<small>//&nbsp;".substr($this->lcml('. '.$text), 2)."</small>";
	}
}
