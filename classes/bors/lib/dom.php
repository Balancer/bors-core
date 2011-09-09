<?php

class bors_lib_dom
{
	static function element_html($element)
	{
		$tmp_dom = new DOMDocument();
		$tmp_dom->appendChild($tmp_dom->importNode($element, true));
		return trim($tmp_dom->saveHTML());
	}
}
